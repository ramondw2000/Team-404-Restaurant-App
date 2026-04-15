<?php

namespace App\Livewire;

use App\Enums\OrderStatus;
use App\Enums\TableStatus;
use App\Models\FloorPlan;
use App\Models\FloorPlanElement;
use App\Models\Image;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\File;
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

    // Accept Order — existing-order confirmation
    public bool $showResumeOrderConfirm = false;

    public ?int $pendingOrderElementId = null;

    // Create floor plan form
    public string $newFloorPlanName = '';

    public $newBackgroundImage = null;

    // Rename floor plan form
    public string $renameFloorPlanName = '';

    // Background image replacement
    public $replacementBackgroundImage = null;

    // Snap to elements toggle
    public bool $snapEnabled = true;

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

    // ─── Computed Properties ───────────────────────────────────────────

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

        return FloorPlan::with(['backgroundImage', 'elements'])->find($this->activeFloorPlanId);
    }

    /**
     * Build the element array for the canvas, merging DB state with pending changes.
     *
     * @return array<int, array<string, mixed>>
     */
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
                'shape' => $element->shape,
                'seat_count' => $element->seat_count,
                'image_path' => $element->image_path,
                'x' => $element->x,
                'y' => $element->y,
                'width' => $element->width,
                'height' => $element->height,
                'rotation' => $element->rotation,
                'z_index' => $element->z_index,
                'table_name' => $element->table_name,
                'status' => $element->status?->value,
            ];

            if (isset($this->pendingChanges[$element->id])) {
                $merged = array_merge($data, $this->pendingChanges[$element->id]);

                // Re-resolve image path if seat_count changed
                if (isset($this->pendingChanges[$element->id]['seat_count'])) {
                    $merged['image_path'] = $this->resolveImagePath(
                        $merged['shape'],
                        $merged['seat_count'],
                    );
                }

                $data = $merged;
            }

            $elements[] = $data;
        }

        foreach ($this->pendingNewElements as $newElement) {
            $elements[] = $newElement;
        }

        usort($elements, fn ($a, $b) => $a['z_index'] <=> $b['z_index']);

        return $elements;
    }

    /**
     * Available preset elements derived from config and validated against the filesystem.
     *
     * @return array<string, array{label: string, variants: array<int, array{width: float, height: float, image_path: string}>}>
     */
    #[Computed]
    public function presetElements(): array
    {
        /** @var array<string, array{label: string, variants: array<int, array{width: float, height: float}>}> $config */
        $config = config('table-elements', []);
        $presets = [];

        foreach ($config as $shape => $shapeConfig) {
            $variants = [];

            foreach ($shapeConfig['variants'] as $seatCount => $dimensions) {
                $imagePath = $this->resolveImagePath($shape, $seatCount);
                if ($imagePath === null) {
                    continue;
                }

                $variants[$seatCount] = [
                    'width' => $dimensions['width'],
                    'height' => $dimensions['height'],
                    'image_path' => $imagePath,
                ];
            }

            if ($variants !== []) {
                $presets[$shape] = [
                    'label' => $shapeConfig['label'],
                    'variants' => $variants,
                ];
            }
        }

        return $presets;
    }

    #[Computed]
    public function selectedElement(): ?array
    {
        if (! $this->selectedElementId) {
            return null;
        }

        foreach ($this->elements as $element) {
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
            if ($element['status']) {
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

    /**
     * Return the available seat count options for a given shape.
     *
     * @return int[]
     */
    public function availableSeatCounts(string $shape): array
    {
        return array_keys($this->presetElements[$shape]['variants'] ?? []);
    }

    // ─── Floor Plan Management ─────────────────────────────────────────

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

    // ─── Edit Mode ─────────────────────────────────────────────────────

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

        foreach ($this->pendingChanges as $elementId => $changes) {
            $element = FloorPlanElement::find($elementId);
            if (! $element) {
                continue;
            }

            $updateData = array_intersect_key($changes, array_flip([
                'x', 'y', 'width', 'height', 'rotation', 'z_index',
                'table_name', 'seat_count', 'status',
            ]));

            $element->update($updateData);
        }

        foreach ($this->pendingDeletes as $elementId) {
            FloorPlanElement::find($elementId)?->delete();
        }

        $maxZIndex = FloorPlanElement::where('floor_plan_id', $this->activeFloorPlanId)->max('z_index') ?? 0;
        foreach ($this->pendingNewElements as $newElementData) {
            $maxZIndex++;
            FloorPlanElement::create([
                'floor_plan_id' => $this->activeFloorPlanId,
                'shape' => $newElementData['shape'],
                'seat_count' => $newElementData['seat_count'],
                'x' => $newElementData['x'],
                'y' => $newElementData['y'],
                'width' => $newElementData['width'],
                'height' => $newElementData['height'],
                'rotation' => $newElementData['rotation'] ?? 0,
                'z_index' => $maxZIndex,
                'table_name' => $newElementData['table_name'],
                'status' => $newElementData['status'] ?? TableStatus::Available->value,
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

    // ─── Element Operations ────────────────────────────────────────────

    /**
     * Place a new preset element on the canvas.
     */
    public function placeElement(string $shape, int $seatCount, float $x, float $y, float $width, float $height): void
    {
        $imagePath = $this->resolveImagePath($shape, $seatCount);
        if ($imagePath === null) {
            return;
        }

        $tableName = $this->generateTableName();

        $this->pendingNewElements[] = [
            'id' => 'new_'.count($this->pendingNewElements),
            'shape' => $shape,
            'seat_count' => $seatCount,
            'image_path' => $imagePath,
            'x' => $x,
            'y' => $y,
            'width' => $width,
            'height' => $height,
            'rotation' => 0.0,
            'z_index' => 999 + count($this->pendingNewElements),
            'table_name' => $tableName,
            'status' => TableStatus::Available->value,
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

        if ($this->selectedElementId == $elementId) {
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

        $tableName = $this->generateTableName();

        $this->pendingNewElements[] = [
            'id' => 'new_'.count($this->pendingNewElements),
            'shape' => $this->clipboard['shape'],
            'seat_count' => $this->clipboard['seat_count'],
            'image_path' => $this->clipboard['image_path'],
            'x' => $newX,
            'y' => $newY,
            'width' => $this->clipboard['width'],
            'height' => $this->clipboard['height'],
            'rotation' => $this->clipboard['rotation'],
            'z_index' => 999 + count($this->pendingNewElements),
            'table_name' => $tableName,
            'status' => $this->clipboard['status'],
        ];

        $this->hasUnsavedChanges = true;
        $this->unsetComputed();
    }

    /**
     * Update the properties of a selected element (table name, seat count, status).
     *
     * When the seat count changes, the element is proportionally scaled based on
     * the ratio of the new default size to the old default size from config.
     */
    public function updateElementProperties(
        int|string $elementId,
        ?string $tableName,
        int $seatCount,
        ?string $status,
    ): void {
        $currentElement = null;
        foreach ($this->elements as $el) {
            if ($el['id'] == $elementId) {
                $currentElement = $el;
                break;
            }
        }

        if (! $currentElement) {
            return;
        }

        $updateData = [
            'table_name' => $tableName,
            'seat_count' => $seatCount,
            'status' => $status,
        ];

        // Proportional scaling when seat count changes
        if ($seatCount !== $currentElement['seat_count']) {
            $shape = $currentElement['shape'];
            $scaled = $this->computeProportionalScale(
                $shape,
                $currentElement['seat_count'],
                $seatCount,
                $currentElement['width'],
                $currentElement['height'],
            );
            $updateData['width'] = $scaled['width'];
            $updateData['height'] = $scaled['height'];

            $updateData['image_path'] = $this->resolveImagePath($shape, $seatCount);
        }

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
            tableName: $tableName,
            seatCount: $seatCount,
            status: $status,
            width: $updateData['width'] ?? null,
            height: $updateData['height'] ?? null,
            imagePath: $updateData['image_path'] ?? null,
        );

        $this->hasUnsavedChanges = true;
        $this->unsetComputed();
    }

    // ─── View Mode: Accept Order ───────────────────────────────────────

    /**
     * Initiate the Accept Order flow for a given table element.
     * If the element already has a draft/active order, prompt the user to resume or start new.
     * Otherwise navigate directly to the order creation page.
     */
    public function acceptOrder(int $elementId): void
    {
        $element = FloorPlanElement::find($elementId);
        if (! $element) {
            return;
        }

        $activeOrder = $element->orders()
            ->whereIn('status', [OrderStatus::Draft->value, OrderStatus::Active->value])
            ->latest()
            ->first();

        if ($activeOrder) {
            $this->pendingOrderElementId = $elementId;
            $this->showResumeOrderConfirm = true;
        } else {
            $this->redirect(route('orders.create', $element));
        }
    }

    /**
     * Resume the existing draft/active order for the pending element.
     */
    public function resumeOrder(): void
    {
        if (! $this->pendingOrderElementId) {
            return;
        }

        $element = FloorPlanElement::find($this->pendingOrderElementId);
        $this->showResumeOrderConfirm = false;
        $this->pendingOrderElementId = null;

        if ($element) {
            $this->redirect(route('orders.create', $element));
        }
    }

    /**
     * Cancel the existing draft order and start a fresh one.
     */
    public function startNewOrder(): void
    {
        if (! $this->pendingOrderElementId) {
            return;
        }

        $element = FloorPlanElement::find($this->pendingOrderElementId);
        if ($element) {
            $element->orders()
                ->whereIn('status', [OrderStatus::Draft->value, OrderStatus::Active->value])
                ->update(['status' => OrderStatus::Cancelled->value]);
        }

        $this->showResumeOrderConfirm = false;
        $this->pendingOrderElementId = null;

        if ($element) {
            $this->redirect(route('orders.create', $element));
        }
    }

    public function dismissResumeConfirm(): void
    {
        $this->showResumeOrderConfirm = false;
        $this->pendingOrderElementId = null;
    }

    // ─── View Mode: Table Status ───────────────────────────────────────

    public function updateTableStatus(int $elementId, string $status): void
    {
        $element = FloorPlanElement::find($elementId);
        if ($element) {
            $element->update(['status' => $status]);
        }

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

    // ─── Snap Toggle ───────────────────────────────────────────────────

    public function toggleSnap(): void
    {
        $this->snapEnabled = ! $this->snapEnabled;
    }

    // ─── Private Helpers ───────────────────────────────────────────────

    /**
     * Resolve the public URL path for a shape + seat count image.
     */
    private function resolveImagePath(string $shape, int $seatCount): ?string
    {
        $extensions = ['svg', 'png', 'jpg', 'jpeg', 'webp'];
        $directory = public_path("elements/{$shape}");

        foreach ($extensions as $ext) {
            if (File::exists("{$directory}/{$seatCount}.{$ext}")) {
                return "/elements/{$shape}/{$seatCount}.{$ext}";
            }
        }

        return null;
    }

    /**
     * Auto-generate the next sequential table name across all elements on this floor plan.
     */
    private function generateTableName(): string
    {
        $maxNumber = 0;

        foreach ($this->elements as $element) {
            if (preg_match('/^Table (\d+)$/', $element['table_name'] ?? '', $matches)) {
                $maxNumber = max($maxNumber, (int) $matches[1]);
            }
        }

        foreach ($this->pendingNewElements as $newElement) {
            if (preg_match('/^Table (\d+)$/', $newElement['table_name'] ?? '', $matches)) {
                $maxNumber = max($maxNumber, (int) $matches[1]);
            }
        }

        return 'Table '.($maxNumber + 1);
    }

    /**
     * Compute proportionally scaled dimensions when changing seat count.
     *
     * @return array{width: float, height: float}
     */
    private function computeProportionalScale(
        string $shape,
        int $oldSeatCount,
        int $newSeatCount,
        float $currentWidth,
        float $currentHeight,
    ): array {
        /** @var array<string, array{variants: array<int, array{width: float, height: float}>}> $config */
        $config = config('table-elements', []);

        $oldDefault = $config[$shape]['variants'][$oldSeatCount] ?? null;
        $newDefault = $config[$shape]['variants'][$newSeatCount] ?? null;

        if (! $oldDefault || ! $newDefault) {
            return ['width' => $currentWidth, 'height' => $currentHeight];
        }

        $widthScale = $newDefault['width'] / $oldDefault['width'];
        $heightScale = $newDefault['height'] / $oldDefault['height'];

        return [
            'width' => min(100, $currentWidth * $widthScale),
            'height' => min(100, $currentHeight * $heightScale),
        ];
    }

    private function storeUploadedImage(mixed $file): Image
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
            $this->presetElements,
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
