<?php

// Rôle : point d'entrée GET pour le téléchargement sécurisé d'un PDF
//        Redirige vers serve_pdf.php en vérifiant les droits d'accès
//        action=telecharger → force le téléchargement (Content-Disposition: attachment)
//        action=afficher    → affichage inline dans le navigateur (défaut)


require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../dao/MemoireDAO.php';

// Uniquement les requêtes GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    exit('Méthode non autorisée.');
}

// Doit être connecté
requireAuth();

$id     = (int) ($_GET['id']     ?? 0);
$action = trim($_GET['action']   ?? 'afficher');

if (!$id) {
    http_response_code(400);
    header('Location: /views/commentateur/rechercher.php?error=id_manquant');
    exit;
}

$memoireDAO = new MemoireDAO();
$memoire    = $memoireDAO->trouverParId($id);

if (!$memoire) {
    http_response_code(404);
    header('Location: /views/commentateur/rechercher.php?error=introuvable');
    exit;
}

// ── Contrôle d'accès selon le rôle ──────────────────────────
$role     = $_SESSION['user_role'];
$userId   = (int) $_SESSION['user_id'];
$autorise = false;

switch ($role) {
    case ROLE_ETUDIANT:
        // Un étudiant ne peut accéder qu'à son propre mémoire
        $autorise = ((int) $memoire['etudiant_id'] === $userId);
        break;

    case ROLE_PROFESSEUR:
        // Un professeur accède à ses mémoires assignés ou à ceux en attente
        $autorise = (
            (int) $memoire['professeur_id'] === $userId
            || $memoire['statut'] === STATUT_EN_ATTENTE
            || $memoire['statut'] === STATUT_EN_VERIFICATION
        );
        break;

    case ROLE_DIRECTEUR:
    case ROLE_TECHNICIEN:
        // Accès total pour ces rôles administratifs
        $autorise = true;
        break;

    default:
        // Tout utilisateur connecté peut accéder aux mémoires publiés
        $autorise = ($memoire['statut'] === STATUT_PUBLIE);
        break;
}

if (!$autorise) {
    http_response_code(403);
    header('Location: /views/commentateur/rechercher.php?error=acces_refuse');
    exit;
}

// ── Résoudre le chemin physique du fichier ───────────────────
$nomFichier    = basename($memoire['fichier_pdf']); // basename() empêche la traversée de répertoires
$cheminFichier = PDF_STORAGE_PATH . $nomFichier;

if (!file_exists($cheminFichier) || !is_readable($cheminFichier)) {
    http_response_code(404);
    header('Location: /views/commentateur/rechercher.php?error=fichier_introuvable');
    exit;
}

$tailleFichier = filesize($cheminFichier);

// ── En-têtes HTTP communs ────────────────────────────────────
header('Content-Type: application/pdf');
header('Content-Length: ' . $tailleFichier);
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header("Content-Security-Policy: default-src 'none'");

// ── Mode d'affichage ─────────────────────────────────────────
if ($action === 'telecharger') {
    // Nom de téléchargement lisible : "Titre_du_memoire.pdf"
    $nomTelecharge = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $memoire['titre']);
    $nomTelecharge = substr($nomTelecharge, 0, 80); // Limite la longueur
    header('Content-Disposition: attachment; filename="' . $nomTelecharge . '.pdf"');
} else {
    // Affichage inline — le navigateur ouvre son lecteur PDF intégré
    header('Content-Disposition: inline; filename="document.pdf"');
}

// ── Vider le buffer de sortie avant d'envoyer le fichier ─────
if (ob_get_level()) {
    ob_end_clean();
}

// ── Envoyer le fichier ───────────────────────────────────────
readfile($cheminFichier);
exit;