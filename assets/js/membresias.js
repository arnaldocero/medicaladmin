(function($) {
    "use strict";

    $(document).ready(function() {
        listarClinicas();
        $("#filtroEstadoClinica").on("change", listarClinicas);

        $("#formClinica").off("submit").on("submit", function(e) {
            e.preventDefault();
            let btn = $(this).find("button[type='submit']");
            btn.prop("disabled", true).html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

            let datos = $(this).serialize() + "&action=guardar";

            $.ajax({
                url: 'ajax/membresias_ajax.php',
                type: 'POST',
                data: datos,
                success: function(res) {
                    if (res.status === "success") {
                        if (res.aviso) {
                            Swal.fire('Guardada con aviso', res.aviso, 'warning');
                        } else {
                            Swal.fire('¡Éxito!', 'Clínica guardada correctamente', 'success');
                        }
                        $('#modalClinica').modal('hide');
                        listarClinicas();
                        $("#formClinica")[0].reset();
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                },
                complete: function() {
                    btn.prop("disabled", false).html('<i class="fa fa-save"></i> Guardar Clínica');
                }
            });
        });
    });

    window.listarClinicas = function() {
        $.get('ajax/membresias_ajax.php', { action: 'listar', estado: $("#filtroEstadoClinica").val() || '1' }, function(data) {
            let html = '';
            data.forEach(c => {
                const activa = Number(c.estado) === 1;
                const botonEstado = activa
                    ? `<button class="btn btn-danger btn-sm" title="Desactivar" onclick="cambiarEstadoClinica(${Number(c.id)}, 0)"><i class="fa fa-ban"></i></button>`
                    : `<button class="btn btn-success btn-sm" title="Reactivar" onclick="cambiarEstadoClinica(${Number(c.id)}, 1)"><i class="fa fa-check"></i></button>`;
                let badgePlan = (c.plan_contratado == 'Alto') ? 'badge-danger' : (c.plan_contratado == 'Medio' ? 'badge-warning' : 'badge-info');
                // Usuarios activos frente al límite del plan; en rojo si lo supera
                const max = c.max_usuarios === null ? null : Number(c.max_usuarios);
                const usados = Number(c.usuarios_activos);
                const usuarios = max === null
                    ? `${usados} <small class="text-muted">/ sin límite</small>`
                    : `<span class="${usados > max ? 'text-danger font-weight-bold' : (usados === max ? 'text-warning font-weight-bold' : '')}">${usados} / ${max}</span>`
                      + (usados > max ? ' <span class="badge badge-danger">excede</span>' : '');
                html += `<tr class="${activa ? '' : 'text-muted'}">
                    <td><b>${esc(c.nit_empresa)}</b><br>${esc(c.nombre_clinica)}${activa ? '' : ' <span class="badge badge-secondary">Inactiva</span>'}</td>
                    <td>${esc(c.representante_legal)}</td>
                    <td><span class="badge ${badgePlan}">${esc(c.plan_nombre || c.plan_contratado)}</span></td>
                    <td data-order="${usados}">${usuarios}</td>
                    <td><small>Vence: ${esc(c.fecha_vencimiento)}</small></td>
                    <td class="text-center text-nowrap">
                        <button class="btn btn-warning btn-sm" title="Editar" onclick="editarClinica(${Number(c.id)})"><i class="fa fa-edit"></i></button>
                        ${botonEstado}
                    </td>
                </tr>`;
            });
            pintarTabla("#tablaClinicas", html, { exportar: { archivo: "clinicas", titulo: "Clínicas", sinUltimaColumna: true } });
        });
    }

    window.editarClinica = function(id) {
        $.post('ajax/membresias_ajax.php', { action: 'obtener', id: id }, function(c) {
            $("#idCliente").val(c.id);
            $("#nit_empresa").val(c.nit_empresa);
            $("#nombre_clinica").val(c.nombre_clinica);
            $("#representante_legal").val(c.representante_legal);
            $("#telefono").val(c.telefono);
            $("#email_contacto").val(c.email_contacto);
            $("#plan_contratado").val(c.plan_contratado);
            $("#fecha_activacion").val(c.fecha_activacion);
            $("#fecha_vencimiento").val(c.fecha_vencimiento);
            $(".modal-title").text("Editar Clínica: " + c.nombre_clinica);
            $("#modalClinica").modal("show");
        });
    }

    window.cambiarEstadoClinica = function(id, estado) {
        const desactivar = estado === 0;
        Swal.fire({
            title: desactivar ? '¿Desactivar clínica?' : '¿Reactivar clínica?',
            text: desactivar
                ? 'Dejará de contar en el dashboard y no se le podrán crear ni reactivar usuarios. No se borra nada: podrás reactivarla cuando quieras.'
                : 'Volverá a aparecer como clínica activa en el panel y en el dashboard.',
            icon: desactivar ? 'warning' : 'question',
            showCancelButton: true,
            confirmButtonText: desactivar ? 'Sí, desactivar' : 'Sí, reactivar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (!result.isConfirmed) return;
            $.post('ajax/membresias_ajax.php', { action: 'cambiar_estado', id: id, estado: estado }, function(res) {
                if (res.status !== 'success') {
                    Swal.fire('Error', res.message || 'No se pudo cambiar el estado', 'error');
                    return;
                }
                listarClinicas();
                if (res.aviso) {
                    Swal.fire('Reactivada con aviso', res.aviso, 'warning');
                } else {
                    Swal.fire(desactivar ? 'Desactivada' : 'Reactivada', '', 'success');
                }
            });
        });
    }

    window.limpiarClinica = function() {
        $("#formClinica")[0].reset();
        $("#idCliente").val("");
        $(".modal-title").text("Nueva Clínica");
    }

})(jQuery);