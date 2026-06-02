<?php


// Rôle : point d'entrée POST pour toutes les actions étudiant
// action=deposer_memoire  → upload PDF + insertion BDD
// action=modifier_memoire → remplacement PDF + update BDD


require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../dao/MemoireDAO.php';
require_once __DIR__ . '/../dao/EtudiantDAO.php';
require_once __DIR__ . '/../dao/UtilisateurDAO.php';
require_once __DIR__ . '/../models/Memoire.php';

// Seules les requêtes POST sont traitées ici
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /views/etudiant/dashboard.php');
    exit;
}

// L'utilisateur doit être connecté et avoir le rôle étudiant
requireRole(ROLE_ETUDIANT);

$action = $_POST['action'] ?? '';


// ACTION : déposer un mémoire

if ($action === 'deposer_memoire') {

    // --- 1. Récupération et nettoyage des champs texte ------
    $titre           = trim($_POST['titre']            ?? '');
    $theme           = trim($_POST['theme']            ?? '');
    $type_diplome    = trim($_POST['type_diplome']     ?? '');
    $annee           = (int) ($_POST['annee_soutenance'] ?? 0);
    $professeurId    = (int) ($_POST['professeur_id']  ?? 0);
    $etudiant2Id     = (int) ($_POST['etudiant2_id']   ?? 0);

    // --- 2. Validation des champs obligatoires --------------
    if (empty($titre) || empty($theme) || empty($type_diplome) || $annee < 2000 || $professeurId <= 0) {
        header('Location: /views/etudiant/deposer_memoire.php?error=champs_vides');
        exit;
    }

    // type_diplome doit être licence ou master (valeurs de constants.php)
    if (!in_array($type_diplome, [DIPLOME_LICENCE, DIPLOME_MASTER], true)) {
        header('Location: /views/etudiant/deposer_memoire.php?error=type_invalide');
        exit;
    }

    // --- 3. Vérifier que l'étudiant a le niveau requis ------
    // On relit le niveau depuis la BDD (jamais faire confiance à la session seule)
    $etudiantDAO = new EtudiantDAO();
    $etudiant    = $etudiantDAO->trouverParId((int) $_SESSION['user_id']);

    if (!$etudiant || !in_array($etudiant['niveau_etude'], NIVEAUX_DEPOT, true)) {
        header('Location: /views/etudiant/deposer_memoire.php?error=niveau_insuffisant');
        exit;
    }

    // --- 3.1 Vérifier la cohérence type/niveau ----------------
    $typeAutorise = [
        'L3' => DIPLOME_LICENCE,
        'M2' => DIPLOME_MASTER,
    ];

    if (!isset($typeAutorise[$etudiant['niveau_etude']])
        || $typeAutorise[$etudiant['niveau_etude']] !== $type_diplome) {
        header('Location: /views/etudiant/deposer_memoire.php?error=type_invalide');
        exit;
    }

    // --- 4. Vérifier l'encadreur choisi ---------------------
    $utilisateurDAO = new UtilisateurDAO();
    $professeurs    = array_filter(
        $utilisateurDAO->listerProfesseurs(),
        fn($professeur) => (int) $professeur['centre_id'] === (int) $etudiant['centre_id']
    );
    $professeurIds  = array_map('intval', array_column($professeurs, 'id_utilisateur'));

    if (!in_array($professeurId, $professeurIds, true)) {
        header('Location: /views/etudiant/deposer_memoire.php?error=professeur_invalide');
        exit;
    }

    // --- 5. Vérifier l'unicité (un seul mémoire par type de diplôme) ---
    $memoireDAO = new MemoireDAO();
    $existant   = $memoireDAO->trouverParEtudiantEtType(
        (int) $_SESSION['user_id'],
        $type_diplome
    );

    if ($existant) {
        header('Location: /views/etudiant/deposer_memoire.php?error=doublon');
        exit;
    }

    // --- 6. Vérifier le binôme optionnel --------------------
    if ($etudiant2Id > 0) {
        $binomesPossibles = $etudiantDAO->chercherBinomePossible(
            (int) $etudiant['filiere_id'],
            $etudiant['niveau_etude'],
            (int) $_SESSION['user_id']
        );
        $binomeIds = array_map('intval', array_column($binomesPossibles, 'id_utilisateur'));

        if (!in_array($etudiant2Id, $binomeIds, true)) {
            header('Location: /views/etudiant/deposer_memoire.php?error=binome_invalide');
            exit;
        }

        $existantBinome = $memoireDAO->trouverParEtudiantEtType($etudiant2Id, $type_diplome);

        if ($existantBinome) {
            header('Location: /views/etudiant/deposer_memoire.php?error=binome_doublon');
            exit;
        }
    } else {
        $etudiant2Id = null;
    }

    // --- 7. Validation du fichier PDF -----------------------
    $fichier = $_FILES['fichier_pdf'] ?? null;

    if (!$fichier || $fichier['error'] !== UPLOAD_ERR_OK) {
        header('Location: /views/etudiant/deposer_memoire.php?error=fichier_manquant');
        exit;
    }

    // Vérifier le type MIME réel (pas juste l'extension)
    $finfo    = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $fichier['tmp_name']);
    finfo_close($finfo);

    if ($mimeType !== 'application/pdf') {
        header('Location: /views/etudiant/deposer_memoire.php?error=pas_pdf');
        exit;
    }

    // Vérifier la taille (MAX_PDF_SIZE défini dans constants.php = 10 Mo)
    if ($fichier['size'] > MAX_PDF_SIZE) {
        header('Location: /views/etudiant/deposer_memoire.php?error=trop_lourd');
        exit;
    }

    // --- 8. Déplacer le fichier dans le dossier de stockage ---
    // Le dossier uploads/ est hors webroot (cf. uploads/.htaccess Deny from all)
    // Le nom du fichier = prefixe unique + extension .pdf
    $nomFichier  = uniqid('mem_', true) . '.pdf';
    $destination = PDF_STORAGE_PATH . $nomFichier;   // défini dans constants.php

    if (!move_uploaded_file($fichier['tmp_name'], $destination)) {
        header('Location: /views/etudiant/deposer_memoire.php?error=upload_echec');
        exit;
    }

    // --- 9. Construire l'objet Memoire et insérer -----------
    $memoire = new Memoire();
    $memoire->setEtudiantId((int) $_SESSION['user_id']);
    $memoire->setEtudiant2Id($etudiant2Id);
    $memoire->setTitre($titre);
    $memoire->setTheme($theme);
    $memoire->setFichierPdf($nomFichier);
    $memoire->setStatut(STATUT_EN_ATTENTE);     // toujours en_attente à la création
    $memoire->setTypeDiplome($type_diplome);
    $memoire->setAnneeSoutenance($annee);
    $memoire->setRemarques('');                 // pas encore de remarques
    $memoire->setProfesseurId($professeurId);

    $ok = $memoireDAO->ajouterMemoire($memoire);

    if (!$ok) {
        // Si l'insertion BDD échoue, supprimer le fichier déjà uploadé
        @unlink($destination);
        header('Location: /views/etudiant/deposer_memoire.php?error=bdd');
        exit;
    }

    // --- 10. Succès → retour au dashboard ------------------
    header('Location: /views/etudiant/dashboard.php?success=depot_ok');
    exit;
}


