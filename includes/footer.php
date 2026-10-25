<footer class="site-footer">
        <div class="footer-inner bg-white">
            <div class="row">
                <div class="col-sm-6">
                    Copyright &copy; 2024 Admin Clínica
                </div>
            </div>
        </div>
    </footer>

</div><script src="https://cdn.jsdelivr.net/npm/jquery@2.2.4/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-match-height@0.7.2/dist/jquery.matchHeight.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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