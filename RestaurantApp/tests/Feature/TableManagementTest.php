<?php

use App\Enums\TableStatus;
use App\Livewire\TableManagement;
use App\Models\FloorPlan;
use App\Models\FloorPlanElement;
use App\Models\Image;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;

beforeEach(function () {
    (new RoleSeeder)->run();
});

function tableManagementUser(): User
{
    $user = User::factory()->create();
    $user->assignRole('management');

    return $user;
}

// ── Rendering ────────────────────────────────────────────────

it('renders the table management component', function () {
    $user = tableManagementUser();

    Livewire::actingAs($user)
        ->test(TableManagement::class)
        ->assertStatus(200);
});

it('selects the first floor plan on mount', function () {
    $user = tableManagementUser();
    $plan = FloorPlan::factory()->create(['name' => 'Main Hall']);

    Livewire::actingAs($user)
        ->test(TableManagement::class)
        ->assertSet('activeFloorPlanId', $plan->id);
});

// ── Preset Elements ──────────────────────────────────────────

it('returns preset elements from config validated against filesystem', function () {
    $user = tableManagementUser();

    $component = Livewire::actingAs($user)->test(TableManagement::class);
    $presets = $component->get('presetElements');

    expect($presets)->toHaveKey('round');
    expect($presets)->toHaveKey('rectangular');
    expect($presets['round']['label'])->toBe('Round Table');
    expect($presets['round']['variants'])->toHaveKeys([2, 4, 6, 8, 10]);
});

// ── Floor Plan CRUD ──────────────────────────────────────────

it('can create a floor plan', function () {
    $user = tableManagementUser();

    Livewire::actingAs($user)
        ->test(TableManagement::class)
        ->set('newFloorPlanName', 'Terrace')
        ->set('newBackgroundImage', UploadedFile::fake()->create('bg.png', 100, 'image/png'))
        ->call('createFloorPlan')
        ->assertSet('showCreateFloorPlanModal', false);

    expect(FloorPlan::where('name', 'Terrace')->exists())->toBeTrue();
});

it('can rename a floor plan', function () {
    $user = tableManagementUser();
    $plan = FloorPlan::factory()->create(['name' => 'Old Name']);

    Livewire::actingAs($user)
        ->test(TableManagement::class)
        ->set('renameFloorPlanName', 'New Name')
        ->call('renameFloorPlan');

    expect($plan->fresh()->name)->toBe('New Name');
});

it('can delete a floor plan', function () {
    $user = tableManagementUser();
    $plan = FloorPlan::factory()->create();

    Livewire::actingAs($user)
        ->test(TableManagement::class)
        ->assertSet('activeFloorPlanId', $plan->id)
        ->call('deleteFloorPlan');

    expect(FloorPlan::withTrashed()->find($plan->id)->trashed())->toBeTrue();
});

// ── Element Placement ────────────────────────────────────────

it('can place a preset element on the canvas', function () {
    $user = tableManagementUser();
    FloorPlan::factory()->create();

    $component = Livewire::actingAs($user)
        ->test(TableManagement::class)
        ->call('enterEditMode')
        ->call('placeElement', 'round', 4, 25.0, 30.0, 8.0, 8.0);

    $elements = $component->get('elements');
    expect($elements)->toHaveCount(1);
    expect($elements[0]['shape'])->toBe('round');
    expect($elements[0]['seat_count'])->toBe(4);
    expect($elements[0]['table_name'])->toBe('Table 1');
    expect($elements[0]['status'])->toBe('Available');
});

it('auto-generates sequential table names', function () {
    $user = tableManagementUser();
    $plan = FloorPlan::factory()->create();
    FloorPlanElement::factory()->create([
        'floor_plan_id' => $plan->id,
        'table_name' => 'Table 3',
        'shape' => 'round',
        'seat_count' => 4,
    ]);

    $component = Livewire::actingAs($user)
        ->test(TableManagement::class)
        ->call('enterEditMode')
        ->call('placeElement', 'round', 4, 10.0, 10.0, 8.0, 8.0);

    $newElements = $component->get('pendingNewElements');
    expect($newElements[0]['table_name'])->toBe('Table 4');
});

// ── Element Properties ───────────────────────────────────────

