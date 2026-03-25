<?php

use Illuminate\Support\Facades\Blade;

it('renders the orders scripts component with JS functions', function () {
    $html = Blade::render('<x-orders.scripts />');

    expect($html)
        ->toContain('<script>')
        ->toContain('switchTab')
        ->toContain('handleActionClick')
        ->toContain('markDishReady')
        ->toContain('completeOrder')
        ->toContain('updateOrderSendState')
        ->toContain('setTabAppearance')
        ->toContain('updateDishVisualState')
        ->toContain('setMarkReadyAppearance')
        ->toContain('setStatusDotAppearance')
        ->toContain('setSendButtonState')
        ->toContain('syncCardVisualState')
        ->toContain('hideOrderActions')
        ->toContain('showOrderActions')
        ->toContain('parseClasses');
});

it('renders the orders status-summary component with counts', function () {
    $html = Blade::render(
        '<x-orders.status-summary :totalPending="$totalPending" :totalReady="$totalReady" :countCompleted="$countCompleted" />',
        ['totalPending' => 5, 'totalReady' => 2, 'countCompleted' => 3]
    );

    expect($html)
        ->toContain('5 preparing')
        ->toContain('2 ready')
        ->toContain('3 done');
});

it('renders the orders filter-tabs component with all tab options', function () {
    $html = Blade::render(
        '<x-orders.filter-tabs :orderCount="$orderCount" :countActive="$countActive" :countCompleted="$countCompleted" />',
        ['orderCount' => 8, 'countActive' => 5, 'countCompleted' => 3]
    );

    expect($html)
        ->toContain('All')
        ->toContain('Active')
        ->toContain('Completed')
        ->toContain('Restaurant')
        ->toContain('Room Service')
        ->toContain('data-tab="all"')
        ->toContain('data-tab="active"')
        ->toContain('data-tab="completed"')
        ->toContain('data-tab="restaurant"')
        ->toContain('data-tab="room_service"')
        ->toContain('switchTab(this)');
});

it('renders the orders order-grid component as a slot wrapper', function () {
    $html = Blade::render('<x-orders.order-grid><p>child</p></x-orders.order-grid>');

    expect($html)
        ->toContain('id="order-list"')
        ->toContain('grid')
        ->toContain('<p>child</p>');
});
