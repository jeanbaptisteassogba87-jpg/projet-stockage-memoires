<?php

require_once __DIR__ . '/../dao/MemoireDAO.php';
require_once __DIR__ . '/../dao/EtudiantDAO.php';
require_once __DIR__ . '/../models/Memoire.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';

class EtudiantController {
    
    private MemoireDAO $memoireDAO;
    private EtudiantDAO $etudiantDAO;

    public function __construct() {
        $this->memoireDAO = new MemoireDAO();
        $this->etudiantDAO = new EtudiantDAO();
    }

    /**
     * Ajouter un nouveau mémoire
     * POST: titre, theme, type_diplome, annee_soutenance, fichier_pdf
     */
    public function ajouterMemoire(): void {
        requireRole(ROLE_ETUDIANT);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /views/etudiant/deposer_memoire.php');
            exit;
        }

        $profilEtudiant = $this->etudiantDAO->trouverProfilParUtilisateurId((int)$_SESSION['user_id']);

        if (!$profilEtudiant || !$this->etudiantDAO->peutDeposer((int)$_SESSION['user_id'])) {
            $_SESSION['upload_errors'] = [
                'Vous n\'etes pas autorise a deposer un memoire. Seuls les etudiants L3, M2 ou diplomes permanents peuvent le faire.'
            ];
            header('Location: /views/etudiant/deposer_memoire.php');
            exit;
        }

        // Récupérer et valider les données
        $titre = trim($_POST['titre'] ?? '');
        $theme = trim($_POST['theme'] ?? '');
        $typeDiplome = trim($_POST['type_diplome'] ?? '');
        $anneeSoutenance = (int)($_POST['annee_soutenance'] ?? 0);

        // Validations basiques
        $erreurs = [];

        if (empty($titre) || strlen($titre) < 5) {
            $erreurs[] = 'Le titre doit contenir au moins 5 caractères';
        }

        if (empty($theme) || strlen($theme) < 5) {
            $erreurs[] = 'Le thème doit contenir au moins 5 caractères';
        }

        if (!in_array($typeDiplome, [DIPLOME_LICENCE, DIPLOME_MASTER])) {
            $erreurs[] = 'Type de diplôme invalide';
        }

        if (!empty($typeDiplome) && !(bool)$profilEtudiant['est_diplome_permanent']) {
            $niveauEtude = $profilEtudiant['niveau_etude'];

            if ($niveauEtude === 'L3' && $typeDiplome !== DIPLOME_LICENCE) {
                $erreurs[] = 'Un etudiant L3 ne peut deposer qu\'un memoire de Licence';
            }

            if ($niveauEtude === 'M2' && $typeDiplome !== DIPLOME_MASTER) {
                $erreurs[] = 'Un etudiant M2 ne peut deposer qu\'un memoire de Master';
            }
        }

        $anneeActuelle = (int)date('Y');
        if ($anneeSoutenance < 2000 || $anneeSoutenance > $anneeActuelle + 1) {
            $erreurs[] = 'Année de soutenance invalide';
        }

        // Vérifier si l'étudiant a déjà un mémoire de ce type
        if (!empty($typeDiplome) && $this->memoireDAO->existeMemoireParTypeDiplome($_SESSION['user_id'], $typeDiplome)) {
            $erreurs[] = 'Vous avez déjà un mémoire pour ce type de diplôme. Un étudiant ne peut déposer qu\'un seul mémoire par type.';
        }

        // Vérifier le fichier PDF
        if (!isset($_FILES['fichier_pdf']) || $_FILES['fichier_pdf']['error'] !== UPLOAD_ERR_OK) {
            $erreurs[] = 'Erreur lors du téléchargement du fichier PDF';
        } else {
            $file = $_FILES['fichier_pdf'];
            
            // Vérifier l'extension
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if ($ext !== 'pdf') {
                $erreurs[] = 'Seuls les fichiers PDF sont acceptés';
            }

            // Vérifier la taille
            if ($file['size'] > MAX_PDF_SIZE) {
                $erreurs[] = 'Le fichier dépasse la taille maximale de ' . (MAX_PDF_SIZE / (1024 * 1024)) . ' Mo';
            }

            // Vérifier le type MIME
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if ($mime !== 'application/pdf') {
                $erreurs[] = 'Le fichier n\'est pas un PDF valide';
            }
        }

