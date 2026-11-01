<?php

// Rôle : prévisualisation d'un mémoire pour le directeur avant publication

$pageTitle = 'Prévisualiser un mémoire — UATM GASA Formation';

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../dao/DirecteurDAO.php';

requireRole(ROLE_DIRECTEUR);

$id = (int) ($_GET['id'] ?? 0);
if (!$id) {
    header('Location: /views/directeur/gerer_visibilite.php');
    exit;
}

$dao = new DirecteurDAO();
$memoire = $dao->trouverMemoirePreview($id, (int) $_SESSION['centre_id']);
if (!$memoire) {
    header('Location: /views/directeur/gerer_visibilite.php?error=introuvable');
    exit;
}

$labelStatut = [
    STATUT_VALIDE     => 'Validé',
    STATUT_NON_PUBLIC => 'Non public',
    STATUT_PUBLIE     => 'Publié',
];
?>
<?php require_once __DIR__ . '/../layout/header.php'; ?>
<?php require_once __DIR__ . '/../layout/navbar.php'; ?>

<div class="container-fluid">
  <div class="row">

    <div class="col-md-2 px-0 sidebar">
      <nav class="nav flex-column pt-3">
        <a class="nav-link" href="/views/directeur/dashboard.php">
          <i class="bi bi-speedometer2"></i> Tableau de bord
        </a>
        <a class="nav-link" href="/views/directeur/gerer_visibilite.php">
          <i class="bi bi-eye"></i> Gérer la visibilité
        </a>
        <a class="nav-link active" href="/views/directeur/preview_memoire.php?id=<?= $memoire['id_memoire'] ?>">
          <i class="bi bi-eye-fill"></i> Prévisualiser
        </a>
      </nav>
    </div>

    <div class="col-md-10 p-4">
      <nav style="font-size:0.85rem;margin-bottom:12px">
        <a href="/views/directeur/gerer_visibilite.php"
           style="color:var(--primary);text-decoration:none">
          ← Retour à la gestion
        </a>
      </nav>

      <div class="mb-4">
        <h2 class="section-title">Prévisualisation du mémoire</h2>
        <p style="color:var(--text-muted);font-size:0.95rem;margin-top:4px">
          Cette vue permet de vérifier le contenu avant publication.
        </p>
      </div>

      <div class="row g-4">
        <div class="col-md-5">
          <div class="card mb-4">
            <div class="card-header-uatm">
              <i class="bi bi-info-circle me-2"></i> Infos du mémoire
            </div>
            <div class="card-body">
              <div class="mb-3">
                <span style="font-size:0.8rem;font-weight:600;
                             background:<?= $memoire['type_diplome'] === 'master' ? 'var(--primary)' : 'var(--secondary)' ?>;
                             color:#fff;padding:3px 10px;border-radius:20px">
                  <?= htmlspecialchars(ucfirst($memoire['type_diplome'])) ?>
                </span>
                <span style="font-size:0.8rem;color:var(--text-muted);margin-left:8px">
                  Soutenance <?= (int) $memoire['annee_soutenance'] ?>
                </span>
              </div>

              <h5 class="fw-bold mb-2" style="color:var(--text-main);line-height:1.4">
                <?= htmlspecialchars($memoire['titre']) ?>
              </h5>

              <p style="color:var(--text-muted);font-size:0.9rem;margin-bottom:16px">
                <?= htmlspecialchars($memoire['theme']) ?>
              </p>

              <div class="mb-2" style="font-size:0.88rem">
                <span style="color:var(--text-muted)">Auteur</span><br>
                <strong><?= htmlspecialchars($memoire['nom_etudiant']) ?></strong>
              </div>

              <?php if (!empty($memoire['nom_filiere'])): ?>
                <div class="mb-2" style="font-size:0.88rem">
                  <span style="color:var(--text-muted)">Filière</span><br>
                  <strong><?= htmlspecialchars($memoire['nom_filiere']) ?></strong>
                </div>
              <?php endif; ?>

              <?php if (!empty($memoire['nom_centre'])): ?>
                <div class="mb-2" style="font-size:0.88rem">
                  <span style="color:var(--text-muted)">Centre</span><br>
                  <strong><?= htmlspecialchars($memoire['nom_centre']) ?></strong>
                </div>
              <?php endif; ?>

              <?php if (!empty($memoire['nom_professeur'])): ?>
                <div class="mb-2" style="font-size:0.88rem">
                  <span style="color:var(--text-muted)">Validé par</span><br>
                  <strong><?= htmlspecialchars($memoire['nom_professeur']) ?></strong>
                </div>
              <?php endif; ?>

              <div class="mt-4">
                <span class="badge badge-<?= $memoire['statut'] ?>">
                  <?= htmlspecialchars($labelStatut[$memoire['statut']] ?? $memoire['statut']) ?>
                </span>
              </div>

              <?php if ($memoire['statut'] !== STATUT_PUBLIE): ?>
                <div class="alert mt-4 mb-0"
                     style="background:#FFF3CD;border-left:4px solid var(--warning);border-radius:var(--radius)">
                  <i class="bi bi-eye me-2"></i>
                  Mémoire non publié, voici la vue de prévisualisation.
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div class="col-md-7">
          <div class="card">
            <div class="card-header-uatm d-flex justify-content-between align-items-center">
              <span>
                <i class="bi bi-file-earmark-pdf me-2"></i>
                <?= htmlspecialchars(mb_substr($memoire['titre'], 0, 50)) ?>
              </span>
            </div>
            <div class="card-body p-2">
              <div data-protected-pdf data-memoire-id="<?= (int) $memoire['id_memoire'] ?>">
                <div class="d-flex justify-content-center align-items-center gap-3 mb-3">
                  <button type="button" class="btn btn-sm btn-secondary" data-pdf-previous>◀ Précédent</button>
                  <span data-pdf-status>Chargement du document…</span>
                  <button type="button" class="btn btn-sm btn-secondary" data-pdf-next>Suivant ▶</button>
                </div>
                <img data-pdf-page alt="Page du mémoire" class="img-fluid d-block mx-auto"
                     style="max-height:75vh;border:1px solid #ccc;border-radius:8px">
              </div>
              <script src="/public/js/protected_pdf_viewer.js"></script>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
