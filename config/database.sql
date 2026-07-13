-- ============================================================
-- Schéma de base de données — Javeline
-- Encodage : UTF-8 (utf8mb4)
-- ============================================================

CREATE DATABASE IF NOT EXISTS javeline
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE javeline;

-- ------------------------------------------------------------
-- Comptes utilisateurs (authentification)
-- Rien n'est accessible sur le site sans être connecté.
-- Trois profils :
--   - administrateur : accès total + gestion des comptes
--   - tour           : saisie des scores + consultation du planning
--   - utilisateur    : consultation des résultats des challenges
-- Les mots de passe sont stockés hashés (password_hash / bcrypt),
-- jamais en clair.
-- ------------------------------------------------------------
CREATE TABLE utilisateurs (
    id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    identifiant     VARCHAR(50)     NOT NULL UNIQUE,
    mot_de_passe    VARCHAR(255)    NOT NULL,
    role            ENUM('administrateur', 'tour', 'utilisateur') NOT NULL DEFAULT 'utilisateur',
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Compte administrateur initial (sinon impossible de se connecter
-- sur une base neuve). Mot de passe : Javeline!2026
-- ⚠ À CHANGER IMMÉDIATEMENT après la première connexion.
INSERT INTO utilisateurs (identifiant, mot_de_passe, role) VALUES
    ('admin', '$2y$12$Kj1uxlJBkCTc0z2C.JKAq.EDMy.Bp5gFvnJgAtKOx8SZ4tgqi7Wle', 'administrateur');

-- ------------------------------------------------------------
-- Tireurs membres du club
-- ------------------------------------------------------------
CREATE TABLE membres (
    id                  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    nom                 VARCHAR(100)    NOT NULL,
    prenom              VARCHAR(100)    NOT NULL,
    date_naissance      DATE            NOT NULL,
    lieu_naissance      VARCHAR(150)    NOT NULL,
    numero_licence      VARCHAR(50)     NOT NULL UNIQUE,
    adresse1            VARCHAR(200)    NOT NULL,
    adresse2            VARCHAR(200)        NULL DEFAULT NULL,
    code_postal         CHAR(5)         NOT NULL,
    ville               VARCHAR(100)    NOT NULL,
    telephone           VARCHAR(15)     NOT NULL,
    email               VARCHAR(150)    NOT NULL,
    certificat_medical  TINYINT(1)      NOT NULL DEFAULT 0,
    coach               VARCHAR(150)        NULL DEFAULT NULL,
    created_at          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tireurs externes (non-membres)
-- ------------------------------------------------------------
CREATE TABLE externes (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    nom         VARCHAR(100)    NOT NULL,
    prenom      VARCHAR(100)    NOT NULL,
    club        VARCHAR(150)    NOT NULL,
    telephone   VARCHAR(20)         NULL DEFAULT NULL,
    email       VARCHAR(150)        NULL DEFAULT NULL,
    etranger    TINYINT(1)      NOT NULL DEFAULT 0,
    coach       VARCHAR(150)        NULL DEFAULT NULL,
    created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Challenges (épreuves)
-- ------------------------------------------------------------
CREATE TABLE challenges (
    id          INT UNSIGNED                    NOT NULL AUTO_INCREMENT,
    libelle     VARCHAR(200)                    NOT NULL,
    date_debut  DATE                            NOT NULL,
    date_fin    DATE                            NOT NULL,
    statut      ENUM('ouvert', 'archive')       NOT NULL DEFAULT 'ouvert',
    created_at  DATETIME                        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT chk_dates CHECK (date_fin >= date_debut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Disciplines (table de référence — données fixes)
-- ------------------------------------------------------------
CREATE TABLE disciplines (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    code        SMALLINT        NOT NULL UNIQUE,
    libelle_fr  VARCHAR(100)    NOT NULL,
    libelle_en  VARCHAR(100)    NOT NULL,
    qualif_f1   SMALLINT        NOT NULL DEFAULT 0,
    qualif_f2   SMALLINT        NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO disciplines (code, libelle_fr, libelle_en, qualif_f1, qualif_f2) VALUES
    (400, 'Gros Calibre Revolver',             'Big Bore Revolver',           28, 26),
    (401, 'Gros Calibre Production',           'Big Bore Production',         30, 28),
    (402, 'Gros Calibre Unlimited',            'Big Bore Unlimited',          34, 32),
    (403, 'Gros Calibre Debout',               'Big Bore Standing',           18, 16),
    (404, 'Petit Calibre Revolver',            'Small Bore Revolver',         30, 28),
    (405, 'Petit Calibre Production',          'Small Bore Production',       32, 30),
    (406, 'Petit Calibre Unlimited',           'Small Bore Unlimited',        36, 34),
    (407, 'Petit Calibre Debout',              'Small Bore Standing',         20, 18),
    (408, 'Field Visée Ouverte',               'Field Pistol Any Sight',      22, 20),
    (409, 'Field Optique',                     'Field Pistol Production',     24, 22),
    (410, 'Carabine Petit Calibre Légère',     'Small Bore Light Rifle',      14, 10),
    (411, 'Carabine Petit Calibre Silhouette', 'Small Bore Silhouette Rifle', 16, 12),
    (412, 'Carabine Gros Calibre Hunting',     'Big Bore Hunting Rifle',      14, 11),
    (413, 'Carabine Gros Calibre Silhouette',  'Big Bore Silhouette Rifle',   14, 11);

-- ------------------------------------------------------------
-- Inscriptions : un tireur s'inscrit à une discipline d'un challenge
-- Un tireur peut avoir plusieurs lignes (une par discipline choisie)
-- ------------------------------------------------------------
CREATE TABLE inscriptions (
    id              INT UNSIGNED                    NOT NULL AUTO_INCREMENT,
    challenge_id    INT UNSIGNED                    NOT NULL,
    tireur_type     ENUM('membre', 'externe')       NOT NULL,
    tireur_id       INT UNSIGNED                    NOT NULL,
    discipline_id   INT UNSIGNED                    NOT NULL,
    created_at      DATETIME                        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_inscription (challenge_id, tireur_type, tireur_id, discipline_id),
    CONSTRAINT fk_inscription_challenge  FOREIGN KEY (challenge_id)  REFERENCES challenges  (id) ON DELETE CASCADE,
    CONSTRAINT fk_inscription_discipline FOREIGN KEY (discipline_id) REFERENCES disciplines (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Matchs : créneau horaire attribué à une inscription
-- Un match = une inscription + un jour + une heure de départ
-- heure_fin est stockée pour faciliter la détection de chevauchements
-- ------------------------------------------------------------
CREATE TABLE matchs (
    id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    inscription_id  INT UNSIGNED    NOT NULL,
    date_match      DATE            NOT NULL,
    heure_debut     TIME            NOT NULL,
    heure_fin       TIME            NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uk_match (inscription_id),
    CONSTRAINT fk_match_inscription FOREIGN KEY (inscription_id) REFERENCES inscriptions (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Blocs horaires : créneaux libres d'un challenge non liés à un
-- tireur (pause déjeuner, rangement, etc.). Les zones d'ouverture
-- et de clôture standard restent calculées automatiquement à
-- l'affichage ; cette table ne sert qu'aux blocs ajoutés à la main.
-- ------------------------------------------------------------
CREATE TABLE blocs_horaires (
    id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    challenge_id    INT UNSIGNED    NOT NULL,
    jour            DATE            NOT NULL,
    libelle         VARCHAR(150)    NOT NULL,
    heure_debut     TIME            NOT NULL,
    heure_fin       TIME            NOT NULL,
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_bloc_challenge FOREIGN KEY (challenge_id) REFERENCES challenges (id) ON DELETE CASCADE,
    CONSTRAINT chk_bloc_heures CHECK (heure_fin >= heure_debut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Scores : résultats par match (0-10 par animal)
-- ------------------------------------------------------------
CREATE TABLE scores (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    match_id    INT UNSIGNED    NOT NULL,
    poulets     TINYINT         NOT NULL DEFAULT 0 CHECK (poulets  BETWEEN 0 AND 10),
    cochons     TINYINT         NOT NULL DEFAULT 0 CHECK (cochons  BETWEEN 0 AND 10),
    dindons     TINYINT         NOT NULL DEFAULT 0 CHECK (dindons  BETWEEN 0 AND 10),
    mouflons    TINYINT         NOT NULL DEFAULT 0 CHECK (mouflons BETWEEN 0 AND 10),
    PRIMARY KEY (id),
    UNIQUE KEY uk_score_match (match_id),
    CONSTRAINT fk_score_match FOREIGN KEY (match_id) REFERENCES matchs (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
