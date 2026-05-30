<?php
$pageTitle = 'Déposer un mémoire — Étudiant';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../dao/EtudiantDAO.php';
requireRole(ROLE_ETUDIANT);

// Si premier connexion, forcer changement mdp
if ($_SESSION['doit_changer_mdp']) {
    header('Location: /views/auth/change_password.php'); exit;
}

// Récupérer les erreurs et données de formulaire de la session
$erreurs = $_SESSION['upload_errors'] ?? [];
$formData = $_SESSION['form_data'] ?? [];

// Nettoyer la session
unset($_SESSION['upload_errors']);
unset($_SESSION['form_data']);

$etudiantDAO = new EtudiantDAO();
$peutDeposer = $etudiantDAO->peutDeposer((int)$_SESSION['user_id']);
?>

<?php require_once __DIR__ . '/../layout/header.php'; ?>
<?php require_once __DIR__ . '/../layout/navbar.php'; ?>

<div class="container-fluid">
  <div class="row">

    <!-- Sidebar -->
    <div class="col-md-2 px-0 sidebar">
      <nav class="nav flex-column pt-3">
        <a class="nav-link" href="/views/etudiant/dashboard.php">
          <i class="bi bi-speedometer2"></i> Tableau de bord
        </a>
        <a class="nav-link active" href="/views/etudiant/deposer_memoire.php">
          <i class="bi bi-upload"></i> Déposer un mémoire
        </a>
        <a class="nav-link" href="/views/etudiant/modifier_memoire.php">
          <i class="bi bi-pencil"></i> Modifier mon mémoire
        </a>
        <a class="nav-link" href="/views/etudiant/voir_remarques.php">
          <i class="bi bi-chat-text"></i> Mes remarques
        </a>
        <a class="nav-link" href="/views/commentateur/rechercher.php">
          <i class="bi bi-search"></i> Consulter mémoires
        </a>
      </nav>
    </div>

    <!-- Contenu principal -->
    <div class="col-md-10 p-4">
      <h2 class="section-title mb-4">Déposer un nouveau mémoire</h2>

      <!-- Afficher les erreurs s'il y en a -->
      <?php if (!empty($erreurs)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <strong><i class="bi bi-exclamation-circle"></i> Erreurs détectées:</strong>
          <ul class="mb-0 mt-2">
            <?php foreach ($erreurs as $erreur): ?>
              <li><?= htmlspecialchars($erreur) ?></li>
            <?php endforeach; ?>
          </ul>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
      <?php endif; ?>

      <!-- Formulaire de dépôt -->
      <?php if (!$peutDeposer): ?>
        <div class="alert alert-warning" role="alert">
          <i class="bi bi-exclamation-triangle"></i>
          Vous ne pouvez pas deposer de memoire avec votre niveau actuel. Seuls les etudiants L3, M2 ou diplomes permanents sont autorises.
        </div>
      <?php else: ?>

      <div class="card p-4 mb-4">
        <form id="form-depot" method="POST" action="/controllers/EtudiantController.php" enctype="multipart/form-data" novalidate>
          
          <input type="hidden" name="action" value="ajouter_memoire">

          <!-- Titre du mémoire -->
          <div class="mb-3">
            <label for="titre" class="form-label">Titre du mémoire <span class="text-danger">*</span></label>
            <input 
              type="text" 
              class="form-control" 
              id="titre" 
              name="titre" 
              placeholder="Entrez le titre de votre mémoire"
              value="<?= htmlspecialchars($formData['titre'] ?? '') ?>"
              required
              minlength="5"
            >
            <small class="form-text text-muted">Au minimum 5 caractères</small>
          </div>

          <!-- Thème -->
          <div class="mb-3">
            <label for="theme" class="form-label">Thème <span class="text-danger">*</span></label>
            <input 
              type="text" 
              class="form-control" 
              id="theme" 
              name="theme" 
              placeholder="Entrez le thème de votre mémoire"
              value="<?= htmlspecialchars($formData['theme'] ?? '') ?>"
              required
              minlength="5"
            >
            <small class="form-text text-muted">Au minimum 5 caractères</small>
          </div>

          <!-- Type de diplôme -->
          <div class="mb-3">
            <label for="type_diplome" class="form-label">Type de diplôme <span class="text-danger">*</span></label>
            <select class="form-select" id="type_diplome" name="type_diplome" required>
              <option value="">-- Sélectionnez un type --</option>
              <option value="<?= DIPLOME_LICENCE ?>" <?= ($formData['type_diplome'] ?? '') === DIPLOME_LICENCE ? 'selected' : '' ?>>
                Licence
              </option>
              <option value="<?= DIPLOME_MASTER ?>" <?= ($formData['type_diplome'] ?? '') === DIPLOME_MASTER ? 'selected' : '' ?>>
                Master
              </option>
            </select>
          </div>

          <!-- Année de soutenance -->
          <div class="mb-3">
            <label for="annee_soutenance" class="form-label">Année de soutenance <span class="text-danger">*</span></label>
            <input 
              type="number" 
              class="form-control" 
              id="annee_soutenance" 
              name="annee_soutenance" 
              placeholder="YYYY"
              value="<?= htmlspecialchars($formData['annee_soutenance'] ?? '') ?>"
              required
              min="2000"
              max="<?= date('Y') + 1 ?>"
            >
            <small class="form-text text-muted">Entre 2000 et <?= date('Y') + 1 ?></small>
          </div>

          <!-- Fichier PDF -->
          <div class="mb-4">
            <label for="fichier_pdf" class="form-label">Fichier PDF <span class="text-danger">*</span></label>
            <div class="input-group">
              <input 
                type="file" 
                class="form-control" 
                id="fichier_pdf" 
                name="fichier_pdf" 
                accept=".pdf"
                required
              >
            </div>
            <small class="form-text text-muted">
              Format: PDF uniquement | Taille max: <?= (MAX_PDF_SIZE / (1024 * 1024)) ?> Mo
            </small>
            <!-- Aperçu du fichier sélectionné -->
            <div id="file-preview" class="mt-2"></div>
          </div>

          <!-- Boutons -->
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-upload"></i> Déposer le mémoire
            </button>
            <a href="/views/etudiant/dashboard.php" class="btn btn-secondary">
              Annuler
            </a>
          </div>

        </form>
      </div>

      <?php endif; ?>

      <!-- Informations utiles -->
      <div class="card bg-light border-0 p-4">
        <h5 class="card-title mb-3">
          <i class="bi bi-info-circle text-info"></i> Informations importantes
        </h5>
        <ul>
          <li><strong>Un seul mémoire par type de diplôme:</strong> Vous ne pouvez déposer qu'un seul mémoire par type (Licence ou Master)</li>
          <li><strong>Vérification:</strong> Votre mémoire sera vérifiépar un professeur avant d'être publié</li>
          <li><strong>Format:</strong> Seuls les fichiers PDF sont acceptés</li>
          <li><strong>Taille:</strong> La taille maximale est de <?= (MAX_PDF_SIZE / (1024 * 1024)) ?> Mo</li>
          <li><strong>Statuts possibles:</strong> En attente → En vérification → Validé/Rejeté → Publié/Non public</li>
        </ul>
      </div>

    </div>

  </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>

<!-- Script de validation et aperçu fichier -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Aperçu du fichier sélectionné
  const fileInput = document.getElementById('fichier_pdf');
  const filePreview = document.getElementById('file-preview');

  if (!fileInput || !filePreview) {
    return;
  }

  fileInput.addEventListener('change', function() {
    filePreview.innerHTML = '';

    if (this.files.length > 0) {
      const file = this.files[0];
      const maxSize = <?= MAX_PDF_SIZE ?>;

      // Vérifier le type
      if (file.type !== 'application/pdf') {
        filePreview.innerHTML = '<div class="alert alert-warning mt-2">⚠️ Ce fichier ne semble pas être un PDF valide</div>';
        return;
      }

      // Vérifier la taille
      if (file.size > maxSize) {
        filePreview.innerHTML = '<div class="alert alert-warning mt-2">⚠️ Le fichier dépasse la taille maximale (' + (maxSize / (1024 * 1024)).toFixed(2) + ' Mo)</div>';
        return;
      }

      // Afficher les infos du fichier
      const sizeInMB = (file.size / (1024 * 1024)).toFixed(2);
      filePreview.innerHTML = `
        <div class="alert alert-success mt-2">
          <i class="bi bi-check-circle"></i> 
          <strong>${file.name}</strong> (${sizeInMB} Mo)
        </div>
      `;
    }
  });

  // Validation du formulaire côté client
  const form = document.getElementById('form-depot');
  form.addEventListener('submit', function(e) {
    if (!form.checkValidity()) {
      e.preventDefault();
      e.stopPropagation();
    }
    form.classList.add('was-validated');
  });
});
</script>

<style>
.section-title {
  color: var(--primary);
  font-weight: 600;
  border-bottom: 3px solid var(--secondary);
  padding-bottom: 10px;
}

.form-label {
  font-weight: 500;
  color: var(--primary);
}

.text-danger {
  color: var(--danger);
  font-weight: bold;
}

.card {
  border-color: var(--border);
  box-shadow: var(--shadow);
}

.btn-primary {
  background-color: var(--primary);
  border-color: var(--primary);
}

.btn-primary:hover {
  background-color: var(--primary-light);
  border-color: var(--primary-light);
}

.btn-secondary {
  background-color: #6C757D;
}

.btn-secondary:hover {
  background-color: #5A6268;
}

.sidebar {
  background: white;
  border-right: 1px solid var(--border);
}

.nav-link {
  color: var(--text-main) !important;
  padding: 12px 20px;
  border-left: 3px solid transparent;
  transition: all 0.3s ease;
}

.nav-link:hover {
  background-color: var(--bg-page);
  border-left-color: var(--secondary);
  color: var(--primary) !important;
}

.nav-link.active {
  background-color: var(--bg-page);
  border-left-color: var(--secondary);
  color: var(--primary) !important;
  font-weight: 600;
}
</style>
