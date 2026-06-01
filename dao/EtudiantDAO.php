<?php

// Rôle : requêtes BDD sur la table etudiant (jointure utilisateur)
// Utilisé par EtudiantController pour vérifier le niveau et par d'autres controllers qui ont besoin des infos étudiant

require_once __DIR__ . '/../config/database.php';

class EtudiantDAO
{
    private PDO $pdo;

    public function __construct()
    {
        $db        = new Database();
        $this->pdo = $db->connect();
    }

    /**
     * Retourne les infos complètes d'un étudiant
     * Jointure entre utilisateur et etudiant pour avoir
     * le niveau_etude, filiere_id, etc. en une seule requête
     *
     * @param int $utilisateurId  id_utilisateur (= clé dans la table etudiant)
     * @return array|null         tableau associatif ou null si introuvable
     */
    public function trouverParId(int $utilisateurId): ?array
    {
        $sql = "
            SELECT
                u.id_utilisateur,
                u.nom,
                u.email,
                u.centre_id,
                u.est_actif,
                e.numero_etudiant,
                e.niveau_etude,
                e.filiere_id,
                e.est_diplome_permanent
            FROM utilisateur u
            INNER JOIN etudiant e ON e.utilisateur_id = u.id_utilisateur
            WHERE u.id_utilisateur = :id
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $utilisateurId]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Retourne tous les étudiants d'un centre donné
     * Utilisé par le technicien pour l'import et la gestion
     *
     * @param int $centreId
     * @return array
     */
    public function listerParCentre(int $centreId): array
    {
        $sql = "
            SELECT
                u.id_utilisateur,
                u.nom,
                u.email,
                e.numero_etudiant,
                e.niveau_etude,
                e.filiere_id
            FROM utilisateur u
            INNER JOIN etudiant e ON e.utilisateur_id = u.id_utilisateur
            WHERE u.centre_id = :centre_id
              AND u.est_actif  = 1
            ORDER BY u.nom ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':centre_id' => $centreId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}