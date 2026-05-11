<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 - Access Denied</title>
    <link rel="stylesheet" href="<?= defined('URL') ? URL : '/' ?>css/lib/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="<?= defined('URL') ? URL : '/' ?>css/lib/adminlte/adminlte.min.css">
    <link rel="stylesheet" href="<?= defined('URL') ? URL : '/' ?>css/lib/fontawesome/all.min.css">
    <link rel="stylesheet" href="<?= defined('URL') ? URL : '/' ?>css/core/webfonts.css">
    <link rel="icon" type="image/png" href="<?= defined('URL') ? URL : '/' ?>img/e-commerce_logo.png">
    <style>
        body {
            background-color: #f4f6f9;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
    </style>
</head>

<body>
    <div class="text-center">
        <h1 class="display-1 font-weight-bold text-danger">403</h1>
        <h3 class="mb-3">Access Denied</h3>
        <p class="text-muted mb-4">You do not have permission to access this section.</p>
        <a href="<?= defined('URL') ? URL : '/' ?>" class="btn btn-primary">
            <i class="fas fa-home mr-1"></i> Back to Home
        </a>
    </div>
</body>

</html>
