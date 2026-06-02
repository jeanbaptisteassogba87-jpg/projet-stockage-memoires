<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../dao/UtilisateurDAO.php';
require_once __DIR__ . '/../../dao/CentreDAO.php';

requireRole(ROLE_TECHNICIEN);

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: /views/technicien/gerer_comptes.php');
    exit;
}

$dao = new UtilisateurDAO();
$user = $dao->trouverParId($id);
if (!$user) {
    header('Location: /views/technicien/gerer_comptes.php?error=notfound');
    exit;
}

$centreDAO = new CentreDAO();
$centres = $centreDAO->listerTous();

?>
<?php require_once __DIR__ . '/../layout/header.php'; ?>
<?php require_once __DIR__ . '/../layout/navbar.php'; ?>

<div class="container mt-4">
  <h2>Modifier l'utilisateur #<?= (int) $user['id_utilisateur'] ?></h2>

  <form method="POST" action="/controllers/TechnicienController.php" class="mt-3">
    <input type="hidden" name="action" value="modifier_utilisateur">
    <input type="hidden" name="id_utilisateur" value="<?= (int) $user['id_utilisateur'] ?>">

    <div class="mb-3">
      <label for="nom" class="form-label">Nom</label>
      <input type="text" id="nom" name="nom" class="form-control" value="<?= htmlspecialchars($user['nom']) ?>" required>
    </div>

    <div class="mb-3">
      <label for="email" class="form-label">Email</label>
      <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
    </div>

    <div class="mb-3">
      <label for="role" class="form-label">Rôle</label>
      <select id="role" name="role" class="form-select" required>
        <?php foreach (['technicien','professeur','directeur','etudiant'] as $r): ?>
          <option value="<?= $r ?>" <?= $user['role'] === $r ? 'selected' : '' ?>><?= ucfirst($r) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="mb-3">
      <label for="centre_id" class="form-label">Centre</label>
      <select id="centre_id" name="centre_id" class="form-select" required>
        <?php foreach ($centres as $centre): ?>
          <option value="<?= (int) $centre['id_centre'] ?>" <?= ((int)$user['centre_id'] === (int)$centre['id_centre']) ? 'selected' : '' ?>><?= htmlspecialchars($centre['nom_centre']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-primary">Enregistrer</button>
      <a href="/views/technicien/gerer_comptes.php" class="btn btn-outline-secondary">Annuler</a>
    </div>
  </form>

  <hr>

  <?php if ((int) $user['id_utilisateur'] === (int) ($_SESSION['user_id'] ?? 0)): ?>
    <button class="btn btn-danger" disabled>Toute action interdite sur votre compte</button>
  <?php else: ?>
    <button type="button" class="btn btn-danger js-confirm-action" data-action="supprimer_utilisateur" data-user-id="<?= (int) $user['id_utilisateur'] ?>" data-user-name="<?= htmlspecialchars($user['nom']) ?>">
      Supprimer l'utilisateur
    </button>
  <?php endif; ?>

</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>

<!-- Confirmation modal (local) -->
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
