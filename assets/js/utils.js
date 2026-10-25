// Envía el token CSRF (meta csrf-token de header.php) en todas las peticiones AJAX
jQuery.ajaxSetup({
    headers: { 'X-CSRF-Token': jQuery('meta[name="csrf-token"]').attr('content') || '' }
});

// Muestra el mensaje del servidor cuando una petición AJAX es rechazada (422 validación, 403 CSRF, 401...)
jQuery(document).ajaxError(function(evento, xhr) {
    const res = xhr.responseJSON || {};
    Swal.fire('Error', res.message || 'Error de comunicación con el servidor', 'error');
});

// Textos de DataTables en español (sin pedir archivos externos)
const DATATABLES_ES = {
    search: "Buscar:",
    lengthMenu: "Mostrar _MENU_ registros",
    info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
    infoEmpty: "Sin registros",
    infoFiltered: "(filtrado de _MAX_ en total)",
    zeroRecords: "No se encontraron resultados",
    emptyTable: "No hay datos registrados",
    paginate: { first: "Primero", last: "Último", next: "Siguiente", previous: "Anterior" }
};

// Pinta las filas en el <tbody> y aplica DataTables (búsqueda, orden y paginación).
// Si la tabla ya existía, conserva el texto buscado y la página actual.
window.pintarTabla = function(selectorTbody, html) {
    const $tabla = jQuery(selectorTbody).closest('table');
    let busqueda = '', pagina = 0, longitud = 10;

    if (jQuery.fn.dataTable.isDataTable($tabla)) {
        const anterior = $tabla.DataTable();
        busqueda = anterior.search();
        pagina = anterior.page();
        longitud = anterior.page.len();
        anterior.destroy();
    }

    jQuery(selectorTbody).html(html);

    $tabla.css('width', '100%').DataTable({
        language: DATATABLES_ES,
        pageLength: longitud,
        order: [],                                           // respeta el orden que envía el servidor
        columnDefs: [{ targets: -1, orderable: false, searchable: false }]  // columna de acciones
    }).search(busqueda).page(pagina).draw('page');
};

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
