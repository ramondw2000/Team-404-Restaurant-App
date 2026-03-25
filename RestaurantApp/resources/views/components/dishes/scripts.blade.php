<script>
    /* ── Sheet helpers ─────────────────────────────────── */
    function openSheet() {
        document.getElementById('sheet-overlay').classList.add('open');
        document.getElementById('sheet-panel').classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function closeSheet() {
        document.getElementById('sheet-overlay').classList.remove('open');
        document.getElementById('sheet-panel').classList.remove('open');
        document.body.style.overflow = '';
    }
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeSheet(); });

    /* ── Create mode ───────────────────────────────────── */
    function openCreateSheet() {
        document.getElementById('sheet-title').textContent    = 'Add New Dish';
        document.getElementById('sheet-subtitle').textContent = 'Fill in the details below to add a dish to the menu.';
        document.getElementById('sheet-save-btn').textContent = 'Save Dish';
        document.getElementById('sheet-delete-btn').classList.add('hidden');
        document.getElementById('current-photo-preview').classList.add('hidden');
        document.getElementById('upload-zone-wrapper').classList.remove('hidden');
        document.getElementById('dish-name').value   = '';
        document.getElementById('dish-desc').value   = '';
        document.getElementById('dish-price').value  = '';
        document.getElementById('dish-category').value = '';
        document.querySelectorAll('.allergen-checkbox').forEach(cb => cb.checked = false);
        openSheet();
    }

    /* ── Edit mode ─────────────────────────────────────── */
    function openEditSheet(card) {
        const name = card.querySelector('.font-bold').textContent.trim();

        document.getElementById('sheet-title').textContent    = 'Edit Dish';
        document.getElementById('sheet-subtitle').innerHTML   =
            'Editing: <span class="font-semibold text-gray-600">' + name + '</span>';
        document.getElementById('sheet-save-btn').textContent = 'Update Dish';
        document.getElementById('sheet-delete-btn').classList.remove('hidden');

        document.getElementById('upload-zone-wrapper').classList.add('hidden');
        document.getElementById('current-photo-preview').classList.remove('hidden');
        document.getElementById('preview-bg').style.backgroundColor = card.dataset.color || '#309bcf';

        document.getElementById('dish-name').value    = name;
        document.getElementById('dish-desc').value    = '';
        document.getElementById('dish-price').value   = card.dataset.price  || '';
        document.getElementById('dish-category').value = card.dataset.category || '';

        const allergens = (card.dataset.allergens || '').split(',').filter(Boolean);
        document.getElementById('al-gluten').checked = allergens.includes('gluten');
        document.getElementById('al-nuts').checked   = allergens.includes('nuts');
        document.getElementById('al-milk').checked   = allergens.includes('milk');
        document.getElementById('al-wheat').checked  = allergens.includes('wheat');
        document.getElementById('al-fish').checked   = allergens.includes('fish');
        document.getElementById('al-egg').checked    = allergens.includes('egg');

        const dietary = (card.dataset.dietary || '').split(',').filter(Boolean);
        document.getElementById('diet-veg').checked   = dietary.includes('vegetarian');
        document.getElementById('diet-vegan').checked = dietary.includes('vegan');

        openSheet();
    }

    /* ── Filtering ─────────────────────────────────────── */
    const state = { category: 'all', dietary: [], freefrom: [] };

    function applyFilters() {
        const search  = document.getElementById('search-input').value.trim().toLowerCase();
        const cards   = document.querySelectorAll('#dish-grid .dish-card');
        let visible   = 0;

        cards.forEach(card => {
            const name      = card.dataset.name      || '';
            const category  = card.dataset.category  || '';
            const allergens = card.dataset.allergens  ? card.dataset.allergens.split(',').filter(Boolean) : [];
            const dietary   = card.dataset.dietary    ? card.dataset.dietary.split(',').filter(Boolean)   : [];

            const passCategory = state.category === 'all' || category === state.category;
            const passSearch   = !search || name.includes(search);
            const passDietary  = state.dietary.every(d => dietary.includes(d));
            const passFree     = state.freefrom.every(a => !allergens.includes(a));

            const show = passCategory && passSearch && passDietary && passFree;
            card.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        document.getElementById('no-results').classList.toggle('hidden', visible > 0);
    }

    const TAB_ACTIVE   = ['bg-molveno-blue-500', 'border-molveno-blue-500', 'text-white'];
    const TAB_INACTIVE = ['bg-white', 'border-gray-200', 'text-gray-600', 'hover:border-molveno-blue-300', 'hover:text-molveno-blue-700'];
    const TAB_COUNT_ACTIVE   = ['bg-white/25', 'text-white'];
    const TAB_COUNT_INACTIVE = ['bg-gray-100', 'text-gray-500'];

    function setCategory(btn) {
        state.category = btn.dataset.value;
        document.querySelectorAll('[data-filter="category"]').forEach(b => {
            const isActive = b === btn;
            b.classList.remove(...TAB_ACTIVE, ...TAB_INACTIVE);
            b.classList.add(...(isActive ? TAB_ACTIVE : TAB_INACTIVE));
            const countEl = b.querySelector('span');
            if (countEl) {
                countEl.classList.remove(...TAB_COUNT_ACTIVE, ...TAB_COUNT_INACTIVE);
                countEl.classList.add(...(isActive ? TAB_COUNT_ACTIVE : TAB_COUNT_INACTIVE));
            }
        });
        applyFilters();
    }

    function toggleMulti(btn, key) {
        const val = btn.dataset.value;
        const idx = state[key].indexOf(val);
        if (idx === -1) { state[key].push(val); }
        else            { state[key].splice(idx, 1); }
        btn.classList.toggle('filter-active', state[key].includes(val));
        applyFilters();
    }

    function resetFilters() {
        state.category = 'all';
        state.dietary  = [];
        state.freefrom = [];
        document.getElementById('search-input').value = '';
        document.querySelectorAll('[data-filter="category"]').forEach(b => {
            const isActive = b.dataset.value === 'all';
            b.classList.remove(...TAB_ACTIVE, ...TAB_INACTIVE);
            b.classList.add(...(isActive ? TAB_ACTIVE : TAB_INACTIVE));
        });
        document.querySelectorAll('[data-filter="dietary"], [data-filter="freefrom"]')
                .forEach(b => b.classList.remove('filter-active'));
        applyFilters();
    }

    document.getElementById('search-input').addEventListener('input', applyFilters);
</script>
