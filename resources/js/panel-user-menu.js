/**
 * Menu akun pengurus di topbar panel: buka/tutup, Escape, klik di luar.
 */
document.addEventListener('DOMContentLoaded', () => {
    const menus = document.querySelectorAll('[data-panel-user-menu]');

    if (!menus.length) {
        return;
    }

    const closeAll = (except = null) => {
        menus.forEach((menu) => {
            if (menu === except) {
                return;
            }

            const trigger = menu.querySelector('[data-panel-user-menu-trigger]');
            const panel = menu.querySelector('[data-panel-user-menu-panel]');

            if (!trigger || !panel) {
                return;
            }

            trigger.setAttribute('aria-expanded', 'false');
            panel.hidden = true;
            menu.classList.remove('lw-panel-user-menu--open');
        });
    };

    menus.forEach((menu) => {
        const trigger = menu.querySelector('[data-panel-user-menu-trigger]');
        const panel = menu.querySelector('[data-panel-user-menu-panel]');

        if (!trigger || !panel) {
            return;
        }

        trigger.addEventListener('click', (event) => {
            event.stopPropagation();
            const willOpen = panel.hidden;
            closeAll(willOpen ? menu : null);

            if (willOpen) {
                panel.hidden = false;
                trigger.setAttribute('aria-expanded', 'true');
                menu.classList.add('lw-panel-user-menu--open');
            }
        });
    });

    document.addEventListener('click', () => closeAll());

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeAll();
        }
    });
});
