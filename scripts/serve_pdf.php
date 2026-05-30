<?php
/**
 * Script de service des fichiers PDF stockés
 * Sécurise l'accès aux fichiers en dehors du webroot
 * 
 * Usage: serve_pdf.php?id=123
 */

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../dao/MemoireDAO.php';

requireAuth();

// Récupérer l'ID du mémoire
$memoireId = (int)($_GET['id'] ?? 0);

if ($memoireId <= 0) {
    http_response_code(400);
    die('ID de mémoire invalide');
}

// Récupérer les infos du mémoire
$dao = new MemoireDAO();
$memoire = $dao->trouverParId($memoireId);

if (!$memoire) {
    http_response_code(404);
    die('Mémoire introuvable');
}

// Vérifier les permissions
// - L'étudiant peut voir son propre mémoire
// - Les utilisateurs authentifiés peuvent voir les mémoires validés/publiés
// - Les professeurs assignés peuvent voir tous les mémoires qu'ils vérifient

$isOwner = ($memoire['etudiant_id'] == $_SESSION['user_id']);
$isAssignedProfessor = (
    $_SESSION['user_role'] === ROLE_PROFESSEUR 
    && $memoire['professeur_id'] == $_SESSION['user_id']
);
$isPublic = in_array($memoire['statut'], [STATUT_VALIDE, STATUT_PUBLIE]);
$isDirector = $_SESSION['user_role'] === ROLE_DIRECTEUR;
$isTechnician = $_SESSION['user_role'] === ROLE_TECHNICIEN;

// Refuser l'accès si non autorisé
if (!($isOwner || $isAssignedProfessor || $isPublic || $isDirector || $isTechnician)) {
    http_response_code(403);
    die('Accès refusé');
}

// Chemin du fichier
$filePath = PDF_STORAGE_PATH . $memoire['fichier_pdf'];

// Vérifier que le fichier existe et est dans le bon répertoire
if (!file_exists($filePath) || !is_file($filePath)) {
    http_response_code(404);
    die('Fichier introuvable');
}

// Vérifier que le chemin est bien dans le répertoire autorisé (prévention de traversée)
$realPath = realpath($filePath);
$allowedDir = realpath(PDF_STORAGE_PATH);

if ($realPath === false || strpos($realPath, $allowedDir) !== 0) {
    http_response_code(403);
    die('Accès refusé');
}

// Servir le fichier
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . basename($memoire['fichier_pdf']) . '"');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: public, max-age=3600');

// Lire et envoyer le fichier
readfile($filePath);
exit;
