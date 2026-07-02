<?php
require_once APP_ROOT . '/core/Model.php';

class MembreModel extends Model
{
    protected string $table = 'membres';

    /**
     * Retourne tous les membres triés par nom puis prénom.
     */
    public function findAll(): array
    {
        $stmt = $this->db->query(
            'SELECT id, nom, prenom, date_naissance, telephone, email
             FROM membres
             ORDER BY nom ASC, prenom ASC'
        );
        return $stmt->fetchAll();
    }

    /**
     * Retourne un membre complet par son id.
     */
    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM membres WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Vérifie si un numéro de licence est déjà utilisé (hors $excludeId).
     */
    public function licenceExiste(string $licence, int $excludeId = 0): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM membres WHERE numero_licence = :licence AND id != :id'
        );
        $stmt->execute([':licence' => $licence, ':id' => $excludeId]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Retourne l'historique des challenges auxquels ce membre a participé,
     * avec son score total (toutes disciplines confondues) pour chacun.
     */
    public function findHistoriqueChallenges(int $membreId): array
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
                WHERE i.tireur_type = 'membre' AND i.tireur_id = :mid
                GROUP BY c.id, c.libelle, c.date_debut, c.date_fin, c.statut
                ORDER BY c.date_debut DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':mid' => $membreId]);
        return $stmt->fetchAll();
    }
}
