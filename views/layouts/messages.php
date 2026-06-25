<?php if (isset($_SESSION['welcome_user'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            AlertUtils.welcome(<?= json_encode((string) $_SESSION['welcome_user']); ?>);
        });
    </script>
<?php
    unset($_SESSION['welcome_user']);
endif;

if (isset($_SESSION['message'], $_SESSION['icon'])):
    $message = $_SESSION['message'];
    $icon    = $_SESSION['icon'];
?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            ToastUtils[<?= json_encode((string) $icon); ?>](<?= json_encode((string) $message); ?>);
        });
    </script>
<?php
    unset($_SESSION['message'], $_SESSION['icon']);
endif; ?>