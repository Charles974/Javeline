-- ============================================================
-- Jeu de données de test — Javeline
-- Encodage : UTF-8 (utf8mb4)
--
-- A importer APRES database.sql, sur une base vide (les id sont
-- fixés explicitement pour pouvoir cabler les jointures entre
-- inscriptions / matchs / scores).
--
-- Contenu :
--   - 15 tireurs membres, 8 tireurs externes (dont 4 etrangers)
--   - Challenge 1 "archive" (mars 2026) : inscriptions + plan de tir
--     + scores complets -> permet de tester les classements,
--     y compris un cas d'ex-aequo
--   - Challenge 2 "ouvert" en cours (le challenge actif du jour) :
--     melange d'inscrits avec score saisi, avec match planifie mais
--     score manquant, et inscrits sans plan de tir -> teste le workflow
--     "plan de tir" + "saisie des scores"
--   - Challenge 3 "ouvert" futur : inscriptions seules, aucun plan de
--     tir -> teste le workflow "inscriptions" en amont
-- ============================================================

USE javeline;

-- ------------------------------------------------------------
-- Membres
-- ------------------------------------------------------------
INSERT INTO membres (id, nom, prenom, date_naissance, lieu_naissance, numero_licence, adresse1, adresse2, code_postal, ville, telephone, email, certificat_medical) VALUES
(1,  'Dubois',   'Jean',      '1978-04-12', 'Montpellier',  'JAV-2019-001', '12 rue des Oliviers',        NULL,                 '34000', 'Montpellier', '06 12 34 56 01', 'jean.dubois@example.com',    1),
(2,  'Martin',   'Sophie',    '1985-09-03', 'Nimes',        'JAV-2019-002', '4 avenue de la Republique',  NULL,                 '30000', 'Nimes',       '06 12 34 56 02', 'sophie.martin@example.com',  1),
(3,  'Bernard',  'Pierre',    '1970-01-25', 'Beziers',      'JAV-2020-003', '8 impasse des Vignes',       'Batiment B',         '34500', 'Beziers',     '06 12 34 56 03', 'pierre.bernard@example.com', 0),
(4,  'Thomas',   'Marie',     '1992-11-17', 'Sete',         'JAV-2020-004', '15 quai de la Marine',       NULL,                 '34200', 'Sete',        '06 12 34 56 04', 'marie.thomas@example.com',   1),
(5,  'Robert',   'Nicolas',   '1988-06-30', 'Montpellier',  'JAV-2021-005', '3 rue du Faubourg',          NULL,                 '34000', 'Montpellier', '06 12 34 56 05', 'nicolas.robert@example.com', 1),
(6,  'Richard',  'Claire',    '1995-02-08', 'Lattes',       'JAV-2021-006', '22 chemin des Amandiers',    NULL,                 '34970', 'Lattes',      '06 12 34 56 06', 'claire.richard@example.com', 0),
(7,  'Petit',    'Julien',    '1980-07-22', 'Ales',         'JAV-2018-007', '9 rue Victor Hugo',          'Appartement 3',      '30100', 'Ales',        '06 12 34 56 07', 'julien.petit@example.com',   1),
(8,  'Durand',   'Camille',   '1998-03-14', 'Montpellier',  'JAV-2022-008', '17 avenue Foch',             NULL,                 '34000', 'Montpellier', '06 12 34 56 08', 'camille.durand@example.com', 1),
(9,  'Leroy',    'Antoine',   '1975-12-05', 'Frontignan',   'JAV-2017-009', '5 rue des Salins',           NULL,                 '34110', 'Frontignan',  '06 12 34 56 09', 'antoine.leroy@example.com',  0),
(10, 'Moreau',   'Laura',     '1990-08-19', 'Montpellier',  'JAV-2019-010', '11 rue de la Loge',          NULL,                 '34000', 'Montpellier', '06 12 34 56 10', 'laura.moreau@example.com',   1),
(11, 'Simon',    'Thomas',    '1983-05-27', 'Castelnau-le-Lez', 'JAV-2020-011', '6 avenue de Nimes',      NULL,                 '34170', 'Castelnau-le-Lez', '06 12 34 56 11', 'thomas.simon@example.com', 1),
(12, 'Laurent',  'Emma',      '2000-10-02', 'Montpellier',  'JAV-2023-012', '2 rue Saint-Guilhem',        NULL,                 '34000', 'Montpellier', '06 12 34 56 12', 'emma.laurent@example.com',   0),
(13, 'Lefebvre', 'Hugo',      '1993-01-11', 'Palavas-les-Flots', 'JAV-2021-013', '14 rue du Levant',      NULL,                 '34250', 'Palavas-les-Flots', '06 12 34 56 13', 'hugo.lefebvre@example.com', 1),
(14, 'Michel',   'Chloe',     '1987-04-29', 'Montpellier',  'JAV-2018-014', '20 rue Nationale',           NULL,                 '34000', 'Montpellier', '06 12 34 56 14', 'chloe.michel@example.com',   1),
(15, 'Garcia',   'Lucas',     '1996-09-09', 'Mauguio',      'JAV-2022-015', '1 chemin des Cabanes',       NULL,                 '34130', 'Mauguio',     '06 12 34 56 15', 'lucas.garcia@example.com',   0);

