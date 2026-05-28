<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Base System | Accept Invitation</title>

    <!-- Dark mode detection (inline — evita FOUC) -->
    <script>
        (function() {
            try {
                const saved = localStorage.getItem('theme');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (saved === 'dark' || (!saved && prefersDark)) {
                    document.documentElement.classList.add('dark-mode');
                }
            } catch (e) {}
        })();
    </script>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="<?= URL ?>css/lib/fontawesome/all.min.css">
    <link rel="stylesheet" href="<?= URL ?>css/lib/adminlte/adminlte.min.css">
    <link rel="stylesheet" href="<?= URL ?>css/core/webfonts.css">
    <link rel="icon" type="image/png" href="<?= URL ?>img/e-commerce_logo.png">
    <link rel="stylesheet" href="<?= URL ?>css/core/dark-mode.css">
    <link rel="stylesheet" href="<?= URL ?>css/modules/login/login.css">
    <link rel="stylesheet" href="<?= URL ?>css/modules/login/login-dark.css">
    <link rel="stylesheet" href="<?= URL ?>css/plugins/sweetalert2/sweetalert2.min.css">
    <script src="<?= URL ?>js/plugins/sweetalert2/sweetalert2.min.js"></script>
    <script src="<?= URL ?>js/core/sweetalert-utils.js"></script>
</head>

<body class="hold-transition login-page">
    <!-- Dark mode toggle -->
    <a href="#" id="theme-toggle" class="auth-theme-toggle" role="button" title="Toggle dark mode">
        <i class="fas fa-moon"></i>
    </a>
    <div class="login-box">
        <div class="card card-outline card-success">
            <div class="card-header text-center">
                <h1 class="h3">Base System</h1>
            </div>
            <div class="card-body login-card-body">
                <p class="login-box-msg">
                    <i class="fas fa-envelope-open-text mr-1"></i>
                    Welcome! Set your password to activate your account.
                </p>

                <form action="<?= URL ?>accept-invitation" method="post" id="accept-invitation-form">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                    <div class="form-group">
                        <div class="input-group">
                            <input type="password" name="password" id="password" class="form-control"
                                placeholder="New Password" required minlength="8" autocomplete="new-password">
                            <div class="input-group-append">
                                <div class="input-group-text"><span class="fas fa-lock"></span></div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-group">
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control"
                                placeholder="Confirm Password" required minlength="8" autocomplete="new-password">
                            <div class="input-group-append">
                                <div class="input-group-text"><span class="fas fa-lock"></span></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-success btn-block" id="btn-accept">
                                <i class="fas fa-check mr-2"></i> Activate Account
                            </button>
                        </div>
                    </div>
                </form>

                <p class="mt-3 mb-1 text-center">
                    <a href="<?= URL ?>login">Back to Login</a>
                </p>
            </div>
        </div>
    </div>

    <script src="<?= URL ?>js/lib/jquery/jquery.min.js"></script>
    <script src="<?= URL ?>js/lib/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="<?= URL ?>js/lib/adminlte/adminlte.min.js"></script>
    <script src="<?= URL ?>js/plugins/validations/jquery.validate.min.js"></script>
    <script src="<?= URL ?>js/plugins/validations/additional-methods.min.js"></script>
    <script src="<?= URL ?>js/core/common-validate.js"></script>
    <script src="<?= URL ?>js/modules/auth/accept-invitation.js"></script>
    <script src="<?= URL ?>js/modules/profile/theme-toggle.js"></script>

    <?php require_once __DIR__ . '/../layouts/messages.php'; ?>
</body>

</html>
