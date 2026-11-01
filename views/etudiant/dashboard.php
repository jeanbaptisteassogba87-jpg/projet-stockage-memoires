<?php

// Rôle : page d'accueil de l'étudiant connecté
// Affiche les stats (nb mémoires, remarques, statut) et la liste de ses mémoires avec leurs statuts


$pageTitle = 'Mon espace — Étudiant';

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../dao/MemoireDAO.php';

requireRole(ROLE_ETUDIANT);

// Forcer le changement de mdp à la première connexion
if ($_SESSION['doit_changer_mdp']) {
    header('Location: /views/auth/change_password.php');
    exit;
}

// Charger les mémoires de l'étudiant
$memoireDAO = new MemoireDAO();
$memoires   = $memoireDAO->listerParEtudiant((int) $_SESSION['user_id']);

// Calculer les stats
$nbMemoires   = count($memoires);
$nbRemarques  = 0;
$dernierStatut = '—';

foreach ($memoires as $m) {
    if (!empty($m['remarques'])) {
        $nbRemarques++;
    }
}

// Statut du mémoire le plus récent
if ($nbMemoires > 0) {
    $dernierStatut = $memoires[0]['statut'];
}

// Labels lisibles
$labelStatut = [
    STATUT_EN_ATTENTE      => 'En attente',
    STATUT_EN_VERIFICATION => 'En vérification',
    STATUT_VALIDE          => 'Validé',
    STATUT_REJETE          => 'Rejeté',
    STATUT_PUBLIE          => 'Publié',
    STATUT_NON_PUBLIC      => 'Non public',
];

// Message de succès après dépôt ou modification
$successMessages = [
    'depot_ok' => 'Votre mémoire a été déposé avec succès. Il est en attente de vérification.',
    'modif_ok' => 'Votre mémoire a été mis à jour et resoumis.',
];
$successMsg = !empty($_GET['success']) ? ($successMessages[$_GET['success']] ?? '') : '';

