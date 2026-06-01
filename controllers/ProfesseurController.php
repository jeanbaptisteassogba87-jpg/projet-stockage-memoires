<?php

// Rôle : point d'entrée POST pour toutes les actions professeur
//        action=prendre_en_charge → assigner le mémoire au prof
//        action=valider           → valider le mémoire
//        action=rejeter           → rejeter avec remarque
//        action=ajouter_remarque  → remarque sans changer statut


require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../dao/ProfesseurDAO.php';

// Seules les requêtes POST sont acceptées
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /views/professeur/dashboard.php');
    exit;
}

// Doit être connecté avec le rôle professeur
requireRole(ROLE_PROFESSEUR);

$action      = $_POST['action']      ?? '';
$id_memoire  = (int) ($_POST['id_memoire'] ?? 0);
$professeurId = (int) $_SESSION['user_id'];

$dao = new ProfesseurDAO();

// URL de retour par défaut
$retour = '/views/professeur/liste_memoires.php';


// ACTION : prendre en charge un mémoire (en_attente → en_verification)

if ($action === 'prendre_en_charge') {

    if (!$id_memoire) {
        header('Location: ' . $retour . '?error=id_manquant');
        exit;
    }

    $ok = $dao->prendreEnCharge($id_memoire, $professeurId);

    if (!$ok) {
        // rowCount = 0 : le mémoire a déjà été pris par un autre prof
        header('Location: ' . $retour . '?error=deja_pris');
        exit;
    }

    // Rediriger vers la page de vérification de ce mémoire
    header('Location: /views/professeur/verifier_memoire.php?id=' . $id_memoire . '&success=pris_en_charge');
    exit;
}


// ACTION : valider un mémoire (en_verification → valide)

if ($action === 'valider') {

    if (!$id_memoire) {
        header('Location: ' . $retour . '?error=id_manquant');
        exit;
    }

    $ok = $dao->valider($id_memoire, $professeurId);

    if (!$ok) {
        header('Location: /views/professeur/verifier_memoire.php?id=' . $id_memoire . '&error=validation_impossible');
        exit;
    }

    header('Location: /views/professeur/liste_memoires.php?success=valide');
    exit;
}


// ACTION : rejeter un mémoire avec remarque obligatoire

if ($action === 'rejeter') {

    $remarque = trim($_POST['remarque'] ?? '');

    if (!$id_memoire) {
        header('Location: ' . $retour . '?error=id_manquant');
        exit;
    }

    // La remarque est obligatoire pour un rejet
    if (empty($remarque)) {
        header('Location: /views/professeur/verifier_memoire.php?id=' . $id_memoire . '&error=remarque_vide');
        exit;
    }

    $ok = $dao->rejeter($id_memoire, $professeurId, $remarque);

    if (!$ok) {
        header('Location: /views/professeur/verifier_memoire.php?id=' . $id_memoire . '&error=rejet_impossible');
        exit;
    }

    header('Location: /views/professeur/liste_memoires.php?success=rejete');
    exit;
}


// ACTION : ajouter une remarque sans changer le statut

if ($action === 'ajouter_remarque') {

    $remarque = trim($_POST['remarque'] ?? '');

    if (!$id_memoire) {
        header('Location: ' . $retour . '?error=id_manquant');
        exit;
    }

    if (empty($remarque)) {
        header('Location: /views/professeur/verifier_memoire.php?id=' . $id_memoire . '&error=remarque_vide');
        exit;
    }

    $dao->ajouterRemarque($id_memoire, $professeurId, $remarque);

    header('Location: /views/professeur/verifier_memoire.php?id=' . $id_memoire . '&success=remarque_ok');
    exit;
}

// Action inconnue
header('Location: /views/professeur/dashboard.php');
exit;