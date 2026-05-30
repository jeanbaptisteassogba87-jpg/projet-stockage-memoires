<?php

class Utilisateur
{
    private $id;
    private $nom;
    private $email;
    private $motDePasse;
    private $role;
    private $centreId;
    private $estActif;
    private $doitChangerMdp;

    // GETTERS

    public function getId()
    {
        return $this->id;
    }

    public function getNom()
    {
        return $this->nom;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getMotDePasse()
    {
        return $this->motDePasse;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function getCentreId()
    {
        return $this->centreId;
    }

    public function getEstActif()
    {
        return $this->estActif;
    }

    public function getDoitChangerMdp()
    {
        return $this->doitChangerMdp;
    }

    // SETTERS

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setNom($nom)
    {
        $this->nom = $nom;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function setMotDePasse($motDePasse)
    {
        $this->motDePasse = $motDePasse;
    }

    public function setRole($role)
    {
        $this->role = $role;
    }

    public function setCentreId($centreId)
    {
        $this->centreId = $centreId;
    }

    public function setEstActif($estActif)
    {
        $this->estActif = $estActif;
    }

    public function setDoitChangerMdp($doitChangerMdp)
    {
        $this->doitChangerMdp = $doitChangerMdp;
    }
}