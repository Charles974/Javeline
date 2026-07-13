-- ============================================================
-- Jeu de données de test — Javeline
-- Encodage : UTF-8 (utf8mb4)
--
-- A importer APRES database.sql, sur une base vide (les id sont
-- fixés explicitement pour pouvoir cabler les jointures entre
-- inscriptions / matchs / scores).
--
-- Contenu :
--   - 20 tireurs membres, 12 tireurs externes (dont 6 etrangers)
--   - Challenge 1 "archive" (mars 2026) : inscriptions + plan de tir
--     + scores complets -> permet de tester les classements,
--     y compris un cas d'ex-aequo
--   - Challenge 2 "ouvert" en cours (le challenge actif du jour) :
--     melange d'inscrits avec score saisi, avec match planifie mais
--     score manquant, et inscrits sans plan de tir -> teste le workflow
--     "plan de tir" + "saisie des scores"
--   - Challenge 3 "ouvert" futur : inscriptions seules, aucun plan de
--     tir -> teste le workflow "inscriptions" en amont
--   - Challenge 4 "archive" (decembre 2025) : inscriptions + plan de
--     tir + scores complets, avec des tireurs inscrits sur toutes les
--     disciplines d'un meme combine -> permet de tester le calcul des
--     combines (aggregates), y compris un quasi ex-aequo departage par
--     le nombre de mouflons
-- ============================================================

USE javeline;

-- ------------------------------------------------------------
-- Comptes utilisateurs de test (un par profil)
-- Mots de passe en clair (pour les tests uniquement) :
--   - test.admin       : Admin@123    (administrateur, acces total)
--   - test.tour        : Tour@123     (tour, saisie des scores + planning)
--   - test.utilisateur : User@123     (utilisateur, consultation des resultats)
-- Les hash ci-dessous sont générés avec password_hash() / bcrypt.
-- database.sql cree deja le compte "admin" (mot de passe Javeline!2026).
-- ------------------------------------------------------------
INSERT INTO utilisateurs (identifiant, mot_de_passe, role) VALUES
('test.admin',       '$2y$12$KzarZiKpzlmye5vS4qjL1ee9ffpKpAFNaz5s5Gn5Gu0M1.IjRbrNS', 'administrateur'),
('test.tour',        '$2y$12$nTDBktWG2LGwF9V/zRTrqOdRKlbOvXhFOCjN/Hxb94wUrOHhmgI/C', 'tour'),
('test.utilisateur', '$2y$12$3ivQEebWZcJe.Q/Jr7PDtus1qlpTKUih/1uXRvhrN1m0faQwit592', 'utilisateur');

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
(15, 'Garcia',   'Lucas',     '1996-09-09', 'Mauguio',      'JAV-2022-015', '1 chemin des Cabanes',       NULL,                 '34130', 'Mauguio',     '06 12 34 56 15', 'lucas.garcia@example.com',   0),
(16, 'Fontaine', 'Julie',     '1991-03-15', 'Montpellier',  'JAV-2022-016', '10 rue de la Fontaine',      NULL,                 '34000', 'Montpellier', '06 12 34 56 16', 'julie.fontaine@example.com', 1),
(17, 'Girard',   'Maxime',    '1986-12-01', 'Nimes',        'JAV-2019-017', '7 rue des Marronniers',      NULL,                 '30000', 'Nimes',       '06 12 34 56 17', 'maxime.girard@example.com',  1),
(18, 'Bonnet',   'Sarah',     '1994-07-08', 'Beziers',      'JAV-2023-018', '13 avenue Jean Jaures',      NULL,                 '34500', 'Beziers',     '06 12 34 56 18', 'sarah.bonnet@example.com',   0),
(19, 'Francois', 'Paul',      '1982-02-20', 'Sete',         'JAV-2020-019', '5 quai de Bosc',             NULL,                 '34200', 'Sete',        '06 12 34 56 19', 'paul.francois@example.com',  1),
(20, 'Andre',    'Manon',     '1999-05-05', 'Lattes',       'JAV-2024-020', '18 chemin des Pins',         NULL,                 '34970', 'Lattes',      '06 12 34 56 20', 'manon.andre@example.com',    0);

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
(8, 'Brunet',   'Celine', 'Club de Tir de Nimes',        '06 98 76 54 08', NULL,                          0),
(9, 'Weber',    'Anna',   'Schutzenverein Wien',         '06 98 76 54 09', 'anna.weber@example.at',       1),
(10, 'Costa',   'Miguel', 'Clube de Tiro Porto',         '06 98 76 54 10', 'miguel.costa@example.pt',     1),
(11, 'Dupuis',  'Marc',   'Club de Tir de Perpignan',    '06 98 76 54 11', 'marc.dupuis@example.com',     0),
(12, 'Novak',   'Petra',  'Strelecky Klub Praha',        NULL,             'petra.novak@example.cz',      1);

