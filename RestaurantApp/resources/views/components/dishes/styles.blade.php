<style>
    /* ── Sheet ───────────────────────────────────────────── */
    .sheet-overlay {
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s ease;
    }
    .sheet-overlay.open { opacity: 1; pointer-events: auto; }

    .sheet-panel {
        transform: translateX(100%);
        transition: transform 0.35s cubic-bezier(0.32, 0.72, 0, 1);
        height: 100vh;
        height: 100dvh;
    }
    .sheet-panel.open { transform: translateX(0); }

    .sheet-input {
        width: 100%;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        color: #111827;
        background: #fff;
        outline: none;
        transition: border-color 0.15s, box-shadow 0.15s;
        font-family: inherit;
    }
    .sheet-input:focus {
        border-color: #309bcf;
        box-shadow: 0 0 0 3px rgba(48, 155, 207, 0.2);
    }
    .sheet-input::placeholder { color: #9ca3af; }

    .allergen-checkbox { display: none; }
    .allergen-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.4rem 0.75rem;
        border: 1px solid #e5e7eb;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        cursor: pointer;
        user-select: none;
        transition: border-color 0.15s, background 0.15s, color 0.15s;
        color: #374151;
        background: #f9fafb;
    }
    .allergen-checkbox:checked + .allergen-label {
        border-color: #309bcf;
        background: #eaf4fa;
        color: #005693;
    }

    .upload-zone {
        border: 2px dashed #d1d5db;
        border-radius: 0.75rem;
        padding: 1.75rem 1rem;
        text-align: center;
        cursor: pointer;
        transition: border-color 0.15s, background 0.15s;
    }
    .upload-zone:hover { border-color: #309bcf; background: #f0f9ff; }

    /* ── Filter pills ────────────────────────────────────── */
    .filter-btn {
        padding: 0.35rem 1rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        border: 1px solid #e5e7eb;
        background: #fff;
        color: #374151;
        cursor: pointer;
        transition: border-color 0.15s, background 0.15s, color 0.15s;
        white-space: nowrap;
        font-family: inherit;
    }
    .filter-btn:hover { border-color: #309bcf; color: #005693; }
    .filter-btn.filter-active {
        background: #005693;
        border-color: #005693;
        color: #fff;
    }

    /* ── Dish grid ───────────────────────────────────────── */
    .dish-grid { grid-template-columns: repeat(5, 200px); }
    .dish-card  { width: 200px; height: 240px; box-sizing: border-box; }

    @media (max-width: 1279px) {
        .dish-grid { grid-template-columns: repeat(4, 1fr); }
        .dish-card  { width: 100%; height: auto; aspect-ratio: 5 / 6; box-sizing: border-box; }
    }
    @media (max-width: 767px) {
        .dish-grid { grid-template-columns: repeat(3, 1fr); }
    }
    @media (max-width: 639px) {
        .dish-grid { grid-template-columns: repeat(2, 1fr); }
    }
</style>
