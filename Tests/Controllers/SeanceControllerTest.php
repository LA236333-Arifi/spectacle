<?php

if (!defined("BASE_URL")) {
    define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'));
}

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../autoload.php';
require_once __DIR__ . '/../BaseTestClass.php';

class SeanceControllerTest extends BaseTestClass
{
    private $controller;
    private $testUser;

    protected function setUp(): void
    {
        // Réinitialiser les superglobales
        $_POST = [];
        $_GET = [];
        $_SESSION = [];
        $_SERVER['REQUEST_METHOD'] = 'POST';

        // Vider les tables et créer les données de test
        self::clearTestTables();
        self::insertReferenceData();
        
        // Créer un utilisateur admin
        $this->testUser = $this->createTestUser([
            'role' => 1 // 1 = Gérant
        ]);
        $this->loginTestUser($this->testUser);
        
        $this->controller = new SeanceController();
    }

    public function testAddSeanceWithMissingParameters()
    {
        $csrfToken = $this->getValidCsrfToken();
        $_POST = [
            'csrf_token' => $csrfToken,
            // Paramètres manquants
        ];

        ob_start();
        $result = $this->controller->addSeance();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);

        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testAddSeanceWithValidData()
    {
        $spectacleData = $this->createTestSpectacle();
        $csrfToken = $this->getValidCsrfToken();
        
        $_POST = [
            'csrf_token' => $csrfToken,
            'date_soiree_seance' => '2024-12-25',
            'spectacle_id' => $spectacleData['id']
        ];

        ob_start();
        $result = $this->controller->addSeance();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);

