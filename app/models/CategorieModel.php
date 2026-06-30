<?php
require_once APP_ROOT . '/core/Model.php';

class CategorieModel extends Model
{
    protected string $table = 'categories';

    public function findAll(): array
    {
        $pdo  = $this->db->getConnection();
        $stmt = $pdo->query('SELECT id, libelle FROM categories ORDER BY libelle ASC');
        return $stmt->fetchAll();
    }
}
