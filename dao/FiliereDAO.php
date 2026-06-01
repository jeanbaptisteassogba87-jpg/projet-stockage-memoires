<?php

// Rôle : toutes les requêtes BDD sur la table filiere
//        - lister les filières d'un centre
//        - trouver une filière par id
//        - créer / modifier une filière


require_once __DIR__ . '/../config/database.php';

class FiliereDAO
{
    private PDO $pdo;

    public function __construct()
    {
        $db        = new Database();
        $this->pdo = $db->connect();
    }

    /**
     * Retourne toutes les filières d'un centre donné
     * Utilisé dans les formulaires de création d'étudiant
     *
     * @param int $centreId
     * @return array
     */
    public function listerParCentre(int $centreId): array
    {
        $sql = "
            SELECT
                id_filiere,
                nom_filiere,
                centre_id
            FROM filiere
            WHERE centre_id = :centre_id
            ORDER BY nom_filiere ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':centre_id' => $centreId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retourne toutes les filières, toutes centres confondus
     * Utilisé dans les interfaces d'administration
     *
     * @return array
     */
    public function listerToutes(): array
    {
        $sql = "
            SELECT
                f.id_filiere,
                f.nom_filiere,
                f.centre_id,
                c.nom_centre
            FROM filiere f
            INNER JOIN centre c ON c.id_centre = f.centre_id
            ORDER BY c.nom_centre ASC, f.nom_filiere ASC
        ";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Trouve une filière par son id
     *
     * @param int $id
     * @return array|null
     */
    public function trouverParId(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM filiere WHERE id_filiere = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Crée une nouvelle filière dans un centre
     *
     * @param string $nomFiliere
     * @param int    $centreId
     * @return bool
     */
    public function creer(string $nomFiliere, int $centreId): bool
    {
        $sql = "
            INSERT INTO filiere (nom_filiere, centre_id)
            VALUES (:nom, :centre_id)
        ";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':nom'       => $nomFiliere,
            ':centre_id' => $centreId,
        ]);
    }

    /**
     * Modifie le nom d'une filière
     *
     * @param int    $id
     * @param string $nomFiliere
     * @return bool
     */
    public function modifier(int $id, string $nomFiliere): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE filiere SET nom_filiere = :nom WHERE id_filiere = :id"
        );
        return $stmt->execute([
            ':nom' => $nomFiliere,
            ':id'  => $id,
        ]);
    }

    /**
     * Vérifie si une filière est utilisée par des étudiants
     * Avant une éventuelle suppression
     *
     * @param int $id
     * @return bool
     */
    public function estUtilisee(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM etudiant WHERE filiere_id = :id"
        );
        $stmt->execute([':id' => $id]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Retourne l'id de la dernière filière insérée
     *
     * @return int|null
     */
    public function getDernierId(): ?int
    {
        $id = $this->pdo->lastInsertId();
        return $id ? (int) $id : null;
    }
}