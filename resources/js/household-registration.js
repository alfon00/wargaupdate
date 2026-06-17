/**
 * Shared KK + household members dynamic form (panel RT & layanan publik).
 */
export function initHouseholdRegistration(root) {
    const form = root.querySelector('[data-household-registration-form], [data-rt-registration-form]');
    const container = root.querySelector('[data-members-container]');
    const addBtn = root.querySelector('[data-add-member-btn]');
    const countLabel = root.querySelector('[data-member-count-label]');
    const maxMsg = root.querySelector('[data-member-max-msg]');

    if (!form || !container) {
        return;
    }

    const ui = form.dataset.registrationUi || 'panel';
    const isPublic = ui === 'public';
    const includeMemberDocuments = form.dataset.includeMemberDocuments === '1';

    const MAX = parseInt(form.dataset.maxMembers, 10) || 50;
    const relationships = ['Kepala Keluarga', 'Istri', 'Anak', 'Orang Tua', 'Anggota Keluarga', 'Lainnya'];
    const DEMO = JSON.parse(form.dataset.demographics || '{}');
    const oldMembers = JSON.parse(form.dataset.oldMembers || '[]');
    const validationErrors = JSON.parse(form.dataset.validationErrors || '{}');

    const cardClass = isPublic ? 'lw-pendataan-member lw-surface-muted' : 'lw-rt-reg-member';
    const gridClass = isPublic ? 'lw-form-grid lw-form-grid--labeled lw-pendataan-member-grid' : 'lw-panel-form-grid lw-panel-form-grid--labeled';
    const fieldClass = isPublic ? 'lw-form-field' : 'lw-panel-field';
    const span2Class = isPublic ? 'lw-form-field lw-form-field--span2' : 'lw-panel-field lw-panel-field--span2';
    const labelClass = isPublic ? 'lw-form-label' : 'lw-panel-field-label';
    const inputClass = isPublic ? 'lw-form-input' : 'lw-panel-field-input';
    const fileClass = isPublic ? 'lw-form-input lw-form-file' : 'lw-panel-field-input';
    const removeBtnClass = isPublic
        ? 'lw-btn-secondary lw-btn-secondary--sm'
        : 'lw-panel-btn lw-panel-btn--ghost lw-panel-btn--sm';
    const headTitleClass = isPublic ? 'lw-form-label lw-mb-0' : 'lw-rt-reg-member-title';

    let membersData = [];

    function escapeAttr(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;');
    }

    function selectOptions(list, selected) {
        return (list || [])
            .map((item) => `<option value="${escapeAttr(item)}"${selected === item ? ' selected' : ''}>${escapeAttr(item)}</option>`)
            .join('');
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

    function fieldError(fieldKey) {
        const messages = validationErrors[fieldKey];
        if (!messages || messages.length === 0) {
            return '';
        }

        return `<p class="lw-form-error">${escapeAttr(messages[0])}</p>`;
    }

    function renderMemberCard(index, data = {}) {
        const isHead = index === 0;
        const rel = isHead ? 'Kepala Keluarga' : (data.relationship || 'Anggota Keluarga');
        const relOptions = relationships
            .map((r) => `<option value="${escapeAttr(r)}"${rel === r ? ' selected' : ''}>${escapeAttr(r)}</option>`)
            .join('');
        const idLabel = identityLabel(data.birth_date);
        const card = document.createElement('article');
        card.className = cardClass;
        card.dataset.memberIndex = String(index);

        const documentField = includeMemberDocuments
            ? `<div class="${span2Class}">
                    <label class="${labelClass}">Unggah ${escapeAttr(idLabel)} <span class="lw-form-label-required">*</span></label>
                    <input type="file" name="members[${index}][document_id]" class="${fileClass}" accept=".pdf,.jpg,.jpeg,.png" required>
                    ${fieldError(`members.${index}.document_id`)}
                    <p class="lw-form-hint">PDF/JPG/PNG, maks. 5 MB.</p>
                </div>`
            : '';

        card.innerHTML = `
            <header class="${isPublic ? 'lw-mb-3' : 'lw-rt-reg-member-head'}">
                <h3 class="${headTitleClass}">${isHead ? 'Kepala keluarga' : 'Anggota ' + (index + 1)}</h3>
                ${isHead ? '' : `<button type="button" class="${removeBtnClass}" data-remove-member>Hapus</button>`}
            </header>
            <div class="${gridClass}">
                <div class="${span2Class}">
                    <label class="${labelClass}">Nama lengkap <span class="lw-form-label-required">*</span></label>
                    <input name="members[${index}][name]" type="text" required value="${escapeAttr(data.name)}" class="${inputClass}">
                    ${fieldError(`members.${index}.name`)}
                </div>
                <div class="${span2Class}">
                    <label class="${labelClass}">NIK (16 digit) <span class="lw-form-label-required">*</span></label>
                    <input name="members[${index}][nik]" type="text" inputmode="numeric" maxlength="16" pattern="\\d{16}" required
                        value="${escapeAttr(data.nik)}" class="${inputClass} member-nik">
                    ${fieldError(`members.${index}.nik`)}
                </div>
                ${isHead ? '<input type="hidden" name="members[' + index + '][relationship]" value="Kepala Keluarga">' : `
                <div class="${fieldClass}">
                    <label class="${labelClass}">Hubungan dengan KK <span class="lw-form-label-required">*</span></label>
                    <select name="members[${index}][relationship]" required class="${inputClass}">${relOptions}</select>
                </div>`}
                <div class="${fieldClass}">
                    <label class="${labelClass}">Tempat lahir <span class="lw-form-label-required">*</span></label>
                    <input name="members[${index}][birth_place]" type="text" required value="${escapeAttr(data.birth_place)}" class="${inputClass}">
                </div>
                <div class="${fieldClass}">
                    <label class="${labelClass}">Tanggal lahir <span class="lw-form-label-required">*</span></label>
                    <input name="members[${index}][birth_date]" type="date" required value="${escapeAttr(data.birth_date)}" class="${inputClass}">
                </div>
                <div class="${fieldClass}">
                    <label class="${labelClass}">Jenis kelamin <span class="lw-form-label-required">*</span></label>
                    <select name="members[${index}][gender]" required class="${inputClass}">
                        <option value="">— Pilih —</option>
                        <option value="Laki-laki"${data.gender === 'Laki-laki' ? ' selected' : ''}>Laki-laki</option>
                        <option value="Perempuan"${data.gender === 'Perempuan' ? ' selected' : ''}>Perempuan</option>
                    </select>
                </div>
                <div class="${fieldClass}">
                    <label class="${labelClass}">Pekerjaan <span class="lw-form-label-required">*</span></label>
                    <select name="members[${index}][occupation]" required class="${inputClass}">
                        <option value="">— Pilih —</option>
                        ${selectOptions(DEMO.occupations, data.occupation)}
                    </select>
                </div>
                <div class="${fieldClass}">
                    <label class="${labelClass}">Pendidikan <span class="lw-form-label-required">*</span></label>
                    <select name="members[${index}][education]" required class="${inputClass}">
                        <option value="">— Pilih —</option>
                        ${selectOptions(DEMO.education_levels, data.education)}
                    </select>
                </div>
                <div class="${fieldClass}">
                    <label class="${labelClass}">Agama <span class="lw-form-label-required">*</span></label>
                    <select name="members[${index}][religion]" required class="${inputClass}">
                        <option value="">— Pilih —</option>
                        ${selectOptions(DEMO.religions, data.religion)}
                    </select>
                </div>
                <div class="${fieldClass}">
                    <label class="${labelClass}">Status perkawinan <span class="lw-form-label-required">*</span></label>
                    <select name="members[${index}][marital_status]" required class="${inputClass}">
                        <option value="">— Pilih —</option>
                        ${selectOptions(DEMO.marital_statuses, data.marital_status)}
                    </select>
                </div>
                <div class="${fieldClass}">
                    <label class="${labelClass}">Kewarganegaraan <span class="lw-form-label-required">*</span></label>
                    <select name="members[${index}][citizenship]" required class="${inputClass}">
                        <option value="">— Pilih —</option>
                        ${selectOptions(DEMO.citizenships, data.citizenship || 'WNI')}
                    </select>
                </div>
                ${documentField}
            </div>
        `;

        const removeBtn = card.querySelector('[data-remove-member]');
        if (removeBtn) {
            removeBtn.addEventListener('click', () => {
                membersData.splice(index, 1);
                if (membersData.length === 0) {
                    membersData.push({});
                }
                renderAll();
            });
        }

        if (includeMemberDocuments && index === 0) {
            const headDocInput = card.querySelector('input[name="members[0][document_id]"]');
            headDocInput?.addEventListener('change', () => {
                document.dispatchEvent(new CustomEvent('pendataan-head-ktp-changed', {
                    detail: { file: headDocInput.files?.[0] ?? null },
                }));
            });
        }

        return card;
    }

    function renderAll() {
        container.innerHTML = '';
        membersData.forEach((data, index) => {
            container.appendChild(renderMemberCard(index, data));
        });

        const count = membersData.length;
        if (countLabel) {
            countLabel.textContent = count === 1 ? '1 anggota (kepala KK)' : count + ' anggota';
        }
        if (addBtn) {
            addBtn.disabled = count >= MAX;
        }
        if (maxMsg) {
            if (count >= MAX) {
                maxMsg.textContent = 'Maksimum ' + MAX + ' anggota per KK.';
                maxMsg.classList.remove('is-hidden');
                maxMsg.classList.remove('hidden');
            } else {
                maxMsg.classList.add('is-hidden');
                maxMsg.classList.add('hidden');
            }
        }
    }

    function syncFromDom() {
        membersData = [];
        container.querySelectorAll('[data-member-index]').forEach((card) => {
            const index = card.dataset.memberIndex;
            const get = (field) => card.querySelector(`[name="members[${index}][${field}]"]`);
            membersData.push({
                name: get('name')?.value ?? '',
                nik: get('nik')?.value ?? '',
                relationship: get('relationship')?.value ?? '',
                birth_place: get('birth_place')?.value ?? '',
                birth_date: get('birth_date')?.value ?? '',
                gender: get('gender')?.value ?? '',
                occupation: get('occupation')?.value ?? '',
                education: get('education')?.value ?? '',
                religion: get('religion')?.value ?? '',
                marital_status: get('marital_status')?.value ?? '',
                citizenship: get('citizenship')?.value ?? 'WNI',
            });
        });
    }

    if (addBtn) {
        addBtn.addEventListener('click', () => {
            syncFromDom();
            if (membersData.length >= MAX) {
                return;
            }
            membersData.push({ citizenship: 'WNI' });
            renderAll();
        });
    }

    if (oldMembers.length > 0) {
        membersData = oldMembers;
    } else {
        membersData = [{}];
    }
    renderAll();

    const statusInput = form.querySelector('[data-status-rumah-input]');
    const kondisiField = form.querySelector('[data-kondisi-field]');
    const kondisiInput = form.querySelector('[data-kondisi-input]');

    function syncKondisi() {
        if (!statusInput || !kondisiField || !kondisiInput) {
            return;
        }
        const milikValue = form.dataset.milikSendiriValue || 'Milik sendiri';
        const needs = statusInput.value === milikValue;
        const reqMark = form.querySelector('.kondisi-req-mark');
        kondisiField.classList.toggle('lw-is-hidden', !needs);
        kondisiInput.required = needs;
        kondisiInput.disabled = !needs;
        if (reqMark) {
            reqMark.hidden = !needs;
        }
        if (!needs) {
            kondisiInput.value = '';
        }
    }

    if (statusInput) {
        statusInput.addEventListener('change', syncKondisi);
        syncKondisi();
    }
}
