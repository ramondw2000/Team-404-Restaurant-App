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

    /* ── Photo helpers ─────────────────────────────────── */
    function resetPhotoInput() {
        document.getElementById('dish-photo').value = '';
    }

    function setUploadZonePreview(src) {
        const img = document.getElementById('upload-preview-img');
        const placeholder = document.getElementById('upload-placeholder');
        if (src) {
            img.src = src;
            img.classList.remove('hidden');
            placeholder.classList.add('hidden');
        } else {
            img.src = '';
            img.classList.add('hidden');
            placeholder.classList.remove('hidden');
        }
    }

    function setEditPhotoPreview(src, color) {
        const previewBg = document.getElementById('preview-bg');
        const img = document.getElementById('current-photo-img');
        const placeholder = document.getElementById('preview-placeholder');
        previewBg.style.backgroundColor = color || '#309bcf';
        previewBg.dataset.color = color || '#309bcf';
        if (src) {
            img.src = src;
            img.classList.remove('hidden');
            placeholder.classList.add('hidden');
        } else {
            img.src = '';
            img.classList.add('hidden');
            placeholder.classList.remove('hidden');
        }
    }

    document.getElementById('dish-photo').addEventListener('change', function () {
        const file = this.files[0];
        if (!file) { return; }
        const objectUrl = URL.createObjectURL(file);
        if (!document.getElementById('upload-zone-wrapper').classList.contains('hidden')) {
            setUploadZonePreview(objectUrl);
        } else {
            setEditPhotoPreview(objectUrl, document.getElementById('preview-bg').dataset.color);
        }
    });

    const uploadZone = document.querySelector('.upload-zone');
    uploadZone.addEventListener('dragover', e => { e.preventDefault(); uploadZone.style.borderColor = '#309bcf'; uploadZone.style.background = '#f0f9ff'; });
    uploadZone.addEventListener('dragleave', () => { uploadZone.style.borderColor = ''; uploadZone.style.background = ''; });
    uploadZone.addEventListener('drop', e => {
        e.preventDefault();
        uploadZone.style.borderColor = '';
        uploadZone.style.background = '';
        const file = e.dataTransfer.files[0];
        if (file && file.type.startsWith('image/')) {
            const dt = new DataTransfer();
            dt.items.add(file);
            document.getElementById('dish-photo').files = dt.files;
            document.getElementById('dish-photo').dispatchEvent(new Event('change'));
        }
    });

    /* ── Create mode ───────────────────────────────────── */
    function openCreateSheet() {
        currentDishId = null;
        document.getElementById('dish-form').action = '{{ route('dishes.store') }}';
        document.getElementById('sheet-title').textContent    = 'Add New Dish';
        document.getElementById('sheet-subtitle').textContent = 'Fill in the details below to add a dish to the menu.';
        document.getElementById('sheet-save-btn').textContent = 'Save Dish';
        document.getElementById('delete-dish-form').classList.add('hidden');
        document.getElementById('current-photo-preview').classList.add('hidden');
        document.getElementById('upload-zone-wrapper').classList.remove('hidden');
        document.getElementById('dish-name').value   = '';
        document.getElementById('dish-desc').value   = '';
        document.getElementById('dish-price').value  = '';
        document.getElementById('dish-category').value = '';
        document.getElementById('dish-color').value  = '#309bcf';
        document.querySelectorAll('.allergen-checkbox').forEach(cb => cb.checked = false);
        resetPhotoInput();
        setUploadZonePreview(null);
        openSheet();
    }

    /* ── Edit mode ─────────────────────────────────────── */
    function openEditSheet(card) {
        const name = card.querySelector('.font-bold').textContent.trim();
        currentDishId = card.dataset.id;

        document.getElementById('dish-form').action = '/dishes/' + currentDishId + '/update';
        document.getElementById('delete-dish-form').action = '/dishes/' + currentDishId;
        document.getElementById('sheet-title').textContent    = 'Edit Dish';
        document.getElementById('sheet-subtitle').innerHTML   =
            'Editing: <span class="font-semibold text-gray-600">' + name + '</span>';
        document.getElementById('sheet-save-btn').textContent = 'Update Dish';
        document.getElementById('delete-dish-form').classList.remove('hidden');

        document.getElementById('upload-zone-wrapper').classList.add('hidden');
        document.getElementById('current-photo-preview').classList.remove('hidden');
        setEditPhotoPreview(card.dataset.photo || '', card.dataset.color || '#309bcf');

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

        document.getElementById('dish-color').value = card.dataset.color || '#309bcf';
        resetPhotoInput();
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
