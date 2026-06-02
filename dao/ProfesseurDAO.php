<?php

// Rôle : toutes les requêtes BDD liées au professeur
//        - lister les mémoires en attente de son centre
//        - lister ses mémoires assignés
//        - changer le statut d'un mémoire
//        - ajouter/modifier une remarque


require_once __DIR__ . '/../config/database.php';

class ProfesseurDAO
{
    private PDO $pdo;

    public function __construct()
    {
        $db        = new Database();
        $this->pdo = $db->connect();
    }

    /**
     * Liste les mémoires en attente de vérification du même centre
     * que le professeur connecté — ce sont les mémoires à traiter
     *
     * @param int $centreId  centre du professeur (depuis $_SESSION)
     * @return array
     */
    public function listerEnAttente(int $centreId, ?int $professeurId = null): array
    {
        $sql = "
            SELECT
                m.id_memoire,
                m.titre,
                m.theme,
                m.type_diplome,
                m.annee_soutenance,
                m.date_depot,
                m.statut,
                m.remarques,
                u.nom      AS nom_etudiant,
                u.email    AS email_etudiant,
                e.niveau_etude
            FROM memoire m
            INNER JOIN etudiant  et ON et.utilisateur_id = m.etudiant_id
            INNER JOIN utilisateur u ON u.id_utilisateur  = m.etudiant_id
            INNER JOIN etudiant   e ON e.utilisateur_id  = m.etudiant_id
            WHERE m.statut       = 'en_attente'
              AND u.centre_id    = :centre_id
              AND (m.professeur_id IS NULL OR m.professeur_id = :professeur_id)
            ORDER BY m.date_depot ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':centre_id'    => $centreId,
            ':professeur_id' => $professeurId,
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Liste les mémoires assignés à ce professeur (en_verification)
     * Ce sont les mémoires qu'il est en train de traiter
     *
     * @param int $professeurId  id_utilisateur du professeur
     * @return array
     */
    public function listerMesMemoires(int $professeurId): array
    {
        $sql = "
            SELECT
                m.id_memoire,
                m.titre,
                m.theme,
                m.type_diplome,
                m.annee_soutenance,
                m.date_depot,
                m.statut,
                m.remarques,
                u.nom   AS nom_etudiant,
                e.niveau_etude
            FROM memoire m
            INNER JOIN utilisateur u ON u.id_utilisateur = m.etudiant_id
            INNER JOIN etudiant    e ON e.utilisateur_id = m.etudiant_id
            WHERE m.professeur_id = :professeur_id
              AND m.statut <> 'en_attente'
            ORDER BY m.date_depot DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':professeur_id' => $professeurId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Charge un mémoire avec les infos de l'étudiant
     * Utilisé sur la page de vérification détaillée
     *
     * @param int $idMemoire
     * @return array|null
     */
    public function trouverMemoireAvecEtudiant(int $idMemoire): ?array
    {
        $sql = "
            SELECT
                m.*,
                u.nom        AS nom_etudiant,
                u.email      AS email_etudiant,
                u.centre_id,
                e.niveau_etude,
                e.numero_etudiant,
                f.nom_filiere
            FROM memoire       m
            INNER JOIN utilisateur u ON u.id_utilisateur = m.etudiant_id
            INNER JOIN etudiant    e ON e.utilisateur_id = m.etudiant_id
            LEFT  JOIN filiere     f ON f.id_filiere     = e.filiere_id
            WHERE m.id_memoire = :id
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $idMemoire]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Prend en charge un mémoire : assigne le professeur
     * et passe le statut à en_verification
     * Empêche deux professeurs de prendre le même mémoire
     *
     * @param int $idMemoire
     * @param int $professeurId
     * @return bool
     */
    public function prendreEnCharge(int $idMemoire, int $professeurId): bool
    {
        // La condition WHERE statut='en_attente' est un verrou optimiste :
        // si un autre prof a déjà pris le mémoire, la requête ne met rien à jour
        $sql = "
            UPDATE memoire
            SET statut       = 'en_verification',
                professeur_id = :professeur_id
            WHERE id_memoire = :id
              AND statut     = 'en_attente'
              AND (professeur_id IS NULL OR professeur_id = :professeur_id)
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':professeur_id' => $professeurId,
            ':id'            => $idMemoire,
        ]);

        // rowCount() = 0 si le mémoire avait déjà été pris
        return $stmt->rowCount() > 0;
    }

    /**
     * Valide un mémoire : statut → valide
     * Uniquement si le mémoire appartient bien à ce professeur
     *
     * @param int $idMemoire
     * @param int $professeurId
     * @return bool
     */
    public function valider(int $idMemoire, int $professeurId): bool
    {
        $sql = "
            UPDATE memoire
            SET statut = 'valide'
            WHERE id_memoire    = :id
              AND professeur_id = :professeur_id
              AND statut        = 'en_verification'
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id'            => $idMemoire,
            ':professeur_id' => $professeurId,
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Rejette un mémoire avec une remarque obligatoire
     * statut → rejete
     * La remarque est obligatoire pour qu'on sache pourquoi
     *
     * @param int    $idMemoire
     * @param int    $professeurId
     * @param string $remarque
     * @return bool
     */
    public function rejeter(int $idMemoire, int $professeurId, string $remarque): bool
    {
        $sql = "
            UPDATE memoire
            SET statut    = 'rejete',
                remarques = :remarque
            WHERE id_memoire    = :id
              AND professeur_id = :professeur_id
              AND statut        = 'en_verification'
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id'            => $idMemoire,
            ':professeur_id' => $professeurId,
            ':remarque'      => $remarque,
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Ajoute ou modifie la remarque sans changer le statut
     * Utile pour laisser un commentaire sans encore valider/rejeter
     *
     * @param int    $idMemoire
     * @param int    $professeurId
     * @param string $remarque
     * @return bool
     */
    public function ajouterRemarque(int $idMemoire, int $professeurId, string $remarque): bool
    {
        $sql = "
            UPDATE memoire
            SET remarques = :remarque
            WHERE id_memoire    = :id
              AND professeur_id = :professeur_id
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id'            => $idMemoire,
            ':professeur_id' => $professeurId,
            ':remarque'      => $remarque,
        ]);

        return $stmt->rowCount() >= 0;
    }

    /**
     * Compte les mémoires par statut pour les stats du dashboard
     *
     * @param int $professeurId
     * @return array  ex: ['en_verification' => 3, 'valide' => 12, 'rejete' => 2]
     */
    public function compterParStatut(int $professeurId): array
    {
        $sql = "
            SELECT statut, COUNT(*) AS total
            FROM memoire
            WHERE professeur_id = :professeur_id
            GROUP BY statut
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':professeur_id' => $professeurId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Transformer en tableau associatif statut => total
        $result = [];
        foreach ($rows as $row) {
            $result[$row['statut']] = (int) $row['total'];
        }

        return $result;
    }

    /**
     * Compte les mémoires en attente du centre (pour la pastille dashboard)
     *
     * @param int $centreId
     * @return int
     */
    public function compterEnAttenteCentre(int $centreId, ?int $professeurId = null): int
    {
        $sql = "
            SELECT COUNT(*) 
            FROM memoire m
            INNER JOIN utilisateur u ON u.id_utilisateur = m.etudiant_id
            WHERE m.statut    = 'en_attente'
              AND u.centre_id = :centre_id
              AND (m.professeur_id IS NULL OR m.professeur_id = :professeur_id)
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':centre_id'    => $centreId,
            ':professeur_id' => $professeurId,
        ]);
        return (int) $stmt->fetchColumn();
    }
}
