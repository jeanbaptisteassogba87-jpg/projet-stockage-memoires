<?php
// Statuts possibles d'un mémoire (NE PAS MODIFIER sans accord du groupe)
define('STATUT_EN_ATTENTE',     'en_attente');
define('STATUT_EN_VERIFICATION','en_verification');
define('STATUT_VALIDE',         'valide');
define('STATUT_REJETE',         'rejete');
define('STATUT_PUBLIE',         'publie');
define('STATUT_NON_PUBLIC',     'non_public');

// Rôles utilisateurs
define('ROLE_ETUDIANT',   'etudiant');
define('ROLE_PROFESSEUR', 'professeur');
define('ROLE_DIRECTEUR',  'directeur');
define('ROLE_TECHNICIEN', 'technicien');

// Types de diplôme
define('DIPLOME_LICENCE', 'licence');
define('DIPLOME_MASTER',  'master');

// Niveaux pouvant déposer
define('NIVEAUX_DEPOT', ['L3', 'M2']);

// Taille max PDF (10 Mo)
define('MAX_PDF_SIZE', 10 * 1024 * 1024);

// Chemin stockage PDF (HORS webroot)
define('PDF_STORAGE_PATH', __DIR__ . '/../uploads/memoires/');
