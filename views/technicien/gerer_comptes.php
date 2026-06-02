<?php

// Rôle : création et désactivation des comptes utilisateurs par le technicien

$pageTitle = 'Gérer les comptes — UATM GASA Formation';

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../dao/UtilisateurDAO.php';
require_once __DIR__ . '/../../dao/CentreDAO.php';
require_once __DIR__ . '/../../dao/FiliereDAO.php';

requireRole(ROLE_TECHNICIEN);

if ($_SESSION['doit_changer_mdp']) {
    header('Location: /views/auth/change_password.php');
    exit;
}

$utilisateurDAO = new UtilisateurDAO();
$centreDAO      = new CentreDAO();
$filiereDAO     = new FiliereDAO();

$utilisateurs = $utilisateurDAO->getAllUtilisateurs();
$centres      = $centreDAO->listerTous();
$filieres     = $filiereDAO->listerToutes();

$nbActifs    = count(array_filter($utilisateurs, fn($user) => $user['est_actif']));
$nbInactifs  = count($utilisateurs) - $nbActifs;

$successMessages = [
    '1' => 'Utilisateur créé avec succès.',
];
$errorMessages = [
    '1'       => 'Erreur lors de la création du compte.',
    'profil'  => 'Merci de renseigner les informations obligatoires du profil étudiant.',
    'doublon' => 'Un compte existe déjà avec cet email.',
  'own_account' => 'Impossible de supprimer le compte actuellement connecté.'
];

