<?php


require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../dao/MemoireDAO.php';

$id = (int) ($_GET['id'] ?? 0);
if (!$id) {
    http_response_code(400);
    exit('Identifiant manquant.');
}

$memoireDAO = new MemoireDAO();
$memoire    = $memoireDAO->trouverParId($id);

if (!$memoire) {
    http_response_code(404);
    exit('Mémoire introuvable.');
}

// ── Contrôle d'accès par rôle ────────────────────────────────
$role     = $_SESSION['user_role'] ?? null;
$userId   = (int) ($_SESSION['user_id'] ?? 0);
$telecharger = ($_GET['action'] ?? '') === 'telecharger';
$estAuteur = (int) $memoire['etudiant_id'] === $userId
    || (int) ($memoire['etudiant2_id'] ?? 0) === $userId;
$estProprietaireAction = $role === ROLE_ETUDIANT && $estAuteur;
$autorise = false;

// Le PDF original ne peut être servi qu'à l'auteur ou au binôme.
if ($telecharger && !$estProprietaireAction) {
    http_response_code(403);
    exit('Téléchargement réservé à l’auteur du mémoire.');
}

switch ($role) {
    case ROLE_ETUDIANT:
        // Chaque étudiant peut accéder à ses propres mémoires
        // Tout étudiant peut accéder aux mémoires publiés
        $autorise = (
            (int) $memoire['etudiant_id'] === $userId
            || (int) ($memoire['etudiant2_id'] ?? 0) === $userId
            || $memoire['statut'] === STATUT_PUBLIE
        );
        break;
    case ROLE_PROFESSEUR:
        $autorise = (
            (int) $memoire['professeur_id'] === $userId
            || ($memoire['statut'] === STATUT_EN_ATTENTE
                && empty($memoire['professeur_id']))
        );
        break;
    case ROLE_DIRECTEUR:
    case ROLE_TECHNICIEN:
        $autorise = true;
        break;
    default:
        $autorise = ($memoire['statut'] === STATUT_PUBLIE);
        break;
}

if (!$autorise) {
    http_response_code(403);
    exit('Accès refusé.');
}

// ── Chemin du fichier ────────────────────────────────────────
$cheminFichier = PDF_STORAGE_PATH . basename($memoire['fichier_pdf']);

if (!file_exists($cheminFichier) || !is_readable($cheminFichier)) {
    http_response_code(404);
    exit('Fichier introuvable sur le serveur.');
}

// Seul l'auteur récupère le fichier PDF original. Les autres rôles utilisent
// serve_pdf_page.php, qui ne transmet que des images des pages.
if (!$estAuteur) {
    http_response_code(403);
    exit('Le fichier original est réservé à l’auteur du mémoire.');
}

// ── Envoi du fichier original à l'auteur ─────────────────────
header('Content-Type: application/pdf');
header($telecharger
    ? 'Content-Disposition: attachment; filename="memoire.pdf"'
    : 'Content-Disposition: inline; filename="memoire.pdf"');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header("Content-Security-Policy: default-src 'none'");
header('Content-Length: ' . filesize($cheminFichier));

if (ob_get_level()) {
    ob_end_clean();
}

readfile($cheminFichier);
exit;
