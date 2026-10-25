(function($) {
    "use strict";

    // Nombres legibles de las acciones y de los campos que aparecen en los cambios
    const ACCIONES = {
        crear:    ['Creó', 'badge-success'],
        editar:   ['Editó', 'badge-warning'],
        eliminar: ['Eliminó', 'badge-danger'],
        login:    ['Inicio de sesión', 'badge-info']
    };
    const CAMPOS = {
        nit_empresa: 'NIT', nombre_clinica: 'Clínica', representante_legal: 'Representante',
        telefono: 'Teléfono', email_contacto: 'Email de contacto', plan_contratado: 'Plan',
        fecha_activacion: 'Activación', fecha_vencimiento: 'Vencimiento',
        nombre: 'Nombre', usuario: 'Usuario', email: 'Email', rol: 'Rol', clinica: 'Clínica',
        password: 'Contraseña'
    };

    $(document).ready(function() {
        listarBitacora();
        $("#formFiltroBitacora").on("submit", function(e) {
            e.preventDefault();
            listarBitacora();
        });
    });

    // Lista de cambios "Campo: antes → después"
    function pintarCambios(cambios) {
        if (!cambios) return '';
        return '<ul class="list-unstyled mb-0 small">' + Object.keys(cambios).map(function(campo) {
            const c = cambios[campo];
            const antes = c.antes === null || c.antes === '' ? '<span class="text-muted">—</span>' : esc(c.antes);
            const despues = c.despues === null || c.despues === '' ? '<span class="text-muted">—</span>' : esc(c.despues);
            return '<li><b>' + esc(CAMPOS[campo] || campo) + ':</b> ' + antes + ' → ' + despues + '</li>';
        }).join('') + '</ul>';
    }

    window.listarBitacora = function() {
        const filtros = $("#formFiltroBitacora").serialize();
        $.get('ajax/bitacora_ajax.php?action=listar&' + filtros, function(res) {
            let html = '';
            res.filas.forEach(function(b) {
                const accion = ACCIONES[b.accion] || [b.accion, 'badge-secondary'];
                html += `<tr>
                    <td data-order="${esc(b.fecha)}"><span class="text-nowrap">${esc(b.fecha)}</span><br><small class="text-muted">${esc(b.ip)}</small></td>
                    <td>${esc(b.usuario_nombre)}</td>
                    <td><span class="badge ${accion[1]}">${esc(accion[0])}</span></td>
                    <td>${esc(b.descripcion)}</td>
                    <td>${pintarCambios(b.cambios)}</td>
                </tr>`;
            });
            pintarTabla("#tablaBitacora", html, {
                pageLength: 25,
                columnDefs: [{ targets: -1, orderable: false }]   // los cambios sí se pueden buscar
            });

            const aviso = $("#avisoLimite");
            if (res.filas.length >= res.limite) {
                aviso.text('Se muestran los ' + res.limite + ' registros más recientes. Acota las fechas para ver registros anteriores.').removeClass('d-none');
            } else {
                aviso.addClass('d-none');
            }
        });
    };

})(jQuery);
