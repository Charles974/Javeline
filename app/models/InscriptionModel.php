<?php
require_once APP_ROOT . '/core/Model.php';

class InscriptionModel extends Model
{
    protected string $table = 'inscriptions';

    /**
     * Membres non encore inscrits au challenge.
     */
    public function findMembresDispo(int $challengeId): array
    {
        $sql = "SELECT m.id, m.nom, m.prenom, m.numero_licence
                FROM membres m
                WHERE m.id NOT IN (
                    SELECT DISTINCT tireur_id FROM inscriptions
                    WHERE challenge_id = :cid AND tireur_type = 'membre'
                )
                ORDER BY m.nom ASC, m.prenom ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':cid' => $challengeId]);
        return $stmt->fetchAll();
    }

    /**
     * Tireurs externes non encore inscrits au challenge.
     */
    public function findExternesDispo(int $challengeId): array
    {
        $sql = "SELECT e.id, e.nom, e.prenom, e.club
                FROM externes e
                WHERE e.id NOT IN (
                    SELECT DISTINCT tireur_id FROM inscriptions
                    WHERE challenge_id = :cid AND tireur_type = 'externe'
                )
                ORDER BY e.nom ASC, e.prenom ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':cid' => $challengeId]);
        return $stmt->fetchAll();
    }

    /**
     * Liste de tous les inscrits au challenge (une ligne par discipline).
     */
    public function findByChallenge(int $challengeId): array
    {
        $sql = "SELECT
                    i.id,
                    i.tireur_type,
                    i.tireur_id,
                    i.discipline_id,
                    d.code        AS discipline_code,
                    d.libelle_fr  AS discipline_fr,
                    d.libelle_en  AS discipline_en,
                    CASE WHEN i.tireur_type = 'membre'
                         THEN m.nom  ELSE e.nom  END AS nom,
                    CASE WHEN i.tireur_type = 'membre'
                         THEN m.prenom ELSE e.prenom END AS prenom,
                    CASE WHEN i.tireur_type = 'externe'
                         THEN e.club ELSE NULL END AS club,
                    CASE WHEN i.tireur_type = 'externe'
                         THEN e.etranger ELSE 0 END AS etranger
                FROM inscriptions i
                JOIN disciplines d  ON d.id = i.discipline_id
                LEFT JOIN membres m ON i.tireur_type = 'membre'  AND m.id = i.tireur_id
                LEFT JOIN externes e ON i.tireur_type = 'externe' AND e.id = i.tireur_id
                WHERE i.challenge_id = :cid
                ORDER BY nom ASC, prenom ASC, d.code ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':cid' => $challengeId]);
        return $stmt->fetchAll();
    }

    /**
     * Retourne tous les inscrits d'une discipline (avec ou sans score), pour le classement.
     * Les inscrits sans score renseigne (DEFECT) sont places en fin de liste.
     * Si $disciplineCode est fourni, filtre sur cette discipline uniquement.
     * Tri : code discipline ASC, puis avec score d'abord, puis regles de classement
     * (total, mouflons, dindons, cochons, poulets) DESC.
     */
    public function findClassements(int $challengeId, ?int $disciplineCode = null): array
    {
        $sql = "SELECT
                    d.code              AS discipline_code,
                    d.libelle_fr        AS discipline_fr,
                    d.libelle_en        AS discipline_en,
                    i.tireur_type,
                    CASE WHEN i.tireur_type = 'membre'
                         THEN m.nom   ELSE e.nom   END AS nom,
                    CASE WHEN i.tireur_type = 'membre'
                         THEN m.prenom ELSE e.prenom END AS prenom,
                    CASE WHEN i.tireur_type = 'membre'
                         THEN 'JAV' ELSE e.club END AS club,
                    s.poulets,
                    s.cochons,
                    s.dindons,
                    s.mouflons,
                    CASE WHEN s.id IS NULL THEN NULL
                         ELSE (s.poulets + s.cochons + s.dindons + s.mouflons) END AS total
                FROM inscriptions i
                JOIN disciplines d    ON d.id = i.discipline_id
                LEFT JOIN membres m   ON i.tireur_type = 'membre'   AND m.id = i.tireur_id
                LEFT JOIN externes e  ON i.tireur_type = 'externe'  AND e.id = i.tireur_id
                LEFT JOIN matchs ma   ON ma.inscription_id = i.id
                LEFT JOIN scores s    ON s.match_id = ma.id
                WHERE i.challenge_id = :cid";

        $params = [':cid' => $challengeId];

        if ($disciplineCode !== null) {
            $sql .= ' AND d.code = :dcode';
            $params[':dcode'] = $disciplineCode;
        }

        $sql .= ' ORDER BY d.code ASC,
                            (s.id IS NULL) ASC,
                            total DESC, s.mouflons DESC,
                            s.dindons DESC, s.cochons DESC, s.poulets DESC,
                            nom ASC, prenom ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Classement d'un combiné (aggregate) : additionne les scores d'un tireur
     * sur plusieurs disciplines. Un tireur apparaît dès qu'il a au moins un
     * score parmi les disciplines du combiné (total partiel accepté).
     * Tri : règles de classement (total, mouflons, dindons, cochons, poulets) DESC.
     */
    public function findClassementCombine(int $challengeId, array $disciplineCodes): array
    {
        if (empty($disciplineCodes)) {
            return [];
        }

        $params        = [':cid' => $challengeId];
        $placeholders  = [];
        foreach (array_values($disciplineCodes) as $i => $code) {
            $cle               = ":code{$i}";
            $placeholders[]    = $cle;
            $params[$cle]      = $code;
        }

        $sql = "SELECT
                    i.tireur_type,
                    i.tireur_id,
                    CASE WHEN i.tireur_type = 'membre'
                         THEN m.nom   ELSE e.nom   END AS nom,
                    CASE WHEN i.tireur_type = 'membre'
                         THEN m.prenom ELSE e.prenom END AS prenom,
                    CASE WHEN i.tireur_type = 'externe'
                         THEN e.club ELSE NULL END AS club,
                    COUNT(DISTINCT d.id)  AS nb_disciplines,
                    SUM(s.poulets)        AS poulets,
                    SUM(s.cochons)        AS cochons,
                    SUM(s.dindons)        AS dindons,
                    SUM(s.mouflons)       AS mouflons,
                    SUM(s.poulets + s.cochons + s.dindons + s.mouflons) AS total
                FROM inscriptions i
                JOIN disciplines d    ON d.id = i.discipline_id
                LEFT JOIN membres m   ON i.tireur_type = 'membre'   AND m.id = i.tireur_id
                LEFT JOIN externes e  ON i.tireur_type = 'externe'  AND e.id = i.tireur_id
                JOIN matchs ma        ON ma.inscription_id = i.id
                JOIN scores s         ON s.match_id = ma.id
                WHERE i.challenge_id = :cid
                  AND d.code IN (" . implode(',', $placeholders) . ")
                GROUP BY i.tireur_type, i.tireur_id
                ORDER BY total DESC, mouflons DESC, dindons DESC, cochons DESC,
                         poulets DESC, nom ASC, prenom ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Enregistre (insert ou update) les scores d'une inscription.
     * Crée le match automatiquement si aucun plan de tir n'a encore été assigné.
     * Retourne un tableau avec match_id et score_id.
     */
    public function saisirScore(int $inscriptionId, string $dateChallenge, int $poulets, int $cochons, int $dindons, int $mouflons): array
    {
        // Trouver ou créer le match pour cette inscription
        $stmt = $this->db->prepare('SELECT id FROM matchs WHERE inscription_id = :iid');
        $stmt->execute([':iid' => $inscriptionId]);
        $match = $stmt->fetch();

        if (!$match) {
            $stmt = $this->db->prepare(
                "INSERT INTO matchs (inscription_id, date_match, heure_debut, heure_fin)
                 VALUES (:iid, :date, '00:00:00', '01:00:00')"
            );
            $stmt->execute([':iid' => $inscriptionId, ':date' => $dateChallenge]);
            $matchId = (int)$this->db->lastInsertId();
        } else {
            $matchId = (int)$match['id'];
        }

        // Insérer ou mettre à jour le score (UPSERT)
        $stmt = $this->db->prepare(
            "INSERT INTO scores (match_id, poulets, cochons, dindons, mouflons)
             VALUES (:mid, :p, :c, :d, :m)
             ON DUPLICATE KEY UPDATE
                 poulets  = VALUES(poulets),
                 cochons  = VALUES(cochons),
                 dindons  = VALUES(dindons),
                 mouflons = VALUES(mouflons)"
        );
        $stmt->execute([
            ':mid' => $matchId,
            ':p'   => $poulets,
            ':c'   => $cochons,
            ':d'   => $dindons,
            ':m'   => $mouflons,
        ]);

        $stmt = $this->db->prepare('SELECT id FROM scores WHERE match_id = :mid');
        $stmt->execute([':mid' => $matchId]);
        $score = $stmt->fetch();

        return [
            'match_id' => $matchId,
            'score_id' => $score ? (int)$score['id'] : null,
        ];
    }

    /**
     * Retourne le challenge, le type et l'id du tireur d'une inscription
     * (utilisé pour vérifier l'appartenance au challenge et détecter les
     * chevauchements d'horaires du même tireur).
     */
    public function findInfosTireur(int $inscriptionId): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT challenge_id, tireur_type, tireur_id FROM inscriptions WHERE id = :id'
        );
        $stmt->execute([':id' => $inscriptionId]);
        return $stmt->fetch();
    }

    /**
     * Crée ou met à jour le plan de tir (date/horaire) d'une inscription.
     * Retourne l'id du match.
     */
    public function modifierHoraire(int $inscriptionId, string $dateMatch, string $heureDebut, string $heureFin): int
    {
        $stmt = $this->db->prepare('SELECT id FROM matchs WHERE inscription_id = :iid');
        $stmt->execute([':iid' => $inscriptionId]);
        $match = $stmt->fetch();

        if ($match) {
            $stmt = $this->db->prepare(
                'UPDATE matchs SET date_match = :date, heure_debut = :debut, heure_fin = :fin WHERE id = :id'
            );
            $stmt->execute([
                ':date'  => $dateMatch,
                ':debut' => $heureDebut,
                ':fin'   => $heureFin,
                ':id'    => $match['id'],
            ]);
            return (int)$match['id'];
        }

        $stmt = $this->db->prepare(
            'INSERT INTO matchs (inscription_id, date_match, heure_debut, heure_fin)
             VALUES (:iid, :date, :debut, :fin)'
        );
        $stmt->execute([
            ':iid'   => $inscriptionId,
            ':date'  => $dateMatch,
            ':debut' => $heureDebut,
            ':fin'   => $heureFin,
        ]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Retourne les autres matchs planifiés du même tireur dans ce challenge
     * (hors l'inscription en cours d'édition), pour détecter les chevauchements.
     */
    public function findAutresMatchsTireur(int $challengeId, string $tireurType, int $tireurId, int $excludeInscriptionId): array
    {
        $sql = "SELECT
                    i.id AS inscription_id,
                    d.code       AS discipline_code,
                    d.libelle_fr AS discipline_fr,
                    ma.date_match,
                    ma.heure_debut,
                    ma.heure_fin
                FROM inscriptions i
                JOIN disciplines d ON d.id = i.discipline_id
                JOIN matchs ma     ON ma.inscription_id = i.id
                WHERE i.challenge_id = :cid
                  AND i.tireur_type = :type
                  AND i.tireur_id   = :tid
                  AND i.id != :excl
                  AND ma.date_match IS NOT NULL";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':cid'  => $challengeId,
            ':type' => $tireurType,
            ':tid'  => $tireurId,
            ':excl' => $excludeInscriptionId,
        ]);
        return $stmt->fetchAll();
    }

    /**
     * Retourne les matchs planifiés d'un challenge qui chevauchent une plage
     * horaire donnée, un jour donné (toutes disciplines confondues). Sert à
     * empêcher la création d'un bloc horaire libre par-dessus des tireurs
     * déjà programmés sur ce créneau.
     */
    public function findMatchsDansPlage(int $challengeId, string $jour, string $heureDebut, string $heureFin): array
    {
        $sql = "SELECT
                    d.code       AS discipline_code,
                    d.libelle_fr AS discipline_fr,
                    CASE WHEN i.tireur_type = 'membre'
                         THEN m.nom  ELSE e.nom  END AS nom,
                    ma.heure_debut,
                    ma.heure_fin
                FROM inscriptions i
                JOIN disciplines d    ON d.id = i.discipline_id
                JOIN matchs ma        ON ma.inscription_id = i.id
                LEFT JOIN membres m   ON i.tireur_type = 'membre'  AND m.id = i.tireur_id
                LEFT JOIN externes e  ON i.tireur_type = 'externe' AND e.id = i.tireur_id
                WHERE i.challenge_id = :cid
                  AND ma.date_match  = :jour
                  AND ma.heure_debut < :fin
                  AND ma.heure_fin   > :debut";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':cid'   => $challengeId,
            ':jour'  => $jour,
            ':debut' => $heureDebut,
            ':fin'   => $heureFin,
        ]);
        return $stmt->fetchAll();
    }

    /**
     * Retourne les discipline_id déjà assignés à un tireur dans un challenge.
     */
    public function findDisciplinesByTireur(int $challengeId, string $type, int $tireurId): array
    {
        $sql = "SELECT discipline_id FROM inscriptions
                WHERE challenge_id = :cid
                  AND tireur_type  = :type
                  AND tireur_id    = :tid";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':cid' => $challengeId, ':type' => $type, ':tid' => $tireurId]);
        return array_column($stmt->fetchAll(), 'discipline_id');
    }

    /**
     * Inscrit un tireur à plusieurs disciplines d'un challenge.
     */
    public function inscrireTireur(int $challengeId, string $type, int $tireurId, array $disciplineIds): void
    {
        $sql  = "INSERT IGNORE INTO inscriptions (challenge_id, tireur_type, tireur_id, discipline_id)
                 VALUES (:cid, :type, :tid, :did)";
        $stmt = $this->db->prepare($sql);

        foreach ($disciplineIds as $did) {
            $stmt->execute([
                ':cid'  => $challengeId,
                ':type' => $type,
                ':tid'  => $tireurId,
                ':did'  => (int)$did,
            ]);
        }
    }

    /**
     * Modifie les disciplines d'un tireur déjà inscrit (supprime tout, réinsère).
     */
    public function modifierTireur(int $challengeId, string $type, int $tireurId, array $disciplineIds): void
    {
        $this->supprimerParTireur($challengeId, $type, $tireurId);
        $this->inscrireTireur($challengeId, $type, $tireurId, $disciplineIds);
    }

    /**
     * Retourne la liste complète des participants d'un challenge avec infos de match et de score.
     * Tri par défaut : heure_debut ASC (matchs planifiés d'abord), puis code discipline ASC.
     */
    public function findResume(int $challengeId): array
    {
        $sql = "SELECT
                    i.id                AS inscription_id,
                    i.tireur_type,
                    i.tireur_id,
                    d.code              AS discipline_code,
                    d.libelle_fr        AS discipline_fr,
                    CASE WHEN i.tireur_type = 'membre'
                         THEN m.nom   ELSE e.nom   END AS nom,
                    CASE WHEN i.tireur_type = 'membre'
                         THEN m.prenom ELSE e.prenom END AS prenom,
                    CASE WHEN i.tireur_type = 'externe'
                         THEN e.club ELSE NULL END AS club,
                    ma.date_match,
                    ma.heure_debut,
                    ma.heure_fin,
                    s.id                AS score_id,
                    s.poulets,
                    s.cochons,
                    s.dindons,
                    s.mouflons,
                    (COALESCE(s.poulets,0) + COALESCE(s.cochons,0)
                     + COALESCE(s.dindons,0) + COALESCE(s.mouflons,0)) AS total
                FROM inscriptions i
                JOIN disciplines d     ON d.id = i.discipline_id
                LEFT JOIN membres m    ON i.tireur_type = 'membre'   AND m.id = i.tireur_id
                LEFT JOIN externes e   ON i.tireur_type = 'externe'  AND e.id = i.tireur_id
                LEFT JOIN matchs ma    ON ma.inscription_id = i.id
                LEFT JOIN scores s     ON s.match_id = ma.id
                WHERE i.challenge_id = :cid
                ORDER BY
                    CASE WHEN ma.heure_debut IS NOT NULL THEN 0 ELSE 1 END ASC,
                    ma.date_match ASC,
                    ma.heure_debut ASC,
                    d.code ASC,
                    nom ASC,
                    prenom ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':cid' => $challengeId]);
        return $stmt->fetchAll();
    }

    /**
     * Retourne les matchs planifiés (date/horaire renseignés) d'un challenge,
     * triés chronologiquement — pour l'impression du planning.
     */
    public function findPlanning(int $challengeId): array
    {
        $sql = "SELECT
                    d.code              AS discipline_code,
                    d.libelle_fr        AS discipline_fr,
                    d.libelle_en        AS discipline_en,
                    CASE WHEN i.tireur_type = 'membre'
                         THEN m.nom   ELSE e.nom   END AS nom,
                    CASE WHEN i.tireur_type = 'membre'
                         THEN m.prenom ELSE e.prenom END AS prenom,
                    i.tireur_type,
                    CASE WHEN i.tireur_type = 'membre'
                         THEN 'Javeline' ELSE e.club END AS club,
                    CASE WHEN i.tireur_type = 'membre'
                         THEN m.coach ELSE e.coach END AS coach,
                    ma.date_match,
                    ma.heure_debut,
                    ma.heure_fin
                FROM inscriptions i
                JOIN disciplines d   ON d.id = i.discipline_id
                JOIN matchs ma       ON ma.inscription_id = i.id
                LEFT JOIN membres m  ON i.tireur_type = 'membre'  AND m.id = i.tireur_id
                LEFT JOIN externes e ON i.tireur_type = 'externe' AND e.id = i.tireur_id
                WHERE i.challenge_id = :cid
                ORDER BY ma.date_match ASC, ma.heure_debut ASC, nom ASC, prenom ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':cid' => $challengeId]);
        return $stmt->fetchAll();
    }

    /**
     * Retourne toutes les inscriptions d'un challenge avec les infos nécessaires
     * à la grille du plan de tir : discipline, tireur, coach, et match (planifié ou non).
     * Sert à la fois à construire la grille (matchs planifiés) et le pool des
     * tireurs en attente d'horaire (date_match IS NULL) par discipline.
     */
    public function findGrille(int $challengeId): array
    {
        $sql = "SELECT
                    i.id                AS inscription_id,
                    i.tireur_type,
                    i.tireur_id,
                    d.code              AS discipline_code,
                    d.libelle_fr        AS discipline_fr,
                    d.libelle_en        AS discipline_en,
                    CASE WHEN i.tireur_type = 'membre'
                         THEN m.nom   ELSE e.nom   END AS nom,
                    CASE WHEN i.tireur_type = 'membre'
                         THEN m.prenom ELSE e.prenom END AS prenom,
                    CASE WHEN i.tireur_type = 'membre'
                         THEN m.coach ELSE e.coach END AS coach,
                    ma.date_match,
                    ma.heure_debut,
                    ma.heure_fin,
                    s.id                AS score_id
                FROM inscriptions i
                JOIN disciplines d     ON d.id = i.discipline_id
                LEFT JOIN membres m    ON i.tireur_type = 'membre'   AND m.id = i.tireur_id
                LEFT JOIN externes e   ON i.tireur_type = 'externe'  AND e.id = i.tireur_id
                LEFT JOIN matchs ma    ON ma.inscription_id = i.id
                LEFT JOIN scores s     ON s.match_id = ma.id
                WHERE i.challenge_id = :cid
                ORDER BY d.code ASC, nom ASC, prenom ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':cid' => $challengeId]);
        return $stmt->fetchAll();
    }

    /**
     * Retire l'horaire (le match) d'une inscription, ce qui la remet dans le
     * pool des tireurs en attente. Refuse si un score a déjà été saisi pour
     * ne pas perdre de résultat (ON DELETE CASCADE supprimerait le score).
     * Retourne true si supprimé, false si aucun match ou score déjà présent.
     */
    public function supprimerHoraire(int $inscriptionId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT ma.id, s.id AS score_id
             FROM matchs ma
             LEFT JOIN scores s ON s.match_id = ma.id
             WHERE ma.inscription_id = :iid'
        );
        $stmt->execute([':iid' => $inscriptionId]);
        $match = $stmt->fetch();

        if (!$match) {
            return false;
        }
        if ($match['score_id'] !== null) {
            return false;
        }

        $stmt = $this->db->prepare('DELETE FROM matchs WHERE id = :id');
        return $stmt->execute([':id' => $match['id']]);
    }

    /**
     * Supprime une inscription par son id.
     */
    public function supprimerInscription(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM inscriptions WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Supprime toutes les inscriptions d'un tireur dans un challenge.
     */
    public function supprimerParTireur(int $challengeId, string $type, int $tireurId): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM inscriptions
             WHERE challenge_id = :cid AND tireur_type = :type AND tireur_id = :tid"
        );
        return $stmt->execute([':cid' => $challengeId, ':type' => $type, ':tid' => $tireurId]);
    }
}
