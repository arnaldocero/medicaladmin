<?php
$rolRequerido = "superadmin";
include "includes/header.php";
?>
<?php include "includes/menu.php"; ?>

<div id="right-panel" class="right-panel">
    <div class="content">
        <div class="animated fadeIn">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <strong class="card-title">Planes de membresía</strong>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small">
                                El precio mensual se usa para calcular los ingresos del dashboard. El límite de usuarios
                                impide crear o reactivar usuarios de una clínica que ya llegó al máximo de su plan
                                (vacío = sin límite).
                            </p>
                            <table class="table table-striped table-bordered">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Plan</th>
                                        <th>Precio mensual</th>
                                        <th>Límite de usuarios</th>
                                        <th>Clínicas activas</th>
                                        <th>Ingreso mensual (vigentes)</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaPlanes"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> </div>

<div class="modal fade" id="modalPlan" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="tituloModalPlan">Editar plan</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formPlan">
                <div class="modal-body">
                    <input type="hidden" id="idPlan" name="idPlan">
                    <div class="form-group">
                        <label for="nombre_plan">Nombre visible</label>
                        <input type="text" id="nombre_plan" name="nombre" class="form-control" maxlength="50" required>
                        <small class="text-muted">Código interno: <b id="codigoPlan"></b> (no cambia)</small>
                    </div>
                    <div class="form-group">
                        <label for="precio_mensual">Precio mensual (COP)</label>
                        <input type="number" id="precio_mensual" name="precio_mensual" class="form-control" min="0" step="1" required>
                    </div>
                    <div class="form-group">
                        <label for="max_usuarios">Límite de usuarios activos por clínica</label>
                        <input type="number" id="max_usuarios" name="max_usuarios" class="form-control" min="1" step="1" placeholder="Sin límite">
                        <small class="text-muted">Déjalo vacío para no limitar.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$paginaJs = "planes.js";
include "includes/footer.php";
?>
