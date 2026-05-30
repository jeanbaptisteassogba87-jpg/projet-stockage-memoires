<?php

require_once __DIR__ . '/../dao/CommentaireDAO.php';
require_once __DIR__ . '/../dao/MemoireDAO.php';
require_once __DIR__ . '/../models/Commentaire.php';
require_once __DIR__ . '/../config/constants.php';


class CommentateurController
{
    private PDO            $pdo;
    private CommentaireDAO $commentaireDAO;
    private MemoireDAO     $memoireDAO;

    public function __construct(PDO $pdo)
    {
        $this->pdo            = $pdo;
        $this->commentaireDAO = new CommentaireDAO($pdo);
        $this->memoireDAO     = new MemoireDAO($pdo);
    }

    // DISPATCHER 

    public function dispatcher(string $action): void
    {
        $this->verifierConnexion();   

        match ($action) {
            'dashboard'            => $this->dashboard(),
            'recherche'            => $this->recherche(),
            'consulter'            => $this->consulter(),
            'ajouterCommentaire'   => $this->ajouterCommentaire(),
            'modifierCommentaire'  => $this->modifierCommentaire(),
            'supprimerCommentaire' => $this->supprimerCommentaire(),
            'likerMemoire'         => $this->likerMemoire(),
            'unlikeMemoire'        => $this->unlikeMemoire(),
            'voirPdf'              => $this->voirPdf(),
            'suggestions'          => $this->suggestions(),
            default                => $this->dashboard(),
        };
    }

    // ── 1. DASHBOARD 

    public function dashboard(): void
    {
        // Derniers mémoires publics + stats rapides
        $derniersMemoires = $this->memoireDAO->rechercher();
        $derniersMemoires = array_slice($derniersMemoires, 0, 6);

        require_once __DIR__ . '/../views/commentateur/dashboard.php';
    }

    // ── 2. RECHERCHER MÉMOIRE 

    public function recherche(): void
    {
        $motCle  = isset($_GET['mot_cle']) ? trim(htmlspecialchars($_GET['mot_cle'])) : '';
        $filiere = isset($_GET['filiere']) ? trim(htmlspecialchars($_GET['filiere'])) : '';
        $annee   = isset($_GET['annee'])   ? (int) $_GET['annee']                    : 0;
        $niveau  = isset($_GET['niveau'])  ? trim(htmlspecialchars($_GET['niveau']))  : '';

        $memoires       = [];
        $totalResultats = 0;

        if ($motCle || $filiere || $annee || $niveau) {
            $memoires       = $this->memoireDAO->rechercher($motCle, $filiere, $annee, $niveau);
            $totalResultats = count($memoires);
        }

        $filieres = $this->memoireDAO->getFilieres();
        $annees   = $this->memoireDAO->getAnnees();
        $niveaux  = $this->memoireDAO->getNiveaux();

        require_once __DIR__ . '/../views/commentateur/recherche.php';
    }

    // ── 3. CONSULTER MÉMOIRE 

    public function consulter(): void
    {
        $memoireId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        if ($memoireId <= 0) {
            $this->rediriger('recherche', "Identifiant de mémoire invalide.");
            return;
        }

        $memoire = $this->memoireDAO->getParId($memoireId);

        if (!$memoire) {
            $this->rediriger('recherche', "Ce mémoire n'existe pas.");
            return;
        }

        // Seuls les mémoires rendus publics sont lisibles 
        if ($memoire['statut'] !== 'public') {
            $this->rediriger('recherche', "Ce mémoire n'est pas encore disponible.");
            return;
        }

        // Commentaires 
        $commentaires   = $this->commentaireDAO->getParMemoire($memoireId);
        $nbCommentaires = count($commentaires);

        // Likes 
        $nbLikes         = $this->memoireDAO->compterLikes($memoireId);
        $utilisateurALike = $this->memoireDAO->utilisateurALike(
            $memoireId,
            $_SESSION['user_id']        
        );

        $messageFlash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        require_once __DIR__ . '/../views/commentateur/consulter.php';
    }

    // ── 4. COMMENTER 

    public function ajouterCommentaire(): void
    {
        $this->verifierPost();

        $memoireId = isset($_POST['memoire_id']) ? (int) $_POST['memoire_id'] : 0;
        $contenu   = isset($_POST['contenu'])    ? trim($_POST['contenu'])    : '';

        $commentaire = new Commentaire(
            $memoireId,
            $_SESSION['user_id'],       
            $contenu
        );

        $erreurs = $commentaire->valider();

        if (!empty($erreurs)) {
            $_SESSION['flash'] = ['type' => 'erreur', 'message' => implode(' ', $erreurs)];
            $this->versConsulter($memoireId);
            return;
        }

        $resultat = $this->commentaireDAO->inserer($commentaire);

        $_SESSION['flash'] = ($resultat !== false)
            ? ['type' => 'succes', 'message' => "Commentaire publié avec succès."]
            : ['type' => 'erreur', 'message' => "Erreur lors de la publication."];

        $this->versConsulter($memoireId);
    }

