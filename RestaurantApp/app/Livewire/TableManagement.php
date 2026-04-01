<?php

namespace App\Livewire;

use App\Enums\TableStatus;
use App\Models\FloorPlan;
use App\Models\FloorPlanElement;
use App\Models\Image;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('Table Management')]
class TableManagement extends Component
{
    use WithFileUploads;

    // Active floor plan state
    public ?int $activeFloorPlanId = null;

    // Edit mode state
    public bool $editMode = false;

    public bool $hasUnsavedChanges = false;

    public bool $showDiscardConfirm = false;

    // Selected element in edit mode
    public int|string|null $selectedElementId = null;

    // Modals
    public bool $showCreateFloorPlanModal = false;

    public bool $showRenameModal = false;

    // Table detail sheet (view mode)
    public bool $showTableSheet = false;

    public ?int $tableSheetElementId = null;

    // Create floor plan form
    public string $newFloorPlanName = '';

    public $newBackgroundImage = null;

    // Rename floor plan form
    public string $renameFloorPlanName = '';

    // Image upload for element library
    public $newElementImage = null;

    // Background image replacement
    public $replacementBackgroundImage = null;

    // Crop tool modal
    public bool $showCropModal = false;

    public ?int $cropEditImageId = null;

    // Clipboard for copy/paste
    /** @var array<string, mixed> */
    public array $clipboard = [];

    // Pending changes: keyed by element ID or 'new_N' for new elements
    /** @var array<string, array<string, mixed>> */
    public array $pendingChanges = [];

    /** @var int[] */
    public array $pendingDeletes = [];

    /** @var array<int, array<string, mixed>> */
    public array $pendingNewElements = [];

    public function mount(): void
    {
        $firstPlan = FloorPlan::query()->oldest()->first();
        if ($firstPlan) {
            $this->activeFloorPlanId = $firstPlan->id;
        }
    }

    #[Computed]
    public function floorPlans(): Collection
    {
        return FloorPlan::query()->oldest()->get();
    }

    #[Computed]
    public function activeFloorPlan(): ?FloorPlan
    {
        if (! $this->activeFloorPlanId) {
            return null;
        }

        return FloorPlan::with(['backgroundImage', 'elements.image'])->find($this->activeFloorPlanId);
    }

    #[Computed]
    public function elements(): array
    {
        if (! $this->activeFloorPlan) {
            return [];
        }

        $elements = [];

        foreach ($this->activeFloorPlan->elements as $element) {
            if (in_array($element->id, $this->pendingDeletes)) {
                continue;
            }

            $data = [
                'id' => $element->id,
                'image_id' => $element->image_id,
                'image_url' => $element->image->url(),
                'crop_x' => $element->image->crop_x,
                'crop_y' => $element->image->crop_y,
                'crop_w' => $element->image->crop_w,
                'crop_h' => $element->image->crop_h,
                'x' => $element->x,
                'y' => $element->y,
                'width' => $element->width,
                'height' => $element->height,
                'rotation' => $element->rotation,
                'z_index' => $element->z_index,
                'is_table' => $element->is_table,
                'table_name' => $element->table_name,
                'seat_count' => $element->seat_count,
                'status' => $element->status?->value,
            ];

            // Apply any pending changes
            if (isset($this->pendingChanges[$element->id])) {
                $data = array_merge($data, $this->pendingChanges[$element->id]);
            }

            $elements[] = $data;
        }

        // Add newly placed elements (pending new)
        foreach ($this->pendingNewElements as $newElement) {
            $elements[] = $newElement;
        }

        // Sort by z_index
        usort($elements, fn ($a, $b) => $a['z_index'] <=> $b['z_index']);

        return $elements;
    }

    #[Computed]
    public function imageLibrary(): Collection
    {
        return Image::query()->latest()->get();
    }

    #[Computed]
    public function selectedElement(): ?array
    {
        if (! $this->selectedElementId) {
            return null;
        }

        foreach ($this->elements as $element) {
            // Use loose comparison so int DB ids and string pending ids both match.
            if ($element['id'] == $this->selectedElementId) {
                return $element;
            }
        }

        return null;
    }

    #[Computed]
    public function tableSheetElement(): ?array
    {
        if (! $this->tableSheetElementId) {
            return null;
        }

        foreach ($this->elements as $element) {
            if ($element['id'] === $this->tableSheetElementId) {
                return $element;
            }
        }

        return null;
    }

