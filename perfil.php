<?php include "includes/header.php"; ?>
<?php include "includes/menu.php"; ?>
<?php
$stmt = Conexion::conectar()->prepare("SELECT nombre, email, rol FROM usuarios_admin WHERE id = :id");
$stmt->execute([":id" => $_SESSION["id"]]);
$yo = $stmt->fetch(PDO::FETCH_ASSOC);
$e = function($t) { return htmlspecialchars((string)$t, ENT_QUOTES, "UTF-8"); };
?>

<div id="right-panel" class="right-panel">
<?php include "includes/barra_superior.php"; ?>
    <div class="content">
        <div class="animated fadeIn">
            <div class="row">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header"><strong class="card-title">Mis datos</strong></div>
                        <div class="card-body">
                            <form id="formNombre">
                                <div class="form-group">
                                    <label for="perfil_nombre">Nombre</label>
                                    <input type="text" id="perfil_nombre" name="nombre" class="form-control" maxlength="100" required value="<?php echo $e($yo["nombre"]); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="text" class="form-control" value="<?php echo $e($yo["email"]); ?>" disabled>
                                    <small class="text-muted">Para cambiarlo, pídeselo a un superadmin.</small>
                                </div>
                                <div class="form-group">
                                    <label>Rol</label>
                                    <input type="text" class="form-control" value="<?php echo $yo["rol"] === "superadmin" ? "Super administrador" : "Administrador"; ?>" disabled>
                                </div>
                                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Guardar nombre</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header"><strong class="card-title">Cambiar contraseña</strong></div>
                        <div class="card-body">
                            <form id="formPassword" autocomplete="off">
                                <div class="form-group">
                                    <label for="password_actual">Contraseña actual</label>
                                    <input type="password" id="password_actual" name="password_actual" class="form-control" autocomplete="current-password" required>
                                </div>
                                <div class="form-group">
                                    <label for="password_nueva">Nueva contraseña</label>
                                    <input type="password" id="password_nueva" name="password_nueva" class="form-control" minlength="8" autocomplete="new-password" required>
                                    <small class="text-muted">Mínimo 8 caracteres.</small>
                                </div>
                                <div class="form-group">
                                    <label for="password_confirmar">Confirmar nueva contraseña</label>
                                    <input type="password" id="password_confirmar" name="password_confirmar" class="form-control" minlength="8" autocomplete="new-password" required>
                                </div>
                                <button type="submit" class="btn btn-warning"><i class="fa fa-key"></i> Cambiar contraseña</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> </div>

<?php
$paginaJs = "perfil.js";
include "includes/footer.php";
?>
