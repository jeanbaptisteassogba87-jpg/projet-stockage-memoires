<?php

// Rôle : page d'accueil du directeur des études
//        Vue d'ensemble de tous les mémoires du centre
//        avec stats globales (par statut, nb étudiants)


$pageTitle = 'Espace Directeur — UATM GASA Formation';

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../dao/DirecteurDAO.php';

requireRole(ROLE_DIRECTEUR);

$dao      = new DirecteurDAO();
$centreId = (int) $_SESSION['centre_id'];

// Stats par statut
$stats        = $dao->statsParStatut($centreId);
$nbEtudiants  = $dao->compterEtudiants($centreId);

$nbEnAttente   = $stats[STATUT_EN_ATTENTE]      ?? 0;
$nbEnVerif     = $stats[STATUT_EN_VERIFICATION] ?? 0;
$nbValides     = $stats[STATUT_VALIDE]          ?? 0;
$nbPublies     = $stats[STATUT_PUBLIE]          ?? 0;
$nbNonPublics  = $stats[STATUT_NON_PUBLIC]      ?? 0;
$nbRejetes     = $stats[STATUT_REJETE]          ?? 0;
$totalMemoires = array_sum($stats);

// Mémoires récemment validés (à publier)
$gerables       = $dao->listerMemoresGerables($centreId);
$aPublier       = array_filter($gerables, fn($m) => $m['statut'] === STATUT_VALIDE);
$aPublier       = array_slice($aPublier, 0, 5); // 5 derniers seulement sur le dashboard
?>
<?php require_once __DIR__ . '/../layout/header.php'; ?>
<?php require_once __DIR__ . '/../layout/navbar.php'; ?>

