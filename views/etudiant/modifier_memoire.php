<?php
$pageTitle = 'Corriger un memoire - Etudiant';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../dao/MemoireDAO.php';
requireRole(ROLE_ETUDIANT);

if ($_SESSION['doit_changer_mdp']) {
    header('Location: /views/auth/change_password.php');
    exit;
}

$memoireDAO = new MemoireDAO();
$memoiresRejetes = $memoireDAO->listerRejetesParEtudiant((int)$_SESSION['user_id']);
$memoireSelectionne = null;
$memoireId = (int)($_GET['id'] ?? ($_SESSION['correction_form_data']['memoire_id'] ?? 0));

if ($memoireId > 0) {
    $memoireSelectionne = $memoireDAO->trouverParIdEtEtudiant($memoireId, (int)$_SESSION['user_id']);
    if (!$memoireSelectionne || $memoireSelectionne['statut'] !== STATUT_REJETE) {
        $memoireSelectionne = null;
    }
}

if (!$memoireSelectionne && !empty($memoiresRejetes)) {
    $memoireSelectionne = $memoiresRejetes[0];
}

$erreurs = $_SESSION['correction_errors'] ?? [];
$formData = $_SESSION['correction_form_data'] ?? [];
unset($_SESSION['correction_errors'], $_SESSION['correction_form_data']);
?>

<?php require_once __DIR__ . '/../layout/header.php'; ?>
<?php require_once __DIR__ . '/../layout/navbar.php'; ?>

<div class="container-fluid">
  <div class="row">
    <div class="col-md-2 px-0 sidebar">
      <nav class="nav flex-column pt-3">
        <a class="nav-link" href="/views/etudiant/dashboard.php">
          <i class="bi bi-speedometer2"></i> Tableau de bord
        </a>
        <a class="nav-link" href="/views/etudiant/deposer_memoire.php">
          <i class="bi bi-upload"></i> Deposer un memoire
        </a>
        <a class="nav-link active" href="/views/etudiant/modifier_memoire.php">
          <i class="bi bi-pencil"></i> Corriger mon memoire
        </a>
        <a class="nav-link" href="/views/etudiant/voir_remarques.php">
          <i class="bi bi-chat-text"></i> Mes remarques
        </a>
        <a class="nav-link" href="/views/commentateur/rechercher.php">
          <i class="bi bi-search"></i> Consulter memoires
        </a>
      </nav>
    </div>

    <div class="col-md-10 p-4">
      <h2 class="section-title mb-4">Corriger un memoire rejete</h2>

      <?php if (!empty($erreurs)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <strong><i class="bi bi-exclamation-circle"></i> Erreurs detectees:</strong>
          <ul class="mb-0 mt-2">
            <?php foreach ($erreurs as $erreur): ?>
              <li><?= htmlspecialchars($erreur) ?></li>
            <?php endforeach; ?>
          </ul>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
      <?php endif; ?>

      <?php if (empty($memoiresRejetes)): ?>
        <div class="alert alert-info" role="alert">
          <i class="bi bi-info-circle"></i>
          Aucun memoire rejete a corriger pour le moment.
        </div>
      <?php else: ?>
        <div class="row g-4">
          <div class="col-lg-4">
            <div class="card">
              <div class="card-header-uatm">
                <i class="bi bi-list-ul me-2"></i>Memoires rejetes
              </div>
              <div class="list-group list-group-flush">
                <?php foreach ($memoiresRejetes as $memoire): ?>
                  <a
                    class="list-group-item list-group-item-action <?= $memoireSelectionne && (int)$memoireSelectionne['id_memoire'] === (int)$memoire['id_memoire'] ? 'active' : '' ?>"
                    href="/views/etudiant/modifier_memoire.php?id=<?= (int)$memoire['id_memoire'] ?>"
                  >
                    <div class="fw-semibold"><?= htmlspecialchars($memoire['titre']) ?></div>
                    <small><?= htmlspecialchars(ucfirst($memoire['type_diplome'])) ?> - <?= htmlspecialchars($memoire['annee_soutenance']) ?></small>
                  </a>
                <?php endforeach; ?>
              </div>
            </div>
          </div>

          <div class="col-lg-8">
            <?php if ($memoireSelectionne): ?>
              <div class="card p-4">
                <?php if (!empty($memoireSelectionne['remarques'])): ?>
                  <div class="alert alert-warning">
                    <strong><i class="bi bi-chat-left-text"></i> Remarques du professeur</strong>
                    <div class="mt-2"><?= nl2br(htmlspecialchars($memoireSelectionne['remarques'])) ?></div>
                  </div>
                <?php endif; ?>

                <form method="POST" action="/controllers/EtudiantController.php" enctype="multipart/form-data" novalidate>
                  <input type="hidden" name="action" value="corriger_memoire">
                  <input type="hidden" name="memoire_id" value="<?= (int)$memoireSelectionne['id_memoire'] ?>">

                  <div class="mb-3">
                    <label for="titre" class="form-label">Titre du memoire <span class="text-danger">*</span></label>
                    <input
                      type="text"
                      class="form-control"
                      id="titre"
                      name="titre"
                      value="<?= htmlspecialchars($formData['titre'] ?? $memoireSelectionne['titre']) ?>"
                      required
                      minlength="5"
                    >
                  </div>

                  <div class="mb-3">
                    <label for="theme" class="form-label">Theme <span class="text-danger">*</span></label>
                    <input
                      type="text"
                      class="form-control"
                      id="theme"
                      name="theme"
                      value="<?= htmlspecialchars($formData['theme'] ?? $memoireSelectionne['theme']) ?>"
                      required
                      minlength="5"
                    >
                  </div>

                  <div class="mb-3">
                    <label class="form-label">Type de diplome</label>
                    <input
                      type="text"
                      class="form-control"
                      value="<?= htmlspecialchars(ucfirst($memoireSelectionne['type_diplome'])) ?>"
                      disabled
                    >
                  </div>

                  <div class="mb-3">
                    <label for="annee_soutenance" class="form-label">Annee de soutenance <span class="text-danger">*</span></label>
                    <input
                      type="number"
                      class="form-control"
                      id="annee_soutenance"
                      name="annee_soutenance"
                      value="<?= htmlspecialchars($formData['annee_soutenance'] ?? $memoireSelectionne['annee_soutenance']) ?>"
                      min="2000"
                      max="<?= date('Y') + 1 ?>"
                      required
                    >
                  </div>

                  <div class="mb-4">
                    <label for="fichier_pdf" class="form-label">Version corrigee en PDF <span class="text-danger">*</span></label>
                    <input
                      type="file"
                      class="form-control"
                      id="fichier_pdf"
                      name="fichier_pdf"
                      accept=".pdf"
                      required
                    >
                    <small class="form-text text-muted">PDF uniquement | Taille max: <?= (MAX_PDF_SIZE / (1024 * 1024)) ?> Mo</small>
                  </div>

                  <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                      <i class="bi bi-upload"></i> Envoyer la version corrigee
                    </button>
                    <a
                      class="btn btn-outline-secondary"
                      href="/scripts/serve_pdf.php?id=<?= (int)$memoireSelectionne['id_memoire'] ?>"
                      target="_blank"
                      rel="noopener"
                    >
                      <i class="bi bi-eye"></i> Voir l'ancienne version
                    </a>
                  </div>
                </form>
              </div>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
