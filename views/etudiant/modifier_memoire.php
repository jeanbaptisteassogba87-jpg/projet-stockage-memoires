<?php

// Rôle : permet à l'étudiant de modifier un mémoire uniquement si son statut est en_attente ou rejete
// Pré-remplit les champs avec les données existantes       
        


$pageTitle = 'Modifier mon mémoire — UATM GASA Formation';

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../dao/MemoireDAO.php';

requireRole(ROLE_ETUDIANT);

if ($_SESSION['doit_changer_mdp']) {
    header('Location: /views/auth/change_password.php');
    exit;
}

$memoireDAO = new MemoireDAO();

// Récupérer les mémoires de l'étudiant connecté
$memoires = $memoireDAO->listerParEtudiant((int) $_SESSION['user_id']);

// Filtrer : uniquement ceux qu'on peut modifier
$modifiables = array_filter($memoires, function($m) {
    return in_array($m['statut'], [STATUT_EN_ATTENTE, STATUT_REJETE]);
});

// Si un id est passé en GET, charger ce mémoire spécifique
$memoireSelectionne = null;
if (!empty($_GET['id'])) {
    $idGet = (int) $_GET['id'];
    foreach ($modifiables as $m) {
        if ((int) $m['id_memoire'] === $idGet) {
            $memoireSelectionne = $m;
            break;
        }
    }
}

