<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Base System | Sign In</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="<?= URL ?>css/lib/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="<?= URL ?>css/lib/fontawesome/all.min.css">
    <link rel="stylesheet" href="<?= URL ?>css/lib/adminlte/adminlte.min.css">
    <link rel="stylesheet" href="<?= URL ?>css/lib/bootstrap/icheck-bootstrap.min.css">
    <link rel="stylesheet" href="<?= URL ?>css/core/webfonts.css">
    <link rel="icon" type="image/png" href="<?= URL ?>img/e-commerce_logo.png">
    <link rel="stylesheet" href="<?= URL ?>css/modules/login/login.css">
    <link rel="stylesheet" href="<?= URL ?>css/plugins/sweetalert2/sweetalert2.min.css">
    <script src="<?= URL ?>js/plugins/sweetalert2/sweetalert2.min.js"></script>
</head>

<body class="hold-transition login-page">
    <div class="login-box">
        <div class="card card-outline card-primary">
            <div class="card-header text-center">
                <h1 class="h3">Base System</h1>
            </div>
            <div class="card-body login-card-body">
                <p class="login-box-msg">Enter your credentials to sign in</p>

                <form action="<?= URL ?>login" method="post" id="login-form">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

                    <div class="form-group">
                        <div class="input-group">
                            <input type="text" name="identifier" class="form-control"
                                placeholder="Email or document number" autocomplete="username">
                            <div class="input-group-append">
                                <div class="input-group-text"><span class="fas fa-user"></span></div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-group">
                            <input type="password" name="password" id="password-field" class="form-control"
                                placeholder="Password" autocomplete="current-password">
                            <div class="input-group-append">
                                <div class="input-group-text password-toggle" title="Show/Hide password">
                                    <span class="fas fa-eye-slash toggle-password" id="toggle-password"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-12">
                            <div class="icheck-primary">
                                <input type="checkbox" id="remember" name="remember" value="1">
                                <label for="remember">Remember me</label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-block" id="btn-login">
                                <i class="fas fa-sign-in-alt mr-2" id="btn-icon"></i> Sign In
                            </button>
                        </div>
                    </div>
                </form>

                <p class="mb-1 mt-3 text-center">
                    <a href="<?= URL ?>forgot-password">I forgot my password</a>
                </p>
            </div>
        </div>

        <div class="login-footer text-center mt-3">
            <p class="text-muted">&copy; <?= date('Y') ?> Base System. All rights reserved.</p>
        </div>
    </div>

    <script src="<?= URL ?>js/lib/jquery/jquery.min.js"></script>
    <script src="<?= URL ?>js/lib/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="<?= URL ?>js/lib/adminlte/adminlte.min.js"></script>
    <script src="<?= URL ?>js/plugins/validations/jquery.validate.min.js"></script>
    <script src="<?= URL ?>js/plugins/validations/additional-methods.min.js"></script>
    <script src="<?= URL ?>js/core/common-validate.js"></script>
    <script src="<?= URL ?>js/modules/auth/login.js"></script>

    <?php require_once __DIR__ . '/../layouts/messages.php'; ?>
</body>

</html>