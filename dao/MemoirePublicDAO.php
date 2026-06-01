<?php

// Rôle : requêtes de recherche et de consultation des mémoires publiés — accessibles à tous les connectés
// Séparé de MemoireDAO pour garder les responsabilités claires   

require_once __DIR__ . '/../config/database.php';

class MemoirePublicDAO
{
    private PDO $pdo;

    public function __construct()
    {
        $db        = new Database();
        $this->pdo = $db->connect();
    }

    /**
     * Recherche les mémoires publiés selon des critères
     * Tous les paramètres sont optionnels
     *
     * @param string $motsCles   recherche dans titre + theme
     * @param string $type       'licence' | 'master' | ''
     * @param string $annee      année de soutenance | ''
     * @param string $filiere    nom de la filière | ''
     * @param int    $centreId   0 = tous les centres
     * @return array
     */
    public function rechercher(
        string $motsCles = '',
        string $type     = '',
        string $annee    = '',
        string $filiere  = '',
        int    $centreId = 0
    ): array {
        $conditions = ["m.statut = 'publie'"];
        $params     = [];

        // Recherche plein texte dans titre et thème
        if (!empty($motsCles)) {
            $conditions[] = "(m.titre LIKE :mots OR m.theme LIKE :mots2)";
            $params[':mots']  = '%' . $motsCles . '%';
            $params[':mots2'] = '%' . $motsCles . '%';
        }

        if (!empty($type)) {
            $conditions[] = "m.type_diplome = :type";
            $params[':type'] = $type;
        }

        if (!empty($annee)) {
            $conditions[] = "m.annee_soutenance = :annee";
            $params[':annee'] = (int) $annee;
        }

        if (!empty($filiere)) {
            $conditions[] = "f.nom_filiere LIKE :filiere";
            $params[':filiere'] = '%' . $filiere . '%';
        }

        if ($centreId > 0) {
            $conditions[] = "u.centre_id = :centre_id";
            $params[':centre_id'] = $centreId;
        }

        $where = implode(' AND ', $conditions);

        $sql = "
            SELECT
                m.id_memoire,
                m.titre,
                m.theme,
                m.type_diplome,
                m.annee_soutenance,
                m.date_depot,
                u.nom           AS nom_etudiant,
                f.nom_filiere,
                c.nom_centre,
                COUNT(DISTINCT lk.id_like)        AS nb_likes,
                COUNT(DISTINCT cm.id_commentaire) AS nb_commentaires
            FROM memoire        m
            INNER JOIN utilisateur  u  ON u.id_utilisateur  = m.etudiant_id
            INNER JOIN etudiant     e  ON e.utilisateur_id  = m.etudiant_id
            LEFT  JOIN filiere      f  ON f.id_filiere      = e.filiere_id
            LEFT  JOIN centre       c  ON c.id_centre       = u.centre_id
            LEFT  JOIN like_memoire lk ON lk.memoire_id     = m.id_memoire
            LEFT  JOIN commentaire  cm ON cm.memoire_id     = m.id_memoire
            WHERE $where
            GROUP BY m.id_memoire
            ORDER BY m.annee_soutenance DESC, m.date_depot DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Charge un mémoire publié avec toutes ses infos
     * pour la page de consultation détaillée
     *
     * @param int $id
     * @return array|null
     */
    public function trouverPublie(int $id): ?array
    {
        $sql = "
            SELECT
                m.*,
                u.nom           AS nom_etudiant,
                u.email         AS email_etudiant,
                e.niveau_etude,
                e.numero_etudiant,
                f.nom_filiere,
                c.nom_centre,
                prof.nom        AS nom_professeur,
                COUNT(DISTINCT lk.id_like)        AS nb_likes,
                COUNT(DISTINCT cm.id_commentaire) AS nb_commentaires
            FROM memoire        m
            INNER JOIN utilisateur  u    ON u.id_utilisateur  = m.etudiant_id
            INNER JOIN etudiant     e    ON e.utilisateur_id  = m.etudiant_id
            LEFT  JOIN filiere      f    ON f.id_filiere      = e.filiere_id
            LEFT  JOIN centre       c    ON c.id_centre       = u.centre_id
            LEFT  JOIN utilisateur  prof ON prof.id_utilisateur = m.professeur_id
            LEFT  JOIN like_memoire lk   ON lk.memoire_id    = m.id_memoire
            LEFT  JOIN commentaire  cm   ON cm.memoire_id    = m.id_memoire
            WHERE m.id_memoire = :id
              AND m.statut     = 'publie'
            GROUP BY m.id_memoire
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Retourne les années de soutenance disponibles
     * pour remplir le filtre de recherche
     *
     * @return array  ex: [2024, 2023, 2022]
     */
    public function getAnneesDisponibles(): array
    {
        $stmt = $this->pdo->query("
            SELECT DISTINCT annee_soutenance
            FROM memoire
            WHERE statut = 'publie'
            ORDER BY annee_soutenance DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Retourne les filières qui ont au moins un mémoire publié
     * pour remplir le filtre de recherche
     *
     * @return array
     */
    public function getFilieresDisponibles(): array
    {
        $stmt = $this->pdo->query("
            SELECT DISTINCT f.nom_filiere
            FROM filiere      f
            INNER JOIN etudiant   e ON e.filiere_id      = f.id_filiere
            INNER JOIN memoire    m ON m.etudiant_id     = e.utilisateur_id
            WHERE m.statut = 'publie'
            ORDER BY f.nom_filiere ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}