<?php

class Commentaire
{
    private int $id_commentaire;
    private string $contenu;
    private string $date_creation;

    // Getters
    public function getId(): int
    {
        return $this->id_commentaire;
    }

    public function getContenu(): string
    {
        return $this->contenu;
    }

    public function getDateCreation(): string
    {
        return $this->date_creation;
    }

    // Setters
    public function setContenu(string $contenu): void
    {
        $this->contenu = $contenu;
    }

    public function setDateCreation(string $date): void
    {
        $this->date_creation = $date;
    }

    // Méthodes métier
    public function ajouter(): bool
    {
        return true;
    }

    public function supprimer(): bool
    {
        return true;
    }
}