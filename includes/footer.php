<footer class="site-footer">
        <div class="footer-inner bg-white">
            <div class="row">
                <div class="col-sm-6">
                    Copyright &copy; 2024 Admin Clínica
                </div>
            </div>
        </div>
    </footer>

</div><script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net@1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net-bs4@1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jszip@3.10.1/dist/jszip.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net-buttons@2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net-buttons-bs4@2.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net-buttons@2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-match-height@0.7.2/dist/jquery.matchHeight.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
<?php
// Scripts propios: los comunes y solo el de la página actual ($paginaJs).
// ?v= cambia cuando se modifica el archivo, para que el navegador no use una copia vieja.
$scriptsJs = ["main.js", "utils.js"];
if (!empty($paginaJs)) $scriptsJs[] = $paginaJs;
foreach ($scriptsJs as $js) {
    $ruta = "assets/js/" . $js;
    echo '<script src="' . $ruta . '?v=' . @filemtime(__DIR__ . "/../" . $ruta) . '"></script>' . "\n";
}
?>

</body>
</html>