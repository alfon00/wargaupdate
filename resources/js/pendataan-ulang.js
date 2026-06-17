/**
 * Public pendataan ulang: read-only member list + KTP/KIA upload per member.
 */
export function initPendataanUlang(root) {
    const form = root.querySelector('[data-pendataan-ulang-form]');
    const container = root.querySelector('[data-members-container]');
    const countLabel = root.querySelector('[data-member-count-label]');

    if (!form || !container) {
        return;
    }

    const members = JSON.parse(form.dataset.oldMembers || '[]');

    function escapeAttr(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;');
    }

    function identityLabel(birthDate) {
        if (!birthDate) {
            return 'KTP / KIA';
        }
        const birth = new Date(birthDate);
        if (Number.isNaN(birth.getTime())) {
            return 'KTP / KIA';
        }
        const ageMs = Date.now() - birth.getTime();
        const age = Math.floor(ageMs / (365.25 * 24 * 60 * 60 * 1000));

        return age < 17 ? 'KIA (Kartu Identitas Anak)' : 'KTP';
    }

    function renderMemberCard(index, data = {}) {
        const isHead = index === 0;
        const idLabel = identityLabel(data.birth_date);
        const card = document.createElement('article');
        card.className = 'lw-pendataan-member lw-surface-muted';
        card.dataset.memberIndex = String(index);

        const nikDisplay = data.nik ? data.nik : 'belum ada';

        card.innerHTML = `
            <div class="lw-pendataan-member-head">
                <p class="lw-pendataan-member-title">${isHead ? 'Kepala keluarga' : 'Anggota ' + (index + 1)}</p>
            </div>
            <input type="hidden" name="members[${index}][resident_id]" value="${escapeAttr(data.resident_id)}">
            <div class="lw-form-grid lw-form-grid--labeled lw-pendataan-member-grid">
                <div class="lw-form-field">
                    <p class="lw-form-label">Nama</p>
                    <p class="lw-form-hint lw-mb-0">${escapeAttr(data.name || '—')}</p>
                </div>
                <div class="lw-form-field">
                    <p class="lw-form-label">NIK</p>
                    <p class="lw-form-hint lw-mb-0">${escapeAttr(nikDisplay)}</p>
                </div>
                <div class="lw-form-field lw-form-field--span2">
                    <label class="lw-form-label">Unggah ${escapeAttr(idLabel)} <span class="lw-form-label-required">*</span></label>
                    <input type="file" name="members[${index}][document_id]" class="lw-form-input lw-form-file" accept=".pdf,.jpg,.jpeg,.png" required>
                    <p class="lw-form-hint">PDF/JPG/PNG, maks. 5 MB.</p>
                </div>
            </div>
        `;

        return card;
    }

    container.innerHTML = '';
    members.forEach((data, index) => {
        container.appendChild(renderMemberCard(index, data));
    });

    if (countLabel) {
        const count = members.length;
        countLabel.textContent = count === 1
            ? '1 anggota — unggah KTP/KIA kepala keluarga.'
            : count + ' anggota — unggah KTP/KIA untuk setiap orang.';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-pendataan-ulang-page]');
    if (root) {
        initPendataanUlang(root);
    }
});
