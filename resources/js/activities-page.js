(function () {
    const monthNames = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
    ];
    const dowLabels = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

    const calendarRoot = document.getElementById('lw-activities-calendar');
    if (calendarRoot) {
        const events = JSON.parse(calendarRoot.dataset.events || '[]');
        const eventsByDate = events.reduce((acc, ev) => {
            if (!acc[ev.date]) acc[ev.date] = [];
            acc[ev.date].push(ev);
            return acc;
        }, {});

        let viewYear = new Date().getFullYear();
        let viewMonth = new Date().getMonth();
        let selectedDateStr = null;

        const monthLabel = document.getElementById('lw-calendar-month-label');
        const grid = document.getElementById('lw-calendar-grid');
        const eventsBox = document.getElementById('lw-calendar-events');
        const eventsList = document.getElementById('lw-calendar-events-list');
        const selectedLabel = document.getElementById('lw-calendar-selected-label');
        const dayEmptyMsg = document.getElementById('lw-calendar-day-empty');
        const globalEmptyMsg = document.getElementById('lw-calendar-empty');
        const detailPanel = document.getElementById('lw-calendar-detail');

        function pad(n) {
            return String(n).padStart(2, '0');
        }

        function dateKey(year, month, day) {
            return year + '-' + pad(month + 1) + '-' + pad(day);
        }

        function todayKey() {
            const t = new Date();
            return dateKey(t.getFullYear(), t.getMonth(), t.getDate());
        }

        function pickInitialDate() {
            const today = todayKey();
            if (eventsByDate[today]) {
                return today;
            }
            const sorted = events.map((e) => e.date).sort();
            const future = sorted.find((d) => d >= today);
            return future || sorted[sorted.length - 1] || today;
        }

        function clearSelectedButtons() {
            grid?.querySelectorAll('.lw-calendar-day--selected').forEach((el) => {
                el.classList.remove('lw-calendar-day--selected');
            });
        }

        function markSelectedInGrid(dateStr) {
            clearSelectedButtons();
            if (!grid || !dateStr) return;
            const parts = dateStr.split('-');
            const y = parseInt(parts[0], 10);
            const m = parseInt(parts[1], 10) - 1;
            const d = parseInt(parts[2], 10);
            if (y !== viewYear || m !== viewMonth) return;
            const buttons = grid.querySelectorAll('.lw-calendar-day:not(.lw-calendar-day--other):not(:disabled)');
            buttons.forEach((btn) => {
                if (parseInt(btn.textContent, 10) === d) {
                    btn.classList.add('lw-calendar-day--selected');
                }
            });
        }

        function showEvents(dateStr, day) {
            selectedDateStr = dateStr;
            const list = eventsByDate[dateStr] || [];
            const parts = dateStr.split('-');
            const y = parseInt(parts[0], 10);
            const m = parseInt(parts[1], 10) - 1;

            markSelectedInGrid(dateStr);

            if (detailPanel) {
                detailPanel.hidden = events.length === 0;
            }

            if (events.length === 0) {
                return;
            }

            if (list.length === 0) {
                if (eventsBox) eventsBox.hidden = true;
                if (dayEmptyMsg) dayEmptyMsg.hidden = false;
                return;
            }

            if (dayEmptyMsg) dayEmptyMsg.hidden = true;
            if (!eventsBox || !eventsList) return;

            eventsBox.hidden = false;
            if (selectedLabel) {
                selectedLabel.textContent = 'Agenda ' + day + ' ' + monthNames[m] + ' ' + y;
            }
            eventsList.innerHTML = '';
            list.forEach((ev) => {
                const li = document.createElement('li');
                li.className = 'lw-calendar-event-item';
                li.innerHTML =
                    '<strong>' + escapeHtml(ev.title) + '</strong>' +
                    '<span class="lw-calendar-event-meta">' + escapeHtml(ev.rt) +
                    (ev.lokasi ? ' · ' + escapeHtml(ev.lokasi) : '') + '</span>';
                eventsList.appendChild(li);
            });
        }

        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        function renderCalendar() {
            if (!grid) return;
            monthLabel.textContent = monthNames[viewMonth] + ' ' + viewYear;
            grid.innerHTML = '';
            dowLabels.forEach((d) => {
                const el = document.createElement('div');
                el.className = 'lw-calendar-dow';
                el.textContent = d;
                grid.appendChild(el);
            });

            const first = new Date(viewYear, viewMonth, 1);
            const startDay = first.getDay();
            const daysInMonth = new Date(viewYear, viewMonth + 1, 0).getDate();
            const today = new Date();

            for (let i = 0; i < startDay; i++) {
                const padEl = document.createElement('button');
                padEl.type = 'button';
                padEl.className = 'lw-calendar-day lw-calendar-day--other';
                padEl.disabled = true;
                padEl.setAttribute('aria-hidden', 'true');
                grid.appendChild(padEl);
            }

            for (let day = 1; day <= daysInMonth; day++) {
                const dateStr = dateKey(viewYear, viewMonth, day);
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'lw-calendar-day';
                btn.textContent = String(day);
                btn.setAttribute('aria-label', day + ' ' + monthNames[viewMonth] + ' ' + viewYear);
                if (eventsByDate[dateStr]) btn.classList.add('lw-calendar-day--has-event');
                if (
                    today.getFullYear() === viewYear &&
                    today.getMonth() === viewMonth &&
                    today.getDate() === day
                ) {
                    btn.classList.add('lw-calendar-day--today');
                }
                btn.addEventListener('click', () => showEvents(dateStr, day));
                grid.appendChild(btn);
            }

            if (globalEmptyMsg) {
                globalEmptyMsg.hidden = events.length > 0;
            }

            if (selectedDateStr) {
                const parts = selectedDateStr.split('-');
                const sy = parseInt(parts[0], 10);
                const sm = parseInt(parts[1], 10) - 1;
                const sd = parseInt(parts[2], 10);
                if (sy === viewYear && sm === viewMonth) {
                    showEvents(selectedDateStr, sd);
                } else {
                    clearSelectedButtons();
                }
            }
        }

        document.getElementById('lw-calendar-prev')?.addEventListener('click', () => {
            viewMonth--;
            if (viewMonth < 0) {
                viewMonth = 11;
                viewYear--;
            }
            renderCalendar();
        });
        document.getElementById('lw-calendar-next')?.addEventListener('click', () => {
            viewMonth++;
            if (viewMonth > 11) {
                viewMonth = 0;
                viewYear++;
            }
            renderCalendar();
        });

        if (events.length > 0) {
            const initial = pickInitialDate();
            const parts = initial.split('-');
            viewYear = parseInt(parts[0], 10);
            viewMonth = parseInt(parts[1], 10) - 1;
            selectedDateStr = initial;
        }

        renderCalendar();

        if (events.length > 0 && selectedDateStr) {
            const sd = parseInt(selectedDateStr.split('-')[2], 10);
            showEvents(selectedDateStr, sd);
        } else if (detailPanel) {
            detailPanel.hidden = true;
        }
    }

    const lightbox = document.getElementById('lw-gallery-lightbox');
    const items = window.lwGalleryItems || [];
    if (lightbox && items.length) {
        const img = document.getElementById('lw-lightbox-img');
        const caption = document.getElementById('lw-lightbox-caption');
        const closeBtn = document.getElementById('lw-lightbox-close');

        function openAt(index) {
            const item = items[index];
            if (!item) return;
            img.src = item.url;
            img.alt = item.title;
            caption.textContent = item.title + (item.date ? ' · ' + item.date : '');
            lightbox.hidden = false;
        }

        document.querySelectorAll('.lw-gallery-item').forEach((btn) => {
            btn.addEventListener('click', () => openAt(parseInt(btn.dataset.galleryIndex, 10)));
        });
        closeBtn?.addEventListener('click', () => {
            lightbox.hidden = true;
        });
        lightbox.addEventListener('click', (e) => {
            if (e.target === lightbox) lightbox.hidden = true;
        });
    }

    document.querySelectorAll('.lw-page-subnav-link').forEach((link) => {
        link.addEventListener('click', () => {
            document.querySelectorAll('.lw-page-subnav-link').forEach((l) => l.classList.remove('is-active'));
            link.classList.add('is-active');
        });
    });
})();