$successMsg = !empty($_GET['success']) ? ($successMessages[$_GET['success']] ?? '') : '';
$errorMsg   = !empty($_GET['error'])   ? ($errorMessages[$_GET['error']] ?? '') : '';
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
        <a class="nav-link active" href="/views/technicien/gerer_comptes.php">
          <i class="bi bi-people"></i> Gérer les comptes
        </a>
        <a class="nav-link" href="/views/technicien/importer_memoires.php">
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

      <h2 class="section-title">Gérer les comptes</h2>

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

      <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
          <div class="card p-3 text-center">
            <div style="font-size:2rem;color:var(--primary)">
              <i class="bi bi-people"></i>
            </div>
            <div class="fw-bold mt-1" style="font-size:1.5rem"><?= count($utilisateurs) ?></div>
            <div style="color:var(--text-muted);font-size:0.85rem">Comptes enregistrés</div>
          </div>
        </div>

        <div class="col-12 col-md-4">
          <div class="card p-3 text-center">
            <div style="font-size:2rem;color:var(--success)">
              <i class="bi bi-person-check"></i>
            </div>
            <div class="fw-bold mt-1" style="font-size:1.5rem"><?= $nbActifs ?></div>
            <div style="color:var(--text-muted);font-size:0.85rem">Comptes actifs</div>
          </div>
        </div>

        <div class="col-12 col-md-4">
          <div class="card p-3 text-center">
            <div style="font-size:2rem;color:var(--danger)">
              <i class="bi bi-person-dash"></i>
            </div>
            <div class="fw-bold mt-1" style="font-size:1.5rem"><?= $nbInactifs ?></div>
            <div style="color:var(--text-muted);font-size:0.85rem">Comptes désactivés</div>
          </div>
        </div>
      </div>

      <div class="card mb-4">
        <div class="card-header-uatm">
          <i class="bi bi-person-plus me-2"></i>Créer un utilisateur
        </div>
        <div class="card-body p-4">
          <form method="POST" action="/controllers/TechnicienController.php">
            <input type="hidden" name="action" value="creer_utilisateur">

            <div class="row g-3">
              <div class="col-12 col-md-6">
                <label for="nom">Nom</label>
                <input type="text" name="nom" id="nom" class="form-control mt-1" maxlength="100" required>
              </div>

              <div class="col-12 col-md-6">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" class="form-control mt-1" maxlength="150" required>
              </div>

              <div class="col-12 col-md-4">
                <label for="mot_de_passe">Mot de passe temporaire</label>
                <input type="text" name="mot_de_passe" id="mot_de_passe" class="form-control mt-1" required>
              </div>

              <div class="col-12 col-md-4">
                <label for="role">Rôle</label>
                <select name="role" id="role" class="form-select mt-1" required>
                  <option value="technicien">Technicien</option>
                  <option value="professeur">Professeur</option>
                  <option value="directeur">Directeur</option>
                  <option value="etudiant">Étudiant</option>
                </select>
              </div>

              <div class="col-12 col-md-4">
                <label for="centre_id">Centre</label>
                <select name="centre_id" id="centre_id" class="form-select mt-1" required>
                  <option value="">— Choisir —</option>
                  <?php foreach ($centres as $centre): ?>
                    <option value="<?= (int) $centre['id_centre'] ?>">
                      <?= htmlspecialchars($centre['nom_centre']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col-12 role-fields role-etudiant d-none">
                <div class="row g-3">
                  <div class="col-12 col-md-4">
                    <label for="numero_etudiant">Numéro étudiant</label>
                    <input type="text" name="numero_etudiant" id="numero_etudiant" class="form-control mt-1" maxlength="50">
                  </div>

                  <div class="col-12 col-md-4">
                    <label for="niveau_etude">Niveau</label>
                    <select name="niveau_etude" id="niveau_etude" class="form-select mt-1">
                      <option value="">— Choisir —</option>
                      <option value="L1">L1</option>
                      <option value="L2">L2</option>
                      <option value="L3">L3</option>
                      <option value="M1">M1</option>
                      <option value="M2">M2</option>
                    </select>
                  </div>

                  <div class="col-12 col-md-4">
                    <label for="filiere_id">Filière</label>
                    <select name="filiere_id" id="filiere_id" class="form-select mt-1">
                      <option value="">— Choisir —</option>
                      <?php foreach ($filieres as $filiere): ?>
                        <option value="<?= (int) $filiere['id_filiere'] ?>">
                          <?= htmlspecialchars($filiere['nom_filiere'] . ' — ' . $filiere['nom_centre']) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
              </div>

              <div class="col-12 role-fields role-professeur d-none">
                <div class="row g-3">
                  <div class="col-12 col-md-6">
                    <label for="specialite">Spécialité</label>
                    <input type="text" name="specialite" id="specialite" class="form-control mt-1" maxlength="100">
                  </div>
                  <div class="col-12 col-md-6">
                    <label for="grade">Grade</label>
                    <input type="text" name="grade" id="grade" class="form-control mt-1" maxlength="50">
                  </div>
                </div>
              </div>

              <div class="col-12 col-md-6 role-fields role-directeur d-none">
                <label for="responsabilite">Responsabilité</label>
                <input type="text" name="responsabilite" id="responsabilite" class="form-control mt-1" maxlength="100">
              </div>

              <div class="col-12 col-md-6 role-fields role-technicien">
                <label for="service">Service</label>
                <input type="text" name="service" id="service" class="form-control mt-1" maxlength="100">
              </div>
            </div>

            <div class="d-flex flex-wrap gap-2 mt-4">
              <button type="submit" class="btn btn-uatm">
                <i class="bi bi-check2-circle me-1"></i> Créer le compte
              </button>
              <a href="/views/technicien/importer_utilisateurs.php" class="btn btn-outline-secondary">
                <i class="bi bi-file-earmark-arrow-up me-1"></i> Import CSV
              </a>
            </div>
          </form>
        </div>
      </div>

      <div class="card">
        <div class="card-header-uatm d-flex justify-content-between align-items-center flex-wrap gap-2">
          <span><i class="bi bi-list-ul me-2"></i>Liste des utilisateurs</span>
          <span class="badge bg-light text-dark"><?= count($utilisateurs) ?> compte<?= count($utilisateurs) > 1 ? 's' : '' ?></span>
        </div>
        <div class="card-body p-0">
          <?php if (empty($utilisateurs)): ?>
            <div class="text-center text-muted py-4">
              <i class="bi bi-inbox" style="font-size:2rem"></i>
              <p class="mt-2 mb-0">Aucun utilisateur enregistré.</p>
            </div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-uatm table-hover mb-0 align-middle">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Centre</th>
                    <th>Statut</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($utilisateurs as $user): ?>
                    <tr>
                      <td><?= (int) $user['id_utilisateur'] ?></td>
                      <td><?= htmlspecialchars($user['nom']) ?></td>
                      <td><?= htmlspecialchars($user['email']) ?></td>
                      <td>
                        <span class="badge bg-primary-uatm">
                          <?= htmlspecialchars(ucfirst($user['role'])) ?>
                        </span>
                      </td>
                      <td><?= htmlspecialchars($user['nom_centre'] ?? ('Centre #' . (int) $user['centre_id'])) ?></td>
                      <td>
                        <?php if ($user['est_actif']): ?>
                          <span class="badge bg-success">Actif</span>
                        <?php else: ?>
                          <span class="badge bg-secondary">Désactivé</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <div class="d-flex gap-2">
                          <a href="/views/technicien/modifier_utilisateur.php?id=<?= (int) $user['id_utilisateur'] ?>" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil-square me-1"></i> Modifier
                          </a>

                          <?php if ($user['est_actif']): ?>
                            <button type="button" class="btn btn-danger btn-sm js-confirm-action"
                                    data-action="desactiver_utilisateur"
                                    data-user-id="<?= (int) $user['id_utilisateur'] ?>"
                                    data-user-name="<?= htmlspecialchars($user['nom']) ?>">
                              <i class="bi bi-person-dash me-1"></i> Désactiver
                            </button>
                          <?php endif; ?>

                          <button type="button" class="btn btn-outline-danger btn-sm js-confirm-action"
                                  data-action="supprimer_utilisateur"
                                  data-user-id="<?= (int) $user['id_utilisateur'] ?>"
                                  data-user-name="<?= htmlspecialchars($user['nom']) ?>">
                            <i class="bi bi-trash me-1"></i> Supprimer
                          </button>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const role = document.getElementById('role');
  const groupes = document.querySelectorAll('.role-fields');

  function actualiserProfil() {
    groupes.forEach(function (groupe) {
      groupe.classList.add('d-none');
    });

    const actif = document.querySelector('.role-' + role.value);
    if (actif) {
      actif.classList.remove('d-none');
    }
  }

  if (role) {
    role.addEventListener('change', actualiserProfil);
    actualiserProfil();
  }
});
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>

