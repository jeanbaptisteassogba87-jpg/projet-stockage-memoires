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

    /**
     * Vérifie si un étudiant a déjà un mémoire pour un type de diplôme donné
     * Utilisé pour respecter la contrainte UNIQUE(etudiant_id, type_diplome)
     * définie dans database.sql
     *
     * @param int    $etudiantId   id de l'étudiant connecté
     * @param string $typeDiplome  'licence' ou 'master'
     * @return array|null          le mémoire existant, ou null
     */
    public function trouverParEtudiantEtType(int $etudiantId, string $typeDiplome): ?array
    {
        $sql = "
            SELECT *
            FROM memoire
            WHERE etudiant_id  = :etudiant_id
              AND type_diplome  = :type_diplome
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':etudiant_id'  => $etudiantId,
            ':type_diplome' => $typeDiplome,
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Met à jour les champs modifiables d'un mémoire
     * Seuls les champs présents dans $data sont mis à jour
     * Utilisé par EtudiantController action=modifier_memoire
     *
     * @param int   $id    id_memoire
     * @param array $data  clés : titre, theme, annee_soutenance, statut, fichier_pdf (optionnel)
     * @return bool
     */
    public function modifierMemoire(int $id, array $data): bool
    {
        // Construire dynamiquement la liste SET selon les clés fournies
        // Les colonnes autorisées — jamais de champ arbitraire depuis POST
        $colonnesAutorisees = [
            'titre', 'theme', 'annee_soutenance',
            'statut', 'fichier_pdf', 'remarques', 'professeur_id'
        ];

        $setClauses = [];
        $params     = [':id' => $id];

        foreach ($data as $colonne => $valeur) {
            if (!in_array($colonne, $colonnesAutorisees)) {
                continue; // ignorer toute colonne non autorisée
            }
            $setClauses[]         = "$colonne = :$colonne";
            $params[":$colonne"]  = $valeur;
        }

        if (empty($setClauses)) {
            return false; // rien à mettre à jour
        }

        $sql = "UPDATE memoire SET " . implode(', ', $setClauses) . " WHERE id_memoire = :id";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Compte les mémoires d'un étudiant
     * Affiché dans les cartes statistiques du dashboard étudiant
     *
     * @param int $etudiantId
     * @return int
     */
    public function compterParEtudiant(int $etudiantId): int
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM memoire WHERE etudiant_id = :id"
        );
        $stmt->execute([':id' => $etudiantId]);
        return (int) $stmt->fetchColumn();
    }
}