<div class="container-fluid">
  <div class="row">

    <!-- Sidebar -->
    <div class="col-md-2 px-0 sidebar">
      <nav class="nav flex-column pt-3">
        <a class="nav-link active" href="/views/directeur/dashboard.php">
          <i class="bi bi-speedometer2"></i> Tableau de bord
        </a>
        <a class="nav-link" href="/views/directeur/gerer_visibilite.php">
          <i class="bi bi-eye"></i> Gérer la visibilité
          <?php if (count($aPublier) > 0): ?>
            <span class="badge bg-warning text-dark ms-1"><?= count($aPublier) ?></span>
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

      <!-- Cartes statistiques — ligne 1 -->
      <div class="row g-3 mb-3">

        <div class="col-md-3">
          <div class="card p-3 text-center">
            <div style="font-size:2rem;color:var(--primary)">
              <i class="bi bi-people"></i>
            </div>
            <div class="fw-bold mt-1" style="font-size:1.5rem"><?= $nbEtudiants ?></div>
            <div style="color:var(--text-muted);font-size:0.85rem">Étudiants actifs</div>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card p-3 text-center">
            <div style="font-size:2rem;color:var(--info)">
              <i class="bi bi-file-earmark-text"></i>
            </div>
            <div class="fw-bold mt-1" style="font-size:1.5rem"><?= $totalMemoires ?></div>
            <div style="color:var(--text-muted);font-size:0.85rem">Mémoires au total</div>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card p-3 text-center">
            <div style="font-size:2rem;color:var(--success)">
              <i class="bi bi-globe"></i>
            </div>
            <div class="fw-bold mt-1" style="font-size:1.5rem"><?= $nbPublies ?></div>
            <div style="color:var(--text-muted);font-size:0.85rem">Publiés en ligne</div>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card p-3 text-center">
            <div style="font-size:2rem;color:var(--warning)">
              <i class="bi bi-hourglass-split"></i>
            </div>
            <div class="fw-bold mt-1" style="font-size:1.5rem"><?= $nbEnAttente + $nbEnVerif ?></div>
            <div style="color:var(--text-muted);font-size:0.85rem">En cours de traitement</div>
          </div>
        </div>

      </div>

      <!-- Barre de progression globale -->
      <div class="card mb-4">
        <div class="card-header-uatm">
          <i class="bi bi-bar-chart me-2"></i>Répartition des mémoires par statut
        </div>
        <div class="card-body">
          <?php if ($totalMemoires > 0):
            $pctPublies  = round($nbPublies     / $totalMemoires * 100);
            $pctValides  = round($nbValides     / $totalMemoires * 100);
            $pctVerif    = round($nbEnVerif     / $totalMemoires * 100);
            $pctAttente  = round($nbEnAttente   / $totalMemoires * 100);
            $pctRejetes  = round($nbRejetes     / $totalMemoires * 100);
            $pctNonPub   = round($nbNonPublics  / $totalMemoires * 100);
          ?>
            <div style="height:24px;border-radius:12px;overflow:hidden;display:flex;margin-bottom:12px">
              <?php if ($nbPublies):  ?><div style="width:<?= $pctPublies ?>%;background:var(--primary);transition:width 0.5s" title="Publiés : <?= $nbPublies ?>"></div><?php endif; ?>
              <?php if ($nbValides):  ?><div style="width:<?= $pctValides ?>%;background:var(--success);transition:width 0.5s" title="Validés : <?= $nbValides ?>"></div><?php endif; ?>
              <?php if ($nbEnVerif):  ?><div style="width:<?= $pctVerif ?>%;background:var(--info);transition:width 0.5s" title="En vérif : <?= $nbEnVerif ?>"></div><?php endif; ?>
              <?php if ($nbEnAttente):?><div style="width:<?= $pctAttente ?>%;background:var(--warning);transition:width 0.5s" title="En attente : <?= $nbEnAttente ?>"></div><?php endif; ?>
              <?php if ($nbRejetes):  ?><div style="width:<?= $pctRejetes ?>%;background:var(--danger);transition:width 0.5s" title="Rejetés : <?= $nbRejetes ?>"></div><?php endif; ?>
              <?php if ($nbNonPublics):?><div style="width:<?= $pctNonPub ?>%;background:var(--text-muted);transition:width 0.5s" title="Non publics : <?= $nbNonPublics ?>"></div><?php endif; ?>
            </div>
            <!-- Légende -->
            <div class="d-flex flex-wrap gap-3" style="font-size:0.82rem">
              <span><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:var(--primary);margin-right:4px"></span>Publiés <strong><?= $nbPublies ?></strong></span>
              <span><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:var(--success);margin-right:4px"></span>Validés <strong><?= $nbValides ?></strong></span>
              <span><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:var(--info);margin-right:4px"></span>En vérif. <strong><?= $nbEnVerif ?></strong></span>
              <span><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:var(--warning);margin-right:4px"></span>En attente <strong><?= $nbEnAttente ?></strong></span>
              <span><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:var(--danger);margin-right:4px"></span>Rejetés <strong><?= $nbRejetes ?></strong></span>
              <span><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:var(--text-muted);margin-right:4px"></span>Non publics <strong><?= $nbNonPublics ?></strong></span>
            </div>
          <?php else: ?>
            <p style="color:var(--text-muted);margin:0">Aucun mémoire dans le centre pour le moment.</p>
          <?php endif; ?>
        </div>
      </div>

      <!-- Mémoires validés en attente de publication -->
      <div class="card">
        <div class="card-header-uatm d-flex justify-content-between align-items-center">
          <span>
            <i class="bi bi-check-circle me-2"></i>
            Mémoires validés — en attente de publication
          </span>
          <a href="/views/directeur/gerer_visibilite.php"
             class="btn btn-accent btn-sm" style="font-size:0.8rem">
            <i class="bi bi-sliders me-1"></i> Gérer tous
          </a>
        </div>
        <div class="card-body p-0">
          <?php if (empty($aPublier)): ?>
            <div class="text-center text-muted py-4">
              <i class="bi bi-check2-all" style="font-size:2rem;color:var(--success)"></i>
              <p class="mt-2 mb-0">Aucun mémoire en attente de publication.</p>
            </div>
          <?php else: ?>
            <table class="table table-uatm table-hover mb-0">
              <thead>
                <tr>
                  <th>Étudiant</th>
                  <th>Titre</th>
                  <th>Type</th>
                  <th>Professeur</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($aPublier as $m): ?>
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
                    <td><?= htmlspecialchars($m['nom_professeur'] ?? '—') ?></td>
                    <td>
                      <form method="POST" action="/controllers/DirecteurController.php"
                            style="display:inline">
                        <input type="hidden" name="action"     value="publier">
                        <input type="hidden" name="id_memoire" value="<?= $m['id_memoire'] ?>">
                        <button type="submit" class="btn btn-sm btn-uatm"
                                onclick="return confirm('Publier ce mémoire en ligne ?')">
                          <i class="bi bi-globe me-1"></i> Publier
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

    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>