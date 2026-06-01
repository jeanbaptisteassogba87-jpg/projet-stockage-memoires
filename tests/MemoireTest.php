<?php

// Rôle : teste les getters/setters et la logique du modèle Memoire
//        statuts, types de diplôme, valeurs par défaut


use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Memoire.php';
require_once __DIR__ . '/../config/constants.php';

class MemoireTest extends TestCase
{
    private Memoire $memoire;

    protected function setUp(): void
    {
        $this->memoire = new Memoire();
    }

    // ── Getters / Setters de base ────────────────────────────

    /**
     * Vérifie que le titre se stocke et se relit correctement
     */
    public function testTitre(): void
    {
        $this->memoire->setTitre('Mise en place d\'une infrastructure réseau sécurisée');
        $this->assertSame(
            'Mise en place d\'une infrastructure réseau sécurisée',
            $this->memoire->getTitre()
        );
    }

    /**
     * Vérifie que le thème se stocke correctement
     */
    public function testTheme(): void
    {
        $this->memoire->setTheme('Sécurité informatique');
        $this->assertSame('Sécurité informatique', $this->memoire->getTheme());
    }

    /**
     * Vérifie le nom du fichier PDF
     */
    public function testFichierPdf(): void
    {
        $this->memoire->setFichierPdf('mem_abc123.pdf');
        $this->assertSame('mem_abc123.pdf', $this->memoire->getFichierPdf());
    }

    /**
     * Vérifie que l'année de soutenance est bien un entier
     */
    public function testAnneeSoutenance(): void
    {
        $this->memoire->setAnneeSoutenance(2024);
        $this->assertSame(2024, $this->memoire->getAnneeSoutenance());
    }

    /**
     * Vérifie les remarques
     */
    public function testRemarques(): void
    {
        $remarque = 'La bibliographie est incomplète. Veuillez corriger.';
        $this->memoire->setRemarques($remarque);
        $this->assertSame($remarque, $this->memoire->getRemarques());
    }

    // ── Statuts ──────────────────────────────────────────────

    /**
     * Vérifie que les 6 statuts possibles sont acceptés
     * et correspondent aux constantes définies dans constants.php
     */
    public function testStatutsValides(): void
    {
        $statuts = [
            STATUT_EN_ATTENTE,
            STATUT_EN_VERIFICATION,
            STATUT_VALIDE,
            STATUT_REJETE,
            STATUT_PUBLIE,
            STATUT_NON_PUBLIC,
        ];

        foreach ($statuts as $statut) {
            $this->memoire->setStatut($statut);
            $this->assertSame(
                $statut,
                $this->memoire->getStatut(),
                "Le statut '$statut' devrait être accepté"
            );
        }
    }

    /**
     * Un mémoire nouvellement créé doit être en_attente
     * On vérifie que la constante STATUT_EN_ATTENTE vaut bien 'en_attente'
     */
    public function testStatutEnAttenteParDefaut(): void
    {
        $this->memoire->setStatut(STATUT_EN_ATTENTE);
        $this->assertSame('en_attente', $this->memoire->getStatut());
    }

    // ── Types de diplôme ─────────────────────────────────────

    /**
     * Vérifie que les deux types de diplôme sont acceptés
     */
    public function testTypesDiplome(): void
    {
        $this->memoire->setTypeDiplome(DIPLOME_LICENCE);
        $this->assertSame('licence', $this->memoire->getTypeDiplome());

        $this->memoire->setTypeDiplome(DIPLOME_MASTER);
        $this->assertSame('master', $this->memoire->getTypeDiplome());
    }

    // ── Relations ────────────────────────────────────────────

    /**
     * Vérifie l'id de l'étudiant propriétaire
     */
    public function testEtudiantId(): void
    {
        $this->memoire->setEtudiantId(5);
        $this->assertSame(5, $this->memoire->getEtudiantId());
    }

    /**
     * Vérifie l'id du professeur assigné
     */
    public function testProfesseurId(): void
    {
        $this->memoire->setProfesseurId(3);
        $this->assertSame(3, $this->memoire->getProfesseurId());
    }

    // ── Méthodes métier ──────────────────────────────────────

    /**
     * deposer() doit retourner true
     */
    public function testDeposer(): void
    {
        $this->assertTrue($this->memoire->deposer());
    }

    /**
     * modifier() doit retourner true
     */
    public function testModifier(): void
    {
        $this->assertTrue($this->memoire->modifier());
    }

    // ── Flux de statuts ──────────────────────────────────────

    /**
     * Simule le flux complet d'un mémoire :
     * en_attente → en_verification → valide → publie
     * Vérifie chaque transition de statut
     */
    public function testFluxCompletStatuts(): void
    {
        // Dépôt initial
        $this->memoire->setStatut(STATUT_EN_ATTENTE);
        $this->assertSame(STATUT_EN_ATTENTE, $this->memoire->getStatut());

        // Professeur prend en charge
        $this->memoire->setStatut(STATUT_EN_VERIFICATION);
        $this->assertSame(STATUT_EN_VERIFICATION, $this->memoire->getStatut());

        // Professeur valide
        $this->memoire->setStatut(STATUT_VALIDE);
        $this->assertSame(STATUT_VALIDE, $this->memoire->getStatut());

        // Directeur publie
        $this->memoire->setStatut(STATUT_PUBLIE);
        $this->assertSame(STATUT_PUBLIE, $this->memoire->getStatut());
    }

    /**
     * Simule le flux d'un mémoire rejeté puis corrigé
     * en_attente → en_verification → rejete → en_attente
     */
    public function testFluxRejetCorrection(): void
    {
        $this->memoire->setStatut(STATUT_EN_ATTENTE);
        $this->memoire->setStatut(STATUT_EN_VERIFICATION);
        $this->memoire->setStatut(STATUT_REJETE);
        $this->assertSame(STATUT_REJETE, $this->memoire->getStatut());

        // Étudiant corrige et resoumet
        $this->memoire->setStatut(STATUT_EN_ATTENTE);
        $this->assertSame(STATUT_EN_ATTENTE, $this->memoire->getStatut());
    }
}