</div>
<!-- /.content-wrapper -->

<!-- Main Footer -->
<footer class="main-footer">
    <!-- To the right -->
    <div class="float-right d-none d-sm-inline">
        <div class="text-muted">
            <small>
                <i class="fas fa-tag"></i> Version <?= $APP_VERSION; ?>
            </small>
        </div>
    </div>
    <div class="footer-content">
        <strong>Copyright &copy; <?= date('Y'); ?>
            <a href="#" class="text-decoration-none">BaseProject</a>
        </strong>
        - Base System
    </div>
</footer>
</div>
<!-- ./wrapper -->

<!-- Bootstrap 4 -->
<script src="<?= $URL; ?>public/js/lib/bootstrap/bootstrap.bundle.min.js"></script>

<!-- AdminLTE App -->
<script src="<?= $URL; ?>public/js/lib/adminlte/adminlte.min.js"></script>

<!-- Main application scripts -->
<script src="<?= $URL; ?>public/js/core/common-utils.js"></script>

<!-- Conditional JS plugins -->
<?php
$activePlugins = (isset($plugins) && is_array($plugins)) ? $plugins : [];
$pluginJsFiles = \App\Core\AssetRegistry::resolvePluginJs($activePlugins);
foreach ($pluginJsFiles as $js): ?>
    <script src="<?= $URL; ?>public/js/<?= $js; ?>"></script>
<?php endforeach; ?>

<!-- UI Components utilities (after plugins so Select2/Tooltip are available) -->
<script src="<?= $URL; ?>public/js/core/ui-components.js"></script>

<!-- Module-specific scripts -->
<?php if (isset($module_scripts) && is_array($module_scripts)): ?>
    <?php foreach ($module_scripts as $script): ?>
        <script src="<?= $URL; ?>public/js/modules/<?= $script; ?>.js"></script>
    <?php endforeach; ?>
<?php endif; ?>

</body>

</html>