    public function modifierCommentaire(): void
    {
        $this->verifierPost();

        $id        = isset($_POST['commentaire_id']) ? (int) $_POST['commentaire_id'] : 0;
        $memoireId = isset($_POST['memoire_id'])     ? (int) $_POST['memoire_id']     : 0;
        $contenu   = isset($_POST['contenu'])        ? trim($_POST['contenu'])        : '';

        if ($id <= 0 || empty($contenu)) {
            $_SESSION['flash'] = ['type' => 'erreur', 'message' => "Données invalides."];
            $this->versConsulter($memoireId);
            return;
        }

        $ok = $this->commentaireDAO->modifier(
            $id,
            $_SESSION['user_id'],      
            $contenu
        );

        $_SESSION['flash'] = $ok
            ? ['type' => 'succes', 'message' => "Commentaire modifié."]
            : ['type' => 'erreur', 'message' => "Impossible de modifier ce commentaire."];

        $this->versConsulter($memoireId);
    }

    public function supprimerCommentaire(): void
    {
        $this->verifierPost();

        $id        = isset($_POST['commentaire_id']) ? (int) $_POST['commentaire_id'] : 0;
        $memoireId = isset($_POST['memoire_id'])     ? (int) $_POST['memoire_id']     : 0;

        $ok = $this->commentaireDAO->supprimer(
            $id,
            $_SESSION['user_id']        
        );

        $_SESSION['flash'] = $ok
            ? ['type' => 'succes', 'message' => "Commentaire supprimé."]
            : ['type' => 'erreur', 'message' => "Impossible de supprimer."];

        $this->versConsulter($memoireId);
    }

    // ── 5. LIKER 

    public function likerMemoire(): void
    {
        $this->verifierPost();

        $memoireId = isset($_POST['memoire_id']) ? (int) $_POST['memoire_id'] : 0;

        $this->memoireDAO->ajouterLike($memoireId, $_SESSION['user_id']);
        $this->versConsulter($memoireId);
    }

    public function unlikeMemoire(): void
    {
        $this->verifierPost();

        $memoireId = isset($_POST['memoire_id']) ? (int) $_POST['memoire_id'] : 0;

        $this->memoireDAO->retirerLike($memoireId, $_SESSION['user_id']);
        $this->versConsulter($memoireId);
    }

    // ── 6. VOIR PDF (sécurisé)

    public function voirPdf(): void
    {
        $memoireId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        if ($memoireId <= 0) {
            http_response_code(404); exit("Fichier introuvable.");
        }

        $memoire = $this->memoireDAO->getParId($memoireId);

        if (!$memoire || $memoire['statut'] !== 'public') {
            http_response_code(403); exit("Accès interdit.");
        }

        // basename() pour éviter toute traversée de répertoire
        $chemin = __DIR__ . '/../uploads/memoires/' . basename($memoire['fichier_pdf']);

        if (!file_exists($chemin)) {
            http_response_code(404); exit("Fichier PDF introuvable.");
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="memoire.pdf"');
        header('Content-Length: ' . filesize($chemin));
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('X-Content-Type-Options: nosniff');
        readfile($chemin);
        exit();
    }

    // ── 7. SUGGESTIONS AJAX 

    public function suggestions(): void
    {
        if (
            empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'
        ) {
            http_response_code(403); exit();
        }

        $q = isset($_GET['q']) ? trim($_GET['q']) : '';

        header('Content-Type: application/json');

        if (mb_strlen($q) < 2) {
            echo json_encode([]); exit();
        }

        $titres = $this->memoireDAO->getSuggestions($q);
        echo json_encode($titres);
        exit();
    }

    // ── UTILITAIRES 

    private function verifierConnexion(): void
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: /views/auth/login.php');   
            exit();
        }
    }

    // Bloque les requêtes qui ne sont pas en POST.
    private function verifierPost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); exit("Méthode non autorisée.");
        }
    }

    // Redirige avec un message flash d'erreur.
    private function rediriger(string $action, string $message = ''): void
    {
        if ($message) {
            $_SESSION['flash'] = ['type' => 'erreur', 'message' => $message];
        }
        header("Location: /views/commentateur/dashboard.php?action=$action");
        exit();
    }

    // Redirige vers la consultation d'un mémoire.
    private function versConsulter(int $memoireId): void
    {
        header("Location: /views/commentateur/dashboard.php?action=consulter&id=$memoireId");
        exit();
    }
}