(function($) {
    "use strict";

    // 1500000 -> "$ 1.500.000"
    function pesos(valor) {
        return '$ ' + Math.round(Number(valor) || 0).toLocaleString('es-CO');
    }

    $(document).ready(function() {
        listarPlanes();

        $("#formPlan").on("submit", function(e) {
            e.preventDefault();
            $.post('ajax/planes_ajax.php', $(this).serialize() + "&action=guardar", function(res) {
                if (res.status === "success") {
                    $('#modalPlan').modal('hide');
                    Swal.fire(res.aviso ? 'Guardado con aviso' : '¡Éxito!', res.aviso || 'Plan guardado', res.aviso ? 'warning' : 'success');
                    listarPlanes();
                } else {
                    Swal.fire('Error', res.message || 'No se pudo guardar', 'error');
                }
            });
        });
    });

    window.listarPlanes = function() {
        $.get('ajax/planes_ajax.php', { action: 'listar' }, function(data) {
            let html = '';
            data.forEach(p => {
                const limite = p.max_usuarios === null ? '<span class="text-muted">Sin límite</span>' : Number(p.max_usuarios);
                // Avisa si alguna clínica del plan ya tiene más usuarios activos que el límite
                const excedido = p.max_usuarios !== null && Number(p.max_en_uso) > Number(p.max_usuarios)
                    ? ' <span class="badge badge-danger" title="Hay clínicas por encima del límite">excedido</span>' : '';
                html += `<tr>
                    <td><b>${esc(p.nombre)}</b><br><small class="text-muted">${esc(p.codigo)}</small></td>
                    <td data-order="${Number(p.precio_mensual)}">${pesos(p.precio_mensual)}</td>
                    <td>${limite}${excedido}</td>
                    <td>${Number(p.clinicas)} <small class="text-muted">(${Number(p.vigentes)} vigentes)</small></td>
                    <td data-order="${Number(p.precio_mensual) * Number(p.vigentes)}">${pesos(Number(p.precio_mensual) * Number(p.vigentes))}</td>
                    <td class="text-center">
                        <button class="btn btn-warning btn-sm" title="Editar" onclick="editarPlan(${Number(p.id)})"><i class="fa fa-edit"></i></button>
                    </td>
                </tr>`;
            });
            pintarTabla("#tablaPlanes", html, { paging: false, searching: false, info: false });
        });
    };

    window.editarPlan = function(id) {
        $.post('ajax/planes_ajax.php', { action: 'obtener', id: id }, function(p) {
            $("#idPlan").val(p.id);
            $("#codigoPlan").text(p.codigo);
            $("#nombre_plan").val(p.nombre);
            $("#precio_mensual").val(Math.round(Number(p.precio_mensual)));
            $("#max_usuarios").val(p.max_usuarios === null ? '' : p.max_usuarios);
            $("#tituloModalPlan").text("Editar plan " + p.nombre);
            $("#modalPlan").modal("show");
        });
    };

})(jQuery);
