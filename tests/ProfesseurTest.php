<?php

// Rôle : teste la logique métier du modèle Professeur
//        getters/setters, méthodes métier, héritage


use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Professeur.php';
require_once __DIR__ . '/../config/constants.php';

class ProfesseurTest extends TestCase
{
    private Professeur $professeur;

    protected function setUp(): void
    {
        $this->professeur = new Professeur();
    }

    // ── Getters / Setters ────────────────────────────────────

    /**
     * Vérifie que le grade se stocke correctement
     */
    public function testGrade(): void
    {
        $this->professeur->setGrade('Maître de conférences');
        $this->assertSame('Maître de conférences', $this->professeur->getGrade());
    }

    /**
     * Vérifie que la spécialité se stocke correctement
     */
    public function testSpecialite(): void
    {
        $this->professeur->setSpecialite('Développement web');
        $this->assertSame('Développement web', $this->professeur->getSpecialite());
    }

    // ── Méthodes métier ──────────────────────────────────────

    /**
     * verifierMemoire() doit retourner true
     * C'est la méthode de base du workflow de vérification
     */
    public function testVerifierMemoire(): void
    {
        $this->assertTrue($this->professeur->verifierMemoire());
    }

    /**
     * faireRemarques() doit retourner true
     */
    public function testFaireRemarques(): void
    {
        $this->assertTrue($this->professeur->faireRemarques());
    }

    /**
     * validerMemoire() doit retourner true
     */
    public function testValiderMemoire(): void
    {
        $this->assertTrue($this->professeur->validerMemoire());
    }

    /**
     * rejeterMemoire() doit retourner true
     */
    public function testRejeterMemoire(): void
    {
        $this->assertTrue($this->professeur->rejeterMemoire());
    }

    // ── Héritage Utilisateur ─────────────────────────────────

    /**
     * Professeur hérite bien de Utilisateur
     * Les méthodes de la classe parente doivent fonctionner
     */
    public function testHeritageUtilisateur(): void
    {
        $this->professeur->setNom('Prof Akpo');
        $this->professeur->setEmail('prof@uatm.bj');
        $this->professeur->setRole(ROLE_PROFESSEUR);
        $this->professeur->setCentreId(1);

        $this->assertSame('Prof Akpo',        $this->professeur->getNom());
        $this->assertSame('prof@uatm.bj',     $this->professeur->getEmail());
        $this->assertSame(ROLE_PROFESSEUR,    $this->professeur->getRole());
        $this->assertSame(1,                  $this->professeur->getCentreId());
        $this->assertInstanceOf(Professeur::class, $this->professeur);
    }

    /**
     * Vérifie que Professeur est bien une instance de Utilisateur
     */
    public function testEstUneInstanceDeUtilisateur(): void
    {
        $this->assertInstanceOf(
            \Utilisateur::class,
            $this->professeur
        );
    }

    // ── Combinaison grade + spécialité ───────────────────────

    /**
     * Vérifie qu'on peut définir grade et spécialité indépendamment
     */
    public function testGradeEtSpecialiteIndependants(): void
    {
        $this->professeur->setGrade('Assistant');
        $this->professeur->setSpecialite('Réseaux informatiques');

        $this->assertSame('Assistant',            $this->professeur->getGrade());
        $this->assertSame('Réseaux informatiques',$this->professeur->getSpecialite());
    }
}