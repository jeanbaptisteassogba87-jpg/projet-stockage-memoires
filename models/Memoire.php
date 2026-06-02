<?php

class Memoire
{
    private int $id_memoire;
    private string $titre;
    private string $theme;
    private string $fichier_pdf;
    private string $statut;
    private string $type_diplome;
    private int $annee_soutenance;
    private string $date_depot;
    private string $remarques;
    private int $etudiant_id;
    private ?int $etudiant2_id = null;
    private ?int $professeur_id = null;

    // Getters
    public function getId(): int
    {
        return $this->id_memoire;
    }

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function getTheme(): string
    {
        return $this->theme;
    }

    public function getFichierPdf(): string
    {
        return $this->fichier_pdf;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function getTypeDiplome(): string
    {
        return $this->type_diplome;
    }

    public function getAnneeSoutenance(): int
    {
        return $this->annee_soutenance;
    }

    public function getDateDepot(): string
    {
        return $this->date_depot;
    }

    public function getRemarques(): string
    {
        return $this->remarques;
    }

    public function getEtudiantId(): int { 
        return $this->etudiant_id; 
    }
    public function getEtudiant2Id(): ?int {
        return $this->etudiant2_id;
    }
    public function getProfesseurId(): ?int {
        return $this->professeur_id; 
    }


    // Setters
    public function setTitre(string $titre): void
    {
        $this->titre = $titre;
    }

    public function setTheme(string $theme): void
    {
        $this->theme = $theme;
    }

    public function setFichierPdf(string $fichier): void
    {
        $this->fichier_pdf = $fichier;
    }

    public function setStatut(string $statut): void
    {
        $this->statut = $statut;
    }

    public function setTypeDiplome(string $type): void
    {
        $this->type_diplome = $type;
    }

    public function setAnneeSoutenance(int $annee): void
    {
        $this->annee_soutenance = $annee;
    }

    public function setDateDepot(string $date): void
    {
        $this->date_depot = $date;
    }

    public function setRemarques(string $remarques): void
    {
        $this->remarques = $remarques;
    }

    public function setEtudiantId(int $id): void { 
        $this->etudiant_id = $id; 
    }
    public function setEtudiant2Id(?int $id): void {
        $this->etudiant2_id = $id;
    }
    public function setProfesseurId(?int $id): void {
        $this->professeur_id = $id;
    }

    // Méthodes métier
    public function deposer(): bool
    {
        return true;
    }

    public function modifier(): bool
    {
        return true;
    }
}
