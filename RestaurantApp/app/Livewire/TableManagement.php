<?php

namespace App\Livewire;

use App\Enums\OrderStatus;
use App\Enums\TableStatus;
use App\Models\FloorPlan;
use App\Models\FloorPlanElement;
use App\Models\Image;
use App\Models\Order;
use App\Models\Reservation;
use App\Services\OrderService;
use App\Services\ReservationService;
use Carbon\Carbon;
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

    // Order info modal
    public bool $showOrderInfoModal = false;

    public ?int $orderInfoElementId = null;

    public ?int $orderInfoOrderId = null;

    // Receipt modal
    public bool $showReceiptModal = false;

    public ?array $receiptData = null;

    // Datetime preview filter (for checking table availability at a specific time)
    public string $previewDatetime = '';

    // Reservation modal
    public bool $showReservationModal = false;

    public ?int $reservationElementId = null;

    public string $reservationGuestName = '';

    public string $reservationPhone = '';

    public string $reservationEmail = '';

    public int $reservationPartySize = 2;

    public string $reservationDatetime = '';

    public string $reservationNotes = '';

    // Accept Order — existing-order confirmation
    public bool $showResumeOrderConfirm = false;

    public ?int $pendingOrderElementId = null;

    // Departure confirmation modal
    public bool $showDepartureConfirm = false;

    public ?int $pendingDepartureReservationId = null;

    public bool $departurePaid = true;

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

    /**
     * Reservation map for current floor plan (element_id => reservation info).
     *
     * @return array<int, array{reservation_id: int, guest_name: string, party_size: int, time: string, status: string}>
     */
    #[Computed]
    public function reservationMap(): array
    {
        if (! $this->activeFloorPlanId) {
            return [];
        }

        $service = app(ReservationService::class);

        if ($this->previewDatetime !== '') {
            return $service->getReservationMapForFloorPlanAt(
                $this->activeFloorPlanId,
                Carbon::parse($this->previewDatetime)
            );
        }

        return $service->getReservationMapForFloorPlan($this->activeFloorPlanId);
    }

    /**
     * Order info for the currently viewed table element.
     *
     * @return array<string, mixed>|null
     */
    #[Computed]
    public function orderInfo(): ?array
    {
        if (! $this->orderInfoOrderId && ! $this->orderInfoElementId) {
            return null;
        }

        $orderService = app(OrderService::class);

        if ($this->orderInfoOrderId) {
            return $orderService->getOrderDetails($this->orderInfoOrderId);
        }

        // Fallback: find most recent non-cancelled order for the element
        $order = Order::where('floor_plan_element_id', $this->orderInfoElementId)
            ->whereNotIn('status', [OrderStatus::Cancelled])
            ->latest()
            ->first();

        return $order ? $orderService->getOrderDetails($order->id) : null;
    }

    /**
     * Today's reservations for the table sheet element.
     *
     * @return Collection<int, Reservation>
     */
    #[Computed]
    public function tableSheetReservations(): Collection
    {
        if (! $this->tableSheetElementId) {
            return new Collection;
        }

        return app(ReservationService::class)->getTodayReservationsForElement($this->tableSheetElementId);
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
        $this->authorize('Manage Floor Plans');

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
        $this->authorize('Manage Floor Plans');

        $this->validate([
            'renameFloorPlanName' => ['required', 'string', 'max:255'],
        ]);

        $this->activeFloorPlan?->update(['name' => $this->renameFloorPlanName]);
        $this->showRenameModal = false;
        $this->unsetComputed();
    }

    public function deleteFloorPlan(): void
    {
        $this->authorize('Manage Floor Plans');

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
        $this->authorize('Manage Floor Plans');

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
        $this->authorize('Edit Table Layout');

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
        $this->authorize('Edit Table Layout');

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
     * Update the properties of a selected element (table name, seat count).
     *
     * When the seat count changes, the element is proportionally scaled based on
     * the ratio of the new default size to the old default size from config.
     * Status is not manually settable — it is driven by reservations.
     */
    public function updateElementProperties(
        int|string $elementId,
        ?string $tableName,
        int $seatCount,
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
     * Only allowed for tables with an active reservation (Occupied status).
     */
    public function acceptOrder(int $elementId): void
    {
        $element = FloorPlanElement::find($elementId);
        if (! $element) {
            return;
        }

        // Only allow orders for occupied tables (active reservation)
        if ($element->status !== TableStatus::Occupied) {
            $this->dispatch('notify', message: 'Orders can only be placed for tables with an active reservation.', type: 'error');

            return;
        }

        $activeOrder = $element->orders()
            ->whereIn('status', [OrderStatus::Draft->value, OrderStatus::Active->value])
            ->has('items')
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

    // ─── View Mode: Order Info ─────────────────────────────────────────

    public function openOrderInfo(int $elementId): void
    {
        // Prefer the most recent non-cancelled order (including completed)
        $order = Order::where('floor_plan_element_id', $elementId)
            ->whereNotIn('status', [OrderStatus::Cancelled])
            ->latest()
            ->first();

        if (! $order) {
            $this->dispatch('notify', message: 'No order found for this table.', type: 'error');

            return;
        }

        $this->orderInfoOrderId = $order->id;
        $this->orderInfoElementId = $elementId;
        $this->showOrderInfoModal = true;
    }

    public function closeOrderInfo(): void
    {
        $this->showOrderInfoModal = false;
        $this->orderInfoElementId = null;
        $this->orderInfoOrderId = null;
    }

    /**
     * Complete the active order for a table and update statistics.
     */
    public function completeOrderForTable(int $elementId): void
    {
        $order = Order::where('floor_plan_element_id', $elementId)
            ->whereIn('status', [OrderStatus::Active->value, OrderStatus::Completed->value])
            ->where('paid', false)
            ->latest()
            ->first();

        if (! $order) {
            $this->dispatch('notify', message: 'No unpaid order found for this table.', type: 'error');

            return;
        }

        $order->update(['paid' => true]);

        $this->closeOrderInfo();
        $this->unsetComputed();
        $this->dispatch('notify', message: 'Order marked as paid.');
    }

    // ─── View Mode: Receipt ────────────────────────────────────────────

    public function openReceipt(int $elementId): void
    {
        $orderService = app(OrderService::class);
        $reservationService = app(ReservationService::class);

        $activeReservation = $reservationService->getActiveReservationForElement($elementId);

        if ($activeReservation) {
            $this->receiptData = $orderService->getReceiptForReservation($activeReservation->id);
        } else {
            $activeOrder = $orderService->getActiveOrderForElement($elementId);
            if ($activeOrder) {
                $this->receiptData = $orderService->getReceiptForOrder($activeOrder->id);
            }
        }

        if ($this->receiptData) {
            $this->showReceiptModal = true;
        } else {
            $this->dispatch('notify', message: 'No orders found to generate receipt.', type: 'error');
        }
    }

    public function closeReceipt(): void
    {
        $this->showReceiptModal = false;
        $this->receiptData = null;
    }

    // ─── View Mode: Reservation Management ─────────────────────────────

    public function openReservationModal(int $elementId): void
    {
        $this->reservationElementId = $elementId;
        $this->reservationGuestName = '';
        $this->reservationPhone = '';
        $this->reservationEmail = '';
        $this->reservationPartySize = 2;
        $this->reservationDatetime = $this->previewDatetime !== ''
            ? $this->previewDatetime
            : now()->addHour()->format('Y-m-d\TH:i');
        $this->reservationNotes = '';
        $this->showReservationModal = true;
    }

    public function closeReservationModal(): void
    {
        $this->showReservationModal = false;
        $this->reservationElementId = null;
        $this->resetValidation();
    }

    public function createReservation(): void
    {
        $this->validate([
            'reservationGuestName' => ['required', 'string', 'max:255'],
            'reservationPhone' => ['nullable', 'string', 'max:50'],
            'reservationEmail' => ['nullable', 'email', 'max:255'],
            'reservationPartySize' => ['required', 'integer', 'min:1', 'max:20'],
            'reservationDatetime' => ['required', 'date', 'after:now'],
            'reservationNotes' => ['nullable', 'string', 'max:1000'],
        ]);

        $element = FloorPlanElement::find($this->reservationElementId);
        if (! $element) {
            return;
        }

        // Check table is not currently occupied
        if ($element->status === TableStatus::Occupied) {
            $this->addError('reservationDatetime', 'This table is currently occupied.');

            return;
        }

        $reservationService = app(ReservationService::class);
        $dateTime = Carbon::parse($this->reservationDatetime);

        // Check availability
        if (! $reservationService->isTableAvailableAt($element->id, $dateTime)) {
            $this->addError('reservationDatetime', 'This table is already reserved around that time (2-hour window).');

            return;
        }

        $reservationService->createForTable($element->id, [
            'guest_name' => $this->reservationGuestName,
            'phone' => $this->reservationPhone,
            'email' => $this->reservationEmail,
            'party_size' => $this->reservationPartySize,
            'reservation_datetime' => $this->reservationDatetime,
            'internal_notes' => $this->reservationNotes,
        ]);

        $this->closeReservationModal();
        $this->unsetComputed();
        $this->dispatch('notify', message: 'Reservation created successfully.');
    }

    /**
     * Seat a reservation (mark as arrived, table becomes Occupied).
     */
    public function seatReservation(int $reservationId): void
    {
        $reservation = Reservation::find($reservationId);
        if (! $reservation) {
            return;
        }

        app(ReservationService::class)->seatReservation($reservation);
        $this->unsetComputed();
        $this->dispatch('notify', message: $reservation->guest_name.' has been seated.');
    }

    /**
     * Open departure confirmation modal.
     */
    public function openDepartureConfirm(int $reservationId): void
    {
        $this->pendingDepartureReservationId = $reservationId;
        $this->departurePaid = true;
        $this->showDepartureConfirm = true;
    }

    /**
     * Close departure confirmation modal.
     */
    public function closeDepartureConfirm(): void
    {
        $this->showDepartureConfirm = false;
        $this->pendingDepartureReservationId = null;
        $this->departurePaid = true;
    }

    /**
     * Complete a reservation (mark as departed) with payment status.
     */
    public function confirmDeparture(): void
    {
        if (! $this->pendingDepartureReservationId) {
            return;
        }

        $reservation = Reservation::find($this->pendingDepartureReservationId);
        if (! $reservation) {
            $this->closeDepartureConfirm();

            return;
        }

        app(ReservationService::class)->completeReservation($reservation, $this->departurePaid);
        $this->closeDepartureConfirm();
        $this->unsetComputed();
        $this->dispatch('notify', message: $reservation->guest_name.' has departed. Orders marked as '.($this->departurePaid ? 'paid' : 'unpaid').'.');
    }

    /**
     * Cancel a reservation.
     */
    public function cancelReservation(int $reservationId): void
    {
        $this->authorize('Cancel Reservation');

        $reservation = Reservation::find($reservationId);
        if (! $reservation) {
            return;
        }

        $reservation->update(['status' => 'cancelled']);

        // Free up the table if this was the only active reservation
        if ($reservation->floorPlanElement) {
            $hasOtherActive = Reservation::where('floor_plan_element_id', $reservation->floor_plan_element_id)
                ->where('id', '!=', $reservation->id)
                ->whereIn('status', ['scheduled', 'arrived'])
                ->whereDate('reservation_datetime', today())
                ->exists();

            if (! $hasOtherActive) {
                $reservation->floorPlanElement->update(['status' => \App\Enums\TableStatus::Available]);
            }
        }

        $this->unsetComputed();
        $this->dispatch('notify', message: $reservation->guest_name.' reservation cancelled.');
    }

    /**
     * Mark a reservation as no-show.
     */
    public function markNoShow(int $reservationId): void
    {
        $reservation = Reservation::find($reservationId);
        if (! $reservation) {
            return;
        }

        $reservation->update(['status' => 'no_show']);

        // Free up the table if this was the only active reservation
        if ($reservation->floorPlanElement) {
            $hasOtherActive = Reservation::where('floor_plan_element_id', $reservation->floor_plan_element_id)
                ->where('id', '!=', $reservation->id)
                ->whereIn('status', ['scheduled', 'arrived'])
                ->whereDate('reservation_datetime', today())
                ->exists();

            if (! $hasOtherActive) {
                $reservation->floorPlanElement->update(['status' => \App\Enums\TableStatus::Available]);
            }
        }

        $this->unsetComputed();
        $this->dispatch('notify', message: $reservation->guest_name.' marked as no-show.');
    }

    // ─── View Mode: Table Sheet ───────────────────────────────────────

    public function openTableSheet(int $elementId): void
    {
        // Auto-mark late reservations before showing
        app(ReservationService::class)->autoMarkLateReservations();

        $this->tableSheetElementId = $elementId;
        $this->showTableSheet = true;
        unset($this->tableSheetReservations);
        $this->unsetComputed();
    }

    public function closeTableSheet(): void
    {
        $this->showTableSheet = false;
        $this->tableSheetElementId = null;
        unset($this->tableSheetReservations);
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
            $this->reservationMap,
            $this->orderInfo,
            $this->tableSheetReservations,
        );
    }

    public function render(): View
    {
        return view('livewire.table-management')
            ->layout('layouts.molveno');
    }
}
