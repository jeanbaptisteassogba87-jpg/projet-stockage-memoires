<?php

// Rôle : page de consultation détaillée d'un mémoire publié
//        - infos complètes du mémoire
//        - visionneuse PDF intégrée
//        - bouton like / unlike
//        - section commentaires (ajouter + liste)


$pageTitle = 'Consulter un mémoire — UATM GASA Formation';

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../dao/MemoirePublicDAO.php';
require_once __DIR__ . '/../../dao/CommentaireDAO.php';
require_once __DIR__ . '/../../dao/LikeDAO.php';

requireAuth();

$id = (int) ($_GET['id'] ?? 0);
if (!$id) {
    header('Location: /views/commentateur/rechercher.php');
    exit;
}

$dao          = new MemoirePublicDAO();
$commentaireDAO = new CommentaireDAO();
$likeDAO      = new LikeDAO();
$userId       = (int) $_SESSION['user_id'];

// Charger le mémoire — uniquement s'il est publié
$memoire = $dao->trouverPublie($id);
if (!$memoire) {
    header('Location: /views/commentateur/rechercher.php?error=introuvable');
    exit;
}

// Charger les commentaires et l'état du like
$commentaires = $commentaireDAO->listerParMemoire($id);
$aLike        = $likeDAO->aDejaLike($id, $userId);