    #[Computed]
    public function statusSummary(): array
    {
        $counts = [
            'Available' => 0,
            'Reserved' => 0,
            'Occupied' => 0,
        ];

        foreach ($this->elements as $element) {
            if ($element['is_table'] && $element['status']) {
                $counts[$element['status']] = ($counts[$element['status']] ?? 0) + 1;
            }
        }

        return $counts;
    }

    #[Computed]
    public function tableStatuses(): array
    {
        return array_map(fn (TableStatus $s) => $s->value, TableStatus::cases());
    }

    public function switchFloorPlan(int $floorPlanId): void
    {
        if ($this->hasUnsavedChanges) {
            $this->showDiscardConfirm = true;

            return;
        }

        $this->setActiveFloorPlan($floorPlanId);
    }

    public function setActiveFloorPlan(int $floorPlanId): void
    {
        $this->activeFloorPlanId = $floorPlanId;
        $this->selectedElementId = null;
        $this->pendingChanges = [];
        $this->pendingDeletes = [];
        $this->pendingNewElements = [];
        $this->hasUnsavedChanges = false;
        $this->showDiscardConfirm = false;
        $this->unsetComputed();
    }

    public function openCreateFloorPlanModal(): void
    {
        $this->newFloorPlanName = '';
        $this->newBackgroundImage = null;
        $this->showCreateFloorPlanModal = true;
    }

    public function createFloorPlan(): void
    {
        $this->validate([
            'newFloorPlanName' => ['required', 'string', 'max:255'],
            'newBackgroundImage' => ['required', 'file', 'mimes:png,jpg,jpeg,webp,svg', 'max:51200'],
        ], [
            'newFloorPlanName.required' => 'A floor plan name is required.',
            'newBackgroundImage.required' => 'A background image is required.',
        ]);

        $image = $this->storeUploadedImage($this->newBackgroundImage);

        $floorPlan = FloorPlan::create([
            'name' => $this->newFloorPlanName,
            'background_image_id' => $image->id,
        ]);

        $this->showCreateFloorPlanModal = false;
        $this->newFloorPlanName = '';
        $this->newBackgroundImage = null;
        $this->unsetComputed();

        $this->setActiveFloorPlan($floorPlan->id);
        $this->editMode = true;
    }

    public function openRenameModal(): void
    {
        if (! $this->activeFloorPlan) {
            return;
        }

        $this->renameFloorPlanName = $this->activeFloorPlan->name;
        $this->showRenameModal = true;
    }

    public function renameFloorPlan(): void
    {
        $this->validate([
            'renameFloorPlanName' => ['required', 'string', 'max:255'],
        ]);

        $this->activeFloorPlan?->update(['name' => $this->renameFloorPlanName]);
        $this->showRenameModal = false;
        $this->unsetComputed();
    }

    public function deleteFloorPlan(): void
    {
        if (! $this->activeFloorPlan) {
            return;
        }

        // Soft-delete all elements first
        $this->activeFloorPlan->elements()->delete();
        $this->activeFloorPlan->delete();

        $this->editMode = false;
        $this->hasUnsavedChanges = false;
        $this->pendingChanges = [];
        $this->pendingDeletes = [];
        $this->pendingNewElements = [];
        $this->selectedElementId = null;
        $this->unsetComputed();

        $firstPlan = FloorPlan::query()->oldest()->first();
        $this->activeFloorPlanId = $firstPlan?->id;
    }

    public function replaceBackgroundImage(): void
    {
        $this->validate([
            'replacementBackgroundImage' => ['required', 'file', 'mimes:png,jpg,jpeg,webp,svg', 'max:51200'],
        ]);

        $image = $this->storeUploadedImage($this->replacementBackgroundImage);

        $this->activeFloorPlan?->update(['background_image_id' => $image->id]);
        $this->replacementBackgroundImage = null;
        $this->unsetComputed();
    }

    public function enterEditMode(): void
    {
        $this->editMode = true;
        $this->selectedElementId = null;
    }

    public function exitEditMode(): void
    {
        if ($this->hasUnsavedChanges) {
            $this->showDiscardConfirm = true;

            return;
        }

        $this->editMode = false;
        $this->selectedElementId = null;
    }

