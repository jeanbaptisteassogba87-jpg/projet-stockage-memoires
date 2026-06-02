<?php

// Rôle : formulaire permettant à l'étudiant de déposer son mémoire
//        Accessible uniquement si niveau L3 ou M2


$pageTitle = 'Déposer mon mémoire — UATM GASA Formation';

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../dao/EtudiantDAO.php';
require_once __DIR__ . '/../../dao/MemoireDAO.php';
require_once __DIR__ . '/../../dao/UtilisateurDAO.php';

requireRole(ROLE_ETUDIANT);

// Redirection si changement de mdp requis
if ($_SESSION['doit_changer_mdp']) {
    header('Location: /views/auth/change_password.php');
    exit;
}

// Charger les infos étudiant depuis la BDD
$etudiantDAO = new EtudiantDAO();
$etudiant    = $etudiantDAO->trouverParId((int) $_SESSION['user_id']);

// Vérifier le niveau (L3 ou M2 seulement)
if (!$etudiant || !in_array($etudiant['niveau_etude'], NIVEAUX_DEPOT)) {
    header('Location: /views/etudiant/dashboard.php?error=niveau_insuffisant');
    exit;
}

// Messages d'erreur
$messages = [
    'champs_vides'       => 'Merci de remplir tous les champs obligatoires.',
    'type_invalide'      => 'Type de diplôme non reconnu.',
    'niveau_insuffisant' => 'Votre niveau ne vous permet pas de déposer un mémoire.',
    'doublon'            => 'Vous avez déjà déposé un mémoire pour ce type de diplôme.',
    'fichier_manquant'   => 'Merci de joindre un fichier PDF.',
    'pas_pdf'            => 'Le fichier doit être au format PDF.',
    'trop_lourd'         => 'Le fichier dépasse la taille maximale autorisée (10 Mo).',
    'upload_echec'       => 'Erreur lors de l\'upload. Réessayez.',
    'bdd'                => 'Erreur lors de l\'enregistrement. Contactez l\'administration.',
    'professeur_invalide'=> 'L\'encadreur choisi est invalide.',
    'binome_invalide'    => 'Le binôme choisi doit être de la même filière et du même niveau.',
    'binome_doublon'     => 'Ce binôme a déjà un mémoire pour ce type de diplôme.',
];
$erreur = !empty($_GET['error']) ? ($messages[$_GET['error']] ?? 'Erreur inconnue.') : '';

// Vérifier les mémoires déjà déposés (pour griser les types déjà utilisés)
$memoireDAO     = new MemoireDAO();
$deja_licence   = $memoireDAO->trouverParEtudiantEtType((int) $_SESSION['user_id'], DIPLOME_LICENCE);
$deja_master    = $memoireDAO->trouverParEtudiantEtType((int) $_SESSION['user_id'], DIPLOME_MASTER);

