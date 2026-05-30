<?php

require_once 'config/session.php';
require_once 'config/constants.php';


if (empty($_SESSION['user_id'])) {

    header('Location: /projet-stockage-memoires/views/auth/login.php');

    exit;
}

switch ($_SESSION['user_role']) {

    case ROLE_ETUDIANT:

        header('Location: /projet-stockage-memoires/views/etudiant/dashboard.php');

        break;

    case ROLE_PROFESSEUR:

        header('Location: /projet-stockage-memoires/views/professeur/dashboard.php');

        break;

    case ROLE_DIRECTEUR:

        header('Location: /projet-stockage-memoires/views/directeur/dashboard.php');

        break;

    case ROLE_TECHNICIEN:

        header('Location: /projet-stockage-memoires/views/technicien/dashboard.php');

        break;

    default:

        header('Location: /projet-stockage-memoires/views/auth/login.php');
}

exit;