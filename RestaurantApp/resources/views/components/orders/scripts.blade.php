<script>
    const KITCHEN_MARK_READY_URL = '{{ rtrim(route('kitchen-orders.dish.ready', ['orderItem' => '__ID__']), '') }}';
    const KITCHEN_COMPLETE_URL   = '{{ rtrim(route('kitchen-orders.order.complete', ['order' => '__ID__']), '') }}';
    const KITCHEN_DELETE_URL     = '{{ rtrim(route('kitchen-orders.order.delete', ['order' => '__ID__']), '') }}';
    const KITCHEN_POLL_URL       = '{{ route('kitchen-orders.poll') }}';
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content;
    const POLL_INTERVAL_MS       = 5000; // Poll every 5 seconds
    const KITCHEN_TAB_ACTIVE_CLASSES = ['bg-molveno-blue-500', 'border-molveno-blue-500', 'text-white'];
    const KITCHEN_TAB_INACTIVE_CLASSES = ['bg-white', 'border-gray-200', 'text-gray-600', 'hover:border-molveno-blue-300', 'hover:text-molveno-blue-700'];
    const KITCHEN_TAB_COUNT_ACTIVE_CLASSES = ['bg-white/25', 'text-white'];
    const KITCHEN_TAB_COUNT_INACTIVE_CLASSES = ['bg-gray-100', 'text-gray-500'];

    document.addEventListener('DOMContentLoaded', () => {
        const kitchenPanel = document.getElementById('kitchen-panel');
        const scope = kitchenPanel || document;
        const defaultTab = scope.querySelector('button[data-default="true"]') || scope.querySelector('button[data-tab]');
        if (defaultTab) {
            kitchenSwitchTab(defaultTab);
        }
        scope.querySelectorAll('.mark-ready-btn').forEach(btn => {
            kitchenSetMarkReadyAppearance(btn, btn.dataset.dishStatus === 'ready' ? 'ready' : 'pending');
        });
        scope.querySelectorAll('.order-card').forEach(card => {
            kitchenUpdateOrderSendState(card);
            kitchenSyncCardVisualState(card);
        });
        document.addEventListener('click', kitchenHandleActionClick);
    });

    const KITCHEN_CARD_COMPLETED_CLASSES = ['bg-emerald-50', 'border-emerald-200', 'shadow-md'];
    const KITCHEN_CARD_DEFAULT_CLASSES = ['bg-white', 'border-gray-200', 'shadow-sm'];

    function kitchenSwitchTab(btn) {
        const tab = btn.dataset.tab;
        const kitchenPanel = document.getElementById('kitchen-panel');
        const scope = kitchenPanel || document;
        scope.querySelectorAll('button[data-tab]').forEach(b => kitchenSetTabAppearance(b, b === btn));
        let visible = 0;
        scope.querySelectorAll('.order-card').forEach(card => {
            const overall = card.dataset.overall;
            const type    = card.dataset.type;
            const show    = tab === 'all'          ? true
                          : tab === 'active'       ? overall !== 'completed'
                          : tab === 'completed'    ? overall === 'completed'
                          : tab === 'restaurant'   ? type === 'restaurant'
                          : tab === 'room_service' ? type === 'room_service'
                          : true;
            card.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        const emptyEl = scope.querySelector('#kitchen-empty') || document.getElementById('no-orders');
        if (emptyEl) emptyEl.classList.toggle('hidden', visible > 0);
    }

    function kitchenHandleActionClick(event) {
        const kitchenPanel = document.getElementById('kitchen-panel');
        if (!kitchenPanel || !kitchenPanel.contains(event.target)) return;

        const target = event.target instanceof Element ? event.target : event.target.parentElement;
        if (!target) return;

        const markBtn = target.closest('.mark-ready-btn');
        if (markBtn) {
            event.preventDefault();
            markDishReady(markBtn);
            return;
        }

        const sendBtn = target.closest('.order-send-btn');
        if (sendBtn) {
            event.preventDefault();
            if (!sendBtn.disabled) {
                kitchenCompleteOrder(sendBtn);
            }
            return;
        }

        const deleteBtn = target.closest('.delete-order-btn');
        if (deleteBtn) {
            event.preventDefault();
            kitchenDeleteOrder(deleteBtn);
        }
    }

    function markDishReady(button) {
        const nextState = button.dataset.dishStatus === 'ready' ? 'pending' : 'ready';
        kitchenSetMarkReadyAppearance(button, nextState);
        button.dataset.dishStatus = nextState;

        const label = button.querySelector('.mark-ready-label');
        if (label) label.textContent = nextState === 'ready' ? 'Ready' : 'Mark Ready';

        const dishAction = button.closest('.dish-action');
        kitchenUpdateDishVisualState(dishAction, nextState);

        kitchenUpdateOrderSendState(button.closest('.order-card'));

        const itemId = dishAction?.dataset.itemId;
        if (itemId) {
            fetch(KITCHEN_MARK_READY_URL.replace('__ID__', itemId), {
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
            }).catch(() => {});
        }
    }

    function kitchenCompleteOrder(button) {
        kitchenSetSendButtonState(button, 'sent');
        const card = button.closest('.order-card');
        if (card) {
            card.dataset.overall = 'completed';
            kitchenHideOrderActions(card);
            markAllDishesServed(card);
            kitchenSyncCardVisualState(card);
            kitchenUpdateFilterCounts('completed');
        }

        const dbId = card?.dataset.orderDbId;
        if (dbId) {
            fetch(KITCHEN_COMPLETE_URL.replace('__ID__', dbId), {
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
            }).catch(() => {});
        }
    }

    function kitchenDeleteOrder(button) {
        const card = button.closest('.order-card');
        const dbId = card?.dataset.orderDbId;
        if (!dbId) return;

        if (!confirm('Are you sure you want to delete this order?')) return;

        const wasCompleted = card?.dataset.overall === 'completed';

        if (card) {
            card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            card.style.opacity = '0';
            card.style.transform = 'scale(0.95)';
            setTimeout(() => card.remove(), 300);
        }

        kitchenUpdateFilterCounts(wasCompleted ? 'remove_completed' : 'remove_active');

        fetch(KITCHEN_DELETE_URL.replace('__ID__', dbId), {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
        }).catch(() => {});
    }

    function kitchenUpdateFilterCounts(direction) {
        const kitchenPanel = document.getElementById('kitchen-panel') || document;
        const activeCountEl = kitchenPanel.querySelector('button[data-count-type="active"] span');
        const completedCountEl = kitchenPanel.querySelector('button[data-count-type="completed"] span');
        const allCountEl = kitchenPanel.querySelector('button[data-tab="all"] span');

        if (direction === 'completed') {
            if (activeCountEl) {
                const current = parseInt(activeCountEl.textContent, 10) || 0;
                activeCountEl.textContent = Math.max(0, current - 1);
            }
            if (completedCountEl) {
                const current = parseInt(completedCountEl.textContent, 10) || 0;
                completedCountEl.textContent = current + 1;
            }
        } else if (direction === 'remove_active') {
            if (activeCountEl) {
                const current = parseInt(activeCountEl.textContent, 10) || 0;
                activeCountEl.textContent = Math.max(0, current - 1);
            }
        } else if (direction === 'remove_completed') {
            if (completedCountEl) {
                const current = parseInt(completedCountEl.textContent, 10) || 0;
                completedCountEl.textContent = Math.max(0, current - 1);
            }
        }

        // Update "All" count to match sum of active + completed
        if (allCountEl && activeCountEl && completedCountEl) {
            const active = parseInt(activeCountEl.textContent, 10) || 0;
            const completed = parseInt(completedCountEl.textContent, 10) || 0;
            allCountEl.textContent = active + completed;
        }
    }

    function markAllDishesServed(card) {
        // Update every dish row to "served" state
        card.querySelectorAll('[data-dish-wrapper]').forEach(wrapper => {
            wrapper.dataset.dishStatus = 'served';

            // Update status dot
            const dot = wrapper.querySelector('[data-role="status-dot"]');
            if (dot) kitchenSetStatusDotAppearance(dot, 'served');

            // Replace mark-ready button with served badge
            const action = wrapper.querySelector('.dish-action');
            if (action) {
                action.dataset.dishStatus = 'served';
                action.innerHTML = `
                    <div class="inline-flex items-center gap-1 text-xs font-semibold text-green-600 bg-green-50 border border-green-200 px-2.5 py-1.5 rounded-lg">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg>
                        Served
                    </div>`;
            }
        });
    }

    function kitchenUpdateOrderSendState(card) {
        if (!card) return;

        const dishActions = Array.from(card.querySelectorAll('.dish-action'));
        const hasPending = dishActions.some(action => action.dataset.dishStatus === 'pending');
        const allReadyOrServed = dishActions.length > 0 && dishActions.every(action => ['ready', 'served'].includes(action.dataset.dishStatus));

        const sendBtn = card.querySelector('.order-send-btn');

        if (sendBtn) {
            const currentState = card.dataset.overall === 'completed' ? 'sent'
                                : allReadyOrServed ? 'ready'
                                : 'disabled';
            kitchenSetSendButtonState(sendBtn, currentState);
        }

        if (card.dataset.overall !== 'completed') {
            card.dataset.overall = allReadyOrServed ? 'ready' : (hasPending ? 'pending' : card.dataset.overall);
        }

        if (card.dataset.overall === 'completed') {
            kitchenHideOrderActions(card);
        } else {
            kitchenShowOrderActions(card);
        }

        kitchenSyncCardVisualState(card);
    }

    function kitchenSetTabAppearance(button, isActive) {
        if (!button) return;
        button.classList.remove(...KITCHEN_TAB_ACTIVE_CLASSES, ...KITCHEN_TAB_INACTIVE_CLASSES);
        button.classList.add(...(isActive ? KITCHEN_TAB_ACTIVE_CLASSES : KITCHEN_TAB_INACTIVE_CLASSES));

        const count = button.querySelector('span');
        if (count) {
            count.classList.remove(...KITCHEN_TAB_COUNT_ACTIVE_CLASSES, ...KITCHEN_TAB_COUNT_INACTIVE_CLASSES);
            count.classList.add(...(isActive ? KITCHEN_TAB_COUNT_ACTIVE_CLASSES : KITCHEN_TAB_COUNT_INACTIVE_CLASSES));
        }
    }

    function kitchenUpdateDishVisualState(dishAction, state) {
        if (!dishAction) return;
        dishAction.dataset.dishStatus = state;

        const wrapper = dishAction.closest('[data-dish-wrapper]');
        if (wrapper) {
            wrapper.dataset.dishStatus = state;
            const dot = wrapper.querySelector('[data-role="status-dot"]');
            if (dot) {
                kitchenSetStatusDotAppearance(dot, state);
            }
        }
    }

    function kitchenSetMarkReadyAppearance(button, state) {
        if (!button) return;
        const pending = kitchenParseClasses(button.dataset.classPending);
        const ready = kitchenParseClasses(button.dataset.classReady);
        button.classList.remove(...pending, ...ready);

        if (state === 'ready') {
            button.classList.add(...ready);
        } else {
            button.classList.add(...pending);
        }
    }

    function kitchenSetStatusDotAppearance(dot, state) {
        if (!dot) return;
        const pending = kitchenParseClasses(dot.dataset.classPending);
        const ready = kitchenParseClasses(dot.dataset.classReady);
        const served = kitchenParseClasses(dot.dataset.classServed);
        dot.classList.remove(...pending, ...ready, ...served);

        if (state === 'ready') {
            dot.classList.add(...ready);
        } else if (state === 'served') {
            dot.classList.add(...served);
        } else {
            dot.classList.add(...pending);
        }

        dot.dataset.status = state;
    }

    function kitchenSetSendButtonState(button, state) {
        if (!button) return;
        const disabled = kitchenParseClasses(button.dataset.classDisabled);
        const ready = kitchenParseClasses(button.dataset.classReady);
        const sent = kitchenParseClasses(button.dataset.classSent);
        button.classList.remove(...disabled, ...ready, ...sent);

        if (state === 'sent') {
            button.classList.add(...sent);
            button.disabled = true;
        } else if (state === 'ready') {
            button.classList.add(...ready);
            button.disabled = false;
        } else {
            button.classList.add(...disabled);
            button.disabled = true;
        }

        button.dataset.sendState = state;
        const label = button.querySelector('.order-send-label');
        if (label) label.textContent = state === 'sent' ? 'Sent' : 'Send Out';
    }

    function kitchenSyncCardVisualState(card) {
        if (!card) return;
        card.classList.remove(...KITCHEN_CARD_COMPLETED_CLASSES, ...KITCHEN_CARD_DEFAULT_CLASSES);

        const isCompleted = card.dataset.overall === 'completed';

        if (isCompleted) {
            card.classList.add(...KITCHEN_CARD_COMPLETED_CLASSES);
        } else {
            card.classList.add(...KITCHEN_CARD_DEFAULT_CLASSES);
        }

        // Sync header background & summary text
        const header = card.querySelector(':scope > div:first-child');
        if (header) {
            header.classList.remove('bg-sky-700', 'bg-amber-600', 'bg-emerald-600');
            header.classList.add(isCompleted ? 'bg-emerald-600' : (card.dataset.overall === 'ready' ? 'bg-amber-600' : 'bg-sky-700'));

            // Update the summary line (second <p> in the right side)
            const rightP = header.querySelectorAll('.text-right p');
            if (rightP.length >= 2) {
                rightP[1].textContent = isCompleted ? 'All served' : rightP[1].textContent;
            }
        }

        // Sync footer status label
        const footerStatus = card.querySelector('.px-4.py-3.bg-gray-50 .inline-flex.items-center.gap-1.text-xs.font-semibold');
        if (footerStatus) {
            footerStatus.classList.remove('text-green-600', 'text-amber-600', 'text-gray-400');
            if (isCompleted) {
                footerStatus.classList.add('text-green-600');
                footerStatus.innerHTML = `<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg> Completed`;
            } else if (card.dataset.overall === 'ready') {
                footerStatus.classList.add('text-amber-600');
                footerStatus.innerHTML = `<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg> Ready to serve`;
            } else {
                footerStatus.classList.add('text-gray-400');
                footerStatus.innerHTML = `<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 1.5"/></svg> Preparing`;
            }
        }
    }

    function kitchenHideOrderActions(card) {
        const actions = card.querySelector('[data-role="order-actions"]');
        if (actions) actions.classList.add('hidden');
    }

    function kitchenShowOrderActions(card) {
        const actions = card.querySelector('[data-role="order-actions"]');
        if (actions) actions.classList.remove('hidden');
    }

    function kitchenParseClasses(value) {
        return (value || '').split(' ').filter(Boolean);
    }

    // ── Polling for new orders ─────────────────────────────
    let pollTimer = null;
    let isPolling = false;

    function startPolling() {
        if (pollTimer) return;
        pollTimer = setInterval(pollForOrders, POLL_INTERVAL_MS);
    }

    function stopPolling() {
        if (pollTimer) {
            clearInterval(pollTimer);
            pollTimer = null;
        }
    }

    async function pollForOrders() {
        if (isPolling) return;
        isPolling = true;

        try {
            const response = await fetch(KITCHEN_POLL_URL, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
            });

            if (!response.ok) throw new Error('Poll failed');

            const data = await response.json();
            mergeOrders(data.orders);
            kitchenUpdateSummaryCounts(data.totalPending, data.totalReady, data.countCompleted);
            kitchenUpdateTabCounts(data.countActive, data.countCompleted);
            kitchenApplyCurrentFilter();
        } catch (e) {
            // Silently fail - don't disrupt the user on network errors
        } finally {
            isPolling = false;
        }
    }

    function mergeOrders(orders) {
        const grid = document.querySelector('.order-grid');
        if (!grid) return;

        const existingIds = new Set();
        document.querySelectorAll('.order-card').forEach(card => {
            existingIds.add(card.dataset.orderId);
        });

        orders.forEach(order => {
            if (!existingIds.has(order.id)) {
                // New order - add to grid
                const cardHtml = buildOrderCardHtml(order);
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = cardHtml;
                const newCard = tempDiv.firstElementChild;

                // Initialize the new card
                newCard.querySelectorAll('.mark-ready-btn').forEach(btn => {
                    kitchenSetMarkReadyAppearance(btn, btn.dataset.dishStatus === 'ready' ? 'ready' : 'pending');
                });
                kitchenUpdateOrderSendState(newCard);
                kitchenSyncCardVisualState(newCard);

                // Add to beginning of grid (latest orders first)
                grid.insertBefore(newCard, grid.firstChild);

                // Flash animation for new order
                newCard.style.animation = 'kds-pulse-new 0.6s ease';
            }
        });
    }

    function buildOrderCardHtml(order) {
        const isCompleted = order.overall === 'completed';
        const isReady = order.overall === 'ready';
        const headerClass = isCompleted ? 'bg-emerald-600' : (isReady ? 'bg-amber-600' : 'bg-sky-700');
        const cardStateClass = isCompleted
            ? 'bg-emerald-50 border-emerald-200 shadow-md'
            : 'bg-white border-gray-200 shadow-sm';
        const overallClass = isCompleted ? 'text-green-600' : (isReady ? 'text-amber-600' : 'text-gray-400');

        const sendClassMap = {
            disabled: 'bg-slate-50 border-slate-200 text-slate-400 cursor-not-allowed shadow-none',
            ready: 'bg-gradient-to-r from-sky-400 to-blue-600 text-white border-transparent shadow-lg cursor-pointer',
            sent: 'bg-emerald-600 border-emerald-600 text-white cursor-default shadow-md',
        };

        const canSend = order.cnt_pending === 0 && order.cnt_ready > 0 && !isCompleted;
        const sendState = isCompleted ? 'sent' : (canSend ? 'ready' : 'disabled');

        const dotClassMap = { pending: 'bg-gray-300', ready: 'bg-amber-400', served: 'bg-green-400' };
        const markPendingClasses = 'border border-dashed border-slate-300 bg-slate-50 text-slate-500 hover:bg-slate-100 hover:text-slate-700';
        const markReadyClasses = 'bg-sky-600 border border-sky-700 text-white shadow-lg shadow-sky-200 hover:bg-sky-700';
        const noteClasses = 'w-full bg-slate-50 border border-slate-200 rounded-md px-2 py-1.5 text-xs text-slate-700 focus:ring-2 focus:ring-sky-200 focus:border-sky-400 placeholder:text-slate-400 transition';

        const dishesHtml = order.dishes.map((dish, idx) => {
            const dotClass = dotClassMap[dish.status] || dotClassMap.pending;
            const isServed = dish.status === 'served';
            return `
                <div class="px-4 py-3 flex flex-col gap-2" data-dish-wrapper data-dish-status="${dish.status}">
                    <div class="flex items-start gap-2">
                        <span class="mt-1.5 w-2 h-2 rounded-full shrink-0 ${dotClass}"
                              data-role="status-dot"
                              data-status="${dish.status}"
                              data-class-pending="${dotClassMap.pending}"
                              data-class-ready="${dotClassMap.ready}"
                              data-class-served="${dotClassMap.served}"></span>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-baseline justify-between gap-1">
                                <span class="text-sm font-semibold text-gray-800 leading-snug">${escapeHtml(dish.name)}</span>
                                <span class="text-xs text-gray-400 font-medium shrink-0">&times;${dish.qty}</span>
                            </div>
                        </div>
                    </div>
                    <textarea class="${noteClasses}" rows="2" placeholder="No notes…">${escapeHtml(dish.notes)}</textarea>
                    <div class="dish-action" data-order-id="${order.id}" data-item-id="${dish.item_id}" data-dish-status="${dish.status}" data-dish-index="${idx}">
                        ${isServed ? `
                            <div class="inline-flex items-center gap-1 text-xs font-semibold text-green-600 bg-green-50 border border-green-200 px-2.5 py-1.5 rounded-lg">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg>
                                Served
                            </div>
                        ` : `
                            <button type="button"
                                    class="mark-ready-btn inline-flex items-center justify-center gap-1 text-xs font-semibold px-2.5 py-1.5 rounded-lg w-full transition ${dish.status === 'ready' ? markReadyClasses : markPendingClasses}"
                                    data-role="mark-ready"
                                    data-order-id="${order.id}"
                                    data-dish-status="${dish.status}"
                                    data-class-pending="${markPendingClasses}"
                                    data-class-ready="${markReadyClasses}">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg>
                                <span class="mark-ready-label">${dish.status === 'ready' ? 'Ready' : 'Mark Ready'}</span>
                            </button>
                        `}
                    </div>
                </div>
            `;
        }).join('');

        return `
            <div class="order-card rounded-xl border overflow-hidden flex flex-col transition-colors duration-300 ${cardStateClass}"
                 data-overall="${order.overall}"
                 data-type="${order.type}"
                 data-order-id="${order.id}"
                 data-order-db-id="${order.db_id}">
                <div class="px-4 py-3 text-white ${headerClass}">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="inline-flex items-center gap-1 text-xs font-bold bg-black/20 px-2 py-0.5 rounded">
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3zm0 0v7"/></svg>
                                    Table ${escapeHtml(order.table)}
                                </span>
                                <span class="text-sm font-bold tracking-wide">${order.id}</span>
                            </div>
                            <p class="text-xs opacity-70 mt-0.5">
                                ${escapeHtml(order.waiter)}${order.customer && order.customer !== '—' ? ' &middot; ' + escapeHtml(order.customer) : ''}
                            </p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-xs font-semibold">${order.time}</p>
                            <p class="text-xs opacity-70 mt-0.5">
                                ${isCompleted ? 'All served' : (order.cnt_ready > 0 ? order.cnt_ready + ' ready &middot; ' : '') + (order.cnt_pending > 0 ? order.cnt_pending + ' preparing' : '')}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="flex-1 divide-y divide-gray-100">
                    ${dishesHtml}
                </div>
                <div class="px-4 py-3 bg-gray-50 border-t border-gray-100 flex flex-col gap-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-400">
                            ${order.cnt_total} ${order.cnt_total === 1 ? 'dish' : 'dishes'}
                            &middot; ${order.cnt_served} served
                        </span>
                        <span class="inline-flex items-center gap-1 text-xs font-semibold ${overallClass}">
                            ${isCompleted
                                ? '<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg> Completed'
                                : isReady
                                    ? '<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg> Ready to serve'
                                    : '<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 1.5"/></svg> Preparing'
                            }
                        </span>
                    </div>
                    <div class="order-actions flex flex-col gap-2 ${isCompleted ? 'hidden' : ''}" data-role="order-actions">
                        <button type="button"
                                class="order-send-btn inline-flex items-center justify-center gap-2 text-xs font-semibold px-3 py-2 rounded-lg border border-dashed transition focus:outline-none focus:ring-2 focus:ring-sky-200 ${sendClassMap[sendState]}"
                                data-role="send-order"
                                data-order-id="${order.id}"
                                data-send-state="${sendState}"
                                data-class-disabled="${sendClassMap.disabled}"
                                data-class-ready="${sendClassMap.ready}"
                                data-class-sent="${sendClassMap.sent}"
                                ${sendState !== 'ready' ? 'disabled' : ''}>
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                            <span class="order-send-label">${isCompleted ? 'Sent' : 'Send Out'}</span>
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function kitchenUpdateSummaryCounts(totalPending, totalReady, countCompleted) {
        const pendingEl = document.querySelector('[data-summary="pending"]');
        const readyEl = document.querySelector('[data-summary="ready"]');
        const completedEl = document.querySelector('[data-summary="completed"]');

        if (pendingEl) pendingEl.textContent = totalPending;
        if (readyEl) readyEl.textContent = totalReady;
        if (completedEl) completedEl.textContent = countCompleted;
    }

    function kitchenUpdateTabCounts(countActive, countCompleted) {
        const kitchenPanel = document.getElementById('kitchen-panel') || document;
        const activeCountEl = kitchenPanel.querySelector('button[data-count-type="active"] span');
        const completedCountEl = kitchenPanel.querySelector('button[data-count-type="completed"] span');
        const allCountEl = kitchenPanel.querySelector('button[data-tab="all"] span');

        if (activeCountEl) activeCountEl.textContent = countActive;
        if (completedCountEl) completedCountEl.textContent = countCompleted;
        if (allCountEl && activeCountEl && completedCountEl) {
            const active = parseInt(activeCountEl.textContent, 10) || 0;
            const completed = parseInt(completedCountEl.textContent, 10) || 0;
            allCountEl.textContent = active + completed;
        }
    }

    function kitchenApplyCurrentFilter() {
        const kitchenPanel = document.getElementById('kitchen-panel') || document;
        const activeTab = kitchenPanel.querySelector('button[data-tab].bg-molveno-blue-500');
        if (activeTab) {
            kitchenSwitchTab(activeTab);
        }
    }

    // Add CSS animation for new orders
    const style = document.createElement('style');
    style.textContent = `
        @keyframes kds-pulse-new {
            0% { box-shadow: 0 0 0 0 rgba(0, 132, 196, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(0, 132, 196, 0); }
            100% { box-shadow: 0 0 0 0 rgba(0, 132, 196, 0); }
        }
    `;
    document.head.appendChild(style);

    // Start polling when page loads
    document.addEventListener('DOMContentLoaded', () => {
        startPolling();
    });

    // Stop polling when page is hidden to save resources
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stopPolling();
        } else {
            startPolling();
            pollForOrders(); // Immediate poll when becoming visible
        }
    });
</script>