// Messages feedback
$successMessages = [
    'commentaire_ok' => 'Commentaire ajouté.',
];
$errorMessages = [
    'commentaire_vide' => 'Le commentaire ne peut pas être vide.',
    'trop_long'        => 'Le commentaire dépasse 2000 caractères.',
    'acces_refuse'     => 'Accès refusé.',
];
$successMsg = !empty($_GET['success']) ? ($successMessages[$_GET['success']] ?? '') : '';
$errorMsg   = !empty($_GET['error'])   ? ($errorMessages[$_GET['error']]   ?? '') : '';

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
        <?php elseif ($role === ROLE_PROFESSEUR): ?>
          <a class="nav-link" href="/views/professeur/dashboard.php">
            <i class="bi bi-speedometer2"></i> Tableau de bord
          </a>
        <?php elseif ($role === ROLE_DIRECTEUR): ?>
          <a class="nav-link" href="/views/directeur/dashboard.php">
            <i class="bi bi-speedometer2"></i> Tableau de bord
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

      <!-- Fil d'ariane -->
      <nav style="font-size:0.85rem;margin-bottom:12px">
        <a href="/views/commentateur/rechercher.php"
           style="color:var(--primary);text-decoration:none">
          ← Retour à la recherche
        </a>
      </nav>

      <?php if ($successMsg): ?>
        <div class="alert alert-success mb-3">
          <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($successMsg) ?>
        </div>
      <?php endif; ?>
      <?php if ($errorMsg): ?>
        <div class="alert alert-danger mb-3">
          <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($errorMsg) ?>
        </div>
      <?php endif; ?>

      <div class="row g-4">

        <!-- ── Colonne gauche : infos + like + commentaires ── -->
        <div class="col-md-5">

          <!-- Carte infos -->
          <div class="card mb-4">
            <div class="card-header-uatm">
              <i class="bi bi-info-circle me-2"></i>Informations
            </div>
            <div class="card-body">

              <!-- Badge type + année -->
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

              <hr style="border-color:var(--border)">

              <!-- Auteur -->
              <div class="mb-2" style="font-size:0.88rem">
                <span style="color:var(--text-muted)">Auteur</span><br>
                <strong><?= htmlspecialchars($memoire['nom_etudiant']) ?></strong>
              </div>

              <!-- Filière -->
              <?php if ($memoire['nom_filiere']): ?>
                <div class="mb-2" style="font-size:0.88rem">
                  <span style="color:var(--text-muted)">Filière</span><br>
                  <strong><?= htmlspecialchars($memoire['nom_filiere']) ?></strong>
                </div>
              <?php endif; ?>

              <!-- Centre -->
              <?php if ($memoire['nom_centre']): ?>
                <div class="mb-2" style="font-size:0.88rem">
                  <span style="color:var(--text-muted)">Centre</span><br>
                  <strong><?= htmlspecialchars($memoire['nom_centre']) ?></strong>
                </div>
              <?php endif; ?>

              <!-- Professeur validateur -->
              <?php if ($memoire['nom_professeur']): ?>
                <div class="mb-2" style="font-size:0.88rem">
                  <span style="color:var(--text-muted)">Validé par</span><br>
                  <strong><?= htmlspecialchars($memoire['nom_professeur']) ?></strong>
                </div>
              <?php endif; ?>

              <hr style="border-color:var(--border)">

              <!-- Stats likes / commentaires + bouton like -->
              <div class="d-flex align-items-center justify-content-between">
                <div style="font-size:0.9rem;color:var(--text-muted)">
                  <i class="bi bi-heart-fill me-1" style="color:var(--danger)"></i>
                  <span id="nb-likes"><?= (int) $memoire['nb_likes'] ?></span> like<?= (int) $memoire['nb_likes'] > 1 ? 's' : '' ?>
                  <span class="mx-2">·</span>
                  <i class="bi bi-chat me-1"></i>
                  <?= count($commentaires) ?> commentaire<?= count($commentaires) > 1 ? 's' : '' ?>
                </div>

                <!-- Bouton like -->
                <form method="POST" action="/controllers/CommentateurController.php"
                      style="margin:0">
                  <input type="hidden" name="action"     value="toggler_like">
                  <input type="hidden" name="memoire_id" value="<?= $memoire['id_memoire'] ?>">
                  <input type="hidden" name="retour"
                         value="/views/commentateur/consulter_memoire.php?id=<?= $memoire['id_memoire'] ?>">
                  <button type="submit"
                          class="btn btn-sm <?= $aLike ? 'btn-danger' : 'btn-outline-danger' ?>"
                          title="<?= $aLike ? 'Retirer mon like' : 'Liker ce mémoire' ?>">
                    <i class="bi bi-heart<?= $aLike ? '-fill' : '' ?> me-1"></i>
                    <?= $aLike ? 'Je n\'aime plus' : 'J\'aime' ?>
                  </button>
                </form>
              </div>

            </div>
          </div>

          <!-- ── Section commentaires ── -->
          <div class="card" id="commentaires">
            <div class="card-header-uatm">
              <i class="bi bi-chat-text me-2"></i>
              Commentaires (<?= count($commentaires) ?>)
            </div>
            <div class="card-body">

              <!-- Formulaire d'ajout -->
              <form method="POST" action="/controllers/CommentateurController.php"
                    class="mb-4">
                <input type="hidden" name="action"     value="ajouter_commentaire">
                <input type="hidden" name="memoire_id" value="<?= $memoire['id_memoire'] ?>">
                <label for="contenu" style="font-size:0.88rem;margin-bottom:6px">
                  Laisser un commentaire
                </label>
                <textarea name="contenu"
                          id="contenu"
                          class="form-control"
                          rows="3"
                          maxlength="2000"
                          placeholder="Votre avis sur ce mémoire…"
                          required></textarea>
                <div class="d-flex justify-content-between align-items-center mt-2">
                  <small id="compteur-chars"
                         style="color:var(--text-muted)">0 / 2000</small>
                  <button type="submit" class="btn btn-uatm btn-sm">
                    <i class="bi bi-send me-1"></i> Publier
                  </button>
                </div>
              </form>

              <!-- Liste des commentaires -->
              <?php if (empty($commentaires)): ?>
                <p style="color:var(--text-muted);font-size:0.88rem;text-align:center">
                  Aucun commentaire pour l'instant. Soyez le premier !
                </p>
              <?php else: ?>
                <div style="max-height:400px;overflow-y:auto">
                  <?php foreach ($commentaires as $c): ?>
                    <div style="padding:12px 0;
                                border-bottom:1px solid var(--border)"
                         class="commentaire-item">

                      <!-- En-tête commentaire -->
                      <div class="d-flex justify-content-between align-items-start">
                        <div>
                          <strong style="font-size:0.88rem">
                            <?= htmlspecialchars($c['nom_auteur']) ?>
                          </strong>
                          <span style="font-size:0.75rem;
                                       color:var(--text-muted);
                                       margin-left:6px">
                            <?= htmlspecialchars(ucfirst($c['role_auteur'])) ?>
                          </span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                          <span style="font-size:0.75rem;color:var(--text-muted)">
                            <?= date('d/m/Y à H:i', strtotime($c['date_creation'])) ?>
                          </span>
                          <!-- Bouton supprimer (auteur uniquement) -->
                          <?php if ((int) $c['utilisateur_id'] === $userId): ?>
                            <form method="POST"
                                  action="/controllers/CommentateurController.php"
                                  style="margin:0">
                              <input type="hidden" name="action"
                                     value="supprimer_commentaire">
                              <input type="hidden" name="id_commentaire"
                                     value="<?= $c['id_commentaire'] ?>">
                              <input type="hidden" name="memoire_id"
                                     value="<?= $memoire['id_memoire'] ?>">
                              <button type="submit"
                                      class="btn btn-sm"
                                      style="padding:0 4px;color:var(--text-muted);
                                             background:none;border:none"
                                      title="Supprimer"
                                      data-confirm="Supprimer ce commentaire ?">
                                <i class="bi bi-trash3" style="font-size:0.8rem"></i>
                              </button>
                            </form>
                          <?php endif; ?>
                        </div>
                      </div>

                      <!-- Contenu -->
                      <p style="font-size:0.88rem;
                                margin:6px 0 0;
                                white-space:pre-line;
                                color:var(--text-main)">
                        <?= nl2br(htmlspecialchars($c['contenu'])) ?>
                      </p>

                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>

            </div>
          </div>

        </div>

        <!-- ── Colonne droite : visionneuse PDF ── -->
        <div class="col-md-7">
          <div class="card">
            <div class="card-header-uatm d-flex justify-content-between align-items-center">
              <span>
                <i class="bi bi-file-earmark-pdf me-2"></i>
                <?= htmlspecialchars(mb_substr($memoire['titre'], 0, 50)) ?>
              </span>
              <a href="/scripts/serve_pdf.php?id=<?= $memoire['id_memoire'] ?>"
                 target="_blank"
                 class="btn btn-sm"
                 style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.3);font-size:0.8rem">
                <i class="bi bi-box-arrow-up-right me-1"></i> Ouvrir
              </a>
            </div>
            <div class="card-body p-2">
              <div id="pdf-container">
                <iframe
                  src="/scripts/serve_pdf.php?id=<?= $memoire['id_memoire'] ?>"
                  width="100%"
                  height="750px"
                  style="border:none;border-radius:var(--radius)">
                  <p style="color:#fff;padding:20px">
                    Votre navigateur ne supporte pas l'affichage intégré.
                    <a href="/scripts/serve_pdf.php?id=<?= $memoire['id_memoire'] ?>"
                       style="color:var(--secondary)">
                      Télécharger le mémoire
                    </a>
                  </p>
                </iframe>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<script>
// Compteur de caractères pour le champ commentaire
const textarea  = document.getElementById('contenu');
const compteur  = document.getElementById('compteur-chars');

if (textarea && compteur) {
  textarea.addEventListener('input', function () {
    const nb = this.value.length;
    compteur.textContent = nb + ' / 2000';
    compteur.style.color = nb > 1800 ? 'var(--danger)' : 'var(--text-muted)';
  });
}
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>