-- ------------------------------------------------------------
-- Externes (non-membres)
-- ------------------------------------------------------------
INSERT INTO externes (id, nom, prenom, club, telephone, email, etranger) VALUES
(1, 'Sanchez',  'Carlos', 'Club de Tir Toulousain',      '06 98 76 54 01', 'carlos.sanchez@example.com', 0),
(2, 'Rossi',    'Marco',  'Tiro a Segno Roma',           '06 98 76 54 02', 'marco.rossi@example.it',     1),
(3, 'Smith',    'John',   'London Rifle Club',           NULL,             NULL,                          1),
(4, 'Silva',    'Ana',    'Clube de Tiro Lisboa',        '06 98 76 54 04', 'ana.silva@example.pt',       1),
(5, 'Ferrand',  'Julie',  'Club de Tir de Toulouse',     '06 98 76 54 05', 'julie.ferrand@example.com',  0),
(6, 'Gauthier', 'Marc',   'Club de Tir de Bordeaux',     NULL,             'marc.gauthier@example.com',  0),
(7, 'Meyer',    'Klaus',  'Munchener Schutzenverein',    '06 98 76 54 07', 'klaus.meyer@example.de',     1),
(8, 'Brunet',   'Celine', 'Club de Tir de Nimes',        '06 98 76 54 08', NULL,                          0);

-- ------------------------------------------------------------
-- Challenges
-- ------------------------------------------------------------
INSERT INTO challenges (id, libelle, date_debut, date_fin, statut) VALUES
(1, 'Challenge de Printemps 2026', '2026-03-14', '2026-03-15', 'archive'),
(2, 'Challenge d''Ete 2026',       '2026-06-27', '2026-07-05', 'ouvert'),
(3, 'Challenge d''Automne 2026',   '2026-09-19', '2026-09-20', 'ouvert');

-- ------------------------------------------------------------
-- Challenge 1 (archive) — inscriptions
-- Discipline 400 = Gros Calibre Revolver, 404 = Petit Calibre Revolver,
-- 410 = Carabine Petit Calibre Legere
-- ------------------------------------------------------------
INSERT INTO inscriptions (id, challenge_id, tireur_type, tireur_id, discipline_id) VALUES
(1,  1, 'membre',  1,  (SELECT id FROM disciplines WHERE code = 400)),
(2,  1, 'membre',  2,  (SELECT id FROM disciplines WHERE code = 400)),
(3,  1, 'membre',  3,  (SELECT id FROM disciplines WHERE code = 400)),
(4,  1, 'membre',  4,  (SELECT id FROM disciplines WHERE code = 400)),
(5,  1, 'externe', 1,  (SELECT id FROM disciplines WHERE code = 400)),
(6,  1, 'externe', 2,  (SELECT id FROM disciplines WHERE code = 400)),
(7,  1, 'membre',  5,  (SELECT id FROM disciplines WHERE code = 404)),
(8,  1, 'membre',  6,  (SELECT id FROM disciplines WHERE code = 404)),
(9,  1, 'membre',  7,  (SELECT id FROM disciplines WHERE code = 404)),
(10, 1, 'externe', 3,  (SELECT id FROM disciplines WHERE code = 404)),
(11, 1, 'membre',  8,  (SELECT id FROM disciplines WHERE code = 410)),
(12, 1, 'membre',  9,  (SELECT id FROM disciplines WHERE code = 410)),
(13, 1, 'membre',  10, (SELECT id FROM disciplines WHERE code = 410)),
(14, 1, 'externe', 4,  (SELECT id FROM disciplines WHERE code = 410));

