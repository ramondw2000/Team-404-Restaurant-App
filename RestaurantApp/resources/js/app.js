import './bootstrap';

import interact from 'interactjs';

window.interact = interact;

// ─── Table filter store — shared state across the canvas ───────────────────
document.addEventListener('alpine:init', () => {
    window.Alpine.store('filters', {
        statuses: [],
        name: '',
        seats: '',

        get active() {
            return this.statuses.length > 0 || this.name !== '' || this.seats !== '';
        },

        toggleStatus(status) {
            const idx = this.statuses.indexOf(status);
            if (idx >= 0) {
                this.statuses.splice(idx, 1);
            } else {
                this.statuses.push(status);
            }
        },

        matches(el) {
            const nameOk = this.name === ''
                || (el.table_name || '').toLowerCase().includes(this.name.toLowerCase());
            const statusOk = this.statuses.length === 0
                || this.statuses.includes(el.status);
            const seatsOk = this.seats === ''
                || (el.seat_count !== null && el.seat_count === parseInt(this.seats));
            return nameOk && statusOk && seatsOk;
        },

        clear() {
            this.statuses = [];
            this.name = '';
            this.seats = '';
        },
    });
});

// ─── Snap guide system ─────────────────────────────────────────────────────

/**
 * Edge alignment snap logic. Compares a dragging element's four edges against
 * all other elements' edges and returns snap deltas + guide line positions.
 */
const SNAP_THRESHOLD_PX = 5;

/**
 * @param {object} moving      - { x, y, width, height } in % of canvas
 * @param {string} movingId    - element ID of the moving element
 * @param {HTMLElement} canvas - [data-canvas-inner] element
 * @returns {{ dx: number, dy: number, guides: Array<{axis:'x'|'y', position:number}> }}
 */
function computeSnap(moving, movingId, canvas) {
    const canvasRect = canvas.getBoundingClientRect();
    const result = { dx: 0, dy: 0, guides: [] };

    // Collect edges of all other elements
    const others = canvas.querySelectorAll('[data-element-id]');
    const otherEdges = { x: [], y: [] };

    others.forEach(el => {
        const id = el.dataset.elementId;
        if (id == movingId) return;

        // Read percentages from the Alpine component's position style
        const style = el.style;
        const left = parseFloat(style.left) || 0;
        const top = parseFloat(style.top) || 0;
        const width = parseFloat(style.width) || 0;
        const height = parseFloat(style.height) || 0;

        otherEdges.x.push(left, left + width);
        otherEdges.y.push(top, top + height);
    });

    // Moving element edges
    const movingLeft = moving.x;
    const movingRight = moving.x + moving.width;
    const movingTop = moving.y;
    const movingBottom = moving.y + moving.height;

    const movingEdgesX = [movingLeft, movingRight];
    const movingEdgesY = [movingTop, movingBottom];

    // Convert threshold from px to % of canvas
    const thresholdX = (SNAP_THRESHOLD_PX / canvasRect.width) * 100;
    const thresholdY = (SNAP_THRESHOLD_PX / canvasRect.height) * 100;

    // Find closest X snap
    let bestDx = Infinity;
    let snapGuideX = null;
    for (const me of movingEdgesX) {
        for (const oe of otherEdges.x) {
            const diff = oe - me;
            if (Math.abs(diff) < Math.abs(bestDx) && Math.abs(diff) <= thresholdX) {
                bestDx = diff;
                snapGuideX = oe;
            }
        }
    }
    if (snapGuideX !== null) {
        result.dx = bestDx;
        result.guides.push({ axis: 'x', position: snapGuideX });
    }

    // Find closest Y snap
    let bestDy = Infinity;
    let snapGuideY = null;
    for (const me of movingEdgesY) {
        for (const oe of otherEdges.y) {
            const diff = oe - me;
            if (Math.abs(diff) < Math.abs(bestDy) && Math.abs(diff) <= thresholdY) {
                bestDy = diff;
                snapGuideY = oe;
            }
        }
    }
    if (snapGuideY !== null) {
        result.dy = bestDy;
        result.guides.push({ axis: 'y', position: snapGuideY });
    }

    return result;
}

/**
 * Render snap guide lines into the [data-snap-guides] container.
 * @param {HTMLElement} container
 * @param {Array<{axis:'x'|'y', position:number}>} guides
 */
