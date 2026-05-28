<?php
    require_once __DIR__ . '/Utilisateur.php';
    require_once __DIR__ . '/../config/constants.php';

    class Professeur extends Utilisateur{
        private string $grade ;
        private string $specialite ;

        // Getters

        public function getGrade():string {
            return $this->grade ;
        } 
        public function getSpecialite():string {
            return $this->specialite ;
        }

        //Setters
        public function setGrade(string $grade):void {
            $this->grade = $grade ;
        }
        public function setSpecialite(string $specialite): void {
            $this->specialite = $specialite ;
        }

        //Méthodes métier 
        public function verifierMemoire(): bool {
            return true;
        }

        public function faireRemarques(): bool {
            return true;
        }

        public function validerMemoire(): bool {
            return true;
        }

        public function rejeterMemoire(): bool {
            return true;
        }
    }
?>