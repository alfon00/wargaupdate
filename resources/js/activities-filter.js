(function () {
    const filterChips = document.querySelectorAll('.lw-activities-filter__chip');
    const searchInput = document.getElementById('lw-activities-search');
    const eventList = document.getElementById('lw-activities-event-list');
    const emptyFilter = document.getElementById('lw-activities-empty-filter');
    const announceToggle = document.getElementById('lw-activities-announce-toggle');
    const announcePanel = document.querySelector('.lw-activities-announce-panel');

    let activeFilter = 'semua';

    function cardMatchesFilter(card) {
        if (activeFilter === 'semua') {
            return true;
        }
        if (activeFilter === 'minggu_ini') {
            return card.dataset.mingguIni === '1';
        }
        return card.dataset.status === activeFilter;
    }

    function cardMatchesSearch(card, query) {
        if (!query) {
            return true;
        }
        const haystack = (card.dataset.search || '').toLowerCase();
        return haystack.includes(query);
    }

    function applyFilters() {
        if (!eventList) {
            return;
        }

        const query = (searchInput?.value || '').trim().toLowerCase();
        const cards = eventList.querySelectorAll('.lw-activities-event-card');
        let visibleCount = 0;

        cards.forEach(function (card) {
            const visible = cardMatchesFilter(card) && cardMatchesSearch(card, query);
            card.hidden = !visible;
            visibleCount += visible ? 1 : 0;
        });

        if (emptyFilter) {
            emptyFilter.hidden = visibleCount > 0;
        }
    }

    filterChips.forEach(function (chip) {
        chip.addEventListener('click', function () {
            activeFilter = chip.dataset.filter || 'semua';
            filterChips.forEach(function (other) {
                const active = other === chip;
                other.classList.toggle('is-active', active);
                other.setAttribute('aria-pressed', active ? 'true' : 'false');
            });
            applyFilters();
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', applyFilters);
    }

    if (announceToggle && announcePanel) {
        announceToggle.addEventListener('click', function () {
            const expanded = announcePanel.classList.toggle('is-expanded');
            announceToggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            announceToggle.textContent = expanded ? 'Tampilkan Sedikit' : 'Lihat Semua';
        });
    }
})();