    public function saveChanges(): void
    {
        if (! $this->activeFloorPlanId) {
            return;
        }

        // Apply pending changes to DB elements
        foreach ($this->pendingChanges as $elementId => $changes) {
            $element = FloorPlanElement::find($elementId);
            if (! $element) {
                continue;
            }

            $updateData = array_intersect_key($changes, array_flip([
                'x', 'y', 'width', 'height', 'rotation', 'z_index',
                'is_table', 'table_name', 'seat_count', 'status',
            ]));

            // Convert status string to enum value for DB storage
            if (isset($updateData['status']) && $updateData['status'] !== null) {
                $updateData['status'] = $updateData['status'];
            }

            if (isset($updateData['is_table']) && ! $updateData['is_table']) {
                $updateData['table_name'] = null;
                $updateData['seat_count'] = null;
                $updateData['status'] = null;
            }

            $element->update($updateData);
        }

        // Delete pending elements
        foreach ($this->pendingDeletes as $elementId) {
            FloorPlanElement::find($elementId)?->delete();
        }

        // Create new elements
        $maxZIndex = FloorPlanElement::where('floor_plan_id', $this->activeFloorPlanId)->max('z_index') ?? 0;
        foreach ($this->pendingNewElements as $newElementData) {
            $maxZIndex++;
            FloorPlanElement::create([
                'floor_plan_id' => $this->activeFloorPlanId,
                'image_id' => $newElementData['image_id'],
                'x' => $newElementData['x'],
                'y' => $newElementData['y'],
                'width' => $newElementData['width'],
                'height' => $newElementData['height'],
                'rotation' => $newElementData['rotation'] ?? 0,
                'z_index' => $maxZIndex,
                'is_table' => $newElementData['is_table'] ?? false,
                'table_name' => $newElementData['table_name'] ?? null,
                'seat_count' => $newElementData['seat_count'] ?? null,
                'status' => $newElementData['status'] ?? null,
            ]);
        }

        $this->pendingChanges = [];
        $this->pendingDeletes = [];
        $this->pendingNewElements = [];
        $this->hasUnsavedChanges = false;
        $this->showDiscardConfirm = false;
        $this->editMode = false;
        $this->selectedElementId = null;
        $this->unsetComputed();
    }

    public function discardChanges(): void
    {
        $this->pendingChanges = [];
        $this->pendingDeletes = [];
        $this->pendingNewElements = [];
        $this->hasUnsavedChanges = false;
        $this->showDiscardConfirm = false;
        $this->editMode = false;
        $this->selectedElementId = null;
        $this->unsetComputed();
    }

    public function uploadElementImage(float $cropX = 0.0, float $cropY = 0.0, float $cropW = 100.0, float $cropH = 100.0): void
    {
        $this->validate([
            'newElementImage' => ['required', 'file', 'mimes:png,jpg,jpeg,webp,svg', 'max:51200'],
        ]);

        $this->storeUploadedImage($this->newElementImage, $cropX, $cropY, $cropW, $cropH);
        $this->newElementImage = null;
        $this->showCropModal = false;
        $this->unsetComputed();
    }

    public function openNewElementCropModal(): void
    {
        $this->showCropModal = true;
    }

    public function openCropEditor(int $imageId): void
    {
        $this->cropEditImageId = $imageId;
        $this->showCropModal = true;
    }

    public function saveCrop(int $imageId, float $cropX, float $cropY, float $cropW, float $cropH): void
    {
        Image::findOrFail($imageId)->update([
            'crop_x' => max(0, min(99, $cropX)),
            'crop_y' => max(0, min(99, $cropY)),
            'crop_w' => max(1, min(100 - $cropX, $cropW)),
            'crop_h' => max(1, min(100 - $cropY, $cropH)),
        ]);

        $this->showCropModal = false;
        $this->cropEditImageId = null;
        $this->unsetComputed();
    }

    public function closeCropModal(): void
    {
        $this->showCropModal = false;
        $this->cropEditImageId = null;
        $this->newElementImage = null;
    }

    public function deleteImageFromLibrary(int $imageId): void
    {
        $image = Image::findOrFail($imageId);

        if ($image->isInUse()) {
            $this->dispatch('notify', type: 'error', message: 'This image is in use and cannot be deleted.');

            return;
        }

        $image->floorPlanElements()->withTrashed()->forceDelete();
        $image->floorPlans()->withTrashed()->forceDelete();

        Storage::delete($image->path);
        $image->delete();
        $this->unsetComputed();
    }

