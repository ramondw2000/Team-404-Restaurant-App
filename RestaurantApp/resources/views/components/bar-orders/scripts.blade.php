<script>
    const TAB_ACTIVE_CLASSES = ['bg-molveno-blue-500', 'border-molveno-blue-500', 'text-white'];
    const TAB_INACTIVE_CLASSES = ['bg-white', 'border-gray-200', 'text-gray-600', 'hover:border-molveno-blue-300', 'hover:text-molveno-blue-700'];
    const TAB_COUNT_ACTIVE_CLASSES = ['bg-white/25', 'text-white'];
    const TAB_COUNT_INACTIVE_CLASSES = ['bg-gray-100', 'text-gray-500'];

    document.addEventListener('DOMContentLoaded', () => {
        const defaultTab = document.querySelector('button[data-default="true"]') || document.querySelector('button[data-tab]');
        if (defaultTab) {
            switchTab(defaultTab);
        }
        document.querySelectorAll('.order-card').forEach(card => {
            updateOrderSendState(card);
            syncCardVisualState(card);
        });
        document.addEventListener('click', handleActionClick);
    });

    const CARD_COMPLETED_CLASSES = ['bg-emerald-50', 'border-emerald-200', 'shadow-md'];
    const CARD_DEFAULT_CLASSES = ['bg-white', 'border-gray-200', 'shadow-sm'];

    function switchTab(btn) {
        const tab = btn.dataset.tab;
        document.querySelectorAll('button[data-tab]').forEach(b => setTabAppearance(b, b === btn));
        let visible = 0;
        document.querySelectorAll('.order-card').forEach(card => {
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
        document.getElementById('no-orders').classList.toggle('hidden', visible > 0);
    }

    function handleActionClick(event) {
        const target = event.target instanceof Element ? event.target : event.target.parentElement;
        if (!target) return;

        const sendBtn = target.closest('.order-send-btn');
        if (sendBtn) {
            event.preventDefault();
            if (!sendBtn.disabled) {
                completeOrder(sendBtn);
            }
        }
    }

    function completeOrder(button) {
        setSendButtonState(button, 'sent');
        const card = button.closest('.order-card');
        if (card) {
            card.dataset.overall = 'completed';
            hideOrderActions(card);
            markAllDrinksServed(card);
            syncCardVisualState(card);
        }
    }

    function markAllDrinksServed(card) {
        card.querySelectorAll('[data-drink-wrapper]').forEach(wrapper => {
            wrapper.dataset.drinkStatus = 'served';

            // Update status dot
            const dot = wrapper.querySelector('[data-role="status-dot"]');
            if (dot) setStatusDotAppearance(dot, 'served');

            // Replace status badge with served badge
            const status = wrapper.querySelector('.drink-status');
            if (status) {
                status.dataset.drinkStatus = 'served';
                status.innerHTML = `
                    <div class="inline-flex items-center gap-1 text-xs font-semibold text-green-600 bg-green-50 border border-green-200 px-2.5 py-1.5 rounded-lg">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg>
                        Served
                    </div>`;
            }
        });
    }

    function updateOrderSendState(card) {
        if (!card) return;

        const drinkStatuses = Array.from(card.querySelectorAll('.drink-status'));
        const hasPending = drinkStatuses.some(s => s.dataset.drinkStatus === 'pending');
        const allServed = drinkStatuses.length > 0 && drinkStatuses.every(s => s.dataset.drinkStatus === 'served');

        const sendBtn = card.querySelector('.order-send-btn');

        if (sendBtn) {
            const currentState = card.dataset.overall === 'completed' ? 'sent'
                                : (!hasPending && !allServed) ? 'ready'
                                : allServed ? 'sent'
                                : 'disabled';
            setSendButtonState(sendBtn, currentState);
        }

        syncCardVisualState(card);
    }

    function setTabAppearance(button, isActive) {
        if (!button) return;
        button.classList.remove(...TAB_ACTIVE_CLASSES, ...TAB_INACTIVE_CLASSES);
        button.classList.add(...(isActive ? TAB_ACTIVE_CLASSES : TAB_INACTIVE_CLASSES));

        const count = button.querySelector('span');
        if (count) {
            count.classList.remove(...TAB_COUNT_ACTIVE_CLASSES, ...TAB_COUNT_INACTIVE_CLASSES);
            count.classList.add(...(isActive ? TAB_COUNT_ACTIVE_CLASSES : TAB_COUNT_INACTIVE_CLASSES));
        }
    }

    function setStatusDotAppearance(dot, state) {
        if (!dot) return;
        const pending = parseClasses(dot.dataset.classPending);
        const ready = parseClasses(dot.dataset.classReady);
        const served = parseClasses(dot.dataset.classServed);
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

    function setSendButtonState(button, state) {
        if (!button) return;
        const disabled = parseClasses(button.dataset.classDisabled);
        const ready = parseClasses(button.dataset.classReady);
        const sent = parseClasses(button.dataset.classSent);
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

    function syncCardVisualState(card) {
        if (!card) return;
        card.classList.remove(...CARD_COMPLETED_CLASSES, ...CARD_DEFAULT_CLASSES);

        const isCompleted = card.dataset.overall === 'completed';

        if (isCompleted) {
            card.classList.add(...CARD_COMPLETED_CLASSES);
        } else {
            card.classList.add(...CARD_DEFAULT_CLASSES);
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

    function hideOrderActions(card) {
        const actions = card.querySelector('[data-role="order-actions"]');
        if (actions) actions.classList.add('hidden');
    }

    function showOrderActions(card) {
        const actions = card.querySelector('[data-role="order-actions"]');
        if (actions) actions.classList.remove('hidden');
    }

    function parseClasses(value) {
        return (value || '').split(' ').filter(Boolean);
    }
</script>