$errorMessages = [
    'niveau_insuffisant' => 'Votre niveau d\'études ne vous permet pas de déposer un mémoire.',
    'non_autorise'       => 'Action non autorisée.',
];
$errorMsg = !empty($_GET['error']) ? ($errorMessages[$_GET['error']] ?? '') : '';
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
          <i class="bi bi-upload"></i> Déposer un mémoire
        </a>
        <a class="nav-link" href="/views/etudiant/modifier_memoire.php">
          <i class="bi bi-pencil"></i> Modifier mon mémoire
        </a>
        <a class="nav-link" href="/views/etudiant/voir_remarques.php">
          <i class="bi bi-chat-text"></i> Mes remarques
        </a>
        <a class="nav-link" href="/views/commentateur/rechercher.php">
          <i class="bi bi-search"></i> Consulter mémoires
        </a>
      </nav>
    </div>

    <!-- Contenu principal -->
    <div class="col-md-10 p-4">

      <h2 class="section-title">Tableau de bord</h2>

      <!-- Alertes succès / erreur -->
      <?php if ($successMsg): ?>
        <div class="alert alert-success mb-4">
          <i class="bi bi-check-circle me-2"></i>
          <?= htmlspecialchars($successMsg) ?>
        </div>
      <?php endif; ?>

      <?php if ($errorMsg): ?>
        <div class="alert alert-danger mb-4">
          <i class="bi bi-exclamation-triangle me-2"></i>
          <?= htmlspecialchars($errorMsg) ?>
        </div>
      <?php endif; ?>

      <!-- Cartes statistiques -->
      <div class="row g-3 mb-4">

        <div class="col-md-4">
          <div class="card p-3 text-center">
            <div style="font-size:2rem;color:var(--primary)">
              <i class="bi bi-file-earmark-text"></i>
            </div>
            <div class="fw-bold mt-1" style="font-size:1.5rem">
              <?= $nbMemoires ?>
            </div>
            <div style="color:var(--text-muted);font-size:0.85rem">
              Mémoire<?= $nbMemoires > 1 ? 's' : '' ?> déposé<?= $nbMemoires > 1 ? 's' : '' ?>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card p-3 text-center">
            <div style="font-size:2rem;color:var(--secondary)">
              <i class="bi bi-chat-dots"></i>
            </div>
            <div class="fw-bold mt-1" style="font-size:1.5rem">
              <?= $nbRemarques ?>
            </div>
            <div style="color:var(--text-muted);font-size:0.85rem">
              Remarque<?= $nbRemarques > 1 ? 's' : '' ?> reçue<?= $nbRemarques > 1 ? 's' : '' ?>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card p-3 text-center">
            <div style="font-size:2rem;color:var(--success)">
              <i class="bi bi-check-circle"></i>
            </div>
            <div class="fw-bold mt-1" style="font-size:1rem">
              <?php if ($dernierStatut !== '—'): ?>
                <span class="badge badge-<?= $dernierStatut ?>" style="font-size:0.85rem">
                  <?= htmlspecialchars($labelStatut[$dernierStatut] ?? $dernierStatut) ?>
                </span>
              <?php else: ?>
                —
              <?php endif; ?>
            </div>
            <div style="color:var(--text-muted);font-size:0.85rem">
              Statut du dernier mémoire
            </div>
          </div>
        </div>

      </div>

      <!-- Tableau des mémoires -->
      <div class="card">
        <div class="card-header-uatm d-flex justify-content-between align-items-center">
          <span><i class="bi bi-list-ul me-2"></i>Mes mémoires</span>
          <a href="/views/etudiant/deposer_memoire.php"
             class="btn btn-accent btn-sm"
             style="font-size:0.8rem">
            <i class="bi bi-plus me-1"></i> Nouveau dépôt
          </a>
        </div>
        <div class="card-body p-0">
          <?php if (empty($memoires)): ?>
            <div class="text-center text-muted py-4">
              <i class="bi bi-inbox" style="font-size:2rem"></i>
              <p class="mt-2 mb-0">Aucun mémoire déposé pour l'instant.</p>
            </div>
          <?php else: ?>
            <table class="table table-uatm table-hover mb-0">
              <thead>
                <tr>
                  <th>Titre</th>
                  <th>Type</th>
                  <th>Année</th>
                  <th>Date dépôt</th>
                  <th>Statut</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($memoires as $m): ?>
                  <?php
                    $estBinome = !empty($m['etudiant2_id']);
                    $binomeNom = null;
                    if ($estBinome) {
                        if ((int) $_SESSION['user_id'] === (int) $m['etudiant_id']) {
                            $binomeNom = $m['binome_nom'];
                        } else {
                            $binomeNom = $m['auteur_nom'];
                        }
                    }
                  ?>
                  <tr>
                    <td>
                      <span title="<?= htmlspecialchars($m['titre']) ?>">
                        <?= htmlspecialchars(
                            mb_strlen($m['titre']) > 50
                            ? mb_substr($m['titre'], 0, 50) . '…'
                            : $m['titre']
                        ) ?>
                      </span>
                      <?php if ($binomeNom): ?>
                        <div class="small text-muted mt-1">
                          Binôme avec <?= htmlspecialchars($binomeNom) ?>
                        </div>
                      <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars(ucfirst($m['type_diplome'])) ?></td>
                    <td><?= (int) $m['annee_soutenance'] ?></td>
                    <td><?= date('d/m/Y', strtotime($m['date_depot'])) ?></td>
                    <td>
                      <span class="badge badge-<?= $m['statut'] ?>">
                        <?= htmlspecialchars($labelStatut[$m['statut']] ?? $m['statut']) ?>
                      </span>
                      <?php if ($estBinome): ?>
                        <span class="badge bg-secondary ms-1">Binôme</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <!-- Voir remarques -->
                      <a href="/views/etudiant/voir_remarques.php?id=<?= $m['id_memoire'] ?>"
                         class="btn btn-sm btn-outline-secondary me-1"
                         title="Voir les remarques">
                        <i class="bi bi-chat-text"></i>
                      </a>
                      <?php if ($m['statut'] === STATUT_PUBLIE): ?>
                        <a href="/views/commentateur/consulter_memoire.php?id=<?= $m['id_memoire'] ?>"
                           class="btn btn-sm btn-outline-primary me-1"
                           title="Voir les commentaires publics">
                          <i class="bi bi-chat-left-text"></i>
                        </a>
                      <?php endif; ?>
                      <!-- Modifier uniquement si statut le permet -->
                      <?php if (in_array($m['statut'], [STATUT_EN_ATTENTE, STATUT_REJETE], true)): ?>
                        <a href="/views/etudiant/modifier_memoire.php?id=<?= $m['id_memoire'] ?>"
                           class="btn btn-sm btn-uatm"
                           title="Modifier">
                          <i class="bi bi-pencil"></i>
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
