<?php
require_once __DIR__ . '/Utilisateur.php';

class Technicien extends Utilisateur {
    private string $service;

    public function getService(): string {
        return $this->service;
    }

    public function setService(string $service): void {
        $this->service = $service;
    }

    // Méthodes métier
    public function importerMemoires(): bool {
        return true;
    }

    public function gererComptesUtilisateurs(): bool {
        return true;
    }

    public function gererDroitsAcces(): bool {
        return true;
    }

    public function restaurerSysteme(): bool {
        return true;
    }
}