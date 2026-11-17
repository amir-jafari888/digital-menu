<?php
/**
 * Tiny inline-SVG icon set (Lucide-style, stroke based).
 * Keeps markup readable and avoids an icon-font dependency.
 */
function icon(string $name, int $size = 24, string $class = ''): string
{
    $paths = [
        'menu'      => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'close'     => '<path d="M6 6l12 12M18 6L6 18"/>',
        'arrow'     => '<path d="M19 12H5M11 18l-6-6 6-6"/>',
        'check'     => '<path d="M20 6L9 17l-5-5"/>',
        'check-circle' => '<circle cx="12" cy="12" r="9"/><path d="M8.5 12.5l2.5 2.5 4.5-5"/>',
        'refresh'   => '<path d="M20 11a8 8 0 10-2.3 5.7"/><path d="M20 4v7h-7"/>',
        'receipt'   => '<path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3z"/><path d="M9 8h6M9 12h4"/>',
        'qr'        => '<path d="M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4z"/><path d="M14 14h2v2h-2zM18 14h2v2h-2zM14 18h2v2h-2zM18 18h2v2h-2z"/>',
        'gift'      => '<rect x="4" y="9" width="16" height="11" rx="2"/><path d="M4 13h16M12 9v11"/><path d="M12 9S9.5 4 7.5 5.5 9 9 12 9zM12 9s2.5-5 4.5-3.5S15 9 12 9z"/>',
        'megaphone' => '<path d="M4 11v2a1 1 0 001 1h2l7 4V7L7 11H5a1 1 0 00-1 1z"/><path d="M18 9a4 4 0 010 6"/>',
        'chart'     => '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
        'star'      => '<path d="M12 3l2.7 5.5 6 .9-4.3 4.2 1 6-5.4-2.8L6.6 19.6l1-6L3.3 9.4l6-.9L12 3z"/>',
        'users'     => '<circle cx="9" cy="8" r="3"/><path d="M3.5 19a5.5 5.5 0 0111 0"/><path d="M16 5.5a3 3 0 010 5.8M17.5 19a5.5 5.5 0 00-2-4.3"/>',
        'clock'     => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'shield'    => '<path d="M12 3l7 3v5c0 4.5-3 8-7 10-4-2-7-5.5-7-10V6l7-3z"/><path d="M9 12l2 2 4-4"/>',
        'spark'     => '<path d="M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8L12 3z"/>',
        'phone'     => '<rect x="7" y="3" width="10" height="18" rx="2"/><path d="M11 18h2"/>',
        'map-pin'   => '<path d="M12 21s7-5.5 7-11a7 7 0 10-14 0c0 5.5 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/>',
        'mail'      => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/>',
        'instagram' => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><path d="M17.5 6.5h.01"/>',
        'coffee'    => '<path d="M4 8h12v6a5 5 0 01-5 5H9a5 5 0 01-5-5V8z"/><path d="M16 9h2a2.5 2.5 0 010 5h-2"/><path d="M7 4v2M10 4v2M13 4v2"/>',
        'restaurant'=> '<path d="M5 3v7a3 3 0 006 0V3M8 10v11"/><path d="M16 3c-1.5 1-2 3-2 5s.5 4 2 5v8"/>',
        'store'     => '<path d="M4 9l1-5h14l1 5"/><path d="M4 9h16v11H4z"/><path d="M9 20v-6h6v6"/>',
        'cake'      => '<path d="M4 20h16v-6H4z"/><path d="M6 14V9h12v5"/><path d="M12 9V6"/><path d="M12 4.5c.6.7 1 1.2 1 1.7a1 1 0 11-2 0c0-.5.4-1 1-1.7z"/>',
        'play'      => '<path d="M8 5l11 7-11 7V5z"/>',
        'plus'      => '<path d="M12 5v14M5 12h14"/>',
        'lock'      => '<rect x="5" y="10" width="14" height="10" rx="2"/><path d="M8 10V7a4 4 0 018 0v3"/>',
        'bolt'      => '<path d="M13 3L5 14h6l-1 7 8-11h-6l1-7z"/>',
        'quote'     => '<path d="M7 7h4v6H7zM13 7h4v6h-4z"/><path d="M7 13c0 2 1 3 3 3M13 13c0 2 1 3 3 3"/>',
    ];

    $body = $paths[$name] ?? '';
    $cls  = $class !== '' ? ' class="' . e($class) . '"' : '';

    return '<svg viewBox="0 0 24 24" width="' . $size . '" height="' . $size . '" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"' . $cls . '>' . $body . '</svg>';
}
