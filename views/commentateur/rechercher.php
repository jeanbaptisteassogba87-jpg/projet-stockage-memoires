<?php

require_once __DIR__ . '/../layout/header.php';
?>

<div class="container py-4">

    <!-- TITRE -->
    <div class="text-center mb-4">
        <h2 class="fw-bold text-primary">
            <i class="bi bi-search me-2"></i>Recherche de mémoires
        </h2>
        <p class="text-muted">
            Consultez les mémoires de l'UATM GASA Formation.
        </p>
    </div>

    <!-- FORMULAIRE DE RECHERCHE -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET"
                  action="/views/commentateur/dashboard.php"
                  id="formRecherche"
                  autocomplete="off">

                <input type="hidden" name="action" value="recherche">

                <div class="row g-3 align-items-end">

                    <!-- Mot-clé -->
                    <div class="col-12 col-md-5 position-relative">
                        <label for="mot_cle" class="form-label fw-semibold">
                            <i class="bi bi-keyboard me-1"></i>Mot-clé
                        </label>
                        <input
                            type="text"
                            class="form-control"
                            id="mot_cle"
                            name="mot_cle"
                            placeholder="Titre, thème, auteur..."
                            value="<?= htmlspecialchars($motCle ?? '') ?>"
                        >
                        <!-- Suggestions AJAX (recherche.js) -->
                        <ul class="list-group position-absolute w-100 shadow z-3 d-none"
                            id="suggestions"
                            style="top:100%; left:0; z-index:1000;">
                        </ul>
                    </div>

                    <!-- Filière -->
                    <div class="col-6 col-md-2">
                        <label for="filiere" class="form-label fw-semibold">
                            <i class="bi bi-diagram-3 me-1"></i>Filière
                        </label>
                        <select class="form-select" id="filiere" name="filiere">
                            <option value="">Toutes</option>
                            <?php foreach ($filieres as $f): ?>
                                <option value="<?= htmlspecialchars($f) ?>"
                                    <?= ($filiere === $f) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($f) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Niveau -->
                    <div class="col-6 col-md-2">
                        <label for="niveau" class="form-label fw-semibold">
                            <i class="bi bi-mortarboard me-1"></i>Niveau
                        </label>
                        <select class="form-select" id="niveau" name="niveau">
                            <option value="">Tous</option>
                            <?php foreach ($niveaux as $n): ?>
                                <option value="<?= htmlspecialchars($n) ?>"
                                    <?= ($niveau === $n) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($n) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Année -->
                    <div class="col-6 col-md-2">
                        <label for="annee" class="form-label fw-semibold">
                            <i class="bi bi-calendar me-1"></i>Année
                        </label>
                        <select class="form-select" id="annee" name="annee">
                            <option value="">Toutes</option>
                            <?php foreach ($annees as $a): ?>
                                <option value="<?= (int)$a ?>"
                                    <?= ((int)$annee === (int)$a) ? 'selected' : '' ?>>
                                    <?= (int)$a ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Bouton recherche -->
                    <div class="col-6 col-md-1">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>

                </div>

                <?php if ($motCle || $filiere || $annee || $niveau): ?>
                    <div class="mt-2">
                        <a href="/views/commentateur/dashboard.php?action=recherche"
                           class="text-secondary small">
                            <i class="bi bi-x-circle me-1"></i>Réinitialiser les filtres
                        </a>
                    </div>
                <?php endif; ?>

            </form>
        </div>
    </div>

    <!-- RÉSULTATS -->
    <?php if ($motCle || $filiere || $annee || $niveau): ?>

        <p class="text-muted mb-3">
            <strong><?= $totalResultats ?></strong>
            résultat<?= $totalResultats > 1 ? 's' : '' ?> trouvé<?= $totalResultats > 1 ? 's' : '' ?>
        </p>

        <?php if (empty($memoires)): ?>

            <div class="alert alert-info text-center">
                <i class="bi bi-info-circle me-2"></i>
                Aucun mémoire ne correspond à votre recherche.
                <br><small>Essayez d'autres mots-clés ou filtres.</small>
            </div>

        <?php else: ?>

            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4"
                 id="listeMemoires">

                <?php foreach ($memoires as $m): ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm border-0 carte-memoire">

                            <!-- En-tête -->
                            <div class="card-header bg-primary text-white
                                        d-flex justify-content-between align-items-center">
                                <span class="badge bg-white text-primary fw-bold">
                                    <?= htmlspecialchars($m['type_diplome'] ?? $m['niveau'] ?? '') ?>
                                </span>
                                <small><?= htmlspecialchars($m['filiere'] ?? '') ?></small>
                            </div>

                            <div class="card-body d-flex flex-column">

                                <!-- Titre -->
                                <h6 class="card-title fw-bold text-dark mb-1">
                                    <?= htmlspecialchars($m['titre']) ?>
                                </h6>

                                <!-- Auteur + Année -->
                                <p class="text-muted small mb-2">
                                    <i class="bi bi-person me-1"></i>
                                    <?= htmlspecialchars($m['auteur_nom']) ?>
                                    &nbsp;|&nbsp;
                                    <i class="bi bi-calendar2 me-1"></i>
                                    <?= (int)($m['annee_soutenance'] ?? 0) ?>
                                </p>

                                <!-- Thème -->
                                <?php if (!empty($m['theme'])): ?>
                                    <p class="card-text text-secondary small flex-grow-1">
                                        <?= htmlspecialchars(
                                            mb_strimwidth($m['theme'], 0, 120, '...')
                                        ) ?>
                                    </p>
                                <?php endif; ?>

                            </div>

                            <!-- Pied de carte -->
                            <div class="card-footer d-flex justify-content-between
                                        align-items-center bg-white border-top">
                                <div class="d-flex gap-3 text-muted small">
                                    <span>
                                        <i class="bi bi-heart me-1"></i>
                                        <?= (int)($m['nb_likes'] ?? 0) ?>
                                    </span>
                                    <span>
                                        <i class="bi bi-chat-dots me-1"></i>
                                        <?= (int)($m['nb_commentaires'] ?? 0) ?>
                                    </span>
                                </div>
                                <a href="/views/commentateur/dashboard.php?action=consulter&id=<?= (int)$m['id'] ?>"
                                   class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye me-1"></i>Consulter
                                </a>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    <?php else: ?>

        <!-- Invitation initiale -->
        <div class="text-center py-5 text-muted">
            <i class="bi bi-book-half" style="font-size:4rem; opacity:.3;"></i>
            <p class="mt-3 fs-5">
                Utilisez les filtres ci-dessus pour trouver un mémoire.
            </p>
            <p class="small">Cherchez par titre, thème, auteur, filière, niveau ou année.</p>
        </div>

    <?php endif; ?>

</div>

<script src="/js/recherche.js"></script>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>