(function($) {
    "use strict";

    $(document).ready(function() {
        listarClinicas();

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
                        Swal.fire('¡Éxito!', 'Clínica guardada correctamente', 'success');
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
        $.get('ajax/membresias_ajax.php?action=listar', function(data) {
            let html = '';
            data.forEach(c => {
                let badgePlan = (c.plan_contratado == 'Alto') ? 'badge-danger' : (c.plan_contratado == 'Medio' ? 'badge-warning' : 'badge-info');
                html += `<tr>
                    <td><b>${c.nit_empresa}</b><br>${c.nombre_clinica}</td>
                    <td>${c.representante_legal}</td>
                    <td><span class="badge ${badgePlan}">${c.plan_contratado}</span></td>
                    <td><small>Vence: ${c.fecha_vencimiento}</small></td>
                    <td class="text-center">
                        <div class="btn-group">
                            <button class="btn btn-warning btn-sm" onclick="editarClinica(${c.id})"><i class="fa fa-edit"></i></button>
                            <button class="btn btn-danger btn-sm" onclick="eliminarClinica(${c.id})"><i class="fa fa-trash"></i></button>
                        </div>
                    </td>
                </tr>`;
            });
            $("#tablaClinicas").html(html);
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

    window.eliminarClinica = function(id) {
        Swal.fire({
            title: '¿Eliminar clínica?',
            text: "Se marcará como inactiva",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('ajax/membresias_ajax.php', { action: 'eliminar', id: id }, function() {
                    listarClinicas();
                    Swal.fire('Eliminado', '', 'success');
                });
            }
        });
    }

    window.limpiarClinica = function() {
        $("#formClinica")[0].reset();
        $("#idCliente").val("");
        $(".modal-title").text("Nueva Clínica");
    }

})(jQuery);