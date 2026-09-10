@php
    $whatsappNumber = strtr((string) \App\Models\Setting::get('contact_whatsapp', ''), array_combine(
        preg_split('//u', '٠١٢٣٤٥٦٧٨٩', -1, PREG_SPLIT_NO_EMPTY), range(0, 9)
    ));
    $whatsappNumber = preg_replace('/\D/', '', $whatsappNumber);
    if (str_starts_with($whatsappNumber, '00')) {
        $whatsappNumber = substr($whatsappNumber, 2);
    }
@endphp
@if($whatsappNumber !== '')
<a class="floating-whatsapp" href="https://wa.me/{{ $whatsappNumber }}" target="_blank" rel="noopener noreferrer" aria-label="تواصل معنا على واتساب" title="تواصل معنا على واتساب">
    <span class="floating-whatsapp-label" aria-hidden="true">خلّينا نساعدك 💬</span>
    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
        <path d="M20.52 3.48A11.91 11.91 0 0 0 12.05 0C5.47 0 .12 5.35.12 11.93c0 2.1.55 4.15 1.6 5.96L.02 24l6.25-1.64a11.9 11.9 0 0 0 5.77 1.47h.01C18.63 23.83 24 18.48 24 11.9c0-3.19-1.24-6.18-3.48-8.42ZM12.05 21.82a9.9 9.9 0 0 1-5.05-1.38l-.36-.21-3.71.97.99-3.62-.24-.37a9.89 9.89 0 0 1-1.52-5.28c0-5.47 4.45-9.92 9.93-9.92a9.86 9.86 0 0 1 7.01 2.9 9.85 9.85 0 0 1 2.9 7.02c0 5.47-4.46 9.89-9.95 9.89Zm5.44-7.42c-.3-.15-1.77-.87-2.04-.97-.28-.1-.48-.15-.68.15-.2.3-.77.97-.95 1.17-.17.2-.35.22-.64.07-.3-.15-1.26-.46-2.4-1.48-.89-.79-1.49-1.77-1.66-2.07-.18-.3-.02-.46.13-.61.14-.13.3-.35.44-.52.15-.18.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.68-1.63-.93-2.23-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.8.37-.27.3-1.04 1.02-1.04 2.5s1.07 2.9 1.22 3.1c.15.2 2.1 3.2 5.08 4.49.71.3 1.27.49 1.7.62.71.22 1.36.19 1.87.11.57-.08 1.77-.72 2.02-1.42.25-.7.25-1.3.17-1.42-.07-.13-.27-.2-.57-.35Z"/>
    </svg>
    <span class="floating-whatsapp-status" aria-hidden="true"></span>
</a>
@endif
