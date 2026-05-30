<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

class EtudiantDAO
{
    private PDO $pdo;

    public function __construct()
    {
        $db = new Database();
        $this->pdo = $db->connect();
    }

    public function trouverProfilParUtilisateurId(int $utilisateurId): ?array
    {
        $sql = "
            SELECT
                u.id_utilisateur,
                u.nom,
                u.email,
                u.role,
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

        $profil = $stmt->fetch(PDO::FETCH_ASSOC);
        return $profil ?: null;
    }

    public function peutDeposer(int $utilisateurId): bool
    {
        $profil = $this->trouverProfilParUtilisateurId($utilisateurId);

        if (!$profil) {
            return false;
        }

        if ((bool)$profil['est_diplome_permanent']) {
            return true;
        }

        return in_array($profil['niveau_etude'], NIVEAUX_DEPOT, true);
    }
}
