<?php

require_once __DIR__ . '/../layout/header.php';

$userId = $_SESSION['user_id'];   
?>

<div class="container py-4">

    <!-- Fil d'Ariane -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/views/commentateur/dashboard.php?action=recherche">
                    <i class="bi bi-search me-1"></i>Recherche
                </a>
            </li>
            <li class="breadcrumb-item active">Consultation du mémoire</li>
        </ol>
    </nav>

    <!-- Message flash -->
    <?php if (!empty($messageFlash)): ?>
        <div class="alert alert-<?= $messageFlash['type'] === 'succes' ? 'success' : 'danger' ?>
                    alert-dismissible fade show" role="alert">
            <i class="bi bi-<?= $messageFlash['type'] === 'succes'
                ? 'check-circle' : 'exclamation-triangle' ?> me-2"></i>
            <?= htmlspecialchars($messageFlash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">

        
        <div class="col-lg-8">

            <!-- Fiche mémoire -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-primary text-white
                            d-flex justify-content-between align-items-start">
                    <div>
                        <h5 class="mb-1"><?= htmlspecialchars($memoire['titre']) ?></h5>
                        <small class="text-white-50">
                            <i class="bi bi-tag me-1"></i>
                            <?= htmlspecialchars($memoire['type_diplome'] ?? '') ?>
                        </small>
                    </div>
                    <span class="badge bg-success mt-1">Public</span>
                </div>

                <div class="card-body">
                    <div class="row g-3 mb-3">

                        <div class="col-sm-6">
                            <small class="text-muted text-uppercase fw-semibold"
                                   style="font-size:.7rem;">Auteur</small>
                            <p class="mb-0 fw-semibold">
                                <i class="bi bi-person-circle me-1 text-primary"></i>
                                <?= htmlspecialchars($memoire['auteur_nom']) ?>
                            </p>
                        </div>

                        <div class="col-sm-3">
                            <small class="text-muted text-uppercase fw-semibold"
                                   style="font-size:.7rem;">Filière</small>
                            <p class="mb-0 fw-semibold">
                                <?= htmlspecialchars($memoire['filiere'] ?? '—') ?>
                            </p>
                        </div>

                        <div class="col-sm-3">
                            <small class="text-muted text-uppercase fw-semibold"
                                   style="font-size:.7rem;">Niveau</small>
                            <p class="mb-0">
                                <span class="badge bg-secondary">
                                    <?= htmlspecialchars($memoire['niveau'] ?? '') ?>
                                </span>
                            </p>
                        </div>

                        <div class="col-sm-3">
                            <small class="text-muted text-uppercase fw-semibold"
                                   style="font-size:.7rem;">Année de soutenance</small>
                            <p class="mb-0 fw-semibold">
                                <?= (int)($memoire['annee_soutenance'] ?? 0) ?>
                            </p>
                        </div>

                        <div class="col-sm-3">
                            <small class="text-muted text-uppercase fw-semibold"
                                   style="font-size:.7rem;">Date de dépôt</small>
                            <p class="mb-0 fw-semibold">
                                <?= !empty($memoire['date_depot'])
                                    ? date('d/m/Y', strtotime($memoire['date_depot']))
                                    : '—' ?>
                            </p>
                        </div>

                    </div>

                    <?php if (!empty($memoire['theme'])): ?>
                        <hr>
                        <h6 class="fw-bold">Thème</h6>
                        <p class="text-secondary mb-0">
                            <?= nl2br(htmlspecialchars($memoire['theme'])) ?>
                        </p>
                    <?php endif; ?>

                    <!-- LIKES  (LIKE::ajouter() / LIKE::retirer()) -->
                    <hr>
                    <div class="d-flex align-items-center gap-3">
                        <span class="text-muted small">
                            <i class="bi bi-heart-fill text-danger me-1"></i>
                            <strong><?= $nbLikes ?></strong>
                            like<?= $nbLikes > 1 ? 's' : '' ?>
                        </span>

                        <?php if ($utilisateurALike): ?>
                            <form method="POST"
                                  action="/views/commentateur/dashboard.php?action=unlikeMemoire">
                                <input type="hidden" name="memoire_id"
                                       value="<?= (int)$memoire['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="bi bi-heart-fill me-1"></i>Je n'aime plus
                                </button>
                            </form>
                        <?php else: ?>
                            <form method="POST"
                                  action="/views/commentateur/dashboard.php?action=likerMemoire">
                                <input type="hidden" name="memoire_id"
                                       value="<?= (int)$memoire['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-heart me-1"></i>J'aime
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>

                </div>
            </div>

            <!-- VISIONNEUSE PDF SÉCURISÉE (pdf_viewer.js) -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-dark text-white
                            d-flex justify-content-between align-items-center">
                    <span>
                        <i class="bi bi-file-earmark-pdf me-2"></i>Lecture du mémoire
                    </span>
                    <span class="badge bg-warning text-dark small">
                        <i class="bi bi-lock-fill me-1"></i>Lecture seule
                    </span>
                </div>

                <div class="card-body p-0">
                    <div id="pdfViewer"
                         data-url="/views/commentateur/dashboard.php?action=voirPdf&id=<?= (int)$memoire['id'] ?>"
                         style="height:620px; background:#525659; position:relative;">

                        <!-- Barre de navigation -->
                        <div id="pdfToolbar"
                             class="d-flex align-items-center gap-2 px-3 py-2 bg-secondary text-white"
                             style="height:46px;">
                            <button class="btn btn-sm btn-light" id="btnPrevPage"
                                    disabled title="Page précédente">
                                <i class="bi bi-chevron-left"></i>
                            </button>
                            <span class="small">
                                Page&nbsp;<span id="pageActuelle">—</span>
                                &nbsp;/&nbsp;<span id="totalPages">—</span>
                            </span>
                            <button class="btn btn-sm btn-light" id="btnNextPage"
                                    disabled title="Page suivante">
                                <i class="bi bi-chevron-right"></i>
                            </button>
                            <div class="ms-auto d-flex gap-1">
                                <button class="btn btn-sm btn-light" id="btnZoomMoins"
                                        title="Zoom -">
                                    <i class="bi bi-zoom-out"></i>
                                </button>
                                <button class="btn btn-sm btn-light" id="btnZoomPlus"
                                        title="Zoom +">
                                    <i class="bi bi-zoom-in"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Canvas PDF.js -->
                        <div style="overflow-y:auto; height:calc(100% - 46px);">
                            <canvas id="pdfCanvas"
                                    style="display:block; margin:0 auto;"></canvas>
                        </div>

                        <!-- Overlay protecteur (anti-clic droit / drag) -->
                        <div id="pdfOverlay" style="
                            position:absolute; top:46px; left:0; right:0; bottom:0;
                            z-index:10; user-select:none; -webkit-user-select:none;">
                        </div>

                    </div>
                </div>
            </div>

        </div><!-- /col-lg-8 -->


        <div class="col-lg-4">

            <!-- Formulaire d'ajout de commentaire -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-chat-plus me-2"></i>Laisser un commentaire
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST"
                          action="/views/commentateur/dashboard.php?action=ajouterCommentaire"
                          id="formCommentaire">

                        <input type="hidden" name="memoire_id"
                               value="<?= (int)$memoire['id'] ?>">

                        <div class="mb-3">
                            <textarea
                                class="form-control"
                                id="contenu"
                                name="contenu"
                                rows="5"
                                maxlength="2000"
                                placeholder="Votre avis (10 à 2000 caractères)..."
                                required
                            ></textarea>
                            <div class="d-flex justify-content-end mt-1">
                                <small class="text-muted">
                                    <span id="compteurCaracteres">0</span>/2000
                                </small>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-send me-1"></i>Publier
                        </button>
                    </form>
                </div>
            </div>

            <!-- Liste des commentaires -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light
                            d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 text-dark">
                        <i class="bi bi-chat-dots me-2"></i>Commentaires
                    </h6>
                    <span class="badge bg-primary"><?= $nbCommentaires ?></span>
                </div>

                <div class="card-body p-2"
                     id="listeCommentaires"
                     style="max-height:500px; overflow-y:auto;">

                    <?php if (empty($commentaires)): ?>
                        <p class="text-center text-muted small py-3">
                            <i class="bi bi-chat-slash me-1"></i>
                            Aucun commentaire. Soyez le premier !
                        </p>

                    <?php else: ?>

                        <?php foreach ($commentaires as $c): ?>
                            <div class="card mb-2 border-0 bg-light"
                                 id="commentaire-<?= $c->getId() ?>">
                                <div class="card-body p-2">

                                    <!-- En-tête commentaire -->
                                    <div class="d-flex justify-content-between
                                                align-items-start mb-1">
                                        <div>
                                            <strong class="small">
                                                <i class="bi bi-person-circle me-1 text-primary"></i>
                                                <?= htmlspecialchars(
                                                    $c->getNomAuteur() ?? 'Anonyme'
                                                ) ?>
                                            </strong>
                                            <br>
                                            <small class="text-muted">
                                                <?= $c->getDateFormatee() ?>
                                            </small>
                                        </div>

                                        <!-- Actions réservées à l'auteur du commentaire -->
                                        <?php if ($c->getUtilisateurId() === $userId): ?>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-link
                                                               text-muted p-0"
                                                        data-bs-toggle="dropdown"
                                                        aria-label="Options">
                                                    <i class="bi bi-three-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu
                                                           dropdown-menu-end shadow">

                                                    <!-- Modifier -->
                                                    <li>
                                                        <button class="dropdown-item small"
                                                                onclick="ouvrirModification(
                                                                    <?= $c->getId() ?>,
                                                                    <?= (int)$memoire['id'] ?>,
                                                                    `<?= addslashes(
                                                                        htmlspecialchars(
                                                                            $c->getContenu()
                                                                        )
                                                                    ) ?>`
                                                                )">
                                                            <i class="bi bi-pencil me-1"></i>
                                                            Modifier
                                                        </button>
                                                    </li>

                                                    <!-- Supprimer -->
                                                    <li>
                                                        <form method="POST"
                                                              action="/views/commentateur/dashboard.php?action=supprimerCommentaire"
                                                              onsubmit="return confirm(
                                                                  'Supprimer ce commentaire ?'
                                                              )">
                                                            <input type="hidden"
                                                                   name="commentaire_id"
                                                                   value="<?= $c->getId() ?>">
                                                            <input type="hidden"
                                                                   name="memoire_id"
                                                                   value="<?= (int)$memoire['id'] ?>">
                                                            <button type="submit"
                                                                    class="dropdown-item
                                                                           small text-danger">
                                                                <i class="bi bi-trash me-1"></i>
                                                                Supprimer
                                                            </button>
                                                        </form>
                                                    </li>

                                                </ul>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Contenu (attribut de Commentaire.php) -->
                                    <p class="small mb-0 text-dark">
                                        <?= $c->getContenuHtml() ?>
                                    </p>

                                </div>
                            </div>
                        <?php endforeach; ?>

                    <?php endif; ?>

                </div>
            </div>

        </div>

    </div>
