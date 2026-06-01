<?php

// Rôle : moteur de recherche des mémoires publiés
//        Accessible à tous les utilisateurs connectés
//        Filtres : mots-clés, type, année, filière


$pageTitle = 'Rechercher un mémoire — UATM GASA Formation';

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../dao/MemoirePublicDAO.php';
require_once __DIR__ . '/../../dao/LikeDAO.php';

requireAuth();

$dao     = new MemoirePublicDAO();
$likeDAO = new LikeDAO();

// Récupérer les filtres depuis GET
$motsCles = trim($_GET['q']       ?? '');
$type     = trim($_GET['type']    ?? '');
$annee    = trim($_GET['annee']   ?? '');
$filiere  = trim($_GET['filiere'] ?? '');

// Lancer la recherche uniquement si au moins un filtre est renseigné
// ou si la page est chargée sans filtre (afficher tout)
$memoires   = $dao->rechercher($motsCles, $type, $annee, $filiere);
$annees     = $dao->getAnneesDisponibles();
$filieres   = $dao->getFilieresDisponibles();

// Récupérer les likes de l'utilisateur connecté pour ces mémoires
$memoireIds = array_column($memoires, 'id_memoire');
$mesLikes   = $likeDAO->getLikesUtilisateur((int) $_SESSION['user_id'], $memoireIds);
$mesLikes   = array_flip($mesLikes); // transformer en tableau associatif pour lookup rapide

$aRecherche = ($motsCles || $type || $annee || $filiere);

// Sidebar selon le rôle
$role = $_SESSION['user_role'];
?>
<?php require_once __DIR__ . '/../layout/header.php'; ?>
<?php require_once __DIR__ . '/../layout/navbar.php'; ?>

