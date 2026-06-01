<?php

// Rôle : teste les getters/setters du modèle Utilisateur et la logique de mot de passe


use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Utilisateur.php';

class UtilisateurTest extends TestCase
{
    private Utilisateur $user;

    // setUp() est appelé automatiquement avant chaque test
    protected function setUp(): void
    {
        $this->user = new Utilisateur();
    }

    // ── Getters / Setters ────────────────────────────────────

    /**
     * Vérifie que setNom + getNom fonctionnent correctement
     */
    public function testNom(): void
    {
        $this->user->setNom('Kofi Mensah');
        $this->assertSame('Kofi Mensah', $this->user->getNom());
    }

    /**
     * Vérifie que setEmail + getEmail fonctionnent correctement
     */
    public function testEmail(): void
    {
        $this->user->setEmail('kofi@uatm.bj');
        $this->assertSame('kofi@uatm.bj', $this->user->getEmail());
    }

    /**
     * Vérifie que setRole + getRole fonctionnent correctement
     */
    public function testRole(): void
    {
        $this->user->setRole('etudiant');
        $this->assertSame('etudiant', $this->user->getRole());
    }

    /**
     * Vérifie que setCentreId + getCentreId fonctionnent
     */
    public function testCentreId(): void
    {
        $this->user->setCentreId(1);
        $this->assertSame(1, $this->user->getCentreId());
    }

    /**
     * Vérifie que setEstActif + getEstActif fonctionnent
     */
    public function testEstActif(): void
    {
        $this->user->setEstActif(1);
        $this->assertEquals(1, $this->user->getEstActif());

        $this->user->setEstActif(0);
        $this->assertEquals(0, $this->user->getEstActif());
    }

    /**
     * Vérifie que le flag doit_changer_mdp fonctionne
     */
    public function testDoitChangerMdp(): void
    {
        $this->user->setDoitChangerMdp(true);
        $this->assertTrue((bool) $this->user->getDoitChangerMdp());

        $this->user->setDoitChangerMdp(false);
        $this->assertFalse((bool) $this->user->getDoitChangerMdp());
    }

    // ── Mot de passe ─────────────────────────────────────────

    /**
     * Vérifie que le hash bcrypt stocké est bien vérifié
     * par password_verify() — c'est ce que fait AuthController
     */
    public function testMotDePasseHash(): void
    {
        $mdpClair = 'MonMotDePasse123!';
        $hash     = password_hash($mdpClair, PASSWORD_DEFAULT);

        $this->user->setMotDePasse($hash);

        // Le hash stocké doit être vérifié par password_verify
        $this->assertTrue(
            password_verify($mdpClair, $this->user->getMotDePasse())
        );
    }

    /**
     * Vérifie qu'un mauvais mot de passe ne passe pas
     */
    public function testMauvaisMotDePasse(): void
    {
        $hash = password_hash('CorrectPassword', PASSWORD_DEFAULT);
        $this->user->setMotDePasse($hash);

        $this->assertFalse(
            password_verify('MauvaisMotDePasse', $this->user->getMotDePasse())
        );
    }

    // ── Id ───────────────────────────────────────────────────

    /**
     * Vérifie que setId + getId fonctionnent
     */
    public function testId(): void
    {
        $this->user->setId(42);
        $this->assertSame(42, $this->user->getId());
    }
}