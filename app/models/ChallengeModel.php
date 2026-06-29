<?php
require_once APP_ROOT . '/core/Model.php';

class ChallengeModel extends Model
{
    protected string $table = 'challenges';

    /**
     * Retourne le challenge en cours (aujourd'hui compris entre date_debut et date_fin)
     * ou le prochain challenge à venir, ou null si aucun.
     */
    public function findActif(): array|null
    {
        $sql = "SELECT *
                FROM challenges
                WHERE statut = 'ouvert'
                  AND date_fin >= CURDATE()
                ORDER BY date_debut ASC
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();

        return $result ?: null;
    }
}
