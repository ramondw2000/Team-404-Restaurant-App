@props(['dishes', 'allergenConfig'])

<script>
    /* ═══════════════════════════════════════════════════════
       Menu data (mirrors PHP arrays above)
    ═══════════════════════════════════════════════════════ */
    const MENU = @json($dishes);
    const ALLERGEN = @json($allergenConfig);

    /* ═══════════════════════════════════════════════════════
       Order state: { [id]: { dish, qty, note } }
    ═══════════════════════════════════════════════════════ */
    let order = {};

    /* ═══════════════════════════════════════════════════════
       Filter state
    ═══════════════════════════════════════════════════════ */
    let activeCat     = 'all';
    let activeDietary = new Set();
    let activeFreefrom = new Set();

    const TAB_ACTIVE   = ['bg-molveno-blue-500', 'border-molveno-blue-500', 'text-white'];
    const TAB_INACTIVE = ['bg-white', 'border-gray-200', 'text-gray-600', 'hover:border-molveno-blue-300', 'hover:text-molveno-blue-700'];

    function setCategory(btn) {
        activeCat = btn.dataset.cat;
        document.querySelectorAll('[data-cat]').forEach(b => {
            const isActive = b === btn;
            b.classList.remove(...TAB_ACTIVE, ...TAB_INACTIVE);
            b.classList.add(...(isActive ? TAB_ACTIVE : TAB_INACTIVE));
        });
        applyFilters();
    }

    function toggleDietary(btn) {
        const val = btn.dataset.dietary;
        activeDietary.has(val) ? activeDietary.delete(val) : activeDietary.add(val);
        btn.classList.toggle('filter-active', activeDietary.has(val));
        applyFilters();
    }

    function toggleFreefrom(btn) {
        const val = btn.dataset.freefrom;
        activeFreefrom.has(val) ? activeFreefrom.delete(val) : activeFreefrom.add(val);
        btn.classList.toggle('filter-active', activeFreefrom.has(val));
        applyFilters();
    }

    function applyFilters() {
        const q = document.getElementById('search-input').value.toLowerCase().trim();
        let visible = 0;

        document.querySelectorAll('.dish-card').forEach(card => {
            const catOk      = activeCat === 'all' || card.dataset.cat === activeCat;
            const nameOk     = !q || card.dataset.name.includes(q);
            const dietaryOk  = activeDietary.size === 0 || [...activeDietary].every(d => card.dataset.dietary.includes(d));
            const freefromOk = activeFreefrom.size === 0 || [...activeFreefrom].every(a => !card.dataset.allergens.includes(a));

            const show = catOk && nameOk && dietaryOk && freefromOk;
            card.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        document.getElementById('no-results').classList.toggle('hidden', visible > 0);
    }

    function resetFilters() {
        activeCat = 'all';
        activeDietary.clear();
        activeFreefrom.clear();
        document.getElementById('search-input').value = '';
        document.querySelectorAll('[data-cat]').forEach(b => {
            const isActive = b.dataset.cat === 'all';
            b.classList.remove(...TAB_ACTIVE, ...TAB_INACTIVE);
            b.classList.add(...(isActive ? TAB_ACTIVE : TAB_INACTIVE));
        });
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('filter-active'));
        applyFilters();
    }

    /* ═══════════════════════════════════════════════════════
       Add-dish modal
    ═══════════════════════════════════════════════════════ */
    let addModalDishId = null;
    let addModalQty    = 1;

    function addDish(id) {
        const dish = MENU.find(d => d.id === id);
        if (!dish) return;

        addModalDishId = id;
        addModalQty = order[id] ? 1 : 1;

        document.getElementById('add-modal-name').textContent  = dish.name;
        document.getElementById('add-modal-price').textContent = '€ ' + dish.price.toFixed(2);
        document.getElementById('add-modal-qty').textContent   = addModalQty;
        document.getElementById('add-modal-note').value        = '';

        const badgeRow = document.getElementById('add-modal-badges');
        badgeRow.innerHTML = '';
        dish.allergens.forEach(a => {
            if (!ALLERGEN[a]) return;
            const d = document.createElement('div');
            d.title = ALLERGEN[a].label;
            d.className = 'w-5 h-5 rounded-full flex items-center justify-center shrink-0 shadow-sm';
            d.style.backgroundColor = ALLERGEN[a].bg;
            d.innerHTML = `<svg viewBox="0 0 16 16" width="10" height="10">${ALLERGEN[a].icon}</svg>`;
            badgeRow.appendChild(d);
        });
        if (dish.dietary.includes('vegetarian')) {
            badgeRow.insertAdjacentHTML('beforeend', `<div title="Vegetarian" class="w-5 h-5 rounded-full bg-green-500 flex items-center justify-center shrink-0"><svg viewBox="0 0 16 16" width="10" height="10"><path fill="black" d="M3 14c0-5 4-11 10-12C13 7 11 11 8 13l4-3c-1 3-5 5-9 4z"/></svg></div>`);
        }
        if (dish.dietary.includes('vegan')) {
            badgeRow.insertAdjacentHTML('beforeend', `<div title="Vegan" class="w-5 h-5 rounded-full bg-green-700 flex items-center justify-center shrink-0"><svg viewBox="0 0 16 16" width="10" height="10"><path stroke="black" stroke-width="1.5" fill="none" stroke-linecap="round" d="M8 14V8M8 8C8 5 5 2 2 2C2 5 5 8 8 8M8 8C8 5 11 2 14 2C14 5 11 8 8 8"/></svg></div>`);
        }
        if (badgeRow.children.length === 0) {
            badgeRow.innerHTML = '<span class="text-xs text-gray-400">No allergens</span>';
        }

        document.getElementById('add-overlay').classList.add('open');
        document.body.style.overflow = 'hidden';
        setTimeout(() => document.getElementById('add-modal-note').focus(), 220);
    }

    function addModalChangeQty(delta) {
        addModalQty = Math.max(1, addModalQty + delta);
        document.getElementById('add-modal-qty').textContent = addModalQty;
    }

    function confirmAddDish() {
        const id   = addModalDishId;
        const dish = MENU.find(d => d.id === id);
        if (!dish) return;
        const note = document.getElementById('add-modal-note').value.trim();

        if (order[id]) {
            order[id].qty  += addModalQty;
            if (note) order[id].note = note;
        } else {
            order[id] = { dish, qty: addModalQty, note };
        }

        updateBadge(id);
        updateOrderBar();
        closeAddModal();
    }

    function closeAddModal(e) {
        if (e instanceof MouseEvent && e.target !== document.getElementById('add-overlay')) return;
        document.getElementById('add-overlay').classList.remove('open');
        document.body.style.overflow = '';
        addModalDishId = null;
    }

    /* ═══════════════════════════════════════════════════════
       Order management
    ═══════════════════════════════════════════════════════ */

    function removeDish(id) {
        delete order[id];
        updateBadge(id);
        updateOrderBar();
        renderReviewCard();
    }

    function changeQty(id, delta) {
        if (!order[id]) return;
        const newQty = order[id].qty + delta;
        if (newQty < 1) { removeDish(id); return; }
        order[id].qty = newQty;
        updateBadge(id);
        updateOrderBar();
        renderReviewCard();
    }

    function updateNote(id, val) {
        if (order[id]) order[id].note = val;
    }

    /* ── Badge on dish card ── */
    function updateBadge(id) {
        const badge = document.getElementById('badge-' + id);
        if (!badge) return;
        const item = order[id];
        if (item && item.qty > 0) {
            badge.textContent = item.qty;
            badge.classList.add('visible');
        } else {
            badge.classList.remove('visible');
        }
    }

    /* ── Sticky order bar ── */
    function updateOrderBar() {
        const items  = Object.values(order);
        const count  = items.reduce((s, i) => s + i.qty, 0);
        const total  = items.reduce((s, i) => s + i.dish.price * i.qty, 0);
        const table  = document.getElementById('sel-table').value;
        const bar    = document.getElementById('order-bar');

        document.getElementById('bar-count').textContent  = count;
        document.getElementById('bar-item-label').textContent = count === 1 ? 'item' : 'items';
        document.getElementById('bar-total').textContent  = '€ ' + total.toFixed(2);
        document.getElementById('bar-table').textContent  = table ? 'Table ' + table : 'No table selected';

        if (count > 0) {
            bar.classList.remove('hidden-bar');
        } else {
            bar.classList.add('hidden-bar');
        }
    }

    document.getElementById('sel-table').addEventListener('change', updateOrderBar);

    /* ═══════════════════════════════════════════════════════
       Review screen
    ═══════════════════════════════════════════════════════ */
    function openReview() {
        if (Object.keys(order).length === 0) return;
        renderReviewCard();
        document.getElementById('review-screen').classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeReview() {
        document.getElementById('review-screen').classList.remove('open');
        document.body.style.overflow = '';
    }

    function renderReviewCard() {
        const items  = Object.values(order);
        const table  = document.getElementById('sel-table').value || '—';
        const now    = new Date();
        const time   = now.getHours().toString().padStart(2,'0') + ':' + now.getMinutes().toString().padStart(2,'0');
        const orderId = 'ORD-' + String(Math.floor(Math.random() * 900) + 100).padStart(3,'0');

        const cntPending = items.reduce((s, i) => s + i.qty, 0);
        const total      = items.reduce((s, i) => s + i.dish.price * i.qty, 0);

        document.getElementById('review-nav-table').textContent  = table !== '—' ? 'Table ' + table : 'No table';
        document.getElementById('review-meta-time').textContent  = time;
        document.getElementById('review-total').textContent      = '€ ' + total.toFixed(2);

        document.getElementById('review-ticket-header').style.backgroundColor = '#0084c4';
        document.getElementById('review-ticket-header').innerHTML = `
            <div class="flex items-start justify-between gap-2">
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="inline-flex items-center gap-1 text-xs font-bold bg-black/20 px-2 py-0.5 rounded">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                <path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/>
                                <path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3zm0 0v7"/>
                            </svg>
                            Table ${esc(table)}
                        </span>
                        <span class="text-sm font-bold tracking-wide">${orderId}</span>
                    </div>
                    <p class="text-xs opacity-70 mt-0.5">John Doe</p>
                </div>
                <div class="text-right shrink-0">
                    <p class="text-xs font-semibold">${time}</p>
                    <p class="text-xs opacity-70 mt-0.5">${cntPending} preparing</p>
                </div>
            </div>`;

        document.getElementById('review-dish-list').innerHTML = items.map(({ dish, qty, note }) => {
            const allergenHtml = dish.allergens.length
                ? `<div class="flex items-center gap-1 flex-wrap mt-1">
                    ${dish.allergens.map(a => ALLERGEN[a]
                        ? `<div title="${esc(ALLERGEN[a].label)}"
                                class="w-5 h-5 rounded-full flex items-center justify-center shrink-0 shadow-sm"
                                style="background-color:${ALLERGEN[a].bg}">
                                <svg viewBox="0 0 16 16" width="10" height="10">${ALLERGEN[a].icon}</svg>
                           </div>`
                        : '').join('')}
                   </div>`
                : '';

            const dietaryHtml = dish.dietary.length
                ? `<div class="flex items-center gap-1 flex-wrap mt-1">
                    ${dish.dietary.includes('vegetarian')
                        ? `<div title="Vegetarian" class="w-4 h-4 rounded-full bg-green-500 flex items-center justify-center shrink-0"><svg viewBox="0 0 16 16" width="8" height="8"><path fill="black" d="M3 14c0-5 4-11 10-12C13 7 11 11 8 13l4-3c-1 3-5 5-9 4z"/></svg></div>` : ''}
                    ${dish.dietary.includes('vegan')
                        ? `<div title="Vegan" class="w-4 h-4 rounded-full bg-green-700 flex items-center justify-center shrink-0"><svg viewBox="0 0 16 16" width="8" height="8"><path stroke="black" stroke-width="1.5" fill="none" stroke-linecap="round" d="M8 14V8M8 8C8 5 5 2 2 2C2 5 5 8 8 8M8 8C8 5 11 2 14 2C14 5 11 8 8 8"/></svg></div>` : ''}
                   </div>`
                : '';

            const noteHtml = note.trim()
                ? `<p class="text-xs text-gray-400 italic mt-1 leading-snug">"${esc(note)}"</p>`
                : '';

            return `
            <div class="px-4 py-3 flex flex-col gap-1.5">
                <div class="flex items-start gap-2">
                    <span class="mt-1.5 w-2 h-2 rounded-full shrink-0 bg-gray-300 flex-shrink-0"></span>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-baseline justify-between gap-1">
                            <span class="text-sm font-semibold text-gray-800 leading-snug">${esc(dish.name)}</span>
                            <span class="text-xs text-gray-400 font-medium shrink-0">&times;${qty}</span>
                        </div>
                        ${allergenHtml}${dietaryHtml}${noteHtml}
                    </div>
                </div>
                <div class="pl-4">
                    <textarea class="note-area" rows="1"
                              placeholder="Add notes for this dish…"
                              oninput="updateNote(${dish.id}, this.value); updateNotePreview(${dish.id}, this.value)">${esc(note)}</textarea>
                </div>
                <div class="pl-4 flex items-center gap-2">
                    <button class="qty-btn" onclick="changeQty(${dish.id}, -1)">
                        <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M5 12h14"/></svg>
                    </button>
                    <span class="text-sm font-bold text-gray-700 w-5 text-center" id="review-qty-${dish.id}">${qty}</span>
                    <button class="qty-btn" onclick="changeQty(${dish.id}, 1)">
                        <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                    </button>
                    <span class="text-xs text-gray-400 ms-1">€ ${(dish.price * qty).toFixed(2)}</span>
                    <button onclick="removeDish(${dish.id})" class="ms-auto text-gray-300 hover:text-red-500 transition-colors">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>`;
        }).join('');

        const dishCount = items.length;
        document.getElementById('review-card-footer').innerHTML = `
            <span class="text-xs text-gray-400">${dishCount} ${dishCount === 1 ? 'dish' : 'dishes'} &middot; 0 served</span>
            <span class="inline-flex items-center gap-1 text-xs font-semibold text-gray-400">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 1.5"/>
                </svg>
                Preparing &middot; <strong class="text-gray-700 ms-0.5">&euro; ${total.toFixed(2)}</strong>
            </span>`;
    }

    function updateNotePreview(id, val) {
        // notes already stored via updateNote(); re-render not needed since
        // the textarea itself is the source of truth in the review card.
    }

    /* ═══════════════════════════════════════════════════════
       Send order
    ═══════════════════════════════════════════════════════ */
    function sendOrder() {
        const table = document.getElementById('sel-table').value;
        if (!table) {
            alert('Please select a table before sending the order.');
            return;
        }
        closeReview();
        Object.keys(order).forEach(id => updateBadge(id));
        order = {};
        updateOrderBar();
        window.dispatchEvent(new CustomEvent('toast', {
            detail: { message: 'Order sent to kitchen!', type: 'success' }
        }));
    }

    /* ═══════════════════════════════════════════════════════
       Helpers
    ═══════════════════════════════════════════════════════ */
    function esc(str) {
        return String(str ?? '')
            .replace(/&/g,'&amp;')
            .replace(/</g,'&lt;')
            .replace(/>/g,'&gt;')
            .replace(/"/g,'&quot;');
    }
</script>
