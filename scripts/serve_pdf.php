<?php
// Rôle : servir les fichiers PDF de façon sécurisée
//        Les PDFs sont stockés dans uploads/ hors webroot
//        (Deny from all dans uploads/.htaccess)
//        Ce script vérifie les droits avant de streamer le fichier
//
// Droits d'accès :
//   - étudiant  : seulement ses propres mémoires
//   - professeur: les mémoires qui lui sont assignés
//   - directeur : tous les mémoires
//   - technicien: tous les mémoires
//   - publie    : accessible à tous les connectés (commentateurs)


require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../dao/MemoireDAO.php';

// Doit être connecté
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

// ── Vérification des droits selon le rôle ───────────────────
$role      = $_SESSION['user_role'];
$userId    = (int) $_SESSION['user_id'];
$autorise  = false;

switch ($role) {

    case ROLE_ETUDIANT:
        // L'étudiant ne peut voir que ses propres mémoires
        $autorise = ((int) $memoire['etudiant_id'] === $userId);
        break;

    case ROLE_PROFESSEUR:
        // Le professeur peut voir les mémoires qui lui sont assignés
        // et les mémoires en attente (pour décider de les prendre)
        $autorise = (
            (int) $memoire['professeur_id'] === $userId
            || $memoire['statut'] === STATUT_EN_ATTENTE
        );
        break;

    case ROLE_DIRECTEUR:
    case ROLE_TECHNICIEN:
        // Accès complet
        $autorise = true;
        break;

    default:
        // Commentateur : seulement les mémoires publiés
        $autorise = ($memoire['statut'] === STATUT_PUBLIE);
        break;
}

if (!$autorise) {
    http_response_code(403);
    exit('Accès refusé.');
}

// ── Construire le chemin absolu du fichier ───────────────────
// PDF_STORAGE_PATH est défini dans config/constants.php
// ex: /var/www/html/uploads/memoires/
$cheminFichier = PDF_STORAGE_PATH . basename($memoire['fichier_pdf']);
// basename() empêche toute traversée de répertoire (ex: ../../etc/passwd)

if (!file_exists($cheminFichier) || !is_readable($cheminFichier)) {
    http_response_code(404);
    exit('Fichier PDF introuvable sur le serveur.');
}

// ── Streamer le fichier avec les bons headers ────────────────
$tailleFichier = filesize($cheminFichier);

header('Content-Type: application/pdf');
header('Content-Length: ' . $tailleFichier);

// inline = affichage dans le navigateur (pas de téléchargement forcé)
header('Content-Disposition: inline; filename="' . basename($memoire['fichier_pdf']) . '"');

// Cache : 1 heure côté navigateur (le PDF ne change pas souvent)
header('Cache-Control: private, max-age=3600');
header('X-Content-Type-Options: nosniff');

// Vider le buffer de sortie avant de streamer
if (ob_get_level()) {
    ob_end_clean();
}

readfile($cheminFichier);
exit;