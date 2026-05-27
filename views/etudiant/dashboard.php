<?php
$pageTitle = 'Mon espace — Étudiant';
require_once __DIR__ . '/../../config/session.php';
requireRole(ROLE_ETUDIANT);
// Si premier connexion, forcer changement mdp
if ($_SESSION['doit_changer_mdp']) {
    header('Location: /views/auth/change_password.php'); exit;
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

      <!-- Cartes de statistiques -->
      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <div class="card p-3 text-center">
            <div style="font-size:2rem;color:var(--primary)">
              <i class="bi bi-file-earmark-text"></i>
            </div>
            <div class="fw-bold mt-1" id="stat-memoires">—</div>
            <div style="color:var(--text-muted);font-size:0.85rem">Mes mémoires déposés</div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card p-3 text-center">
            <div style="font-size:2rem;color:var(--secondary)">
              <i class="bi bi-chat-dots"></i>
            </div>
            <div class="fw-bold mt-1" id="stat-remarques">—</div>
            <div style="color:var(--text-muted);font-size:0.85rem">Remarques reçues</div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card p-3 text-center">
            <div style="font-size:2rem;color:var(--success)">
              <i class="bi bi-check-circle"></i>
            </div>
            <div class="fw-bold mt-1" id="stat-statut">—</div>
            <div style="color:var(--text-muted);font-size:0.85rem">Statut du mémoire</div>
          </div>
        </div>
      </div>

      <!-- Tableau des mémoires -->
      <div class="card">
        <div class="card-header-uatm">
          <i class="bi bi-list-ul me-2"></i>Mes mémoires
        </div>
        <div class="card-body p-0">
          <table class="table table-uatm table-hover mb-0">
            <thead>
              <tr>
                <th>Titre</th>
                <th>Type</th>
                <th>Date dépôt</th>
                <th>Statut</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="tbody-memoires">
              <tr><td colspan="5" class="text-center text-muted py-3">Chargement...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
