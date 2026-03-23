<?php

use Illuminate\Support\Facades\Blade;

it('renders the button component with primary variant by default', function () {
    $html = Blade::render('<x-ui.button>Save</x-ui.button>');

    expect($html)
        ->toContain('Save')
        ->toContain('bg-molveno-blue-500')
        ->toContain('hover:bg-molveno-blue-700')
        ->toContain('text-white');
});

it('renders the button component with secondary variant', function () {
    $html = Blade::render('<x-ui.button variant="secondary">Cancel</x-ui.button>');

    expect($html)
        ->toContain('Cancel')
        ->toContain('border-gray-200')
        ->toContain('text-gray-700');
});

it('renders the button component with danger variant', function () {
    $html = Blade::render('<x-ui.button variant="danger">Delete</x-ui.button>');

    expect($html)
        ->toContain('Delete')
        ->toContain('bg-red-600');
});

it('renders the button component with sm size', function () {
    $html = Blade::render('<x-ui.button size="sm">Small</x-ui.button>');

    expect($html)
        ->toContain('px-3')
        ->toContain('py-1.5')
        ->toContain('text-xs');
});

it('renders the button component as a link when href is provided', function () {
    $html = Blade::render('<x-ui.button href="https://example.com">Link</x-ui.button>');

    expect($html)
        ->toContain('<a')
        ->toContain('href="https://example.com"')
        ->toContain('Link');
});

it('renders the button component with disabled state', function () {
    $html = Blade::render('<x-ui.button :disabled="true">Disabled</x-ui.button>');

    expect($html)->toContain('disabled');
});

it('renders the input component with default type', function () {
    $html = Blade::render('<x-ui.input name="email" placeholder="Enter email" />');

    expect($html)
        ->toContain('type="text"')
        ->toContain('name="email"')
        ->toContain('placeholder="Enter email"')
        ->toContain('border-gray-200');
});

it('renders the input component with error state', function () {
    $html = Blade::render('<x-ui.input name="email" :error="true" />');

    expect($html)
        ->toContain('border-red-500');
});

it('renders the input component as textarea', function () {
    $html = Blade::render('<x-ui.input type="textarea" name="desc" />');

    expect($html)
        ->toContain('<textarea')
        ->toContain('resize-none');
});

it('renders the input component as select', function () {
    $html = Blade::render('<x-ui.input type="select" name="cat"><option>A</option></x-ui.input>');

    expect($html)
        ->toContain('<select')
        ->toContain('<option>A</option>');
});

it('renders the input-group with label and input', function () {
    $html = Blade::render('<x-ui.input-group label="Full Name" name="name" placeholder="e.g. Sofia" />');

    expect($html)
        ->toContain('Full Name')
        ->toContain('<label')
        ->toContain('name="name"')
        ->toContain('placeholder="e.g. Sofia"');
});

it('renders the input-group with required asterisk', function () {
    $html = Blade::render('<x-ui.input-group label="Email" name="email" :required="true" />');

    expect($html)
        ->toContain('text-red-400')
        ->toContain('*');
});

it('renders the input-group with hint text', function () {
    $html = Blade::render('<x-ui.input-group label="Password" name="password" hint="Min 8 chars" />');

    expect($html)->toContain('Min 8 chars');
});

it('renders the form component with csrf token for POST', function () {
    $html = Blade::render('<x-ui.form action="/test">content</x-ui.form>');

    expect($html)
        ->toContain('method="POST"')
        ->toContain('action="/test"')
        ->toContain('_token')
        ->toContain('content');
});

it('renders the form component with method spoofing for PUT', function () {
    $html = Blade::render('<x-ui.form method="PUT" action="/test">content</x-ui.form>');

    expect($html)
        ->toContain('method="POST"')
        ->toContain('_method')
        ->toContain('PUT');
});

it('renders the form component without csrf for GET', function () {
    $html = Blade::render('<x-ui.form method="GET" action="/test">content</x-ui.form>');

    expect($html)
        ->toContain('method="GET"')
        ->not->toContain('_token');
});

it('renders the sheet component with Alpine.js data', function () {
    $html = Blade::render('<x-ui.sheet name="test-sheet" title="Test Title" subtitle="Test Sub">Body</x-ui.sheet>');

    expect($html)
        ->toContain('test-sheet')
        ->toContain('Test Title')
        ->toContain('Test Sub')
        ->toContain('Body')
        ->toContain('x-data')
        ->toContain('x-show');
});

it('renders the sheet component on the left side', function () {
    $html = Blade::render('<x-ui.sheet name="left-sheet" side="left">Body</x-ui.sheet>');

    expect($html)
        ->toContain('left-0')
        ->toContain('-translate-x-full');
});