</div>

<!-- MODAL MODIFICATION COMMENTAIRE -->
<div class="modal fade" id="modalModifier" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content shadow">
            <form method="POST"
                  action="/views/commentateur/dashboard.php?action=modifierCommentaire">

                <input type="hidden" name="commentaire_id" id="modif_commentaire_id">
                <input type="hidden" name="memoire_id"     id="modif_memoire_id">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil me-2"></i>Modifier le commentaire
                    </h5>
                    <button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <textarea
                        class="form-control"
                        name="contenu"
                        id="modif_contenu"
                        rows="5"
                        maxlength="2000"
                        required
                    ></textarea>
                    <div class="d-flex justify-content-end mt-1">
                        <small class="text-muted">
                            <span id="modif_compteur">0</span>/2000
                        </small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Enregistrer
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- PDF.js CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script src="/js/pdf_viewer.js"></script>

<script>
// Compteur caractères – formulaire ajout
document.getElementById('contenu').addEventListener('input', function () {
    document.getElementById('compteurCaracteres').textContent = this.value.length;
});

// Compteur caractères – modal modification
document.getElementById('modif_contenu').addEventListener('input', function () {
    document.getElementById('modif_compteur').textContent = this.value.length;
});


function ouvrirModification(commentaireId, memoireId, contenu) {
    document.getElementById('modif_commentaire_id').value = commentaireId;
    document.getElementById('modif_memoire_id').value     = memoireId;
    document.getElementById('modif_contenu').value        = contenu;
    document.getElementById('modif_compteur').textContent = contenu.length;

    new bootstrap.Modal(document.getElementById('modalModifier')).show();
}
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>