function renderGuides(container, guides) {
    if (!container) return;
    container.innerHTML = '';

    for (const guide of guides) {
        const line = document.createElement('div');
        line.style.position = 'absolute';
        line.style.backgroundColor = '#22d3ee'; // cyan-400
        line.style.zIndex = '9000';

        if (guide.axis === 'x') {
            line.style.left = `${guide.position}%`;
            line.style.top = '0';
            line.style.width = '1px';
            line.style.height = '100%';
        } else {
            line.style.top = `${guide.position}%`;
            line.style.left = '0';
            line.style.height = '1px';
            line.style.width = '100%';
        }

        container.appendChild(line);
    }
}

function clearGuides(canvas) {
    const container = canvas?.querySelector('[data-snap-guides]');
    if (container) container.innerHTML = '';
}

// ─── Canvas pan & zoom Alpine component ────────────────────────────────────
window.canvasApp = function () {
    return {
        // Pan state
        panX: 0,
        panY: 0,
        isPanning: false,
        panStartX: 0,
        panStartY: 0,

        // Zoom state
        scale: 1,
        minScale: 0.2,
        maxScale: 4,

        // Touch state
        lastTouchDistance: null,

        // Context menu state
        menuShow: false,
        menuX: 0,
        menuY: 0,
        menuTargetId: null,

        // Canvas inner dimensions (computed to match image aspect ratio)
        canvasInnerW: 0,
        canvasInnerH: 0,
        canvasInnerLeft: 0,
        canvasInnerTop: 0,

        get transformStyle() {
            return `transform: translate(${this.panX}px, ${this.panY}px) scale(${this.scale});`;
        },

        get canvasInnerStyle() {
            if (this.canvasInnerW && this.canvasInnerH) {
                return `position:absolute;width:${this.canvasInnerW}px;height:${this.canvasInnerH}px;left:${this.canvasInnerLeft}px;top:${this.canvasInnerTop}px;`;
            }
            return 'position:absolute;inset:0;';
        },

        init() {
            const ro = new ResizeObserver(() => this._recomputeInner());
            ro.observe(this.$el);
            this._recomputeInner();

            const mo = new MutationObserver(() => this._recomputeInner());
            mo.observe(this.$el, { attributes: true, attributeFilter: ['data-img-width', 'data-img-height'] });
        },

        _recomputeInner() {
            const imgW = parseInt(this.$el.dataset.imgWidth || 0);
            const imgH = parseInt(this.$el.dataset.imgHeight || 0);
            const cw = this.$el.offsetWidth;
            const ch = this.$el.offsetHeight;
            if (!cw || !ch || !imgW || !imgH) return;
            const imgAspect = imgW / imgH;
            const containerAspect = cw / ch;
            if (imgAspect > containerAspect) {
                this.canvasInnerW = cw;
                this.canvasInnerH = Math.round(cw / imgAspect);
            } else {
                this.canvasInnerH = ch;
                this.canvasInnerW = Math.round(ch * imgAspect);
            }
            this.canvasInnerLeft = Math.round((cw - this.canvasInnerW) / 2);
            this.canvasInnerTop = Math.round((ch - this.canvasInnerH) / 2);
        },

        onWheel(event) {
            event.preventDefault();
            const zoomFactor = event.deltaY < 0 ? 1.1 : 0.9;
            const newScale = Math.min(this.maxScale, Math.max(this.minScale, this.scale * zoomFactor));

            const rect = this.$el.getBoundingClientRect();
            const mouseX = event.clientX - rect.left;
            const mouseY = event.clientY - rect.top;

            this.panX = mouseX + (this.panX - mouseX) * (newScale / this.scale);
            this.panY = mouseY + (this.panY - mouseY) * (newScale / this.scale);
            this.scale = newScale;
        },

        onMouseDown(event) {
            if (event.button === 1 || event.target === event.currentTarget || event.target.dataset.pannable === 'true') {
                this.isPanning = true;
                this.panStartX = event.clientX - this.panX;
                this.panStartY = event.clientY - this.panY;
                event.preventDefault();
            }
        },

        onMouseMove(event) {
            if (!this.isPanning) return;
            this.panX = event.clientX - this.panStartX;
            this.panY = event.clientY - this.panStartY;
        },

        onMouseUp() {
            this.isPanning = false;
        },

        onTouchStart(event) {
            if (event.touches.length === 2) {
                const dx = event.touches[0].clientX - event.touches[1].clientX;
                const dy = event.touches[0].clientY - event.touches[1].clientY;
                this.lastTouchDistance = Math.sqrt(dx * dx + dy * dy);
            } else if (event.touches.length === 1) {
                this.isPanning = true;
                this.panStartX = event.touches[0].clientX - this.panX;
                this.panStartY = event.touches[0].clientY - this.panY;
            }
        },

        onTouchMove(event) {
            event.preventDefault();
            if (event.touches.length === 2 && this.lastTouchDistance !== null) {
                const dx = event.touches[0].clientX - event.touches[1].clientX;
                const dy = event.touches[0].clientY - event.touches[1].clientY;
                const distance = Math.sqrt(dx * dx + dy * dy);
                const zoomFactor = distance / this.lastTouchDistance;
                this.scale = Math.min(this.maxScale, Math.max(this.minScale, this.scale * zoomFactor));
                this.lastTouchDistance = distance;
            } else if (event.touches.length === 1 && this.isPanning) {
                this.panX = event.touches[0].clientX - this.panStartX;
                this.panY = event.touches[0].clientY - this.panStartY;
            }
        },

        onTouchEnd() {
            this.isPanning = false;
            this.lastTouchDistance = null;
        },

        resetView() {
            this.panX = 0;
            this.panY = 0;
            this.scale = 1;
        },

        openMenu(x, y, id) {
            this.menuX = x;
            this.menuY = y;
            this.menuTargetId = id;
            this.menuShow = true;
        },

        closeMenu() {
            this.menuShow = false;
            this.menuTargetId = null;
        },
    };
};

