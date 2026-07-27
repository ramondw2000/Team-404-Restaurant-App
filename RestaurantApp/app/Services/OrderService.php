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
            ->whereHas('reservation', fn ($q) => $q->whereIn('status', ['scheduled', 'arrived']))
            ->latest()
            ->first();
    }

    /**
     * Get unpaid orders (Active or Completed with paid=false) for a table element.
     *
     * @return Collection<int, Order>
     */
    public function getUnpaidOrdersForElement(int $elementId): Collection
    {
        return Order::with(['items.dish', 'reservation', 'floorPlanElement'])
            ->where('floor_plan_element_id', $elementId)
            ->whereIn('status', [OrderStatus::Active, OrderStatus::Completed])
            ->where('paid', false)
            ->whereHas('reservation', fn ($q) => $q->whereIn('status', ['scheduled', 'arrived']))
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Get every Active/Completed order for a table element (paid and unpaid).
     *
     * @return Collection<int, Order>
     */
    public function getOrdersForElementExcludingDraft(int $elementId): Collection
    {
        return Order::with(['items.dish', 'reservation', 'floorPlanElement'])
            ->where('floor_plan_element_id', $elementId)
            ->whereIn('status', [OrderStatus::Active, OrderStatus::Completed])
            ->whereHas('reservation', fn ($q) => $q->whereIn('status', ['scheduled', 'arrived']))
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Full order overview for a table element — paid and unpaid items aggregated
     * separately, plus a grand total across both.
     *
     * @return array<string, mixed>|null
     */
    public function getOrderSummaryForElement(int $elementId): ?array
    {
        $orders = $this->getOrdersForElementExcludingDraft($elementId);

        if ($orders->isEmpty()) {
            return null;
        }

        $unpaidOrders = $orders->where('paid', false)->values();
        $paidOrders = $orders->where('paid', true)->values();

        $unpaid = $this->aggregateItems($unpaidOrders);
        $paid = $this->aggregateItems($paidOrders);

        $grandSubtotal = round($unpaid['subtotal'] + $paid['subtotal'], 2);
        $grandTax = round($grandSubtotal * (float) config('tax.rate'), 2);
        $grandTotal = round($grandSubtotal + $grandTax, 2);

        $first = $orders->first();
        $last = $orders->last();

        return [
            'table_name' => $first->floorPlanElement?->table_name ?? 'Unknown',
            'guest_name' => $first->reservation?->guest_name,
            'order_count' => $orders->count(),
            'unpaid_order_count' => $unpaidOrders->count(),
            'paid_order_count' => $paidOrders->count(),
            'first_order_at' => $first->created_at?->format('d M Y H:i'),
            'latest_order_at' => $last->created_at?->format('d M Y H:i'),
            'unpaid' => $unpaid,
            'paid' => $paid,
            'grand_subtotal' => $grandSubtotal,
            'grand_tax' => $grandTax,
            'grand_total' => $grandTotal,
        ];
    }

    /**
     * Aggregate items from a collection of orders. Returns empty items + zero totals
     * for an empty collection.
     *
     * @param  Collection<int, Order>  $orders
     * @return array{items: array<int, array<string, mixed>>, subtotal: float, tax: float, total: float}
     */
    private function aggregateItems(Collection $orders): array
    {
        $buckets = [];
        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $key = $item->dish_id.'-'.$item->unit_price.'-'.($item->notes ?? '');
                if (isset($buckets[$key])) {
                    $buckets[$key]['quantity'] += $item->quantity;
                    $buckets[$key]['total'] = round($buckets[$key]['quantity'] * (float) $item->unit_price, 2);
                } else {
                    $buckets[$key] = [
                        'name' => $item->dish?->name ?? 'Unknown Dish',
                        'quantity' => $item->quantity,
                        'unit_price' => (float) $item->unit_price,
                        'total' => round($item->quantity * (float) $item->unit_price, 2),
                        'notes' => $item->notes,
                    ];
                }
            }
        }

        $items = array_values($buckets);
        $subtotal = round(collect($items)->sum('total'), 2);
        $tax = round($subtotal * (float) config('tax.rate'), 2);
        $total = round($subtotal + $tax, 2);

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
        ];
    }

    /**
     * Aggregate unpaid items for a table element for display in Order Info.
     *
     * @return array<string, mixed>|null
     */
    public function getUnpaidOrderSummaryForElement(int $elementId): ?array
    {
        $orders = $this->getUnpaidOrdersForElement($elementId);

        if ($orders->isEmpty()) {
            return null;
        }

        $aggregated = [];
        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $key = $item->dish_id.'-'.$item->unit_price.'-'.($item->notes ?? '');
                if (isset($aggregated[$key])) {
                    $aggregated[$key]['quantity'] += $item->quantity;
                    $aggregated[$key]['total'] = round($aggregated[$key]['quantity'] * (float) $item->unit_price, 2);
                } else {
                    $aggregated[$key] = [
                        'name' => $item->dish?->name ?? 'Unknown Dish',
                        'quantity' => $item->quantity,
                        'unit_price' => (float) $item->unit_price,
                        'total' => round($item->quantity * (float) $item->unit_price, 2),
                        'notes' => $item->notes,
                    ];
                }
            }
        }

        $items = array_values($aggregated);
        $subtotal = round(collect($items)->sum('total'), 2);
        $tax = round($subtotal * (float) config('tax.rate'), 2);
        $total = round($subtotal + $tax, 2);

        $firstOrder = $orders->first();
        $lastOrder = $orders->last();

        return [
            'table_name' => $firstOrder->floorPlanElement?->table_name ?? 'Unknown',
            'guest_name' => $firstOrder->reservation?->guest_name,
            'order_count' => $orders->count(),
            'item_count' => count($items),
            'first_order_at' => $firstOrder->created_at?->format('d M Y H:i'),
            'latest_order_at' => $lastOrder->created_at?->format('d M Y H:i'),
            'items' => $items,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
        ];
    }

    /**
     * Mark all unpaid Active/Completed orders for a table element as paid and completed.
     * Fires OrderCompleted per order so listeners (analytics, kitchen) can react.
     */
    public function markUnpaidOrdersPaidForElement(int $elementId): int
    {
        return DB::transaction(function () use ($elementId): int {
            $orders = $this->getUnpaidOrdersForElement($elementId);

            foreach ($orders as $order) {
                $wasCompleted = $order->status === OrderStatus::Completed;
                $order->update([
                    'status' => OrderStatus::Completed,
                    'paid' => true,
                ]);

                if (! $wasCompleted) {
                    event(new OrderCompleted($order->fresh()));
                }
            }

            return $orders->count();
        });
    }

    /**
     * Build receipt payload from unpaid orders for a table element.
     *
     * @return array<string, mixed>|null
     */
    public function getUnpaidReceiptForElement(int $elementId): ?array
    {
        $summary = $this->getUnpaidOrderSummaryForElement($elementId);

        if (! $summary) {
            return null;
        }

        $firstOrder = $this->getUnpaidOrdersForElement($elementId)->first();
        $reservation = $firstOrder?->reservation;

        return [
            'guest_name' => $summary['guest_name'] ?? 'Walk-in Guest',
            'table_name' => $summary['table_name'],
            'party_size' => $reservation?->party_size,
            'reservation_time' => $reservation?->reservation_datetime?->format('d M Y H:i'),
            'order_count' => $summary['order_count'],
            'items' => $summary['items'],
            'subtotal' => $summary['subtotal'],
            'tax' => $summary['tax'],
            'total' => $summary['total'],
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
