<?php

use Illuminate\Support\Facades\Blade;

it('renders the ordermanagement styles component with CSS rules', function () {
    $html = Blade::render('<x-ordermanagement.styles />');

    expect($html)
        ->toContain('<style>')
        ->toContain('.filter-btn')
        ->toContain('.dish-card')
        ->toContain('.btn-add-dish')
        ->toContain('.qty-badge')
        ->toContain('.note-area')
        ->toContain('.qty-btn')
        ->toContain('#order-bar')
        ->toContain('.add-overlay')
        ->toContain('#review-screen')
        ->toContain('.scrollbar-hide');
});

it('renders the ordermanagement filter-bar component with dietary and free-from buttons', function () {
    $allergenConfig = [
        'gluten' => ['label' => 'Gluten', 'bg' => '#D97706', 'icon' => '<path/>'],
        'milk' => ['label' => 'Milk',   'bg' => '#0284C7', 'icon' => '<path/>'],
    ];

    $html = Blade::render(
        '<x-ordermanagement.filter-bar :allergenConfig="$allergenConfig" />',
        compact('allergenConfig')
    );

    expect($html)
        ->toContain('Dietary:')
        ->toContain('data-value="vegetarian"')
        ->toContain('data-value="vegan"')
        ->toContain('Free from:')
        ->toContain('data-value="gluten"')
        ->toContain('data-value="milk"')
        ->toContain('Gluten-free')
        ->toContain('Milk-free');
});

it('renders the ordermanagement table-picker component with server name and tables', function () {
    $tables = ['A1', 'B2', 'C3'];

    $html = Blade::render(
        '<x-ordermanagement.table-picker :tables="$tables" />',
        compact('tables')
    );

    expect($html)
        ->toContain('John Doe')
        ->toContain('sel-table')
        ->toContain('Select table')
        ->toContain('Table A1')
        ->toContain('Table B2')
        ->toContain('Table C3');
});

it('renders the ordermanagement dish-grid component as a slot wrapper', function () {
    $html = Blade::render('<x-ordermanagement.dish-grid><p>child</p></x-ordermanagement.dish-grid>');

    expect($html)
        ->toContain('id="dish-grid"')
        ->toContain('grid')
        ->toContain('<p>child</p>');
});

it('renders the ordermanagement scripts component with JS functions', function () {
    $dishes = [
        ['id' => 1, 'name' => 'Test Dish', 'price' => 10.00, 'cat' => 'Mains', 'desc' => 'A test', 'allergens' => [], 'dietary' => []],
    ];
    $allergenConfig = [
        'gluten' => ['label' => 'Gluten', 'bg' => '#D97706', 'icon' => '<path/>'],
    ];

    $html = Blade::render(
        '<x-ordermanagement.scripts :dishes="$dishes" :allergenConfig="$allergenConfig" />',
        compact('dishes', 'allergenConfig')
    );

    expect($html)
        ->toContain('<script>')
        ->toContain('const MENU')
        ->toContain('const ALLERGEN')
        ->toContain('setCategory')
        ->toContain('toggleMulti')
        ->toContain('applyFilters')
        ->toContain('resetFilters')
        ->toContain('addDish')
        ->toContain('confirmAddDish')
        ->toContain('removeDish')
        ->toContain('changeQty')
        ->toContain('updateOrderBar')
        ->toContain('openReview')
        ->toContain('closeReview')
        ->toContain('sendOrder');
});
