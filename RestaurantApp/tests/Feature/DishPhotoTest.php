<?php

use App\Livewire\Dishes\DishSheet;
use App\Models\Dish;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('public');
    (new RoleSeeder)->run();
    $this->user = User::factory()->create();
    $this->user->assignRole('management');
});

it('stores a photo when creating a dish', function () {
    $photo = UploadedFile::fake()->create('dish.jpg', 100, 'image/jpeg');

    Livewire::actingAs($this->user)
        ->test(DishSheet::class)
        ->set('name', 'Test Dish')
        ->set('price', '12.50')
        ->set('photo', $photo)
        ->call('save')
        ->assertDispatched('dish-saved');

    $dish = Dish::where('name', 'Test Dish')->first();
    expect($dish->photo_path)->not->toBeNull();
    Storage::disk('public')->assertExists($dish->photo_path);
});

it('creates a dish without a photo', function () {
    Livewire::actingAs($this->user)
        ->test(DishSheet::class)
        ->set('name', 'No Photo Dish')
        ->set('price', '8.00')
        ->call('save')
        ->assertDispatched('dish-saved');

    $dish = Dish::where('name', 'No Photo Dish')->first();
    expect($dish->photo_path)->toBeNull();
});

it('replaces old photo when updating with a new one', function () {
    $oldPhoto = UploadedFile::fake()->create('old.jpg', 100, 'image/jpeg');
    $dish = Dish::factory()->create([
        'photo_path' => $oldPhoto->store('dishes', 'public'),
    ]);
    $oldPath = $dish->photo_path;

    $newPhoto = UploadedFile::fake()->create('new.jpg', 100, 'image/jpeg');

    Livewire::actingAs($this->user)
        ->test(DishSheet::class, ['dishId' => $dish->id])
        ->set('photo', $newPhoto)
        ->call('save')
        ->assertDispatched('dish-saved');

    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertExists($dish->fresh()->photo_path);
});

it('keeps existing photo when updating without a new one', function () {
    $photo = UploadedFile::fake()->create('keep.jpg', 100, 'image/jpeg');
    $dish = Dish::factory()->create([
        'photo_path' => $photo->store('dishes', 'public'),
    ]);
    $originalPath = $dish->photo_path;

    Livewire::actingAs($this->user)
        ->test(DishSheet::class, ['dishId' => $dish->id])
        ->set('name', 'Updated Name')
        ->call('save')
        ->assertDispatched('dish-saved');

    Storage::disk('public')->assertExists($originalPath);
    expect($dish->fresh()->photo_path)->toBe($originalPath);
});

it('deletes the photo file when a dish is deleted', function () {
    $photo = UploadedFile::fake()->create('todelete.jpg', 100, 'image/jpeg');
    $dish = Dish::factory()->create([
        'photo_path' => $photo->store('dishes', 'public'),
    ]);
    $path = $dish->photo_path;

    Livewire::actingAs($this->user)
        ->test(DishSheet::class, ['dishId' => $dish->id])
        ->call('deleteDish')
        ->assertDispatched('dish-deleted');

    Storage::disk('public')->assertMissing($path);
    $this->assertModelMissing($dish);
});

it('deletes a dish without a photo without errors', function () {
    $dish = Dish::factory()->create(['photo_path' => null]);

    Livewire::actingAs($this->user)
        ->test(DishSheet::class, ['dishId' => $dish->id])
        ->call('deleteDish')
        ->assertDispatched('dish-deleted');

    $this->assertModelMissing($dish);
});

it('validates photo must be an image', function () {
    Livewire::actingAs($this->user)
        ->test(DishSheet::class)
        ->set('name', 'Test Dish')
        ->set('price', '10.00')
        ->set('photo', UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'))
        ->call('save')
        ->assertHasErrors('photo');
});