-- Plan de tir du challenge 1 (deja tire, en 2 jours)
INSERT INTO matchs (id, inscription_id, date_match, heure_debut, heure_fin) VALUES
(1,  1,  '2026-03-14', '08:00:00', '09:00:00'),
(2,  2,  '2026-03-14', '09:00:00', '10:00:00'),
(3,  3,  '2026-03-14', '10:00:00', '11:00:00'),
(4,  4,  '2026-03-14', '11:00:00', '12:00:00'),
(5,  5,  '2026-03-14', '13:00:00', '14:00:00'),
(6,  6,  '2026-03-14', '14:00:00', '15:00:00'),
(7,  7,  '2026-03-14', '15:00:00', '16:00:00'),
(8,  8,  '2026-03-15', '08:00:00', '09:00:00'),
(9,  9,  '2026-03-15', '09:00:00', '10:00:00'),
(10, 10, '2026-03-15', '10:00:00', '11:00:00'),
(11, 11, '2026-03-15', '11:00:00', '12:00:00'),
(12, 12, '2026-03-15', '13:00:00', '14:00:00'),
(13, 13, '2026-03-15', '14:00:00', '15:00:00'),
(14, 14, '2026-03-15', '15:00:00', '16:00:00');

-- Scores du challenge 1 (dont un cas d'ex-aequo sur la discipline 400 : ids 1 et 2)
INSERT INTO scores (match_id, poulets, cochons, dindons, mouflons) VALUES
(1,  10, 9,  8,  10), -- membre 1 (disc 400) : total 37
(2,  10, 9,  8,  10), -- membre 2 (disc 400) : total 37, ex-aequo avec membre 1
(3,  9,  8,  7,  9),  -- membre 3 (disc 400) : total 33
(4,  8,  7,  6,  8),  -- membre 4 (disc 400) : total 29
(5,  10, 10, 9,  9),  -- externe 1 (disc 400) : total 38, 1er
(6,  7,  6,  5,  7),  -- externe 2 (disc 400) : total 25
(7,  9,  9,  9,  9),  -- membre 5 (disc 404) : total 36
(8,  8,  8,  8,  8),  -- membre 6 (disc 404) : total 32
(9,  10, 10, 10, 10), -- membre 7 (disc 404) : total 40, 1er
(10, 7,  7,  7,  7),  -- externe 3 (disc 404) : total 28
(11, 10, 8,  9,  7),  -- membre 8 (disc 410) : total 34
(12, 9,  9,  9,  9),  -- membre 9 (disc 410) : total 36, 1er
(13, 8,  7,  6,  9),  -- membre 10 (disc 410) : total 30
(14, 6,  6,  6,  6);  -- externe 4 (disc 410) : total 24

-- ------------------------------------------------------------
-- Challenge 2 (ouvert, en cours) — inscriptions
-- Melange volontaire de 3 etats : score saisi / match planifie sans
-- score / inscrit sans plan de tir, pour tester tout le workflow.
-- Disciplines : 401 Gros Calibre Production, 405 Petit Calibre Production,
-- 412 Carabine GC Hunting, 408 Field Visee Ouverte, 409 Field Optique,
-- 413 Carabine GC Silhouette, 400 Gros Calibre Revolver,
-- 404 Petit Calibre Revolver, 410 Carabine PC Legere
-- ------------------------------------------------------------
INSERT INTO inscriptions (id, challenge_id, tireur_type, tireur_id, discipline_id) VALUES
(15, 2, 'membre',  1,  (SELECT id FROM disciplines WHERE code = 401)),
(16, 2, 'membre',  2,  (SELECT id FROM disciplines WHERE code = 401)),
(17, 2, 'membre',  3,  (SELECT id FROM disciplines WHERE code = 401)),
(18, 2, 'membre',  11, (SELECT id FROM disciplines WHERE code = 405)),
(19, 2, 'membre',  12, (SELECT id FROM disciplines WHERE code = 405)),
(20, 2, 'membre',  13, (SELECT id FROM disciplines WHERE code = 405)), -- match planifie, score non saisi
(21, 2, 'externe', 1,  (SELECT id FROM disciplines WHERE code = 401)),
(22, 2, 'externe', 5,  (SELECT id FROM disciplines WHERE code = 405)),
(23, 2, 'membre',  14, (SELECT id FROM disciplines WHERE code = 412)),
(24, 2, 'membre',  15, (SELECT id FROM disciplines WHERE code = 412)), -- match planifie, score non saisi
(25, 2, 'externe', 6,  (SELECT id FROM disciplines WHERE code = 412)),
(26, 2, 'membre',  4,  (SELECT id FROM disciplines WHERE code = 408)),
(27, 2, 'membre',  5,  (SELECT id FROM disciplines WHERE code = 408)),
(28, 2, 'externe', 2,  (SELECT id FROM disciplines WHERE code = 409)), -- tireur etranger, feuille en anglais
(29, 2, 'membre',  6,  (SELECT id FROM disciplines WHERE code = 409)),
(30, 2, 'membre',  7,  (SELECT id FROM disciplines WHERE code = 413)), -- pas encore de plan de tir
(31, 2, 'membre',  8,  (SELECT id FROM disciplines WHERE code = 413)), -- pas encore de plan de tir
(32, 2, 'externe', 3,  (SELECT id FROM disciplines WHERE code = 400)), -- pas encore de plan de tir
(33, 2, 'membre',  9,  (SELECT id FROM disciplines WHERE code = 404)), -- pas encore de plan de tir
(34, 2, 'membre',  10, (SELECT id FROM disciplines WHERE code = 410)); -- pas encore de plan de tir

-- Plan de tir partiel du challenge 2 (inscriptions 15 a 29 uniquement)
INSERT INTO matchs (id, inscription_id, date_match, heure_debut, heure_fin) VALUES
(15, 15, '2026-07-03', '08:00:00', '09:00:00'),
(16, 16, '2026-07-03', '09:00:00', '10:00:00'),
(17, 17, '2026-07-03', '10:00:00', '11:00:00'),
(18, 18, '2026-07-03', '11:00:00', '12:00:00'),
(19, 19, '2026-07-03', '13:00:00', '14:00:00'),
(20, 20, '2026-07-03', '14:00:00', '15:00:00'),
(21, 21, '2026-07-03', '15:00:00', '16:00:00'),
(22, 22, '2026-07-03', '16:00:00', '17:00:00'),
(23, 23, '2026-07-04', '08:00:00', '09:00:00'),
(24, 24, '2026-07-04', '09:00:00', '10:00:00'),
(25, 25, '2026-07-04', '10:00:00', '11:00:00'),
(26, 26, '2026-07-04', '11:00:00', '12:00:00'),
(27, 27, '2026-07-04', '13:00:00', '14:00:00'),
(28, 28, '2026-07-04', '14:00:00', '15:00:00'),
(29, 29, '2026-07-04', '15:00:00', '16:00:00');

-- Scores du challenge 2 (manquants pour les matchs 20 et 24 -> saisie a faire)
INSERT INTO scores (match_id, poulets, cochons, dindons, mouflons) VALUES
(15, 9,  9,  9,  9),
(16, 9,  9,  9,  9),  -- ex-aequo avec le match 15
(17, 10, 9,  8,  10),
(18, 8,  8,  7,  8),
(19, 9,  8,  8,  9),
(21, 10, 10, 9,  9),
(22, 7,  7,  6,  7),
(23, 8,  9,  8,  7),
(25, 9,  8,  7,  8),
(26, 7,  8,  8,  7),
(27, 8,  7,  7,  8),
(28, 9,  9,  8,  9),
(29, 6,  7,  7,  6);

-- ------------------------------------------------------------
-- Challenge 3 (ouvert, futur) — inscriptions seules, aucun plan de
-- tir : permet de tester l'ecran d'inscriptions puis la generation
-- du plan de tir a partir de zero.
-- ------------------------------------------------------------
INSERT INTO inscriptions (id, challenge_id, tireur_type, tireur_id, discipline_id) VALUES
(35, 3, 'membre',  1,  (SELECT id FROM disciplines WHERE code = 400)),
(36, 3, 'membre',  2,  (SELECT id FROM disciplines WHERE code = 404)),
(37, 3, 'membre',  3,  (SELECT id FROM disciplines WHERE code = 410)),
(38, 3, 'membre',  11, (SELECT id FROM disciplines WHERE code = 401)),
(39, 3, 'membre',  12, (SELECT id FROM disciplines WHERE code = 405)),
(40, 3, 'externe', 1,  (SELECT id FROM disciplines WHERE code = 400)),
(41, 3, 'externe', 4,  (SELECT id FROM disciplines WHERE code = 410)),
(42, 3, 'externe', 7,  (SELECT id FROM disciplines WHERE code = 412)),
(43, 3, 'membre',  13, (SELECT id FROM disciplines WHERE code = 408)),
(44, 3, 'membre',  14, (SELECT id FROM disciplines WHERE code = 409));
