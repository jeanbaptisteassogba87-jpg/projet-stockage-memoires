<?php

require_once __DIR__ . '/../../config/session.php';

requireRole('directeur');

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Directeur</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body class="container mt-5">

    <h1>Dashboard Directeur</h1>

    <div class="alert alert-success mt-4">

        Bienvenue <?= $_SESSION['user_nom'] ?>

    </div>

    <a href="../../logout.php"
       class="btn btn-danger">

       Déconnexion

    </a>

</body>
</html>