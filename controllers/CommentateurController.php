<?php

// Rôle : point d'entrée POST pour les actions commentateur
//        action=ajouter_commentaire  → ajouter un commentaire
//        action=supprimer_commentaire→ supprimer son propre commentaire
//        action=toggler_like         → ajouter ou retirer un like


require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../dao/CommentaireDAO.php';
require_once __DIR__ . '/../dao/LikeDAO.php';
require_once __DIR__ . '/../dao/MemoireDAO.php';

// Seules les requêtes POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /views/commentateur/rechercher.php');
    exit;
}

// Doit être connecté (tous les rôles peuvent commenter)
requireAuth();

$action      = $_POST['action']      ?? '';
$userId      = (int) $_SESSION['user_id'];


// ACTION : ajouter un commentaire

if ($action === 'ajouter_commentaire') {

    $memoireId = (int) ($_POST['memoire_id'] ?? 0);
    $contenu   = trim($_POST['contenu']      ?? '');

    if (!$memoireId || empty($contenu)) {
        header('Location: /views/commentateur/consulter_memoire.php?id=' . $memoireId . '&error=commentaire_vide');
        exit;
    }

    // Vérifier que le mémoire est bien publié (seuls les publiés sont consultables)
    $memoireDAO = new MemoireDAO();
    $memoire    = $memoireDAO->trouverParId($memoireId);

    if (!$memoire || $memoire['statut'] !== STATUT_PUBLIE) {
        header('Location: /views/commentateur/rechercher.php?error=acces_refuse');
        exit;
    }

    // Longueur max du commentaire : 2000 caractères
    if (mb_strlen($contenu) > 2000) {
        header('Location: /views/commentateur/consulter_memoire.php?id=' . $memoireId . '&error=trop_long');
        exit;
    }

    $dao = new CommentaireDAO();
    $dao->ajouter($memoireId, $userId, $contenu);

    header('Location: /views/commentateur/consulter_memoire.php?id=' . $memoireId . '&success=commentaire_ok#commentaires');
    exit;
}


// ACTION : supprimer un commentaire

if ($action === 'supprimer_commentaire') {

    $idCommentaire = (int) ($_POST['id_commentaire'] ?? 0);
    $memoireId     = (int) ($_POST['memoire_id']     ?? 0);

    if (!$idCommentaire) {
        header('Location: /views/commentateur/consulter_memoire.php?id=' . $memoireId . '&error=id_manquant');
        exit;
    }

    $dao = new CommentaireDAO();
    // La méthode supprimer() vérifie que l'auteur = userId
    $dao->supprimer($idCommentaire, $userId);

    header('Location: /views/commentateur/consulter_memoire.php?id=' . $memoireId . '#commentaires');
    exit;
}


// ACTION : toggler un like (ajouter ou retirer)
if ($action === 'toggler_like') {

    $memoireId = (int) ($_POST['memoire_id'] ?? 0);

    if (!$memoireId) {
        header('Location: /views/commentateur/rechercher.php');
        exit;
    }

    // Vérifier accès au mémoire
    $memoireDAO = new MemoireDAO();
    $memoire    = $memoireDAO->trouverParId($memoireId);

    if (!$memoire || $memoire['statut'] !== STATUT_PUBLIE) {
        header('Location: /views/commentateur/rechercher.php?error=acces_refuse');
        exit;
    }

    $likeDAO = new LikeDAO();

    if ($likeDAO->aDejaLike($memoireId, $userId)) {
        $likeDAO->retirer($memoireId, $userId);
    } else {
        $likeDAO->ajouter($memoireId, $userId);
    }

    // Retour vers la page du mémoire
    $retour = $_POST['retour'] ?? '/views/commentateur/consulter_memoire.php?id=' . $memoireId;
    header('Location: ' . $retour);
    exit;
}

// Action inconnue
header('Location: /views/commentateur/rechercher.php');
exit;