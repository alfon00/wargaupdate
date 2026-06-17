/**
 * Kanvas tanda tangan generik (surat RT, hapus permanen, dll.).
 */

export function exportSignatureFromCanvas(canvas) {
    if (!canvas) {
        return '';
    }

    const rect = canvas.getBoundingClientRect();
    if (rect.width < 1 || rect.height < 1) {
        return '';
    }

    try {
        return canvas.toDataURL('image/png');
    } catch {
        return '';
    }
}

function loadSignatureOntoCanvas(canvas, ctx, dataUri, ratio, onLoaded) {
    if (!dataUri || !dataUri.startsWith('data:image')) {
        return;
    }

    const img = new Image();
    img.onload = () => {
        const rect = canvas.getBoundingClientRect();
        const cssWidth = rect.width;
        const cssHeight = rect.height;

        ctx.setTransform(1, 0, 0, 1, 0, 0);
        ctx.scale(ratio, ratio);
        ctx.clearRect(0, 0, cssWidth, cssHeight);

        const scale = Math.min(cssWidth / img.width, cssHeight / img.height, 1);
        const drawWidth = img.width * scale;
        const drawHeight = img.height * scale;
        const offsetX = (cssWidth - drawWidth) / 2;
        const offsetY = (cssHeight - drawHeight) / 2;

        ctx.drawImage(img, offsetX, offsetY, drawWidth, drawHeight);
        onLoaded?.();
    };
    img.src = dataUri;
}

/**
 * @param {string} canvasId
 * @param {string} hiddenInputId
 * @param {string|null} clearButtonId
 * @param {{ onSync?: () => void }} [options]
 */
export function initSignaturePad(canvasId, hiddenInputId, clearButtonId = null, options = {}) {
    const canvas = document.getElementById(canvasId);
    const hidden = document.getElementById(hiddenInputId);
    const clearBtn = clearButtonId ? document.getElementById(clearButtonId) : null;

    if (!canvas || !hidden) {
        return null;
    }

    const ctx = canvas.getContext('2d');
    let drawing = false;
    let deviceRatio = window.devicePixelRatio || 1;

    const syncHidden = () => {
        hidden.value = exportSignatureFromCanvas(canvas);
        options.onSync?.();
    };

    const resize = () => {
        const saved = exportSignatureFromCanvas(canvas);
        const rect = canvas.getBoundingClientRect();
        deviceRatio = window.devicePixelRatio || 1;
        canvas.width = Math.floor(rect.width * deviceRatio);
        canvas.height = Math.floor(rect.height * deviceRatio);
        ctx.setTransform(1, 0, 0, 1, 0, 0);
        ctx.scale(deviceRatio, deviceRatio);
        ctx.strokeStyle = '#0f172a';
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';

        if (saved && saved.length > 100) {
            loadSignatureOntoCanvas(canvas, ctx, saved, deviceRatio, syncHidden);
        }
    };

    resize();
    window.addEventListener('resize', resize);

    const pos = (event) => {
        const rect = canvas.getBoundingClientRect();
        const clientX = event.touches ? event.touches[0].clientX : event.clientX;
        const clientY = event.touches ? event.touches[0].clientY : event.clientY;

        return {
            x: clientX - rect.left,
            y: clientY - rect.top,
        };
    };

    const start = (event) => {
        event.preventDefault();
        drawing = true;
        const { x, y } = pos(event);
        ctx.beginPath();
        ctx.moveTo(x, y);
    };

    const move = (event) => {
        if (!drawing) {
            return;
        }
        event.preventDefault();
        const { x, y } = pos(event);
        ctx.lineTo(x, y);
        ctx.stroke();
    };

    const end = () => {
        drawing = false;
    };

    canvas.addEventListener('mousedown', start);
    canvas.addEventListener('mousemove', move);
    canvas.addEventListener('mouseup', end);
    canvas.addEventListener('mouseleave', end);
    canvas.addEventListener('touchstart', start, { passive: false });
    canvas.addEventListener('touchmove', move, { passive: false });
    canvas.addEventListener('touchend', end);

    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            const rect = canvas.getBoundingClientRect();
            ctx.clearRect(0, 0, rect.width, rect.height);
            hidden.value = '';
            options.onSync?.();
        });
    }

    canvas.addEventListener('mouseup', syncHidden);
    canvas.addEventListener('touchend', syncHidden);

    if (hidden.value && hidden.value.startsWith('data:image')) {
        loadSignatureOntoCanvas(canvas, ctx, hidden.value, deviceRatio, syncHidden);
    }

    return {
        clear() {
            const rect = canvas.getBoundingClientRect();
            ctx.clearRect(0, 0, rect.width, rect.height);
            hidden.value = '';
        },
        sync() {
            syncHidden();
            return hidden.value;
        },
        resize() {
            resize();
        },
    };
}
