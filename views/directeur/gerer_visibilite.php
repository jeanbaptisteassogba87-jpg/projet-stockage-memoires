<?php

// Rôle : liste tous les mémoires validés/publiés/non_public
//        Le directeur peut basculer la visibilité de chacun
//        Filtrable par statut, type, filière


$pageTitle = 'Gérer la visibilité — UATM GASA Formation';

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../dao/DirecteurDAO.php';

requireRole(ROLE_DIRECTEUR);

$dao      = new DirecteurDAO();
$centreId = (int) $_SESSION['centre_id'];

$memoires = $dao->listerMemoresGerables($centreId);

// Filtre GET (facultatif)
$filtreStatut = $_GET['statut'] ?? '';
$filtreType   = $_GET['type']   ?? '';

if ($filtreStatut) {
    $memoires = array_filter($memoires, fn($m) => $m['statut'] === $filtreStatut);
}
if ($filtreType) {
    $memoires = array_filter($memoires, fn($m) => $m['type_diplome'] === $filtreType);
}

// Messages feedback
$successMessages = [
    'publie'                  => 'Mémoire publié en ligne avec succès.',
    'depublie'                => 'Mémoire retiré de la plateforme publique.',
];
$errorMessages = [
    'publication_impossible'  => 'Impossible de publier ce mémoire (statut incorrect).',
    'depublication_impossible' => 'Impossible de dépublier ce mémoire.',
    'id_manquant'             => 'Identifiant manquant.',
];

$successMsg = !empty($_GET['success']) ? ($successMessages[$_GET['success']] ?? '') : '';
$errorMsg   = !empty($_GET['error'])   ? ($errorMessages[$_GET['error']]   ?? '') : '';

$labelStatut = [
    STATUT_VALIDE      => 'Validé',
    STATUT_PUBLIE      => 'Publié',
    STATUT_NON_PUBLIC  => 'Non public',
];
?>
<?php require_once __DIR__ . '/../layout/header.php'; ?>
<?php require_once __DIR__ . '/../layout/navbar.php'; ?>

