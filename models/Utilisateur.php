<?php
require_once __DIR__ . '/../config/database.php';

class Utilisateur {
    // Attributs
    protected int $id_utilisateur;
    protected string $nom;
    protected string $email;
    protected string $mot_de_passe;
    protected int $centre_id;
    protected string $role;
    protected bool $est_actif;
    protected bool $doit_changer_mdp;
    protected string $date_creation;

    // Getters
    public function getId(): int {
         return $this->id_utilisateur; 
    }
    public function getNom(): string {
         return $this->nom;
     }
    public function getEmail(): string {
         return $this->email; 
    }
    public function getRole(): string {
         return $this->role; 
    }
    public function isActif(): bool {
         return $this->est_actif; 
    }
    public function doitChangerMdp(): bool { 
        return $this->doit_changer_mdp; 
    }

    // Setters
    public function setNom(string $nom): void { 
        $this->nom = $nom; 
    }
    public function setEmail(string $email): void {
         $this->email = $email; 
    }
    public function setRole(string $role): void { 
        $this->role = $role;
    }
    public function setEstActif(bool $actif): void { 
        $this->est_actif = $actif; 
    }
    public function setDoitChangerMdp(bool $val): void { 
        $this->doit_changer_mdp = $val; 
    }
    public function setCentreId(int $id): void { 
        $this->centre_id = $id;
     }

    // Méthodes métier
    public function seConnecter(string $email, string $mdp): bool {
        return $this->email === $email && password_verify($mdp, $this->mot_de_passe);
    }

    public function changerMotDePasse(string $nouveauMdp): bool {
        $this->mot_de_passe = password_hash($nouveauMdp, PASSWORD_BCRYPT);
        return true;
    }
}
?>