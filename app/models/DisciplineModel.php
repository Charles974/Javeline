<?php
require_once APP_ROOT . '/core/Model.php';

class DisciplineModel extends Model
{
    protected string $table = 'disciplines';

    public function findAll(): array
    {
        $stmt = $this->db->query('SELECT * FROM disciplines ORDER BY code ASC');
        return $stmt->fetchAll();
    }
}
