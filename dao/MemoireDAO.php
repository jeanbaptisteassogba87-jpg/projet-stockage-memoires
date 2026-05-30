<?php
require_once __DIR__ . '/../models/Memoire.php';
require_once __DIR__ . '/../config/database.php';

class MemoireDAO {
    private PDO $pdo;

    public function __construct() {
        $db = new Database();
        $this->pdo = $db->connect();
    }

    // Insérer un seul mémoire
    public function ajouterMemoire(Memoire $memoire): bool {
        $sql = "INSERT INTO memoire 
                (etudiant_id, titre, theme, fichier_pdf, statut, 
                type_diplome, annee_soutenance, remarques)
                VALUES 
                (:etudiant_id, :titre, :theme, :fichier_pdf, :statut, 
                :type_diplome, :annee_soutenance, :remarques)";
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
        ]);
    }

    // Importer plusieurs mémoires anciens
    public function importerPlusieurs(array $memoires): bool {
        foreach ($memoires as $memoire) {
            if (!$this->ajouterMemoire($memoire)) {
                return false;
            }
        }
        return true;
    }

    // Lister tous les mémoires
    public function listerTous(): array {
        $stmt = $this->pdo->query("SELECT * FROM memoire ORDER BY date_depot DESC");
        return $stmt->fetchAll();
    }

    // Trouver par ID
    public function trouverParId(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM memoire WHERE id_memoire = :id");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function trouverParIdEtEtudiant(int $id, int $etudiantId): ?array {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM memoire WHERE id_memoire = :id AND etudiant_id = :etudiant_id LIMIT 1"
        );
        $stmt->execute([
            ':id' => $id,
            ':etudiant_id' => $etudiantId
        ]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    // Supprimer
    public function supprimer(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM memoire WHERE id_memoire = :id");
        return $stmt->execute([':id' => $id]);
    }

    // Lister les mémoires d'un étudiant
    public function listerParEtudiant(int $etudiantId): array {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM memoire WHERE etudiant_id = :id ORDER BY date_depot DESC"
        );
        $stmt->execute([':id' => $etudiantId]);
        return $stmt->fetchAll();
    }

    public function listerRejetesParEtudiant(int $etudiantId): array {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM memoire
             WHERE etudiant_id = :id AND statut = 'rejete'
             ORDER BY date_depot DESC"
        );
        $stmt->execute([':id' => $etudiantId]);
        return $stmt->fetchAll();
    }

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
                statut = 'en_attente',
                remarques = '',
                professeur_id = NULL,
                date_depot = CURRENT_TIMESTAMP
            WHERE id_memoire = :id
              AND etudiant_id = :etudiant_id
              AND statut = 'rejete'
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':titre' => $titre,
            ':theme' => $theme,
            ':annee_soutenance' => $anneeSoutenance,
            ':fichier_pdf' => $fichierPdf,
            ':id' => $memoireId,
            ':etudiant_id' => $etudiantId
        ]);

        return $stmt->rowCount() > 0;
    }

    // Vérifier si un étudiant a déjà un mémoire du même type
    public function existeMemoireParTypeDiplome(int $etudiantId, string $typeDiplome): bool {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) as count FROM memoire 
             WHERE etudiant_id = :etudiant_id AND type_diplome = :type_diplome"
        );
        $stmt->execute([
            ':etudiant_id' => $etudiantId,
            ':type_diplome' => $typeDiplome
        ]);
        $result = $stmt->fetch();
        return ($result['count'] ?? 0) > 0;
    }

    // Trouver le mémoire d'un étudiant par type de diplôme
    public function trouverParEtudiantEtType(int $etudiantId, string $typeDiplome): ?array {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM memoire 
             WHERE etudiant_id = :etudiant_id AND type_diplome = :type_diplome 
             LIMIT 1"
        );
        $stmt->execute([
            ':etudiant_id' => $etudiantId,
            ':type_diplome' => $typeDiplome
        ]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
}
