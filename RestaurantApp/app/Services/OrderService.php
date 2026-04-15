<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Events\OrderCompleted;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service responsible for order operations.
 * Single Responsibility: Handles order lifecycle management.
 */
final readonly class OrderService
{
    /**
     * Get active order for a table element with items.
     */
    public function getActiveOrderForElement(int $elementId): ?Order
    {
        return Order::with(['items.dish', 'floorPlanElement'])
            ->where('floor_plan_element_id', $elementId)
            ->whereIn('status', [OrderStatus::Draft, OrderStatus::Active])
            ->latest()
            ->first();
    }

    /**
     * Get order details formatted for display.
     *
     * @return array<string, mixed>|null
     */
    public function getOrderDetails(int $orderId): ?array
    {
        $order = Order::with(['items.dish', 'floorPlanElement', 'reservation'])->find($orderId);

        if (! $order) {
            return null;
        }

        $items = $order->items->map(fn (OrderItem $item): array => [
            'id' => $item->id,
            'name' => $item->dish?->name ?? 'Unknown Dish',
            'quantity' => $item->quantity,
            'unit_price' => (float) $item->unit_price,
            'total' => round($item->quantity * (float) $item->unit_price, 2),
            'status' => $item->status->value,
            'notes' => $item->notes,
        ])->all();

        $subtotal = collect($items)->sum('total');
        $tax = round($subtotal * 0.1, 2);
        $total = round($subtotal + $tax, 2);

        return [
            'id' => $order->id,
            'order_number' => 'ORD-'.str_pad((string) $order->id, 3, '0', STR_PAD_LEFT),
            'status' => $order->status->value,
            'paid' => (bool) $order->paid,
            'table_name' => $order->floorPlanElement?->table_name ?? 'Unknown',
            'guest_name' => $order->reservation?->guest_name,
            'created_at' => $order->created_at?->format('d M Y H:i'),
            'items' => $items,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'item_count' => count($items),
        ];
    }

    /**
     * Get receipt data for all orders under a reservation.
     *
     * @return array<string, mixed>|null
     */
    public function getReceiptForReservation(int $reservationId): ?array
    {
        $reservation = Reservation::with(['floorPlanElement'])->find($reservationId);

        if (! $reservation) {
            return null;
        }

        $orders = Order::with(['items.dish'])
            ->where('reservation_id', $reservationId)
            ->whereIn('status', [OrderStatus::Active, OrderStatus::Completed])
            ->orderBy('created_at')
            ->get();

        $allItems = [];
        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $key = $item->dish_id.'-'.$item->unit_price;
                if (isset($allItems[$key])) {
                    $allItems[$key]['quantity'] += $item->quantity;
                    $allItems[$key]['total'] = round($allItems[$key]['quantity'] * (float) $item->unit_price, 2);
                } else {
                    $allItems[$key] = [
                        'name' => $item->dish?->name ?? 'Unknown Dish',
                        'quantity' => $item->quantity,
                        'unit_price' => (float) $item->unit_price,
                        'total' => round($item->quantity * (float) $item->unit_price, 2),
                    ];
                }
            }
        }

        $items = array_values($allItems);
        $subtotal = collect($items)->sum('total');
        $tax = round($subtotal * 0.1, 2);
        $total = round($subtotal + $tax, 2);

        return [
            'guest_name' => $reservation->guest_name,
            'table_name' => $reservation->floorPlanElement?->table_name ?? $reservation->table_number ?? 'Unknown',
            'party_size' => $reservation->party_size,
            'reservation_time' => $reservation->reservation_datetime->format('d M Y H:i'),
            'order_count' => $orders->count(),
            'items' => $items,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'printed_at' => now()->format('d M Y H:i'),
        ];
    }

    /**
     * Get receipt data for a single order (when no reservation).
     *
     * @return array<string, mixed>|null
     */
    public function getReceiptForOrder(int $orderId): ?array
    {
        $details = $this->getOrderDetails($orderId);
        if (! $details) {
            return null;
        }

        return [
            'guest_name' => $details['guest_name'] ?? 'Walk-in Guest',
            'table_name' => $details['table_name'],
            'party_size' => null,
            'reservation_time' => null,
            'order_count' => 1,
            'items' => $details['items'],
            'subtotal' => $details['subtotal'],
            'tax' => $details['tax'],
            'total' => $details['total'],
            'printed_at' => now()->format('d M Y H:i'),
        ];
    }

    /**
     * Mark an order as completed and update related entities.
     *
     * @throws \Exception
     */
    public function completeOrder(int $orderId): Order
    {
        return DB::transaction(function () use ($orderId): Order {
            $order = Order::with(['items', 'floorPlanElement'])->findOrFail($orderId);

            $order->items()->update(['status' => OrderItemStatus::Served]);
            $order->update(['status' => OrderStatus::Completed]);

            event(new OrderCompleted($order));

            return $order->fresh();
        });
    }

    /**
     * Check if all items in an order are ready/served.
     */
    public function isOrderReadyToComplete(int $orderId): bool
    {
        $order = Order::with('items')->find($orderId);

        if (! $order) {
            return false;
        }

        if ($order->items->isEmpty()) {
            return false;
        }

        return $order->items->every(fn (OrderItem $item): bool => in_array($item->status, [OrderItemStatus::Ready, OrderItemStatus::Served], true)
        );
    }

    /**
     * Get all orders for a table element.
     *
     * @return Collection<int, Order>
     */
    public function getOrdersForElement(int $elementId): Collection
    {
        return Order::with(['items.dish'])
            ->where('floor_plan_element_id', $elementId)
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Get all orders linked to a reservation.
     *
     * @return Collection<int, Order>
     */
    public function getOrdersForReservation(int $reservationId): Collection
    {
        return Order::with(['items.dish'])
            ->where('reservation_id', $reservationId)
            ->orderByDesc('created_at')
            ->get();
    }
}
