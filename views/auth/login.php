<?php
$pageTitle = 'Connexion — UATM GASA Formation';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/constants.php';

// Si déjà connecté, rediriger
if (!empty($_SESSION['user_id'])) {
    header('Location: /index.php'); exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $pageTitle ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
  <link href="/public/css/style.css" rel="stylesheet">
</head>
<body>
<div class="login-wrapper">
  <div class="login-card">
    <div class="login-logo">
      <i class="bi bi-mortarboard-fill" style="font-size:2.5rem;color:var(--primary)"></i>
      <div class="mt-2">UATM GASA Formation</div>
      <div style="font-size:0.9rem;color:var(--text-muted);font-weight:400">
        Système de gestion des mémoires
      </div>
    </div>

    <?php if (!empty($_GET['error'])): ?>
      <div class="alert alert-danger py-2">
        <i class="bi bi-exclamation-triangle me-1"></i>
        <?php
          $errors = [
            'identifiants'  => 'Email ou mot de passe incorrect.',
            'compte_inactif'=> 'Votre compte est désactivé. Contactez le service technique.',
            'acces_refuse'  => 'Accès refusé.',
          ];
          echo htmlspecialchars($errors[$_GET['error']] ?? 'Erreur de connexion.');
        ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="/controllers/AuthController.php">
      <input type="hidden" name="action" value="login">

      <div class="mb-3">
        <label for="email">Adresse email</label>
        <div class="input-group mt-1">
          <span class="input-group-text"><i class="bi bi-envelope"></i></span>
          <input type="email" name="email" id="email"
                 class="form-control" placeholder="votre@email.com"
                 required autofocus>
        </div>
      </div>

      <div class="mb-4">
        <label for="mdp">Mot de passe</label>
        <div class="input-group mt-1">
          <span class="input-group-text"><i class="bi bi-lock"></i></span>
          <input type="password" name="mot_de_passe" id="mdp"
                 class="form-control" placeholder="••••••••" required>
          <button class="btn btn-outline-secondary" type="button"
                  onclick="toggleMdp()">
            <i class="bi bi-eye" id="eye-icon"></i>
          </button>
        </div>
      </div>

      <button type="submit" class="btn btn-uatm w-100">
        <i class="bi bi-box-arrow-in-right me-1"></i> Se connecter
      </button>
    </form>

    <p class="text-center mt-3" style="font-size:0.8rem;color:var(--text-muted)">
      Compte créé uniquement par l'administration
    </p>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleMdp() {
  const input = document.getElementById('mdp');
  const icon  = document.getElementById('eye-icon');
  if (input.type === 'password') {
    input.type = 'text';
    icon.className = 'bi bi-eye-slash';
  } else {
    input.type = 'password';
    icon.className = 'bi bi-eye';
  }
}
</script>
</body>
</html>