it('renders the sheet component with footer slot', function () {
    $html = Blade::render('
        <x-ui.sheet name="footer-sheet" title="Test">
            Body
            <x-slot:footer>Footer Content</x-slot:footer>
        </x-ui.sheet>
    ');

    expect($html)->toContain('Footer Content');
});

it('renders the divider component horizontal by default', function () {
    $html = Blade::render('<x-ui.divider />');

    expect($html)
        ->toContain('border-t')
        ->toContain('border-gray-200');
});

it('renders the divider component with dashed style', function () {
    $html = Blade::render('<x-ui.divider :dashed="true" />');

    expect($html)->toContain('border-dashed');
});

it('renders the divider component vertically', function () {
    $html = Blade::render('<x-ui.divider orientation="vertical" />');

    expect($html)->toContain('bg-gray-200');
});

it('renders the card component with default padding', function () {
    $html = Blade::render('<x-ui.card>Card content</x-ui.card>');

    expect($html)
        ->toContain('Card content')
        ->toContain('rounded-xl')
        ->toContain('border-gray-200')
        ->toContain('shadow-sm')
        ->toContain('p-6');
});

it('renders the card component with no padding', function () {
    $html = Blade::render('<x-ui.card padding="none">Content</x-ui.card>');

    expect($html)
        ->toContain('Content')
        ->not->toContain('p-6');
});

it('renders the card component with colored header', function () {
    $html = Blade::render('
        <x-ui.card headerColor="bg-primary">
            <x-slot:header>Header Text</x-slot:header>
            Body
        </x-ui.card>
    ');

    expect($html)
        ->toContain('bg-primary')
        ->toContain('Header Text')
        ->toContain('Body');
});

it('renders the card component with footer', function () {
    $html = Blade::render('
        <x-ui.card>
            Body
            <x-slot:footer>Footer</x-slot:footer>
        </x-ui.card>
    ');

    expect($html)
        ->toContain('Footer')
        ->toContain('border-t')
        ->toContain('bg-gray-50');
});

it('renders the image component with src', function () {
    $html = Blade::render('<x-ui.image src="/img/test.jpg" alt="Test" />');

    expect($html)
        ->toContain('<img')
        ->toContain('src="/img/test.jpg"')
        ->toContain('alt="Test"')
        ->toContain('object-cover');
});

it('renders the image component with placeholder when src is null', function () {
    $html = Blade::render('<x-ui.image :src="null" />');

    expect($html)
        ->toContain('opacity-30')
        ->toContain('#309bcf')
        ->not->toContain('<img');
});

it('renders the image component with custom aspect ratio', function () {
    $html = Blade::render('<x-ui.image :src="null" aspect="16/9" />');

    expect($html)->toContain('aspect-ratio: 16/9');
});

it('renders the toast component with Alpine.js', function () {
    $html = Blade::render('<x-ui.toast />');

    expect($html)
        ->toContain('x-data')
        ->toContain('x-on:toast.window')
        ->toContain('fixed')
        ->toContain('bottom-6')
        ->toContain('right-6');
});

it('renders the badge component with neutral variant by default', function () {
    $html = Blade::render('<x-ui.badge>Default</x-ui.badge>');

    expect($html)
        ->toContain('Default')
        ->toContain('bg-gray-100')
        ->toContain('text-gray-600')
        ->toContain('rounded-full');
});

it('renders the badge component with success variant and dot', function () {
    $html = Blade::render('<x-ui.badge variant="success" :dot="true">Active</x-ui.badge>');

    expect($html)
        ->toContain('Active')
        ->toContain('bg-green-100')
        ->toContain('text-green-700')
        ->toContain('bg-green-500');
});

it('renders the badge component with sm size', function () {
    $html = Blade::render('<x-ui.badge size="sm">Small</x-ui.badge>');

    expect($html)
        ->toContain('px-2')
        ->toContain('text-[0.65rem]');
});

it('renders the modal component with Alpine.js', function () {
    $html = Blade::render('<x-ui.modal name="test-modal">Modal Body</x-ui.modal>');

    expect($html)
        ->toContain('test-modal')
        ->toContain('Modal Body')
        ->toContain('x-data')
        ->toContain('x-show');
});

it('renders the modal component with maxWidth sm', function () {
    $html = Blade::render('<x-ui.modal name="sm-modal" maxWidth="sm">Body</x-ui.modal>');

    expect($html)->toContain('sm:max-w-sm');
});

it('renders the table component', function () {
    $html = Blade::render('<x-ui.table><thead></thead></x-ui.table>');

    expect($html)
        ->toContain('<table')
        ->toContain('w-full')
        ->toContain('text-sm');
});

it('renders the th component with default alignment', function () {
    $html = Blade::render('<table><tr><x-ui.th>Header</x-ui.th></tr></table>');

    expect($html)
        ->toContain('<th')
        ->toContain('Header')
        ->toContain('text-left')
        ->toContain('uppercase')
        ->toContain('tracking-wide');
});

it('renders the th component with sortable and asc sort', function () {
    $html = Blade::render('<table><tr><x-ui.th :sortable="true" sorted="asc">Col</x-ui.th></tr></table>');

    expect($html)
        ->toContain('cursor-pointer')
        ->toContain('Col')
        ->toContain('<svg');
});

it('renders the td component with right alignment', function () {
    $html = Blade::render('<table><tr><x-ui.td align="right">Data</x-ui.td></tr></table>');

    expect($html)
        ->toContain('<td')
        ->toContain('Data')
        ->toContain('text-right');
});

it('renders the dropdown component with Alpine.js', function () {
    $html = Blade::render('
        <x-ui.dropdown>
            <x-slot:trigger><button>Open</button></x-slot:trigger>
            <x-slot:content><a>Item</a></x-slot:content>
        </x-ui.dropdown>
    ');

    expect($html)
        ->toContain('x-data')
        ->toContain('Open')
        ->toContain('Item')
        ->toContain('rounded-xl');
});

it('renders the dropdown-link component', function () {
    $html = Blade::render('<x-ui.dropdown-link href="/profile">Profile</x-ui.dropdown-link>');

    expect($html)
        ->toContain('<a')
        ->toContain('href="/profile"')
        ->toContain('Profile')
        ->toContain('hover:bg-gray-50');
});

it('renders the avatar component with initials', function () {
    $html = Blade::render('<x-ui.avatar name="Sofia Ricci" />');

    expect($html)
        ->toContain('SR')
        ->toContain('bg-molveno-blue-500')
        ->toContain('rounded-full')
        ->toContain('w-9')
        ->toContain('h-9');
});

it('renders the avatar component with lg size and custom color', function () {
    $html = Blade::render('<x-ui.avatar name="Marco D." size="lg" color="bg-amber-500" />');

    expect($html)
        ->toContain('MD')
        ->toContain('bg-amber-500')
        ->toContain('w-12')
        ->toContain('h-12');
});

it('renders the avatar component with single name', function () {
    $html = Blade::render('<x-ui.avatar name="Admin" />');

    expect($html)->toContain('A');
});

it('renders the empty-state component with default values', function () {
    $html = Blade::render('<x-ui.empty-state />');

    expect($html)
        ->toContain('No results found')
        ->toContain('<svg');
});

it('renders the empty-state component with custom title and description', function () {
    $html = Blade::render('<x-ui.empty-state title="No accounts" description="Create one to get started." />');

    expect($html)
        ->toContain('No accounts')
        ->toContain('Create one to get started.');
});

it('renders the empty-state component with action slot', function () {
    $html = Blade::render('
        <x-ui.empty-state title="Empty">
            <x-slot:action><button>Add New</button></x-slot:action>
        </x-ui.empty-state>
    ');

    expect($html)->toContain('Add New');
});

it('renders the page-header component with title and subtitle', function () {
    $html = Blade::render('<x-ui.page-header title="Accounts" subtitle="Manage staff" />');

    expect($html)
        ->toContain('Accounts')
        ->toContain('Manage staff')
        ->toContain('text-2xl')
        ->toContain('font-black');
});

it('renders the page-header component with actions slot', function () {
    $html = Blade::render('
        <x-ui.page-header title="Dishes">
            <x-slot:actions><button>Add</button></x-slot:actions>
        </x-ui.page-header>
    ');

    expect($html)->toContain('Add');
});

it('renders the tab-group component', function () {
    $html = Blade::render('<x-ui.tab-group><span>Tabs</span></x-ui.tab-group>');

    expect($html)
        ->toContain('Tabs')
        ->toContain('flex')
        ->toContain('gap-2');
});

it('renders the tab component as active', function () {
    $html = Blade::render('<x-ui.tab :active="true" :count="5" value="all">All</x-ui.tab>');

    expect($html)
        ->toContain('All')
        ->toContain('bg-molveno-blue-500')
        ->toContain('text-white')
        ->toContain('5')
        ->toContain('data-value="all"');
});

it('renders the tab component as inactive', function () {
    $html = Blade::render('<x-ui.tab :active="false">Inactive</x-ui.tab>');

    expect($html)
        ->toContain('Inactive')
        ->toContain('bg-white')
        ->toContain('border-gray-200')
        ->toContain('text-gray-600');
});

it('renders the search-input component with icon and input', function () {
    $html = Blade::render('<x-ui.search-input placeholder="Search dishes…" />');

    expect($html)
        ->toContain('<svg')
        ->toContain('placeholder="Search dishes…"')
        ->toContain('pl-10');
});
