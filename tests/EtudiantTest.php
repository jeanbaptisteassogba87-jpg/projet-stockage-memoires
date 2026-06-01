<?php

// Rôle : teste la logique métier du modèle Etudiant
//        peutDeposer(), getStatut(), héritage Utilisateur


use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Etudiant.php';
require_once __DIR__ . '/../config/constants.php';

class EtudiantTest extends TestCase
{
    // ── peutDeposer() ────────────────────────────────────────

    /**
     * Un étudiant en L3 peut déposer un mémoire
     * NIVEAUX_DEPOT = ['L3', 'M2'] défini dans constants.php
     */
    public function testL3PeutDeposer(): void
    {
        $etudiant = new Etudiant();
        $etudiant->setNiveauEtude('L3');

        $this->assertTrue($etudiant->peutDeposer());
    }

    /**
     * Un étudiant en M2 peut déposer un mémoire
     */
    public function testM2PeutDeposer(): void
    {
        $etudiant = new Etudiant();
        $etudiant->setNiveauEtude('M2');

        $this->assertTrue($etudiant->peutDeposer());
    }

    /**
     * Un étudiant en L1 ne peut PAS déposer
     */
    public function testL1NePeutPasDeposer(): void
    {
        $etudiant = new Etudiant();
        $etudiant->setNiveauEtude('L1');

        $this->assertFalse($etudiant->peutDeposer());
    }

    /**
     * Un étudiant en L2 ne peut PAS déposer
     */
    public function testL2NePeutPasDeposer(): void
    {
        $etudiant = new Etudiant();
        $etudiant->setNiveauEtude('L2');

        $this->assertFalse($etudiant->peutDeposer());
    }

    /**
     * Un étudiant en M1 ne peut PAS déposer
     */
    public function testM1NePeutPasDeposer(): void
    {
        $etudiant = new Etudiant();
        $etudiant->setNiveauEtude('M1');

        $this->assertFalse($etudiant->peutDeposer());
    }

    // ── peutCommenter() ──────────────────────────────────────

    /**
     * Tout étudiant peut commenter, quel que soit son niveau
     */
    public function testToutEtudiantPeutCommenter(): void
    {
        $etudiant = new Etudiant();

        foreach (['L1', 'L2', 'L3', 'M1', 'M2'] as $niveau) {
            $etudiant->setNiveauEtude($niveau);
            $this->assertTrue(
                $etudiant->peutCommenter(),
                "L'étudiant en $niveau devrait pouvoir commenter"
            );
        }
    }

    // ── getStatut() ──────────────────────────────────────────

    /**
     * Un diplômé permanent a le statut DIPLOME_PERMANENT
     */
    public function testStatutDiplomePermanent(): void
    {
        $etudiant = new Etudiant();
        $etudiant->setEstDiplomePermanent(true);
        $etudiant->setNiveauEtude('L3');

        $this->assertSame('DIPLOME_PERMANENT', $etudiant->getStatut());
    }

    /**
     * Un étudiant L3 non diplômé permanent a le statut DIPLOME_ANNEE_SOUTENANCE
     */
    public function testStatutDiplomeAnneeSoutenance(): void
    {
        $etudiant = new Etudiant();
        $etudiant->setEstDiplomePermanent(false);
        $etudiant->setNiveauEtude('L3');

        $this->assertSame('DIPLOME_ANNEE_SOUTENANCE', $etudiant->getStatut());
    }

    /**
     * Un étudiant L1 non diplômé permanent a le statut COMMENTATEUR
     */
    public function testStatutCommentateur(): void
    {
        $etudiant = new Etudiant();
        $etudiant->setEstDiplomePermanent(false);
        $etudiant->setNiveauEtude('L1');

        $this->assertSame('COMMENTATEUR', $etudiant->getStatut());
    }

    // ── Getters / Setters ────────────────────────────────────

    /**
     * Vérifie les setters/getters spécifiques à Etudiant
     */
    public function testGettersSetters(): void
    {
        $etudiant = new Etudiant();
        $etudiant->setNumeroEtudiant('ETU2024001');
        $etudiant->setNiveauEtude('M2');
        $etudiant->setFiliereId(2);
        $etudiant->setEstDiplomePermanent(false);

        $this->assertSame('ETU2024001', $etudiant->getNumeroEtudiant());
        $this->assertSame('M2',         $etudiant->getNiveauEtude());
        $this->assertSame(2,            $etudiant->getFiliereId());
        $this->assertFalse($etudiant->estDiplomePermanent());
    }

    // ── Héritage Utilisateur ─────────────────────────────────

    /**
     * Etudiant hérite bien de Utilisateur
     * On peut utiliser les setters/getters de la classe parente
     */
    public function testHeritageUtilisateur(): void
    {
        $etudiant = new Etudiant();
        $etudiant->setNom('Amivi Kossou');
        $etudiant->setEmail('amivi@uatm.bj');
        $etudiant->setRole('etudiant');

        $this->assertSame('Amivi Kossou', $etudiant->getNom());
        $this->assertSame('amivi@uatm.bj', $etudiant->getEmail());
        $this->assertSame('etudiant',      $etudiant->getRole());
        $this->assertInstanceOf(Etudiant::class, $etudiant);
    }
}