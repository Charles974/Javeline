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
                    CASE WHEN i.tireur_type = 'membre'
                         THEN m.nom  ELSE e.nom  END AS nom,
                    CASE WHEN i.tireur_type = 'membre'
                         THEN m.prenom ELSE e.prenom END AS prenom,
                    CASE WHEN i.tireur_type = 'externe'
                         THEN e.club ELSE NULL END AS club
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
