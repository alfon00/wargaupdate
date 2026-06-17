/**
 * RT panel: scroll to focused resident row when ?household= is present.
 */
const RT_DATA_FOCUS_SCROLL_OFFSET = 80;

function scrollToFocusedHouseholdRow(root) {
    const params = new URLSearchParams(window.location.search);

    if (!params.get('household')) {
        return;
    }

    const focused = root.querySelector('.lw-rt-data-resident-row.is-focused')
        ?? root.querySelector('.lw-rt-data-resident-row.is-focused-resident');

    if (!focused) {
        return;
    }

    const top = focused.getBoundingClientRect().top + window.scrollY - RT_DATA_FOCUS_SCROLL_OFFSET;
    window.scrollTo({ top: Math.max(0, top), behavior: 'smooth' });
}

function initRtDataWargaTable(root) {
    scrollToFocusedHouseholdRow(root);
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-rt-data-warga-table]').forEach(initRtDataWargaTable);
});
