</div>
<!-- /.content-wrapper -->

<!-- Main Footer -->
<footer class="main-footer">
    <!-- To the right -->
    <div class="float-right d-none d-sm-inline">
        <div class="text-muted">
            <small>
                <i class="fas fa-tag"></i> Versión <?= $APP_VERSION; ?>
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

<!-- Scripts principales de la aplicación -->
<script src="<?= $URL; ?>public/js/core/common-utils.js"></script>

<!-- Plugins condicionales JS -->
<?php
$plugin_js = [
    'datatables' => [
        'plugins/datatables/jquery.dataTables.min.js',
        'plugins/datatables/dataTables.bootstrap4.min.js',
        'plugins/datatables/dataTables.responsive.min.js',
        'plugins/datatables/responsive.bootstrap4.min.js',
        'plugins/datatables/dataTables.buttons.min.js',
        'plugins/datatables/buttons.bootstrap4.min.js',
        'plugins/datatables/buttons.html5.min.js',
        'plugins/datatables/buttons.print.min.js',
        'plugins/datatables/buttons.colVis.min.js',
        'core/common-datatable.js',
    ],
    'datatables-export' => [
        'plugins/utils/jszip.min.js',
        'plugins/utils/pdfmake.min.js',
        'plugins/utils/vfs_fonts.js',
    ],
    'select2' => [
        'plugins/select2/select2.min.js',
    ],
    'validate' => [
        'plugins/validations/jquery.validate.min.js',
        'plugins/validations/additional-methods.min.js',
        'core/common-validate.js',
    ],
    'chart' => [
        'plugins/chart/Chart.js',
    ],
];
if (isset($plugins) && is_array($plugins)):
    foreach ($plugins as $plugin):
        if (isset($plugin_js[$plugin])):
            foreach ($plugin_js[$plugin] as $js): ?>
                <script src="<?= $URL; ?>public/js/<?= $js; ?>"></script>
<?php endforeach;
        endif;
    endforeach;
endif;
?>

<!-- Scripts específicos por módulo -->
<?php if (isset($module_scripts) && is_array($module_scripts)): ?>
    <?php foreach ($module_scripts as $script): ?>
        <script src="<?= $URL; ?>public/js/modules/<?= $script; ?>.js"></script>
    <?php endforeach; ?>
<?php endif; ?>

</body>

</html>