<?php
require_once __DIR__ . '/Utilisateur.php';
require_once __DIR__ . '/../config/constants.php';

class Etudiant extends Utilisateur {
    private string $numero_etudiant;
    private string $niveau_etude;
    private int $filiere_id;
    private bool $est_diplome_permanent;

    // Getters
    public function getNumeroEtudiant(): string { 
        return $this->numero_etudiant; 
    }
    public function getNiveauEtude(): string {
         return $this->niveau_etude;
    }
    public function getFiliereId(): int {
         return $this->filiere_id; 
    }
    public function estDiplomePermanent(): bool { 
        return $this->est_diplome_permanent; 
    }

    // Setters
    public function setNumeroEtudiant(string $num): void { $this->numero_etudiant = $num; }
    public function setNiveauEtude(string $niveau): void { $this->niveau_etude = $niveau; }
    public function setFiliereId(int $id): void { $this->filiere_id = $id; }
    public function setEstDiplomePermanent(bool $val): void { $this->est_diplome_permanent = $val; }

    // Méthodes métier
    public function peutDeposer(): bool {
        return in_array($this->niveau_etude, NIVEAUX_DEPOT);
    }

    public function peutCommenter(): bool {
        return true; 
    }

    public function getStatut(): string {
        if ($this->est_diplome_permanent) return 'DIPLOME_PERMANENT';
        if (in_array($this->niveau_etude, NIVEAUX_DEPOT)) return 'DIPLOME_ANNEE_SOUTENANCE';
        return 'COMMENTATEUR';
    }
}