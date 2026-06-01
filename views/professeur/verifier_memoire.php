<?php

// Rôle : page de vérification détaillée d'un mémoire
//        Le professeur peut :
//        - lire les infos complètes du mémoire
//        - visionner le PDF
//        - laisser une remarque sans décider
//        - valider ou rejeter avec remarque obligatoire


$pageTitle = 'Vérifier un mémoire — UATM GASA Formation';

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../dao/ProfesseurDAO.php';

requireRole(ROLE_PROFESSEUR);

$professeurId = (int) $_SESSION['user_id'];
$dao          = new ProfesseurDAO();

// Récupérer l'id depuis l'URL
$id = (int) ($_GET['id'] ?? 0);
if (!$id) {
    header('Location: /views/professeur/liste_memoires.php?error=id_manquant');
    exit;
}

// Charger le mémoire avec les infos étudiant
$memoire = $dao->trouverMemoireAvecEtudiant($id);
if (!$memoire) {
    header('Location: /views/professeur/liste_memoires.php?error=introuvable');
    exit;
}

// Vérifier que ce professeur est bien assigné à ce mémoire
// (sauf si mémoire encore en_attente — cas rare mais possible)
if ($memoire['professeur_id'] && (int) $memoire['professeur_id'] !== $professeurId) {
    header('Location: /views/professeur/liste_memoires.php?error=non_autorise');
    exit;
}

// Messages feedback
$successMessages = [
    'pris_en_charge' => 'Mémoire pris en charge. Vous pouvez maintenant le vérifier.',
    'remarque_ok'    => 'Remarque enregistrée.',
];
$errorMessages = [
    'remarque_vide'       => 'La remarque est obligatoire pour rejeter un mémoire.',
    'validation_impossible' => 'Impossible de valider ce mémoire (statut incorrect).',
    'rejet_impossible'    => 'Impossible de rejeter ce mémoire.',
];

$successMsg = !empty($_GET['success']) ? ($successMessages[$_GET['success']] ?? '') : '';
$errorMsg   = !empty($_GET['error'])   ? ($errorMessages[$_GET['error']]   ?? '') : '';

$labelStatut = [
    STATUT_EN_ATTENTE      => 'En attente',
    STATUT_EN_VERIFICATION => 'En vérification',
    STATUT_VALIDE          => 'Validé',
    STATUT_REJETE          => 'Rejeté',
    STATUT_PUBLIE          => 'Publié',
    STATUT_NON_PUBLIC      => 'Non public',
];

// Le professeur peut agir uniquement si le mémoire est en_verification
$peutAgir = ($memoire['statut'] === STATUT_EN_VERIFICATION
             && (int) $memoire['professeur_id'] === $professeurId);
?>
<?php require_once __DIR__ . '/../layout/header.php'; ?>
<?php require_once __DIR__ . '/../layout/navbar.php'; ?>