    public function placeElement(int $imageId, float $x, float $y, float $width = 10.0, float $height = 10.0): void
    {
        $image = Image::findOrFail($imageId);

        $this->pendingNewElements[] = [
            'id' => 'new_'.count($this->pendingNewElements),
            'image_id' => $imageId,
            'image_url' => $image->url(),
            'crop_x' => $image->crop_x,
            'crop_y' => $image->crop_y,
            'crop_w' => $image->crop_w,
            'crop_h' => $image->crop_h,
            'x' => $x,
            'y' => $y,
            'width' => $width,
            'height' => $height,
            'rotation' => 0.0,
            'z_index' => 999 + count($this->pendingNewElements),
            'is_table' => false,
            'table_name' => null,
            'seat_count' => null,
            'status' => null,
        ];

        $this->hasUnsavedChanges = true;
        $this->unsetComputed();
    }

    public function selectElement(int|string $elementId): void
    {
        $this->selectedElementId = is_numeric($elementId) ? (int) $elementId : $elementId;
    }

    public function deselectElement(): void
    {
        $this->selectedElementId = null;
    }

    #[On('element-transformed')]
    public function updateElementTransform(int|string $elementId, float $x, float $y, float $width, float $height, float $rotation): void
    {
        $transformData = compact('x', 'y', 'width', 'height', 'rotation');

        if (is_numeric($elementId)) {
            $id = (int) $elementId;
            $existing = $this->pendingChanges[$id] ?? [];
            $this->pendingChanges[$id] = array_merge($existing, $transformData);
        } else {
            // New element
            foreach ($this->pendingNewElements as &$newElement) {
                if ($newElement['id'] === $elementId) {
                    $newElement = array_merge($newElement, $transformData);
                    break;
                }
            }
            unset($newElement);
        }

        $this->hasUnsavedChanges = true;
        $this->unsetComputed();
    }

    public function bringToFront(int|string $elementId): void
    {
        $id = (int) $elementId;
        $maxZIndex = 0;
        foreach ($this->elements as $element) {
            if ($element['z_index'] > $maxZIndex) {
                $maxZIndex = $element['z_index'];
            }
        }

        $newZIndex = $maxZIndex + 1;
        $existing = $this->pendingChanges[$id] ?? [];
        $this->pendingChanges[$id] = array_merge($existing, ['z_index' => $newZIndex]);
        $this->dispatch('element-zindex-updated', id: $id, zIndex: $newZIndex);
        $this->hasUnsavedChanges = true;
        $this->unsetComputed();
    }

    public function sendToBack(int|string $elementId): void
    {
        $id = (int) $elementId;
        $minZIndex = 0;
        foreach ($this->elements as $element) {
            if ($element['z_index'] < $minZIndex) {
                $minZIndex = $element['z_index'];
            }
        }

        $newZIndex = max(1, $minZIndex - 1);
        $existing = $this->pendingChanges[$id] ?? [];
        $this->pendingChanges[$id] = array_merge($existing, ['z_index' => $newZIndex]);
        $this->dispatch('element-zindex-updated', id: $id, zIndex: $newZIndex);
        $this->hasUnsavedChanges = true;
        $this->unsetComputed();
    }

    public function deleteElement(int|string|null $elementId): void
    {
        if ($elementId === null) {
            return;
        }

        if (is_numeric($elementId)) {
            $this->pendingDeletes[] = (int) $elementId;
            unset($this->pendingChanges[(int) $elementId]);
        } else {
            $this->pendingNewElements = array_values(
                array_filter($this->pendingNewElements, fn ($el) => $el['id'] !== $elementId)
            );
        }

        if ($this->selectedElementId === (int) $elementId) {
            $this->selectedElementId = null;
        }

        $this->hasUnsavedChanges = true;
        $this->unsetComputed();
    }

    public function copyElement(int|string $elementId): void
    {
        foreach ($this->elements as $element) {
            if ($element['id'] == $elementId) {
                $this->clipboard = $element;

                return;
            }
        }
    }

    public function pasteElement(): void
    {
        if (empty($this->clipboard)) {
            return;
        }

        $offset = 2.0;
        $newX = min(90, ($this->clipboard['x'] ?? 0) + $offset);
        $newY = min(90, ($this->clipboard['y'] ?? 0) + $offset);

        $this->pendingNewElements[] = [
            'id' => 'new_'.count($this->pendingNewElements),
            'image_id' => $this->clipboard['image_id'],
            'image_url' => $this->clipboard['image_url'],
            'crop_x' => $this->clipboard['crop_x'] ?? 0,
            'crop_y' => $this->clipboard['crop_y'] ?? 0,
            'crop_w' => $this->clipboard['crop_w'] ?? 100,
            'crop_h' => $this->clipboard['crop_h'] ?? 100,
            'x' => $newX,
            'y' => $newY,
            'width' => $this->clipboard['width'],
            'height' => $this->clipboard['height'],
            'rotation' => $this->clipboard['rotation'],
            'z_index' => 999 + count($this->pendingNewElements),
            'is_table' => $this->clipboard['is_table'],
            'table_name' => $this->clipboard['table_name'],
            'seat_count' => $this->clipboard['seat_count'],
            'status' => $this->clipboard['status'],
        ];

        $this->hasUnsavedChanges = true;
        $this->unsetComputed();
    }

