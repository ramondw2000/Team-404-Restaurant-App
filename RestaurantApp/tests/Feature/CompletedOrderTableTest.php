<?php

use App\Livewire\CompletedOrderTable;
use App\Models\Dish;
use App\Models\FloorPlanElement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(function () {
    // Seed roles and permissions
    (new RoleSeeder)->run();

    /** @var FloorPlanElement $element */
    $element = FloorPlanElement::factory()->create(['table_name' => 'Table A1']);
    $dish = Dish::factory()->create(['name' => 'Truffle Pasta', 'price' => 24.00]);

    $this->completedOrder = Order::factory()->completed()->create([
        'floor_plan_element_id' => $element->id,
    ]);
    OrderItem::factory()->served()->create([
        'order_id' => $this->completedOrder->id,
        'dish_id' => $dish->id,
        'quantity' => 2,
        'unit_price' => 24.00,
    ]);

    $this->expectedId = 'ORD-'.str_pad((string) $this->completedOrder->id, 3, '0', STR_PAD_LEFT);
});

it('renders the completed order table component', function () {
    Livewire::test(CompletedOrderTable::class)
        ->assertStatus(200)
        ->assertSee('Order Ledger')
        ->assertSee('All orders');
});

it('displays completed orders from the database', function () {
    Livewire::test(CompletedOrderTable::class)
        ->call('setDateRange', 'all')
        ->assertSee($this->expectedId);
});

it('shows empty state when no orders match the search', function () {
    Livewire::test(CompletedOrderTable::class)
        ->set('search', 'nonexistent-order-xyz')
        ->assertSee('No orders match the selected criteria.');
});

it('filters orders by search term on order id', function () {
    $element2 = FloorPlanElement::factory()->create(['table_name' => 'Table B9']);
    $order2 = Order::factory()->completed()->create(['floor_plan_element_id' => $element2->id]);
    $id2 = 'ORD-'.str_pad((string) $order2->id, 3, '0', STR_PAD_LEFT);

    Livewire::test(CompletedOrderTable::class)
        ->call('setDateRange', 'all')
        ->set('search', $this->expectedId)
        ->assertSee($this->expectedId)
        ->assertDontSee($id2);
});

it('filters orders by location multi-select', function () {
    $element2 = FloorPlanElement::factory()->create(['table_name' => 'Table Z99']);
    $order2 = Order::factory()->completed()->create(['floor_plan_element_id' => $element2->id]);
    $id2 = 'ORD-'.str_pad((string) $order2->id, 3, '0', STR_PAD_LEFT);

    Livewire::test(CompletedOrderTable::class)
        ->call('setDateRange', 'all')
        ->set('selectedLocations', ['Table A1'])
        ->assertSee($this->expectedId)
        ->assertDontSee($id2);
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
        ->call('setDateRange', 'all')
        ->call('viewReceipt', $this->expectedId)
        ->assertSet('showReceiptModal', true)
        ->assertSet('receiptOrderId', $this->expectedId)
        ->assertSee('Receipt')
        ->assertSee('Truffle Pasta')
        ->call('closeReceipt')
        ->assertSet('showReceiptModal', false)
        ->assertSet('receiptOrderId', null);
});

it('shows receipt with itemized dish details', function () {
    Livewire::test(CompletedOrderTable::class)
        ->call('setDateRange', 'all')
        ->call('viewReceipt', $this->expectedId)
        ->assertSee('Truffle Pasta')
        ->assertSee('Thank you for dining at Molveno Lake Resort');
});

it('toggles select all on current page', function () {
    $component = Livewire::test(CompletedOrderTable::class)
        ->call('setDateRange', 'all')
        ->set('selectAllOnPage', true)
        ->call('toggleSelectAll');

    expect($component->get('selectedOrders'))->not->toBeEmpty();
});

it('clears selection when select all is unchecked', function () {
    Livewire::test(CompletedOrderTable::class)
        ->call('setDateRange', 'all')
        ->set('selectAllOnPage', true)
        ->call('toggleSelectAll')
        ->set('selectAllOnPage', false)
        ->call('toggleSelectAll')
        ->assertSet('selectedOrders', []);
});

it('dispatches print-receipt event', function () {
    Livewire::test(CompletedOrderTable::class)
        ->call('printReceipt', $this->expectedId)
        ->assertDispatched('print-receipt', orderId: $this->expectedId);
});

it('dispatches batch-print event for selected orders', function () {
    Livewire::test(CompletedOrderTable::class)
        ->set('selectedOrders', [$this->expectedId])
        ->call('batchPrint')
        ->assertDispatched('batch-print', orderIds: [$this->expectedId]);
});

it('does not dispatch batch-print when no orders are selected', function () {
    Livewire::test(CompletedOrderTable::class)
        ->call('batchPrint')
        ->assertNotDispatched('batch-print');
});

it('applies stale row color class for orders older than 30 minutes', function () {
    $element = FloorPlanElement::factory()->create();
    $oldOrder = Order::factory()->completed()->create([
        'floor_plan_element_id' => $element->id,
        'updated_at' => now()->subHour(),
    ]);

    $component = Livewire::test(CompletedOrderTable::class)->call('setDateRange', 'all');
    $staleId = 'ORD-'.str_pad((string) $oldOrder->id, 3, '0', STR_PAD_LEFT);
    $order = $component->instance()->allOrders->firstWhere('id', $staleId);

    expect($component->instance()->rowClasses($order))->toBe('bg-yellow-50');
});