-- ------------------------------------------------------------
-- Challenges
-- ------------------------------------------------------------
INSERT INTO challenges (id, libelle, date_debut, date_fin, statut) VALUES
(1, 'Challenge de Printemps 2026', '2026-03-14', '2026-03-15', 'archive'),
(2, 'Challenge d''Ete 2026',       '2026-06-27', '2026-07-05', 'ouvert'),
(3, 'Challenge d''Automne 2026',   '2026-09-19', '2026-09-20', 'ouvert'),
(4, 'Challenge d''Hiver 2025',     '2025-12-06', '2025-12-07', 'archive');

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

-- Quelques inscriptions supplementaires au challenge 3, avec les nouveaux
-- tireurs, pour etoffer le jeu de donnees "inscriptions seules"
INSERT INTO inscriptions (id, challenge_id, tireur_type, tireur_id, discipline_id) VALUES
(65, 3, 'membre',  18, (SELECT id FROM disciplines WHERE code = 404)),
(66, 3, 'membre',  19, (SELECT id FROM disciplines WHERE code = 405)),
(67, 3, 'externe', 9,  (SELECT id FROM disciplines WHERE code = 400)),
(68, 3, 'externe', 12, (SELECT id FROM disciplines WHERE code = 410));

-- ------------------------------------------------------------
-- Challenge 4 (archive) — challenge complet supplementaire, pense pour
-- tester les combines (aggregates) : plusieurs tireurs inscrits sur
-- toutes les disciplines d'un meme combine, avec un quasi ex-aequo
-- sur le Combine Gros Calibre (membres 16 et 17, 124 points chacun,
-- departage par le nombre de mouflons).
-- Disciplines : 400-403 (combine Gros Calibre), 407+408+409+403
-- (combine Debout), 410+411 (combine Carabine PC), 412+413
-- (combine Carabine GC), 404, 405, 406 (disciplines seules)
-- ------------------------------------------------------------
INSERT INTO inscriptions (id, challenge_id, tireur_type, tireur_id, discipline_id) VALUES
(45, 4, 'membre',  16, (SELECT id FROM disciplines WHERE code = 400)),
(46, 4, 'membre',  16, (SELECT id FROM disciplines WHERE code = 401)),
(47, 4, 'membre',  16, (SELECT id FROM disciplines WHERE code = 402)),
(48, 4, 'membre',  16, (SELECT id FROM disciplines WHERE code = 403)),
(49, 4, 'membre',  17, (SELECT id FROM disciplines WHERE code = 400)),
(50, 4, 'membre',  17, (SELECT id FROM disciplines WHERE code = 401)),
(51, 4, 'membre',  17, (SELECT id FROM disciplines WHERE code = 402)),
(52, 4, 'membre',  17, (SELECT id FROM disciplines WHERE code = 403)),
(53, 4, 'externe', 9,  (SELECT id FROM disciplines WHERE code = 403)),
(54, 4, 'membre',  18, (SELECT id FROM disciplines WHERE code = 407)),
(55, 4, 'membre',  18, (SELECT id FROM disciplines WHERE code = 408)),
(56, 4, 'membre',  18, (SELECT id FROM disciplines WHERE code = 409)),
(57, 4, 'membre',  18, (SELECT id FROM disciplines WHERE code = 403)),
(58, 4, 'externe', 10, (SELECT id FROM disciplines WHERE code = 404)),
(59, 4, 'externe', 11, (SELECT id FROM disciplines WHERE code = 405)),
(60, 4, 'membre',  19, (SELECT id FROM disciplines WHERE code = 410)),
(61, 4, 'membre',  19, (SELECT id FROM disciplines WHERE code = 411)),
(62, 4, 'membre',  20, (SELECT id FROM disciplines WHERE code = 412)),
(63, 4, 'membre',  20, (SELECT id FROM disciplines WHERE code = 413)),
(64, 4, 'externe', 12, (SELECT id FROM disciplines WHERE code = 406));

