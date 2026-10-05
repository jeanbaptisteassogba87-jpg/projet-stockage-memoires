<?php
require_once __DIR__ . '/../models/Memoire.php';
require_once __DIR__ . '/../config/database.php';

class MemoireDAO {
    private PDO $pdo;

    public function __construct() {
        $db = new Database();
        $this->pdo = $db->connect();
    }

    /**
     * Insérer un seul mémoire
     */
    public function ajouterMemoire(Memoire $memoire): bool {
        $sql = "INSERT INTO memoire 
                (etudiant_id, titre, theme, fichier_pdf, statut, 
                type_diplome, annee_soutenance, remarques, professeur_id, etudiant2_id)
                VALUES 
                (:etudiant_id, :titre, :theme, :fichier_pdf, :statut, 
                :type_diplome, :annee_soutenance, :remarques, :professeur_id, :etudiant2_id)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':etudiant_id'      => $memoire->getEtudiantId(),
            ':titre'            => $memoire->getTitre(),
            ':theme'            => $memoire->getTheme(),
            ':fichier_pdf'      => $memoire->getFichierPdf(),
            ':statut'           => $memoire->getStatut(),
            ':type_diplome'     => $memoire->getTypeDiplome(),
            ':annee_soutenance' => $memoire->getAnneeSoutenance(),
            ':remarques'        => $memoire->getRemarques(),
            ':professeur_id'    => $memoire->getProfesseurId(),
            ':etudiant2_id'     => $memoire->getEtudiant2Id(),
        ]);
    }

    /**
     * Importer plusieurs mémoires anciens
     */
    public function importerPlusieurs(array $memoires): bool {
        foreach ($memoires as $memoire) {
            if (!$this->ajouterMemoire($memoire)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Lister tous les mémoires
     */
    public function listerTous(): array {
        $stmt = $this->pdo->query("SELECT * FROM memoire ORDER BY date_depot DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Trouver un mémoire par ID
     */
    public function trouverParId(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM memoire WHERE id_memoire = :id");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Trouver un mémoire par ID ET l'étudiant propriétaire
     */
    public function trouverParIdEtEtudiant(int $id, int $etudiantId): ?array {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM memoire WHERE id_memoire = :id 
             AND (etudiant_id = :etudiant_id OR etudiant2_id = :etudiant_id) LIMIT 1"
        );
        $stmt->execute([
            ':id' => $id,
            ':etudiant_id' => $etudiantId
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Supprimer un mémoire
     */
    public function supprimer(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM memoire WHERE id_memoire = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Lister les mémoires d'un étudiant (y compris en binôme)
     */
    public function listerParEtudiant(int $etudiantId): array {
        $stmt = $this->pdo->prepare(
            "SELECT
                m.*,
                u.nom AS auteur_nom,
                u2.nom AS binome_nom,
                e2.numero_etudiant AS binome_numero,
                e2.niveau_etude AS binome_niveau,
                f2.nom_filiere AS binome_filiere
             FROM memoire m
             INNER JOIN utilisateur u ON u.id_utilisateur = m.etudiant_id
             LEFT JOIN utilisateur u2 ON u2.id_utilisateur = m.etudiant2_id
             LEFT JOIN etudiant e2 ON e2.utilisateur_id = u2.id_utilisateur
             LEFT JOIN filiere f2 ON f2.id_filiere = e2.filiere_id
             WHERE m.etudiant_id = :id OR m.etudiant2_id = :id
             ORDER BY m.date_depot DESC"
        );
        $stmt->execute([':id' => $etudiantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lister les mémoires rejetés d'un étudiant (pour correction)
     */
    public function listerRejetesParEtudiant(int $etudiantId): array {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM memoire
             WHERE (etudiant_id = :id OR etudiant2_id = :id) 
             AND statut = :statut
             ORDER BY date_depot DESC"
        );
        $stmt->execute([
            ':id' => $etudiantId,
            ':statut' => STATUT_REJETE
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Mettre à jour une version corrigée d'un mémoire
     */
    public function mettreAJourVersionCorrigee(
        int $memoireId,
        int $etudiantId,
        string $titre,
        string $theme,
        int $anneeSoutenance,
        string $fichierPdf
    ): bool {
        $sql = "
            UPDATE memoire
            SET
                titre = :titre,
                theme = :theme,
                annee_soutenance = :annee_soutenance,
                fichier_pdf = :fichier_pdf,
                statut = :statut,
                remarques = '',
                professeur_id = NULL,
                date_depot = CURRENT_TIMESTAMP
            WHERE id_memoire = :id
              AND (etudiant_id = :etudiant_id OR etudiant2_id = :etudiant_id)
              AND statut = :statut_ancien
        ";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':titre' => $titre,
            ':theme' => $theme,
            ':annee_soutenance' => $anneeSoutenance,
            ':fichier_pdf' => $fichierPdf,
            ':statut' => STATUT_EN_ATTENTE,
            ':id' => $memoireId,
            ':etudiant_id' => $etudiantId,
            ':statut_ancien' => STATUT_REJETE
        ]);
    }

    /**
     * Vérifie si un étudiant a déjà un mémoire du même type de diplôme
     */
    public function existeMemoireParTypeDiplome(int $etudiantId, string $typeDiplome): bool {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) as count FROM memoire 
             WHERE (etudiant_id = :etudiant_id OR etudiant2_id = :etudiant_id) 
             AND type_diplome = :type_diplome"
        );
        $stmt->execute([
            ':etudiant_id' => $etudiantId,
            ':type_diplome' => $typeDiplome
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($result['count'] ?? 0) > 0;
    }

    /**
     * Trouver le mémoire d'un étudiant par type de diplôme
     */
    public function trouverParEtudiantEtType(int $etudiantId, string $typeDiplome): ?array {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM memoire 
             WHERE (etudiant_id = :etudiant_id OR etudiant2_id = :etudiant_id) 
             AND type_diplome = :type_diplome 
             LIMIT 1"
        );
        $stmt->execute([
            ':etudiant_id' => $etudiantId,
            ':type_diplome' => $typeDiplome
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Modifier les champs d'un mémoire (titre, theme, statut, etc.)
     */
    public function modifierMemoire(int $id, array $data): bool {
        $colonnesAutorisees = [
            'titre', 'theme', 'annee_soutenance',
            'statut', 'fichier_pdf', 'remarques', 'professeur_id'
        ];

        $setClauses = [];
        $params = [':id' => $id];

        foreach ($data as $colonne => $valeur) {
            if (!in_array($colonne, $colonnesAutorisees)) {
                continue; // Ignorer toute colonne non autorisée
            }
            $setClauses[] = "$colonne = :$colonne";
            $params[":$colonne"] = $valeur;
        }

        if (empty($setClauses)) {
            return false; // Rien à mettre à jour
        }

        $sql = "UPDATE memoire SET " . implode(', ', $setClauses) . " WHERE id_memoire = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Compter les mémoires d'un étudiant
     */
    public function compterParEtudiant(int $etudiantId): int {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM memoire 
             WHERE etudiant_id = :id OR etudiant2_id = :id"
        );
        $stmt->execute([':id' => $etudiantId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Lister les mémoires publiés (pour consultation publique)
     */
    public function listerPublies(): array {
        $stmt = $this->pdo->prepare(
            "SELECT m.*, u.nom AS auteur_nom 
             FROM memoire m
             INNER JOIN utilisateur u ON u.id_utilisateur = m.etudiant_id
             WHERE m.statut = :statut
             ORDER BY m.date_depot DESC"
        );
        $stmt->execute([':statut' => STATUT_PUBLIE]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
