<?php
$pageTitle = 'Mon espace - Etudiant';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../dao/MemoireDAO.php';
requireRole(ROLE_ETUDIANT);

if ($_SESSION['doit_changer_mdp']) {
    header('Location: /views/auth/change_password.php');
    exit;
}

$memoireDAO = new MemoireDAO();
$memoires = $memoireDAO->listerParEtudiant((int)$_SESSION['user_id']);
$successMessage = $_SESSION['success_message'] ?? '';
unset($_SESSION['success_message']);

function libelleStatutMemoire(string $statut): string {
    $libelles = [
        STATUT_EN_ATTENTE => 'En attente',
        STATUT_EN_VERIFICATION => 'En verification',
        STATUT_VALIDE => 'Valide',
        STATUT_REJETE => 'Rejete',
        STATUT_PUBLIE => 'Publie',
        STATUT_NON_PUBLIC => 'Non public',
    ];

    return $libelles[$statut] ?? $statut;
}

function classeBadgeStatutMemoire(string $statut): string {
    $classes = [
        STATUT_EN_ATTENTE => 'bg-secondary',
        STATUT_EN_VERIFICATION => 'bg-info text-dark',
        STATUT_VALIDE => 'bg-success',
        STATUT_REJETE => 'bg-danger',
        STATUT_PUBLIE => 'bg-primary',
        STATUT_NON_PUBLIC => 'bg-dark',
    ];

    return $classes[$statut] ?? 'bg-secondary';
}

$dernierStatut = !empty($memoires) ? libelleStatutMemoire($memoires[0]['statut']) : 'Aucun';
$nombreRemarques = 0;
foreach ($memoires as $memoire) {
    if (!empty($memoire['remarques'])) {
        $nombreRemarques++;
    }
}
?>
<?php require_once __DIR__ . '/../layout/header.php'; ?>
<?php require_once __DIR__ . '/../layout/navbar.php'; ?>

<div class="container-fluid">
  <div class="row">

    <!-- Sidebar -->
    <div class="col-md-2 px-0 sidebar">
      <nav class="nav flex-column pt-3">
        <a class="nav-link active" href="/views/etudiant/dashboard.php">
          <i class="bi bi-speedometer2"></i> Tableau de bord
        </a>
        <a class="nav-link" href="/views/etudiant/deposer_memoire.php">
          <i class="bi bi-upload"></i> Deposer un memoire
        </a>
        <a class="nav-link" href="/views/etudiant/modifier_memoire.php">
          <i class="bi bi-pencil"></i> Modifier mon memoire
        </a>
        <a class="nav-link" href="/views/etudiant/voir_remarques.php">
          <i class="bi bi-chat-text"></i> Mes remarques
        </a>
        <a class="nav-link" href="/views/commentateur/rechercher.php">
          <i class="bi bi-search"></i> Consulter memoires
        </a>
      </nav>
    </div>

    <!-- Contenu principal -->
    <div class="col-md-10 p-4">
      <h2 class="section-title">Tableau de bord</h2>

      <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <i class="bi bi-check-circle"></i>
          <?= htmlspecialchars($successMessage) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
      <?php endif; ?>

      <!-- Cartes de statistiques -->
      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <div class="card p-3 text-center">
            <div style="font-size:2rem;color:var(--primary)">
              <i class="bi bi-file-earmark-text"></i>
            </div>
            <div class="fw-bold mt-1" id="stat-memoires"><?= count($memoires) ?></div>
            <div style="color:var(--text-muted);font-size:0.85rem">Mes memoires deposes</div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card p-3 text-center">
            <div style="font-size:2rem;color:var(--secondary)">
              <i class="bi bi-chat-dots"></i>
            </div>
            <div class="fw-bold mt-1" id="stat-remarques"><?= $nombreRemarques ?></div>
            <div style="color:var(--text-muted);font-size:0.85rem">Remarques recues</div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card p-3 text-center">
            <div style="font-size:2rem;color:var(--success)">
              <i class="bi bi-check-circle"></i>
            </div>
            <div class="fw-bold mt-1" id="stat-statut"><?= htmlspecialchars($dernierStatut) ?></div>
            <div style="color:var(--text-muted);font-size:0.85rem">Statut du dernier memoire</div>
          </div>
        </div>
      </div>

      <!-- Tableau des memoires -->
      <div class="card">
        <div class="card-header-uatm">
          <i class="bi bi-list-ul me-2"></i>Mes memoires
        </div>
        <div class="card-body p-0">
          <table class="table table-uatm table-hover mb-0">
            <thead>
              <tr>
                <th>Titre</th>
                <th>Type</th>
                <th>Date depot</th>
                <th>Statut</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="tbody-memoires">
              <?php if (empty($memoires)): ?>
                <tr>
                  <td colspan="5" class="text-center text-muted py-3">
                    Aucun memoire depose pour le moment.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($memoires as $memoire): ?>
                  <tr>
                    <td><?= htmlspecialchars($memoire['titre']) ?></td>
                    <td><?= htmlspecialchars(ucfirst($memoire['type_diplome'])) ?></td>
                    <td>
                      <?= !empty($memoire['date_depot']) ? htmlspecialchars(date('d/m/Y', strtotime($memoire['date_depot']))) : '-' ?>
                    </td>
                    <td>
                      <span class="badge <?= classeBadgeStatutMemoire($memoire['statut']) ?>">
                        <?= htmlspecialchars(libelleStatutMemoire($memoire['statut'])) ?>
                      </span>
                    </td>
                    <td>
                      <a
                        class="btn btn-sm btn-outline-primary"
                        href="/scripts/serve_pdf.php?id=<?= (int)$memoire['id_memoire'] ?>"
                        target="_blank"
                        rel="noopener"
                      >
                        <i class="bi bi-eye"></i> Consulter
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
