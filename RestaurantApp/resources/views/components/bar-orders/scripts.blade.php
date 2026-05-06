<script>
    const BAR_DELETE_URL = '{{ rtrim(route('kitchen-orders.order.delete', ['order' => '__ID__']), '') }}';
    const BAR_MARK_READY_URL = '{{ rtrim(route('kitchen-orders.dish.ready', ['orderItem' => '__ID__']), '') }}';
    const BAR_COMPLETE_URL = '{{ rtrim(route('kitchen-orders.order.complete', ['order' => '__ID__']), '') }}';
    const BAR_CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content;
    const BAR_TAB_ACTIVE_CLASSES   = ['bg-molveno-blue-500', 'border-molveno-blue-500', 'text-white'];
    const BAR_TAB_INACTIVE_CLASSES = ['bg-white', 'border-gray-200', 'text-gray-600', 'hover:border-molveno-blue-300', 'hover:text-molveno-blue-700'];
    const BAR_TAB_COUNT_ACTIVE_CLASSES   = ['bg-white/25', 'text-white'];
    const BAR_TAB_COUNT_INACTIVE_CLASSES = ['bg-gray-100', 'text-gray-500'];
    const BAR_CARD_COMPLETED_CLASSES = ['bg-emerald-50', 'border-emerald-200', 'shadow-md'];
    const BAR_CARD_DEFAULT_CLASSES   = ['bg-white', 'border-gray-200', 'shadow-sm'];

    document.addEventListener('DOMContentLoaded', () => {
        const barPanel = document.getElementById('bar-panel');
        const scope = barPanel || document;
        const defaultTab = scope.querySelector('button[data-default="true"]') || scope.querySelector('button[data-tab]');
        if (defaultTab) {
            barSwitchTab(defaultTab);
        }
        scope.querySelectorAll('.mark-ready-btn').forEach(btn => {
            barSetMarkReadyAppearance(btn, btn.dataset.dishStatus === 'ready' ? 'ready' : 'pending');
        });
        scope.querySelectorAll('.order-card').forEach(card => {
            barUpdateOrderSendState(card);
            barSyncCardVisualState(card);
        });
        document.addEventListener('click', barHandleActionClick);
    });

    function barSwitchTab(btn) {
        const tab = btn.dataset.tab;
        const barPanel = document.getElementById('bar-panel');
        const scope = barPanel || document;
        scope.querySelectorAll('button[data-tab]').forEach(b => barSetTabAppearance(b, b === btn));
        let visible = 0;
        scope.querySelectorAll('.order-card').forEach(card => {
            const overall = card.dataset.overall;
            const type    = card.dataset.type;
            const show    = tab === 'all'       ? true
                          : tab === 'active'    ? overall !== 'completed'
                          : tab === 'completed' ? overall === 'completed'
                          : tab === 'table'     ? type === 'table'
                          : tab === 'bar'       ? type === 'bar'
                          : true;
            card.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        const emptyEl = scope.querySelector('#bar-empty') || document.getElementById('bar-empty');
        if (emptyEl) emptyEl.classList.toggle('hidden', visible > 0);
    }

    function barHandleActionClick(event) {
        const barPanel = document.getElementById('bar-panel');
        if (!barPanel || !barPanel.contains(event.target)) return;

        const target = event.target instanceof Element ? event.target : event.target.parentElement;
        if (!target) return;

        const markBtn = target.closest('.mark-ready-btn');
        if (markBtn) {
            event.preventDefault();
            barMarkDrinkReady(markBtn);
            return;
        }

        const sendBtn = target.closest('.order-send-btn');
        if (sendBtn) {
            event.preventDefault();
            if (!sendBtn.disabled) {
                barCompleteOrder(sendBtn);
            }
            return;
        }

        const deleteBtn = target.closest('.delete-order-btn');
        if (deleteBtn) {
            event.preventDefault();
            barDeleteOrder(deleteBtn);
        }
    }

    function barMarkDrinkReady(button) {
        const nextState = button.dataset.dishStatus === 'ready' ? 'pending' : 'ready';
        barSetMarkReadyAppearance(button, nextState);
        button.dataset.dishStatus = nextState;

        const label = button.querySelector('.mark-ready-label');
        if (label) label.textContent = nextState === 'ready' ? 'Ready' : 'Mark Ready';

        const dishAction = button.closest('.dish-action');
        if (dishAction) dishAction.dataset.dishStatus = nextState;

        const wrapper = button.closest('[data-dish-wrapper]');
        if (wrapper) {
            wrapper.dataset.dishStatus = nextState;
            const dot = wrapper.querySelector('[data-role="status-dot"]');
            if (dot) barSetStatusDotAppearance(dot, nextState);
        }

        barUpdateOrderSendState(button.closest('.order-card'));

        const itemId = dishAction?.dataset.itemId;
        if (itemId) {
            fetch(BAR_MARK_READY_URL.replace('__ID__', itemId), {
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': BAR_CSRF_TOKEN, 'Accept': 'application/json' },
            }).catch(() => {});
        }
    }

    function barCompleteOrder(button) {
        barSetSendButtonState(button, 'sent');
        const card = button.closest('.order-card');
        if (card) {
            card.dataset.overall = 'completed';
            const actions = card.querySelector('[data-role="order-actions"]');
            if (actions) actions.classList.add('hidden');
            barMarkAllDrinksServed(card);
            barSyncCardVisualState(card);
            barUpdateFilterCounts('completed');
        }

        const dbId = card?.dataset.orderDbId;
        if (dbId) {
            fetch(BAR_COMPLETE_URL.replace('__ID__', dbId), {
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': BAR_CSRF_TOKEN, 'Accept': 'application/json' },
            }).catch(() => {});
        }
    }

    function barMarkAllDrinksServed(card) {
        card.querySelectorAll('[data-dish-wrapper]').forEach(wrapper => {
            wrapper.dataset.dishStatus = 'served';
            const dot = wrapper.querySelector('[data-role="status-dot"]');
            if (dot) barSetStatusDotAppearance(dot, 'served');
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

    function barUpdateOrderSendState(card) {
        if (!card) return;
        const dishActions = Array.from(card.querySelectorAll('.dish-action'));
        const hasPending = dishActions.some(a => a.dataset.dishStatus === 'pending');
        const allReadyOrServed = dishActions.length > 0 && dishActions.every(a => ['ready', 'served'].includes(a.dataset.dishStatus));
        const sendBtn = card.querySelector('.order-send-btn');
        if (sendBtn) {
            const currentState = card.dataset.overall === 'completed' ? 'sent'
                                : allReadyOrServed ? 'ready'
                                : 'disabled';
            barSetSendButtonState(sendBtn, currentState);
        }
        if (card.dataset.overall !== 'completed') {
            card.dataset.overall = allReadyOrServed ? 'ready' : (hasPending ? 'pending' : card.dataset.overall);
        }
        barSyncCardVisualState(card);
    }

    function barDeleteOrder(button) {
        const card = button.closest('.order-card');
        const dbId = card?.dataset.orderDbId;
        if (!dbId) return;

        if (!confirm('Are you sure you want to delete this order?')) return;

        const wasCompleted = card?.dataset.overall === 'completed';

        if (card) {
            card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            card.style.opacity = '0';
            card.style.transform = 'scale(0.95)';
            setTimeout(() => {
                card.remove();
                barApplyCurrentFilter();
            }, 300);
        }

        barUpdateFilterCounts(wasCompleted ? 'remove_completed' : 'remove_active');

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        fetch(BAR_DELETE_URL.replace('__ID__', dbId), {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        }).catch(() => {});
    }

    function barUpdateFilterCounts(direction) {
        const barPanel = document.getElementById('bar-panel') || document;
        const activeCountEl    = barPanel.querySelector('button[data-count-type="active"] span');
        const completedCountEl = barPanel.querySelector('button[data-count-type="completed"] span');
        const allCountEl       = barPanel.querySelector('button[data-tab="all"] span');
        if (direction === 'completed') {
            if (activeCountEl) activeCountEl.textContent = Math.max(0, (parseInt(activeCountEl.textContent, 10) || 0) - 1);
            if (completedCountEl) completedCountEl.textContent = (parseInt(completedCountEl.textContent, 10) || 0) + 1;
        } else if (direction === 'remove_active') {
            if (activeCountEl) activeCountEl.textContent = Math.max(0, (parseInt(activeCountEl.textContent, 10) || 0) - 1);
        } else if (direction === 'remove_completed') {
            if (completedCountEl) completedCountEl.textContent = Math.max(0, (parseInt(completedCountEl.textContent, 10) || 0) - 1);
        }
        if (allCountEl && activeCountEl && completedCountEl) {
            allCountEl.textContent = (parseInt(activeCountEl.textContent, 10) || 0) + (parseInt(completedCountEl.textContent, 10) || 0);
        }
    }

    function barSetTabAppearance(button, isActive) {
        if (!button) return;
        button.classList.remove(...BAR_TAB_ACTIVE_CLASSES, ...BAR_TAB_INACTIVE_CLASSES);
        button.classList.add(...(isActive ? BAR_TAB_ACTIVE_CLASSES : BAR_TAB_INACTIVE_CLASSES));
        const count = button.querySelector('span');
        if (count) {
            count.classList.remove(...BAR_TAB_COUNT_ACTIVE_CLASSES, ...BAR_TAB_COUNT_INACTIVE_CLASSES);
            count.classList.add(...(isActive ? BAR_TAB_COUNT_ACTIVE_CLASSES : BAR_TAB_COUNT_INACTIVE_CLASSES));
        }
    }

    function barSetMarkReadyAppearance(button, state) {
        if (!button) return;
        const pending = barParseClasses(button.dataset.classPending);
        const ready   = barParseClasses(button.dataset.classReady);
        button.classList.remove(...pending, ...ready);
        button.classList.add(...(state === 'ready' ? ready : pending));
    }

    function barSetStatusDotAppearance(dot, state) {
        if (!dot) return;
        const pending = barParseClasses(dot.dataset.classPending);
        const ready   = barParseClasses(dot.dataset.classReady);
        const served  = barParseClasses(dot.dataset.classServed);
        dot.classList.remove(...pending, ...ready, ...served);
        if (state === 'ready') dot.classList.add(...ready);
        else if (state === 'served') dot.classList.add(...served);
        else dot.classList.add(...pending);
        dot.dataset.status = state;
    }

    function barSetSendButtonState(button, state) {
        if (!button) return;
        const disabled = barParseClasses(button.dataset.classDisabled);
        const ready    = barParseClasses(button.dataset.classReady);
        const sent     = barParseClasses(button.dataset.classSent);
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

    function barSyncCardVisualState(card) {
        if (!card) return;
        card.classList.remove(...BAR_CARD_COMPLETED_CLASSES, ...BAR_CARD_DEFAULT_CLASSES);
        const isCompleted = card.dataset.overall === 'completed';
        card.classList.add(...(isCompleted ? BAR_CARD_COMPLETED_CLASSES : BAR_CARD_DEFAULT_CLASSES));

        const header = card.querySelector(':scope > div:first-child');
        if (header) {
            header.classList.remove('bg-violet-700', 'bg-amber-600', 'bg-emerald-600');
            header.classList.add(isCompleted ? 'bg-emerald-600' : (card.dataset.overall === 'ready' ? 'bg-amber-600' : 'bg-violet-700'));
            const rightP = header.querySelectorAll('.text-right p');
            if (rightP.length >= 2 && isCompleted) rightP[1].textContent = 'All served';
        }

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

    function barParseClasses(value) {
        return (value || '').split(' ').filter(Boolean);
    }

    function barApplyCurrentFilter() {
        const barPanel = document.getElementById('bar-panel') || document;
        const activeTab = barPanel.querySelector('button[data-tab].bg-molveno-blue-500');
        if (activeTab) {
            barSwitchTab(activeTab);
        }
    }
</script>