-- Plan de tir complet du challenge 4 (2 jours)
INSERT INTO matchs (id, inscription_id, date_match, heure_debut, heure_fin) VALUES
(30, 45, '2025-12-06', '08:00:00', '09:00:00'),
(31, 46, '2025-12-06', '09:00:00', '10:00:00'),
(32, 47, '2025-12-06', '10:00:00', '11:00:00'),
(33, 48, '2025-12-06', '11:00:00', '12:00:00'),
(34, 49, '2025-12-06', '13:00:00', '14:00:00'),
(35, 50, '2025-12-06', '14:00:00', '15:00:00'),
(36, 51, '2025-12-06', '15:00:00', '16:00:00'),
(37, 52, '2025-12-06', '16:00:00', '17:00:00'),
(38, 53, '2025-12-06', '17:00:00', '18:00:00'),
(39, 54, '2025-12-06', '18:00:00', '19:00:00'),
(40, 55, '2025-12-07', '08:00:00', '09:00:00'),
(41, 56, '2025-12-07', '09:00:00', '10:00:00'),
(42, 57, '2025-12-07', '10:00:00', '11:00:00'),
(43, 58, '2025-12-07', '11:00:00', '12:00:00'),
(44, 59, '2025-12-07', '13:00:00', '14:00:00'),
(45, 60, '2025-12-07', '14:00:00', '15:00:00'),
(46, 61, '2025-12-07', '15:00:00', '16:00:00'),
(47, 62, '2025-12-07', '16:00:00', '17:00:00'),
(48, 63, '2025-12-07', '17:00:00', '18:00:00'),
(49, 64, '2025-12-07', '18:00:00', '19:00:00');

-- Scores complets du challenge 4
INSERT INTO scores (match_id, poulets, cochons, dindons, mouflons) VALUES
(30, 9,  8,  8,  9),  -- membre 16 (disc 400) : total 34
(31, 8,  8,  8,  8),  -- membre 16 (disc 401) : total 32
(32, 7,  8,  7,  8),  -- membre 16 (disc 402) : total 30
(33, 7,  7,  7,  7),  -- membre 16 (disc 403) : total 28 -> combine GC membre 16 = 124, mouflons = 32
(34, 8,  8,  8,  10), -- membre 17 (disc 400) : total 34
(35, 9,  8,  7,  8),  -- membre 17 (disc 401) : total 32
(36, 7,  7,  8,  8),  -- membre 17 (disc 402) : total 30
(37, 7,  7,  7,  7),  -- membre 17 (disc 403) : total 28 -> combine GC membre 17 = 124, mouflons = 33 (departage)
(38, 8,  7,  8,  7),  -- externe 9 (disc 403) : total 30
(39, 9,  9,  8,  9),  -- membre 18 (disc 407) : total 35
(40, 8,  8,  7,  8),  -- membre 18 (disc 408) : total 31
(41, 7,  8,  8,  7),  -- membre 18 (disc 409) : total 30
(42, 8,  7,  7,  8),  -- membre 18 (disc 403) : total 30 -> combine Debout membre 18 = 126
(43, 9,  8,  9,  8),  -- externe 10 (disc 404) : total 34
(44, 7,  7,  6,  7),  -- externe 11 (disc 405) : total 27
(45, 9,  9,  9,  10), -- membre 19 (disc 410) : total 37
(46, 8,  8,  8,  9),  -- membre 19 (disc 411) : total 33 -> combine Carabine PC membre 19 = 70
(47, 10, 9,  9,  10), -- membre 20 (disc 412) : total 38
(48, 9,  9,  8,  9),  -- membre 20 (disc 413) : total 35 -> combine Carabine GC membre 20 = 73
(49, 6,  7,  6,  7);  -- externe 12 (disc 406) : total 26
