<?php

class Like
{
    private int $id_like;
    private string $date_creation;

    // Getters
    public function getId(): int
    {
        return $this->id_like;
    }

    public function getDateCreation(): string
    {
        return $this->date_creation;
    }

    // Setter
    public function setDateCreation(string $date): void
    {
        $this->date_creation = $date;
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