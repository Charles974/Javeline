<?php
/**
 * Classe de base pour tous les modèles.
 * Fournit un accès PDO et des méthodes CRUD génériques.
 */
abstract class Model
{
    protected PDO $db;
    protected string $table = '';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Récupère tous les enregistrements de la table.
     */
    public function findAll(): array
    {
        $stmt = $this->db->query('SELECT * FROM ' . $this->table);
        return $stmt->fetchAll();
    }

    /**
     * Récupère un enregistrement par son identifiant.
     */
    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM ' . $this->table . ' WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Insère un enregistrement. $data = ['colonne' => valeur, ...]
     */
    public function insert(array $data): int
    {
        $colonnes  = implode(', ', array_keys($data));
        $parametres = ':' . implode(', :', array_keys($data));

        $sql  = 'INSERT INTO ' . $this->table . ' (' . $colonnes . ') VALUES (' . $parametres . ')';
        $stmt = $this->db->prepare($sql);

        // Préfixe les clés avec ':'
        $params = [];
        foreach ($data as $cle => $valeur) {
            $params[':' . $cle] = $valeur;
        }

        $stmt->execute($params);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Met à jour un enregistrement par son identifiant.
     */
    public function update(int $id, array $data): bool
    {
        $set = '';
        foreach (array_keys($data) as $cle) {
            $set .= $cle . ' = :' . $cle . ', ';
        }
        $set = rtrim($set, ', ');

        $sql  = 'UPDATE ' . $this->table . ' SET ' . $set . ' WHERE id = :id';
        $stmt = $this->db->prepare($sql);

        $params = [':id' => $id];
        foreach ($data as $cle => $valeur) {
            $params[':' . $cle] = $valeur;
        }

        return $stmt->execute($params);
    }

    /**
     * Supprime un enregistrement par son identifiant.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM ' . $this->table . ' WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }
}
