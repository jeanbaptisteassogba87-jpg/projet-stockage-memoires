<?php

require_once __DIR__ . '/../dao/UtilisateurDAO.php';
require_once __DIR__ . '/../config/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    if ($action === 'login') {

        $email = trim($_POST['email'] ?? '');
        $motDePasse = trim($_POST['mot_de_passe'] ?? '');

        $dao = new UtilisateurDAO();

        $user = $dao->trouverParEmail($email);

        // utilisateur introuvable
        if (!$user) {

            header('Location: ../views/auth/login.php?error=identifiants');
            exit;

        }

        // compte inactif
        if (!$user['est_actif']) {

            header('Location: ../views/auth/login.php?error=compte_inactif');
            exit;

        }

        // mot de passe incorrect
        if (!password_verify($motDePasse, $user['mot_de_passe'])) {

    header('Location: ../views/auth/login.php?error=identifiants');

    exit;
}
        // connexion réussie
        loginUser($user);

        header('Location: ../index.php');
        exit;
    }
}