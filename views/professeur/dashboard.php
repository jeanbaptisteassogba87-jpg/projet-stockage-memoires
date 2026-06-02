<?php

// Rôle : page d'accueil du professeur
// Affiche ses stats (en vérification, validés, rejetés) et le nombre de mémoires en attente dans son centre

$pageTitle = 'Espace Professeur — UATM GASA Formation';

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../dao/ProfesseurDAO.php';

requireRole(ROLE_PROFESSEUR);

$dao          = new ProfesseurDAO();
$professeurId = (int) $_SESSION['user_id'];
$centreId     = (int) $_SESSION['centre_id'];

// Stats du professeur
$stats          = $dao->compterParStatut($professeurId);
$nbEnVerif      = $stats[STATUT_EN_VERIFICATION] ?? 0;
$nbValides      = $stats[STATUT_VALIDE]          ?? 0;
$nbRejetes      = $stats[STATUT_REJETE]          ?? 0;
$nbEnAttente    = $dao->compterEnAttenteCentre($centreId, $professeurId);

// Ses mémoires en cours (en_verification)
$mesMemoires = $dao->listerMesMemoires($professeurId);
$enCours     = array_filter($mesMemoires, fn($m) => $m['statut'] === STATUT_EN_VERIFICATION);

$labelStatut = [
    STATUT_EN_ATTENTE      => 'En attente',
    STATUT_EN_VERIFICATION => 'En vérification',
    STATUT_VALIDE          => 'Validé',
    STATUT_REJETE          => 'Rejeté',
    STATUT_PUBLIE          => 'Publié',
    STATUT_NON_PUBLIC      => 'Non public',
];
?>
<?php require_once __DIR__ . '/../layout/header.php'; ?>
<?php require_once __DIR__ . '/../layout/navbar.php'; ?>

<div class="container-fluid">
  <div class="row">

    <!-- Sidebar -->
    <div class="col-md-2 px-0 sidebar">
      <nav class="nav flex-column pt-3">
        <a class="nav-link active" href="/views/professeur/dashboard.php">
          <i class="bi bi-speedometer2"></i> Tableau de bord
        </a>
        <a class="nav-link" href="/views/professeur/liste_memoires.php">
          <i class="bi bi-list-check"></i> Mémoires à vérifier
          <?php if ($nbEnAttente > 0): ?>
            <span class="badge bg-warning text-dark ms-1"><?= $nbEnAttente ?></span>
          <?php endif; ?>
        </a>
        <a class="nav-link" href="/views/commentateur/rechercher.php">
          <i class="bi bi-search"></i> Consulter mémoires
        </a>
      </nav>
    </div>

    <!-- Contenu -->
    <div class="col-md-10 p-4">

      <h2 class="section-title">Tableau de bord</h2>

      <!-- Cartes statistiques -->
      <div class="row g-3 mb-4">

        <div class="col-md-3">
          <div class="card p-3 text-center">
            <div style="font-size:2rem;color:var(--warning)">
              <i class="bi bi-hourglass-split"></i>
            </div>
            <div class="fw-bold mt-1" style="font-size:1.5rem"><?= $nbEnAttente ?></div>
            <div style="color:var(--text-muted);font-size:0.85rem">En attente dans le centre</div>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card p-3 text-center">
            <div style="font-size:2rem;color:var(--info)">
              <i class="bi bi-eye"></i>
            </div>
            <div class="fw-bold mt-1" style="font-size:1.5rem"><?= $nbEnVerif ?></div>
            <div style="color:var(--text-muted);font-size:0.85rem">En cours de vérification</div>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card p-3 text-center">
            <div style="font-size:2rem;color:var(--success)">
              <i class="bi bi-check-circle"></i>
            </div>
            <div class="fw-bold mt-1" style="font-size:1.5rem"><?= $nbValides ?></div>
            <div style="color:var(--text-muted);font-size:0.85rem">Validés</div>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card p-3 text-center">
            <div style="font-size:2rem;color:var(--danger)">
              <i class="bi bi-x-circle"></i>
            </div>
            <div class="fw-bold mt-1" style="font-size:1.5rem"><?= $nbRejetes ?></div>
            <div style="color:var(--text-muted);font-size:0.85rem">Rejetés</div>
          </div>
        </div>

      </div>

      <!-- Mémoires en cours de vérification -->
      <div class="card">
        <div class="card-header-uatm d-flex justify-content-between align-items-center">
          <span><i class="bi bi-eye me-2"></i>Mes mémoires en cours de vérification</span>
          <a href="/views/professeur/liste_memoires.php" class="btn btn-accent btn-sm"
             style="font-size:0.8rem">
            <i class="bi bi-list-ul me-1"></i> Voir tous
          </a>
        </div>
        <div class="card-body p-0">
          <?php if (empty($enCours)): ?>
            <div class="text-center text-muted py-4">
              <i class="bi bi-inbox" style="font-size:2rem"></i>
              <p class="mt-2 mb-0">Aucun mémoire en cours de vérification.</p>
              <?php if ($nbEnAttente > 0): ?>
                <a href="/views/professeur/liste_memoires.php" class="btn btn-uatm btn-sm mt-3">
                  Voir les <?= $nbEnAttente ?> mémoire<?= $nbEnAttente > 1 ? 's' : '' ?> en attente
                </a>
              <?php endif; ?>
            </div>
          <?php else: ?>
            <table class="table table-uatm table-hover mb-0">
              <thead>
                <tr>
                  <th>Étudiant</th>
                  <th>Titre</th>
                  <th>Type</th>
                  <th>Déposé le</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($enCours as $m): ?>
                  <tr>
                    <td><?= htmlspecialchars($m['nom_etudiant']) ?></td>
                    <td title="<?= htmlspecialchars($m['titre']) ?>">
                      <?= htmlspecialchars(
                          mb_strlen($m['titre']) > 45
                          ? mb_substr($m['titre'], 0, 45) . '…'
                          : $m['titre']
                      ) ?>
                    </td>
                    <td><?= htmlspecialchars(ucfirst($m['type_diplome'])) ?></td>
                    <td><?= date('d/m/Y', strtotime($m['date_depot'])) ?></td>
                    <td>
                      <a href="/views/professeur/verifier_memoire.php?id=<?= $m['id_memoire'] ?>"
                         class="btn btn-sm btn-uatm">
                        <i class="bi bi-eye me-1"></i> Vérifier
                      </a>
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
