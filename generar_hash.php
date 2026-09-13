<?php
// Generamos un hash real para la palabra 'admin123'
$passwordPlana = "admin123";
$nuevoHash = password_hash($passwordPlana, PASSWORD_DEFAULT);

echo "<h3>Copia este hash exacto:</h3>";
echo "<input type='text' value='$nuevoHash' style='width:100%; padding:10px;' readonly>";
echo "<p>Luego ve a phpMyAdmin y pégalo en la columna <b>password</b> del usuario <b>arnaldopachecosarmiento@gmail.com</b></p>";
?>