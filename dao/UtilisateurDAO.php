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
            SELECT *
            FROM utilisateur
            ORDER BY id_utilisateur DESC
        ";

        $requete = $this->pdo->query($sql);

        return $requete->fetchAll(PDO::FETCH_ASSOC);
    }
}