it('can update element properties', function () {
    $user = tableManagementUser();
    $plan = FloorPlan::factory()->create();
    $element = FloorPlanElement::factory()->round(4)->create([
        'floor_plan_id' => $plan->id,
        'table_name' => 'Table 1',
        'width' => 8.0,
        'height' => 8.0,
    ]);

    Livewire::actingAs($user)
        ->test(TableManagement::class)
        ->call('enterEditMode')
        ->call('selectElement', $element->id)
        ->call('updateElementProperties', $element->id, 'VIP Table', 4, 'Reserved');

    $pending = Livewire::actingAs($user)
        ->test(TableManagement::class)
        ->get('pendingChanges');

    // Properties were applied as pending changes, not yet saved
    // We test the full flow instead:
    $component = Livewire::actingAs($user)
        ->test(TableManagement::class)
        ->call('enterEditMode')
        ->call('updateElementProperties', $element->id, 'VIP Table', 4, 'Reserved')
        ->call('saveChanges');

    expect($element->fresh()->table_name)->toBe('VIP Table');
    expect($element->fresh()->status)->toBe(TableStatus::Reserved);
});

it('proportionally scales element when seat count changes', function () {
    $user = tableManagementUser();
    $plan = FloorPlan::factory()->create();
    // Round 4-seat default: 8x8, Round 8-seat default: 12x12
    // Scale factor: 12/8 = 1.5
    $element = FloorPlanElement::factory()->create([
        'floor_plan_id' => $plan->id,
        'shape' => 'round',
        'seat_count' => 4,
        'width' => 8.0,
        'height' => 8.0,
        'table_name' => 'Table 1',
    ]);

    $component = Livewire::actingAs($user)
        ->test(TableManagement::class)
        ->call('enterEditMode')
        ->call('updateElementProperties', $element->id, 'Table 1', 8, 'Available')
        ->call('saveChanges');

    $element->refresh();
    // 8 * (12/8) = 12
    expect($element->width)->toBe(12.0);
    expect($element->height)->toBe(12.0);
    expect($element->seat_count)->toBe(8);
});

// ── Save & Discard ───────────────────────────────────────────

it('can save pending changes', function () {
    $user = tableManagementUser();
    $plan = FloorPlan::factory()->create();

    Livewire::actingAs($user)
        ->test(TableManagement::class)
        ->call('enterEditMode')
        ->call('placeElement', 'rectangular', 6, 20.0, 20.0, 12.0, 6.0)
        ->assertSet('hasUnsavedChanges', true)
        ->call('saveChanges')
        ->assertSet('hasUnsavedChanges', false)
        ->assertSet('editMode', false);

    expect(FloorPlanElement::where('floor_plan_id', $plan->id)->count())->toBe(1);
    $saved = FloorPlanElement::where('floor_plan_id', $plan->id)->first();
    expect($saved->shape)->toBe('rectangular');
    expect($saved->seat_count)->toBe(6);
});

it('can discard pending changes', function () {
    $user = tableManagementUser();
    FloorPlan::factory()->create();

    Livewire::actingAs($user)
        ->test(TableManagement::class)
        ->call('enterEditMode')
        ->call('placeElement', 'round', 4, 10.0, 10.0, 8.0, 8.0)
        ->assertSet('hasUnsavedChanges', true)
        ->call('discardChanges')
        ->assertSet('hasUnsavedChanges', false)
        ->assertSet('editMode', false);

    expect(FloorPlanElement::count())->toBe(0);
});

// ── Delete Element ───────────────────────────────────────────

it('can delete an existing element', function () {
    $user = tableManagementUser();
    $plan = FloorPlan::factory()->create();
    $element = FloorPlanElement::factory()->create([
        'floor_plan_id' => $plan->id,
        'shape' => 'round',
        'seat_count' => 4,
    ]);

    Livewire::actingAs($user)
        ->test(TableManagement::class)
        ->call('enterEditMode')
        ->call('deleteElement', $element->id)
        ->call('saveChanges');

    expect(FloorPlanElement::withTrashed()->find($element->id)->trashed())->toBeTrue();
});

// ── Copy / Paste ─────────────────────────────────────────────

