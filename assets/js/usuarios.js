// Usamos jQuery de forma segura para evitar el error "$" is not defined
(function($) {
    "use strict";

    $(document).ready(function() {
        listarUsuarios();

        // Manejar el submit del formulario
        $("#formUsuario").on("submit", function(e) {
            e.preventDefault();
            registrarUsuarioManual();
            return false;
        });
    });

    // Hacer las funciones globales para que los botones onclick="...()" las vean
    window.listarUsuarios = function() {
        $.get('ajax/usuarios_ajax.php?action=listar', function(data) {
            // ELIMINAMOS EL JSON.parse(data) porque 'data' ya es el objeto.
            let usuarios = data; 
            try {
                let html = '';
                usuarios.forEach(u => {
                    let badgeClass = (u.rol === 'superadmin') ? 'badge-danger' : 'badge-primary';
                    html += `<tr>
                        <td>${esc(u.nombre)}</td>
                        <td>${esc(u.email)}</td>
                        <td><span class="badge ${badgeClass} px-3 py-2">${esc(String(u.rol).toUpperCase())}</span></td>
                        <td class="text-center">
                            <div class="btn-group">
                                <button class="btn btn-warning btn-sm" onclick="editarUsuario(${Number(u.id)})"><i class="fa fa-edit"></i></button>
                                <button class="btn btn-danger btn-sm" onclick="eliminarUsuario(${Number(u.id)})"><i class="fa fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>`;
                });
                $("#tablaUsuarios").html(html);
            } catch (e) { 
                console.error("Error al renderizar la tabla:", e); 
            }
        });
    }
    
    window.registrarUsuarioManual = function() {
        let nombre = $("#nombre").val();
        let email = $("#email").val();
    
        if (nombre == "" || email == "") {
            Swal.fire('Error', 'Nombre y Email son obligatorios', 'error');
            return;
        }
    
        let datos = $("#formUsuario").serialize() + "&action=guardar";
    
        $.ajax({
            url: 'ajax/usuarios_ajax.php',
            type: 'POST',
            data: datos,
            success: function(respuesta) {
                // 'respuesta' ya viene como objeto gracias al header JSON de PHP
                if (respuesta.status == "success") {
                    Swal.fire('¡Éxito!', 'Usuario guardado', 'success');
                    $('#modalUsuario').modal('hide');
                    listarUsuarios();
                    $("#formUsuario")[0].reset();
                } else {
                    Swal.fire('Error', respuesta.message, 'error');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("Error en la petición AJAX:", textStatus, errorThrown);
            }
        });
    }

    window.editarUsuario = function(id) {
        // Enviamos la petición al PHP
        $.post('ajax/usuarios_ajax.php', { action: 'obtener_usuario', id: id }, function(response) {
            
            // ELIMINAMOS ESTA LÍNEA: const u = JSON.parse(response);
            // USAMOS DIRECTAMENTE LA RESPUESTA:
            const u = response; 
    
            // 2. Llenamos los campos del modal
            $("#idUsuario").val(u.id);
            $("#nombre").val(u.nombre);
            $("#email").val(u.email);
            $("#rol").val(u.rol);
            
            // El password siempre se deja vacío en edición
            $("#password").val("");
    
            // 3. Cambiamos el título y abrimos
            $(".modal-title").text("Editar Usuario: " + u.nombre);
            $("#modalUsuario").modal("show");
            
        });
    }

    window.eliminarUsuario = function(id) {
        Swal.fire({
            title: '¿Estás seguro?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, borrar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('ajax/usuarios_ajax.php', { action: 'eliminar', id: id }, function() {
                    listarUsuarios();
                    Swal.fire('Eliminado', '', 'success');
                });
            }
        });
    }

    window.limpiarFormulario = function() {
        $("#formUsuario")[0].reset();
        $("#idUsuario").val("");
        $(".modal-title").text("Nuevo Usuario");
    }

})(jQuery);