<div class="container-fluid">
  <div class="row">

    <!-- Sidebar -->
    <div class="col-md-2 px-0 sidebar">
      <nav class="nav flex-column pt-3">
        <a class="nav-link" href="/views/directeur/dashboard.php">
          <i class="bi bi-speedometer2"></i> Tableau de bord
        </a>
        <a class="nav-link active" href="/views/directeur/gerer_visibilite.php">
          <i class="bi bi-eye"></i> Gérer la visibilité
        </a>
        <a class="nav-link" href="/views/commentateur/rechercher.php">
          <i class="bi bi-search"></i> Consulter mémoires
        </a>
      </nav>
    </div>

    <!-- Contenu -->
    <div class="col-md-10 p-4">

      <h2 class="section-title">Gérer la visibilité des mémoires</h2>

      <?php if ($successMsg): ?>
        <div class="alert alert-success mb-4">
          <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($successMsg) ?>
        </div>
      <?php endif; ?>

      <?php if ($errorMsg): ?>
        <div class="alert alert-danger mb-4">
          <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($errorMsg) ?>
        </div>
      <?php endif; ?>

      <!-- Filtres -->
      <div class="card mb-4">
        <div class="card-body py-3">
          <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
              <label style="font-size:0.85rem">Statut</label>
              <select name="statut" class="form-select form-select-sm mt-1">
                <option value="">Tous les statuts</option>
                <option value="valide"     <?= $filtreStatut === 'valide'     ? 'selected' : '' ?>>Validé</option>
                <option value="publie"     <?= $filtreStatut === 'publie'     ? 'selected' : '' ?>>Publié</option>
                <option value="non_public" <?= $filtreStatut === 'non_public' ? 'selected' : '' ?>>Non public</option>
              </select>
            </div>
            <div class="col-md-3">
              <label style="font-size:0.85rem">Type de diplôme</label>
              <select name="type" class="form-select form-select-sm mt-1">
                <option value="">Tous les types</option>
                <option value="licence" <?= $filtreType === 'licence' ? 'selected' : '' ?>>Licence</option>
                <option value="master"  <?= $filtreType === 'master'  ? 'selected' : '' ?>>Master</option>
              </select>
            </div>
            <div class="col-md-2">
              <button type="submit" class="btn btn-uatm btn-sm w-100">
                <i class="bi bi-funnel me-1"></i> Filtrer
              </button>
            </div>
            <?php if ($filtreStatut || $filtreType): ?>
              <div class="col-md-2">
                <a href="/views/directeur/gerer_visibilite.php"
                   class="btn btn-outline-secondary btn-sm w-100">
                  Réinitialiser
                </a>
              </div>
            <?php endif; ?>
          </form>
        </div>
      </div>

      <!-- Tableau des mémoires -->
      <div class="card">
        <div class="card-header-uatm d-flex justify-content-between align-items-center">
          <span>
            <i class="bi bi-list-ul me-2"></i>
            Mémoires gérables
          </span>
          <span style="font-size:0.85rem;font-weight:400">
            <?= count($memoires) ?> résultat<?= count($memoires) > 1 ? 's' : '' ?>
          </span>
        </div>
        <div class="card-body p-0">

          <?php if (empty($memoires)): ?>
            <div class="text-center text-muted py-5">
              <i class="bi bi-inbox" style="font-size:2.5rem"></i>
              <p class="mt-2 mb-0">Aucun mémoire correspondant aux filtres.</p>
            </div>

          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-uatm table-hover mb-0">
                <thead>
                  <tr>
                    <th>Étudiant</th>
                    <th>Titre</th>
                    <th>Type</th>
                    <th>Filière</th>
                    <th>Année</th>
                    <th>Professeur</th>
                    <th>Statut</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($memoires as $m): ?>
                    <tr>
                      <td><?= htmlspecialchars($m['nom_etudiant']) ?></td>
                      <td title="<?= htmlspecialchars($m['titre']) ?>">
                        <?= htmlspecialchars(
                            mb_strlen($m['titre']) > 35
                            ? mb_substr($m['titre'], 0, 35) . '…'
                            : $m['titre']
                        ) ?>
                      </td>
                      <td><?= htmlspecialchars(ucfirst($m['type_diplome'])) ?></td>
                      <td><?= htmlspecialchars($m['nom_filiere'] ?? '—') ?></td>
                      <td><?= (int) $m['annee_soutenance'] ?></td>
                      <td><?= htmlspecialchars($m['nom_professeur'] ?? '—') ?></td>
                      <td>
                        <span class="badge badge-<?= $m['statut'] ?>">
                          <?= htmlspecialchars($labelStatut[$m['statut']] ?? $m['statut']) ?>
                        </span>
                      </td>
                      <td>
                        <?php if ($m['statut'] === STATUT_PUBLIE): ?>
                          <!-- Dépublier -->
                          <form method="POST" action="/controllers/DirecteurController.php"
                                style="display:inline">
                            <input type="hidden" name="action"     value="depublier">
                            <input type="hidden" name="id_memoire" value="<?= $m['id_memoire'] ?>">
                            <button type="submit"
                                    class="btn btn-sm btn-outline-danger"
                                    data-confirm="Retirer ce mémoire de la plateforme publique ?">
                              <i class="bi bi-eye-slash me-1"></i> Dépublier
                            </button>
                          </form>

                        <?php elseif (in_array($m['statut'], [STATUT_VALIDE, STATUT_NON_PUBLIC], true)): ?>
                          <!-- Publier -->
                          <form method="POST" action="/controllers/DirecteurController.php"
                                style="display:inline">
                            <input type="hidden" name="action"     value="publier">
                            <input type="hidden" name="id_memoire" value="<?= $m['id_memoire'] ?>">
                            <button type="submit"
                                    class="btn btn-sm btn-uatm"
                                    data-confirm="Publier ce mémoire sur la plateforme ?">
                              <i class="bi bi-globe me-1"></i> Publier
                            </button>
                          </form>
                        <?php endif; ?>
                        <a href="/views/directeur/preview_memoire.php?id=<?= $m['id_memoire'] ?>"
                           class="btn btn-sm btn-outline-secondary ms-1">
                          <i class="bi bi-eye me-1"></i> Prévisualiser
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>

        </div>
      </div>

    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>