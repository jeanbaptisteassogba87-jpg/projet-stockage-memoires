<?php

require_once __DIR__ . '/../../config/session.php';

requireRole('technicien');

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <title>Dashboard Technicien</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet">

</head>

<body class="container mt-5">

    <h1>Dashboard Technicien</h1>

    <div class="alert alert-success mt-4">

        Bienvenue <?= $_SESSION['user_nom'] ?>

    </div>

    <a href="gerer_comptes.php"
       class="btn btn-primary">

       Gérer les comptes

    </a>

    <a href="/projet-stockage-memoires-main/logout.php"
       class="btn btn-danger">

       Déconnexion

    </a>

</body>

</html>