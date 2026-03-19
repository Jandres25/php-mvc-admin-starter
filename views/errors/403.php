<?php
http_response_code(403);

$app_url = $GLOBALS['URL'] ?? null;

if (!$app_url) {
    $env_file = __DIR__ . '/../../.env';
    $app_url = '/';
    if (file_exists($env_file)) {
        foreach (file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            if (strpos($line, '=') !== false) {
                [$name, $value] = explode('=', $line, 2);
                if (trim($name) === 'APP_URL') {
                    $app_url = rtrim(trim($value, " \t\n\r\0\x0B\"'"), '/') . '/';
                    break;
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 - Access Denied</title>
    <link rel="stylesheet" href="<?= $app_url ?>public/css/lib/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="<?= $app_url ?>public/css/lib/adminlte/adminlte.min.css">
    <link rel="stylesheet" href="<?= $app_url ?>public/css/lib/fontawesome/all.min.css">
    <link rel="stylesheet" href="<?= $app_url ?>public/css/core/webfonts.css">
    <link rel="icon" type="image/png" href="<?= $app_url ?>public/img/e-commerce_logo.png">
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
        <a href="<?= $app_url ?>" class="btn btn-primary">
            <i class="fas fa-home mr-1"></i> Back to Home
        </a>
    </div>
</body>

</html>
