<?php
$rolRequerido = "superadmin";
include "includes/header.php";
?>
<?php include "includes/menu.php"; ?>
<?php
// Usuarios del panel (también inactivos) para el filtro
$usuariosFiltro = Conexion::conectar()
    ->query("SELECT id, nombre, estado FROM usuarios_admin ORDER BY nombre")
    ->fetchAll(PDO::FETCH_ASSOC);

// Fechas por defecto según el reloj de MySQL (el mismo con el que se guardan los registros)
[$hoy, $hace30] = Conexion::conectar()->query("SELECT CURDATE(), CURDATE() - INTERVAL 30 DAY")->fetch(PDO::FETCH_NUM);
?>

<div id="right-panel" class="right-panel">
    <div class="content">
        <div class="animated fadeIn">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <strong class="card-title">Bitácora de cambios</strong>
                        </div>
                        <div class="card-body">
                            <form id="formFiltroBitacora" class="form-row align-items-end mb-3">
                                <div class="col-sm-6 col-lg-2 form-group">
                                    <label for="f_desde">Desde</label>
                                    <input type="date" id="f_desde" name="desde" class="form-control form-control-sm" value="<?php echo $hace30; ?>">
                                </div>
                                <div class="col-sm-6 col-lg-2 form-group">
                                    <label for="f_hasta">Hasta</label>
                                    <input type="date" id="f_hasta" name="hasta" class="form-control form-control-sm" value="<?php echo $hoy; ?>">
                                </div>
                                <div class="col-sm-6 col-lg-3 form-group">
                                    <label for="f_usuario">Usuario</label>
                                    <select id="f_usuario" name="usuario_id" class="form-control form-control-sm">
                                        <option value="">Todos</option>
                                        <?php foreach ($usuariosFiltro as $u): ?>
                                        <option value="<?php echo (int)$u["id"]; ?>"><?php echo htmlspecialchars($u["nombre"], ENT_QUOTES, "UTF-8") . ($u["estado"] ? "" : " (inactivo)"); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-sm-6 col-lg-2 form-group">
                                    <label for="f_entidad">Sobre</label>
                                    <select id="f_entidad" name="entidad" class="form-control form-control-sm">
                                        <option value="">Todo</option>
                                        <option value="clinica">Clínicas</option>
                                        <option value="usuario_clinica">Usuarios de clínicas</option>
                                        <option value="usuario_admin">Personal del panel</option>
                                        <option value="plan">Planes</option>
                                        <option value="sesion">Inicios de sesión</option>
                                    </select>
                                </div>
                                <div class="col-sm-6 col-lg-2 form-group">
                                    <label for="f_accion">Acción</label>
                                    <select id="f_accion" name="accion_filtro" class="form-control form-control-sm">
                                        <option value="">Todas</option>
                                        <option value="crear">Crear</option>
                                        <option value="editar">Editar</option>
                                        <option value="eliminar">Eliminar</option>
                                        <option value="desactivar">Desactivar</option>
                                        <option value="activar">Reactivar</option>
                                        <option value="login">Inicio de sesión</option>
                                    </select>
                                </div>
                                <div class="col-sm-6 col-lg-1 form-group">
                                    <button type="submit" class="btn btn-primary btn-sm btn-block"><i class="fa fa-filter"></i> Filtrar</button>
                                </div>
                            </form>

                            <div id="avisoLimite" class="alert alert-info py-2 d-none"></div>

                            <table class="table table-striped table-bordered table-sm">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Usuario</th>
                                        <th>Acción</th>
                                        <th>Descripción</th>
                                        <th>Cambios</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaBitacora"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> </div>

<?php
$paginaJs = "bitacora.js";
include "includes/footer.php";
?>
