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

    if ($action === 'importer_memoires') {
        require_once __DIR__ . '/../dao/MemoireDAO.php';
        require_once __DIR__ . '/../models/Memoire.php';
        
        $dao = new MemoireDAO();
        $fichiers  = $_FILES['fichiers_pdf'] ?? null;
        $titres    = $_POST['titres'] ?? [];
        $themes    = $_POST['themes'] ?? [];
        $types     = $_POST['types_diplome'] ?? [];
        $annees    = $_POST['annees'] ?? [];
        $etudiants = $_POST['etudiants_id'] ?? [];

        if (!$fichiers || empty($titres)) {
            header('Location: ../views/technicien/importer_memoires.php?error=champs_vides');
            exit;
        }

        $nbFichiers = count($fichiers['name']);
        $erreurs = 0;

        for ($i = 0; $i < $nbFichiers; $i++) {
            if ($fichiers['type'][$i] !== 'application/pdf') {
                $erreurs++; continue;
            }
            $nomFichier  = uniqid('memoire_') . '.pdf';
            $destination = __DIR__ . '/../uploads/memoires/' . $nomFichier;

            if (!move_uploaded_file($fichiers['tmp_name'][$i], $destination)) {
                $erreurs++; continue;
            }

            $memoire = new Memoire();
            $memoire->setEtudiantId((int)$etudiants[$i]);
            $memoire->setTitre($titres[$i]);
            $memoire->setTheme($themes[$i]);
            $memoire->setFichierPdf($nomFichier);
            $memoire->setStatut('publie');
            $memoire->setTypeDiplome($types[$i]);
            $memoire->setAnneeSoutenance((int)$annees[$i]);
            $memoire->setRemarques('');

            $dao->ajouterMemoire($memoire);
        }

        if ($erreurs > 0) {
            header("Location: ../views/technicien/importer_memoires.php?success=partiel&erreurs=$erreurs");
        } else {
            header('Location: ../views/technicien/importer_memoires.php?success=ok');
        }
        exit;
    }
}