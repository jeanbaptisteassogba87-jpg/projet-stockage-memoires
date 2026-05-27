<?php
require_once 'config/session.php';
require_once 'config/constants.php';

// Routage simple basé sur le rôle connecté
if (empty($_SESSION['user_id'])) {
    header('Location: /views/auth/login.php');
    exit;
}

// Rediriger vers le dashboard du bon rôle
switch ($_SESSION['user_role']) {
    case ROLE_ETUDIANT:
        header('Location: /views/etudiant/dashboard.php'); break;
    case ROLE_PROFESSEUR:
        header('Location: /views/professeur/dashboard.php'); break;
    case ROLE_DIRECTEUR:
        header('Location: /views/directeur/dashboard.php'); break;
    case ROLE_TECHNICIEN:
        header('Location: /views/technicien/dashboard.php'); break;
    default:
        header('Location: /views/auth/login.php');
}
exit;