// ─── Canvas element (draggable/resizable/rotatable) Alpine component ───────
window.canvasElement = function (elementData) {
    return {
        elementId: elementData.id,
        x: elementData.x,
        y: elementData.y,
        width: elementData.width,
        height: elementData.height,
        rotation: elementData.rotation,
        zIndex: elementData.z_index,
        tableName: elementData.table_name || '',
        seatCount: elementData.seat_count,
        statusValue: elementData.status || '',
        imagePath: elementData.image_path || '',
        isDirty: false,

        get positionStyle() {
            return `left:${this.x}%;top:${this.y}%;width:${this.width}%;height:${this.height}%;transform:rotate(${this.rotation}deg);z-index:${this.zIndex};`;
        },

        get badgeClasses() {
            const map = {
                Available: 'bg-green-100 text-green-800 ring-1 ring-inset ring-green-600/20',
                Reserved: 'bg-amber-100 text-amber-800 ring-1 ring-inset ring-amber-600/20',
                Occupied: 'bg-red-100 text-red-800 ring-1 ring-inset ring-red-600/20',
            };
            return (map[this.statusValue] || 'bg-gray-100 text-gray-700') + ' inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-semibold shadow-sm max-w-full truncate';
        },

        init() {
            this.setupInteract();
            window.addEventListener('element-properties-updated', (event) => {
                if (event.detail.id == this.elementId) {
                    this.tableName = event.detail.tableName || '';
                    this.seatCount = event.detail.seatCount;
                    this.statusValue = event.detail.status || '';
                    if (event.detail.width != null) this.width = event.detail.width;
                    if (event.detail.height != null) this.height = event.detail.height;
                    if (event.detail.imagePath) {
                        this.imagePath = event.detail.imagePath;
                        // Swap the image src
                        const img = this.$el.querySelector('img');
                        if (img) img.src = event.detail.imagePath;
                    }
                }
            });
        },

        setupInteract() {
            const el = this.$el;
            const self = this;

            const getCanvas = () => el.closest('[data-canvas-inner]');
            const isSnapEnabled = () => {
                const canvasOuter = el.closest('[data-snap-enabled]');
                return canvasOuter?.dataset.snapEnabled === 'true';
            };

            interact(el)
                .draggable({
                    mouseButtons: 1,
                    ignoreFrom: '[data-rotate-handle]',
                    listeners: {
                        move(event) {
                            const canvas = getCanvas();
                            if (!canvas) return;
                            const canvasRect = canvas.getBoundingClientRect();
                            const dx = (event.dx / canvasRect.width) * 100;
                            const dy = (event.dy / canvasRect.height) * 100;
                            self.x = Math.max(0, Math.min(100 - self.width, self.x + dx));
                            self.y = Math.max(0, Math.min(100 - self.height, self.y + dy));

                            // Snap
                            if (isSnapEnabled()) {
                                const snap = computeSnap(
                                    { x: self.x, y: self.y, width: self.width, height: self.height },
                                    self.elementId,
                                    canvas,
                                );
                                self.x = Math.max(0, Math.min(100 - self.width, self.x + snap.dx));
                                self.y = Math.max(0, Math.min(100 - self.height, self.y + snap.dy));
                                renderGuides(canvas.querySelector('[data-snap-guides]'), snap.guides);
                            }

                            self.isDirty = true;
                        },
                        end() {
                            clearGuides(getCanvas());
                            if (self.isDirty) {
                                self.syncToLivewire();
                            }
                        },
                    },
                })
                .resizable({
                    mouseButtons: 1,
                    ignoreFrom: '[data-rotate-handle]',
                    edges: { left: true, right: true, bottom: true, top: true },
                    margin: 8,
                    listeners: {
                        move(event) {
                            const canvas = getCanvas();
                            if (!canvas) return;
                            const canvasRect = canvas.getBoundingClientRect();
                            const dw = (event.deltaRect.width / canvasRect.width) * 100;
                            const dh = (event.deltaRect.height / canvasRect.height) * 100;
                            const dleft = (event.deltaRect.left / canvasRect.width) * 100;
                            const dtop = (event.deltaRect.top / canvasRect.height) * 100;

                            self.width = Math.max(2, self.width + dw);
                            self.height = Math.max(2, self.height + dh);
                            self.x += dleft;
                            self.y += dtop;

                            // Snap during resize
                            if (isSnapEnabled()) {
                                const snap = computeSnap(
                                    { x: self.x, y: self.y, width: self.width, height: self.height },
                                    self.elementId,
                                    canvas,
                                );
                                // Apply snap to whichever edge is being resized
                                if (snap.dx !== 0) {
                                    self.width = Math.max(2, self.width + snap.dx);
                                }
                                if (snap.dy !== 0) {
                                    self.height = Math.max(2, self.height + snap.dy);
                                }
                                renderGuides(canvas.querySelector('[data-snap-guides]'), snap.guides);
                            }

                            self.isDirty = true;
                        },
                        end() {
                            clearGuides(getCanvas());
                            if (self.isDirty) {
                                self.syncToLivewire();
                            }
                        },
                    },
                    modifiers: [
                        interact.modifiers.restrictSize({
                            min: { width: 20, height: 20 },
                        }),
                    ],
                });
        },

        syncToLivewire() {
            this.isDirty = false;
            this.$dispatch('element-transformed', {
                elementId: this.elementId,
                x: this.x,
                y: this.y,
                width: this.width,
                height: this.height,
                rotation: this.rotation,
            });
        },

        startRotate(event) {
            event.preventDefault();
            event.stopPropagation();

            const freeRotate = event.shiftKey || event.altKey;

            const canvasInner = this.$el.closest('[data-canvas-inner]');
            const canvasRect = canvasInner
                ? canvasInner.getBoundingClientRect()
                : this.$el.getBoundingClientRect();

            const centerX = canvasInner
                ? canvasRect.left + (this.x + this.width / 2) / 100 * canvasRect.width
                : canvasRect.left + canvasRect.width / 2;
            const centerY = canvasInner
                ? canvasRect.top + (this.y + this.height / 2) / 100 * canvasRect.height
                : canvasRect.top + canvasRect.height / 2;

            const startAngle = Math.atan2(event.clientY - centerY, event.clientX - centerX) * (180 / Math.PI);
            const startRotation = this.rotation;

            const onMove = (e) => {
                const currentAngle = Math.atan2(e.clientY - centerY, e.clientX - centerX) * (180 / Math.PI);
                let delta = currentAngle - startAngle;
                let newRotation = ((startRotation + delta) % 360 + 360) % 360;

                if (!freeRotate) {
                    newRotation = ((Math.round(newRotation / 15) * 15) % 360 + 360) % 360;
                }

                this.rotation = newRotation;
            };

            const onUp = () => {
                document.removeEventListener('pointermove', onMove);
                document.removeEventListener('pointerup', onUp);
                this.syncToLivewire();
            };

            document.addEventListener('pointermove', onMove);
            document.addEventListener('pointerup', onUp);
        },
    };
};

// ─── Context menu Alpine component ─────────────────────────────────────────
window.contextMenu = function () {
    return {
        show: false,
        menuX: 0,
        menuY: 0,
        targetElementId: null,

        open({ x, y, id }) {
            this.menuX = x;
            this.menuY = y;
            this.targetElementId = id;
            this.show = true;
        },

        close() {
            this.show = false;
            this.targetElementId = null;
        },
    };
};
