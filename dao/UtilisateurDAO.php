<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Utilisateur.php';

class UtilisateurDAO
{
    private $pdo;

    public function __construct()
    {
        $database = new Database();

        $this->pdo = $database->connect();
    }

    /**
     * Trouver un utilisateur par email
     */
    public function trouverParEmail(string $email)
    {
        $sql = "
            SELECT *
            FROM utilisateur
            WHERE email = :email
            LIMIT 1
        ";

        $requete = $this->pdo->prepare($sql);

        $requete->execute([
            ':email' => $email
        ]);

        return $requete->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Créer un utilisateur
     */
    public function creerUtilisateur(Utilisateur $utilisateur)
    {
        $sql = "
            INSERT INTO utilisateur (
                nom,
                email,
                mot_de_passe,
                centre_id,
                role,
                est_actif,
                doit_changer_mdp
            )
            VALUES (
                :nom,
                :email,
                :mot_de_passe,
                :centre_id,
                :role,
                :est_actif,
                :doit_changer_mdp
            )
        ";

        $requete = $this->pdo->prepare($sql);

        return $requete->execute([

            ':nom' => $utilisateur->getNom(),

            ':email' => $utilisateur->getEmail(),

            ':mot_de_passe' => $utilisateur->getMotDePasse(),

            ':centre_id' => $utilisateur->getCentreId(),

            ':role' => $utilisateur->getRole(),

            ':est_actif' => $utilisateur->getEstActif(),

            ':doit_changer_mdp' => $utilisateur->getDoitChangerMdp()

        ]);
    }

    /**
     * Trouver utilisateur par ID
     */
    public function trouverParId($id)
    {
        $sql = "SELECT * FROM utilisateur WHERE id_utilisateur = :id";

        $requete = $this->pdo->prepare($sql);

        $requete->execute([
            ':id' => $id
        ]);

        return $requete->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Modifier utilisateur
     */
    public function modifierUtilisateur(Utilisateur $utilisateur)
    {
        $sql = "
            UPDATE utilisateur
            SET
                nom = :nom,
                email = :email,
                role = :role,
                centre_id = :centre_id
            WHERE id_utilisateur = :id
        ";

        $requete = $this->pdo->prepare($sql);

        return $requete->execute([

            ':nom' => $utilisateur->getNom(),

            ':email' => $utilisateur->getEmail(),

            ':role' => $utilisateur->getRole(),

            ':centre_id' => $utilisateur->getCentreId(),

            ':id' => $utilisateur->getId()

        ]);
    }

    /**
     * Désactiver utilisateur
     */
    public function desactiverUtilisateur($id)
    {
        $sql = "
            UPDATE utilisateur
            SET est_actif = 0
            WHERE id_utilisateur = :id
        ";

        $requete = $this->pdo->prepare($sql);

        return $requete->execute([
            ':id' => $id
        ]);
    }

    /**
     * Liste utilisateurs
     */
    public function getAllUtilisateurs()
    {
        $sql = "
            SELECT
                u.*,
                c.nom_centre
            FROM utilisateur u
            LEFT JOIN centre c ON c.id_centre = u.centre_id
            ORDER BY u.id_utilisateur DESC
        ";

        $requete = $this->pdo->query($sql);

        return $requete->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Liste les professeurs actifs disponibles comme encadreurs.
     *
     * @return array
     */
    public function listerProfesseurs(): array
    {
        $sql = "
            SELECT
                u.id_utilisateur,
                u.nom,
                u.email,
                u.centre_id,
                p.specialite,
                p.grade
            FROM utilisateur u
            INNER JOIN professeur p ON p.utilisateur_id = u.id_utilisateur
            WHERE u.role = 'professeur'
              AND u.est_actif = 1
            ORDER BY u.nom ASC
        ";

        $requete = $this->pdo->query($sql);
        return $requete->fetchAll(PDO::FETCH_ASSOC);
    }
     public function changerMotDePasse(int $id, string $nouveauHash): bool
    {
        $sql = "
            UPDATE utilisateur
            SET mot_de_passe     = :hash,
                doit_changer_mdp = 0
            WHERE id_utilisateur = :id
        ";
 
        $stmt = $this->pdo->prepare($sql);
 
        return $stmt->execute([
            ':hash' => $nouveauHash,
            ':id'   => $id,
        ]);
    }
    

    /**
     * Retourne l'id du dernier utilisateur inséré
     * Utilisé juste après creerUtilisateur() pour insérer
     * la ligne correspondante dans la table etudiant
     *
     * @return int|null
     */
    public function getDernierId(): ?int
    {
        $id = $this->pdo->lastInsertId();
        return $id ? (int) $id : null;
    }

    /**
     * Insère une ligne dans la table etudiant
     * Appelé après creerUtilisateur() quand le rôle = etudiant
     *
     * @param int    $utilisateurId
     * @param string $numeroEtudiant
     * @param string $niveauEtude    L1 | L2 | L3 | M1 | M2
     * @param int    $filiereId
     * @return bool
     */
    public function creerEtudiant(
        int    $utilisateurId,
        string $numeroEtudiant,
        string $niveauEtude,
        int    $filiereId
    ): bool {
        $sql = "
            INSERT IGNORE INTO etudiant
                (utilisateur_id, numero_etudiant, niveau_etude, filiere_id)
            VALUES
                (:utilisateur_id, :numero_etudiant, :niveau_etude, :filiere_id)
        ";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':utilisateur_id'  => $utilisateurId,
            ':numero_etudiant' => $numeroEtudiant,
            ':niveau_etude'    => $niveauEtude,
            ':filiere_id'      => $filiereId,
        ]);
    }

    public function creerProfesseur(
        int $utilisateurId,
        string $specialite = '',
        string $grade = ''
    ): bool {
        $sql = "
            INSERT IGNORE INTO professeur (utilisateur_id, specialite, grade)
            VALUES (:utilisateur_id, :specialite, :grade)
        ";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':utilisateur_id' => $utilisateurId,
            ':specialite'     => $specialite,
            ':grade'          => $grade,
        ]);
    }

    public function creerDirecteur(
        int $utilisateurId,
        string $responsabilite = ''
    ): bool {
        $sql = "
            INSERT IGNORE INTO directeur_etudes (utilisateur_id, responsabilite)
            VALUES (:utilisateur_id, :responsabilite)
        ";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':utilisateur_id' => $utilisateurId,
            ':responsabilite' => $responsabilite,
        ]);
    }

    public function creerTechnicien(
        int $utilisateurId,
        string $service = ''
    ): bool {
        $sql = "
            INSERT IGNORE INTO technicien (utilisateur_id, service)
            VALUES (:utilisateur_id, :service)
        ";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':utilisateur_id' => $utilisateurId,
            ':service'        => $service,
        ]);
    }
}
