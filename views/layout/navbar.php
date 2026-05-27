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
            <?= htmlspecialchars($_SESSION['user_nom'] ?? '') ?>
            <span class="badge bg-warning text-dark ms-1">
              <?= htmlspecialchars($_SESSION['user_role'] ?? '') ?>
            </span>
          </span>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/logout.php">
            <i class="bi bi-box-arrow-right"></i> Déconnexion
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>
