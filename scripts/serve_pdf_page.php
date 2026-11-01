<?php

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../dao/MemoireDAO.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 0;
if (!$id) {
    http_response_code(400);
    exit('Identifiant manquant.');
}

$memoire = (new MemoireDAO())->trouverParId($id);
if (!$memoire) {
    http_response_code(404);
    exit('Mémoire introuvable.');
}

$role = $_SESSION['user_role'] ?? null;
$userId = (int) ($_SESSION['user_id'] ?? 0);
$estAuteur = (int) $memoire['etudiant_id'] === $userId
    || (int) ($memoire['etudiant2_id'] ?? 0) === $userId;
$autorise = match ($role) {
    ROLE_ETUDIANT => $estAuteur || $memoire['statut'] === STATUT_PUBLIE,
    ROLE_PROFESSEUR => (int) $memoire['professeur_id'] === $userId
        || ($memoire['statut'] === STATUT_EN_ATTENTE && empty($memoire['professeur_id'])),
    ROLE_DIRECTEUR, ROLE_TECHNICIEN => true,
    default => $memoire['statut'] === STATUT_PUBLIE,
};

if (!$autorise) {
    http_response_code(403);
    exit('Accès refusé.');
}

if ($estAuteur) {
    http_response_code(400);
    exit('L’auteur consulte son fichier PDF original.');
}

$pdfPath = PDF_STORAGE_PATH . basename($memoire['fichier_pdf']);
if (!is_readable($pdfPath) || !function_exists('exec')) {
    http_response_code(503);
    exit('Le lecteur protégé est indisponible.');
}

$infoOutput = [];
$infoStatus = 0;
exec('pdfinfo ' . escapeshellarg($pdfPath) . ' 2>/dev/null', $infoOutput, $infoStatus);
$pageCount = 0;
foreach ($infoOutput as $line) {
    if (preg_match('/^Pages:\s+(\d+)/', $line, $match)) {
        $pageCount = (int) $match[1];
        break;
    }
}

if ($infoStatus !== 0 || $pageCount < 1) {
    http_response_code(422);
    exit('Impossible de lire ce PDF.');
}

header('Cache-Control: private, no-store, max-age=0');
header('X-Content-Type-Options: nosniff');

if (($_GET['format'] ?? '') === 'info') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['pages' => $pageCount], JSON_THROW_ON_ERROR);
    exit;
}

if ($page < 1 || $page > $pageCount) {
    http_response_code(404);
    exit('Page introuvable.');
}

$tempBase = tempnam(sys_get_temp_dir(), 'memoire_page_');
if ($tempBase === false) {
    http_response_code(500);
    exit('Rendu temporairement indisponible.');
}
unlink($tempBase);
$outputPath = $tempBase . '.png';
$renderStatus = 0;
exec(
    'pdftoppm -f ' . $page . ' -l ' . $page . ' -singlefile -scale-to 1600 -png '
        . escapeshellarg($pdfPath) . ' ' . escapeshellarg($tempBase) . ' 2>/dev/null',
    $renderOutput,
    $renderStatus
);

if ($renderStatus !== 0 || !is_readable($outputPath)) {
    @unlink($outputPath);
    http_response_code(422);
    exit('Impossible de rendre cette page.');
}

header('Content-Type: image/png');
header('Content-Length: ' . filesize($outputPath));
readfile($outputPath);
unlink($outputPath);
