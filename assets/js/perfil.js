(function($) {
    "use strict";

    $(document).ready(function() {
        $("#formNombre").on("submit", function(e) {
            e.preventDefault();
            $.post('ajax/perfil_ajax.php', $(this).serialize() + "&action=guardar_nombre", function(res) {
                if (res.status === "success") {
                    // Se recarga para que la barra superior muestre el nombre y la inicial nuevos
                    Swal.fire('¡Listo!', 'Nombre actualizado', 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', res.message || 'No se pudo guardar', 'error');
                }
            });
        });

        $("#formPassword").on("submit", function(e) {
            e.preventDefault();
            if ($("#password_nueva").val() !== $("#password_confirmar").val()) {
                Swal.fire('Error', 'La confirmación no coincide con la nueva contraseña', 'error');
                return;
            }
            $.post('ajax/perfil_ajax.php', $(this).serialize() + "&action=cambiar_password", function(res) {
                if (res.status === "success") {
                    $("#formPassword")[0].reset();
                    Swal.fire('¡Listo!', 'Tu contraseña se cambió correctamente', 'success');
                } else {
                    Swal.fire('Error', res.message || 'No se pudo cambiar la contraseña', 'error');
                }
            });
        });
    });

})(jQuery);