$utilisateurDAO = new UtilisateurDAO();
$professeurs    = array_filter(
    $utilisateurDAO->listerProfesseurs(),
    fn($professeur) => (int) $professeur['centre_id'] === (int) $etudiant['centre_id']
);
$binomes        = $etudiantDAO->chercherBinomePossible(
    (int) $etudiant['filiere_id'],
    $etudiant['niveau_etude'],
    (int) $_SESSION['user_id']
);
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

    <!-- Contenu -->
    <div class="col-md-10 p-4">

      <h2 class="section-title">Déposer un mémoire</h2>

      <!-- Info niveau -->
      <div class="alert mb-4"
           style="background:#EEF2F8;border-left:4px solid var(--primary);border-radius:var(--radius)">
        <i class="bi bi-info-circle me-2" style="color:var(--primary)"></i>
        Vous êtes inscrit en <strong><?= htmlspecialchars($etudiant['niveau_etude']) ?></strong>.
        <?php if ($etudiant['niveau_etude'] === 'L3'): ?>
          Vous pouvez déposer un mémoire de <strong>Licence</strong>.
        <?php else: ?>
          Vous pouvez déposer un mémoire de <strong>Master</strong>.
        <?php endif; ?>
      </div>

      <!-- Alerte erreur -->
      <?php if ($erreur): ?>
        <div class="alert alert-danger mb-4">
          <i class="bi bi-exclamation-triangle me-2"></i>
          <?= htmlspecialchars($erreur) ?>
        </div>
      <?php endif; ?>

      <div class="card">
        <div class="card-header-uatm">
          <i class="bi bi-file-earmark-arrow-up me-2"></i>Nouveau dépôt
        </div>
        <div class="card-body p-4">

          <form method="POST"
                action="/controllers/EtudiantController.php"
                enctype="multipart/form-data"
                id="form-depot"
                novalidate>

            <input type="hidden" name="action" value="deposer_memoire">

            <div class="row g-3">

              <!-- Titre -->
              <div class="col-12">
                <label for="titre">
                  Titre du mémoire <span style="color:var(--danger)">*</span>
                </label>
                <input type="text"
                       name="titre"
                       id="titre"
                       class="form-control mt-1"
                       placeholder="Ex : Mise en place d'une infrastructure réseau sécurisée"
                       maxlength="255"
                       required>
                <div class="invalid-feedback">Le titre est obligatoire.</div>
              </div>

              <!-- Thème -->
              <div class="col-12">
                <label for="theme">
                  Thème / domaine <span style="color:var(--danger)">*</span>
                </label>
                <input type="text"
                       name="theme"
                       id="theme"
                       class="form-control mt-1"
                       placeholder="Ex : Sécurité informatique, Réseaux, Gestion..."
                       maxlength="255"
                       required>
                <div class="invalid-feedback">Le thème est obligatoire.</div>
              </div>

              <!-- Type de diplôme -->
              <div class="col-md-6">
                <label for="type_diplome">
                  Type de diplôme <span style="color:var(--danger)">*</span>
                </label>
                <select name="type_diplome" id="type_diplome" class="form-select mt-1" required>
                  <option value="">— Choisir —</option>

                  <!-- Licence : désactivée si déjà déposée -->
                  <option value="licence"
                          <?= ($deja_licence ? 'disabled' : '') ?>
                          <?= ($etudiant['niveau_etude'] === 'L3' ? 'selected' : '') ?>>
                    Licence<?= $deja_licence ? ' (déjà déposé)' : '' ?>
                  </option>

                  <!-- Master : désactivée si déjà déposée -->
                  <option value="master"
                          <?= ($deja_master ? 'disabled' : '') ?>
                          <?= ($etudiant['niveau_etude'] === 'M2' ? 'selected' : '') ?>>
                    Master<?= $deja_master ? ' (déjà déposé)' : '' ?>
                  </option>

                </select>
                <div class="invalid-feedback">Choisissez un type de diplôme.</div>
              </div>

              <!-- Année de soutenance -->
              <div class="col-md-6">
                <label for="annee_soutenance">
                  Année de soutenance <span style="color:var(--danger)">*</span>
                </label>
                <input type="number"
                       name="annee_soutenance"
                       id="annee_soutenance"
                       class="form-control mt-1"
                       value="<?= date('Y') ?>"
                       min="2000"
                       max="<?= date('Y') + 1 ?>"
                       required>
                <div class="invalid-feedback">Année invalide (entre 2000 et <?= date('Y') + 1 ?>).</div>
              </div>

              <!-- Encadreur -->
              <div class="col-md-6">
                <label for="professeur_id">
                  Encadreur <span style="color:var(--danger)">*</span>
                </label>
                <select name="professeur_id" id="professeur_id" class="form-select mt-1" required>
                  <option value="">— Choisir un professeur —</option>
                  <?php foreach ($professeurs as $professeur): ?>
                    <option value="<?= (int) $professeur['id_utilisateur'] ?>">
                      <?= htmlspecialchars($professeur['nom']) ?>
                      <?php if (!empty($professeur['specialite'])): ?>
                        — <?= htmlspecialchars($professeur['specialite']) ?>
                      <?php endif; ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <div class="invalid-feedback">Choisissez un encadreur.</div>
              </div>

              <!-- Binôme optionnel -->
              <div class="col-md-6">
                <label for="etudiant2_recherche">
                  Étudiant 2 <span style="color:var(--text-muted);font-size:0.85rem">(optionnel)</span>
                </label>
                <div class="position-relative mt-1">
                  <input type="text"
                         id="etudiant2_recherche"
                         class="form-control"
                         placeholder="Rechercher par nom, email ou matricule"
                         autocomplete="off"
                         aria-describedby="binome_selection">
                  <input type="hidden" name="etudiant2_id" id="etudiant2_id">

                  <div id="resultats-binomes"
                       class="list-group position-absolute w-100 shadow-sm"
                       style="z-index:10;max-height:220px;overflow:auto;display:none">
                  </div>
                </div>
                <div id="binome_selection" class="form-text">
                  Même filière, même niveau.
                </div>
              </div>

              <!-- Fichier PDF -->
              <div class="col-12">
                <label for="fichier_pdf">
                  Fichier PDF <span style="color:var(--danger)">*</span>
                </label>
                <div class="mt-1" id="zone-upload"
                     style="border:2px dashed var(--border);border-radius:var(--radius);
                            padding:32px;text-align:center;cursor:pointer;
                            transition:border-color 0.2s,background 0.2s"
                     onclick="document.getElementById('fichier_pdf').click()"
                     ondragover="dragOver(event)"
                     ondragleave="dragLeave(event)"
                     ondrop="dropFichier(event)">
                  <i class="bi bi-cloud-arrow-up"
                     style="font-size:2rem;color:var(--text-muted)"></i>
                  <div style="margin-top:8px;color:var(--text-muted)" id="upload-texte">
                    Cliquez ou glissez-déposez votre PDF ici<br>
                    <small>Taille maximale : 10 Mo</small>
                  </div>
                  <input type="file"
                         name="fichier_pdf"
                         id="fichier_pdf"
                         accept=".pdf,application/pdf"
                         style="display:none"
                         required
                         onchange="afficherFichier(this)">
                </div>
                <div class="invalid-feedback d-block" id="pdf-error" style="display:none !important"></div>
              </div>

            </div>

            <!-- Boutons -->
            <div class="d-flex gap-2 mt-4">
              <button type="submit" class="btn btn-uatm" id="btn-soumettre">
                <i class="bi bi-send me-1"></i> Soumettre le mémoire
              </button>
              <a href="/views/etudiant/dashboard.php" class="btn btn-outline-secondary">
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
document.addEventListener('DOMContentLoaded', function () {
  const champRecherche = document.getElementById('etudiant2_recherche');
  const champId = document.getElementById('etudiant2_id');
  const resultats = document.getElementById('resultats-binomes');
  const binomes = <?= json_encode(array_values($binomes), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;

  if (!champRecherche || !champId || !resultats) {
    return;
  }

  function fermerResultats() {
    resultats.style.display = 'none';
    resultats.innerHTML = '';
  }

  function afficherResultats(items) {
    resultats.innerHTML = '';

    if (items.length === 0) {
      const vide = document.createElement('div');
      vide.className = 'list-group-item text-muted';
      vide.textContent = 'Aucun étudiant correspondant.';
      resultats.appendChild(vide);
      resultats.style.display = 'block';
      return;
    }

    items.slice(0, 8).forEach(function (binome) {
      const bouton = document.createElement('button');
      bouton.type = 'button';
      bouton.className = 'list-group-item list-group-item-action';

      const nom = document.createElement('strong');
      nom.textContent = binome.nom;

      const details = document.createElement('div');
      details.className = 'small text-muted';
      details.textContent = binome.email + ' · ' + binome.numero_etudiant + ' · ' + binome.niveau_etude;

      bouton.appendChild(nom);
      bouton.appendChild(details);

      bouton.addEventListener('click', function () {
        champRecherche.value = binome.nom + ' — ' + binome.numero_etudiant;
        champId.value = binome.id_utilisateur;
        fermerResultats();
      });

      resultats.appendChild(bouton);
    });

    resultats.style.display = 'block';
  }

  champRecherche.addEventListener('input', function () {
    const recherche = champRecherche.value.trim().toLowerCase();
    champId.value = '';

    if (recherche.length < 2) {
      fermerResultats();
      return;
    }

    afficherResultats(binomes.filter(function (binome) {
      return [binome.nom, binome.email, binome.numero_etudiant]
        .join(' ')
        .toLowerCase()
        .includes(recherche);
    }));
  });

  document.addEventListener('click', function (event) {
    if (!resultats.contains(event.target) && event.target !== champRecherche) {
      fermerResultats();
    }
  });
});
</script>

<?php $extraJs = '/public/js/validation.js'; ?>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>
