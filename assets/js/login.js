$("#formLogin").on("submit", function(e) {
    e.preventDefault();

    const email = $("#email").val();
    const password = $("#password").val();

    $.ajax({
        url: 'ajax/login_ajax.php',
        type: 'POST',
        // Enviamos los datos como un objeto que PHP entienda bien
        data: {
            email: email,
            password: password
        },
        success: function(response) {
            try {
                const res = JSON.parse(response);
                if(res.status === 'success') {
                    window.location.href = 'index.php';
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            } catch (e) {
                console.error("Error en el JSON:", response);
                Swal.fire('Error', 'El servidor respondió algo inválido', 'error');
            }
        }
    });
});