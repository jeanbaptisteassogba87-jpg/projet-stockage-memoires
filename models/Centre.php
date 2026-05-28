<?php

class Centre
{
    private int $id_centre;
    private string $nom_centre;
    private string $adresse;
    private string $telephone;
    private bool $est_centre_principal;

    // Getters
    public function getId(): int
    {
        return $this->id_centre;
    }

    public function getNomCentre(): string
    {
        return $this->nom_centre;
    }

    public function getAdresse(): string
    {
        return $this->adresse;
    }

    public function getTelephone(): string
    {
        return $this->telephone;
    }

    public function isEstCentrePrincipal(): bool
    {
        return $this->est_centre_principal;
    }

    // Setters
    public function setNomCentre(string $nom): void
    {
        $this->nom_centre = $nom;
    }

    public function setAdresse(string $adresse): void
    {
        $this->adresse = $adresse;
    }

    public function setTelephone(string $telephone): void
    {
        $this->telephone = $telephone;
    }

    public function setEstCentrePrincipal(bool $est): void
    {
        $this->est_centre_principal = $est;
    }
}