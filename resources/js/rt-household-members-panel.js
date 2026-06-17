/**
 * RT panel: pagination and scroll for household members table on KK detail page.
 */
const MEMBERS_PAGE_SIZE = 10;
const MEMBERS_FOCUS_SCROLL_OFFSET = 80;

function scrollToCurrentMemberRow(root) {
    const current = root.querySelector('[data-rt-member-row].is-current-resident');

    if (!current) {
        return;
    }

    const top = current.getBoundingClientRect().top + window.scrollY - MEMBERS_FOCUS_SCROLL_OFFSET;
    window.scrollTo({ top: Math.max(0, top), behavior: 'smooth' });
}

function initHouseholdMembersPanel(root) {
    const tbody = root.querySelector('[data-rt-members-tbody]');

    if (!tbody) {
        return;
    }

    const allRows = [...tbody.querySelectorAll('[data-rt-member-row]')];
    const summaryEl = root.querySelector('[data-rt-members-summary]');
    const paginationEl = root.querySelector('[data-rt-members-pagination]');

    let currentPage = 1;

    function renderPagination(totalPages) {
        if (!paginationEl) {
            return;
        }

        paginationEl.innerHTML = '';

        if (totalPages <= 1) {
            paginationEl.hidden = true;

            return;
        }

        paginationEl.hidden = false;

        const prevBtn = document.createElement('button');
        prevBtn.type = 'button';
        prevBtn.className = 'lw-rt-household-members-page-btn';
        prevBtn.setAttribute('aria-label', 'Halaman sebelumnya');
        prevBtn.textContent = '‹';
        prevBtn.disabled = currentPage <= 1;
        prevBtn.addEventListener('click', () => {
            currentPage = Math.max(1, currentPage - 1);
            applyPagination();
        });
        paginationEl.appendChild(prevBtn);

        for (let page = 1; page <= totalPages; page += 1) {
            const pageBtn = document.createElement('button');
            pageBtn.type = 'button';
            pageBtn.className = 'lw-rt-household-members-page-btn';
            pageBtn.textContent = String(page);
            pageBtn.setAttribute('aria-label', `Halaman ${page}`);
            pageBtn.setAttribute('aria-current', page === currentPage ? 'page' : 'false');

            if (page === currentPage) {
                pageBtn.classList.add('is-active');
            }

            pageBtn.addEventListener('click', () => {
                currentPage = page;
                applyPagination();
            });
            paginationEl.appendChild(pageBtn);
        }

        const nextBtn = document.createElement('button');
        nextBtn.type = 'button';
        nextBtn.className = 'lw-rt-household-members-page-btn';
        nextBtn.setAttribute('aria-label', 'Halaman berikutnya');
        nextBtn.textContent = '›';
        nextBtn.disabled = currentPage >= totalPages;
        nextBtn.addEventListener('click', () => {
            currentPage = Math.min(totalPages, currentPage + 1);
            applyPagination();
        });
        paginationEl.appendChild(nextBtn);
    }

    function updateSummary(totalCount, start, end) {
        if (!summaryEl) {
            return;
        }

        summaryEl.textContent = `Menampilkan ${start}–${end} dari ${totalCount} anggota keluarga`;
    }

    function applyPagination() {
        const totalCount = allRows.length;
        const totalPages = Math.max(1, Math.ceil(totalCount / MEMBERS_PAGE_SIZE));

        if (currentPage > totalPages) {
            currentPage = 1;
        }

        const startIndex = (currentPage - 1) * MEMBERS_PAGE_SIZE;
        const pageRows = allRows.slice(startIndex, startIndex + MEMBERS_PAGE_SIZE);
        const visibleSet = new Set(pageRows);

        allRows.forEach((row) => {
            row.hidden = !visibleSet.has(row);
        });

        pageRows.forEach((row, index) => {
            const noCell = row.querySelector('[data-rt-member-no]');

            if (noCell) {
                noCell.textContent = String(startIndex + index + 1);
            }
        });

        const start = startIndex + 1;
        const end = startIndex + pageRows.length;
        updateSummary(totalCount, start, end);
        renderPagination(totalPages);
    }

    applyPagination();
    scrollToCurrentMemberRow(root);
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-rt-household-members-panel]').forEach(initHouseholdMembersPanel);
});