// ACTION : modifier un mémoire existant
// Autorisé uniquement si statut = en_attente ou rejete

if ($action === 'modifier_memoire') {

    $id_memoire   = (int) ($_POST['id_memoire']        ?? 0);
    $titre        = trim($_POST['titre']               ?? '');
    $theme        = trim($_POST['theme']               ?? '');
    $annee        = (int) ($_POST['annee_soutenance']  ?? 0);

    // --- 1. Charger le mémoire existant et vérifier ownership ---
    $memoireDAO = new MemoireDAO();
    $memoire    = $memoireDAO->trouverParId($id_memoire);

    $estAuteurOuBinome = $memoire && (
        (int) $memoire['etudiant_id'] === (int) $_SESSION['user_id']
        || (int) ($memoire['etudiant2_id'] ?? 0) === (int) $_SESSION['user_id']
    );

    if (!$estAuteurOuBinome) {
        header('Location: /views/etudiant/dashboard.php?error=non_autorise');
        exit;
    }

    // --- 2. Vérifier que le statut autorise la modification ---
    $statutsModifiables = [STATUT_EN_ATTENTE, STATUT_REJETE];
    if (!in_array($memoire['statut'], $statutsModifiables)) {
        header('Location: /views/etudiant/modifier_memoire.php?id=' . $id_memoire . '&error=statut_bloque');
        exit;
    }

    // --- 3. Validation des champs ---
    if (empty($titre) || empty($theme) || $annee < 2000) {
        header('Location: /views/etudiant/modifier_memoire.php?id=' . $id_memoire . '&error=champs_vides');
        exit;
    }

    // --- 4. Préparer les données à mettre à jour ---
    $data = [
        'titre'            => $titre,
        'theme'            => $theme,
        'annee_soutenance' => $annee,
        'statut'           => STATUT_EN_ATTENTE, // repasse en attente après modification
    ];

    // --- 5. Si un nouveau PDF est fourni, le remplacer ---
    if (isset($_FILES['fichier_pdf']) && $_FILES['fichier_pdf']['error'] === UPLOAD_ERR_OK) {

        $fichier = $_FILES['fichier_pdf'];

        $finfo    = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $fichier['tmp_name']);
        finfo_close($finfo);

        if ($mimeType !== 'application/pdf') {
            header('Location: /views/etudiant/modifier_memoire.php?id=' . $id_memoire . '&error=pas_pdf');
            exit;
        }

        if ($fichier['size'] > MAX_PDF_SIZE) {
            header('Location: /views/etudiant/modifier_memoire.php?id=' . $id_memoire . '&error=trop_lourd');
            exit;
        }

        $nomFichier  = uniqid('mem_', true) . '.pdf';
        $destination = PDF_STORAGE_PATH . $nomFichier;

        if (!move_uploaded_file($fichier['tmp_name'], $destination)) {
            header('Location: /views/etudiant/modifier_memoire.php?id=' . $id_memoire . '&error=upload_echec');
            exit;
        }

        // Supprimer l'ancien fichier PDF du serveur
        $ancienFichier = PDF_STORAGE_PATH . $memoire['fichier_pdf'];
        if (file_exists($ancienFichier)) {
            @unlink($ancienFichier);
        }

        $data['fichier_pdf'] = $nomFichier;
    }

    // --- 6. Mettre à jour en BDD ---
    $ok = $memoireDAO->modifierMemoire($id_memoire, $data);

    if (!$ok) {
        header('Location: /views/etudiant/modifier_memoire.php?id=' . $id_memoire . '&error=bdd');
        exit;
    }

    header('Location: /views/etudiant/dashboard.php?success=modif_ok');
    exit;
}

// Si action inconnue → retour dashboard
header('Location: /views/etudiant/dashboard.php');
exit;
