(function($) {
    "use strict";

    $(document).ready(function() {
        listarTodosLosUsuarios();

        $("#formUsuarioClinica").on("submit", function(e) {
            e.preventDefault();
            let datos = $(this).serialize() + "&action=guardar";
            $.post('ajax/usuarios_clinicas_ajax.php', datos, function(res) {
                if(res.status === "success") {
                    Swal.fire('¡Éxito!', 'Usuario guardado', 'success');
                    $('#modalUsuarioClinica').modal('hide');
                    listarTodosLosUsuarios();
                }
            });
        });
    });

    window.listarTodosLosUsuarios = function() {
        $.get('ajax/usuarios_clinicas_ajax.php?action=listar_global', function(data) {
            let html = '';
            data.forEach(u => {
                html += `<tr>
                    <td><span class="badge badge-light">${u.nombre_clinica}</span></td>
                    <td>${u.nombre}<br><small class="text-primary">${u.usuario}</small></td>
                    <td><span class="badge badge-dark">${u.rol}</span></td>
                    <td>${u.email}</td>
                    <td class="text-center">
                        <button class="btn btn-warning btn-sm" onclick="editarUserC(${u.id})"><i class="fa fa-edit"></i></button>
                    </td>
                </tr>`;
            });
            $("#tbodyUsuariosClinicas").html(html);
        });
    }

    window.editarUserC = function(id) {
        $.post('ajax/usuarios_clinicas_ajax.php', {action: 'obtener', id: id}, function(u) {
            $("#idUserC").val(u.id);
            $("#clinica_id_hidden").val(u.clinica_id);
            $("#nombre_uc").val(u.nombre);
            $("#usuario_uc").val(u.usuario);
            $("#email_uc").val(u.email);
            $("#rol_id").val(u.rol_id); // Cambiado: ahora carga el ID del rol
            $("#password").val("");
            $(".modal-title").text("Editar Usuario de Clínica");
            $("#modalUsuarioClinica").modal("show");
        });
    }

    window.limpiarFormUsuarioClinica = function() {
        $("#formUsuarioClinica")[0].reset();
        $("#idUserC").val("");
        $(".modal-title").text("Nuevo Usuario de Clínica");
    }
})(jQuery);