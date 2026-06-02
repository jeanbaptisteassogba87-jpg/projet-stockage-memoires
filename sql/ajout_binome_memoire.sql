ALTER TABLE memoire
    ADD COLUMN etudiant2_id INT NULL AFTER professeur_id,
    ADD CONSTRAINT fk_memoire_etudiant2
        FOREIGN KEY (etudiant2_id) REFERENCES etudiant(utilisateur_id),
    ADD UNIQUE KEY uq_binome_diplome (etudiant2_id, type_diplome);
