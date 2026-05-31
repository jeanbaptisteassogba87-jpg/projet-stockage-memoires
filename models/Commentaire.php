<?php

class Commentaire
{
    private ?int    $id;
    private int     $memoireId;
    private int     $utilisateurId;
    private string  $contenu;
    private string  $dateCommentaire;
    private ?string $nomAuteur;
    private ?string $titreMemoire;

    // ── Constructeur 
    public function __construct(
        int    $memoireId       = 0,
        int    $utilisateurId   = 0,
        string $contenu         = '',
        string $dateCommentaire = '',
        ?int   $id              = null
    ) {
        $this->memoireId       = $memoireId;
        $this->utilisateurId   = $utilisateurId;
        $this->contenu         = $contenu;
        $this->dateCommentaire = $dateCommentaire ?: date('Y-m-d H:i:s');
        $this->id              = $id;
        $this->nomAuteur       = null;
        $this->titreMemoire    = null;
    }

    // ── Getters 
    public function getId(): ?int              { return $this->id; }
    public function getMemoireId(): int        { return $this->memoireId; }
    public function getUtilisateurId(): int    { return $this->utilisateurId; }
    public function getContenu(): string       { return $this->contenu; }
    public function getDateCommentaire(): string { return $this->dateCommentaire; }
    public function getNomAuteur(): ?string    { return $this->nomAuteur; }
    public function getTitreMemoire(): ?string { return $this->titreMemoire; }

    // ── Setters 
    public function setId(int $id): void                  { $this->id = $id; }
    public function setMemoireId(int $memoireId): void    { $this->memoireId = $memoireId; }
    public function setUtilisateurId(int $uid): void      { $this->utilisateurId = $uid; }
    public function setContenu(string $contenu): void     { $this->contenu = $contenu; }
    public function setDateCommentaire(string $d): void   { $this->dateCommentaire = $d; }
    public function setNomAuteur(?string $nom): void      { $this->nomAuteur = $nom; }
    public function setTitreMemoire(?string $titre): void { $this->titreMemoire = $titre; }

    // ── Validation métier 
    public function valider(): array
    {
        $erreurs        = [];
        $contenuNettoye = trim($this->contenu);

        if (empty($contenuNettoye)) {
            $erreurs[] = "Le commentaire ne peut pas être vide.";
        }
        if (mb_strlen($contenuNettoye) < 10) {
            $erreurs[] = "Le commentaire doit contenir au moins 10 caractères.";
        }
        if (mb_strlen($contenuNettoye) > 2000) {
            $erreurs[] = "Le commentaire ne peut pas dépasser 2000 caractères.";
        }
        if ($this->memoireId <= 0) {
            $erreurs[] = "Mémoire invalide.";
        }
        if ($this->utilisateurId <= 0) {
            $erreurs[] = "Utilisateur invalide.";
        }

        return $erreurs;
    }

    // ── Utilitaires 

    /** Contenu sécurisé anti-XSS */
    public function getContenuHtml(): string
    {
        return nl2br(htmlspecialchars($this->contenu, ENT_QUOTES, 'UTF-8'));
    }

    // Date formatée en français : "12/05/2025 à 14h30"
    public function getDateFormatee(): string
    {
        if (empty($this->dateCommentaire)) return '';
        $date = new DateTime($this->dateCommentaire);
        return $date->format('d/m/Y à H\hi');
    }

    /** Export tableau (JSON / API) */
    public function toArray(): array
    {
        return [
            'id'               => $this->id,
            'memoire_id'       => $this->memoireId,
            'utilisateur_id'   => $this->utilisateurId,
            'contenu'          => $this->contenu,
            'date_commentaire' => $this->dateCommentaire,
            'nom_auteur'       => $this->nomAuteur,
            'titre_memoire'    => $this->titreMemoire,
        ];
    }
}