<script>
    const roleDescriptions = {
        management:   'Full access to all features including account management, reports, and settings.',
        server:       'Can view and manage orders, mark dishes as served, and access the KDS.',
        chef:         'Can view the kitchen order queue and update dish preparation status.',
        receptionist: 'Can manage table assignments, room service requests, and guest check-in.',
    };

    // ── Tab filter ────────────────────────────────────────
    const TAB_ACTIVE   = ['bg-molveno-blue-500', 'border-molveno-blue-500', 'text-white'];
    const TAB_INACTIVE = ['bg-white', 'border-gray-200', 'text-gray-600', 'hover:border-molveno-blue-300', 'hover:text-molveno-blue-700'];
    const TAB_COUNT_ACTIVE   = ['bg-white/25', 'text-white'];
    const TAB_COUNT_INACTIVE = ['bg-gray-100', 'text-gray-500'];

    function switchTab(btn) {
        const role = btn.dataset.role;
        btn.closest('[class*="flex"]').querySelectorAll('button').forEach(b => {
            const isActive = b === btn;
            b.classList.remove(...TAB_ACTIVE, ...TAB_INACTIVE);
            b.classList.add(...(isActive ? TAB_ACTIVE : TAB_INACTIVE));
            const countEl = b.querySelector('span');
            if (countEl) {
                countEl.classList.remove(...TAB_COUNT_ACTIVE, ...TAB_COUNT_INACTIVE);
                countEl.classList.add(...(isActive ? TAB_COUNT_ACTIVE : TAB_COUNT_INACTIVE));
            }
        });
        let visible = 0;
        document.querySelectorAll('.user-row').forEach(row => {
            const roles = JSON.parse(row.dataset.roles || '[]');
            const show  = role === 'all' || roles.includes(role);
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        document.getElementById('no-users').classList.toggle('hidden', visible > 0);
    }

    // ── Sheet ─────────────────────────────────────────────
    function setRoleCheckboxes(roles) {
        document.querySelectorAll('.role-checkbox').forEach(cb => {
            cb.checked = roles.includes(cb.value);
        });
    }

    function openSheet() {
        document.getElementById('sheet-title').textContent   = 'Add Account';
        document.getElementById('sheet-submit').textContent  = 'Save Account';
        document.getElementById('account-form').action       = '{{ route('accounts.store') }}';
        document.getElementById('form-method').value         = 'POST';
        document.getElementById('form-account-id').value     = '';
        document.getElementById('field-name').value          = '';
        document.getElementById('field-email').value         = '';
        document.getElementById('field-password').value      = '';
        setRoleCheckboxes([]);
        document.getElementById('password-hint').classList.add('hidden');
        updateRoleDesc();
        document.getElementById('sheet-overlay').classList.add('open');
        document.getElementById('sheet-panel').classList.add('open');
        document.getElementById('field-name').focus();
    }

    function openEditSheet(id, name, email, roles) {
        document.getElementById('sheet-title').textContent   = 'Edit Account';
        document.getElementById('sheet-submit').textContent  = 'Update Account';
        document.getElementById('account-form').action       = `/accounts/${id}`;
        document.getElementById('form-method').value         = 'PUT';
        document.getElementById('form-account-id').value     = id;
        document.getElementById('field-name').value          = name;
        document.getElementById('field-email').value         = email;
        document.getElementById('field-password').value      = '';
        setRoleCheckboxes(Array.isArray(roles) ? roles : [roles]);
        document.getElementById('password-hint').classList.remove('hidden');
        updateRoleDesc();
        document.getElementById('sheet-overlay').classList.add('open');
        document.getElementById('sheet-panel').classList.add('open');
    }

    function closeSheet() {
        document.getElementById('sheet-overlay').classList.remove('open');
        document.getElementById('sheet-panel').classList.remove('open');
    }

    function updateRoleDesc() {
        const checked = [...document.querySelectorAll('.role-checkbox:checked')].map(cb => cb.value);
        const desc    = document.getElementById('role-desc');
        if (checked.length > 0) {
            desc.textContent = checked.map(r => roleDescriptions[r]).filter(Boolean).join(' ');
            desc.classList.remove('hidden');
        } else {
            desc.classList.add('hidden');
        }
    }

    // ── Delete modal ──────────────────────────────────────
    function confirmDelete(id, name) {
        document.getElementById('delete-msg').textContent =
            `Are you sure you want to delete the account for "${name}"? This action cannot be undone.`;
        document.getElementById('delete-form').action = `/accounts/${id}`;
        document.getElementById('delete-confirm-btn').onclick = () => {
            document.getElementById('delete-form').submit();
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

    // ── Auto-open sheet on validation error ───────────────
    @if($errors->any())
        @if(old('_method') === 'PUT')
            openEditSheet(
                '{{ old('_account_id') }}',
                '{{ addslashes(old('name', '')) }}',
                '{{ addslashes(old('email', '')) }}',
                {!! json_encode(old('roles', [])) !!}
            );
        @else
            openSheet();
            document.getElementById('field-name').value  = '{{ addslashes(old('name', '')) }}';
            document.getElementById('field-email').value = '{{ addslashes(old('email', '')) }}';
            setRoleCheckboxes({!! json_encode(old('roles', [])) !!});
            updateRoleDesc();
        @endif
    @endif
</script>
