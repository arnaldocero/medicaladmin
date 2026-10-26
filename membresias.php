<?php
$rolRequerido = "superadmin";
include "includes/header.php";
?>
<?php include "includes/menu.php"; ?>
<?php
require_once "includes/planes.php";
$planes = obtenerPlanes(Conexion::conectar());
?>

<div id="right-panel" class="right-panel">
    <div class="content">
        <div class="animated fadeIn">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <strong class="card-title">Clientes / Clínicas Contratadas</strong>
                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalClinica" onclick="limpiarClinica()">
                                <i class="fa fa-plus"></i> Registrar Clínica
                            </button>
                        </div>
                        <div class="card-body">
                            <table class="table table-striped table-bordered">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>NIT / Clínica</th>
                                        <th>Representante</th>
                                        <th>Plan</th>
                                        <th>Usuarios</th>
                                        <th>Vencimiento</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaClinicas"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalClinica" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Nueva Clínica</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formClinica">
                <div class="modal-body">
                    <input type="hidden" id="idCliente" name="idCliente">
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>NIT Empresa</label>
                            <input type="text" name="nit_empresa" id="nit_empresa" class="form-control" required>
                        </div>
                        <div class="col-md-8 form-group">
                            <label>Nombre de la Clínica</label>
                            <input type="text" name="nombre_clinica" id="nombre_clinica" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Representante Legal</label>
                            <input type="text" name="representante_legal" id="representante_legal" class="form-control">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Teléfono</label>
                            <input type="text" name="telefono" id="telefono" class="form-control">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Email Contacto</label>
                            <input type="email" name="email_contacto" id="email_contacto" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>Plan Contratado</label>
                            <select name="plan_contratado" id="plan_contratado" class="form-control">
                                <?php foreach ($planes as $codigo => $p): ?>
                                <option value="<?php echo htmlspecialchars($codigo, ENT_QUOTES, "UTF-8"); ?>"><?php echo htmlspecialchars($p["nombre"], ENT_QUOTES, "UTF-8") . " — " . formatoPesos($p["precio_mensual"]) . "/mes" . ($p["max_usuarios"] !== null ? " · máx. " . (int)$p["max_usuarios"] . " usuarios" : ""); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Fecha Activación</label>
                            <input type="date" name="fecha_activacion" id="fecha_activacion" class="form-control" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Fecha Vencimiento</label>
                            <input type="date" name="fecha_vencimiento" id="fecha_vencimiento" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Guardar Clínica</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$paginaJs = "membresias.js";
include "includes/footer.php";
?>