it('returns empty row class for a recently completed order', function () {
    $component = Livewire::test(CompletedOrderTable::class)->call('setDateRange', 'all');
    $order = $component->instance()->allOrders->firstWhere('id', $this->expectedId);

    expect($component->instance()->rowClasses($order))->toBe('');
});

it('sets date range filter', function () {
    Livewire::test(CompletedOrderTable::class)
        ->call('setDateRange', 'week')
        ->assertSet('dateRange', 'week');
});

it('resets selection when search filter changes', function () {
    Livewire::test(CompletedOrderTable::class)
        ->set('selectedOrders', [$this->expectedId])
        ->set('search', 'something')
        ->assertSet('selectedOrders', []);
});

it('exports csv as streamed download', function () {
    // Create user with management role that has Export Orders permission
    $user = User::factory()->create();
    $user->assignRole('management');

    $component = Livewire::actingAs($user)->test(CompletedOrderTable::class);
    $response = $component->instance()->exportCsv();

    expect($response->getStatusCode())->toBe(200);
    expect($response->headers->get('Content-Type'))->toBe('text/csv');
});

it('includes active unpaid restaurant orders in the ledger', function () {
    /** @var FloorPlanElement $element */
    $element = FloorPlanElement::factory()->create(['table_name' => 'Table U1']);
    $dish = Dish::factory()->create(['name' => 'Carbonara', 'price' => 18.00]);

    $unpaid = Order::factory()->active()->create([
        'floor_plan_element_id' => $element->id,
        'paid' => false,
    ]);
    OrderItem::factory()->pending()->create([
        'order_id' => $unpaid->id,
        'dish_id' => $dish->id,
        'quantity' => 1,
        'unit_price' => 18.00,
    ]);

    $unpaidId = 'ORD-'.str_pad((string) $unpaid->id, 3, '0', STR_PAD_LEFT);

    Livewire::test(CompletedOrderTable::class)
        ->call('setDateRange', 'all')
        ->assertSee($unpaidId)
        ->assertSee('Unpaid');
});

it('includes non-table bar orders in the ledger', function () {
    $dish = Dish::factory()->create(['name' => 'Negroni', 'price' => 9.00, 'is_bar_item' => true]);

    $barOrder = Order::factory()->bar()->active()->create([
        'paid' => true,
        'guest_type' => 'walk_in',
    ]);
    OrderItem::factory()->served()->create([
        'order_id' => $barOrder->id,
        'dish_id' => $dish->id,
        'quantity' => 1,
        'unit_price' => 9.00,
    ]);

    $barId = 'ORD-'.str_pad((string) $barOrder->id, 3, '0', STR_PAD_LEFT);

    Livewire::test(CompletedOrderTable::class)
        ->call('setDateRange', 'all')
        ->assertSee($barId)
        ->assertSee('Bar')
        ->assertSee('Walk-in');
});

it('includes bar orders linked to a table in the ledger', function () {
    /** @var FloorPlanElement $element */
    $element = FloorPlanElement::factory()->create(['table_name' => 'Bar Stool 3']);
    $dish = Dish::factory()->create(['name' => 'Aperol', 'price' => 8.50, 'is_bar_item' => true]);

    $barTableOrder = Order::factory()->active()->create([
        'origin' => 'bar',
        'floor_plan_element_id' => $element->id,
        'paid' => false,
    ]);
    OrderItem::factory()->pending()->create([
        'order_id' => $barTableOrder->id,
        'dish_id' => $dish->id,
        'quantity' => 2,
        'unit_price' => 8.50,
    ]);

    $id = 'ORD-'.str_pad((string) $barTableOrder->id, 3, '0', STR_PAD_LEFT);

    Livewire::test(CompletedOrderTable::class)
        ->call('setDateRange', 'all')
        ->assertSee($id)
        ->assertSee('Bar Stool 3')
        ->assertSee('Unpaid');
});

it('filters ledger by bar order type', function () {
    /** @var FloorPlanElement $element */
    $element = FloorPlanElement::factory()->create(['table_name' => 'Table R7']);
    $restaurantOrder = Order::factory()->completed()->create([
        'floor_plan_element_id' => $element->id,
    ]);
    $restaurantId = 'ORD-'.str_pad((string) $restaurantOrder->id, 3, '0', STR_PAD_LEFT);

    $bar = Order::factory()->bar()->active()->create(['paid' => false]);
    $barId = 'ORD-'.str_pad((string) $bar->id, 3, '0', STR_PAD_LEFT);

    Livewire::test(CompletedOrderTable::class)
        ->call('setDateRange', 'all')
        ->set('orderType', 'bar')
        ->assertSee($barId)
        ->assertDontSee($restaurantId);
});

it('excludes draft and cancelled orders from the ledger', function () {
    /** @var FloorPlanElement $element */
    $element = FloorPlanElement::factory()->create();

    $draft = Order::factory()->draft()->create(['floor_plan_element_id' => $element->id]);
    $cancelled = Order::factory()->cancelled()->create(['floor_plan_element_id' => $element->id]);

    $draftId = 'ORD-'.str_pad((string) $draft->id, 3, '0', STR_PAD_LEFT);
    $cancelledId = 'ORD-'.str_pad((string) $cancelled->id, 3, '0', STR_PAD_LEFT);

    Livewire::test(CompletedOrderTable::class)
        ->call('setDateRange', 'all')
        ->assertDontSee($draftId)
        ->assertDontSee($cancelledId);
});
