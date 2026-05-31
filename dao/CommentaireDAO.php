<?php

require_once __DIR__ . '/../models/Commentaire.php';

class CommentaireDAO
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // ── CREATE
    
    public function inserer(Commentaire $commentaire): int|false
    {
        $erreurs = $commentaire->valider();
        if (!empty($erreurs)) {
            return false;
        }

        $sql = "INSERT INTO commentaires
                    (memoire_id, utilisateur_id, contenu, date_commentaire)
                VALUES
                    (:memoire_id, :utilisateur_id, :contenu, NOW())";

        $stmt = $this->pdo->prepare($sql);
        $ok   = $stmt->execute([
            ':memoire_id'     => $commentaire->getMemoireId(),
            ':utilisateur_id' => $commentaire->getUtilisateurId(),
            ':contenu'        => trim($commentaire->getContenu()),
        ]);

        return $ok ? (int) $this->pdo->lastInsertId() : false;
    }

    // ── READ 
    
    public function getParMemoire(int $memoireId): array
    {
        $sql = "SELECT
                    c.id,
                    c.memoire_id,
                    c.utilisateur_id,
                    c.contenu,
                    c.date_commentaire,
                    CONCAT(u.prenom, ' ', u.nom) AS nom_auteur
                FROM commentaires c
                INNER JOIN utilisateurs u ON u.id = c.utilisateur_id
                WHERE c.memoire_id = :memoire_id
                ORDER BY c.date_commentaire DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':memoire_id' => $memoireId]);

        return $this->hydraterListe($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

     
    public function getParUtilisateur(int $utilisateurId): array
    {
        $sql = "SELECT
                    c.id,
                    c.memoire_id,
                    c.utilisateur_id,
                    c.contenu,
                    c.date_commentaire,
                    CONCAT(u.prenom, ' ', u.nom) AS nom_auteur,
                    m.titre                       AS titre_memoire
                FROM commentaires c
                INNER JOIN utilisateurs u ON u.id = c.utilisateur_id
                INNER JOIN memoires m     ON m.id = c.memoire_id
                WHERE c.utilisateur_id = :utilisateur_id
                ORDER BY c.date_commentaire DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':utilisateur_id' => $utilisateurId]);

        return $this->hydraterListe($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    
    public function getParId(int $id): ?Commentaire
    {
        $sql = "SELECT
                    c.*,
                    CONCAT(u.prenom, ' ', u.nom) AS nom_auteur
                FROM commentaires c
                INNER JOIN utilisateurs u ON u.id = c.utilisateur_id
                WHERE c.id = :id
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->hydrater($row) : null;
    }

   
    public function compterParMemoire(int $memoireId): int
    {
        $sql  = "SELECT COUNT(*) FROM commentaires WHERE memoire_id = :memoire_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':memoire_id' => $memoireId]);
        return (int) $stmt->fetchColumn();
    }

    // ── UPDATE 
     
    public function modifier(int $id, int $utilisateurId, string $nouveauContenu): bool
    {
        $contenu = trim($nouveauContenu);
        if (mb_strlen($contenu) < 10 || mb_strlen($contenu) > 2000) {
            return false;
        }

        $sql = "UPDATE commentaires
                SET contenu = :contenu
                WHERE id = :id
                  AND utilisateur_id = :utilisateur_id";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':contenu'        => $contenu,
            ':id'             => $id,
            ':utilisateur_id' => $utilisateurId,
        ]);
    }

    // ── DELETE 
     
    public function supprimer(int $id, int $utilisateurId): bool
    {
        $sql  = "DELETE FROM commentaires
                 WHERE id = :id AND utilisateur_id = :utilisateur_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':id'             => $id,
            ':utilisateur_id' => $utilisateurId,
        ]);
    }

    // ── HYDRATATION 
     
    private function hydrater(array $row): Commentaire
    {
        $c = new Commentaire(
            (int)  $row['memoire_id'],
            (int)  $row['utilisateur_id'],
                   $row['contenu'],
                   $row['date_commentaire'],
            (int)  $row['id']
        );

        if (isset($row['nom_auteur']))    $c->setNomAuteur($row['nom_auteur']);
        if (isset($row['titre_memoire'])) $c->setTitreMemoire($row['titre_memoire']);

        return $c;
    }

    // Liste de tableaux → liste d'objets Commentaire
    private function hydraterListe(array $rows): array
    {
        return array_map(fn($row) => $this->hydrater($row), $rows);
    }
}