it('can copy and paste an element', function () {
    $user = tableManagementUser();
    $plan = FloorPlan::factory()->create();
    $element = FloorPlanElement::factory()->create([
        'floor_plan_id' => $plan->id,
        'shape' => 'round',
        'seat_count' => 6,
        'table_name' => 'Table 1',
        'x' => 10.0,
        'y' => 10.0,
    ]);

    $component = Livewire::actingAs($user)
        ->test(TableManagement::class)
        ->call('enterEditMode')
        ->call('copyElement', $element->id)
        ->call('pasteElement');

    $newElements = $component->get('pendingNewElements');
    expect($newElements)->toHaveCount(1);
    expect($newElements[0]['shape'])->toBe('round');
    expect($newElements[0]['seat_count'])->toBe(6);
    expect($newElements[0]['x'])->toBe(12.0); // 10 + 2 offset
});

// ── View Mode Status Update ──────────────────────────────────

it('can update table status immediately in view mode', function () {
    $user = tableManagementUser();
    $plan = FloorPlan::factory()->create();
    $element = FloorPlanElement::factory()->available()->create([
        'floor_plan_id' => $plan->id,
        'shape' => 'round',
        'seat_count' => 4,
    ]);

    Livewire::actingAs($user)
        ->test(TableManagement::class)
        ->call('updateTableStatus', $element->id, 'Occupied');

    expect($element->fresh()->status)->toBe(TableStatus::Occupied);
});

// ── Status Summary ───────────────────────────────────────────

it('computes correct status summary counts', function () {
    $user = tableManagementUser();
    $plan = FloorPlan::factory()->create();

    FloorPlanElement::factory()->available()->count(3)->create([
        'floor_plan_id' => $plan->id,
        'shape' => 'round',
        'seat_count' => 4,
    ]);
    FloorPlanElement::factory()->reserved()->count(2)->create([
        'floor_plan_id' => $plan->id,
        'shape' => 'rectangular',
        'seat_count' => 6,
    ]);
    FloorPlanElement::factory()->occupied()->create([
        'floor_plan_id' => $plan->id,
        'shape' => 'round',
        'seat_count' => 8,
    ]);

    $component = Livewire::actingAs($user)->test(TableManagement::class);
    $summary = $component->get('statusSummary');

    expect($summary['Available'])->toBe(3);
    expect($summary['Reserved'])->toBe(2);
    expect($summary['Occupied'])->toBe(1);
});

// ── Z-Order ──────────────────────────────────────────────────

it('can bring element to front', function () {
    $user = tableManagementUser();
    $plan = FloorPlan::factory()->create();
    $el1 = FloorPlanElement::factory()->create([
        'floor_plan_id' => $plan->id,
        'z_index' => 1,
        'shape' => 'round',
        'seat_count' => 4,
    ]);
    $el2 = FloorPlanElement::factory()->create([
        'floor_plan_id' => $plan->id,
        'z_index' => 5,
        'shape' => 'round',
        'seat_count' => 4,
    ]);

    Livewire::actingAs($user)
        ->test(TableManagement::class)
        ->call('enterEditMode')
        ->call('bringToFront', $el1->id)
        ->call('saveChanges');

    expect($el1->fresh()->z_index)->toBeGreaterThan($el2->fresh()->z_index);
});

// ── Snap Toggle ──────────────────────────────────────────────

it('can toggle snap enabled state', function () {
    $user = tableManagementUser();

    Livewire::actingAs($user)
        ->test(TableManagement::class)
        ->assertSet('snapEnabled', true)
        ->call('toggleSnap')
        ->assertSet('snapEnabled', false)
        ->call('toggleSnap')
        ->assertSet('snapEnabled', true);
});

// ── Model: FloorPlanElement image_path accessor ──────────────

it('resolves image path from shape and seat count', function () {
    $element = FloorPlanElement::factory()->create([
        'shape' => 'round',
        'seat_count' => 4,
    ]);

    expect($element->image_path)->toBe('/elements/round/4.svg');
});

it('returns null image path for non-existent variant', function () {
    $element = FloorPlanElement::factory()->create([
        'shape' => 'nonexistent',
        'seat_count' => 99,
    ]);

    expect($element->image_path)->toBeNull();
});

// ── Model: Image (no crop fields) ────────────────────────────

it('creates an image without crop fields', function () {
    $image = Image::factory()->create();

    expect($image->filename)->not->toBeEmpty();
    expect($image->path)->not->toBeEmpty();

    // Crop columns should not exist
    expect(array_key_exists('crop_x', $image->getAttributes()))->toBeFalse();
});
