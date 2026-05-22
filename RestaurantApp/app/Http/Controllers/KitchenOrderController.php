<?php

namespace App\Http\Controllers;

use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KitchenOrderController extends Controller
{
    public function index(): View
    {
        /** @var array<string, array{label: string, bg: string, icon: string}> */
        $allergenConfig = config('restaurant.allergens');

        $dbOrders = Order::with(['items.dish.ingredients', 'floorPlanElement', 'user', 'reservation'])
            ->whereIn('status', [OrderStatus::Active->value, OrderStatus::Completed->value])
            ->where('paid', false)
            ->whereHas('items.dish', function ($query): void {
                // Kitchen items: routing_tag = 'kitchen' OR legacy items (is_bar_item = false and no routing_tag)
                $query->where(function ($q): void {
                    $q->where('routing_tag', 'kitchen')
                        ->orWhere(function ($q2): void {
                            $q2->where('is_bar_item', false)
                                ->whereNull('routing_tag');
                        });
                });
            })
            ->latest()
            ->get();

        $orders = $dbOrders->map(function (Order $order): ?array {
            // Filter items to only show kitchen items
            $kitchenItems = $order->items->filter(function ($item): bool {
                $dish = $item->dish;
                if (!$dish) {
                    return false;
                }
                // Show if routing_tag is kitchen, or legacy non-bar items
                return $dish->routing_tag === 'kitchen'
                    || ($dish->is_bar_item === false && $dish->routing_tag === null);
            });

            $dishes = $kitchenItems->map(function ($item): array {
                $allergens = $item->dish?->allergens ?? [];

                return [
                    'item_id' => $item->id,
                    'name' => $item->dish?->name ?? 'Unknown',
                    'qty' => $item->quantity,
                    'allergens' => $allergens,
                    'notes' => $item->notes ?? '',
                    'status' => $item->status->value,
                ];
            })->all();

            // Skip orders with no kitchen items
            if (empty($dishes)) {
                return null;
            }

            $statuses = array_column($dishes, 'status');
            $cntPending = count(array_filter($statuses, fn ($s) => $s === 'pending' || $s === 'preparing'));
            $cntReady = count(array_filter($statuses, fn ($s) => $s === 'ready'));
            $cntServed = count(array_filter($statuses, fn ($s) => $s === 'served'));
            $cntTotal = count($statuses);
            $overall = $cntTotal > 0 && $cntServed === $cntTotal ? 'completed'
                        : ($cntReady > 0 && $cntPending === 0 ? 'ready' : 'pending');

            $element = $order->floorPlanElement;

            return [
                'id' => 'ORD-'.str_pad((string) $order->id, 3, '0', STR_PAD_LEFT),
                'db_id' => $order->id,
                'type' => 'restaurant',
                'table' => $element?->table_name ?? '—',
                'room' => null,
                'time' => $order->created_at?->format('H:i') ?? '—',
                'waiter' => $order->user?->name ?? '—',
                'customer' => $order->reservation?->guest_name ?? '—',
                'dishes' => $dishes,
                'cnt_pending' => $cntPending,
                'cnt_ready' => $cntReady,
                'cnt_served' => $cntServed,
                'cnt_total' => $cntTotal,
                'overall' => $overall,
            ];
        })->filter()->values()->all();

        $countActive = count(array_filter($orders, fn ($o) => $o['overall'] !== 'completed'));
        $countCompleted = count(array_filter($orders, fn ($o) => $o['overall'] === 'completed'));
        $totalPending = (int) array_sum(array_column($orders, 'cnt_pending'));
        $totalReady = (int) array_sum(array_column($orders, 'cnt_ready'));

        return view('kitchen-orders', compact(
            'allergenConfig',
            'orders',
            'countActive',
            'countCompleted',
            'totalPending',
            'totalReady',
        ));
    }

    /**
     * Toggle a single dish item between pending ↔ ready.
     */
    public function markDishReady(Request $request, OrderItem $orderItem): JsonResponse
    {
        $nextStatus = $orderItem->status === OrderItemStatus::Ready
            ? OrderItemStatus::Pending
            : OrderItemStatus::Ready;

        $orderItem->update(['status' => $nextStatus]);

        return response()->json(['status' => $nextStatus->value]);
    }

    /**
     * Mark all items on an order as served and complete the order.
     */
    public function completeOrder(Order $order): JsonResponse
    {
        $order->items()->update(['status' => OrderItemStatus::Served]);
        $order->update(['status' => OrderStatus::Completed]);

        return response()->json(['status' => 'completed']);
    }

    /**
     * Delete an order and its items.
     */
    public function deleteOrder(Order $order): JsonResponse
    {
        $order->items()->delete();
        $order->delete();

        return response()->json(['status' => 'deleted']);
    }

    /**
     * Poll endpoint for kitchen orders (JSON).
     */
    public function poll(): JsonResponse
    {
        // Limit to recent orders and restrict sensitive data exposure
        $dbOrders = Order::with([
            'items.dish:id,name,allergens,routing_tag,is_bar_item',
            'floorPlanElement:id,table_name'
        ])
            ->whereIn('status', [OrderStatus::Active->value, OrderStatus::Completed->value])
            ->where('paid', false)
            ->whereHas('items.dish', function ($query): void {
                $query->where('routing_tag', 'kitchen')
                    ->orWhere(function ($q): void {
                        $q->where('is_bar_item', false)->whereNull('routing_tag');
                    });
            })
            ->latest()
            ->limit(50)
            ->get(['id', 'floor_plan_element_id', 'status', 'paid', 'created_at', 'updated_at']);

        $orders = $dbOrders->map(function (Order $order): ?array {
            // Filter to only kitchen items
            $kitchenItems = $order->items->filter(function ($item): bool {
                $dish = $item->dish;
                if (!$dish) return false;
                return $dish->routing_tag === 'kitchen'
                    || ($dish->is_bar_item === false && $dish->routing_tag === null);
            });

            $dishes = $kitchenItems->map(function ($item): array {
                $allergens = $item->dish?->allergens ?? [];

                return [
                    'item_id' => $item->id,
                    'name' => $item->dish?->name ?? 'Unknown',
                    'qty' => $item->quantity,
                    'allergens' => $allergens,
                    'notes' => $item->notes ?? '',
                    'status' => $item->status->value,
                ];
            })->all();

            if (empty($dishes)) {
                return null;
            }

            $statuses = array_column($dishes, 'status');
            $cntPending = count(array_filter($statuses, fn ($s) => $s === 'pending' || $s === 'preparing'));
            $cntReady = count(array_filter($statuses, fn ($s) => $s === 'ready'));
            $cntServed = count(array_filter($statuses, fn ($s) => $s === 'served'));
            $cntTotal = count($statuses);
            $overall = $cntTotal > 0 && $cntServed === $cntTotal ? 'completed'
                        : ($cntReady > 0 && $cntPending === 0 ? 'ready' : 'pending');

            $element = $order->floorPlanElement;

            return [
                'id' => 'ORD-'.str_pad((string) $order->id, 3, '0', STR_PAD_LEFT),
                'db_id' => $order->id,
                'type' => 'restaurant',
                'table' => $element?->table_name ?? '—',
                'room' => null,
                'time' => $order->created_at?->format('H:i') ?? '—',
                'waiter' => $order->user?->name ?? '—',
                'customer' => $order->reservation?->guest_name ?? '—',
                'dishes' => $dishes,
                'cnt_pending' => $cntPending,
                'cnt_ready' => $cntReady,
                'cnt_served' => $cntServed,
                'cnt_total' => $cntTotal,
                'overall' => $overall,
            ];
        })->filter()->values()->all();

        $countActive = count(array_filter($orders, fn ($o) => $o['overall'] !== 'completed'));
        $countCompleted = count(array_filter($orders, fn ($o) => $o['overall'] === 'completed'));
        $totalPending = (int) array_sum(array_column($orders, 'cnt_pending'));
        $totalReady = (int) array_sum(array_column($orders, 'cnt_ready'));

        return response()->json([
            'orders' => $orders,
            'countActive' => $countActive,
            'countCompleted' => $countCompleted,
            'totalPending' => $totalPending,
            'totalReady' => $totalReady,
        ]);
    }
}
