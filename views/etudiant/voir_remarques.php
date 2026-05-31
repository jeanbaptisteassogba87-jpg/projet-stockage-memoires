<?php

// Rôle : affiche les remarques laissées par le professeur sur les mémoires de l'étudiant connecté
// Affiche aussi le statut de chaque mémoire pour que l'étudiant sache quoi faire 

$pageTitle = 'Mes remarques — UATM GASA Formation';

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../dao/MemoireDAO.php';

requireRole(ROLE_ETUDIANT);

if ($_SESSION['doit_changer_mdp']) {
    header('Location: /views/auth/change_password.php');
    exit;
}

$memoireDAO = new MemoireDAO();
$memoires   = $memoireDAO->listerParEtudiant((int) $_SESSION['user_id']);

// Labels lisibles pour les statuts
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
        <a class="nav-link" href="/views/etudiant/dashboard.php">
          <i class="bi bi-speedometer2"></i> Tableau de bord
        </a>
        <a class="nav-link" href="/views/etudiant/deposer_memoire.php">
          <i class="bi bi-upload"></i> Déposer un mémoire
        </a>
        <a class="nav-link" href="/views/etudiant/modifier_memoire.php">
          <i class="bi bi-pencil"></i> Modifier mon mémoire
        </a>
        <a class="nav-link active" href="/views/etudiant/voir_remarques.php">
          <i class="bi bi-chat-text"></i> Mes remarques
        </a>
        <a class="nav-link" href="/views/commentateur/rechercher.php">
          <i class="bi bi-search"></i> Consulter mémoires
        </a>
      </nav>
    </div>

    <!-- Contenu -->
    <div class="col-md-10 p-4">

      <h2 class="section-title">Mes remarques</h2>

      <?php if (empty($memoires)): ?>
        <div class="card">
          <div class="card-body text-center py-5">
            <i class="bi bi-chat-square" style="font-size:3rem;color:var(--text-muted)"></i>
            <p class="mt-3" style="color:var(--text-muted)">
              Vous n'avez pas encore déposé de mémoire.
            </p>
            <a href="/views/etudiant/deposer_memoire.php" class="btn btn-uatm mt-2">
              Déposer un mémoire
            </a>
          </div>
        </div>

      <?php else: ?>
        <?php foreach ($memoires as $m): ?>
          <div class="card mb-4">
            <div class="card-header-uatm d-flex justify-content-between align-items-center">
              <span>
                <i class="bi bi-file-earmark-text me-2"></i>
                <?= htmlspecialchars($m['titre']) ?>
              </span>
              <span class="badge badge-<?= $m['statut'] ?>">
                <?= htmlspecialchars($labelStatut[$m['statut']] ?? $m['statut']) ?>
              </span>
            </div>
            <div class="card-body">

              <!-- Infos du mémoire -->
              <div class="row mb-3"
                   style="font-size:0.9rem;color:var(--text-muted)">
                <div class="col-md-4">
                  <i class="bi bi-mortarboard me-1"></i>
                  Type : <strong><?= htmlspecialchars(ucfirst($m['type_diplome'])) ?></strong>
                </div>
                <div class="col-md-4">
                  <i class="bi bi-calendar me-1"></i>
                  Soutenance : <strong><?= (int) $m['annee_soutenance'] ?></strong>
                </div>
                <div class="col-md-4">
                  <i class="bi bi-clock me-1"></i>
                  Déposé le : <strong>
                    <?= date('d/m/Y', strtotime($m['date_depot'])) ?>
                  </strong>
                </div>
              </div>

              <!-- Remarques du professeur -->
              <?php if (!empty($m['remarques'])): ?>
                <div style="background:#FFF8E7;border-left:4px solid var(--warning);
                            border-radius:var(--radius);padding:16px">
                  <div style="font-weight:600;margin-bottom:8px;color:#856404">
                    <i class="bi bi-chat-quote me-2"></i>Remarque du professeur
                  </div>
                  <div style="white-space:pre-line;color:var(--text-main)">
                    <?= nl2br(htmlspecialchars($m['remarques'])) ?>
                  </div>
                </div>
              <?php else: ?>
                <div style="color:var(--text-muted);font-style:italic">
                  <i class="bi bi-dash-circle me-2"></i>
                  Aucune remarque pour l'instant.
                </div>
              <?php endif; ?>

              <!-- Action selon le statut -->
              <?php if ($m['statut'] === STATUT_REJETE): ?>
                <div class="mt-3">
                  <a href="/views/etudiant/modifier_memoire.php?id=<?= $m['id_memoire'] ?>"
                     class="btn btn-sm btn-uatm">
                    <i class="bi bi-pencil me-1"></i> Corriger et resoumettre
                  </a>
                </div>
              <?php elseif ($m['statut'] === STATUT_VALIDE || $m['statut'] === STATUT_PUBLIE): ?>
                <div class="alert mt-3 mb-0"
                     style="background:#D4EDDA;color:#155724;border-radius:var(--radius);border:none">
                  <i class="bi bi-check-circle me-2"></i>
                  Félicitations ! Votre mémoire a été validé.
                </div>
              <?php elseif ($m['statut'] === STATUT_EN_VERIFICATION): ?>
                <div class="alert mt-3 mb-0"
                     style="background:#D1ECF1;color:#0C5460;border-radius:var(--radius);border:none">
                  <i class="bi bi-hourglass-split me-2"></i>
                  Votre mémoire est en cours de vérification par un professeur.
                </div>
              <?php endif; ?>

            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>

    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>