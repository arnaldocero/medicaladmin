$("#formLogin").on("submit", function(e) {
    e.preventDefault();

    const btn = $(this).find("button[type='submit']");
    btn.prop("disabled", true);

    $.ajax({
        url: 'ajax/login_ajax.php',
        type: 'POST',
        dataType: 'json',
        data: {
            email: $("#email").val(),
            password: $("#password").val()
        },
        success: function(res) {
            if (res.status === 'success') {
                window.location.href = 'index.php';
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        },
        error: function(xhr) {
            // 429 = demasiados intentos; el servidor envía el mensaje en JSON
            const res = xhr.responseJSON || {};
            Swal.fire('Error', res.message || 'El servidor respondió algo inválido', 'error');
        },
        complete: function() {
            btn.prop("disabled", false);
        }
    });
});
