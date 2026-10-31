<?php

$pageTitle = 'Résultats du jury — UATM GASA Formation';

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../dao/DirecteurDAO.php';
require_once __DIR__ . '/../../models/MentionJury.php';

requireRole(ROLE_DIRECTEUR);

$memoires = (new DirecteurDAO())->listerMemoresGerables((int) $_SESSION['centre_id']);
$labelsStatut = [
    STATUT_VALIDE => 'Validé',
    STATUT_PUBLIE => 'Publié',
    STATUT_NON_PUBLIC => 'Non public',
];
$success = ($_GET['success'] ?? '') === 'note_enregistree'
    ? 'La note du jury a été enregistrée.'
    : '';
$errorMessages = [
    'note_invalide' => 'Saisissez une note comprise entre 0 et 20, avec deux décimales au maximum.',
    'enregistrement_impossible' => 'Le résultat n’a pas pu être enregistré pour ce mémoire.',
];
$error = $errorMessages[$_GET['error'] ?? ''] ?? '';
?>
<?php require_once __DIR__ . '/../layout/header.php'; ?>
<?php require_once __DIR__ . '/../layout/navbar.php'; ?>

<div class="container-fluid">
  <div class="row">
    <aside class="col-md-2 px-0 sidebar">
      <nav class="nav flex-column pt-3">
        <a class="nav-link" href="/views/directeur/dashboard.php">
          <i class="bi bi-speedometer2"></i> Tableau de bord
        </a>
        <a class="nav-link" href="/views/directeur/gerer_visibilite.php">
          <i class="bi bi-eye"></i> Gérer la visibilité
        </a>
        <a class="nav-link active" href="/views/directeur/resultats_jury.php">
          <i class="bi bi-award"></i> Résultats du jury
        </a>
        <a class="nav-link" href="/views/commentateur/rechercher.php">
          <i class="bi bi-search"></i> Consulter les mémoires
        </a>
      </nav>
    </aside>

    <main class="col-md-10 p-4">
      <h2 class="section-title">Résultats du jury</h2>
      <p class="text-muted">Enregistrez la note finale communiquée par le jury. La mention est calculée automatiquement.</p>

      <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <div class="card">
        <div class="table-responsive">
          <table class="table table-uatm table-hover mb-0 align-middle">
            <thead>
              <tr>
                <th>Étudiant</th>
                <th>Mémoire</th>
                <th>Statut</th>
                <th>Note sur 20</th>
                <th>Mention calculée</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($memoires as $memoire): ?>
                <tr>
                  <td><?= htmlspecialchars($memoire['nom_etudiant']) ?></td>
                  <td><?= htmlspecialchars($memoire['titre']) ?></td>
                  <td><?= htmlspecialchars($labelsStatut[$memoire['statut']] ?? $memoire['statut']) ?></td>
                  <td>
                      <input class="form-control form-control-sm note-jury"
                             type="number" name="note_jury" min="0" max="20" step="0.01"
                             form="jury-form-<?= (int) $memoire['id_memoire'] ?>"
                             value="<?= $memoire['note_jury'] !== null ? htmlspecialchars((string) $memoire['note_jury']) : '' ?>"
                             data-mention-target="mention-<?= (int) $memoire['id_memoire'] ?>"
                             required aria-label="Note du jury sur 20">
                  </td>
                  <td>
                    <span class="badge bg-secondary" id="mention-<?= (int) $memoire['id_memoire'] ?>" aria-live="polite">
                      <?= $memoire['note_jury'] !== null
                          ? htmlspecialchars(MentionJury::calculer((float) $memoire['note_jury']))
                          : 'À renseigner' ?>
                    </span>
                  </td>
                  <td>
                    <form id="jury-form-<?= (int) $memoire['id_memoire'] ?>" method="POST" action="/controllers/DirecteurController.php">
                      <?= csrfField() ?>
                      <input type="hidden" name="action" value="enregistrer_note_jury">
                      <input type="hidden" name="id_memoire" value="<?= (int) $memoire['id_memoire'] ?>">
                    </form>
                    <button type="submit" form="jury-form-<?= (int) $memoire['id_memoire'] ?>" class="btn btn-uatm btn-sm">
                      Enregistrer
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (!$memoires): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">Aucun mémoire à évaluer pour le moment.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
      <p class="small text-muted mt-3">Barème : moins de 10, Ajourné ; 10–11,99, Passable ; 12–13,99, Assez bien ; 14–15,99, Bien ; 16–20, Très bien.</p>
    </main>
  </div>
</div>

<script>
document.querySelectorAll('.note-jury').forEach((input) => {
  const target = document.getElementById(input.dataset.mentionTarget);
  const updateMention = () => {
    const note = Number(input.value);
    if (input.value === '' || !Number.isFinite(note) || note < 0 || note > 20) {
      target.textContent = 'À renseigner';
      return;
    }
    target.textContent = note < 10 ? 'Ajourné'
      : note < 12 ? 'Passable'
      : note < 14 ? 'Assez bien'
      : note < 16 ? 'Bien'
      : 'Très bien';
  };
  input.addEventListener('input', updateMention);
});
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
