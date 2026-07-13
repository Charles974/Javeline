-- ------------------------------------------------------------
-- Migration : suppression du plafond de 10 sur les scores
-- À exécuter sur une base existante déjà créée avec l'ancienne
-- version de database.sql (contraintes CHECK 0-10 sur scores).
-- Les nouvelles installations utilisent directement database.sql.
-- ------------------------------------------------------------

-- MySQL nomme automatiquement les contraintes CHECK inline
-- scores_chk_1 à scores_chk_4 (dans l'ordre des colonnes).
-- En cas d'erreur "constraint does not exist", vérifier les noms :
--   SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
--   WHERE TABLE_NAME = 'scores' AND CONSTRAINT_TYPE = 'CHECK';
ALTER TABLE scores DROP CHECK scores_chk_1;
ALTER TABLE scores DROP CHECK scores_chk_2;
ALTER TABLE scores DROP CHECK scores_chk_3;
ALTER TABLE scores DROP CHECK scores_chk_4;

-- Élargit les colonnes pour accepter des valeurs supérieures à 10.
ALTER TABLE scores
    MODIFY poulets  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    MODIFY cochons  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    MODIFY dindons  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    MODIFY mouflons SMALLINT UNSIGNED NOT NULL DEFAULT 0;
