<?php

// Rôle : toutes les requêtes BDD sur la table commentaire
//        - ajouter un commentaire
//        - lister les commentaires d'un mémoire
//        - supprimer un commentaire (son auteur seulement)


require_once __DIR__ . '/../config/database.php';

class CommentaireDAO
{
    private PDO $pdo;

    public function __construct()
    {
        $db        = new Database();
        $this->pdo = $db->connect();
    }

    /**
     * Ajoute un commentaire sur un mémoire
     *
     * @param int    $memoireId      id du mémoire commenté
     * @param int    $utilisateurId  id de l'auteur du commentaire
     * @param string $contenu        texte du commentaire
     * @return bool
     */
    public function ajouter(int $memoireId, int $utilisateurId, string $contenu): bool
    {
        $sql = "
            INSERT INTO commentaire (memoire_id, utilisateur_id, contenu)
            VALUES (:memoire_id, :utilisateur_id, :contenu)
        ";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':memoire_id'     => $memoireId,
            ':utilisateur_id' => $utilisateurId,
            ':contenu'        => $contenu,
        ]);
    }

    /**
     * Liste tous les commentaires d'un mémoire
     * avec le nom de l'auteur et son rôle
     * Ordonnés du plus récent au plus ancien
     *
     * @param int $memoireId
     * @return array
     */
    public function listerParMemoire(int $memoireId): array
    {
        $sql = "
            SELECT
                c.id_commentaire,
                c.contenu,
                c.date_creation,
                c.utilisateur_id,
                u.nom  AS nom_auteur,
                u.role AS role_auteur
            FROM commentaire  c
            INNER JOIN utilisateur u ON u.id_utilisateur = c.utilisateur_id
            WHERE c.memoire_id = :memoire_id
            ORDER BY c.date_creation DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':memoire_id' => $memoireId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Supprime un commentaire
     * La condition utilisateur_id = :user_id empêche
     * de supprimer le commentaire d'un autre utilisateur
     *
     * @param int $idCommentaire
     * @param int $utilisateurId  id de l'utilisateur connecté
     * @return bool
     */
    public function supprimer(int $idCommentaire, int $utilisateurId): bool
    {
        $sql = "
            DELETE FROM commentaire
            WHERE id_commentaire = :id
              AND utilisateur_id = :user_id
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id'      => $idCommentaire,
            ':user_id' => $utilisateurId,
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Compte les commentaires d'un mémoire
     * Affiché dans les cartes de résultats de recherche
     *
     * @param int $memoireId
     * @return int
     */
    public function compterParMemoire(int $memoireId): int
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM commentaire WHERE memoire_id = :id"
        );
        $stmt->execute([':id' => $memoireId]);
        return (int) $stmt->fetchColumn();
    }
}