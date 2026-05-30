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
}