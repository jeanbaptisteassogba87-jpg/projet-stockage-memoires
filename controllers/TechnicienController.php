<?php

require_once __DIR__ . '/../models/Utilisateur.php';
require_once __DIR__ . '/../dao/UtilisateurDAO.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    
    if ($action === 'creer_utilisateur') {

        $utilisateur = new Utilisateur();

        $utilisateur->setNom($_POST['nom']);

        $utilisateur->setEmail($_POST['email']);

        $motDePasseHash = password_hash(
            $_POST['mot_de_passe'],
            PASSWORD_DEFAULT
        );

        $utilisateur->setMotDePasse($motDePasseHash);

        $utilisateur->setRole($_POST['role']);

        $utilisateur->setCentreId($_POST['centre_id']);

       
        $utilisateur->setEstActif(1);

        $utilisateur->setDoitChangerMdp(1);

        $dao = new UtilisateurDAO();

        $resultat = $dao->creerUtilisateur($utilisateur);

        if ($resultat) {

            header('Location: ../views/technicien/gerer_comptes.php?success=1');

            exit;

        } else {

            header('Location: ../views/technicien/gerer_comptes.php?error=1');

            exit;
        }
    }

   
    if ($action === 'desactiver_utilisateur') {

        $id = $_POST['id_utilisateur'];

        $dao = new UtilisateurDAO();

        $dao->desactiverUtilisateur($id);

        header('Location: ../views/technicien/gerer_comptes.php');

        exit;
    }
}