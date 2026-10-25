// Envía el token CSRF (meta csrf-token de header.php) en todas las peticiones AJAX
jQuery.ajaxSetup({
    headers: { 'X-CSRF-Token': jQuery('meta[name="csrf-token"]').attr('content') || '' }
});

// Escapa texto antes de insertarlo como HTML (evita XSS en las tablas)
window.esc = function(valor) {
    if (valor === null || valor === undefined) return '';
    return String(valor)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
};
