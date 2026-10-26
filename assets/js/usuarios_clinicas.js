(function($) {
    "use strict";

    $(document).ready(function() {
        listarTodosLosUsuarios();

        // Al cambiar un filtro se vuelve a pedir la lista al servidor
        $("#filtroClinica, #filtroEstado").on("change", listarTodosLosUsuarios);

        $("#formUsuarioClinica").on("submit", function(e) {
            e.preventDefault();
            let datos = $(this).serialize() + "&action=guardar";
            $.post('ajax/usuarios_clinicas_ajax.php', datos, function(res) {
                if(res.status === "success") {
                    Swal.fire('¡Éxito!', 'Usuario guardado', 'success');
                    $('#modalUsuarioClinica').modal('hide');
                    listarTodosLosUsuarios();
                } else {
                    Swal.fire('Error', res.message || 'No se pudo guardar', 'error');
                }
            });
        });
    });

    window.listarTodosLosUsuarios = function() {
        const filtros = { action: 'listar_global', clinica_id: $("#filtroClinica").val(), estado: $("#filtroEstado").val() };
        $.get('ajax/usuarios_clinicas_ajax.php', filtros, function(data) {
            let html = '';
            data.forEach(u => {
                const activo = Number(u.estado) === 1;
                const clinicaInactiva = Number(u.clinica_estado) === 1 ? '' : ' <small class="text-danger">(inactiva)</small>';
                const botonEstado = activo
                    ? `<button class="btn btn-danger btn-sm" title="Desactivar" onclick="cambiarEstadoUserC(${Number(u.id)}, 0)"><i class="fa fa-ban"></i></button>`
                    : `<button class="btn btn-success btn-sm" title="Reactivar" onclick="cambiarEstadoUserC(${Number(u.id)}, 1)"><i class="fa fa-check"></i></button>`;
                html += `<tr>
                    <td><span class="badge badge-light">${esc(u.nombre_clinica)}</span>${clinicaInactiva}</td>
                    <td>${esc(u.nombre)}<br><small class="text-primary">${esc(u.usuario)}</small></td>
                    <td><span class="badge badge-dark">${esc(u.rol)}</span></td>
                    <td>${esc(u.email)}</td>
                    <td>${activo ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-secondary">Inactivo</span>'}</td>
                    <td class="text-center text-nowrap">
                        <button class="btn btn-warning btn-sm" title="Editar" onclick="editarUserC(${Number(u.id)})"><i class="fa fa-edit"></i></button>
                        ${botonEstado}
                    </td>
                </tr>`;
            });
            pintarTabla("#tbodyUsuariosClinicas", html, { exportar: { archivo: "usuarios_clinicas", titulo: "Usuarios de clínicas", sinUltimaColumna: true } });
        });
    }

    window.cambiarEstadoUserC = function(id, estado) {
        const desactivar = estado === 0;
        Swal.fire({
            title: desactivar ? '¿Desactivar usuario?' : '¿Reactivar usuario?',
            text: desactivar
                ? 'No podrá iniciar sesión en el sistema. Su historial (citas, historias clínicas, etc.) se conserva.'
                : 'Podrá volver a iniciar sesión en el sistema.',
            icon: desactivar ? 'warning' : 'question',
            showCancelButton: true,
            confirmButtonText: desactivar ? 'Sí, desactivar' : 'Sí, reactivar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (!result.isConfirmed) return;
            $.post('ajax/usuarios_clinicas_ajax.php', { action: 'cambiar_estado', id: id, estado: estado }, function(res) {
                if (res.status === 'success') {
                    Swal.fire(desactivar ? 'Desactivado' : 'Reactivado', '', 'success');
                    listarTodosLosUsuarios();
                } else {
                    Swal.fire('Error', res.message || 'No se pudo cambiar el estado', 'error');
                }
            });
        });
    }

    window.editarUserC = function(id) {
        $.post('ajax/usuarios_clinicas_ajax.php', {action: 'obtener', id: id}, function(u) {
            $("#idUserC").val(u.id);
            $("#clinica_id_hidden").val(u.clinica_id);
            $("#nombre_uc").val(u.nombre);
            $("#usuario_uc").val(u.usuario);
            $("#email_uc").val(u.email);
            $("#rol_id").val(u.rol_id);
            $("#password").val("");
            $(".modal-title").text("Editar Usuario de Clínica");
            $("#modalUsuarioClinica").modal("show");
        });
    }

    window.limpiarFormUsuarioClinica = function() {
        $("#formUsuarioClinica")[0].reset();
        $("#idUserC").val("");
        // Si hay una clínica filtrada, se propone para el usuario nuevo
        if ($("#filtroClinica").val()) $("#clinica_id_hidden").val($("#filtroClinica").val());
        $(".modal-title").text("Nuevo Usuario de Clínica");
    }
})(jQuery);
