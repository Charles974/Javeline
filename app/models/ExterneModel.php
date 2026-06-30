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
            'SELECT id, nom, prenom, club, telephone, email
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
}
