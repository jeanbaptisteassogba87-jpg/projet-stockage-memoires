<?php

require_once __DIR__ . '/config/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Méthode non autorisée.');
}

requireCsrfToken();
logoutUser();
