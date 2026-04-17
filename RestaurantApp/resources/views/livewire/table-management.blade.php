<div
    class="flex flex-col flex-1 overflow-hidden bg-gray-50"
    @keydown.window.delete="$wire.selectedElementId && $wire.editMode && $wire.deleteElement($wire.selectedElementId)"
    @keydown.window.ctrl.c.prevent="$wire.selectedElementId && $wire.editMode && $wire.copyElement($wire.selectedElementId)"
    @keydown.window.ctrl.v.prevent="$wire.editMode && $wire.pasteElement()"
    @keydown.window.shift.prevent="$wire.editMode && $wire.toggleSnap()"
>
    {{-- ===== TOP BAR ===== --}}
    <header class="flex items-center h-14 px-4 bg-white border-b border-gray-200 shadow-sm shrink-0 z-20">
        {{-- Left: Floor Plan Switcher --}}
        <div class="flex items-center gap-2">
            @if($this->floorPlans->isNotEmpty())
                <div class="relative">
                    <select
                        wire:change="switchFloorPlan($event.target.value)"
                        class="appearance-none bg-none pl-3 pr-8 py-1.5 text-sm font-medium bg-white border border-gray-300 rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent cursor-pointer"
                    >
                        @foreach($this->floorPlans as $plan)
                            <option
                                value="{{ $plan->id }}"
                                @selected($plan->id === $activeFloorPlanId)
                            >{{ $plan->name }}</option>
                        @endforeach
                    </select>
                    <span class="pointer-events-none absolute inset-y-0 right-2 flex items-center text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 9l-7 7-7-7"/></svg>
                    </span>
                </div>
            @endif

            @if($this->canManageFloorPlans)
                <button
                    wire:click="openCreateFloorPlanModal"
                    title="Add floor plan"
                    class="flex items-center justify-center w-8 h-8 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </button>
            @endif
        </div>

        {{-- Center: Title + Snap Toggle (edit mode) --}}
        <div class="flex-1 flex items-center justify-center gap-3">
            <span class="text-sm font-semibold text-gray-700 tracking-wide uppercase">Table Management</span>
            @if($editMode)
                <label class="flex items-center gap-1.5 text-xs text-gray-500 cursor-pointer select-none">
                    <input
                        type="checkbox"
                        wire:model.live="snapEnabled"
                        class="w-3.5 h-3.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-offset-0"
                    >
                    Snap to elements
                </label>
            @endif
        </div>

        {{-- Right: Filters (view mode) / Edit toggle --}}
        <div class="flex items-center gap-3">
            @if($this->activeFloorPlan && $this->floorPlans->isNotEmpty())
                @if(!$editMode)
                    {{-- Datetime availability preview --}}
                    <div class="flex items-center gap-1.5 hidden sm:flex">
                        <div class="relative">
                            <svg class="absolute left-2 top-1/2 -translate-y-1/2 w-3 h-3 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <input
                                type="datetime-local"
                                wire:model.live="previewDatetime"
                                class="pl-6 pr-2 py-1 text-xs border rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-0 transition-colors {{ $previewDatetime ? 'border-amber-400 bg-amber-50 text-amber-800 ring-amber-400 focus:ring-amber-400' : 'border-gray-200 focus:ring-cyan-400' }}"
                                title="Preview table availability at a specific date & time"
                            >
                        </div>
                        @if($previewDatetime)
                            <button
                                type="button"
                                wire:click="$set('previewDatetime', '')"
                                class="text-xs text-amber-600 hover:text-amber-800 transition-colors font-medium"
                                title="Clear preview"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        @endif
                    </div>

                    {{-- Name search --}}
                    <div class="relative hidden sm:block">
                        <svg class="absolute left-2 top-1/2 -translate-y-1/2 w-3 h-3 text-gray-400 pointer-events-none"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                        </svg>
                        <input
                            type="text"
                            placeholder="Table name…"
                            class="pl-6 pr-2 py-1 text-xs border border-gray-200 rounded-lg w-28 focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:border-transparent"
                            x-model="$store.filters.name"
                        >
                    </div>

                    {{-- Seat count --}}
                    <div class="relative hidden sm:block">
                        <select
                            class="px-2 py-1 text-xs border border-gray-200 rounded-lg w-20 focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:border-transparent"
                            x-model="$store.filters.seats"
                        >
                            <option value="">Seats</option>
                            @for($i = 2; $i <= 10; $i += 2)
                                <option value="{{ $i }}">{{ $i }} seats</option>
                            @endfor
                        </select>
                    </div>

                    {{-- Status filter toggles (doubles as status summary) --}}
                    <div class="hidden sm:flex items-center gap-1">
                        @foreach(\App\Enums\TableStatus::cases() as $status)
                            <button
                                type="button"
                                class="flex items-center gap-1.5 px-2 py-1 rounded-lg text-xs font-medium text-gray-600 hover:bg-gray-100 transition-all"
                                :class="$store.filters.statuses.includes('{{ $status->value }}') ? 'bg-cyan-50 ring-1 ring-cyan-400 text-cyan-700' : ''"
                                @click="$store.filters.toggleStatus('{{ $status->value }}')"
                                title="Filter by {{ $status->label() }}"
                            >
                                <span class="w-2 h-2 rounded-full {{ $status->dotClasses() }}"></span>
                                {{ $status->label() }}: {{ $this->statusSummary[$status->value] ?? 0 }}
                            </button>
                        @endforeach
                    </div>

                    {{-- Clear filters --}}
                    <button
                        type="button"
                        x-show="$store.filters.active"
                        x-transition
                        @click="$store.filters.clear()"
                        class="text-xs text-gray-400 hover:text-gray-600 transition-colors px-1"
                        title="Clear filters"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                  d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                @endif

                {{-- Edit Mode Toggle --}}
                @if($editMode)
                    <div class="flex items-center gap-2">
                        <x-ui.button size="sm" wire:click="saveChanges">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M5 13l4 4L19 7"/>
                            </svg>
                            Save
                        </x-ui.button>
                        <x-ui.button variant="secondary" size="sm" wire:click="exitEditMode">
                            Done
                        </x-ui.button>
                    </div>
                @else
                    <x-ui.button variant="outline" size="sm" wire:click="enterEditMode">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit
                    </x-ui.button>
                @endif
            @endif
        </div>
    </header>

    {{-- ===== PREVIEW MODE BANNER ===== --}}
    @if($previewDatetime && !$editMode)
        <div class="flex items-center justify-between gap-3 px-4 py-2 bg-amber-50 border-b border-amber-200 shrink-0 z-10">
            <div class="flex items-center gap-2 text-sm text-amber-800">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>
                    Viewing availability at
                    <strong>{{ \Carbon\Carbon::parse($previewDatetime)->format('D j M Y, H:i') }}</strong>
                    — reservations within ±2 hours are shown. Click any table to book it at this time.
                </span>
            </div>
            <button
                type="button"
                wire:click="$set('previewDatetime', '')"
                class="text-xs font-semibold text-amber-700 hover:text-amber-900 underline shrink-0"
            >
                Back to live view
            </button>
        </div>
    @endif

    {{-- ===== MAIN CONTENT AREA ===== --}}
    <div class="flex flex-1 overflow-hidden">

        {{-- ===== CANVAS AREA ===== --}}
        <div
            class="flex-1 relative overflow-hidden canvas-checkerboard"
            x-data="canvasApp()"
            data-img-width="{{ $this->activeFloorPlan?->backgroundImage?->width ?? 0 }}"
            data-img-height="{{ $this->activeFloorPlan?->backgroundImage?->height ?? 0 }}"
            data-edit-mode="{{ $editMode ? 'true' : 'false' }}"
            data-snap-enabled="{{ $snapEnabled ? 'true' : 'false' }}"
            @wheel.prevent="onWheel($event)"
            @mousedown="onMouseDown($event)"
            @mousemove="onMouseMove($event)"
            @mouseup="onMouseUp()"
            @touchstart.passive="onTouchStart($event)"
            @touchmove.prevent="onTouchMove($event)"
            @touchend="onTouchEnd()"
            @click="closeMenu()"
            @contextmenu.prevent="
                if ($el.dataset.editMode === 'true') {
                    const el = $event.target.closest('[data-element-id]');
                    if (el) {
                        const rawId = el.dataset.elementId;
                        const id = /^\d+$/.test(rawId) ? parseInt(rawId) : rawId;
                        openMenu($event.clientX, $event.clientY, id);
                    }
                }
            "
            @reset-canvas-view.window="resetView()"
            @dragover.prevent
            @drop.prevent="
                const shape = $event.dataTransfer.getData('element-shape');
                const seatCount = $event.dataTransfer.getData('element-seat-count');
                const defaultWidth = parseFloat($event.dataTransfer.getData('element-default-width') || 10);
                const defaultHeight = parseFloat($event.dataTransfer.getData('element-default-height') || 10);
                if (shape && seatCount) {
                    const canvasInner = $el.querySelector('[data-canvas-inner]');
                    const rect = canvasInner.getBoundingClientRect();
                    const xPct = (($event.clientX - rect.left) / rect.width) * 100;
                    const yPct = (($event.clientY - rect.top) / rect.height) * 100;
                    $wire.placeElement(
                        shape,
                        parseInt(seatCount),
                        Math.max(0, Math.min(100 - defaultWidth, xPct - defaultWidth / 2)),
                        Math.max(0, Math.min(100 - defaultHeight, yPct - defaultHeight / 2)),
                        defaultWidth,
                        defaultHeight
                    );
                }
            "
        >
            @if(! $this->activeFloorPlan && $this->floorPlans->isEmpty())
                {{-- Empty State --}}
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <div class="text-center max-w-sm mx-auto px-6">
                        <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">No floor plans available</h3>
                        @if($this->canManageFloorPlans)
                            <p class="text-sm text-gray-500 mb-6">Create your first floor plan to start managing your
                                restaurant tables.</p>
                            <x-ui.button wire:click="openCreateFloorPlanModal">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 4v16m8-8H4"/>
                                </svg>
                                Create your first floor plan
                            </x-ui.button>
                        @else
                            <p class="text-sm text-gray-500">There are no floor plans available. Please contact an administrator or someone with permission to upload a floor plan.</p>
                        @endif
                    </div>
                </div>
            @else
                {{-- Canvas Container (pan/zoom wrapper) --}}
                <div
                    :style="transformStyle"
                    class="absolute inset-0 origin-top-left will-change-transform"
                >
                    {{-- Canvas Inner (proportional to background image) --}}
                    <div
                        data-canvas-inner
                        data-pannable="true"
                        class="relative"
                        :style="canvasInnerStyle"
                        @click.self="$wire.editMode && $wire.deselectElement()"
                    >
                        {{-- Background Image or Checkerboard --}}
                        @if($this->activeFloorPlan?->backgroundImage)
                            <img
                                src="{{ $this->activeFloorPlan->backgroundImage->url() }}"
                                alt="Floor plan background"
                                class="absolute inset-0 w-full h-full select-none pointer-events-none"
                                draggable="false"
                            >
                        @else
                            <div class="absolute inset-0 canvas-checkerboard"></div>
                        @endif

                        {{-- Snap Guide Lines (edit mode only, rendered dynamically by JS) --}}
                        @if($editMode)
                            <div data-snap-guides class="absolute inset-0 pointer-events-none z-[9000]"></div>
                        @endif

                        {{-- Placed Elements --}}
                        @foreach($this->elements as $element)
                            @php
                                $isSelected = $editMode && $selectedElementId == $element['id'];
                                $status = $element['status']
                                    ? \App\Enums\TableStatus::from($element['status'])
                                    : null;
                                $reservation = $this->reservationMap[$element['id']] ?? null;
                            @endphp
                            @php $elementId = is_numeric($element['id']) ? $element['id'] : "'" . $element['id'] . "'" @endphp
                            <div
                                wire:key="element-{{ $element['id'] }}-{{ $editMode ? 'e' : 'v' }}"
                                id="element-{{ $element['id'] }}"
                                data-element-id="{{ $element['id'] }}"
                                @if($editMode)
                                    wire:ignore
                                x-data="canvasElement({{ json_encode($element) }})"
                                class="absolute select-none group"
                                :class="{ 'ring-2 ring-blue-500 ring-offset-1': $wire.selectedElementId == elementId }"
                                :style="positionStyle"
                                @click="hitsSvgContent($event) && $wire.selectElement({{ $elementId }})"
                                @element-zindex-updated.window="if ($event.detail.id == elementId) zIndex = $event.detail.zIndex"
                                @else
                                    x-data="{ ...viewElementSvg('{{ $element['image_path'] }}'), el: {{ json_encode(['table_name' => $element['table_name'], 'status' => $element['status'] ?? null, 'seat_count' => $element['seat_count'] ?? null]) }} }"
                                class="absolute select-none group transition-opacity cursor-pointer"
                                style="left:{{ $element['x'] }}%;top:{{ $element['y'] }}%;width:{{ $element['width'] }}%;height:{{ $element['height'] }}%;transform:rotate({{ $element['rotation'] }}deg);z-index:{{ $element['z_index'] }};"
                                :class="{
                                        'opacity-20': $store.filters.active && !$store.filters.matches(el),
                                        'ring-2 ring-cyan-400 ring-offset-1': $store.filters.active && $store.filters.matches(el)
                                    }"
                                @click.stop="hitsSvgContent($event) && ($wire.previewDatetime ? $wire.openReservationModal({{ $element['id'] }}) : $wire.openTableSheet({{ $element['id'] }}))"
                                @endif
                            >
                                {{-- Element SVG (loaded inline for pixel-precise hit testing) --}}
                                <div class="absolute inset-0 overflow-hidden pointer-events-none" data-svg-container wire:ignore>
                                    <img
                                        src="{{ $element['image_path'] }}"
                                        alt="{{ $element['table_name'] ?? 'Table' }}"
                                        class="absolute inset-0 w-full h-full object-contain pointer-events-none"
                                        draggable="false"
                                    >
                                </div>

                                {{-- Table Name Label + Reservation Indicator --}}
                                @if($editMode)
                                    <div
                                        x-show="tableName"
                                        class="absolute left-1/2 -translate-x-1/2 pointer-events-none"
                                        style="top: 100%; margin-top: 3px;"
                                    >
                                        <span
                                            :class="badgeClasses"
                                            x-text="tableName"
                                            class="whitespace-nowrap"
                                        ></span>
                                    </div>
                                @else
                                    @if($element['table_name'])
                                        <div
                                            class="absolute left-1/2 -translate-x-1/2 pointer-events-none flex flex-col items-center gap-0.5"
                                            style="top: 100%; margin-top: 3px;"
                                        >
                                            @if($status)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold {{ $status->badgeClasses() }} shadow-sm whitespace-nowrap">
                                                    <span class="w-1.5 h-1.5 rounded-full {{ $status->dotClasses() }} shrink-0"></span>
                                                    {{ $element['table_name'] }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-white/90 text-gray-700 shadow-sm ring-1 ring-gray-200 whitespace-nowrap">
                                                    {{ $element['table_name'] }}
                                                </span>
                                            @endif
                                            @if($reservation)
                                                @php
                                                    $resStatusColor = match($reservation['status']) {
                                                        'scheduled' => 'bg-blue-100 text-blue-700 ring-blue-200',
                                                        'arrived' => 'bg-green-100 text-green-700 ring-green-200',
                                                        'departed' => 'bg-gray-100 text-gray-600 ring-gray-200',
                                                        'late' => 'bg-amber-100 text-amber-700 ring-amber-200',
                                                        'no_show' => 'bg-rose-100 text-rose-700 ring-rose-200',
                                                        'cancelled' => 'bg-red-100 text-red-700 ring-red-200',
                                                        default => 'bg-white/95 text-gray-600 ring-gray-200',
                                                    };
                                                    $resStatusDot = match($reservation['status']) {
                                                        'scheduled' => 'bg-blue-500',
                                                        'arrived' => 'bg-green-500',
                                                        'departed' => 'bg-gray-400',
                                                        'late' => 'bg-amber-500',
                                                        'no_show' => 'bg-rose-500',
                                                        'cancelled' => 'bg-red-500',
                                                        default => 'bg-molveno-blue-500',
                                                    };
                                                @endphp
                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[9px] font-semibold {{ $resStatusColor }} shadow-sm ring-1 whitespace-nowrap">
                                                    <span class="w-1.5 h-1.5 rounded-full {{ $resStatusDot }} shrink-0"></span>
                                                    {{ $reservation['guest_name'] }} &middot; {{ $reservation['time'] }}
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                @endif

                                {{-- Edit Mode: Rotation Handle --}}
                                @if($editMode)
                                    <div
                                        data-rotate-handle
                                        class="absolute -top-6 left-1/2 -translate-x-1/2 w-5 h-5 rounded-full bg-white border-2 border-blue-500 shadow cursor-grab active:cursor-grabbing opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center"
                                        @pointerdown.stop="startRotate($event)"
                                        title="Rotate (hold Shift for free rotation)"
                                    >
                                        <svg class="w-3 h-3 text-blue-500" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                        </svg>
                                    </div>

                                    {{-- Delete button --}}
                                    @if($isSelected)
                                        <button
                                            @click.stop="$wire.deleteElement({{ $elementId }})"
                                            class="absolute -top-3 -right-3 w-6 h-6 rounded-full bg-red-500 text-white flex items-center justify-center hover:bg-red-600 transition-colors shadow-md z-10"
                                            title="Delete element"
                                        >
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                      d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    @endif
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Reset view button --}}
                <div class="absolute bottom-4 right-4 z-10" x-data>
                    <button
                        @click="window.dispatchEvent(new CustomEvent('reset-canvas-view'))"
                        class="flex items-center justify-center w-9 h-9 rounded-xl bg-white shadow-md border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 transition-colors"
                        title="Reset view"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                        </svg>
                    </button>
                </div>
            @endif

            {{-- ===== RIGHT-CLICK CONTEXT MENU ===== --}}
            <div
                x-show="menuShow"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                :style="`position: fixed; top: ${menuY}px; left: ${menuX}px; z-index: 9999;`"
                class="bg-white rounded-xl shadow-xl border border-gray-200 py-1.5 min-w-[160px]"
                @click.stop
            >
                <button
                    @click="$wire.copyElement(menuTargetId); closeMenu()"
                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                >
                    Copy
                </button>
                <div class="my-1 border-t border-gray-100"></div>
                <button
                    @click="$wire.bringToFront(menuTargetId); closeMenu()"
                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                >
                    Bring to Front
                </button>
                <button
                    @click="$wire.sendToBack(menuTargetId); closeMenu()"
                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                >
                    Send to Back
                </button>
                <div class="my-1 border-t border-gray-100"></div>
                <button
                    @click="$wire.deleteElement(menuTargetId); closeMenu()"
                    class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors"
                >
                    Delete
                </button>
            </div>
        </div>

        {{-- ===== SIDEBAR (Edit Mode) ===== --}}
        @if($editMode && $this->activeFloorPlan)
            <aside
                class="w-72 bg-white border-l border-gray-200 flex flex-col shadow-sm overflow-hidden shrink-0 transition-all duration-200">
                @if($selectedElementId && $this->selectedElement)
                    {{-- ───── Element Properties Panel ───── --}}
                    @php $el = $this->selectedElement; @endphp
                    <div class="flex flex-col h-full">
                        {{-- Panel Header --}}
                        <div class="flex items-center gap-2 p-4 border-b border-gray-100">
                            <button
                                wire:click="deselectElement"
                                class="flex items-center justify-center w-7 h-7 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors"
                                title="Back"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>
                            <h3 class="text-sm font-semibold text-gray-800">Element Properties</h3>
                        </div>

                        <div class="flex-1 overflow-y-auto p-4 space-y-5">
                            {{-- Element preview --}}
                            <div class="flex justify-center p-4 bg-gray-50 rounded-xl">
                                <img src="{{ $el['image_path'] }}" alt="Element" class="w-24 h-24 object-contain">
                            </div>

                            {{-- Properties Form --}}
                            <div
                                wire:key="element-props-{{ $el['id'] }}"
                                wire:ignore
                                x-data="{
                                    tableName: '{{ addslashes($el['table_name'] ?? '') }}',
                                    seatCount: {{ $el['seat_count'] }},
                                    availableSeats: {{ json_encode($this->availableSeatCounts($el['shape'])) }},
                                    syncToWire() {
                                        $wire.updateElementProperties(
                                            {{ json_encode($el['id']) }},
                                            this.tableName || null,
                                            this.seatCount,
                                        );
                                    }
                                }"
                            >
                                {{-- Shape (read-only) --}}
                                <div class="mb-4">
                                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Shape</label>
                                    <p class="text-sm font-medium text-gray-800">{{ $this->presetElements[$el['shape']]['label'] ?? ucfirst($el['shape']) }}</p>
                                </div>

                                {{-- Table Name --}}
                                <div class="mb-4">
                                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Table Name</label>
                                    <input
                                        type="text"
                                        x-model="tableName"
                                        @blur="syncToWire()"
                                        @keydown.enter="syncToWire()"
                                        placeholder="e.g. Table 1"
                                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    >
                                </div>

                                {{-- Seats (dropdown limited to available variants) --}}
                                <div class="mb-4">
                                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Seats</label>
                                    <select
                                        x-model.number="seatCount"
                                        @change="syncToWire()"
                                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    >
                                        <template x-for="seats in availableSeats" :key="seats">
                                            <option :value="seats" x-text="seats + ' seats'" :selected="seats === seatCount"></option>
                                        </template>
                                    </select>
                                </div>

                                {{-- Status (read-only, driven by reservations) --}}
                                @if($el['status'])
                                    @php $elStatus = \App\Enums\TableStatus::from($el['status']); @endphp
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Status</label>
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold {{ $elStatus->badgeClasses() }}">
                                            <span class="w-2 h-2 rounded-full {{ $elStatus->dotClasses() }}"></span>
                                            {{ $elStatus->label() }}
                                        </span>
                                        <p class="text-[10px] text-gray-400 mt-1">Managed by reservations</p>
                                    </div>
                                @endif
                            </div>

                            {{-- Z-Order Controls --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Layer Order</label>
                                <div class="flex gap-2">
                                    <button
                                        wire:click="bringToFront({{ json_encode($el['id']) }})"
                                        class="flex-1 py-2 px-3 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                                    >
                                        Bring to Front
                                    </button>
                                    <button
                                        wire:click="sendToBack({{ json_encode($el['id']) }})"
                                        class="flex-1 py-2 px-3 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                                    >
                                        Send to Back
                                    </button>
                                </div>
                            </div>

                            {{-- Delete Button --}}
                            <button
                                wire:click="deleteElement({{ json_encode($el['id']) }})"
                                class="w-full py-2 px-3 text-sm font-medium text-red-600 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition-colors"
                            >
                                Delete Element
                            </button>
                        </div>
                    </div>
                @else
                    {{-- ───── Default Sidebar: Preset Palette + Floor Plan Controls ───── --}}
                    <div class="flex flex-col h-full">
                        {{-- Floor Plan Controls --}}
                        <div class="p-4 border-b border-gray-100">
                            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Floor Plan</h3>
                            <div class="space-y-2">
                                <button
                                    wire:click="openRenameModal"
                                    class="w-full flex items-center gap-2 px-3 py-2 text-sm text-gray-700 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors text-left"
                                >
                                    <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    Rename Floor Plan
                                </button>

                                {{-- Background Image Replacement --}}
                                <div
                                    x-data="{ uploading: false, progress: 0 }"
                                    x-on:livewire-upload-start="uploading = true"
                                    x-on:livewire-upload-finish="uploading = false; $wire.replaceBackgroundImage()"
                                    x-on:livewire-upload-progress="progress = $event.detail.progress"
                                >
                                    <label
                                        class="w-full flex items-center gap-2 px-3 py-2 text-sm text-gray-700 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer">
                                        <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span
                                            x-text="uploading ? `Uploading ${progress}%...` : 'Change Background'"></span>
                                        <input
                                            type="file"
                                            wire:model="replacementBackgroundImage"
                                            accept="image/png,image/jpeg,image/webp,image/svg+xml"
                                            class="hidden"
                                        >
                                    </label>
                                    <div x-show="uploading" class="mt-1 h-1 bg-gray-200 rounded-full overflow-hidden">
                                        <div class="h-full bg-blue-500 transition-all"
                                             :style="`width: ${progress}%`"></div>
                                    </div>
                                </div>

                                {{-- Delete Floor Plan --}}
                                <x-ui.button variant="danger" size="sm" wire:click="deleteFloorPlan" wire:confirm="Are you sure you want to delete this floor plan? This cannot be undone." class="w-full justify-start">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Delete Floor Plan
                                </x-ui.button>
                            </div>
                        </div>

                        {{-- ───── Preset Element Palette ───── --}}
                        <div class="flex-1 flex flex-col overflow-hidden">
                            <div class="px-4 pt-4 pb-2">
                                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Elements</h3>
                            </div>

                            @if(empty($this->presetElements))
                                <div class="flex-1 flex flex-col items-center justify-center px-4 py-8 text-center">
                                    <p class="text-sm text-gray-500">No preset elements available.</p>
                                </div>
                            @else
                                <div class="flex-1 overflow-y-auto px-4 pb-4 space-y-4">
                                    @foreach($this->presetElements as $shape => $shapeData)
                                        <div x-data="{ open: true }">
                                            {{-- Shape Group Header (collapsible) --}}
                                            <button
                                                @click="open = !open"
                                                class="flex items-center justify-between w-full py-1.5 text-xs font-semibold text-gray-600 uppercase tracking-wide hover:text-gray-800 transition-colors"
                                            >
                                                {{ $shapeData['label'] }}
                                                <svg class="w-3.5 h-3.5 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            </button>

                                            {{-- Variant Thumbnails --}}
                                            <div x-show="open" x-transition class="grid grid-cols-3 gap-2 mt-1.5">
                                                @foreach($shapeData['variants'] as $seatCount => $variant)
                                                    <div
                                                        wire:key="preset-{{ $shape }}-{{ $seatCount }}"
                                                        class="relative group aspect-square bg-gray-50 rounded-xl overflow-hidden border border-gray-200 hover:border-blue-300 transition-colors cursor-grab active:cursor-grabbing flex items-center justify-center p-2"
                                                        draggable="true"
                                                        @dragstart="
                                                            $event.dataTransfer.setData('element-shape', '{{ $shape }}');
                                                            $event.dataTransfer.setData('element-seat-count', '{{ $seatCount }}');
                                                            $event.dataTransfer.setData('element-default-width', '{{ $variant['width'] }}');
                                                            $event.dataTransfer.setData('element-default-height', '{{ $variant['height'] }}');
                                                            $event.dataTransfer.effectAllowed = 'copy';
                                                            const ghost = document.createElement('div');
                                                            ghost.style.cssText = 'position:fixed;top:-200px;left:-200px;width:60px;height:60px;overflow:hidden;border-radius:8px;background:#f9fafb;display:flex;align-items:center;justify-content:center;';
                                                            const ghostImg = document.createElement('img');
                                                            ghostImg.src = '{{ $variant['image_path'] }}';
                                                            ghostImg.style.cssText = 'width:80%;height:80%;object-fit:contain;';
                                                            ghost.appendChild(ghostImg);
                                                            document.body.appendChild(ghost);
                                                            $event.dataTransfer.setDragImage(ghost, 30, 30);
                                                            setTimeout(() => ghost.remove(), 0);
                                                        "
                                                        title="{{ $shapeData['label'] }} ({{ $seatCount }} seats)"
                                                    >
                                                        <img
                                                            src="{{ $variant['image_path'] }}"
                                                            alt="{{ $shapeData['label'] }} {{ $seatCount }} seats"
                                                            class="w-full h-full object-contain pointer-events-none"
                                                            draggable="false"
                                                        >
                                                        {{-- Seat count label --}}
                                                        <span class="absolute bottom-0.5 right-1 text-[10px] font-bold text-gray-500">{{ $seatCount }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </aside>
        @endif
    </div>

    {{-- ===== TABLE DETAIL SHEET ===== --}}
    @if($showTableSheet && $this->tableSheetElement)
        @php $tableEl = $this->tableSheetElement; $currentStatus = $tableEl['status'] ? \App\Enums\TableStatus::from($tableEl['status']) : null; @endphp
        <div
            class="fixed inset-0 z-50"
            x-data
            @click.self="$wire.closeTableSheet()"
        >
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-black/20 backdrop-blur-sm" @click="$wire.closeTableSheet()"></div>

            {{-- Sheet Panel --}}
            <div
                class="absolute right-0 top-0 bottom-0 w-full max-w-sm bg-white shadow-2xl flex flex-col"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="translate-x-full"
                @click.stop
            >
                {{-- Sheet Header --}}
                <div class="flex items-center justify-between p-5 border-b border-gray-100 shrink-0">
                    <h2 class="text-lg font-bold text-gray-900">{{ $tableEl['table_name'] ?? 'Table' }}</h2>
                    <button
                        wire:click="closeTableSheet"
                        class="flex items-center justify-center w-8 h-8 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Sheet Content --}}
                <div class="flex-1 overflow-y-auto p-5 space-y-6">
                    {{-- Info Row --}}
                    <div class="flex items-center gap-6">
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Seats</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $tableEl['seat_count'] ?? '—' }}</p>
                        </div>
                        @if($currentStatus)
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Current Status</p>
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-semibold {{ $currentStatus->badgeClasses() }}">
                                    <span class="w-2 h-2 rounded-full {{ $currentStatus->dotClasses() }}"></span>
                                    {{ $currentStatus->label() }}
                                </span>
                            </div>
                        @endif
                    </div>

                    {{-- Reservations Section --}}
                    <x-table-management.reservation-list :reservations="$this->tableSheetReservations" />
                </div>

                {{-- Sheet Footer --}}
                <div class="p-5 border-t border-gray-100 flex flex-col gap-2 shrink-0">
                    {{-- Order overview: available whenever any order (paid or unpaid) exists on this table --}}
                    @if($this->tableSheetHasAnyOrders)
                        <x-ui.button
                            wire:click="openOrderInfo({{ $tableEl['id'] }})"
                            variant="secondary"
                            class="w-full justify-center"
                        >
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/>
                                <rect x="9" y="3" width="6" height="4" rx="1"/>
                            </svg>
                            View Order Info
                        </x-ui.button>
                    @endif
                    {{-- Receipt: unpaid orders only (paid orders should not re-print) --}}
                    @if($this->tableSheetHasUnpaidOrders)
                        <x-ui.button
                            wire:click="openReceipt({{ $tableEl['id'] }})"
                            variant="secondary"
                            class="w-full justify-center"
                        >
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                            Print Receipt
                        </x-ui.button>
                    @endif

                    @can('Create Order')
                        <x-ui.button
                            wire:click="acceptOrder({{ $tableEl['id'] }})"
                            class="w-full justify-center"
                            :disabled="$currentStatus && $currentStatus->value !== 'Occupied'"
                        >
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                                <line x1="3" y1="6" x2="21" y2="6"/>
                                <path d="M16 10a4 4 0 0 1-8 0"/>
                            </svg>
                            Accept Order
                        </x-ui.button>
                    @endcan
                    @if($currentStatus && $currentStatus->value !== 'Occupied')
                        <p class="text-xs text-amber-600 text-center">Seat a reservation first to place orders</p>
                    @endif

                    @can('Create Reservation')
                        <x-ui.button
                            wire:click="openReservationModal({{ $tableEl['id'] }})"
                            variant="outline"
                            class="w-full justify-center"
                            :disabled="$currentStatus && $currentStatus->value === 'Occupied'"
                        >
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8" y1="2" x2="8" y2="6"/>
                                <line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                            Add Reservation
                        </x-ui.button>
                    @endcan

                    <p class="text-xs text-gray-400 text-center">Table status managed by reservations</p>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== ORDER INFO MODAL ===== --}}
    <x-table-management.order-info-modal
        :show="$showOrderInfoModal"
        :order-info="$this->orderInfo"
        :element-id="$orderInfoElementId"
    />

    {{-- ===== RECEIPT MODAL ===== --}}
    <x-table-management.receipt-modal
        :show="$showReceiptModal"
        :receipt-data="$receiptData"
    />

    {{-- ===== RESERVATION MODAL ===== --}}
    <x-table-management.reservation-modal
        :show="$showReservationModal"
        :element-id="$reservationElementId"
        :table-name="collect($this->elements)->firstWhere('id', $reservationElementId)['table_name'] ?? 'Table'"
    />

    {{-- ===== RESUME / NEW ORDER CONFIRMATION ===== --}}
    @if($showResumeOrderConfirm)
        <div wire:key="resume-order-confirm" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" wire:click="dismissResumeConfirm"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6" @click.stop>
                <div class="flex items-start gap-4 mb-5">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-amber-100 shrink-0">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Existing Draft Order</h3>
                        <p class="mt-1 text-sm text-gray-500">A draft order already exists for this table — resume it or start a new one?</p>
                    </div>
                </div>
                <div class="flex flex-col gap-2">
                    <x-ui.button wire:click="resumeOrder" class="w-full justify-center">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        Resume Draft Order
                    </x-ui.button>
                    <x-ui.button variant="danger" wire:click="startNewOrder" class="w-full justify-center">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                        Start New Order
                    </x-ui.button>
                    <x-ui.button variant="secondary" wire:click="dismissResumeConfirm" class="w-full justify-center">
                        Cancel
                    </x-ui.button>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== DEPARTURE CONFIRMATION MODAL ===== --}}
    @if($showDepartureConfirm)
        <div wire:key="departure-confirm-modal" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" wire:click="closeDepartureConfirm"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6" @click.stop>
                <h2 class="text-lg font-bold text-gray-900 mb-1">Confirm Departure</h2>
                <p class="text-sm text-gray-500 mb-5">
                    Mark the guest as departed. The table becomes <strong>Reserved</strong> if another reservation is scheduled today, otherwise <strong>Available</strong>.
                </p>
                <p class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 mb-5">
                    Any unpaid orders stay unpaid — settle them from <strong>View Order Info</strong>.
                </p>

                <div class="flex gap-3">
                    <x-ui.button variant="secondary" class="flex-1" wire:click="closeDepartureConfirm">
                        Cancel
                    </x-ui.button>
                    <x-ui.button class="flex-1" wire:click="confirmDeparture">
                        Confirm Departure
                    </x-ui.button>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== CREATE FLOOR PLAN MODAL ===== --}}
    @if($showCreateFloorPlanModal)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            x-data
            @keydown.escape.window="$wire.showCreateFloorPlanModal = false"
        >
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"
                 wire:click="$set('showCreateFloorPlanModal', false)"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md" @click.stop>
                <div class="p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-1">Create Floor Plan</h2>
                    <p class="text-sm text-gray-500 mb-5">Set up a new floor plan for your restaurant.</p>

                    <form wire:submit="createFloorPlan" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Name</label>
                            <input
                                type="text"
                                wire:model="newFloorPlanName"
                                placeholder="e.g. Main Dining Room"
                                autofocus
                                class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                            @error('newFloorPlanName')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Background Image</label>
                            <div
                                x-data="{ uploading: false, progress: 0, hasFile: false }"
                                x-on:livewire-upload-start="uploading = true"
                                x-on:livewire-upload-finish="uploading = false; hasFile = true"
                                x-on:livewire-upload-progress="progress = $event.detail.progress"
                            >
                                <label
                                    class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer hover:border-blue-400 hover:bg-blue-50/50 transition-colors">
                                    <div x-show="!uploading && !hasFile" class="text-center">
                                        <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none"
                                             stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <p class="text-sm text-gray-500">Click to upload <span
                                                class="text-gray-400 text-xs">(PNG, JPG, WebP, SVG)</span></p>
                                    </div>
                                    <div x-show="uploading" class="text-center">
                                        <div class="text-sm text-blue-600 font-medium"
                                             x-text="`Uploading ${progress}%`"></div>
                                    </div>
                                    <div x-show="!uploading && hasFile" class="text-center">
                                        <svg class="w-6 h-6 text-green-500 mx-auto mb-1" fill="none"
                                             stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <p class="text-xs text-green-600 font-medium">Image uploaded</p>
                                    </div>
                                    <input
                                        type="file"
                                        wire:model="newBackgroundImage"
                                        @change="hasFile = false"
                                        accept="image/png,image/jpeg,image/webp,image/svg+xml"
                                        class="hidden"
                                    >
                                </label>
                                <div x-show="uploading" class="mt-2 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-blue-500 transition-all rounded-full"
                                         :style="`width: ${progress}%`"></div>
                                </div>
                            </div>
                            @error('newBackgroundImage')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex gap-3 pt-2">
                            <x-ui.button type="button" variant="secondary" wire:click="$set('showCreateFloorPlanModal', false)" class="flex-1 justify-center">
                                Cancel
                            </x-ui.button>
                            <x-ui.button type="submit" class="flex-1 justify-center">
                                Create Floor Plan
                            </x-ui.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== RENAME FLOOR PLAN MODAL ===== --}}
    @if($showRenameModal)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            @keydown.escape.window="$wire.showRenameModal = false"
        >
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"
                 wire:click="$set('showRenameModal', false)"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm" @click.stop>
                <div class="p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Rename Floor Plan</h2>
                    <form wire:submit="renameFloorPlan" class="space-y-4">
                        <input
                            type="text"
                            wire:model="renameFloorPlanName"
                            autofocus
                            class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                        @error('renameFloorPlanName')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <div class="flex gap-3">
                            <x-ui.button type="button" variant="secondary" wire:click="$set('showRenameModal', false)" class="flex-1 justify-center">
                                Cancel
                            </x-ui.button>
                            <x-ui.button type="submit" class="flex-1 justify-center">
                                Rename
                            </x-ui.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== DISCARD CHANGES CONFIRM ===== --}}
    @if($showDiscardConfirm)
        <div wire:key="discard-confirm-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" wire:click="$set('showDiscardConfirm', false)"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6" @click.stop>
                <div class="flex items-start gap-4 mb-5">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-amber-100 shrink-0">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Unsaved Changes</h3>
                        <p class="mt-1 text-sm text-gray-500">You have unsaved changes. What would you like to do?</p>
                    </div>
                </div>
                <div class="flex flex-col gap-2">
                    <x-ui.button wire:click="saveChanges" class="w-full justify-center">
                        Save Changes
                    </x-ui.button>
                    <x-ui.button variant="danger" wire:click="discardChanges" class="w-full justify-center">
                        Discard Changes
                    </x-ui.button>
                    <x-ui.button variant="secondary" wire:click="$set('showDiscardConfirm', false)" class="w-full justify-center">
                        Keep Editing
                    </x-ui.button>
                </div>
            </div>
        </div>
    @endif

    {{-- Notification toast (dispatched via $wire.dispatch) --}}
    <div
        x-data="{
            show: false,
            type: 'info',
            message: '',
            timeout: null,
        }"
        x-on:notify.window="
            message = $event.detail.message;
            type = $event.detail.type || 'info';
            show = true;
            clearTimeout(timeout);
            timeout = setTimeout(() => show = false, 4000);
        "
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        class="fixed bottom-4 left-1/2 -translate-x-1/2 z-[100] px-4 py-3 rounded-xl shadow-lg text-sm font-medium flex items-center gap-3"
        :class="type === 'error' ? 'bg-red-600 text-white' : 'bg-gray-900 text-white'"
        @click="show = false"
        style="display: none;"
    >
        <template x-if="type === 'error'">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </template>
        <span x-text="message"></span>
    </div>

    {{-- Unsaved changes indicator --}}
    @if($editMode && $hasUnsavedChanges)
        <div
            class="fixed bottom-4 right-4 z-40 flex items-center gap-2 px-3 py-2 bg-amber-50 border border-amber-200 rounded-xl text-xs font-medium text-amber-700 shadow-sm">
            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
            Unsaved changes
        </div>
    @endif
</div>
