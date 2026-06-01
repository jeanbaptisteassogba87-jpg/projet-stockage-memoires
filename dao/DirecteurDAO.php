<?php

// Rôle : toutes les requêtes BDD liées au directeur des études
//        - lister les mémoires validés (à publier ou non)
//        - changer la visibilité (publie / non_public)
//        - stats globales pour le dashboard


require_once __DIR__ . '/../config/database.php';

class DirecteurDAO
{
    private PDO $pdo;

    public function __construct()
    {
        $db        = new Database();
        $this->pdo = $db->connect();
    }

    /**
     * Liste tous les mémoires validés ou déjà publiés/non_public
     * du centre du directeur — ce sont ceux qu'il peut gérer
     *
     * @param int $centreId
     * @return array
     */
    public function listerMemoresGerables(int $centreId): array
    {
        $sql = "
            SELECT
                m.id_memoire,
                m.titre,
                m.theme,
                m.type_diplome,
                m.annee_soutenance,
                m.date_depot,
                m.statut,
                u.nom       AS nom_etudiant,
                p.nom       AS nom_professeur,
                e.niveau_etude,
                f.nom_filiere
            FROM memoire m
            INNER JOIN utilisateur  u  ON u.id_utilisateur  = m.etudiant_id
            INNER JOIN etudiant     e  ON e.utilisateur_id  = m.etudiant_id
            LEFT  JOIN utilisateur  p  ON p.id_utilisateur  = m.professeur_id
            LEFT  JOIN filiere      f  ON f.id_filiere      = e.filiere_id
            WHERE m.statut IN ('valide', 'publie', 'non_public')
              AND u.centre_id = :centre_id
            ORDER BY m.date_depot DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':centre_id' => $centreId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Publie un mémoire : statut → publie
     * Uniquement si statut actuel est valide ou non_public
     *
     * @param int $idMemoire
     * @param int $centreId  sécurité : vérifier que le mémoire appartient au centre
     * @return bool
     */
    public function publier(int $idMemoire, int $centreId): bool
    {
        $sql = "
            UPDATE memoire m
            INNER JOIN utilisateur u ON u.id_utilisateur = m.etudiant_id
            SET m.statut = 'publie'
            WHERE m.id_memoire  = :id
              AND m.statut      IN ('valide', 'non_public')
              AND u.centre_id   = :centre_id
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id'        => $idMemoire,
            ':centre_id' => $centreId,
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Dépublie un mémoire : statut → non_public
     * Uniquement si statut actuel est publie
     *
     * @param int $idMemoire
     * @param int $centreId
     * @return bool
     */
    public function depublier(int $idMemoire, int $centreId): bool
    {
        $sql = "
            UPDATE memoire m
            INNER JOIN utilisateur u ON u.id_utilisateur = m.etudiant_id
            SET m.statut = 'non_public'
            WHERE m.id_memoire = :id
              AND m.statut     = 'publie'
              AND u.centre_id  = :centre_id
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id'        => $idMemoire,
            ':centre_id' => $centreId,
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Stats globales pour le dashboard directeur
     * Retourne le nombre de mémoires par statut dans son centre
     *
     * @param int $centreId
     * @return array  ex: ['en_attente' => 5, 'valide' => 12, 'publie' => 8, ...]
     */
    public function statsParStatut(int $centreId): array
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
     * Compte le total des étudiants actifs du centre
     * Affiché dans les stats du dashboard
     *
     * @param int $centreId
     * @return int
     */
    public function compterEtudiants(int $centreId): int
    {
        $sql = "
            SELECT COUNT(*)
            FROM utilisateur
            WHERE centre_id = :centre_id
              AND role      = 'etudiant'
              AND est_actif = 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':centre_id' => $centreId]);
        return (int) $stmt->fetchColumn();
    }
}