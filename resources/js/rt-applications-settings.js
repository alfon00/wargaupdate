document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('lw-rt-stamp-settings-modal');
    if (!modal) return;

    const open = () => {
        modal.hidden = false;
        document.body.classList.add('lw-rt-delete-modal-open');
        const focusEl = modal.querySelector('#rt-stamp-file');
        if (focusEl) focusEl.focus();
    };

    const close = () => {
        modal.hidden = true;
        if (!document.querySelector('.lw-rt-delete-modal:not([hidden])')) {
            document.body.classList.remove('lw-rt-delete-modal-open');
        }
    };

    document.querySelectorAll('[data-rt-stamp-settings-open]').forEach((btn) => {
        btn.addEventListener('click', open);
    });

    modal.querySelectorAll('[data-rt-stamp-settings-close]').forEach((el) => {
        el.addEventListener('click', close);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;
        document.querySelectorAll('.lw-rt-delete-modal:not([hidden])').forEach((openModal) => {
            openModal.hidden = true;
        });
        document.body.classList.remove('lw-rt-delete-modal-open');
    });

    const input = document.getElementById('rt-stamp-file');
    const preview = document.getElementById('rt-stamp-preview');
    if (input && preview) {
        input.addEventListener('change', () => {
            const file = input.files && input.files[0];
            if (!file) return;

            if (preview.tagName === 'IMG') {
                preview.src = URL.createObjectURL(file);
                return;
            }

            const img = document.createElement('img');
            img.id = preview.id;
            img.className = 'lw-panel-profile-avatar';
            img.alt = 'Cap resmi RT';
            img.width = 112;
            img.height = 112;
            img.style.objectFit = 'contain';
            img.style.background = '#fff';
            img.style.border = '1px solid #e2e8f0';
            img.style.borderRadius = '8px';
            img.style.padding = '8px';
            img.src = URL.createObjectURL(file);
            preview.replaceWith(img);
        });
    }

    if (!modal.hidden) {
        document.body.classList.add('lw-rt-delete-modal-open');
    }
});
