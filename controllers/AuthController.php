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
     if ($action === 'changer_mdp') {
 
        // 1. L'utilisateur doit être connecté
        requireAuth();
 
        $nouveau    = $_POST['nouveau_mdp']    ?? '';
        $confirmer  = $_POST['confirmer_mdp']  ?? '';
 
        // 2. Validation : champs non vides
        if (empty($nouveau) || empty($confirmer)) {
            header('Location: ../views/auth/change_password.php?error=champs_vides');
            exit;
        }
 
        // 3. Validation : les deux champs identiques
        if ($nouveau !== $confirmer) {
            header('Location: ../views/auth/change_password.php?error=non_identiques');
            exit;
        }
 
        // 4. Validation : longueur minimale 8 caractères
        if (strlen($nouveau) < 8) {
            header('Location: ../views/auth/change_password.php?error=trop_court');
            exit;
        }
 
        // 5. Hash bcrypt du nouveau mot de passe
        $hash = password_hash($nouveau, PASSWORD_DEFAULT);
 
        // 6. Mise à jour en base
        $dao     = new UtilisateurDAO();
        $resultat = $dao->changerMotDePasse((int) $_SESSION['user_id'], $hash);
 
        if (!$resultat) {
            header('Location: ../views/auth/change_password.php?error=bdd');
            exit;
        }
 
        // 7. Mettre à jour la session pour ne plus forcer le changement
        $_SESSION['doit_changer_mdp'] = false;
 
        // 8. Rediriger vers le bon dashboard selon le rôle
        $redirections = [
            ROLE_ETUDIANT   => '/views/etudiant/dashboard.php',
            ROLE_PROFESSEUR => '/views/professeur/dashboard.php',
            ROLE_DIRECTEUR  => '/views/directeur/dashboard.php',
            ROLE_TECHNICIEN => '/views/technicien/dashboard.php',
        ];
 
        $cible = $redirections[$_SESSION['user_role']] ?? '/index.php';
 
        header('Location: ' . $cible);
        exit;
    }
}