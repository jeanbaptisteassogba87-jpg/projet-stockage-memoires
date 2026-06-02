<?php


require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../dao/MemoireDAO.php';

requireAuth();

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
$role     = $_SESSION['user_role'];
$userId   = (int) $_SESSION['user_id'];
$autorise = false;

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
            || $memoire['statut'] === STATUT_EN_ATTENTE
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

// ── Headers sécurisés anti-téléchargement ───────────────────
header('Content-Type: application/pdf');
// inline + nom générique = pas d'invitation à sauvegarder
header('Content-Disposition: inline; filename="document.pdf"');
// no-store = jamais écrit sur disque par le navigateur
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
