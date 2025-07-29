<?php

if (!isset($_ENV['APP_ENV'])) 
{
    require_once __DIR__ . '/../vendor/autoload.php'; // ../ pour revenir à la racine

    // Charge les variables d'environnement du fichier .env à la racine du projet
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../'); // ../ pour revenir à la racine
    $dotenv->load();
}

class Database 
{
    // La configuration de la DB par défaut
    private $host;
    private $dbname;
    private $user;
    private $pass;

    // L'instance pour le singleton
    private static $instance = null;

    // L'environnement courant 
    private static $currentEnv = null;

    // L'instance pour la connexion PDO 
    private $connection; 

    // Constructeur en private pour éviter l'instanciation par une autre classe
    private function __construct() 
    {
        $appEnv = $_ENV['APP_ENV'] ?? 'prod';
        self::$currentEnv = $appEnv;
        
        // Vérifie si le mode test est activé
        if ($appEnv === 'test') 
        {
            $this->host = $_ENV['TEST_MYSQL_SERVEUR'];
            $this->dbname = $_ENV['TEST_MYSQL_NOMDB'];
            $this->user = $_ENV['TEST_MYSQL_UTILISATEUR'];
            $this->pass = $_ENV['TEST_MYSQL_MDP'];
        } 
        else 
        {
            $this->host = $_ENV['PROD_MYSQL_SERVEUR'];
            $this->dbname = $_ENV['PROD_MYSQL_NOMDB'];
            $this->user = $_ENV['PROD_MYSQL_UTILISATEUR'];
            $this->pass = $_ENV['PROD_MYSQL_MDP'];
        }

        try 
        {
            $this->connection = new PDO
            (
                "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4",
                $this->user,
                $this->pass
            );
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } 
        catch (PDOException $e) 
        {
            die("Erreur de connexion : " . $e->getMessage());
        }
    }

    // La méthode pour récupérer l'instance
    public static function getInstance()
    {
        $currentEnv = $_ENV['APP_ENV'] ?? 'prod';

        // Crée une nouvele instance si elle est null ou que l'environnement courant a changé
        if (self::$instance === null || self::$currentEnv !== $currentEnv) 
        {
            self::$instance = new Database(); 
        }

        return self::$instance;
    }

    // On retourne la connexion active
    public function getConnection() 
    {
        return $this->connection;
    }
}
