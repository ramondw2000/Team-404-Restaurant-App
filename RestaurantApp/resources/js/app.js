import './bootstrap';

import interact from 'interactjs';

window.interact = interact;

// Table filter store — shared state across the canvas
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
            if (!el.is_table) return false;
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

// Canvas pan & zoom Alpine component
window.canvasApp = function () {
    return {
        // Pan state
        panX: 0,
        panY: 0,
        isPanning: false,
        panStartX: 0,
        panStartY: 0,
        lastPanX: 0,
        lastPanY: 0,

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
            const imgW = parseInt(this.$el.dataset.imgWidth || 0);
            const imgH = parseInt(this.$el.dataset.imgHeight || 0);

            if (imgW && imgH) {
                const ro = new ResizeObserver(() => this._recomputeInner(imgW, imgH));
                ro.observe(this.$el);
                this._recomputeInner(imgW, imgH);
            }
        },

        _recomputeInner(imgW, imgH) {
            const cw = this.$el.offsetWidth;
            const ch = this.$el.offsetHeight;
            if (!cw || !ch) return;
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

            // Zoom towards cursor position using correct formula for translate+scale(origin:top-left)
            const rect = this.$el.getBoundingClientRect();
            const mouseX = event.clientX - rect.left;
            const mouseY = event.clientY - rect.top;

            this.panX = mouseX + (this.panX - mouseX) * (newScale / this.scale);
            this.panY = mouseY + (this.panY - mouseY) * (newScale / this.scale);
            this.scale = newScale;
        },

        onMouseDown(event) {
            // Only pan on empty canvas (middle click or left click on background)
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

// Canvas element (draggable/resizable/rotatable) Alpine component
window.canvasElement = function (elementData) {
    return {
        elementId: elementData.id,
        x: elementData.x,
        y: elementData.y,
        width: elementData.width,
        height: elementData.height,
        rotation: elementData.rotation,
        zIndex: elementData.z_index,
        isTable: elementData.is_table,
        tableName: elementData.table_name || '',
        statusValue: elementData.status || '',
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
                    this.isTable = event.detail.isTable;
                    this.tableName = event.detail.tableName || '';
                    this.statusValue = event.detail.status || '';
                }
            });
        },

        setupInteract() {
            const el = this.$el;
            const self = this;

            const getCanvas = () => el.closest('[data-canvas-inner]');

            interact(el)
                .draggable({
                    mouseButtons: 1, // left-click only
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
                            self.isDirty = true;
                        },
                        end() {
                            if (self.isDirty) {
                                self.syncToLivewire();
                            }
                        },
                    },
                })
                .resizable({
                    mouseButtons: 1, // left-click only
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
                            self.isDirty = true;
                        },
                        end() {
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

            // Compute element center from canvas-relative percentages so rotation is
            // stable regardless of the element's current CSS transform (bounding rect
            // of a rotated element still returns the same center, but this avoids
            // any ambiguity and works correctly when the canvas is panned/zoomed).
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

            // Capture the angle at drag start so rotation is delta-based (no jump).
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

// Crop tool Alpine component
window.cropTool = function (initialCrop) {
    return {
        cropX: initialCrop?.x ?? 0,
        cropY: initialCrop?.y ?? 0,
        cropW: initialCrop?.w ?? 100,
        cropH: initialCrop?.h ?? 100,

        get cropStyle() {
            return `left:${this.cropX}%;top:${this.cropY}%;width:${this.cropW}%;height:${this.cropH}%;`;
        },

        get overlayStyle() {
            return `left:${this.cropX}%;top:${this.cropY}%;width:${this.cropW}%;height:${this.cropH}%;box-shadow:0 0 0 9999px rgba(0,0,0,0.55);`;
        },

        startDrag(handle, event) {
            event.preventDefault();
            event.stopPropagation();

            // Capture image rect once at drag start — avoids needing $el in move handler
            const preview = event.target.closest('[data-crop-preview-container]')?.querySelector('[data-crop-preview]')
                ?? document.querySelector('[data-crop-preview]');
            if (!preview) return;

            const previewRect = preview.getBoundingClientRect();
            const startX = event.clientX;
            const startY = event.clientY;
            const startCrop = { x: this.cropX, y: this.cropY, w: this.cropW, h: this.cropH };
            const MIN = 5;

            const onMove = (e) => {
                const dx = (e.clientX - startX) / previewRect.width * 100;
                const dy = (e.clientY - startY) / previewRect.height * 100;
                const { x: ox, y: oy, w: ow, h: oh } = startCrop;

                if (handle === 'move') {
                    this.cropX = Math.max(0, Math.min(100 - ow, ox + dx));
                    this.cropY = Math.max(0, Math.min(100 - oh, oy + dy));
                    return;
                }

                let x = ox, y = oy, w = ow, h = oh;
                if (handle.includes('e')) { w = Math.max(MIN, Math.min(100 - x, ow + dx)); }
                if (handle.includes('s')) { h = Math.max(MIN, Math.min(100 - y, oh + dy)); }
                if (handle.includes('w')) {
                    const nx = Math.max(0, Math.min(ox + ow - MIN, ox + dx));
                    w = ow + ox - nx; x = nx;
                }
                if (handle.includes('n')) {
                    const ny = Math.max(0, Math.min(oy + oh - MIN, oy + dy));
                    h = oh + oy - ny; y = ny;
                }
                this.cropX = x; this.cropY = y; this.cropW = w; this.cropH = h;
            };

            const onUp = () => {
                document.removeEventListener('mousemove', onMove);
                document.removeEventListener('mouseup', onUp);
            };

            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup', onUp);
        },

        reset() {
            this.cropX = 0; this.cropY = 0; this.cropW = 100; this.cropH = 100;
        },
    };
};

// Context menu Alpine component
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

// Drag from library to canvas
window.setupLibraryDrag = function (imageId, wireComponent) {
    const thumbnails = document.querySelectorAll(`[data-image-id="${imageId}"]`);
    thumbnails.forEach(thumbnail => {
        thumbnail.addEventListener('dragstart', (e) => {
            e.dataTransfer.setData('image-id', imageId);
            e.dataTransfer.effectAllowed = 'copy';
        });
    });
};
