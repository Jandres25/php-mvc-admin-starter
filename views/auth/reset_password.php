<?php
require_once __DIR__ . '/../../views/layouts/session.php';

if (isAuthenticated()) {
    header('Location: ' . $URL);
    exit;
}

require_once __DIR__ . '/../../config/config.php';

$token = isset($_GET['token']) ? trim($_GET['token']) : '';

if (empty($token)) {
    $_SESSION['message'] = 'Invalid or missing token.';
    $_SESSION['icon']    = 'error';
    header('Location: ' . $URL . 'views/auth/login.php');
    exit;
}

$userModel = new \App\Models\User();
$user = $userModel->getUserByResetToken($token);

if (!$user) {
    $_SESSION['message'] = 'The link has expired or is invalid.';
    $_SESSION['icon']    = 'error';
    header('Location: ' . $URL . 'views/auth/login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Base System | Reset Password</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?= $URL; ?>public/css/lib/fontawesome/all.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="<?= $URL; ?>public/css/lib/adminlte/adminlte.min.css">
    <link rel="stylesheet" href="<?= $URL; ?>public/css/core/webfonts.css">
    <link rel="icon" type="image/png" href="<?= $URL; ?>public/img/e-commerce_logo.png">
    <!-- Sweetalert2 -->
    <link rel="stylesheet" href="<?= $URL; ?>public/css/plugins/sweetalert2/sweetalert2.min.css">
    <!-- Custom login styles -->
    <link rel="stylesheet" href="<?= $URL; ?>public/css/modules/login/login.css">
    <script src="<?= $URL; ?>public/js/plugins/sweetalert2/sweetalert2.min.js"></script>
</head>

<body class="hold-transition login-page">
    <div class="login-box">
        <div class="card card-outline card-primary">
            <div class="card-header text-center">
                <h1 class="h3">Base System</h1>
            </div>
            <div class="card-body login-card-body">
                <p class="login-box-msg">You are only one step away from your new password, recover your password now.</p>

                <form action="<?= $URL; ?>controllers/auth/reset_password_process.php" method="post" id="reset-password-form">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken(); ?>">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token); ?>">

                    <div class="form-group">
                        <div class="input-group">
                            <input type="password" name="password" id="password" class="form-control" placeholder="New Password" required minlength="8">
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-lock"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Confirm Password" required minlength="8">
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-lock"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-block" id="btn-reset">
                                <i class="fas fa-lock mr-2" id="btn-icon"></i> Change password
                            </button>
                        </div>
                    </div>
                </form>

                <p class="mt-3 mb-1 text-center">
                    <a href="<?= $URL; ?>views/auth/login.php">Back to Login</a>
                </p>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="<?= $URL; ?>public/js/lib/jquery/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="<?= $URL; ?>public/js/lib/bootstrap/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="<?= $URL; ?>public/js/lib/adminlte/adminlte.min.js"></script>
    <!-- Jquery Validate -->
    <script src="<?= $URL; ?>public/js/plugins/validations/jquery.validate.min.js"></script>
    <script src="<?= $URL; ?>public/js/plugins/validations/additional-methods.min.js"></script>
    <script src="<?= $URL; ?>public/js/core/common-validate.js"></script>
    <!-- Reset Password JS -->
    <script src="<?= $URL; ?>public/js/modules/auth/reset_password.js"></script>

    <?php
    require_once __DIR__ . '/../layouts/messages.php';
    ?>
</body>

</html>