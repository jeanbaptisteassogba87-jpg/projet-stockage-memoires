<?php

class Filiere
{
    private int $id_filiere;
    private string $nom_filiere;
    private int $centre_id;

    // Getters
    public function getId(): int
    {
        return $this->id_filiere;
    }

    public function getNomFiliere(): string
    {
        return $this->nom_filiere;
    }

    public function getCentreId(): int
    {
        return $this->centre_id;
    }

    // Setters
    public function setNomFiliere(string $nom): void
    {
        $this->nom_filiere = $nom;
    }

    public function setCentreId(int $centreId): void
    {
        $this->centre_id = $centreId;
    }
}