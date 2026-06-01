<?php

// Rôle : permet au technicien d'importer des mémoires anciens en masse (plusieurs PDF + métadonnées en une fois)
// Chaque ligne = un mémoire avec son PDF     


$pageTitle = 'Importer des mémoires — UATM GASA Formation';

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../dao/EtudiantDAO.php';

requireRole(ROLE_TECHNICIEN);

$etudiantDAO = new EtudiantDAO();
$etudiants   = $etudiantDAO->listerParCentre((int) $_SESSION['centre_id']);

// Messages feedback
$successMessages = [
    'ok'      => 'Tous les mémoires ont été importés avec succès.',
    'partiel' => 'Import partiel : certains fichiers ont échoué.',
];
$errorMessages = [
    'champs_vides' => 'Merci de remplir tous les champs et de joindre les fichiers PDF.',
];

$successMsg = '';
$errorMsg   = '';

if (!empty($_GET['success'])) {
    $s = $_GET['success'];
    if ($s === 'partiel' && isset($_GET['erreurs'])) {
        $successMsg = 'Import partiel : ' . (int) $_GET['erreurs'] . ' fichier(s) ont échoué.';
    } else {
        $successMsg = $successMessages[$s] ?? '';
    }
}
if (!empty($_GET['error'])) {
    $errorMsg = $errorMessages[$_GET['error']] ?? 'Erreur inconnue.';
}
?>
<?php require_once __DIR__ . '/../layout/header.php'; ?>
<?php require_once __DIR__ . '/../layout/navbar.php'; ?>

<div class="container-fluid">
  <div class="row">

    <!-- Sidebar -->
    <div class="col-md-2 px-0 sidebar">
      <nav class="nav flex-column pt-3">
        <a class="nav-link" href="/views/technicien/dashboard.php">
          <i class="bi bi-speedometer2"></i> Tableau de bord
        </a>
        <a class="nav-link" href="/views/technicien/gerer_comptes.php">
          <i class="bi bi-people"></i> Gérer les comptes
        </a>
        <a class="nav-link active" href="/views/technicien/importer_memoires.php">
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

      <h2 class="section-title">Importer des mémoires anciens</h2>

      <!-- Explication -->
      <div class="alert mb-4"
           style="background:#EEF2F8;border-left:4px solid var(--primary);border-radius:var(--radius)">
        <i class="bi bi-info-circle me-2" style="color:var(--primary)"></i>
        Utilisez ce formulaire pour importer des mémoires existants (archives).
        Chaque mémoire importé sera automatiquement marqué comme <strong>publié</strong>.
        Ajoutez autant de lignes que nécessaire avec le bouton <strong>+ Ajouter un mémoire</strong>.
      </div>

      <?php if ($successMsg): ?>
        <div class="alert alert-success mb-4">
          <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($successMsg) ?>
        </div>
      <?php endif; ?>

      <?php if ($errorMsg): ?>
        <div class="alert alert-danger mb-4">
          <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($errorMsg) ?>
        </div>
      <?php endif; ?>

      <div class="card">
        <div class="card-header-uatm">
          <i class="bi bi-upload me-2"></i>Formulaire d'import
        </div>
        <div class="card-body p-4">

          <form method="POST"
                action="/controllers/TechnicienController.php"
                enctype="multipart/form-data"
                id="form-import">

            <input type="hidden" name="action" value="importer_memoires">

            <!-- Conteneur des lignes de mémoires -->
            <div id="lignes-memoires">
              <!-- Ligne 1 (par défaut) -->
              <div class="ligne-memoire border rounded p-3 mb-3 position-relative"
                   style="background:var(--bg-page)">

                <div class="d-flex justify-content-between align-items-center mb-3">
                  <span class="fw-bold" style="color:var(--primary);font-size:0.9rem">
                    Mémoire #<span class="num-ligne">1</span>
                  </span>
                  <!-- Bouton supprimer la ligne (caché sur la première) -->
                  <button type="button"
                          class="btn btn-sm btn-outline-danger btn-supprimer"
                          style="display:none"
                          onclick="supprimerLigne(this)">
                    <i class="bi bi-trash3"></i>
                  </button>
                </div>

                <div class="row g-3">

                  <!-- Étudiant -->
                  <div class="col-md-4">
                    <label style="font-size:0.85rem">
                      Étudiant <span style="color:var(--danger)">*</span>
                    </label>
                    <select name="etudiants_id[]" class="form-select form-select-sm mt-1" required>
                      <option value="">— Choisir —</option>
                      <?php foreach ($etudiants as $e): ?>
                        <option value="<?= $e['id_utilisateur'] ?>">
                          <?= htmlspecialchars($e['nom']) ?>
                          (<?= htmlspecialchars($e['numero_etudiant']) ?>)
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <!-- Titre -->
                  <div class="col-md-8">
                    <label style="font-size:0.85rem">
                      Titre <span style="color:var(--danger)">*</span>
                    </label>
                    <input type="text"
                           name="titres[]"
                           class="form-control form-control-sm mt-1"
                           placeholder="Titre du mémoire"
                           maxlength="255"
                           required>
                  </div>

                  <!-- Thème -->
                  <div class="col-md-6">
                    <label style="font-size:0.85rem">
                      Thème <span style="color:var(--danger)">*</span>
                    </label>
                    <input type="text"
                           name="themes[]"
                           class="form-control form-control-sm mt-1"
                           placeholder="Thème / domaine"
                           maxlength="255"
                           required>
                  </div>

                  <!-- Type de diplôme -->
                  <div class="col-md-3">
                    <label style="font-size:0.85rem">
                      Type <span style="color:var(--danger)">*</span>
                    </label>
                    <select name="types_diplome[]"
                            class="form-select form-select-sm mt-1" required>
                      <option value="licence">Licence</option>
                      <option value="master">Master</option>
                    </select>
                  </div>

                  <!-- Année -->
                  <div class="col-md-3">
                    <label style="font-size:0.85rem">
                      Année <span style="color:var(--danger)">*</span>
                    </label>
                    <input type="number"
                           name="annees[]"
                           class="form-control form-control-sm mt-1"
                           value="<?= date('Y') ?>"
                           min="2000"
                           max="<?= date('Y') ?>"
                           required>
                  </div>

                  <!-- Fichier PDF -->
                  <div class="col-12">
                    <label style="font-size:0.85rem">
                      Fichier PDF <span style="color:var(--danger)">*</span>
                    </label>
                    <input type="file"
                           name="fichiers_pdf[]"
                           class="form-control form-control-sm mt-1"
                           accept=".pdf,application/pdf"
                           required>
                    <small style="color:var(--text-muted)">Format PDF uniquement — max 10 Mo</small>
                  </div>

                </div>
              </div>
            </div>

            <!-- Bouton ajouter une ligne -->
            <button type="button"
                    class="btn btn-outline-secondary btn-sm mb-4"
                    onclick="ajouterLigne()">
              <i class="bi bi-plus-circle me-1"></i> Ajouter un mémoire
            </button>

            <!-- Soumettre -->
            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-uatm" id="btn-importer">
                <i class="bi bi-cloud-upload me-1"></i> Lancer l'import
              </button>
              <a href="/views/technicien/dashboard.php"
                 class="btn btn-outline-secondary">
                Annuler
              </a>
            </div>

          </form>
        </div>
      </div>

    </div>
  </div>
