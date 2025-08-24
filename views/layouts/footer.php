</div>
<!-- /.content-wrapper -->

<!-- Main Footer -->
<footer class="main-footer">
    <!-- To the right -->
    <div class="float-right d-none d-sm-inline">
        <div class="text-muted">
            <small>
                <i class="fas fa-tag"></i> Versión 1.0.0
            </small>
        </div>
    </div>
    <div class="footer-content">
        <strong>Copyright &copy; <?= date('Y'); ?>
            <a href="#" class="text-decoration-none">ProyectoBase</a>
        </strong>
        - Sistema Base
    </div>
</footer>
</div>
<!-- ./wrapper -->

<!-- Bootstrap 4 -->
<script src="<?= $URL; ?>public/js/lib/bootstrap/bootstrap.bundle.min.js"></script>

<!-- AdminLTE App -->
<script src="<?= $URL; ?>public/js/lib/adminlte/adminlte.min.js"></script>

<!-- DataTables y extensiones (IMPORTANTE: mantener este orden) -->
<script src="<?= $URL; ?>public/js/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?= $URL; ?>public/js/plugins/datatables/dataTables.bootstrap4.min.js"></script>
<script src="<?= $URL; ?>public/js/plugins/datatables/dataTables.responsive.min.js"></script>
<script src="<?= $URL; ?>public/js/plugins/datatables/responsive.bootstrap4.min.js"></script>
<script src="<?= $URL; ?>public/js/plugins/datatables/dataTables.buttons.min.js"></script>
<script src="<?= $URL; ?>public/js/plugins/datatables/buttons.bootstrap4.min.js"></script>
<script src="<?= $URL; ?>public/js/plugins/datatables/buttons.html5.min.js"></script>
<script src="<?= $URL; ?>public/js/plugins/datatables/buttons.print.min.js"></script>
<script src="<?= $URL; ?>public/js/plugins/datatables/buttons.colVis.min.js"></script>

<!-- Bibliotecas para exportación -->
<script src="<?= $URL; ?>public/js/plugins/utils/jszip.min.js"></script>
<script src="<?= $URL; ?>public/js/plugins/utils/pdfmake.min.js"></script>
<script src="<?= $URL; ?>public/js/plugins/utils/vfs_fonts.js"></script>

<!-- Select2 -->
<script src="<?= $URL; ?>public/js/plugins/select2/select2.min.js"></script>

<!-- Scripts principales de la aplicación -->
<script src="<?= $URL; ?>public/js/core/common-utils.js"></script>
<script src="<?= $URL; ?>public/js/core/common-datatable.js"></script>

<!-- Scripts específicos por módulo -->
<?php if (isset($module_scripts) && is_array($module_scripts)): ?>
    <?php foreach ($module_scripts as $script): ?>
        <script src="<?= $URL; ?>public/js/modules/<?= $script; ?>.js"></script>
    <?php endforeach; ?>
<?php endif; ?>

</body>

</html>