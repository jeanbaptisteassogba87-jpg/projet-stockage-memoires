<?php
require_once __DIR__ . '/Utilisateur.php';

class DirecteurEtudes extends Utilisateur {
    private string $responsabilite;

    public function getResponsabilite(): string {
        return $this->responsabilite;
    }

    public function setResponsabilite(string $responsabilite): void {
        $this->responsabilite = $responsabilite;
    }

    // Méthodes métier
    public function gererVisibilite(): bool {
        return true;
    }

    public function validerMiseEnLigne(): bool {
        return true;
    }
}