        $this->assertTrue($result);
        $this->assertEquals('success', $responseData['status']);
    }

    public function testMoveSeanceWithMissingParameters()
    {
        $csrfToken = $this->getValidCsrfToken();
        $_POST = [
            'csrf_token' => $csrfToken,
            // Paramètres manquants
        ];

        ob_start();
        $result = $this->controller->moveSeance();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);

        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testMoveSeanceWithValidData()
    {
        $seanceData = $this->createTestSeance();
        $csrfToken = $this->getValidCsrfToken();
        
        $_POST = [
            'csrf_token' => $csrfToken,
            'seance_id' => $seanceData['id'],
            'nouvelle_date_soiree' => '2024-12-30'
        ];

        ob_start();
        $result = $this->controller->moveSeance();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);

        $this->assertTrue($result);
        $this->assertEquals('success', $responseData['status']);
    }

    public function testAnnulerSeanceWithMissingParameters()
    {
        $csrfToken = $this->getValidCsrfToken();
        $_POST = [
            'csrf_token' => $csrfToken,
            // Paramètres manquants
        ];

        ob_start();
        $result = $this->controller->annulerSeance();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);

        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    // Helper methods
    private function createTestSpectacle(): array
    {
        $db = $this->getTestConnection();
        
        // Créer un groupe
        $stmt = $db->prepare("INSERT INTO Groupe_Spectacle (nom_groupe) VALUES (?)");
        $stmt->execute(['Groupe Test']);
        $groupeId = $db->lastInsertId();
        
        // Créer le spectacle
        $stmt = $db->prepare("
            INSERT INTO Spectacle (nom_spectacle, texte_accroche_spectacle, prix_spectacle, duree_minutes_spectacle, statut_spectacle_id, utilisateur_id, type_spectacle_id, groupe_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            'Spectacle Test',
            'Description test',
            25.50,
            120,
            SpectacleStatut::SansSeance,
            $this->testUser['id'],
            SpectacleType::Theatre,
            $groupeId
        ]);
        
        return [
            'id' => $db->lastInsertId(),
            'nom' => 'Spectacle Test',
            'groupe_id' => $groupeId
        ];
    }
    
    private function createTestSeance(): array
    {
        $spectacleData = $this->createTestSpectacle();
        $db = $this->getTestConnection();
        
        $stmt = $db->prepare("
            INSERT INTO Seance (date_soiree_seance, statut_seance_id, utilisateur_id, spectacle_id) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            '2024-12-25',
            SeanceStatut::Planifier,
            $this->testUser['id'],
            $spectacleData['id']
        ]);
        
        return [
            'id' => $db->lastInsertId(),
            'spectacle_id' => $spectacleData['id']
        ];
    }

    // ===== Tests supplémentaires pour addSeance() =====

    public function testAddSeanceWithoutAdmin()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'date_soiree_seance' => '2024-12-25',
            'spectacle_id' => '1'
        ];
        // Déconnecter l'utilisateur
        $this->logoutTestUser();
        ob_start();
        $result = $this->controller->addSeance();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testAddSeanceGetMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        ob_start();
        $result = $this->controller->addSeance();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testAddSeanceWithInvalidCSRF()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['csrf_token'] = 'valid_token';
        $_POST = 
        [
            'csrf_token' => 'invalid_token', // Token CSRF invalide
            'date_soiree_seance' => '2024-12-25',
            'spectacle_id' => '1'
        ];
        ob_start();
        $result = $this->controller->addSeance();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testAddSeanceWithInvalidDate()
    {
        $csrfToken = $this->getValidCsrfToken();
        $_POST = [
            'csrf_token' => $csrfToken,
            'date_soiree_seance' => 'invalid_date', // Date invalide
            'spectacle_id' => '1'
        ];

        ob_start();
        $result = $this->controller->addSeance();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);

        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testAddSeanceWithEmptySpectacleId()
    {
        $csrfToken = $this->getValidCsrfToken();
        $_POST = [
            'csrf_token' => $csrfToken,
            'date_soiree_seance' => '2024-12-25',
            'spectacle_id' => '' // Spectacle ID vide
        ];

        ob_start();
        $result = $this->controller->addSeance();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);

        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    // ===== Tests supplémentaires pour moveSeance() =====

    public function testMoveSeanceWithoutAdmin()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'seance_id' => '1',
            'nouvelle_date_soiree' => '2024-12-30'
        ];
        // Déconnecter l'utilisateur
        $this->logoutTestUser();
        ob_start();
        $result = $this->controller->moveSeance();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testMoveSeanceGetMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        ob_start();
        $result = $this->controller->moveSeance();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testMoveSeanceWithInvalidSeanceId()
    {
        $csrfToken = $this->getValidCsrfToken();
        $_POST = [
            'csrf_token' => $csrfToken,
            'seance_id' => 'invalid_id', // ID invalide
            'nouvelle_date_soiree' => '2024-12-30'
        ];

        ob_start();
        $result = $this->controller->moveSeance();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);

        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testMoveSeanceWithInvalidDate()
    {
        $csrfToken = $this->getValidCsrfToken();
        $_POST = [
            'csrf_token' => $csrfToken,
            'seance_id' => '1',
            'nouvelle_date_soiree' => 'invalid_date' // Date invalide
        ];

        ob_start();
        $result = $this->controller->moveSeance();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);

        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    // ===== Tests supplémentaires pour annulerSeance() =====

    public function testAnnulerSeanceWithoutAdmin()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'seance_id' => '1'
        ];
        // Déconnecter l'utilisateur
        $this->logoutTestUser();
        ob_start();
        $result = $this->controller->annulerSeance();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testAnnulerSeanceGetMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        ob_start();
        $result = $this->controller->annulerSeance();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testAnnulerSeanceWithInvalidSeanceId()
    {
        $csrfToken = $this->getValidCsrfToken();
        $_POST = [
            'csrf_token' => $csrfToken,
            'seance_id' => 'invalid_id' // ID invalide
        ];

        ob_start();
        $result = $this->controller->annulerSeance();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);

        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testAnnulerSeanceWithNegativeId()
    {
        $csrfToken = $this->getValidCsrfToken();
        $_POST = [
            'csrf_token' => $csrfToken,
            'seance_id' => '-1' // ID négatif
        ];

        ob_start();
        $result = $this->controller->annulerSeance();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);

        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    // ===== Tests avec utilisateur non-admin =====

    public function testAddSeanceWithSecretaireRole()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'date_soiree_seance' => '2024-12-25',
            'spectacle_id' => '1'
        ];
        // Créer un utilisateur secrétaire (role 2)
        $secretaireUser = $this->createTestUser(['role' => 2]);
        $this->loginTestUser($secretaireUser);
        ob_start();
        $result = $this->controller->addSeance();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testMoveSeanceWithSecretaireRole()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'seance_id' => '1',
            'nouvelle_date_soiree' => '2024-12-30'
        ];
        // Créer un utilisateur secrétaire (role 2)
        $secretaireUser = $this->createTestUser(['role' => 2]);
        $this->loginTestUser($secretaireUser);
        ob_start();
        $result = $this->controller->moveSeance();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testAnnulerSeanceWithSecretaireRole()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'seance_id' => '1'
        ];
        // Créer un utilisateur secrétaire (role 2)
        $secretaireUser = $this->createTestUser(['role' => 2]);
        $this->loginTestUser($secretaireUser);
        ob_start();
        $result = $this->controller->annulerSeance();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    // ===== Tests pour getSeancesDates() =====

    public function testGetSeancesDatesWithoutAdmin()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['spectacle_id'] = '1';
        // Déconnecter l'utilisateur
        $this->logoutTestUser();
        ob_start();
        $result = $this->controller->getSeancesDates();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testGetSeancesDatesPostMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_GET['spectacle_id'] = '1';
        ob_start();
        $result = $this->controller->getSeancesDates();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testGetSeancesDatesWithMissingSpectacleId()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        // Pas de spectacle_id dans $_GET
        ob_start();
        $result = $this->controller->getSeancesDates();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testGetSeancesDatesWithInvalidSpectacleId()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['spectacle_id'] = 'invalid_id'; // ID invalide
        ob_start();
        $result = $this->controller->getSeancesDates();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    // ===== Tests pour changeSeanceStatut() =====

    public function testChangeSeanceStatutWithoutAdmin()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'seance_id' => '1',
            'seance_statut_id' => '2'
        ];
        // Déconnecter l'utilisateur
        $this->logoutTestUser();
        ob_start();
        $result = $this->controller->changeSeanceStatut();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testChangeSeanceStatutGetMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        ob_start();
        $result = $this->controller->changeSeanceStatut();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testChangeSeanceStatutMissingParameters()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = $this->getValidCsrfToken();
        $_POST = [
            'csrf_token' => $csrfToken
            // Paramètres manquants
        ];

        ob_start();
        $result = $this->controller->changeSeanceStatut();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);

        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testChangeSeanceStatutInvalidStatutId()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = $this->getValidCsrfToken();
        $_POST = [
            'csrf_token' => $csrfToken,
            'seance_id' => '1',
            'seance_statut_id' => '999' // Statut invalide
        ];

        ob_start();
        $result = $this->controller->changeSeanceStatut();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);

        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }
}
