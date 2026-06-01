<?php

// Rôle : toutes les requêtes BDD sur la table centre
//        - lister tous les centres
//        - trouver un centre par id
//        - créer / modifier un centre
//        - statistiques par centre


require_once __DIR__ . '/../config/database.php';

class CentreDAO
{
    private PDO $pdo;

    public function __construct()
    {
        $db        = new Database();
        $this->pdo = $db->connect();
    }

    /**
     * Retourne tous les centres, ordonnés par nom
     * Le centre principal apparaît en premier
     *
     * @return array
     */
    public function listerTous(): array
    {
        $stmt = $this->pdo->query("
            SELECT
                id_centre,
                nom_centre,
                adresse,
                telephone,
                est_centre_principal,
                created_at
            FROM centre
            ORDER BY est_centre_principal DESC, nom_centre ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Trouve un centre par son id
     *
     * @param int $id
     * @return array|null
     */
    public function trouverParId(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM centre WHERE id_centre = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Crée un nouveau centre
     *
     * @param string $nom
     * @param string $adresse
     * @param string $telephone
     * @param bool   $estPrincipal
     * @return bool
     */
    public function creer(
        string $nom,
        string $adresse    = '',
        string $telephone  = '',
        bool   $estPrincipal = false
    ): bool {
        $sql = "
            INSERT INTO centre (nom_centre, adresse, telephone, est_centre_principal)
            VALUES (:nom, :adresse, :telephone, :principal)
        ";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':nom'       => $nom,
            ':adresse'   => $adresse,
            ':telephone' => $telephone,
            ':principal' => $estPrincipal ? 1 : 0,
        ]);
    }

    /**
     * Met à jour les informations d'un centre
     *
     * @param int    $id
     * @param string $nom
     * @param string $adresse
     * @param string $telephone
     * @return bool
     */
    public function modifier(
        int    $id,
        string $nom,
        string $adresse   = '',
        string $telephone = ''
    ): bool {
        $sql = "
            UPDATE centre
            SET nom_centre = :nom,
                adresse    = :adresse,
                telephone  = :telephone
            WHERE id_centre = :id
        ";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':id'       => $id,
            ':nom'      => $nom,
            ':adresse'  => $adresse,
            ':telephone'=> $telephone,
        ]);
    }

    /**
     * Retourne le nombre d'utilisateurs actifs par rôle pour un centre
     * Utilisé pour afficher des stats dans le dashboard technicien
     *
     * @param int $centreId
     * @return array  ex: ['etudiant' => 45, 'professeur' => 8, ...]
     */
    public function compterUtilisateursParRole(int $centreId): array
    {
        $sql = "
            SELECT role, COUNT(*) AS total
            FROM utilisateur
            WHERE centre_id = :centre_id
              AND est_actif  = 1
            GROUP BY role
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':centre_id' => $centreId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $row) {
            $result[$row['role']] = (int) $row['total'];
        }
        return $result;
    }

    /**
     * Retourne le nombre de mémoires par statut pour un centre
     *
     * @param int $centreId
     * @return array  ex: ['publie' => 12, 'en_attente' => 3, ...]
     */
    public function compterMemoiresParStatut(int $centreId): array
    {
        $sql = "
            SELECT m.statut, COUNT(*) AS total
            FROM memoire       m
            INNER JOIN utilisateur u ON u.id_utilisateur = m.etudiant_id
            WHERE u.centre_id = :centre_id
            GROUP BY m.statut
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':centre_id' => $centreId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $row) {
            $result[$row['statut']] = (int) $row['total'];
        }
        return $result;
    }

    /**
     * Retourne l'id du dernier centre inséré
     *
     * @return int|null
     */
    public function getDernierId(): ?int
    {
        $id = $this->pdo->lastInsertId();
        return $id ? (int) $id : null;
    }
}