    public function updateElementProperties(
        int|string $elementId,
        bool $isTable,
        ?string $tableName,
        ?int $seatCount,
        ?string $status,
    ): void {
        $updateData = [
            'is_table' => $isTable,
            'table_name' => $isTable ? $tableName : null,
            'seat_count' => $isTable ? $seatCount : null,
            'status' => $isTable ? $status : null,
        ];

        if (is_numeric($elementId)) {
            $id = (int) $elementId;
            $existing = $this->pendingChanges[$id] ?? [];
            $this->pendingChanges[$id] = array_merge($existing, $updateData);
        } else {
            foreach ($this->pendingNewElements as &$newElement) {
                if ($newElement['id'] === $elementId) {
                    $newElement = array_merge($newElement, $updateData);
                    break;
                }
            }
            unset($newElement);
        }

        $this->dispatch('element-properties-updated',
            id: $elementId,
            isTable: $isTable,
            tableName: $tableName,
            status: $status,
        );

        $this->hasUnsavedChanges = true;
        $this->unsetComputed();
    }

    public function updateTableStatus(int $elementId, string $status): void
    {
        // This can save immediately (no separate save needed for status changes in view mode)
        $element = FloorPlanElement::find($elementId);
        if ($element && $element->is_table) {
            $element->update(['status' => $status]);
        }

        // Also update in pending changes if element has pending state
        if (isset($this->pendingChanges[$elementId])) {
            $this->pendingChanges[$elementId]['status'] = $status;
        }

        $this->unsetComputed();
    }

    public function openTableSheet(int $elementId): void
    {
        $this->tableSheetElementId = $elementId;
        $this->showTableSheet = true;
        $this->unsetComputed();
    }

    public function closeTableSheet(): void
    {
        $this->showTableSheet = false;
        $this->tableSheetElementId = null;
        $this->unsetComputed();
    }

    private function storeUploadedImage(mixed $file, float $cropX = 0.0, float $cropY = 0.0, float $cropW = 100.0, float $cropH = 100.0): Image
    {
        $originalName = $file->getClientOriginalName();
        $mimeType = $file->getMimeType();
        $size = $file->getSize();

        $path = $file->store('images', 'public');
        $filename = basename($path);

        [$width, $height] = $this->resolveImageDimensions($file->getRealPath(), $mimeType);

        return Image::create([
            'filename' => $filename,
            'original_filename' => $originalName,
            'path' => $path,
            'mime_type' => $mimeType,
            'size' => $size,
            'width' => $width,
            'height' => $height,
            'crop_x' => $cropX,
            'crop_y' => $cropY,
            'crop_w' => $cropW,
            'crop_h' => $cropH,
        ]);
    }

    /**
     * @return array{int|null, int|null}
     */
    private function resolveImageDimensions(string $path, string $mimeType): array
    {
        if ($mimeType === 'image/svg+xml') {
            $svg = @simplexml_load_file($path);
            if ($svg) {
                $viewBox = (string) ($svg['viewBox'] ?? '');
                if ($viewBox) {
                    $parts = preg_split('/[\s,]+/', trim($viewBox));
                    if (count($parts) === 4) {
                        return [(int) $parts[2], (int) $parts[3]];
                    }
                }
                $w = (string) ($svg['width'] ?? '');
                $h = (string) ($svg['height'] ?? '');
                if ($w && $h) {
                    return [(int) $w, (int) $h];
                }
            }

            return [null, null];
        }

        $info = @getimagesize($path);

        return $info ? [$info[0], $info[1]] : [null, null];
    }

    private function unsetComputed(): void
    {
        unset(
            $this->floorPlans,
            $this->activeFloorPlan,
            $this->elements,
            $this->imageLibrary,
            $this->selectedElement,
            $this->tableSheetElement,
            $this->statusSummary,
        );
    }

    public function render(): View
    {
        return view('livewire.table-management', [
            'tableStatuses' => TableStatus::cases(),
        ])->layout('layouts.molveno');
    }
}
