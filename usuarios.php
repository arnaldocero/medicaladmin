<?php include "includes/header.php"; ?>
<?php include "includes/menu.php"; ?>

<?php
if($_SESSION["rol"] !== "superadmin"){
    header("Location: index.php");
    exit();
}
?>

<div id="right-panel" class="right-panel">
    <div class="content">
        <div class="animated fadeIn">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <strong class="card-title">Gestión de Usuarios Administrativos</strong>
                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalUsuario" onclick="limpiarFormulario()">
                                <i class="fa fa-plus"></i> Nuevo Usuario
                            </button>
                        </div>
                        <div class="card-body">
                            <table class="table table-striped table-bordered">
                                <thead class="thead-dark">
                                    <tr>
                                        <th><i class="fa fa-user"></i> Nombre</th>
                                        <th><i class="fa fa-envelope"></i> Email</th>
                                        <th><i class="fa fa-vcard"></i> Rol</th>
                                        <th class="text-center"><i class="fa fa-cog"></i> Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaUsuarios">
                                    </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> </div> <div class="modal fade" id="modalUsuario" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="tituloModal">Nuevo Usuario</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formUsuario" onsubmit="return false;">
                <div class="modal-body">
                    <input type="hidden" id="idUsuario" name="idUsuario">
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-control-label">Nombre Completo</label>
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-user"></i></div>
                                    <input type="text" name="nombre" id="nombre" class="form-control" placeholder="Ej. Juan Pérez" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-control-label">Correo Electrónico</label>
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-envelope"></i></div>
                                    <input type="email" name="email" id="email" class="form-control" placeholder="juan@clinica.com" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-control-label">Contraseña</label>
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-lock"></i></div>
                                    <input type="password" name="password" id="password" class="form-control" placeholder="Min. 6 caracteres">
                                </div>
                                <small class="form-text text-muted">Si editas, deja en blanco para mantener la actual.</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-control-label">Nivel de Acceso</label>
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-shield"></i></div>
                                    <select name="rol" id="rol" class="form-control">
                                        <option value="superadmin">Super Administrador</option>
                                        <option value="admin">Administrador</option>
                                    </select>
                                </div>
                                <small class="form-text text-muted">El Admin no puede gestionar otros usuarios.</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>