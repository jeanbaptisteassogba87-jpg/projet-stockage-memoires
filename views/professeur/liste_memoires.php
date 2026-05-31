<?php

// Rôle : affiche deux listes au professeur :
//        1. Mémoires en attente du centre (à prendre en charge)
//        2. Ses mémoires assignés (en vérification, validés, rejetés)


$pageTitle = 'Mémoires à vérifier — UATM GASA Formation';

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../dao/ProfesseurDAO.php';

requireRole(ROLE_PROFESSEUR);

$dao          = new ProfesseurDAO();
$professeurId = (int) $_SESSION['user_id'];
$centreId     = (int) $_SESSION['centre_id'];

$enAttente   = $dao->listerEnAttente($centreId);
$mesMemoires = $dao->listerMesMemoires($professeurId);

$labelStatut = [
    STATUT_EN_ATTENTE      => 'En attente',
    STATUT_EN_VERIFICATION => 'En vérification',
    STATUT_VALIDE          => 'Validé',
    STATUT_REJETE          => 'Rejeté',
    STATUT_PUBLIE          => 'Publié',
    STATUT_NON_PUBLIC      => 'Non public',
];

// Messages de feedback
$successMessages = [
    'valide'        => 'Mémoire validé avec succès.',
    'rejete'        => 'Mémoire rejeté. L\'étudiant a été notifié.',
    'remarque_ok'   => 'Remarque enregistrée.',
];
$errorMessages = [
    'deja_pris'    => 'Ce mémoire a déjà été pris en charge par un autre professeur.',
    'id_manquant'  => 'Identifiant du mémoire manquant.',
];

$successMsg = !empty($_GET['success']) ? ($successMessages[$_GET['success']] ?? '') : '';
$errorMsg   = !empty($_GET['error'])   ? ($errorMessages[$_GET['error']]   ?? '') : '';
?>
<?php require_once __DIR__ . '/../layout/header.php'; ?>
<?php require_once __DIR__ . '/../layout/navbar.php'; ?>

<div class="container-fluid">
  <div class="row">

    <!-- Sidebar -->
    <div class="col-md-2 px-0 sidebar">
      <nav class="nav flex-column pt-3">
        <a class="nav-link" href="/views/professeur/dashboard.php">
          <i class="bi bi-speedometer2"></i> Tableau de bord
        </a>
        <a class="nav-link active" href="/views/professeur/liste_memoires.php">
          <i class="bi bi-list-check"></i> Mémoires à vérifier
          <?php if (count($enAttente) > 0): ?>
            <span class="badge bg-warning text-dark ms-1"><?= count($enAttente) ?></span>
          <?php endif; ?>
        </a>
        <a class="nav-link" href="/views/commentateur/rechercher.php">
          <i class="bi bi-search"></i> Consulter mémoires
        </a>
      </nav>
    </div>

    <!-- Contenu -->
    <div class="col-md-10 p-4">

      <h2 class="section-title">Mémoires à vérifier</h2>

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

      <!-- ── Mémoires en attente (à prendre en charge) ──────── -->
      <div class="card mb-4">
        <div class="card-header-uatm">
          <i class="bi bi-inbox me-2"></i>
          Mémoires en attente dans votre centre
          <span class="badge bg-warning text-dark ms-2"><?= count($enAttente) ?></span>
        </div>
        <div class="card-body p-0">
          <?php if (empty($enAttente)): ?>
            <div class="text-center text-muted py-4">
              <i class="bi bi-check2-all" style="font-size:2rem;color:var(--success)"></i>
              <p class="mt-2 mb-0">Aucun mémoire en attente pour le moment.</p>
            </div>
          <?php else: ?>
            <table class="table table-uatm table-hover mb-0">
              <thead>
                <tr>
                  <th>Étudiant</th>
                  <th>Niveau</th>
                  <th>Titre</th>
                  <th>Type</th>
                  <th>Déposé le</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($enAttente as $m): ?>
                  <tr>
                    <td><?= htmlspecialchars($m['nom_etudiant']) ?></td>
                    <td>
                      <span class="badge bg-secondary">
                        <?= htmlspecialchars($m['niveau_etude']) ?>
                      </span>
                    </td>
                    <td title="<?= htmlspecialchars($m['titre']) ?>">
                      <?= htmlspecialchars(
                          mb_strlen($m['titre']) > 40
                          ? mb_substr($m['titre'], 0, 40) . '…'
                          : $m['titre']
                      ) ?>
                    </td>
                    <td><?= htmlspecialchars(ucfirst($m['type_diplome'])) ?></td>
                    <td><?= date('d/m/Y', strtotime($m['date_depot'])) ?></td>
                    <td>
                      <!-- Prendre en charge via POST -->
                      <form method="POST" action="/controllers/ProfesseurController.php"
                            style="display:inline">
                        <input type="hidden" name="action"     value="prendre_en_charge">
                        <input type="hidden" name="id_memoire" value="<?= $m['id_memoire'] ?>">
                        <button type="submit" class="btn btn-sm btn-uatm">
                          <i class="bi bi-hand-index me-1"></i> Prendre en charge
                        </button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </div>
      </div>

      <!-- ── Mes mémoires assignés ───────────────────────────── -->
      <div class="card">
        <div class="card-header-uatm">
          <i class="bi bi-person-check me-2"></i>
          Mes mémoires assignés
        </div>
        <div class="card-body p-0">
          <?php if (empty($mesMemoires)): ?>
            <div class="text-center text-muted py-4">
              <p class="mb-0">Vous n'avez pas encore traité de mémoires.</p>
            </div>
          <?php else: ?>
            <table class="table table-uatm table-hover mb-0">
              <thead>
                <tr>
                  <th>Étudiant</th>
                  <th>Titre</th>
                  <th>Type</th>
                  <th>Statut</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($mesMemoires as $m): ?>
                  <tr>
                    <td><?= htmlspecialchars($m['nom_etudiant']) ?></td>
                    <td title="<?= htmlspecialchars($m['titre']) ?>">
                      <?= htmlspecialchars(
                          mb_strlen($m['titre']) > 40
                          ? mb_substr($m['titre'], 0, 40) . '…'
                          : $m['titre']
                      ) ?>
                    </td>
                    <td><?= htmlspecialchars(ucfirst($m['type_diplome'])) ?></td>
                    <td>
                      <span class="badge badge-<?= $m['statut'] ?>">
                        <?= htmlspecialchars($labelStatut[$m['statut']] ?? $m['statut']) ?>
                      </span>
                    </td>
                    <td>
                      <?php if ($m['statut'] === STATUT_EN_VERIFICATION): ?>
                        <a href="/views/professeur/verifier_memoire.php?id=<?= $m['id_memoire'] ?>"
                           class="btn btn-sm btn-uatm">
                          <i class="bi bi-eye me-1"></i> Vérifier
                        </a>
                      <?php else: ?>
                        <a href="/views/professeur/verifier_memoire.php?id=<?= $m['id_memoire'] ?>"
                           class="btn btn-sm btn-outline-secondary">
                          <i class="bi bi-eye me-1"></i> Voir
                        </a>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>