const MIN_SCALE = 1;

let zoomFloorRaf = 0;

function applyZoomFloor() {
    const vv = window.visualViewport;
    if (!vv) {
        return;
    }

    const scale = vv.scale;
    if (scale < MIN_SCALE) {
        document.documentElement.style.zoom = String(MIN_SCALE / scale);
        document.documentElement.classList.add('lw-zoom-floor-active');
    } else {
        document.documentElement.style.zoom = '';
        document.documentElement.classList.remove('lw-zoom-floor-active');
    }
}

function scheduleZoomFloor() {
    if (zoomFloorRaf) {
        cancelAnimationFrame(zoomFloorRaf);
    }

    zoomFloorRaf = requestAnimationFrame(() => {
        zoomFloorRaf = 0;
        applyZoomFloor();
    });
}

if (window.visualViewport) {
    visualViewport.addEventListener('resize', scheduleZoomFloor);
    visualViewport.addEventListener('scroll', scheduleZoomFloor);
    window.addEventListener('resize', scheduleZoomFloor);
    applyZoomFloor();
}
