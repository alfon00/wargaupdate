/**
 * Menu sidebar panel pengurus (mobile): tutup saat navigasi, Escape, atau backdrop.
 */
document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('lw-panel-menu-toggle');
    const sidebar = document.querySelector('.lw-panel-sidebar');
    const menuBtn = document.querySelector('.lw-panel-menu-btn');

    if (!toggle || !sidebar) {
        return;
    }

    const mobileQuery = window.matchMedia('(max-width: 767px)');

    const setOpen = (open) => {
        toggle.checked = open;
        syncState();
    };

    const syncState = () => {
        const open = toggle.checked;
        document.body.classList.toggle('lw-panel-menu-open', open && mobileQuery.matches);

        if (menuBtn) {
            menuBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
            menuBtn.setAttribute('aria-label', open ? 'Tutup menu panel' : 'Buka menu panel');
        }
    };

    toggle.addEventListener('change', syncState);

    sidebar.querySelectorAll('a.lw-panel-nav-link, a.lw-admin-nav-link').forEach((link) => {
        link.addEventListener('click', () => {
            if (mobileQuery.matches) {
                setOpen(false);
            }
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && toggle.checked) {
            setOpen(false);
        }
    });

    mobileQuery.addEventListener('change', () => {
        if (!mobileQuery.matches && toggle.checked) {
            setOpen(false);
        }
        syncState();
    });

    syncState();
});