        // S'il y a des erreurs, retourner à la vue
        if (!empty($erreurs)) {
            $_SESSION['upload_errors'] = $erreurs;
            $_SESSION['form_data'] = [
                'titre' => $titre,
                'theme' => $theme,
                'type_diplome' => $typeDiplome,
                'annee_soutenance' => $anneeSoutenance
            ];
            header('Location: /views/etudiant/deposer_memoire.php');
            exit;
        }

        // Créer le répertoire s'il n'existe pas
        if (!is_dir(PDF_STORAGE_PATH)) {
            mkdir(PDF_STORAGE_PATH, 0755, true);
        }

        // Générer un nom de fichier unique
        $nomFichier = 'memoire_' . $_SESSION['user_id'] . '_' . time() . '.pdf';
        $cheminFichier = PDF_STORAGE_PATH . $nomFichier;

        // Déplacer le fichier uploadé
        if (!move_uploaded_file($_FILES['fichier_pdf']['tmp_name'], $cheminFichier)) {
            $_SESSION['upload_errors'] = ['Erreur lors de la sauvegarde du fichier'];
            header('Location: /views/etudiant/deposer_memoire.php');
            exit;
        }

        // Créer l'objet Memoire
        $memoire = new Memoire();
        $memoire->setEtudiantId($_SESSION['user_id']);
        $memoire->setTitre($titre);
        $memoire->setTheme($theme);
        $memoire->setFichierPdf($nomFichier);
        $memoire->setStatut(STATUT_EN_ATTENTE);
        $memoire->setTypeDiplome($typeDiplome);
        $memoire->setAnneeSoutenance($anneeSoutenance);
        $memoire->setRemarques('');

