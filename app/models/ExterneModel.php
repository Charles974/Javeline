<?php
require_once APP_ROOT . '/core/Model.php';

class ExterneModel extends Model
{
    protected string $table = 'externes';

    /**
     * Retourne tous les tireurs externes triés par nom puis prénom.
     */
    public function findAll(): array
    {
        $stmt = $this->db->query(
            'SELECT id, nom, prenom, club, telephone, email, etranger
             FROM externes
             ORDER BY nom ASC, prenom ASC'
        );
        return $stmt->fetchAll();
    }

    /**
     * Retourne un tireur externe complet par son id.
     */
    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM externes WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Retourne l'historique des challenges auxquels ce tireur externe a participé,
     * avec son score total (toutes disciplines confondues) pour chacun.
     */
    public function findHistoriqueChallenges(int $externeId): array
    {
        $sql = "SELECT
                    c.id,
                    c.libelle,
                    c.date_debut,
                    c.date_fin,
                    c.statut,
                    COUNT(DISTINCT i.discipline_id) AS nb_disciplines,
                    COALESCE(SUM(s.poulets + s.cochons + s.dindons + s.mouflons), 0) AS total_score
                FROM inscriptions i
                JOIN challenges c   ON c.id = i.challenge_id
                LEFT JOIN matchs ma ON ma.inscription_id = i.id
                LEFT JOIN scores s  ON s.match_id = ma.id
                WHERE i.tireur_type = 'externe' AND i.tireur_id = :eid
                GROUP BY c.id, c.libelle, c.date_debut, c.date_fin, c.statut
                ORDER BY c.date_debut DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':eid' => $externeId]);
        return $stmt->fetchAll();
    }
}