// Messages d'erreur et de succès
$messages = [
    'champs_vides'   => 'Merci de remplir tous les champs obligatoires.',
    'pas_pdf'        => 'Le fichier doit être au format PDF.',
    'trop_lourd'     => 'Le fichier dépasse 10 Mo.',
    'upload_echec'   => 'Erreur lors de l\'upload. Réessayez.',
    'bdd'            => 'Erreur lors de l\'enregistrement.',
    'statut_bloque'  => 'Ce mémoire ne peut plus être modifié (déjà validé ou en cours de vérification).',
    'non_autorise'   => 'Action non autorisée.',
];
$erreur  = !empty($_GET['error'])   ? ($messages[$_GET['error']] ?? 'Erreur.') : '';
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
        <a class="nav-link" href="/views/etudiant/deposer_memoire.php">
          <i class="bi bi-upload"></i> Déposer un mémoire
        </a>
        <a class="nav-link active" href="/views/etudiant/modifier_memoire.php">
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

    <!-- Contenu -->
    <div class="col-md-10 p-4">

      <h2 class="section-title">Modifier mon mémoire</h2>

      <?php if ($erreur): ?>
        <div class="alert alert-danger mb-4">
          <i class="bi bi-exclamation-triangle me-2"></i>
          <?= htmlspecialchars($erreur) ?>
        </div>
      <?php endif; ?>

      <?php if (empty($modifiables)): ?>
        <!-- Aucun mémoire modifiable -->
        <div class="card">
          <div class="card-body text-center py-5">
            <i class="bi bi-lock" style="font-size:3rem;color:var(--text-muted)"></i>
            <p class="mt-3" style="color:var(--text-muted)">
              Aucun mémoire ne peut être modifié pour l'instant.<br>
              <small>La modification est possible uniquement quand le statut est
                <strong>En attente</strong> ou <strong>Rejeté</strong>.</small>
            </p>
            <a href="/views/etudiant/dashboard.php" class="btn btn-uatm mt-2">
              Retour au tableau de bord
            </a>
          </div>
        </div>

      <?php else: ?>

        <?php if (!$memoireSelectionne && count($modifiables) > 1): ?>
          <!-- Plusieurs mémoires modifiables : afficher un choix -->
          <div class="card mb-4">
            <div class="card-header-uatm">
              <i class="bi bi-list-ul me-2"></i>Choisir le mémoire à modifier
            </div>
            <div class="card-body p-0">
              <table class="table table-uatm table-hover mb-0">
                <thead>
                  <tr>
                    <th>Titre</th>
                    <th>Type</th>
                    <th>Statut</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($modifiables as $m): ?>
                    <tr>
                      <td><?= htmlspecialchars($m['titre']) ?></td>
                      <td><?= htmlspecialchars(ucfirst($m['type_diplome'])) ?></td>
                      <td>
                        <span class="badge badge-<?= $m['statut'] ?>">
                          <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $m['statut']))) ?>
                        </span>
                      </td>
                      <td>
                        <a href="?id=<?= $m['id_memoire'] ?>" class="btn btn-sm btn-uatm">
                          <i class="bi bi-pencil"></i> Modifier
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>

        <?php else: ?>
          <?php
          // Un seul mémoire modifiable : le sélectionner automatiquement
          if (!$memoireSelectionne) {
              $memoireSelectionne = reset($modifiables);
          }
          ?>

          <!-- Alerte si rejeté : afficher la remarque du professeur -->
          <?php if ($memoireSelectionne['statut'] === STATUT_REJETE && !empty($memoireSelectionne['remarques'])): ?>
            <div class="alert mb-4"
                 style="background:#FFF3CD;border-left:4px solid var(--warning);border-radius:var(--radius)">
              <strong><i class="bi bi-chat-text me-2"></i>Remarque du professeur :</strong><br>
              <div class="mt-1"><?= nl2br(htmlspecialchars($memoireSelectionne['remarques'])) ?></div>
            </div>
          <?php endif; ?>

          <!-- Formulaire de modification -->
          <div class="card">
            <div class="card-header-uatm">
              <i class="bi bi-pencil-square me-2"></i>
              Modifier : <?= htmlspecialchars($memoireSelectionne['titre']) ?>
            </div>
            <div class="card-body p-4">

              <form method="POST"
                    action="/controllers/EtudiantController.php"
                    enctype="multipart/form-data"
                    id="form-modification"
                    novalidate>

                <input type="hidden" name="action"     value="modifier_memoire">
                <input type="hidden" name="id_memoire" value="<?= $memoireSelectionne['id_memoire'] ?>">

                <div class="row g-3">

                  <!-- Titre -->
                  <div class="col-12">
                    <label for="titre">
                      Titre <span style="color:var(--danger)">*</span>
                    </label>
                    <input type="text"
                           name="titre"
                           id="titre"
                           class="form-control mt-1"
                           value="<?= htmlspecialchars($memoireSelectionne['titre']) ?>"
                           maxlength="255"
                           required>
                    <div class="invalid-feedback">Le titre est obligatoire.</div>
                  </div>

                  <!-- Thème -->
                  <div class="col-12">
                    <label for="theme">
                      Thème <span style="color:var(--danger)">*</span>
                    </label>
                    <input type="text"
                           name="theme"
                           id="theme"
                           class="form-control mt-1"
                           value="<?= htmlspecialchars($memoireSelectionne['theme']) ?>"
                           maxlength="255"
                           required>
                    <div class="invalid-feedback">Le thème est obligatoire.</div>
                  </div>

                  <!-- Type (lecture seule — on ne change pas le type) -->
                  <div class="col-md-6">
                    <label>Type de diplôme</label>
                    <input type="text"
                           class="form-control mt-1"
                           value="<?= htmlspecialchars(ucfirst($memoireSelectionne['type_diplome'])) ?>"
                           readonly
                           style="background:var(--bg-page);cursor:not-allowed">
                    <small style="color:var(--text-muted)">Le type ne peut pas être modifié.</small>
                  </div>

                  <!-- Année -->
                  <div class="col-md-6">
                    <label for="annee_soutenance">
                      Année de soutenance <span style="color:var(--danger)">*</span>
                    </label>
                    <input type="number"
                           name="annee_soutenance"
                           id="annee_soutenance"
                           class="form-control mt-1"
                           value="<?= (int) $memoireSelectionne['annee_soutenance'] ?>"
                           min="2000"
                           max="<?= date('Y') + 1 ?>"
                           required>
                    <div class="invalid-feedback">Année invalide.</div>
                  </div>

                  <!-- Nouveau PDF (optionnel) -->
                  <div class="col-12">
                    <label for="fichier_pdf">Remplacer le PDF <small style="color:var(--text-muted)">(optionnel)</small></label>
                    <div class="mt-1" id="zone-upload"
                         style="border:2px dashed var(--border);border-radius:var(--radius);
                                padding:24px;text-align:center;cursor:pointer;
                                transition:border-color 0.2s,background 0.2s"
                         onclick="document.getElementById('fichier_pdf').click()"
                         ondragover="dragOver(event)"
                         ondragleave="dragLeave(event)"
                         ondrop="dropFichier(event)">
                      <i class="bi bi-file-earmark-pdf"
                         style="font-size:1.8rem;color:var(--text-muted)"></i>
                      <div style="margin-top:6px;color:var(--text-muted)" id="upload-texte">
                        Fichier actuel : <strong><?= htmlspecialchars($memoireSelectionne['fichier_pdf']) ?></strong><br>
                        <small>Cliquez pour remplacer (PDF, max 10 Mo)</small>
                      </div>
                      <input type="file"
                             name="fichier_pdf"
                             id="fichier_pdf"
                             accept=".pdf,application/pdf"
                             style="display:none"
                             onchange="afficherFichier(this)">
                    </div>
                    <div class="invalid-feedback d-block" id="pdf-error"></div>
                  </div>

                </div>

                <div class="d-flex gap-2 mt-4">
                  <button type="submit" class="btn btn-uatm" id="btn-soumettre">
                    <i class="bi bi-save me-1"></i> Enregistrer les modifications
                  </button>
                  <a href="/views/etudiant/dashboard.php" class="btn btn-outline-secondary">
                    Annuler
                  </a>
                </div>

              </form>
            </div>
          </div>

        <?php endif; ?>
      <?php endif; ?>

    </div>
  </div>
</div>

<?php $extraJs = '/public/js/validation.js'; ?>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>
