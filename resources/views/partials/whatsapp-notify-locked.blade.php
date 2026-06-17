@props(['label' => 'Terima notifikasi layanan via WhatsApp', 'checkClass' => 'lw-form-check'])

<input type="hidden" name="whatsapp_notify" value="1">
<label class="{{ $checkClass }}">
    <input type="checkbox" value="1" checked disabled>
    {{ $label }}
</label>
