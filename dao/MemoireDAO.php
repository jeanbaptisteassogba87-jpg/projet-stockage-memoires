<?php

require_once __DIR__ . '/../models/Memoire.php';

class MemoireDAO
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // ── READ
     
    public function getParId(int $id): ?array
    {
        $sql = "SELECT
                    m.id,
                    m.titre,
                    m.theme,
                    m.fichier_pdf,
                    m.statut,
                    m.type_diplome,
                    m.annee_soutenance,
                    m.date_depot,
                    m.remarques,
                    CONCAT(u.prenom, ' ', u.nom) AS auteur_nom,
                    u.email                       AS auteur_email,
                    f.nom_filiere                 AS filiere,
                    e.niveau_etude                AS niveau
                FROM memoires m
                INNER JOIN utilisateurs u ON u.id = m.utilisateur_id
                LEFT  JOIN etudiant e     ON e.utilisateur_id = m.utilisateur_id
                LEFT  JOIN filieres f     ON f.id = e.filiere_id
                WHERE m.id = :id
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    
    public function rechercher(
        string $motCle  = '',
        string $filiere = '',
        int    $annee   = 0,
        string $niveau  = ''
    ): array {
        $conditions = ["m.statut = 'public'"];
        $params     = [];

        if ($motCle !== '') {
            $conditions[] = "(m.titre  LIKE :mot_cle
                           OR m.theme  LIKE :mot_cle
                           OR CONCAT(u.prenom, ' ', u.nom) LIKE :mot_cle)";
            $params[':mot_cle'] = '%' . $motCle . '%';
        }

        if ($filiere !== '') {
            $conditions[] = "f.nom_filiere = :filiere";
            $params[':filiere'] = $filiere;
        }

        if ($annee > 0) {
            $conditions[] = "m.annee_soutenance = :annee";
            $params[':annee'] = $annee;
        }

        if ($niveau !== '') {
            $conditions[] = "e.niveau_etude = :niveau";
            $params[':niveau'] = $niveau;
        }

        $where = implode(' AND ', $conditions);

        $sql = "SELECT
                    m.id,
                    m.titre,
                    m.theme,
                    m.type_diplome,
                    m.annee_soutenance,
                    m.date_depot,
                    m.fichier_pdf,
                    m.statut,
                    CONCAT(u.prenom, ' ', u.nom) AS auteur_nom,
                    f.nom_filiere                AS filiere,
                    e.niveau_etude               AS niveau,
                    (SELECT COUNT(*)
                     FROM commentaires c
                     WHERE c.memoire_id = m.id)  AS nb_commentaires,
                    (SELECT COUNT(*)
                     FROM likes l
                     WHERE l.memoire_id = m.id)  AS nb_likes
                FROM memoires m
                INNER JOIN utilisateurs u ON u.id = m.utilisateur_id
                LEFT  JOIN etudiant e     ON e.utilisateur_id = m.utilisateur_id
                LEFT  JOIN filieres f     ON f.id = e.filiere_id
                WHERE $where
                ORDER BY m.annee_soutenance DESC, m.date_depot DESC
                LIMIT 100";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

   
    public function getSuggestions(string $terme, int $limite = 8): array
    {
        $sql = "SELECT DISTINCT titre
                FROM memoires
                WHERE statut = 'public'
                  AND titre LIKE :terme
                ORDER BY titre
                LIMIT :limite";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':terme',  '%' . $terme . '%');
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // ── LISTES POUR MENUS DÉROULANTS 
     
    public function getFilieres(): array
    {
        $sql = "SELECT DISTINCT f.nom_filiere
                FROM filieres f
                INNER JOIN etudiant e ON e.filiere_id = f.id
                INNER JOIN memoires m ON m.utilisateur_id = e.utilisateur_id
                WHERE m.statut = 'public'
                ORDER BY f.nom_filiere";

        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN);
    }
   
    public function getAnnees(): array
    {
        $sql = "SELECT DISTINCT annee_soutenance
                FROM memoires
                WHERE statut = 'public'
                  AND annee_soutenance IS NOT NULL
                ORDER BY annee_soutenance DESC";

        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN);
    }

     
    public function getNiveaux(): array
    {
        $sql = "SELECT DISTINCT e.niveau_etude
                FROM etudiant e
                INNER JOIN memoires m ON m.utilisateur_id = e.utilisateur_id
                WHERE m.statut = 'public'
                ORDER BY e.niveau_etude";

        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN);
    }

    // ── LIKES 
     
    public function compterLikes(int $memoireId): int
    {
        $sql  = "SELECT COUNT(*) FROM likes WHERE memoire_id = :memoire_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':memoire_id' => $memoireId]);
        return (int) $stmt->fetchColumn();
    }

    
    public function utilisateurALike(int $memoireId, int $userId): bool
    {
        $sql = "SELECT COUNT(*) FROM likes
                WHERE memoire_id = :memoire_id
                  AND utilisateur_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':memoire_id' => $memoireId,
            ':user_id'    => $userId,
        ]);
        return (int) $stmt->fetchColumn() > 0;
    }


    public function ajouterLike(int $memoireId, int $userId): bool
    {
        $sql = "INSERT IGNORE INTO likes (memoire_id, utilisateur_id, date_creation)
                VALUES (:memoire_id, :user_id, NOW())";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':memoire_id' => $memoireId,
            ':user_id'    => $userId,
        ]);
    }

    public function retirerLike(int $memoireId, int $userId): bool
    {
        $sql = "DELETE FROM likes
                WHERE memoire_id = :memoire_id
                  AND utilisateur_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':memoire_id' => $memoireId,
            ':user_id'    => $userId,
        ]);
    }
}