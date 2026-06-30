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
