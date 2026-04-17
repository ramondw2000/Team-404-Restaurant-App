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
            ->latest()
            ->get();

        $orders = $dbOrders->map(function (Order $order): array {
            $dishes = $order->items->map(function ($item): array {
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
        })->all();

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
}
