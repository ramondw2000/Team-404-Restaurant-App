<?php

use App\Models\Dish;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    (new RoleSeeder)->run();
    $this->user = User::factory()->create();
    $this->user->assignRole('management');
    $this->actingAs($this->user);
});

it('stores a photo when creating a dish', function () {
    $photo = UploadedFile::fake()->create('dish.jpg', 100, 'image/jpeg');

    $this->post(route('dishes.store'), [
        'name' => 'Test Dish',
        'price' => 12.50,
        'category' => 'Mains',
        'photo' => $photo,
    ])->assertRedirect(route('dishes'));

    $dish = Dish::where('name', 'Test Dish')->first();
    expect($dish->photo_path)->not->toBeNull();
    Storage::disk('public')->assertExists($dish->photo_path);
});

it('creates a dish without a photo', function () {
    $this->post(route('dishes.store'), [
        'name' => 'No Photo Dish',
        'price' => 8.00,
        'category' => 'Starters',
    ])->assertRedirect(route('dishes'));

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
    $this->post(route('dishes.update', $dish), [
        'name' => $dish->name,
        'price' => $dish->price,
        'category' => $dish->category,
        'photo' => $newPhoto,
    ])->assertRedirect(route('dishes'));

    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertExists($dish->fresh()->photo_path);
});

it('keeps existing photo when updating without a new one', function () {
    $photo = UploadedFile::fake()->create('keep.jpg', 100, 'image/jpeg');
    $dish = Dish::factory()->create([
        'photo_path' => $photo->store('dishes', 'public'),
    ]);
    $originalPath = $dish->photo_path;

    $this->post(route('dishes.update', $dish), [
        'name' => 'Updated Name',
        'price' => $dish->price,
        'category' => $dish->category,
    ])->assertRedirect(route('dishes'));

    Storage::disk('public')->assertExists($originalPath);
    expect($dish->fresh()->photo_path)->toBe($originalPath);
});

it('deletes the photo file when a dish is deleted', function () {
    $photo = UploadedFile::fake()->create('todelete.jpg', 100, 'image/jpeg');
    $dish = Dish::factory()->create([
        'photo_path' => $photo->store('dishes', 'public'),
    ]);
    $path = $dish->photo_path;

    $this->delete(route('dishes.destroy', $dish))->assertRedirect(route('dishes'));

    Storage::disk('public')->assertMissing($path);
    $this->assertModelMissing($dish);
});

it('deletes a dish without a photo without errors', function () {
    $dish = Dish::factory()->create(['photo_path' => null]);

    $this->delete(route('dishes.destroy', $dish))->assertRedirect(route('dishes'));

    $this->assertModelMissing($dish);
});

it('rejects non-image files for photo upload', function () {
    $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

    $this->post(route('dishes.store'), [
        'name' => 'Test Dish',
        'price' => 10.00,
        'category' => 'Mains',
        'photo' => $file,
    ])->assertSessionHasErrors('photo');
});

it('includes photo_path in the dishes view data', function () {
    $photo = UploadedFile::fake()->create('view.jpg', 100, 'image/jpeg');
    Dish::factory()->create([
        'photo_path' => $photo->store('dishes', 'public'),
    ]);

    $response = $this->get(route('dishes'));
    $response->assertOk();

    $dishes = $response->viewData('dishes');
    $withPhoto = collect($dishes)->first(fn ($d) => $d['photo_path'] !== null);
    expect($withPhoto)->not->toBeNull();
    expect($withPhoto['photo_path'])->toBeString()->not->toBeEmpty();
});
