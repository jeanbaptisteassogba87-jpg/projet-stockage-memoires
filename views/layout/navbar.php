<?php require_once __DIR__ . '/../../config/session.php'; ?>
<nav class="navbar navbar-expand-lg navbar-uatm">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="/index.php">
      <i class="bi bi-mortarboard-fill me-2"></i>UATM GASA Formation
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item">
          <span class="nav-link text-white-50">
            <i class="bi bi-person-circle me-1"></i>
            <?= htmlspecialchars($_SESSION['user_nom'] ?? 'Visiteur') ?>
            <span class="badge bg-warning text-dark ms-1">
              <?= htmlspecialchars($_SESSION['user_role'] ?? 'lecture seule') ?>
            </span>
          </span>
        </li>
        <li class="nav-item">
          <?php if (!empty($_SESSION['user_id'])): ?>
            <form method="POST" action="/logout.php" class="d-inline">
              <?= csrfField() ?>
              <button class="nav-link bg-transparent border-0" type="submit">
                <i class="bi bi-box-arrow-right"></i> Déconnexion
              </button>
            </form>
          <?php else: ?>
            <a class="nav-link" href="/views/auth/login.php">
              <i class="bi bi-box-arrow-in-right"></i> Connexion
            </a>
          <?php endif; ?>
        </li>
      </ul>
    </div>
  </div>
</nav>
