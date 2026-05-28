<?php

class Like
{
    private int $id_like;
    private string $date_creation;
    private int $memoire_id;
    private int $utilisateur_id;

    // Getters
    public function getId(): int
    {
        return $this->id_like;
    }

    public function getDateCreation(): string
    {
        return $this->date_creation;
    }
    public function getMemoireId(): int {
        return $this->memoire_id;
    }
    public function getUtilisateurId(): int {
         return $this->utilisateur_id; 
    }

    // Setter
    public function setDateCreation(string $date): void
    {
        $this->date_creation = $date;
    }
    public function setMemoireId(int $id): void {
         $this->memoire_id = $id; 
    }
    public function setUtilisateurId(int $id): void {
         $this->utilisateur_id = $id; 
    }    

    // Méthodes métier
    public function ajouter(): bool
    {
        return true;
    }

    public function retirer(): bool
    {
        return true;
    }
}