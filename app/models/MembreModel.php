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
}
