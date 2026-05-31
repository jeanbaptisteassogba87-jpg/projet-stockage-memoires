<?php
// ============================================================
// views/auth/change_password.php
// Affiché automatiquement quand doit_changer_mdp = 1
// Rôle : forcer l'utilisateur à choisir son propre mot de passe
//        avant d'accéder à son espace
// ============================================================

$pageTitle = 'Changer mon mot de passe — UATM GASA Formation';

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/constants.php';

// Doit être connecté pour accéder à cette page
requireAuth();

// Si le flag est déjà à 0, pas besoin d'être ici → dashboard
if (!$_SESSION['doit_changer_mdp']) {
    header('Location: /index.php');
    exit;
}

// Messages d'erreur lisibles
$messages = [
    'champs_vides'   => 'Merci de remplir les deux champs.',
    'non_identiques' => 'Les mots de passe ne correspondent pas.',
    'trop_court'     => 'Le mot de passe doit contenir au moins 8 caractères.',
    'bdd'            => 'Une erreur est survenue. Réessayez.',
];
$erreur = !empty($_GET['error']) ? ($messages[$_GET['error']] ?? 'Erreur inconnue.') : '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle) ?></title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
  <!-- CSS commun UATM -->
  <link href="/public/css/style.css" rel="stylesheet">
</head>
<body>

<div class="login-wrapper">
  <div class="login-card">

    <!-- Logo / en-tête -->
    <div class="login-logo">
      <i class="bi bi-shield-lock-fill" style="font-size:2.5rem;color:var(--primary)"></i>
      <div class="mt-2">Changement de mot de passe</div>
      <div style="font-size:0.85rem;color:var(--text-muted);font-weight:400;margin-top:4px">
        Bonjour <strong><?= htmlspecialchars($_SESSION['user_nom']) ?></strong>,
        vous devez définir un mot de passe personnel avant de continuer.
      </div>
    </div>

    <!-- Alerte erreur -->
    <?php if ($erreur): ?>
      <div class="alert alert-danger py-2 mb-3">
        <i class="bi bi-exclamation-triangle me-1"></i>
        <?= htmlspecialchars($erreur) ?>
      </div>
    <?php endif; ?>

    <!-- Formulaire -->
    <form method="POST" action="/controllers/AuthController.php" novalidate>
      <input type="hidden" name="action" value="changer_mdp">

      <!-- Nouveau mot de passe -->
      <div class="mb-3">
        <label for="nouveau_mdp">Nouveau mot de passe</label>
        <div class="input-group mt-1">
          <span class="input-group-text"><i class="bi bi-lock"></i></span>
          <input type="password"
                 name="nouveau_mdp"
                 id="nouveau_mdp"
                 class="form-control"
                 placeholder="8 caractères minimum"
                 minlength="8"
                 required
                 autofocus>
          <button class="btn btn-outline-secondary" type="button" onclick="toggle('nouveau_mdp','eye1')">
            <i class="bi bi-eye" id="eye1"></i>
          </button>
        </div>
        <!-- Barre de force du mot de passe -->
        <div class="mt-2">
          <div style="height:4px;border-radius:2px;background:var(--border);overflow:hidden">
            <div id="force-barre"
                 style="height:100%;width:0;border-radius:2px;transition:width 0.3s,background 0.3s"></div>
          </div>
          <div id="force-texte"
               style="font-size:0.75rem;color:var(--text-muted);margin-top:3px"></div>
        </div>
      </div>

      <!-- Confirmer -->
      <div class="mb-4">
        <label for="confirmer_mdp">Confirmer le mot de passe</label>
        <div class="input-group mt-1">
          <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
          <input type="password"
                 name="confirmer_mdp"
                 id="confirmer_mdp"
                 class="form-control"
                 placeholder="Répétez le mot de passe"
                 required>
          <button class="btn btn-outline-secondary" type="button" onclick="toggle('confirmer_mdp','eye2')">
            <i class="bi bi-eye" id="eye2"></i>
          </button>
        </div>
        <!-- Indicateur de correspondance -->
        <div id="match-msg"
             style="font-size:0.75rem;margin-top:3px;min-height:16px"></div>
      </div>

      <button type="submit" class="btn btn-uatm w-100">
        <i class="bi bi-check-circle me-1"></i> Enregistrer mon mot de passe
      </button>
    </form>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ── Afficher / masquer le mot de passe ──────────────────────
function toggle(inputId, iconId) {
  const input = document.getElementById(inputId);
  const icon  = document.getElementById(iconId);
  if (input.type === 'password') {
    input.type = 'text';
    icon.className = 'bi bi-eye-slash';
  } else {
    input.type = 'password';
    icon.className = 'bi bi-eye';
  }
}

// ── Barre de force du mot de passe ──────────────────────────
// Critères : longueur 8+, majuscule, chiffre, caractère spécial
const champNouv  = document.getElementById('nouveau_mdp');
const barre      = document.getElementById('force-barre');
const texte      = document.getElementById('force-texte');

champNouv.addEventListener('input', function () {
  const val    = this.value;
  let score    = 0;
  if (val.length >= 8)              score++;
  if (/[A-Z]/.test(val))            score++;
  if (/[0-9]/.test(val))            score++;
  if (/[^A-Za-z0-9]/.test(val))     score++;

  const niveaux = [
    { pct: '0%',   bg: 'transparent', label: '' },
    { pct: '25%',  bg: '#DC3545',     label: 'Faible' },
    { pct: '50%',  bg: '#FFC107',     label: 'Moyen' },
    { pct: '75%',  bg: '#17A2B8',     label: 'Bon' },
    { pct: '100%', bg: '#28A745',     label: 'Fort' },
  ];

  const n = niveaux[score] || niveaux[0];
  barre.style.width      = n.pct;
  barre.style.background = n.bg;
  texte.textContent      = n.label;
  texte.style.color      = n.bg;

  // Revérifier la correspondance quand le champ principal change
  verifierMatch();
});

// ── Vérification de correspondance en temps réel ────────────
const champConf = document.getElementById('confirmer_mdp');
const matchMsg  = document.getElementById('match-msg');

function verifierMatch() {
  const a = champNouv.value;
  const b = champConf.value;

  if (!b) {
    matchMsg.textContent = '';
    return;
  }

  if (a === b) {
    matchMsg.textContent = '✓ Les mots de passe correspondent';
    matchMsg.style.color = '#28A745';
  } else {
    matchMsg.textContent = '✗ Les mots de passe ne correspondent pas';
    matchMsg.style.color = '#DC3545';
  }
}

champConf.addEventListener('input', verifierMatch);
</script>
</body>
</html>
