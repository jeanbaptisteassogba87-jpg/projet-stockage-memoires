<?php

if (session_status() === PHP_SESSION_NONE) {
    $forwardedProtocol = strtolower(trim(explode(',', $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')[0]));
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || $forwardedProtocol === 'https';

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function csrfToken(): string
{
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf_token'];
}

function csrfField(): string
{
    return '<input type="hidden" name="_csrf" value="'
        . htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8')
        . '">';
}

function requireCsrfToken(): void
{
    $sent = $_POST['_csrf'] ?? '';
    $valid = is_string($sent)
        && isset($_SESSION['_csrf_token'])
        && hash_equals($_SESSION['_csrf_token'], $sent);

    if (!$valid) {
        http_response_code(403);
        exit('La session du formulaire a expiré. Rechargez la page et réessayez.');
    }
}


function requireAuth(): void {

    if (empty($_SESSION['user_id'])) {

        header('Location: /views/auth/login.php');

        exit;
    }

    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $allowedChangePasswordScripts = [
        '/views/auth/change_password.php',
        '/controllers/AuthController.php',
    ];
    if (!empty($_SESSION['doit_changer_mdp'])
        && !in_array($script, $allowedChangePasswordScripts, true)) {
        header('Location: /views/auth/change_password.php');
        exit;
    }
}


function requireRole(string $role): void {

    requireAuth();

    if ($_SESSION['user_role'] !== $role) {

        header('Location: /index.php?error=acces_refuse');

        exit;
    }
}


function requireRoles(array $roles): void {

    requireAuth();

    if (!in_array($_SESSION['user_role'], $roles)) {

        header('Location: /index.php?error=acces_refuse');

        exit;
    }
}


function loginUser(array $user): void {

    session_regenerate_id(true);
    $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));

    $_SESSION['user_id'] = $user['id_utilisateur'];

    $_SESSION['user_role'] = $user['role'];

    $_SESSION['user_nom'] = $user['nom'];

    $_SESSION['centre_id'] = $user['centre_id'];

    $_SESSION['doit_changer_mdp'] = $user['doit_changer_mdp'];
}


 
function logoutUser(): void {

    session_unset();

    session_destroy();

    header('Location: /views/auth/login.php');

    exit;
}
