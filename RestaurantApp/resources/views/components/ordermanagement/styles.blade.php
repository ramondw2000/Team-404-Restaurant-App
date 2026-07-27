<style>
    /* ── Filter pills ─────────────────────────────────── */
    .filter-btn { white-space: nowrap; }
    .filter-btn:hover { border-color: #309bcf; color: #005693; }
    .filter-btn.filter-active { background: #005693; border-color: #005693; color: #fff; }
    .filter-btn.filter-active .diet-icon-veg  { background: #fff !important; }
    .filter-btn.filter-active .diet-icon-vegan { background: #fff !important; }

    /* ── Dish card ────────────────────────────────────── */
    .dish-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        display: flex;
        align-items: stretch;
        overflow: hidden;
        transition: box-shadow .15s, border-color .15s;
        cursor: default;
    }
    .dish-card:hover { box-shadow: 0 4px 14px rgba(0,0,0,.08); border-color: #bfdbfe; }

    .dish-card-body {
        flex: 1;
        padding: 0.875rem 1rem;
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .dish-card-image {
        width: 108px;
        min-width: 108px;
        height: 120px;
        position: relative;
        background: #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    @media (max-width: 479px) {
        .dish-card-image { width: 88px; min-width: 88px; height: 100px; }
    }

    .dish-card-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    /* ── Add button — lives inside the image container ── */
    .btn-add-dish {
        position: absolute;
        bottom: 0.5rem;
        right: 0.5rem;
        width: 2rem;
        height: 2rem;
        border-radius: 9999px;
        background: #0084c4;
        color: #fff;
        border: 2px solid #fff;
        box-shadow: 0 2px 8px rgba(0,84,147,.35);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background .15s, transform .1s;
        flex-shrink: 0;
    }
    .btn-add-dish:hover { background: #006ead; transform: scale(1.08); }

    /* ── Qty badge on card ───────────────────────────── */
    .qty-badge {
        position: absolute;
        top: 0.4rem;
        right: 0.4rem;
        min-width: 1.25rem;
        height: 1.25rem;
        padding: 0 0.3rem;
        border-radius: 9999px;
        background: #005693;
        color: #fff;
        font-size: 0.625rem;
        font-weight: 800;
        display: none;
        align-items: center;
        justify-content: center;
        box-shadow: 0 1px 4px rgba(0,0,0,.25);
    }
    .qty-badge.visible { display: flex; }

    /* ── Note area ───────────────────────────────────── */
    .note-area {
        width: 100%;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 0.375rem 0.625rem;
        font-size: 0.75rem;
        color: #374151;
        font-family: inherit;
        resize: none;
        outline: none;
        transition: border-color .15s, box-shadow .15s;
        line-height: 1.5;
    }
    .note-area:focus { border-color: #309bcf; box-shadow: 0 0 0 3px rgba(48,155,207,.15); }
    .note-area::placeholder { color: #9ca3af; }

    /* ── Qty stepper ─────────────────────────────────── */
    .qty-btn {
        width: 1.625rem;
        height: 1.625rem;
        border-radius: 9999px;
        border: 1.5px solid #0084c4;
        color: #0084c4;
        background: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background .12s, color .12s;
        flex-shrink: 0;
        font-family: inherit;
    }
    .qty-btn:hover { background: #0084c4; color: #fff; }

    /* ── Sticky order bar ────────────────────────────── */
    #order-bar {
        transition: transform .3s cubic-bezier(.4,0,.2,1), opacity .3s;
    }
    #order-bar.hidden-bar {
        transform: translateY(100%);
        opacity: 0;
        pointer-events: none;
    }

    /* ── Add-dish overlay ────────────────────────────── */
    .add-overlay {
        opacity: 0;
        pointer-events: none;
        transition: opacity .2s ease;
    }
    .add-overlay.open { opacity: 1; pointer-events: auto; }
    .add-modal {
        transform: translateY(16px) scale(.98);
        transition: transform .2s ease, opacity .2s ease;
        opacity: 0;
    }
    .add-overlay.open .add-modal { transform: translateY(0) scale(1); opacity: 1; }

    /* ── Review screen (full-viewport slide-up) ─────── */
    #review-screen {
        transform: translateY(100%);
        transition: transform .35s cubic-bezier(.32,.72,0,1);
    }
    #review-screen.open {
        transform: translateY(0);
    }

    /* ── Toast ───────────────────────────────────────── */
    #toast {
        transition: opacity .3s, transform .3s;
        opacity: 0;
        transform: translateY(-8px);
        pointer-events: none;
    }
    #toast.show { opacity: 1; transform: translateY(0); pointer-events: auto; }

    /* ── Scrollbar hide (for filter row) ─────────────── */
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
