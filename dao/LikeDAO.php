<?php

// Rôle : toutes les requêtes BDD sur la table like_memoire
//        - ajouter un like
//        - retirer un like
//        - vérifier si l'utilisateur a déjà liké
//        - compter les likes d'un mémoire


require_once __DIR__ . '/../config/database.php';

class LikeDAO
{
    private PDO $pdo;

    public function __construct()
    {
        $db        = new Database();
        $this->pdo = $db->connect();
    }

    /**
     * Ajoute un like sur un mémoire
     * La contrainte UNIQUE(utilisateur_id, memoire_id) en BDD
     * empêche le double like — on ignore l'erreur si elle survient
     *
     * @param int $memoireId
     * @param int $utilisateurId
     * @return bool
     */
    public function ajouter(int $memoireId, int $utilisateurId): bool
    {
        $sql = "
            INSERT IGNORE INTO like_memoire (memoire_id, utilisateur_id)
            VALUES (:memoire_id, :utilisateur_id)
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':memoire_id'     => $memoireId,
            ':utilisateur_id' => $utilisateurId,
        ]);

        // rowCount = 1 si inséré, 0 si déjà existant (INSERT IGNORE)
        return $stmt->rowCount() > 0;
    }

    /**
     * Retire un like
     *
     * @param int $memoireId
     * @param int $utilisateurId
     * @return bool
     */
    public function retirer(int $memoireId, int $utilisateurId): bool
    {
        $sql = "
            DELETE FROM like_memoire
            WHERE memoire_id     = :memoire_id
              AND utilisateur_id = :utilisateur_id
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':memoire_id'     => $memoireId,
            ':utilisateur_id' => $utilisateurId,
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Vérifie si l'utilisateur a déjà liké ce mémoire
     * Utilisé pour afficher le bon état du bouton like
     *
     * @param int $memoireId
     * @param int $utilisateurId
     * @return bool
     */
    public function aDejaLike(int $memoireId, int $utilisateurId): bool
    {
        $sql = "
            SELECT COUNT(*)
            FROM like_memoire
            WHERE memoire_id     = :memoire_id
              AND utilisateur_id = :utilisateur_id
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':memoire_id'     => $memoireId,
            ':utilisateur_id' => $utilisateurId,
        ]);

        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Compte le nombre de likes d'un mémoire
     *
     * @param int $memoireId
     * @return int
     */
    public function compter(int $memoireId): int
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM like_memoire WHERE memoire_id = :id"
        );
        $stmt->execute([':id' => $memoireId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Retourne les ids des mémoires likés par un utilisateur
     * Utilisé pour marquer les mémoires dans les résultats de recherche
     *
     * @param int   $utilisateurId
     * @param array $memoireIds    liste des ids à vérifier
     * @return array               liste des ids likés
     */
    public function getLikesUtilisateur(int $utilisateurId, array $memoireIds): array
    {
        if (empty($memoireIds)) return [];

        // Construire les placeholders (:id0, :id1, ...)
        $placeholders = [];
        $params       = [':user_id' => $utilisateurId];

        foreach ($memoireIds as $i => $id) {
            $key             = ':mid' . $i;
            $placeholders[]  = $key;
            $params[$key]    = $id;
        }

        $sql = "
            SELECT memoire_id
            FROM like_memoire
            WHERE utilisateur_id = :user_id
              AND memoire_id IN (" . implode(',', $placeholders) . ")
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}