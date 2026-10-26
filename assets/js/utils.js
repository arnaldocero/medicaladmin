// Envía el token CSRF (meta csrf-token de header.php) en todas las peticiones AJAX
jQuery.ajaxSetup({
    headers: { 'X-CSRF-Token': jQuery('meta[name="csrf-token"]').attr('content') || '' }
});

// Muestra el mensaje del servidor cuando una petición AJAX es rechazada (422 validación, 403 CSRF, 401...)
jQuery(document).ajaxError(function(evento, xhr) {
    const res = xhr.responseJSON || {};
    // 401 = la sesión terminó (inactividad o usuario desactivado): volver al login
    if (xhr.status === 401) {
        const motivo = res.sesion && res.sesion !== 'sin_sesion' ? '?motivo=' + encodeURIComponent(res.sesion) : '';
        window.location.href = 'login.php' + motivo;
        return;
    }
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
// Texto de una celda para exportar: <br> y cada <li> pasan a salto de línea, sin etiquetas.
// (innerText no sirve: en las filas de otras páginas la celda no está en pantalla y pierde los saltos)
function textoDeCelda(nodo) {
    const copia = nodo.cloneNode(true);
    copia.querySelectorAll('br').forEach(br => br.replaceWith('\n'));
    copia.querySelectorAll('li').forEach(li => li.append('\n'));
    return copia.textContent.replace(/[ \t]+/g, ' ').replace(/ *\n */g, '\n').trim();
}

window.pintarTabla = function(selectorTbody, html, opciones) {
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

    // opciones.exportar = { archivo: 'clinicas', titulo: '...', sinUltimaColumna: true }
    // añade un botón "Excel" que exporta las filas filtradas (todas las páginas)
    opciones = jQuery.extend({}, opciones || {});
    const exportar = opciones.exportar;
    delete opciones.exportar;
    const botones = exportar ? [{
        extend: 'excelHtml5',
        text: '<i class="fa fa-file-excel-o"></i> Excel',
        className: 'btn btn-success btn-sm',
        filename: function() {
            const hoy = new Date();   // fecha local, no UTC
            return exportar.archivo + '_' + hoy.getFullYear() + '-' + String(hoy.getMonth() + 1).padStart(2, '0') + '-' + String(hoy.getDate()).padStart(2, '0');
        },
        title: exportar.titulo || null,
        exportOptions: {
            columns: exportar.sinUltimaColumna ? ':not(:last-child)' : ':visible',
            // Texto tal como se ve (con saltos de línea), sin etiquetas HTML
            format: { body: function(dato, fila, columna, nodo) { return textoDeCelda(nodo); } }
        }
    }] : [];

    const tabla = $tabla.css('width', '100%').DataTable(jQuery.extend({
        language: DATATABLES_ES,
        // Distribución de Bootstrap 4, pero con la tabla en .table-responsive: en el móvil
        // la tabla se desplaza sola en horizontal en vez de ensanchar toda la página
        dom: (exportar
                ? "<'row'<'col-sm-12 col-md-4'l><'col-sm-12 col-md-4 text-md-center mb-2'B><'col-sm-12 col-md-4'f>>"
                : "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>") +
             "<'table-responsive'tr>" +
             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        buttons: botones,
        pageLength: longitud,
        order: [],                                           // respeta el orden que envía el servidor
        columnDefs: [{ targets: -1, orderable: false, searchable: false }]  // columna de acciones
    }, opciones)).search(busqueda).draw();

    // Vuelve a la página en la que estaba, si todavía existe con los datos nuevos
    if (pagina > 0 && pagina < tabla.page.info().pages) tabla.page(pagina).draw('page');
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
