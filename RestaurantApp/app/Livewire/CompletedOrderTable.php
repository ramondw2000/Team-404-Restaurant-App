<?php

namespace App\Livewire;

use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CompletedOrderTable extends Component
{
    #[Url]
    public string $search = '';

    public string $dateRange = 'today';

    public ?string $customDateFrom = null;

    public ?string $customDateTo = null;

    public string $paymentMethod = '';

    /** @var string[] */
    public array $selectedLocations = [];

    /** @var string[] */
    public array $selectedWaiters = [];

    public string $orderType = '';

    public int $perPage = 25;

    public string $sortField = 'completed_at';

    public string $sortDirection = 'desc';

    /** @var string[] */
    public array $selectedOrders = [];

    public bool $selectAllOnPage = false;

    public bool $showReceiptModal = false;

    public ?string $receiptOrderId = null;

    /**
     * @return Collection<int, array<string, mixed>>
     */
    #[Computed]
    public function allOrders(): Collection
    {
        return $this->getCompletedOrders();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    #[Computed]
    public function filteredOrders(): Collection
    {
        $orders = $this->allOrders;

        if ($this->search !== '') {
            $needle = mb_strtolower($this->search);
            $orders = $orders->filter(function (array $order) use ($needle): bool {
                return str_contains(mb_strtolower($order['id']), $needle)
                    || str_contains(mb_strtolower($order['customer'] ?? ''), $needle)
                    || str_contains(mb_strtolower($order['waiter']), $needle)
                    || str_contains(mb_strtolower($order['location']), $needle);
            });
        }

        if ($this->paymentMethod !== '') {
            $orders = $orders->where('payment_method', $this->paymentMethod);
        }

        if ($this->selectedLocations !== []) {
            $orders = $orders->whereIn('location', $this->selectedLocations);
        }

        if ($this->selectedWaiters !== []) {
            $orders = $orders->whereIn('waiter', $this->selectedWaiters);
        }

        if ($this->orderType !== '') {
            $orders = $orders->where('type', $this->orderType);
        }

        $direction = $this->sortDirection === 'asc';
        $orders = $orders->sortBy($this->sortField, SORT_REGULAR, ! $direction)->values();

        return $orders;
    }

    #[Computed]
    public function paginatedOrders(): Collection
    {
        $page = $this->getPage();
        $offset = ($page - 1) * $this->perPage;

        return $this->filteredOrders->slice($offset, $this->perPage)->values();
    }

    #[Computed]
    public function totalPages(): int
    {
        return max(1, (int) ceil($this->filteredOrders->count() / $this->perPage));
    }

    #[Computed]
    public function currentPage(): int
    {
        return $this->getPage();
    }

    /**
     * @return string[]
     */
    #[Computed]
    public function availableLocations(): array
    {
        return $this->allOrders->pluck('location')->unique()->sort()->values()->all();
    }

    /**
     * @return string[]
     */
    #[Computed]
    public function availableWaiters(): array
    {
        return $this->allOrders->pluck('waiter')->unique()->sort()->values()->all();
    }

    /**
     * @return string[]
     */
    #[Computed]
    public function availablePaymentMethods(): array
    {
        return $this->allOrders->pluck('payment_method')->unique()->sort()->values()->all();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatedDateRange(): void
    {
        $this->resetPage();
        $this->resetSelection();
        $this->unsetComputed();
    }

    public function updatedPaymentMethod(): void
    {
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatedSelectedLocations(): void
    {
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatedSelectedWaiters(): void
    {
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatedOrderType(): void
    {
        $this->resetPage();
        $this->resetSelection();
    }

    public function setPerPage(int $perPage): void
    {
        $this->perPage = $perPage;
        $this->resetPage();
        $this->resetSelection();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->unsetComputed();
    }

    public function toggleSelectAll(): void
    {
        if ($this->selectAllOnPage) {
            $this->selectedOrders = $this->paginatedOrders->pluck('id')->all();
        } else {
            $this->selectedOrders = [];
        }
    }

    public function updatedSelectedOrders(): void
    {
        $pageIds = $this->paginatedOrders->pluck('id')->all();
        $this->selectAllOnPage = ! empty($pageIds) && ! array_diff($pageIds, $this->selectedOrders);
    }

    public function viewReceipt(string $orderId): void
    {
        $this->receiptOrderId = $orderId;
        $this->showReceiptModal = true;
    }

    public function closeReceipt(): void
    {
        $this->showReceiptModal = false;
        $this->receiptOrderId = null;
    }

    /**
     * @return array<string, mixed>|null
     */
    #[Computed]
    public function receiptOrder(): ?array
    {
        if (! $this->receiptOrderId) {
            return null;
        }

        return $this->allOrders->firstWhere('id', $this->receiptOrderId);
    }

    public function printReceipt(string $orderId): void
    {
        $this->dispatch('print-receipt', orderId: $orderId);
    }

    public function batchPrint(): void
    {
        if (empty($this->selectedOrders)) {
            return;
        }

        $this->dispatch('batch-print', orderIds: $this->selectedOrders);
    }

    public function exportCsv(): StreamedResponse
    {
        $orders = empty($this->selectedOrders)
            ? $this->filteredOrders
            : $this->filteredOrders->whereIn('id', $this->selectedOrders);

        $filename = 'completed-orders-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($orders): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Order ID', 'Location', 'Waiter', 'Items', 'Total', 'Payment Method', 'Completed At']);

            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order['id'],
                    $order['location'],
                    $order['waiter'],
                    count($order['items']) . ' items',
                    '€' . number_format($order['total'], 2),
                    $order['payment_method'] ?? '',
                    $order['closed_at'],
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function setDateRange(string $range): void
    {
        $this->dateRange = $range;
        $this->resetPage();
        $this->resetSelection();
        $this->unsetComputed();
    }

    public function nextPage(): void
    {
        $page = $this->getPage();
        if ($page < $this->totalPages) {
            $this->dispatch('set-page', page: $page + 1);
        }

        $this->resetSelection();
    }

    public function previousPage(): void
    {
        $page = $this->getPage();
        if ($page > 1) {
            $this->dispatch('set-page', page: $page - 1);
        }

        $this->resetSelection();
    }

    public function gotoPage(int $page): void
    {
        $this->dispatch('set-page', page: max(1, min($page, $this->totalPages)));
        $this->resetSelection();
    }

    /**
     * @param array<string, mixed> $order
     */
    public function rowClasses(array $order): string
    {
        if (! empty($order['is_refunded'])) {
            return 'bg-red-50';
        }

        if ($order['total'] > 100) {
            return 'bg-green-50';
        }

        if ($this->isStale($order)) {
            return 'bg-yellow-50';
        }

        return '';
    }

    public function render(): View
    {
        return view('livewire.completed-order-table');
    }

    private function resetPage(): void
    {
        $this->dispatch('set-page', page: 1);
        $this->unsetComputed();
    }

    private function resetSelection(): void
    {
        $this->selectedOrders = [];
        $this->selectAllOnPage = false;
    }

    private function getPage(): int
    {
        return 1;
    }

    /**
     * @param array<string, mixed> $order
     */
    private function isStale(array $order): bool
    {
        if (! isset($order['completed_at_carbon'])) {
            return false;
        }

        return $order['completed_at_carbon']->diffInMinutes(now()) > 30;
    }

    private function unsetComputed(): void
    {
        unset(
            $this->allOrders,
            $this->filteredOrders,
            $this->paginatedOrders,
            $this->totalPages,
            $this->currentPage,
            $this->receiptOrder,
        );
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function getCompletedOrders(): Collection
    {
        return collect([
            [
                'id' => 'ORD-045',
                'type' => 'restaurant',
                'location' => 'Table B7',
                'waiter' => 'Elena V.',
                'customer' => 'John Smith',
                'closed_at' => '18:14',
                'completed_at' => now()->subMinutes(5)->toDateTimeString(),
                'completed_at_carbon' => now()->subMinutes(5),
                'payment_method' => 'Card',
                'is_refunded' => false,
                'items' => [
                    ['name' => 'Grilled Salmon', 'qty' => 2, 'price' => 22.00],
                    ['name' => 'Beef Tenderloin', 'qty' => 1, 'price' => 28.00],
                    ['name' => 'Verdure Grigliate', 'qty' => 3, 'price' => 6.00],
                    ['name' => 'Vino Rosso della Casa', 'qty' => 1, 'price' => 6.50],
                ],
            ],
            [
                'id' => 'ORD-044',
                'type' => 'restaurant',
                'location' => 'Table A3',
                'waiter' => 'Sofia R.',
                'customer' => 'Maria Rossi',
                'closed_at' => '18:02',
                'completed_at' => now()->subMinutes(17)->toDateTimeString(),
                'completed_at_carbon' => now()->subMinutes(17),
                'payment_method' => 'Cash',
                'is_refunded' => false,
                'items' => [
                    ['name' => 'Truffle Pasta', 'qty' => 2, 'price' => 24.00],
                    ['name' => 'Lobster Bisque', 'qty' => 2, 'price' => 18.00],
                    ['name' => 'Tiramisu', 'qty' => 2, 'price' => 8.50],
                ],
            ],
            [
                'id' => 'ORD-043',
                'type' => 'room_service',
                'location' => 'Room 501',
                'waiter' => 'Marco D.',
                'customer' => 'Guest R501',
                'closed_at' => '17:55',
                'completed_at' => now()->subMinutes(24)->toDateTimeString(),
                'completed_at_carbon' => now()->subMinutes(24),
                'payment_method' => 'Room Charge',
                'is_refunded' => true,
                'items' => [
                    ['name' => 'Club Sandwich', 'qty' => 1, 'price' => 14.00],
                    ['name' => 'Caesar Salad', 'qty' => 1, 'price' => 12.00],
                    ['name' => 'Sparkling Water', 'qty' => 2, 'price' => 4.00],
                ],
            ],
            [
                'id' => 'ORD-041',
                'type' => 'room_service',
                'location' => 'Room 312',
                'waiter' => 'Marco D.',
                'customer' => 'Guest R312',
                'closed_at' => '17:45',
                'completed_at' => now()->subMinutes(34)->toDateTimeString(),
                'completed_at_carbon' => now()->subMinutes(34),
                'payment_method' => 'Room Charge',
                'is_refunded' => false,
                'items' => [
                    ['name' => 'Vegan Buddha Bowl', 'qty' => 1, 'price' => 11.50],
                    ['name' => 'Focaccia al Rosmarino', 'qty' => 1, 'price' => 5.50],
                    ['name' => 'Succo di Frutta', 'qty' => 2, 'price' => 4.00],
                ],
            ],
            [
                'id' => 'ORD-040',
                'type' => 'restaurant',
                'location' => 'Table B2',
                'waiter' => 'Sofia R.',
                'customer' => 'Anna Bianchi',
                'closed_at' => '17:38',
                'completed_at' => now()->subMinutes(41)->toDateTimeString(),
                'completed_at_carbon' => now()->subMinutes(41),
                'payment_method' => 'Card',
                'is_refunded' => false,
                'items' => [
                    ['name' => 'Minestrone Soup', 'qty' => 2, 'price' => 7.00],
                    ['name' => 'Pollo alla Cacciatora', 'qty' => 2, 'price' => 17.50],
                    ['name' => 'Caffè Affogato', 'qty' => 2, 'price' => 5.00],
                ],
            ],
            [
                'id' => 'ORD-039',
                'type' => 'room_service',
                'location' => 'Room 204',
                'waiter' => 'Marco D.',
                'customer' => 'Guest R204',
                'closed_at' => '17:10',
                'completed_at' => now()->subMinutes(69)->toDateTimeString(),
                'completed_at_carbon' => now()->subMinutes(69),
                'payment_method' => 'Room Charge',
                'is_refunded' => false,
                'items' => [
                    ['name' => 'Mushroom Risotto', 'qty' => 1, 'price' => 15.00],
                    ['name' => 'Gelato al Limone', 'qty' => 1, 'price' => 5.50],
                    ['name' => 'Acqua Minerale', 'qty' => 1, 'price' => 3.00],
                ],
            ],
        ])->map(function (array $order): array {
            $order['total'] = collect($order['items'])
                ->reduce(fn (float $carry, array $item): float => $carry + ($item['qty'] * $item['price']), 0);

            return $order;
        });
    }
}
