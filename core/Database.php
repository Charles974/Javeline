<?php
/**
 * Connexion PDO en singleton.
 * Toutes les requêtes passent par cette classe pour garantir UTF-8 et les requêtes préparées.
 */
class Database
{
    private static ?PDO $instance = null;

    // Empêche l'instanciation directe
    private function __construct() {}

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $dsn = 'mysql:host=' . DB_HOST
                 . ';dbname=' . DB_NAME
                 . ';charset=' . DB_CHARSET;

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                // En production, ne pas exposer les détails de connexion
                if (APP_ENV === 'development') {
                    die('Erreur de connexion à la base de données : ' . $e->getMessage());
                } else {
                    die('Erreur de connexion à la base de données. Veuillez réessayer plus tard.');
                }
            }
        }

        return self::$instance;
    }
}
