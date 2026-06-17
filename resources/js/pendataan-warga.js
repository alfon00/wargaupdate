import { initHouseholdRegistration } from './household-registration.js';

document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-pendataan-warga-page]');
    if (root) {
        initHouseholdRegistration(root);
    }
});
