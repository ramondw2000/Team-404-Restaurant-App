<style>
    /* ── Sheet (legacy JS-driven open/close) ─────────────── */
    .sheet-overlay {
        opacity: 0; pointer-events: none;
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
</style>
