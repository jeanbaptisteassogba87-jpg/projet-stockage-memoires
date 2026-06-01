<?php
// Rôle : permet au technicien d'importer des utilisateurs en masse via un fichier CSV
// Format CSV : nom,email,role,centre_id
// Chaque utilisateur importé reçoit un mot de passe temporaire et devra le changer à la première connexion 

$pageTitle = 'Importer des utilisateurs — UATM GASA Formation';

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/constants.php';

requireRole(ROLE_TECHNICIEN);

// Messages feedback
$successMessages = [
    'ok'      => 'Tous les utilisateurs ont été importés avec succès.',
    'partiel' => 'Import partiel — voir les détails ci-dessous.',
];
$errorMessages = [
    'fichier_manquant' => 'Merci de joindre un fichier CSV.',
    'pas_csv'          => 'Le fichier doit être au format CSV.',
    'csv_vide'         => 'Le fichier CSV est vide.',
    'bdd'              => 'Erreur lors de l\'enregistrement en base de données.',
];

$successMsg = !empty($_GET['success']) ? ($successMessages[$_GET['success']] ?? '') : '';
$errorMsg   = !empty($_GET['error'])   ? ($errorMessages[$_GET['error']]   ?? '') : '';

// Résumé de l'import (passé en session par le controller)
$resumeImport = $_SESSION['resume_import'] ?? null;
if ($resumeImport) {
    unset($_SESSION['resume_import']);
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
        <a class="nav-link" href="/views/technicien/importer_memoires.php">
          <i class="bi bi-file-earmark-arrow-up"></i> Importer mémoires
        </a>
        <a class="nav-link active" href="/views/technicien/importer_utilisateurs.php">
          <i class="bi bi-person-plus"></i> Importer utilisateurs
        </a>
        <a class="nav-link" href="/views/commentateur/rechercher.php">
          <i class="bi bi-search"></i> Consulter mémoires
        </a>
      </nav>
    </div>

    <!-- Contenu -->
    <div class="col-md-10 p-4">

      <h2 class="section-title">Importer des utilisateurs via CSV</h2>

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

      <!-- Résumé de l'import précédent -->
      <?php if ($resumeImport): ?>
        <div class="card mb-4" style="border-left:4px solid var(--info)">
          <div class="card-body">
            <h6 class="fw-bold mb-3" style="color:var(--primary)">
              <i class="bi bi-clipboard-data me-2"></i>Résumé du dernier import
            </h6>
            <div class="row g-3">
              <div class="col-md-3 text-center">
                <div style="font-size:1.8rem;font-weight:700;color:var(--success)">
                  <?= (int) $resumeImport['success'] ?>
                </div>
                <div style="font-size:0.82rem;color:var(--text-muted)">Importés</div>
              </div>
              <div class="col-md-3 text-center">
                <div style="font-size:1.8rem;font-weight:700;color:var(--danger)">
                  <?= (int) $resumeImport['erreurs'] ?>
                </div>
                <div style="font-size:0.82rem;color:var(--text-muted)">Erreurs</div>
              </div>
              <div class="col-md-3 text-center">
                <div style="font-size:1.8rem;font-weight:700;color:var(--warning)">
                  <?= (int) $resumeImport['doublons'] ?>
                </div>
                <div style="font-size:0.82rem;color:var(--text-muted)">Doublons ignorés</div>
              </div>
              <div class="col-md-3 text-center">
                <div style="font-size:1.8rem;font-weight:700;color:var(--primary)">
                  <?= (int) $resumeImport['total'] ?>
                </div>
                <div style="font-size:0.82rem;color:var(--text-muted)">Total lignes</div>
              </div>
            </div>
            <?php if (!empty($resumeImport['details'])): ?>
              <hr style="border-color:var(--border);margin:12px 0">
              <div style="font-size:0.82rem;color:var(--danger)">
                <?php foreach ($resumeImport['details'] as $d): ?>
                  <div><i class="bi bi-x-circle me-1"></i><?= htmlspecialchars($d) ?></div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>

      <div class="row g-4">

        <!-- Formulaire d'upload -->
        <div class="col-md-7">
          <div class="card">
            <div class="card-header-uatm">
              <i class="bi bi-file-earmark-spreadsheet me-2"></i>Uploader un fichier CSV
            </div>
            <div class="card-body p-4">

              <form method="POST"
                    action="/controllers/TechnicienController.php"
                    enctype="multipart/form-data"
                    id="form-csv">

                <input type="hidden" name="action" value="importer_utilisateurs">

                <!-- Zone upload CSV -->
                <div class="mb-4">
                  <label for="fichier_csv" class="mb-2">
                    Fichier CSV <span style="color:var(--danger)">*</span>
                  </label>
                  <div id="zone-csv"
                       style="border:2px dashed var(--border);border-radius:var(--radius);
                              padding:28px;text-align:center;cursor:pointer;
                              transition:border-color 0.2s,background 0.2s"
                       onclick="document.getElementById('fichier_csv').click()"
                       ondragover="csvDragOver(event)"
                       ondragleave="csvDragLeave(event)"
                       ondrop="csvDrop(event)">
                    <i class="bi bi-filetype-csv"
                       style="font-size:2.5rem;color:var(--text-muted)"></i>
                    <div style="margin-top:8px;color:var(--text-muted)" id="csv-texte">
                      Cliquez ou glissez votre fichier CSV ici
                    </div>
                    <input type="file"
                           name="fichier_csv"
                           id="fichier_csv"
                           accept=".csv,text/csv"
                           style="display:none"
                           required
                           onchange="afficherCsv(this)">
                  </div>
                </div>

                <!-- Mot de passe temporaire -->
                <div class="mb-4">
                  <label for="mdp_temp" class="mb-1">
                    Mot de passe temporaire
                    <small style="color:var(--text-muted)">(commun à tous les comptes importés)</small>
                  </label>
                  <input type="text"
                         name="mdp_temporaire"
                         id="mdp_temp"
                         class="form-control"
                         value="Uatm2024!"
                         required>
                  <small style="color:var(--text-muted)">
                    Chaque utilisateur devra le changer à sa première connexion.
                  </small>
                </div>

                <div class="d-flex gap-2">
                  <button type="submit" class="btn btn-uatm" id="btn-importer-csv">
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

        <!-- Instructions + modèle CSV -->
        <div class="col-md-5">

          <div class="card mb-3">
            <div class="card-header-uatm">
              <i class="bi bi-question-circle me-2"></i>Format attendu
            </div>
            <div class="card-body">
              <p style="font-size:0.88rem;margin-bottom:12px">
                Le fichier CSV doit avoir une ligne d'en-tête et les colonnes suivantes :
              </p>
              <div style="background:#1e1e2e;border-radius:var(--radius);
                          padding:14px;font-family:monospace;font-size:0.8rem;
                          color:#cdd6f4;overflow-x:auto">
                <div style="color:#89b4fa">nom,email,role,centre_id,niveau_etude,filiere_id,numero_etudiant</div>
                <div style="color:#a6e3a1;margin-top:6px">Kouassi Marc,marc@uatm.bj,etudiant,1,L3,2,ETU2024010</div>
                <div style="color:#a6e3a1">Akpo Serge,serge@uatm.bj,professeur,1,,,</div>
                <div style="color:#a6e3a1">Mensah Julie,julie@uatm.bj,etudiant,1,M2,1,ETU2024011</div>
              </div>
              <ul style="font-size:0.82rem;margin-top:12px;padding-left:18px;
                         color:var(--text-muted)">
                <li><code>role</code> : etudiant, professeur, directeur, technicien</li>
                <li><code>niveau_etude</code> : L1, L2, L3, M1, M2 (étudiants uniquement)</li>
                <li><code>filiere_id</code> et <code>numero_etudiant</code> : étudiants uniquement</li>
                <li>Les colonnes vides sont acceptées pour les non-étudiants</li>
                <li>Les emails déjà existants sont ignorés (doublons)</li>
              </ul>
            </div>
          </div>

          <!-- Télécharger un modèle -->
          <div class="card">
            <div class="card-body text-center py-3">
              <i class="bi bi-download" style="font-size:1.5rem;color:var(--primary)"></i>
              <p style="font-size:0.88rem;margin:8px 0">
                Téléchargez le modèle CSV pré-rempli pour commencer.
              </p>
              <a href="/scripts/modele_import_utilisateurs.csv"
                 class="btn btn-outline-secondary btn-sm"
                 download>
                <i class="bi bi-file-earmark-spreadsheet me-1"></i>
                Modèle CSV
              </a>
            </div>
          </div>

        </div>
      </div>

    </div>
  </div>
</div>

<script>
function csvDragOver(e) {
  e.preventDefault();
  const z = document.getElementById('zone-csv');
  z.style.borderColor = 'var(--primary)';
  z.style.background  = '#EEF2F8';
}
function csvDragLeave(e) {
  const z = document.getElementById('zone-csv');
  z.style.borderColor = 'var(--border)';
  z.style.background  = '';
}
function csvDrop(e) {
  e.preventDefault();
  csvDragLeave(e);
  const input = document.getElementById('fichier_csv');
  const dt    = new DataTransfer();
  dt.items.add(e.dataTransfer.files[0]);
  input.files = dt.files;
  afficherCsv(input);
}

function afficherCsv(input) {
  if (!input.files.length) return;
  const f     = input.files[0];
  const zone  = document.getElementById('zone-csv');
  const texte = document.getElementById('csv-texte');

  zone.style.borderColor = 'var(--success)';
  zone.style.background  = '#F0FFF4';
  texte.innerHTML =
    '<i class="bi bi-filetype-csv" style="font-size:1.5rem;color:var(--success)"></i>' +
    '<div style="margin-top:6px;font-weight:500">' + f.name + '</div>' +
    '<small style="color:var(--text-muted)">' +
      (f.size / 1024).toFixed(1) + ' Ko' +
    '</small>';
}

document.getElementById('form-csv').addEventListener('submit', function () {
  const btn = document.getElementById('btn-importer-csv');
  btn.disabled  = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Import en cours…';
});
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>