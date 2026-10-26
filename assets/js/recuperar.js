// Formularios públicos de recuperación de contraseña (recuperar.php y restablecer.php)
(function($) {
    "use strict";

    function enviar($form, accion, alTerminar) {
        const btn = $form.find("button[type='submit']").prop("disabled", true);
        $.ajax({
            url: 'ajax/recuperar_ajax.php',
            type: 'POST',
            dataType: 'json',
            data: $form.serialize() + "&action=" + accion,
            success: alTerminar,
            error: function(xhr) {
                const res = xhr.responseJSON || {};
                Swal.fire('Error', res.message || 'No se pudo procesar la solicitud', 'error');
            },
            complete: function() { btn.prop("disabled", false); }
        });
    }

    $("#formSolicitar").on("submit", function(e) {
        e.preventDefault();
        enviar($(this), "solicitar", function(res) {
            Swal.fire(res.status === 'success' ? 'Revisa tu correo' : 'Error', res.message, res.status === 'success' ? 'info' : 'error');
        });
    });

    $("#formRestablecer").on("submit", function(e) {
        e.preventDefault();
        if ($("#password_nueva").val() !== $("#password_confirmar").val()) {
            Swal.fire('Error', 'La confirmación no coincide con la nueva contraseña', 'error');
            return;
        }
        enviar($(this), "restablecer", function(res) {
            if (res.status === 'success') {
                Swal.fire('¡Listo!', 'Tu contraseña se cambió. Ya puedes iniciar sesión.', 'success')
                    .then(() => { window.location.href = 'login.php'; });
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        });
    });

})(jQuery);
