<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Table Management - {{ config('app.name', 'Laravel') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,900&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            /* Desktop (≥1280px): original fixed 7-column layout */
            .table-grid {
                grid-template-columns: repeat(7, 200px);
            }
            .table-card {
                width: 200px;
                height: 200px;
                box-sizing: border-box;
            }
            /* Large tablet / small desktop (768px–1279px): 4 fluid columns */
            @media (max-width: 1279px) {
                .table-grid {
                    grid-template-columns: repeat(4, 1fr);
                }
                .table-card {
                    width: 100%;
                    height: auto;
                    aspect-ratio: 1;
                    box-sizing: border-box;
                }
            }
            /* Small tablet (640px–767px): 3 fluid columns */
            @media (max-width: 767px) {
                .table-grid {
                    grid-template-columns: repeat(3, 1fr);
                }
            }
            /* Mobile (<640px): 2 fluid columns, smaller icon + tighter card gap */
            @media (max-width: 639px) {
                .table-grid {
                    grid-template-columns: repeat(2, 1fr);
                }
                .table-card {
                    gap: 0.4rem !important;
                }
                .table-card .flex-1 svg {
                    width: 44px;
                    height: 44px;
                }
            }

            /* ── Sheet ──────────────────────────────────────────── */
            .sheet-overlay {
                opacity: 0; pointer-events: none;
                transition: opacity 0.3s ease;
            }
            .sheet-overlay.open { opacity: 1; pointer-events: auto; }
            .sheet-panel {
                transform: translateX(100%);
                transition: transform 0.35s cubic-bezier(0.32, 0.72, 0, 1);
                height: 100vh; height: 100dvh;
            }
            .sheet-panel.open { transform: translateX(0); }

            /* ── Form inputs ──────────────────────────────────── */
            .sheet-input, .sheet-select {
                width: 100%;
                border: 1px solid #e5e7eb; border-radius: 0.5rem;
                padding: 0.5rem 0.75rem; font-size: 0.875rem;
                color: #111827; background: #fff; outline: none;
                transition: border-color 0.15s, box-shadow 0.15s;
                font-family: inherit;
            }
            .sheet-input:focus, .sheet-select:focus {
                border-color: #309bcf;
                box-shadow: 0 0 0 3px rgba(48,155,207,0.2);
            }
            .sheet-input::placeholder { color: #9ca3af; }
            .sheet-input.error, .sheet-select.error { border-color: #dc2626; }

            /* ── Status toggle pills ──────────────────────────── */
            .status-radio { display: none; }
            .status-label {
                display: inline-flex; align-items: center; gap: 0.375rem;
                padding: 0.375rem 0.875rem; border-radius: 9999px;
                font-size: 0.8125rem; font-weight: 600;
                border: 1px solid #e5e7eb; background: #fff; color: #6b7280;
                cursor: pointer; transition: border-color .15s, background .15s, color .15s;
                user-select: none;
            }
            .status-radio:checked + .status-label {
                border-color: transparent; color: #fff;
            }
            #status-free:checked   + .status-label { background: #2e7d5e; border-color: #2e7d5e; }
            #status-occupied:checked + .status-label { background: #7a4030; border-color: #7a4030; }
            #status-reserved:checked + .status-label { background: #006ead; border-color: #006ead; }

            /* ── Waiter toggle ────────────────────────────────── */
            .waiter-toggle {
                position: relative; display: inline-flex;
                width: 2.75rem; height: 1.5rem; cursor: pointer;
            }
            .waiter-toggle input { opacity: 0; width: 0; height: 0; }
            .waiter-slider {
                position: absolute; inset: 0; border-radius: 9999px;
                background: #d1d5db; transition: background 0.2s;
            }
            .waiter-slider::before {
                content: ''; position: absolute;
                width: 1.125rem; height: 1.125rem; border-radius: 50%;
                background: white; top: 0.1875rem; left: 0.1875rem;
                transition: transform 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,.25);
            }
            .waiter-toggle input:checked + .waiter-slider { background: #b07a20; }
            .waiter-toggle input:checked + .waiter-slider::before { transform: translateX(1.25rem); }
        </style>
    </head>
    <body class="font-sans antialiased min-h-screen bg-[#eaf4fa]">
        @include('layouts.guest-navigation')

        @php
        $tables = [
            ['id' => 'A1',  'seats' => 4, 'status' => 'reserved', 'waiter' => false],
            ['id' => 'A2',  'seats' => 6, 'status' => 'occupied', 'waiter' => false],
            ['id' => 'A3',  'seats' => 4, 'status' => 'occupied', 'waiter' => false],
            ['id' => 'A4',  'seats' => 4, 'status' => 'reserved', 'waiter' => false],
            ['id' => 'A5',  'seats' => 6, 'status' => 'occupied', 'waiter' => false],
            ['id' => 'A6',  'seats' => 4, 'status' => 'free',     'waiter' => true ],
            ['id' => 'A7',  'seats' => 4, 'status' => 'reserved', 'waiter' => false],
            ['id' => 'A8',  'seats' => 4, 'status' => 'free',     'waiter' => false],
            ['id' => 'A9',  'seats' => 4, 'status' => 'reserved', 'waiter' => false],
            ['id' => 'A10', 'seats' => 4, 'status' => 'free',     'waiter' => true ],
            ['id' => 'A11', 'seats' => 4, 'status' => 'reserved', 'waiter' => false],
            ['id' => 'A12', 'seats' => 4, 'status' => 'reserved', 'waiter' => false],
            ['id' => 'A13', 'seats' => 4, 'status' => 'reserved', 'waiter' => false],
            ['id' => 'A14', 'seats' => 4, 'status' => 'reserved', 'waiter' => false],
            ['id' => 'A15', 'seats' => 6, 'status' => 'reserved', 'waiter' => false],
            ['id' => 'A16', 'seats' => 4, 'status' => 'occupied', 'waiter' => false],
            ['id' => 'A17', 'seats' => 4, 'status' => 'occupied', 'waiter' => false],
            ['id' => 'A18', 'seats' => 6, 'status' => 'reserved', 'waiter' => false],
            ['id' => 'A19', 'seats' => 4, 'status' => 'occupied', 'waiter' => false],
            ['id' => 'A20', 'seats' => 4, 'status' => 'occupied', 'waiter' => false],
            ['id' => 'A21', 'seats' => 4, 'status' => 'occupied', 'waiter' => false],
            ['id' => 'A22', 'seats' => 4, 'status' => 'occupied', 'waiter' => false],
            ['id' => 'A23', 'seats' => 4, 'status' => 'occupied', 'waiter' => true ],
            ['id' => 'A24', 'seats' => 6, 'status' => 'free',     'waiter' => false],
            ['id' => 'A25', 'seats' => 4, 'status' => 'reserved', 'waiter' => false],
            ['id' => 'A26', 'seats' => 4, 'status' => 'occupied', 'waiter' => false],
            ['id' => 'A27', 'seats' => 4, 'status' => 'occupied', 'waiter' => false],
            ['id' => 'A28', 'seats' => 4, 'status' => 'occupied', 'waiter' => false],
        ];
        @endphp

        <div class="max-w-screen-xl mx-auto px-6 py-8 flex flex-col gap-8">

            <!-- ── Page header ──────────────────────────────── -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-3">
                    <h2 class="m-0 text-lg font-bold text-primary">Floor Plan</h2>
                    <button onclick="openAddSheet()"
                            class="inline-flex items-center gap-2 bg-molveno-blue-500 hover:bg-molveno-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-lg shadow-sm transition-colors duration-150">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 5v14M5 12h14"/>
                        </svg>
                        Add Table
                    </button>
                </div>
                <div class="flex flex-wrap items-center gap-x-4 gap-y-2 sm:gap-5">
                    <div class="flex items-center gap-1.5">
                        <div class="w-3.5 h-3.5 rounded-sm" style="background-color: #2e7d5e;"></div>
                        <span class="text-[13px] font-medium text-gray-700">Available</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <div class="w-3.5 h-3.5 rounded-sm" style="background-color: #7a4030;"></div>
                        <span class="text-[13px] font-medium text-gray-700">Occupied</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <div class="w-3.5 h-3.5 rounded-sm" style="background-color: #006ead;"></div>
                        <span class="text-[13px] font-medium text-gray-700">Reserved</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <div class="w-3.5 h-3.5 rounded-full" style="background-color: #b07a20;"></div>
                        <span class="text-[13px] font-medium text-gray-700">Waiter needed</span>
                    </div>
                </div>
            </div>

            <!-- ── Table grid (rendered by JS) ──────────────── -->
            <div id="table-grid" class="grid gap-4 justify-center table-grid"></div>

        </div>

        <!-- ── Sheet overlay ────────────────────────────────────── -->
        <div id="sheet-overlay"
             class="sheet-overlay fixed inset-0 bg-black/40 z-40"
             onclick="closeSheet()"></div>

        <!-- ── Sheet panel ──────────────────────────────────────── -->
        <div id="sheet-panel"
             class="sheet-panel fixed top-0 right-0 z-50 w-full max-w-md bg-white shadow-2xl flex flex-col">

            <!-- Header -->
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 shrink-0">
                <h2 id="sheet-title" class="text-base font-bold text-gray-900">Add Table</h2>
                <button onclick="closeSheet()"
                        class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2.5" stroke-linecap="round">
                        <path d="M18 6 6 18M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Body -->
            <div class="flex-1 overflow-y-auto px-5 py-5 flex flex-col gap-5">

                <!-- Table ID -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-semibold text-gray-700" for="field-id">
                        Table ID <span class="text-red-400">*</span>
                    </label>
                    <input id="field-id" type="text" class="sheet-input"
                           placeholder="e.g. A1, B3, Terrace-2">
                    <p id="id-error" class="text-xs text-red-600 hidden">Table ID is required.</p>
                </div>

                <!-- Seats -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-semibold text-gray-700" for="field-seats">Seats</label>
                    <select id="field-seats" class="sheet-select">
                        <option value="2">2 persons</option>
                        <option value="4" selected>4 persons</option>
                        <option value="6">6 persons</option>
                        <option value="8">8 persons</option>
                    </select>
                </div>

                <!-- Status -->
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-semibold text-gray-700">Status</label>
                    <div class="flex flex-wrap gap-2">
                        <div>
                            <input type="radio" name="status" id="status-free" class="status-radio" value="free" checked>
                            <label for="status-free" class="status-label">
                                <span class="w-2 h-2 rounded-full bg-current opacity-80"></span>
                                Available
                            </label>
                        </div>
                        <div>
                            <input type="radio" name="status" id="status-occupied" class="status-radio" value="occupied">
                            <label for="status-occupied" class="status-label">
                                <span class="w-2 h-2 rounded-full bg-current opacity-80"></span>
                                Occupied
                            </label>
                        </div>
                        <div>
                            <input type="radio" name="status" id="status-reserved" class="status-radio" value="reserved">
                            <label for="status-reserved" class="status-label">
                                <span class="w-2 h-2 rounded-full bg-current opacity-80"></span>
                                Reserved
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Waiter needed -->
                <div class="flex items-center justify-between py-1">
                    <div>
                        <p class="text-sm font-semibold text-gray-700">Waiter needed</p>
                        <p class="text-xs text-gray-400 mt-0.5">Flag this table for immediate attention.</p>
                    </div>
                    <label class="waiter-toggle">
                        <input type="checkbox" id="field-waiter">
                        <span class="waiter-slider"></span>
                    </label>
                </div>

                <!-- Table actions (edit mode only) -->
                <div id="sheet-actions" class="flex flex-col gap-2" style="display:none">
                    <hr class="border-gray-100">
                    <p class="text-sm font-semibold text-gray-700">Actions</p>
                    <div class="flex flex-col gap-2">
                        <a id="btn-check-orders" href="#"
                           class="flex items-center justify-center gap-2 w-full px-4 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/>
                                <rect x="9" y="3" width="6" height="4" rx="1"/>
                                <path d="M9 12h6M9 16h4"/>
                            </svg>
                            Check orders for this table
                        </a>
                        <a id="btn-new-order" href="#"
                           class="flex items-center justify-center gap-2 w-full px-4 py-2.5 text-sm font-semibold text-white bg-molveno-blue-500 hover:bg-molveno-blue-700 rounded-lg shadow-sm transition-colors">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 5v14M5 12h14"/>
                            </svg>
                            Take new order
                        </a>
                    </div>
                </div>

            </div>

            <!-- Footer -->
            <div class="px-5 py-4 border-t border-gray-100 flex items-center gap-3 bg-gray-50 shrink-0">
                <button id="sheet-delete-btn"
                        class="hidden mr-auto px-4 py-2 text-sm font-semibold text-red-600 bg-white border border-red-200 rounded-lg hover:bg-red-50 transition-colors"
                        onclick="confirmDelete()">
                    Delete Table
                </button>
                <button onclick="closeSheet()"
                        class="ml-auto px-4 py-2 text-sm font-semibold text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button id="sheet-save-btn"
                        onclick="saveTable()"
                        class="px-5 py-2 text-sm font-semibold text-white bg-molveno-blue-500 hover:bg-molveno-blue-700 rounded-lg shadow-sm transition-colors">
                    Save Table
                </button>
            </div>
        </div>

        <!-- ── Delete confirmation modal ───────────────────────── -->
        <div id="delete-overlay"
             class="sheet-overlay fixed inset-0 bg-black/40 z-40 flex items-center justify-center"
             onclick="closeDelete()"></div>
        <div id="delete-modal"
             class="fixed z-50 bg-white rounded-2xl shadow-2xl p-6 w-full max-w-sm mx-4
                    top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2
                    transition-all duration-200 scale-95 opacity-0 pointer-events-none">
            <div class="flex items-start gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#dc2626"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6"/>
                        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                        <path d="M10 11v6M14 11v6"/>
                        <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-900">Delete Table</h3>
                    <p id="delete-msg" class="text-sm text-gray-500 mt-1"></p>
                </div>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeDelete()"
                        class="px-3 py-1.5 text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                    Cancel
                </button>
                <button type="button" id="delete-confirm-btn"
                        class="px-3 py-1.5 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors">
                    Delete
                </button>
            </div>
        </div>

        <script>
            // ── Data ─────────────────────────────────────────────────
            let tables = @json($tables);
            let editingIndex = null;

            // ── Render ───────────────────────────────────────────────
            const cardBgMap = {
                free:     '#C1E1C1',
                occupied: '#AEC6CF',
                reserved: '#FF6961',
            };

            function renderGrid() {
                const grid = document.getElementById('table-grid');
                grid.innerHTML = '';

                tables.forEach((table, index) => {
                    const cardBg   = cardBgMap[table.status] ?? '#FF6961';
                    const waiterBg = table.waiter ? 'gold' : 'white';

                    const card = document.createElement('div');
                    card.className = 'flex flex-col gap-5 p-4 rounded-2xl cursor-pointer transition-all duration-150 shadow-md hover:shadow-xl hover:-translate-y-1 select-none table-card';
                    card.style.backgroundColor = cardBg;
                    card.onclick = () => openEditSheet(index);

                    card.innerHTML = `
                        <div class="flex justify-between items-start shrink-0">
                            <span style="text-shadow: 1px 1px #000000;" class="text-white font-bold text-lg leading-none">
                                ${table.seats} persons
                            </span>
                            <div class="w-[26px] h-[26px] rounded-full flex items-center justify-center shrink-0 shadow-sm p-2 border border-black/80 shadow-black"
                                 style="background-color: ${waiterBg}">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="black">
                                    <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1 flex items-center justify-center">
                            <svg width="64" height="64" viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="22" y="2"  width="16" height="10" rx="3" fill="white" fill-opacity="0.9"/>
                                <rect x="22" y="48" width="16" height="10" rx="3" fill="white" fill-opacity="0.9"/>
                                <rect x="2"  y="22" width="10" height="16" rx="3" fill="white" fill-opacity="0.9"/>
                                <rect x="48" y="22" width="10" height="16" rx="3" fill="white" fill-opacity="0.9"/>
                                <circle cx="30" cy="30" r="14" fill="white" fill-opacity="0.8"/>
                            </svg>
                        </div>
                        <div class="shrink-0">
                            <span style="text-shadow: 1px 1px #000000;" class="text-white font-black text-[15px] leading-none">${table.id}</span>
                        </div>
                    `;

                    grid.appendChild(card);
                });
            }

            renderGrid();

            // ── Sheet open / close ───────────────────────────────────
            function openSheet() {
                document.getElementById('sheet-overlay').classList.add('open');
                document.getElementById('sheet-panel').classList.add('open');
                document.body.style.overflow = 'hidden';
            }

            function closeSheet() {
                document.getElementById('sheet-overlay').classList.remove('open');
                document.getElementById('sheet-panel').classList.remove('open');
                document.body.style.overflow = '';
                document.getElementById('id-error').classList.add('hidden');
                document.getElementById('field-id').classList.remove('error');
                editingIndex = null;
            }

            document.addEventListener('keydown', e => { if (e.key === 'Escape') closeSheet(); });

            // ── Add mode ─────────────────────────────────────────────
            function openAddSheet() {
                editingIndex = null;
                document.getElementById('sheet-title').textContent         = 'Add Table';
                document.getElementById('sheet-save-btn').textContent      = 'Save Table';
                document.getElementById('sheet-delete-btn').classList.add('hidden');
                document.getElementById('sheet-actions').style.display = 'none';

                document.getElementById('field-id').value                  = '';
                document.getElementById('field-seats').value               = '4';
                document.getElementById('status-free').checked             = true;
                document.getElementById('field-waiter').checked            = false;

                openSheet();
                document.getElementById('field-id').focus();
            }

            // ── Edit mode ─────────────────────────────────────────────
            function openEditSheet(index) {
                const table = tables[index];
                editingIndex = index;

                document.getElementById('sheet-title').textContent         = `Edit Table ${table.id}`;
                document.getElementById('sheet-save-btn').textContent      = 'Update Table';
                document.getElementById('sheet-delete-btn').classList.remove('hidden');

                document.getElementById('field-id').value                  = table.id;
                document.getElementById('field-seats').value               = String(table.seats);
                document.querySelector(`input[name="status"][value="${table.status}"]`).checked = true;
                document.getElementById('field-waiter').checked            = table.waiter;

                document.getElementById('btn-check-orders').href = `/orders?table=${encodeURIComponent(table.id)}`;
                document.getElementById('btn-new-order').href    = `/ordermanagement`;
                document.getElementById('sheet-actions').style.display = 'flex';

                openSheet();
            }

            // ── Save (add / update) ───────────────────────────────────
            function saveTable() {
                const idField = document.getElementById('field-id');
                const id      = idField.value.trim();

                // Validate
                if (!id) {
                    idField.classList.add('error');
                    document.getElementById('id-error').classList.remove('hidden');
                    idField.focus();
                    return;
                }
                idField.classList.remove('error');
                document.getElementById('id-error').classList.add('hidden');

                const entry = {
                    id:     id,
                    seats:  parseInt(document.getElementById('field-seats').value, 10),
                    status: document.querySelector('input[name="status"]:checked').value,
                    waiter: document.getElementById('field-waiter').checked,
                };

                if (editingIndex === null) {
                    tables.push(entry);
                } else {
                    tables[editingIndex] = entry;
                }

                renderGrid();
                closeSheet();
            }

            // ── Delete ────────────────────────────────────────────────
            function confirmDelete() {
                const table = tables[editingIndex];
                document.getElementById('delete-msg').textContent =
                    `Are you sure you want to remove table "${table.id}"? This action cannot be undone.`;

                document.getElementById('delete-confirm-btn').onclick = () => {
                    tables.splice(editingIndex, 1);
                    renderGrid();
                    closeDelete();
                    closeSheet();
                };

                const overlay = document.getElementById('delete-overlay');
                const modal   = document.getElementById('delete-modal');
                overlay.classList.add('open');
                modal.classList.remove('scale-95', 'opacity-0', 'pointer-events-none');
                modal.classList.add('scale-100', 'opacity-100');
            }

            function closeDelete() {
                const overlay = document.getElementById('delete-overlay');
                const modal   = document.getElementById('delete-modal');
                overlay.classList.remove('open');
                modal.classList.remove('scale-100', 'opacity-100');
                modal.classList.add('scale-95', 'opacity-0', 'pointer-events-none');
            }
        </script>
    </body>
</html>
