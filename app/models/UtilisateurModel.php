<?php
require_once APP_ROOT . '/core/Model.php';

/**
 * Modèle des comptes utilisateurs (table utilisateurs).
 * Les mots de passe sont toujours stockés hashés (password_hash / bcrypt).
 */
class UtilisateurModel extends Model
{
    protected string $table = 'utilisateurs';

    /**
     * Recherche un compte par son identifiant de connexion.
     */
    public function findByIdentifiant(string $identifiant): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM ' . $this->table . ' WHERE identifiant = :identifiant'
        );
        $stmt->execute([':identifiant' => $identifiant]);
        return $stmt->fetch();
    }

    /**
     * Liste tous les comptes sans exposer les hash de mots de passe.
     */
    public function findAllSansMotDePasse(): array
    {
        $stmt = $this->db->query(
            'SELECT id, identifiant, role, created_at
             FROM ' . $this->table . '
             ORDER BY identifiant'
        );
        return $stmt->fetchAll();
    }

    /**
     * Vérifie un couple identifiant / mot de passe.
     * Retourne le compte si les identifiants sont valides, false sinon.
     * Le hash est mis à niveau automatiquement si l'algorithme a évolué.
     */
    public function verifierIdentifiants(string $identifiant, string $motDePasse): array|false
    {
        $utilisateur = $this->findByIdentifiant($identifiant);

        if (!$utilisateur || !password_verify($motDePasse, $utilisateur['mot_de_passe'])) {
            return false;
        }

        if (password_needs_rehash($utilisateur['mot_de_passe'], PASSWORD_DEFAULT)) {
            $this->changerMotDePasse((int) $utilisateur['id'], $motDePasse);
        }

        return $utilisateur;
    }

    /**
     * Crée un compte : le mot de passe est hashé ici, jamais en clair en base.
     */
    public function creer(string $identifiant, string $motDePasse, string $role): int
    {
        return $this->insert([
            'identifiant'  => $identifiant,
            'mot_de_passe' => password_hash($motDePasse, PASSWORD_DEFAULT),
            'role'         => $role,
        ]);
    }

    /**
     * Remplace le mot de passe d'un compte (hashé).
     */
    public function changerMotDePasse(int $id, string $nouveauMotDePasse): bool
    {
        return $this->update($id, [
            'mot_de_passe' => password_hash($nouveauMotDePasse, PASSWORD_DEFAULT),
        ]);
    }

    /**
     * Change le profil (rôle) d'un compte.
     */
    public function changerRole(int $id, string $role): bool
    {
        return $this->update($id, ['role' => $role]);
    }

    /**
     * Indique si un identifiant de connexion est déjà pris
     * (en excluant éventuellement un compte lors d'une modification).
     */
    public function identifiantExiste(string $identifiant, int $idExclu = 0): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM ' . $this->table . '
             WHERE identifiant = :identifiant AND id <> :id'
        );
        $stmt->execute([':identifiant' => $identifiant, ':id' => $idExclu]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
