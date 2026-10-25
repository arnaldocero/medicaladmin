// Envía el token CSRF (meta csrf-token de header.php) en todas las peticiones AJAX
jQuery.ajaxSetup({
    headers: { 'X-CSRF-Token': jQuery('meta[name="csrf-token"]').attr('content') || '' }
});

// Muestra el mensaje del servidor cuando una petición AJAX es rechazada (422 validación, 403 CSRF, 401...)
jQuery(document).ajaxError(function(evento, xhr) {
    const res = xhr.responseJSON || {};
    Swal.fire('Error', res.message || 'Error de comunicación con el servidor', 'error');
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
