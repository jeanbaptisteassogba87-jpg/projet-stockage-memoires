<?php

// Rôle : page d'accueil du technicien
//        Accès rapide à toutes ses fonctionnalités


$pageTitle = 'Espace Technicien — UATM GASA Formation';

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../dao/UtilisateurDAO.php';
require_once __DIR__ . '/../../dao/MemoireDAO.php';

requireRole(ROLE_TECHNICIEN);

// Stats rapides
$utilisateurDAO = new UtilisateurDAO();
$memoireDAO     = new MemoireDAO();

$utilisateurs   = $utilisateurDAO->getAllUtilisateurs();
$memoires       = $memoireDAO->listerTous();

$nbUtilisateurs = count($utilisateurs);
$nbActifs       = count(array_filter($utilisateurs, fn($u) => $u['est_actif']));
$nbMemoires     = count($memoires);
?>
<?php require_once __DIR__ . '/../layout/header.php'; ?>
<?php require_once __DIR__ . '/../layout/navbar.php'; ?>

<div class="container-fluid">
  <div class="row">

    <!-- Sidebar -->
    <div class="col-md-2 px-0 sidebar">
      <nav class="nav flex-column pt-3">
        <a class="nav-link active" href="/views/technicien/dashboard.php">
          <i class="bi bi-speedometer2"></i> Tableau de bord
        </a>
        <a class="nav-link" href="/views/technicien/gerer_comptes.php">
          <i class="bi bi-people"></i> Gérer les comptes
        </a>
        <a class="nav-link" href="/views/technicien/importer_memoires.php">
          <i class="bi bi-file-earmark-arrow-up"></i> Importer mémoires
        </a>
        <a class="nav-link" href="/views/technicien/importer_utilisateurs.php">
          <i class="bi bi-person-plus"></i> Importer utilisateurs
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

        <div class="col-md-4">
          <div class="card p-3 text-center">
            <div style="font-size:2rem;color:var(--primary)">
              <i class="bi bi-people"></i>
            </div>
            <div class="fw-bold mt-1" style="font-size:1.5rem"><?= $nbActifs ?></div>
            <div style="color:var(--text-muted);font-size:0.85rem">
              Comptes actifs / <?= $nbUtilisateurs ?> au total
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card p-3 text-center">
            <div style="font-size:2rem;color:var(--secondary)">
              <i class="bi bi-file-earmark-text"></i>
            </div>
            <div class="fw-bold mt-1" style="font-size:1.5rem"><?= $nbMemoires ?></div>
            <div style="color:var(--text-muted);font-size:0.85rem">Mémoires en base</div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card p-3 text-center">
            <div style="font-size:2rem;color:var(--success)">
              <i class="bi bi-shield-check"></i>
            </div>
            <div class="fw-bold mt-1" style="font-size:1rem">Système actif</div>
            <div style="color:var(--text-muted);font-size:0.85rem">Tout fonctionne</div>
          </div>
        </div>

      </div>

      <!-- Accès rapides -->
      <div class="row g-3">

        <div class="col-md-6">
          <div class="card">
            <div class="card-header-uatm">
              <i class="bi bi-people me-2"></i>Gestion des comptes
            </div>
            <div class="card-body">
              <p style="color:var(--text-muted);font-size:0.9rem">
                Créer, activer ou désactiver des comptes utilisateurs.
              </p>
              <a href="/views/technicien/gerer_comptes.php" class="btn btn-uatm btn-sm">
                <i class="bi bi-arrow-right me-1"></i> Gérer les comptes
              </a>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="card">
            <div class="card-header-uatm">
              <i class="bi bi-person-plus me-2"></i>Import utilisateurs CSV
            </div>
            <div class="card-body">
              <p style="color:var(--text-muted);font-size:0.9rem">
                Importer plusieurs utilisateurs en une seule fois via un fichier CSV.
              </p>
              <a href="/views/technicien/importer_utilisateurs.php" class="btn btn-uatm btn-sm">
                <i class="bi bi-arrow-right me-1"></i> Importer utilisateurs
              </a>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="card">
            <div class="card-header-uatm">
              <i class="bi bi-file-earmark-arrow-up me-2"></i>Import mémoires anciens
            </div>
            <div class="card-body">
              <p style="color:var(--text-muted);font-size:0.9rem">
                Archiver des mémoires existants en uploadant leurs fichiers PDF.
              </p>
              <a href="/views/technicien/importer_memoires.php" class="btn btn-uatm btn-sm">
                <i class="bi bi-arrow-right me-1"></i> Importer mémoires
              </a>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="card">
            <div class="card-header-uatm">
              <i class="bi bi-search me-2"></i>Bibliothèque
            </div>
            <div class="card-body">
              <p style="color:var(--text-muted);font-size:0.9rem">
                Consulter les mémoires publiés sur la plateforme.
              </p>
              <a href="/views/commentateur/rechercher.php" class="btn btn-uatm btn-sm">
                <i class="bi bi-arrow-right me-1"></i> Consulter
              </a>
            </div>
          </div>
        </div>

      </div>

    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>