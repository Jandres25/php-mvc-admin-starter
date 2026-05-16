<?php if (isset($_SESSION['welcome_user'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            AlertUtils.welcome('<?= addslashes($_SESSION['welcome_user']); ?>');
        });
    </script>
<?php
    unset($_SESSION['welcome_user']);
endif;

if (isset($_SESSION['message'], $_SESSION['icon'])):
    $message = addslashes($_SESSION['message']);
    $icon    = $_SESSION['icon'];
?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            ToastUtils['<?= $icon; ?>']('<?= $message; ?>');
        });
    </script>
<?php
    unset($_SESSION['message'], $_SESSION['icon']);
endif; ?>
