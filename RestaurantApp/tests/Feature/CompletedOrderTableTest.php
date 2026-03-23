<?php

use App\Livewire\CompletedOrderTable;
use Livewire\Livewire;

it('renders the completed order table component', function () {
    Livewire::test(CompletedOrderTable::class)
        ->assertStatus(200)
        ->assertSee('Order Ledger')
        ->assertSee('Completed orders');
});

it('displays all orders by default', function () {
    Livewire::test(CompletedOrderTable::class)
        ->assertSee('ORD-045')
        ->assertSee('ORD-044')
        ->assertSee('ORD-043')
        ->assertSee('ORD-041')
        ->assertSee('ORD-040')
        ->assertSee('ORD-039');
});

it('filters orders by search term on order id', function () {
    Livewire::test(CompletedOrderTable::class)
        ->set('search', 'ORD-045')
        ->assertSee('ORD-045')
        ->assertDontSee('ORD-039');
});

it('filters orders by search term on waiter name', function () {
    Livewire::test(CompletedOrderTable::class)
        ->set('search', 'Elena')
        ->assertSee('ORD-045')
        ->assertDontSee('ORD-039');
});

it('filters orders by search term on location', function () {
    Livewire::test(CompletedOrderTable::class)
        ->set('search', 'Room 312')
        ->assertSee('ORD-041')
        ->assertDontSee('ORD-045');
});

it('filters orders by payment method', function () {
    Livewire::test(CompletedOrderTable::class)
        ->set('paymentMethod', 'Cash')
        ->assertSee('ORD-044')
        ->assertDontSee('ORD-045');
});

it('filters orders by location multi-select', function () {
    Livewire::test(CompletedOrderTable::class)
        ->set('selectedLocations', ['Table B7'])
        ->assertSee('ORD-045')
        ->assertDontSee('ORD-044');
});

it('filters orders by waiter multi-select', function () {
    Livewire::test(CompletedOrderTable::class)
        ->set('selectedWaiters', ['Marco D.'])
        ->assertSee('ORD-043')
        ->assertSee('ORD-041')
        ->assertSee('ORD-039')
        ->assertDontSee('ORD-045');
});

it('filters orders by order type', function () {
    Livewire::test(CompletedOrderTable::class)
        ->set('orderType', 'room_service')
        ->assertSee('ORD-043')
        ->assertSee('ORD-041')
        ->assertSee('ORD-039')
        ->assertDontSee('ORD-045');
});

it('shows empty state when no orders match', function () {
    Livewire::test(CompletedOrderTable::class)
        ->set('search', 'nonexistent-order-xyz')
        ->assertSee('No completed orders found for the selected criteria');
});

it('sorts orders by column', function () {
    $component = Livewire::test(CompletedOrderTable::class)
        ->call('sortBy', 'id');

    expect($component->get('sortField'))->toBe('id');
    expect($component->get('sortDirection'))->toBe('asc');

    $component->call('sortBy', 'id');

    expect($component->get('sortDirection'))->toBe('desc');
});

it('changes per page count', function () {
    Livewire::test(CompletedOrderTable::class)
        ->call('setPerPage', 10)
        ->assertSet('perPage', 10);
});

it('opens and closes the receipt modal', function () {
    Livewire::test(CompletedOrderTable::class)
        ->call('viewReceipt', 'ORD-045')
        ->assertSet('showReceiptModal', true)
        ->assertSet('receiptOrderId', 'ORD-045')
        ->assertSee('Receipt')
        ->assertSee('Grilled Salmon')
        ->assertSee('Beef Tenderloin')
        ->call('closeReceipt')
        ->assertSet('showReceiptModal', false)
        ->assertSet('receiptOrderId', null);
});

it('shows receipt with itemized details', function () {
    Livewire::test(CompletedOrderTable::class)
        ->call('viewReceipt', 'ORD-045')
        ->assertSee('Grilled Salmon')
        ->assertSee('Beef Tenderloin')
        ->assertSee('Verdure Grigliate')
        ->assertSee('Vino Rosso della Casa')
        ->assertSee('Thank you for dining at Molveno Lake Resort');
});

it('toggles select all on current page', function () {
    $component = Livewire::test(CompletedOrderTable::class)
        ->set('selectAllOnPage', true)
        ->call('toggleSelectAll');

    expect($component->get('selectedOrders'))->not->toBeEmpty();
});

it('clears selection when select all is unchecked', function () {
    Livewire::test(CompletedOrderTable::class)
        ->set('selectAllOnPage', true)
        ->call('toggleSelectAll')
        ->set('selectAllOnPage', false)
        ->call('toggleSelectAll')
        ->assertSet('selectedOrders', []);
});

it('dispatches print-receipt event', function () {
    Livewire::test(CompletedOrderTable::class)
        ->call('printReceipt', 'ORD-045')
        ->assertDispatched('print-receipt', orderId: 'ORD-045');
});

it('dispatches batch-print event for selected orders', function () {
    Livewire::test(CompletedOrderTable::class)
        ->set('selectedOrders', ['ORD-045', 'ORD-044'])
        ->call('batchPrint')
        ->assertDispatched('batch-print', orderIds: ['ORD-045', 'ORD-044']);
});

it('does not dispatch batch-print when no orders are selected', function () {
    Livewire::test(CompletedOrderTable::class)
        ->call('batchPrint')
        ->assertNotDispatched('batch-print');
});

it('applies refunded row color class', function () {
    $component = Livewire::test(CompletedOrderTable::class);
    $refundedOrder = $component->instance()->allOrders->firstWhere('id', 'ORD-043');

    expect($component->instance()->rowClasses($refundedOrder))->toBe('bg-red-50');
});

it('applies stale row color class for old orders', function () {
    $component = Livewire::test(CompletedOrderTable::class);
    $staleOrder = $component->instance()->allOrders->firstWhere('id', 'ORD-041');

    expect($component->instance()->rowClasses($staleOrder))->toBe('bg-yellow-50');
});

it('returns empty row class for normal orders', function () {
    $component = Livewire::test(CompletedOrderTable::class);
    $normalOrder = $component->instance()->allOrders->firstWhere('id', 'ORD-045');

    expect($component->instance()->rowClasses($normalOrder))->toBe('');
});

it('applies high-value row color class for orders over 100', function () {
    $component = Livewire::test(CompletedOrderTable::class);
    $highValueOrder = $component->instance()->allOrders->firstWhere('id', 'ORD-044');

    expect($component->instance()->rowClasses($highValueOrder))->toBe('bg-green-50');
});

it('sets date range filter', function () {
    Livewire::test(CompletedOrderTable::class)
        ->call('setDateRange', 'this_week')
        ->assertSet('dateRange', 'this_week');
});

it('resets selection when filters change', function () {
    Livewire::test(CompletedOrderTable::class)
        ->set('selectedOrders', ['ORD-045'])
        ->set('search', 'something')
        ->assertSet('selectedOrders', []);
});

it('exports csv as streamed download', function () {
    $component = Livewire::test(CompletedOrderTable::class);
    $response = $component->instance()->exportCsv();

    expect($response->getStatusCode())->toBe(200);
    expect($response->headers->get('Content-Type'))->toBe('text/csv');
});