<div class="container-fluid">
  <div class="row">

    <!-- Sidebar dynamique selon le rôle -->
    <div class="col-md-2 px-0 sidebar">
      <nav class="nav flex-column pt-3">

        <?php if ($role === ROLE_ETUDIANT): ?>
          <a class="nav-link" href="/views/etudiant/dashboard.php">
            <i class="bi bi-speedometer2"></i> Tableau de bord
          </a>
          <a class="nav-link" href="/views/etudiant/deposer_memoire.php">
            <i class="bi bi-upload"></i> Déposer un mémoire
          </a>
        <?php elseif ($role === ROLE_PROFESSEUR): ?>
          <a class="nav-link" href="/views/professeur/dashboard.php">
            <i class="bi bi-speedometer2"></i> Tableau de bord
          </a>
          <a class="nav-link" href="/views/professeur/liste_memoires.php">
            <i class="bi bi-list-check"></i> Mémoires à vérifier
          </a>
        <?php elseif ($role === ROLE_DIRECTEUR): ?>
          <a class="nav-link" href="/views/directeur/dashboard.php">
            <i class="bi bi-speedometer2"></i> Tableau de bord
          </a>
          <a class="nav-link" href="/views/directeur/gerer_visibilite.php">
            <i class="bi bi-eye"></i> Gérer la visibilité
          </a>
        <?php elseif ($role === ROLE_TECHNICIEN): ?>
          <a class="nav-link" href="/views/technicien/dashboard.php">
            <i class="bi bi-speedometer2"></i> Tableau de bord
          </a>
        <?php endif; ?>

        <a class="nav-link active" href="/views/commentateur/rechercher.php">
          <i class="bi bi-search"></i> Consulter mémoires
        </a>

      </nav>
    </div>

    <!-- Contenu -->
    <div class="col-md-10 p-4">

      <h2 class="section-title">Bibliothèque des mémoires</h2>

      <!-- Formulaire de recherche -->
      <div class="card mb-4">
        <div class="card-body py-3">
          <form method="GET" id="form-recherche">

            <!-- Barre principale -->
            <div class="input-group mb-3">
              <span class="input-group-text" style="background:var(--primary);color:#fff;border:none">
                <i class="bi bi-search"></i>
              </span>
              <input type="text"
                     name="q"
                     class="form-control"
                     placeholder="Rechercher par titre, thème, auteur…"
                     value="<?= htmlspecialchars($motsCles) ?>"
                     autofocus>
              <button type="submit" class="btn btn-uatm">
                Rechercher
              </button>
            </div>

            <!-- Filtres avancés -->
            <div class="row g-2">
              <div class="col-md-3">
                <select name="type" class="form-select form-select-sm">
                  <option value="">Tous les types</option>
                  <option value="licence" <?= $type === 'licence' ? 'selected' : '' ?>>Licence</option>
                  <option value="master"  <?= $type === 'master'  ? 'selected' : '' ?>>Master</option>
                </select>
              </div>
              <div class="col-md-3">
                <select name="annee" class="form-select form-select-sm">
                  <option value="">Toutes les années</option>
                  <?php foreach ($annees as $a): ?>
                    <option value="<?= $a ?>" <?= $annee == $a ? 'selected' : '' ?>>
                      <?= $a ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-3">
                <select name="filiere" class="form-select form-select-sm">
                  <option value="">Toutes les filières</option>
                  <?php foreach ($filieres as $f): ?>
                    <option value="<?= htmlspecialchars($f) ?>"
                            <?= $filiere === $f ? 'selected' : '' ?>>
                      <?= htmlspecialchars($f) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-2">
                <?php if ($aRecherche): ?>
                  <a href="/views/commentateur/rechercher.php"
                     class="btn btn-outline-secondary btn-sm w-100">
                    <i class="bi bi-x me-1"></i> Effacer
                  </a>
                <?php endif; ?>
              </div>
            </div>

          </form>
        </div>
      </div>

      <!-- Résultats -->
      <?php if ($aRecherche): ?>
        <p style="color:var(--text-muted);font-size:0.9rem;margin-bottom:16px">
          <?= count($memoires) ?> résultat<?= count($memoires) > 1 ? 's' : '' ?>
          pour « <strong><?= htmlspecialchars($motsCles ?: implode(', ', array_filter([$type, $annee, $filiere]))) ?></strong> »
        </p>
      <?php endif; ?>

      <?php if (empty($memoires)): ?>
        <div class="card">
          <div class="card-body text-center py-5">
            <i class="bi bi-journal-x" style="font-size:3rem;color:var(--text-muted)"></i>
            <p class="mt-3" style="color:var(--text-muted)">
              <?= $aRecherche ? 'Aucun mémoire ne correspond à votre recherche.' : 'Aucun mémoire publié pour le moment.' ?>
            </p>
          </div>
        </div>

      <?php else: ?>
        <div class="row g-3">
          <?php foreach ($memoires as $m): ?>
            <?php $aLike = isset($mesLikes[$m['id_memoire']]); ?>
            <div class="col-md-6 col-lg-4">
              <div class="card h-100"
                   style="transition:box-shadow 0.2s"
                   onmouseover="this.style.boxShadow='0 4px 16px rgba(26,60,110,0.13)'"
                   onmouseout="this.style.boxShadow=''">
                <div class="card-body d-flex flex-column">

                  <!-- Type de diplôme -->
                  <div class="mb-2">
                    <span style="font-size:0.75rem;font-weight:600;
                                 background:<?= $m['type_diplome'] === 'master' ? 'var(--primary)' : 'var(--secondary)' ?>;
                                 color:#fff;padding:2px 8px;border-radius:20px">
                      <?= htmlspecialchars(ucfirst($m['type_diplome'])) ?>
                    </span>
                    <span style="font-size:0.75rem;color:var(--text-muted);margin-left:6px">
                      <?= (int) $m['annee_soutenance'] ?>
                    </span>
                  </div>

                  <!-- Titre -->
                  <h6 class="fw-bold mb-1" style="color:var(--text-main);line-height:1.4">
                    <?= htmlspecialchars($m['titre']) ?>
                  </h6>

                  <!-- Thème -->
                  <p style="font-size:0.82rem;color:var(--text-muted);margin-bottom:12px;flex-grow:1">
                    <?= htmlspecialchars(
                        mb_strlen($m['theme']) > 80
                        ? mb_substr($m['theme'], 0, 80) . '…'
                        : $m['theme']
                    ) ?>
                  </p>

                  <!-- Auteur + filière -->
                  <div style="font-size:0.8rem;color:var(--text-muted);margin-bottom:12px">
                    <i class="bi bi-person me-1"></i>
                    <?= htmlspecialchars($m['nom_etudiant']) ?>
                    <?php if ($m['nom_filiere']): ?>
                      · <i class="bi bi-book me-1"></i><?= htmlspecialchars($m['nom_filiere']) ?>
                    <?php endif; ?>
                  </div>

                  <!-- Stats + actions -->
                  <div class="d-flex align-items-center justify-content-between">
                    <div style="font-size:0.82rem;color:var(--text-muted)">
                      <i class="bi bi-chat me-1"></i><?= (int) $m['nb_commentaires'] ?>
                      <span class="mx-2">·</span>
                      <i class="bi bi-heart<?= $aLike ? '-fill' : '' ?> me-1"
                         style="color:<?= $aLike ? 'var(--danger)' : 'inherit' ?>">
                      </i><?= (int) $m['nb_likes'] ?>
                    </div>
                    <a href="/views/commentateur/consulter_memoire.php?id=<?= $m['id_memoire'] ?>"
                       class="btn btn-uatm btn-sm">
                      Consulter
                    </a>
                  </div>

                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>