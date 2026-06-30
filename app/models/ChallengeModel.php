<?php
require_once APP_ROOT . '/core/Model.php';

class ChallengeModel extends Model
{
    protected string $table = 'challenges';

    /**
     * Retourne le challenge en cours (aujourd'hui compris entre date_debut et date_fin)
     * ou le prochain challenge à venir, avec le nombre de tireurs membres et externes inscrits.
     * Retourne null si aucun challenge ouvert ou futur.
     */
    public function findActif(): array|null
    {
        $sql = "SELECT c.*,
                    COUNT(DISTINCT CASE WHEN i.tireur_type = 'membre'   THEN i.tireur_id END) AS nb_membres,
                    COUNT(DISTINCT CASE WHEN i.tireur_type = 'externe'  THEN i.tireur_id END) AS nb_externes
                FROM challenges c
                LEFT JOIN inscriptions i ON i.challenge_id = c.id
                WHERE c.statut = 'ouvert'
                  AND c.date_fin >= CURDATE()
                GROUP BY c.id
                ORDER BY c.date_debut ASC
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();

        return $result ?: null;
    }

    public function findTous(): array
    {
        $sql = "SELECT c.*,
                    COUNT(DISTINCT CASE WHEN i.tireur_type = 'membre'  THEN i.tireur_id END) AS nb_membres,
                    COUNT(DISTINCT CASE WHEN i.tireur_type = 'externe' THEN i.tireur_id END) AS nb_externes
                FROM challenges c
                LEFT JOIN inscriptions i ON i.challenge_id = c.id
                GROUP BY c.id
                ORDER BY c.date_debut DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
