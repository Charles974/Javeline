<?php
require_once APP_ROOT . '/core/Model.php';

class BlocHoraireModel extends Model
{
    protected string $table = 'blocs_horaires';

    /**
     * Retourne les blocs libres d'un challenge, triés par jour puis heure.
     */
    public function findByChallenge(int $challengeId): array
    {
        $sql = "SELECT * FROM blocs_horaires
                WHERE challenge_id = :cid
                ORDER BY jour ASC, heure_debut ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':cid' => $challengeId]);
        return $stmt->fetchAll();
    }

    /**
     * Crée un bloc libre (ex: pause déjeuner). Retourne son id.
     */
    public function creer(int $challengeId, string $jour, string $libelle, string $heureDebut, string $heureFin): int
    {
        return $this->insert([
            'challenge_id' => $challengeId,
            'jour'         => $jour,
            'libelle'      => $libelle,
            'heure_debut'  => $heureDebut,
            'heure_fin'    => $heureFin,
        ]);
    }

    /**
     * Retourne le bloc couvrant un jour/heure donné pour un challenge, ou null.
     * Utilisé pour bloquer l'assignation d'un tireur sur ce créneau.
     */
    public function trouveBlocCouvrant(int $challengeId, string $jour, string $heureDebut, string $heureFin): array|null
    {
        $sql = "SELECT * FROM blocs_horaires
                WHERE challenge_id = :cid
                  AND jour = :jour
                  AND heure_debut < :fin
                  AND heure_fin   > :debut
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':cid'   => $challengeId,
            ':jour'  => $jour,
            ':debut' => $heureDebut,
            ':fin'   => $heureFin,
        ]);
        $bloc = $stmt->fetch();
        return $bloc ?: null;
    }

    /**
     * Supprime un bloc, à condition qu'il appartienne bien au challenge donné.
     */
    public function supprimerPourChallenge(int $id, int $challengeId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM blocs_horaires WHERE id = :id AND challenge_id = :cid');
        return $stmt->execute([':id' => $id, ':cid' => $challengeId]);
    }
}
