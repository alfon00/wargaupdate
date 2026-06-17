import { initHouseholdRegistration } from './household-registration.js';

export function initRtHouseholdRegistration(root) {
    initHouseholdRegistration(root);
}

document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-rt-registration-page]');
    if (root) {
        initRtHouseholdRegistration(root);
    }
});