<div class="container-fluid">
  <div class="row">

    <!-- Sidebar -->
    <div class="col-md-2 px-0 sidebar">
      <nav class="nav flex-column pt-3">
        <a class="nav-link" href="/views/professeur/dashboard.php">
          <i class="bi bi-speedometer2"></i> Tableau de bord
        </a>
        <a class="nav-link active" href="/views/professeur/liste_memoires.php">
          <i class="bi bi-list-check"></i> Mémoires à vérifier
        </a>
        <a class="nav-link" href="/views/commentateur/rechercher.php">
          <i class="bi bi-search"></i> Consulter mémoires
        </a>
      </nav>
    </div>

    <!-- Contenu -->
    <div class="col-md-10 p-4">

      <!-- Fil d'ariane -->
      <nav style="font-size:0.85rem;margin-bottom:12px">
        <a href="/views/professeur/liste_memoires.php"
           style="color:var(--primary);text-decoration:none">
          ← Retour à la liste
        </a>
      </nav>

      <h2 class="section-title">Vérification du mémoire</h2>

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

      <div class="row g-4">

        <!-- ── Colonne gauche : infos + actions ───────────────── -->
        <div class="col-md-5">

          <!-- Infos du mémoire -->
          <div class="card mb-4">
            <div class="card-header-uatm">
              <i class="bi bi-info-circle me-2"></i>Informations
            </div>
            <div class="card-body">

              <div class="mb-3">
                <div style="font-size:0.8rem;color:var(--text-muted);margin-bottom:2px">Titre</div>
                <div class="fw-bold"><?= htmlspecialchars($memoire['titre']) ?></div>
              </div>

              <div class="mb-3">
                <div style="font-size:0.8rem;color:var(--text-muted);margin-bottom:2px">Thème</div>
                <div><?= htmlspecialchars($memoire['theme']) ?></div>
              </div>

              <div class="row mb-3">
                <div class="col-6">
                  <div style="font-size:0.8rem;color:var(--text-muted);margin-bottom:2px">Type</div>
                  <div><?= htmlspecialchars(ucfirst($memoire['type_diplome'])) ?></div>
                </div>
                <div class="col-6">
                  <div style="font-size:0.8rem;color:var(--text-muted);margin-bottom:2px">Année</div>
                  <div><?= (int) $memoire['annee_soutenance'] ?></div>
                </div>
              </div>

              <hr style="border-color:var(--border)">

              <div class="mb-3">
                <div style="font-size:0.8rem;color:var(--text-muted);margin-bottom:2px">Étudiant</div>
                <div class="fw-bold"><?= htmlspecialchars($memoire['nom_etudiant']) ?></div>
                <div style="font-size:0.85rem;color:var(--text-muted)">
                  <?= htmlspecialchars($memoire['email_etudiant']) ?>
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-6">
                  <div style="font-size:0.8rem;color:var(--text-muted);margin-bottom:2px">Niveau</div>
                  <span class="badge bg-secondary"><?= htmlspecialchars($memoire['niveau_etude']) ?></span>
                </div>
                <div class="col-6">
                  <div style="font-size:0.8rem;color:var(--text-muted);margin-bottom:2px">Filière</div>
                  <div><?= htmlspecialchars($memoire['nom_filiere'] ?? '—') ?></div>
                </div>
              </div>

              <div class="mb-2">
                <div style="font-size:0.8rem;color:var(--text-muted);margin-bottom:4px">Statut actuel</div>
                <span class="badge badge-<?= $memoire['statut'] ?>" style="font-size:0.85rem">
                  <?= htmlspecialchars($labelStatut[$memoire['statut']] ?? $memoire['statut']) ?>
                </span>
              </div>

            </div>
          </div>

          <!-- Zone d'action : valider / rejeter -->
          <?php if ($peutAgir): ?>
            <div class="card">
              <div class="card-header-uatm">
                <i class="bi bi-pencil-square me-2"></i>Décision
              </div>
              <div class="card-body">

                <!-- Remarque (commune aux deux actions) -->
                <div class="mb-3">
                  <label for="remarque-texte" class="mb-1">
                    Remarque
                    <small style="color:var(--text-muted)">(obligatoire pour rejeter)</small>
                  </label>
                  <textarea id="remarque-texte"
                            rows="4"
                            class="form-control"
                            placeholder="Indiquez vos observations sur ce mémoire…"><?= htmlspecialchars($memoire['remarques'] ?? '') ?></textarea>
                  <div id="remarque-error"
                       style="color:var(--danger);font-size:0.8rem;display:none;margin-top:4px">
                    La remarque est obligatoire pour rejeter.
                  </div>
                </div>

                <!-- Boutons d'action -->
                <div class="d-flex gap-2">

                  <!-- Enregistrer la remarque seule -->
                  <form method="POST" action="/controllers/ProfesseurController.php"
                        id="form-remarque">
                    <input type="hidden" name="action"     value="ajouter_remarque">
                    <input type="hidden" name="id_memoire" value="<?= $memoire['id_memoire'] ?>">
                    <input type="hidden" name="remarque"   id="remarque-input-note">
                    <button type="submit" class="btn btn-outline-secondary btn-sm"
                            onclick="transfererRemarque('remarque-input-note')">
                      <i class="bi bi-save me-1"></i> Enregistrer la remarque
                    </button>
                  </form>

                </div>

                <hr style="border-color:var(--border);margin:16px 0">

                <div class="d-flex gap-2">

                  <!-- Valider -->
                  <form method="POST" action="/controllers/ProfesseurController.php"
                        id="form-valider">
                    <input type="hidden" name="action"     value="valider">
                    <input type="hidden" name="id_memoire" value="<?= $memoire['id_memoire'] ?>">
                    <button type="submit"
                            class="btn btn-success"
                            onclick="return confirm('Confirmer la validation de ce mémoire ?')">
                      <i class="bi bi-check-lg me-1"></i> Valider
                    </button>
                  </form>

                  <!-- Rejeter -->
                  <form method="POST" action="/controllers/ProfesseurController.php"
                        id="form-rejeter">
                    <input type="hidden" name="action"     value="rejeter">
                    <input type="hidden" name="id_memoire" value="<?= $memoire['id_memoire'] ?>">
                    <input type="hidden" name="remarque"   id="remarque-input-rejet">
                    <button type="button"
                            class="btn btn-danger"
                            onclick="soumettreRejet()">
                      <i class="bi bi-x-lg me-1"></i> Rejeter
                    </button>
                  </form>

                </div>

              </div>
            </div>

          <?php elseif (!empty($memoire['remarques'])): ?>
            <!-- Afficher la remarque si déjà traitée -->
            <div class="card">
              <div class="card-header-uatm">
                <i class="bi bi-chat-quote me-2"></i>Remarque enregistrée
              </div>
              <div class="card-body">
                <div style="white-space:pre-line">
                  <?= nl2br(htmlspecialchars($memoire['remarques'])) ?>
                </div>
              </div>
            </div>
          <?php endif; ?>

        </div>

        <!-- ── Colonne droite : visionneuse PDF ───────────────── -->
        <div class="col-md-7">
          <div class="card">
            <div class="card-header-uatm">
              <i class="bi bi-file-earmark-pdf me-2"></i>Mémoire PDF
            </div>
            <div class="card-body p-2">
              <div id="pdf-container">
                <iframe
                  src="/scripts/serve_pdf.php?id=<?= $memoire['id_memoire'] ?>"
                  width="100%"
                  height="700px"
                  style="border:none;border-radius:var(--radius)">
                  <p style="color:#fff;padding:20px">
                    Votre navigateur ne supporte pas l'affichage de PDF.
                    <a href="/scripts/serve_pdf.php?id=<?= $memoire['id_memoire'] ?>"
                       style="color:var(--secondary)">
                      Télécharger le fichier
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
// Copie le texte de la textarea dans l'input caché avant soumission
function transfererRemarque(inputId) {
  document.getElementById(inputId).value =
    document.getElementById('remarque-texte').value;
}

// Vérifie que la remarque n'est pas vide avant de rejeter
function soumettreRejet() {
  const texte = document.getElementById('remarque-texte').value.trim();
  const errEl = document.getElementById('remarque-error');

  if (!texte) {
    errEl.style.display = 'block';
    document.getElementById('remarque-texte').classList.add('is-invalid');
    document.getElementById('remarque-texte').focus();
    return;
  }

  errEl.style.display = 'none';
  document.getElementById('remarque-input-rejet').value = texte;

  if (confirm('Confirmer le rejet de ce mémoire ?\nL\'étudiant pourra le corriger et le resoumettre.')) {
    document.getElementById('form-rejeter').submit();
  }
}

// Masquer l'erreur dès que l'utilisateur commence à taper
document.getElementById('remarque-texte')?.addEventListener('input', function () {
  document.getElementById('remarque-error').style.display = 'none';
  this.classList.remove('is-invalid');
});
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>