<!-- Confirmation modal for deactivate/delete actions -->
<div class="modal fade" id="confirmActionModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="/controllers/TechnicienController.php" id="confirmActionForm">
        <div class="modal-header">
          <h5 class="modal-title" id="confirmActionTitle">Confirmer l'action</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
        </div>
        <div class="modal-body">
          <p id="confirmActionMessage">Voulez-vous vraiment procéder ?</p>
          <input type="hidden" name="action" id="confirmActionInputAction" value="">
          <input type="hidden" name="id_utilisateur" id="confirmActionInputId" value="">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary" id="confirmActionSubmit">Confirmer</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const modalEl = document.getElementById('confirmActionModal');
  if (!modalEl) return;

  const bootstrapModal = new bootstrap.Modal(modalEl);
  const form = document.getElementById('confirmActionForm');
  const inputAction = document.getElementById('confirmActionInputAction');
  const inputId = document.getElementById('confirmActionInputId');
  const message = document.getElementById('confirmActionMessage');

  document.querySelectorAll('.js-confirm-action').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const action = btn.getAttribute('data-action');
      const id = btn.getAttribute('data-user-id');
      const name = btn.getAttribute('data-user-name') || '';

      inputAction.value = action;
      inputId.value = id;

      if (action === 'desactiver_utilisateur') {
        message.textContent = `Désactiver le compte « ${name} » ?`;
        document.getElementById('confirmActionTitle').textContent = 'Désactiver le compte';
        document.getElementById('confirmActionSubmit').className = 'btn btn-danger';
      } else if (action === 'supprimer_utilisateur') {
        message.textContent = `Supprimer définitivement l'utilisateur « ${name} » ? Cette action est irréversible.`;
        document.getElementById('confirmActionTitle').textContent = "Supprimer l'utilisateur";
        document.getElementById('confirmActionSubmit').className = 'btn btn-outline-danger';
      } else {
        message.textContent = 'Confirmer ?';
        document.getElementById('confirmActionTitle').textContent = "Confirmer l'action";
        document.getElementById('confirmActionSubmit').className = 'btn btn-primary';
      }

      bootstrapModal.show();
    });
  });
});
</script>
