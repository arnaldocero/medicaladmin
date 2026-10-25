<?php include "includes/header.php"; ?>
<?php include "includes/menu.php"; ?>

<div id="right-panel" class="right-panel">
    <div class="content">
        <div class="animated fadeIn">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <strong class="card-title">Usuarios de Clínicas</strong>
                            <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#modalUsuarioClinica" onclick="limpiarFormUsuarioClinica()">
                                <i class="fa fa-plus"></i> Nuevo Usuario
                            </button>
                        </div>
                        <div class="card-body">
                            <table class="table table-striped table-bordered">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Clínica</th>
                                        <th>Nombre / Usuario</th>
                                        <th>Rol</th>
                                        <th>Email</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyUsuariosClinicas"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalUsuarioClinica" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Gestión de Usuario</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formUsuarioClinica">
                <div class="modal-body">
                    <input type="hidden" id="idUserC" name="idUserC">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Clínica</label>
                            <select name="clinica_id_hidden" id="clinica_id_hidden" class="form-control" required>
                                <option value="">Seleccione...</option>
                                <?php
                                require_once "class/Conexion.php";
                                $db = Conexion::conectar();
                                $res = $db->query("SELECT id, nombre_clinica FROM clientes_clinicas WHERE estado = 1");
                                while($f = $res->fetch(PDO::FETCH_ASSOC)) echo "<option value='".(int)$f['id']."'>".htmlspecialchars($f['nombre_clinica'], ENT_QUOTES, 'UTF-8')."</option>";
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Rol del Usuario</label>
                            <select name="rol_id" id="rol_id" class="form-control" required>
                                <option value="">Seleccione Rol...</option>
                                <?php
                                $resR = $db->query("SELECT id, nombre_rol FROM roles_clinicas");
                                while($r = $resR->fetch(PDO::FETCH_ASSOC)) echo "<option value='".(int)$r['id']."'>".htmlspecialchars($r['nombre_rol'], ENT_QUOTES, 'UTF-8')."</option>";
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group"><label>Nombre</label><input type="text" name="nombre_uc" id="nombre_uc" class="form-control" required></div>
                        <div class="col-md-6 form-group"><label>Usuario</label><input type="text" name="usuario_uc" id="usuario_uc" class="form-control" required></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group"><label>Email</label><input type="email" name="email_uc" id="email_uc" class="form-control"></div>
                        <div class="col-md-6 form-group">
                            <label>Password</label>
                            <input type="password" name="password" id="password" class="form-control">
                            <small class="text-muted">Vacío para mantener actual.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-info text-white">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include "includes/footer.php"; ?>