        // Insérer en base de données
        try {
            if ($this->memoireDAO->ajouterMemoire($memoire)) {
                // Nettoyer les données de session
                unset($_SESSION['upload_errors']);
                unset($_SESSION['form_data']);
                
                $_SESSION['success_message'] = 'Votre memoire a ete depose avec succes. En attente de verification.';
                header('Location: /views/etudiant/dashboard.php');
                exit;
            } else {
                // Supprimer le fichier uploadé en cas d'erreur
                unlink($cheminFichier);
                $_SESSION['upload_errors'] = ['Erreur lors de l\'enregistrement du mémoire'];
                header('Location: /views/etudiant/deposer_memoire.php');
                exit;
            }
        } catch (Exception $e) {
            // Supprimer le fichier uploadé en cas d'erreur
            if (file_exists($cheminFichier)) {
                unlink($cheminFichier);
            }
            $_SESSION['upload_errors'] = ['Une erreur est survenue: ' . $e->getMessage()];
            header('Location: /views/etudiant/deposer_memoire.php');
            exit;
        }
    }

    public function corrigerMemoire(): void {
        requireRole(ROLE_ETUDIANT);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /views/etudiant/modifier_memoire.php');
            exit;
        }

        $memoireId = (int)($_POST['memoire_id'] ?? 0);
        $memoireExistant = $this->memoireDAO->trouverParIdEtEtudiant($memoireId, (int)$_SESSION['user_id']);

        if (!$memoireExistant || $memoireExistant['statut'] !== STATUT_REJETE) {
            $_SESSION['correction_errors'] = [
                'Seul un memoire rejete vous appartenant peut etre corrige.'
            ];
            header('Location: /views/etudiant/modifier_memoire.php');
            exit;
        }

        $titre = trim($_POST['titre'] ?? '');
        $theme = trim($_POST['theme'] ?? '');
        $anneeSoutenance = (int)($_POST['annee_soutenance'] ?? 0);
        $erreurs = [];

        if (empty($titre) || strlen($titre) < 5) {
            $erreurs[] = 'Le titre doit contenir au moins 5 caracteres';
        }

        if (empty($theme) || strlen($theme) < 5) {
            $erreurs[] = 'Le theme doit contenir au moins 5 caracteres';
        }

        $anneeActuelle = (int)date('Y');
        if ($anneeSoutenance < 2000 || $anneeSoutenance > $anneeActuelle + 1) {
            $erreurs[] = 'Annee de soutenance invalide';
        }

        if (!isset($_FILES['fichier_pdf']) || $_FILES['fichier_pdf']['error'] !== UPLOAD_ERR_OK) {
            $erreurs[] = 'Veuillez uploader la version corrigee du memoire en PDF';
        } else {
            $file = $_FILES['fichier_pdf'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if ($ext !== 'pdf') {
                $erreurs[] = 'Seuls les fichiers PDF sont acceptes';
            }

            if ($file['size'] > MAX_PDF_SIZE) {
                $erreurs[] = 'Le fichier depasse la taille maximale de ' . (MAX_PDF_SIZE / (1024 * 1024)) . ' Mo';
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if ($mime !== 'application/pdf') {
                $erreurs[] = 'Le fichier n\'est pas un PDF valide';
            }
        }

        if (!empty($erreurs)) {
            $_SESSION['correction_errors'] = $erreurs;
            $_SESSION['correction_form_data'] = [
                'memoire_id' => $memoireId,
                'titre' => $titre,
                'theme' => $theme,
                'annee_soutenance' => $anneeSoutenance
            ];
            header('Location: /views/etudiant/modifier_memoire.php?id=' . $memoireId);
            exit;
        }

        if (!is_dir(PDF_STORAGE_PATH)) {
            mkdir(PDF_STORAGE_PATH, 0755, true);
        }

        $nomFichier = 'memoire_' . $_SESSION['user_id'] . '_' . time() . '_corrige.pdf';
        $cheminFichier = PDF_STORAGE_PATH . $nomFichier;

        if (!move_uploaded_file($_FILES['fichier_pdf']['tmp_name'], $cheminFichier)) {
            $_SESSION['correction_errors'] = ['Erreur lors de la sauvegarde du fichier corrige'];
            header('Location: /views/etudiant/modifier_memoire.php?id=' . $memoireId);
            exit;
        }

        try {
            $corrige = $this->memoireDAO->mettreAJourVersionCorrigee(
                $memoireId,
                (int)$_SESSION['user_id'],
                $titre,
                $theme,
                $anneeSoutenance,
                $nomFichier
            );

            if (!$corrige) {
                if (file_exists($cheminFichier)) {
                    unlink($cheminFichier);
                }
                $_SESSION['correction_errors'] = ['Erreur lors de l\'enregistrement de la correction'];
                header('Location: /views/etudiant/modifier_memoire.php?id=' . $memoireId);
                exit;
            }

            $ancienFichier = PDF_STORAGE_PATH . $memoireExistant['fichier_pdf'];
            if (is_file($ancienFichier) && $ancienFichier !== $cheminFichier) {
                unlink($ancienFichier);
            }

            unset($_SESSION['correction_errors'], $_SESSION['correction_form_data']);
            $_SESSION['success_message'] = 'Votre version corrigee a ete envoyee. Le memoire repasse en attente de validation.';
            header('Location: /views/etudiant/dashboard.php');
            exit;
        } catch (Exception $e) {
            if (file_exists($cheminFichier)) {
                unlink($cheminFichier);
            }
            $_SESSION['correction_errors'] = ['Une erreur est survenue: ' . $e->getMessage()];
            header('Location: /views/etudiant/modifier_memoire.php?id=' . $memoireId);
            exit;
        }
    }
}

// Traiter la requête
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new EtudiantController();
    $action = $_POST['action'] ?? '';

    if ($action === 'ajouter_memoire') {
        $controller->ajouterMemoire();
    }

    if ($action === 'corriger_memoire') {
        $controller->corrigerMemoire();
    }
}
