<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../autoload.php';

abstract class BaseTestClass extends TestCase
{
    // L'environnement précédent
    protected static $previousEnv = 'dev';

    public static function setUpBeforeClass(): void
    {
        // On set la variable d'environnement à "test" pour que la création 
        // du singleton de la DB prenne en compte les infos de DB
        self::$previousEnv = $_ENV['APP_ENV'] ?? 'dev';
        putenv('APP_ENV=test');
        $_ENV['APP_ENV'] = 'test';
    }

    public static function tearDownAfterClass(): void
    {
        // 1. Restaurer l'environnement précédent
        putenv('APP_ENV=' . self::$previousEnv);
        $_ENV['APP_ENV'] = self::$previousEnv;
    }

    /**
     * Vide toutes les tables de test (à appeler dans setUp() si nécessaire)
     */
    protected static function clearTestTables(): void
    {
        $db = Database::getInstance()->getConnection();
        
        // Désactiver les contraintes de clés étrangères temporairement
        $db->exec('SET FOREIGN_KEY_CHECKS = 0');
        
        // Liste complète des tables extraites du fichier spectacle.sql
        // Ordre inversé pour respecter les contraintes de clés étrangères
        $tables = [
            'Liaison_Groupe',
            'Auteur_MetteurScene_Spectacle', 
            'Seance',
            'Spectacle',
            'Utilisateur',
            'Performeur_Spectacle',
            'MetteurScene_Spectacle',
            'Auteur_Spectacle',
            'Statut_Utilisateur',
            'Statut_Spectacle',
            'Role_Utilisateur',
            'Role_Performeur',
            'Groupe_Spectacle',
            'Statut_Seance',
            'Type_Spectacle'
        ];
        
        foreach ($tables as $table) 
        {
            $db->exec("TRUNCATE TABLE `$table`");
        }
        
        // Réactiver les contraintes
        $db->exec('SET FOREIGN_KEY_CHECKS = 1');
    }

    /**
     * Insère les données de référence nécessaires aux tests
     */
    protected function insertReferenceData(): void
    {
        $db = Database::getInstance()->getConnection();
        
        try {
            // Statuts de séance
            $db->exec("INSERT IGNORE INTO Statut_Seance (statut_seance_id, nom_statut_seance) VALUES (1, 'Planifié'), (2, 'Annulé')");
            
            // Types de spectacle
            $db->exec("INSERT IGNORE INTO Type_Spectacle (type_spectacle_id, nom_type_spectacle) VALUES (1, 'Theatre'), (2, 'Concert Rock'), (3, 'Concert Classique'), (4, 'Humouriste'), (5, 'Danse')");
            
            // Statuts de spectacle
            $db->exec("INSERT IGNORE INTO Statut_Spectacle (statut_spectacle_id, nom_statut_spectacle) VALUES (1, 'Sans séance'), (2, 'En cours'), (3, 'Cloturé')");
            
            // Rôles de performeurs
            $db->exec("INSERT IGNORE INTO Role_Performeur (role_performeur_id, nom_role_performeur) VALUES (1, 'Danseur'), (2, 'Humouriste'), (3, 'Acteur'), (4, 'Chanteur'), (5, 'Musicien')");
            
            // Rôles utilisateurs
            $db->exec("INSERT IGNORE INTO Role_Utilisateur (role_utilisateur_id, nom_role_utilisateur) VALUES (1, 'Gérant'), (2, 'Secrétaire')");
            
            // Statuts utilisateurs
            $db->exec("INSERT IGNORE INTO Statut_Utilisateur (statut_utilisateur_id, nom_statut_utilisateur) VALUES (1, 'Non validé et inactif'), (2, 'Validé et inactif'), (3, 'Validé et actif')");
            
        } catch (Exception $e) {
            echo "ERREUR insertion données de référence: " . $e->getMessage() . "\n";
            throw $e;
        }
    }

    /**
     * Crée un utilisateur de test dans la base de données
     * @param array $data Données personnalisées pour l'utilisateur (nom, prenom, email, etc.)
     * @return array Données de l'utilisateur créé avec son ID
     */
    protected function createTestUser(array $data = []): array
    {
        $defaultData = [
            'nom' => 'Test',
            'prenom' => 'User',
            'email' => 'test' . uniqid() . '@example.com',
            'password' => 'Password123',
            'role' => 1, // Gérant par défaut
            'statut' => 3 // Validé et actif par défaut
        ];
        
        $userData = array_merge($defaultData, $data);
        
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            INSERT INTO Utilisateur (nom_utilisateur, prenom_utilisateur, mail_utilisateur, mdp_utilisateur, role_utilisateur_id, statut_utilisateur_id)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);
        
        $stmt->execute([
            $userData['nom'],
            $userData['prenom'],
            $userData['email'],
            $hashedPassword,
            $userData['role'],
            $userData['statut']
        ]);
        
        return [
            'id' => $db->lastInsertId(),
            'email' => $userData['email'],
            'password' => $userData['password'], // Mot de passe en clair pour les tests
            'role' => $userData['role'],
            'nom' => $userData['nom'],
            'prenom' => $userData['prenom'],
            'statut' => $userData['statut']
        ];
    }

    /**
     * Simule la connexion d'un utilisateur en créant une session
     * @param array $userData Données de l'utilisateur (retour de createTestUser)
     */
    protected function loginTestUser(array $userData): void
    {
        // Simuler les données de session qu'un utilisateur connecté aurait
        $_SESSION['user'] = 
        [
            'id' => $userData['id'],
            'nom' => $userData['nom'],
            'prenom' => $userData['prenom'],
            'email' => $userData['email'],
            'role_id' => $userData['role']
        ];
    }

    /**
     * Déconnecte l'utilisateur de test en vidant la session
     */
    protected function logoutTestUser(): void
    {
        $_SESSION = [];
    }

    /**
     * Génère un token CSRF valide pour les tests
     * @return string Token CSRF
     */
    protected function getValidCsrfToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        return $token;
    }

    /**
     * Retourne la connexion à la base de données de test
     * @return PDO
     */
    protected function getTestConnection(): PDO
    {
        return Database::getInstance()->getConnection();
    }
}
