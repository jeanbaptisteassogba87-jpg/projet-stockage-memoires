<?php

// Rôle : point d'entrée POST pour les actions du directeur
//        action=publier    → passe le mémoire en statut publie
//        action=depublier  → passe le mémoire en statut non_public


require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../dao/DirecteurDAO.php';

// Uniquement les requêtes POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /views/directeur/dashboard.php');
    exit;
}

// Doit être connecté avec le rôle directeur
requireRole(ROLE_DIRECTEUR);

$action     = $_POST['action']     ?? '';
$id_memoire = (int) ($_POST['id_memoire'] ?? 0);
$centreId   = (int) $_SESSION['centre_id'];

$dao    = new DirecteurDAO();
$retour = '/views/directeur/gerer_visibilite.php';

if (!$id_memoire) {
    header('Location: ' . $retour . '?error=id_manquant');
    exit;
}


// ACTION : publier un mémoire (valide/non_public → publie)

if ($action === 'publier') {

    $ok = $dao->publier($id_memoire, $centreId);

    if (!$ok) {
        header('Location: ' . $retour . '?error=publication_impossible');
        exit;
    }

    header('Location: ' . $retour . '?success=publie');
    exit;
}


// ACTION : dépublier un mémoire (publie → non_public)

if ($action === 'depublier') {

    $ok = $dao->depublier($id_memoire, $centreId);

    if (!$ok) {
        header('Location: ' . $retour . '?error=depublication_impossible');
        exit;
    }

    header('Location: ' . $retour . '?success=depublie');
    exit;
}

// Action inconnue
header('Location: /views/directeur/dashboard.php');
exit;