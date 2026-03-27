<script>
    /* ── Sheet helpers ─────────────────────────────────── */
    let currentDishId = null;

    function openSheet() {
        document.getElementById('sheet-overlay').classList.add('open');
        document.getElementById('sheet-panel').classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function closeSheet() {
        document.getElementById('sheet-overlay').classList.remove('open');
        document.getElementById('sheet-panel').classList.remove('open');
        document.body.style.overflow = '';
        currentDishId = null;
    }
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeSheet(); });

    /* ── Create mode ───────────────────────────────────── */
    function openCreateSheet() {
        currentDishId = null;
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
        currentDishId = card.dataset.id;

        document.getElementById('sheet-title').textContent    = 'Edit Dish';
        document.getElementById('sheet-subtitle').innerHTML   =
            'Editing: <span class="font-semibold text-gray-600">' + name + '</span>';
        document.getElementById('sheet-save-btn').textContent = 'Update Dish';
        document.getElementById('sheet-delete-btn').classList.remove('hidden');
        document.getElementById('sheet-delete-btn').onclick = () => deleteDish(currentDishId);

        document.getElementById('upload-zone-wrapper').classList.add('hidden');
        document.getElementById('current-photo-preview').classList.remove('hidden');
        const previewBg = document.getElementById('preview-bg');
        previewBg.style.backgroundColor = card.dataset.color || '#309bcf';
        previewBg.dataset.color = card.dataset.color || '#309bcf';

        document.getElementById('dish-name').value    = name;
        document.getElementById('dish-desc').value    = card.dataset.description || '';
        document.getElementById('dish-price').value   = parseFloat(card.dataset.price || 0).toFixed(2);
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

    /* ── Save / Delete ─────────────────────────────────── */
    function getSelectedAllergens() {
        const allergens = [];
        if (document.getElementById('al-gluten').checked) allergens.push('gluten');
        if (document.getElementById('al-nuts').checked) allergens.push('nuts');
        if (document.getElementById('al-milk').checked) allergens.push('milk');
        if (document.getElementById('al-wheat').checked) allergens.push('wheat');
        if (document.getElementById('al-fish').checked) allergens.push('fish');
        if (document.getElementById('al-egg').checked) allergens.push('egg');
        return allergens;
    }

    function getSelectedDietary() {
        const dietary = [];
        if (document.getElementById('diet-veg').checked) dietary.push('vegetarian');
        if (document.getElementById('diet-vegan').checked) dietary.push('vegan');
        return dietary;
    }

    function saveDish() {
        const name = document.getElementById('dish-name').value.trim();
        const price = document.getElementById('dish-price').value;
        const category = document.getElementById('dish-category').value;

        if (!name || !price || !category) {
            alert('Please fill in all required fields (Name, Price, Category).');
            return;
        }

        const data = {
            name: name,
            description: document.getElementById('dish-desc').value,
            price: parseFloat(price),
            category: category,
            allergens: getSelectedAllergens(),
            dietary: getSelectedDietary(),
            color: currentDishId
                ? (document.getElementById('preview-bg').dataset.color || '#309bcf')
                : '#309bcf',
            _token: document.querySelector('meta[name="csrf-token"]').content
        };

        const url = currentDishId
            ? '/dishes/' + currentDishId + '/update'
            : '/dishes';
        const method = 'POST';

        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': data._token,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
        })
        .then(async response => {
            if (response.ok) {
                window.location.reload();
            } else {
                const text = await response.text();
                console.error('Server error:', response.status, text);
                alert('Error saving dish (HTTP ' + response.status + '). Check console for details.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error saving dish: ' + error.message);
        });
    }

    function deleteDish(id) {
        if (!confirm('Are you sure you want to delete this dish?')) return;

        const token = document.querySelector('meta[name="csrf-token"]').content;

        fetch('/dishes/' + id, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (response.ok) {
                window.location.reload();
            } else {
                alert('Error deleting dish. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting dish. Please try again.');
        });
    }

    document.getElementById('sheet-save-btn').addEventListener('click', saveDish);
</script>