</div>

<script>
// ── Template d'une ligne de mémoire ─────────────────────────
// Construit dynamiquement en JS pour éviter la duplication HTML
function getLigneHtml(numero) {
  // Liste des étudiants pour le select
  const options = <?= json_encode(
      array_map(fn($e) => [
          'id'  => $e['id_utilisateur'],
          'nom' => $e['nom'] . ' (' . $e['numero_etudiant'] . ')',
      ], $etudiants)
  ) ?>;

  let optionsHtml = '<option value="">— Choisir —</option>';
  options.forEach(function(e) {
    optionsHtml += `<option value="${e.id}">${e.nom}</option>`;
  });

  const anneeActuelle = new Date().getFullYear();

  return `
    <div class="ligne-memoire border rounded p-3 mb-3 position-relative"
         style="background:var(--bg-page)">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <span class="fw-bold" style="color:var(--primary);font-size:0.9rem">
          Mémoire #<span class="num-ligne">${numero}</span>
        </span>
        <button type="button"
                class="btn btn-sm btn-outline-danger btn-supprimer"
                onclick="supprimerLigne(this)">
          <i class="bi bi-trash3"></i>
        </button>
      </div>
      <div class="row g-3">
        <div class="col-md-4">
          <label style="font-size:0.85rem">Étudiant <span style="color:var(--danger)">*</span></label>
          <select name="etudiants_id[]" class="form-select form-select-sm mt-1" required>
            ${optionsHtml}
          </select>
        </div>
        <div class="col-md-8">
          <label style="font-size:0.85rem">Titre <span style="color:var(--danger)">*</span></label>
          <input type="text" name="titres[]" class="form-control form-control-sm mt-1"
                 placeholder="Titre du mémoire" maxlength="255" required>
        </div>
        <div class="col-md-6">
          <label style="font-size:0.85rem">Thème <span style="color:var(--danger)">*</span></label>
          <input type="text" name="themes[]" class="form-control form-control-sm mt-1"
                 placeholder="Thème / domaine" maxlength="255" required>
        </div>
        <div class="col-md-3">
          <label style="font-size:0.85rem">Type <span style="color:var(--danger)">*</span></label>
          <select name="types_diplome[]" class="form-select form-select-sm mt-1" required>
            <option value="licence">Licence</option>
            <option value="master">Master</option>
          </select>
        </div>
        <div class="col-md-3">
          <label style="font-size:0.85rem">Année <span style="color:var(--danger)">*</span></label>
          <input type="number" name="annees[]" class="form-control form-control-sm mt-1"
                 value="${anneeActuelle}" min="2000" max="${anneeActuelle}" required>
        </div>
        <div class="col-12">
          <label style="font-size:0.85rem">Fichier PDF <span style="color:var(--danger)">*</span></label>
          <input type="file" name="fichiers_pdf[]" class="form-control form-control-sm mt-1"
                 accept=".pdf,application/pdf" required>
          <small style="color:var(--text-muted)">Format PDF uniquement — max 10 Mo</small>
        </div>
      </div>
    </div>
  `;
}

// Compteur de lignes
let nbLignes = 1;

// Ajoute une nouvelle ligne de mémoire
function ajouterLigne() {
  nbLignes++;
  const conteneur = document.getElementById('lignes-memoires');
  conteneur.insertAdjacentHTML('beforeend', getLigneHtml(nbLignes));
  renuméroter();
}

// Supprime une ligne
function supprimerLigne(btn) {
  btn.closest('.ligne-memoire').remove();
  nbLignes--;
  renuméroter();
}

// Remet les numéros à jour après suppression
function renuméroter() {
  document.querySelectorAll('.num-ligne').forEach(function(el, i) {
    el.textContent = i + 1;
  });
}

// Désactiver le bouton submit pendant l'upload
document.getElementById('form-import').addEventListener('submit', function () {
  const btn = document.getElementById('btn-importer');
  btn.disabled  = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Import en cours…';
});
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>