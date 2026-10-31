-- À exécuter une seule fois sur une base existante.
ALTER TABLE memoire
    ADD COLUMN note_jury DECIMAL(4,2) NULL AFTER remarques;
