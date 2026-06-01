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
    

    if ($action === 'importer_utilisateurs') {

        require_once __DIR__ . '/../dao/UtilisateurDAO.php';
        require_once __DIR__ . '/../models/Utilisateur.php';

        // --- 1. Vérifier le fichier CSV -------------------------
        $fichier = $_FILES['fichier_csv'] ?? null;

        if (!$fichier || $fichier['error'] !== UPLOAD_ERR_OK) {
            header('Location: ../views/technicien/importer_utilisateurs.php?error=fichier_manquant');
            exit;
        }

        // Vérifier le type MIME
        $finfo    = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $fichier['tmp_name']);
        finfo_close($finfo);

        // Les CSV peuvent avoir différents types MIME selon l'OS
        $mimesCsv = ['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'];
        if (!in_array($mimeType, $mimesCsv) && !str_ends_with($fichier['name'], '.csv')) {
            header('Location: ../views/technicien/importer_utilisateurs.php?error=pas_csv');
            exit;
        }

        // --- 2. Lire et parser le CSV ---------------------------
        $handle = fopen($fichier['tmp_name'], 'r');
        if (!$handle) {
            header('Location: ../views/technicien/importer_utilisateurs.php?error=bdd');
            exit;
        }

        // Lire la ligne d'en-tête (on l'ignore)
        fgetcsv($handle, 0, ',');

        $dao           = new UtilisateurDAO();
        $mdpTemp       = trim($_POST['mdp_temporaire'] ?? 'Uatm2024!');
        $mdpHash       = password_hash($mdpTemp, PASSWORD_DEFAULT);

        $nbSuccess     = 0;
        $nbErreurs     = 0;
        $nbDoublons    = 0;
        $nbTotal       = 0;
        $details       = [];

        // Rôles valides
        $rolesValides  = ['etudiant', 'professeur', 'directeur', 'technicien'];
        $niveauxValides = ['L1', 'L2', 'L3', 'M1', 'M2'];

        // --- 3. Traiter chaque ligne ----------------------------
        while (($ligne = fgetcsv($handle, 0, ',')) !== false) {

            $nbTotal++;

            // Colonnes : nom, email, role, centre_id, niveau_etude, filiere_id, numero_etudiant
            if (count($ligne) < 4) {
                $nbErreurs++;
                $details[] = "Ligne $nbTotal : format invalide (colonnes insuffisantes)";
                continue;
            }

            $nom        = trim($ligne[0] ?? '');
            $email      = trim($ligne[1] ?? '');
            $role       = trim($ligne[2] ?? '');
            $centreId   = (int) trim($ligne[3] ?? 0);
            $niveau     = trim($ligne[4] ?? '');
            $filiereId  = (int) trim($ligne[5] ?? 0);
            $numEtud    = trim($ligne[6] ?? '');

            // Validation basique
            if (empty($nom) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $nbErreurs++;
                $details[] = "Ligne $nbTotal ($email) : nom ou email invalide";
                continue;
            }

            if (!in_array($role, $rolesValides)) {
                $nbErreurs++;
                $details[] = "Ligne $nbTotal ($email) : rôle '$role' invalide";
                continue;
            }

            if ($centreId <= 0) {
                $nbErreurs++;
                $details[] = "Ligne $nbTotal ($email) : centre_id invalide";
                continue;
            }

            // Vérifier les doublons (email déjà existant)
            $existant = $dao->trouverParEmail($email);
            if ($existant) {
                $nbDoublons++;
                continue; // on ignore silencieusement les doublons
            }

            // Créer l'utilisateur
            $utilisateur = new Utilisateur();
            $utilisateur->setNom($nom);
            $utilisateur->setEmail($email);
            $utilisateur->setMotDePasse($mdpHash);
            $utilisateur->setRole($role);
            $utilisateur->setCentreId($centreId);
            $utilisateur->setEstActif(1);
            $utilisateur->setDoitChangerMdp(1); // doit changer le mdp à la première connexion

            $ok = $dao->creerUtilisateur($utilisateur);

            if (!$ok) {
                $nbErreurs++;
                $details[] = "Ligne $nbTotal ($email) : erreur insertion BDD";
                continue;
            }

            // Si étudiant : insérer aussi dans la table etudiant
            if ($role === 'etudiant' && !empty($niveau) && in_array($niveau, $niveauxValides)) {
                $idNouvel = $dao->getDernierId();
                if ($idNouvel && $filiereId > 0 && !empty($numEtud)) {
                    $dao->creerEtudiant($idNouvel, $numEtud, $niveau, $filiereId);
                }
            }

            $nbSuccess++;
        }

        fclose($handle);

        // --- 4. Stocker le résumé en session pour l'afficher ----
        $_SESSION['resume_import'] = [
            'success'  => $nbSuccess,
            'erreurs'  => $nbErreurs,
            'doublons' => $nbDoublons,
            'total'    => $nbTotal,
            'details'  => $details,
        ];

        $statut = ($nbErreurs === 0) ? 'ok' : 'partiel';
        header('Location: ../views/technicien/importer_utilisateurs.php?success=' . $statut);
        exit;
    }
}