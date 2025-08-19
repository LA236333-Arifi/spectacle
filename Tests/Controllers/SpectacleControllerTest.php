<?php

if (!defined("BASE_URL")) {
    define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'));
}

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../autoload.php';
require_once __DIR__ . '/../BaseTestClass.php';

class SpectacleControllerTest extends BaseTestClass
{
    private $controller;
    private $testUser;

    protected function setUp(): void
    {
        $_POST = [];
        $_GET = [];
        $_SESSION = [];
        $_SERVER = [];
        self::clearTestTables();
        self::insertReferenceData();
        $this->controller = new SpectacleController();
        // Créer un utilisateur admin pour les tests
        $this->testUser = $this->createTestUser(['role' => 1]); // 1 = Gérant (admin)
    }

    // ===== Tests pour ajouter() =====

    public function testAjouterPostMethodWithoutAdmin()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'titre_spectacle' => 'Test Spectacle',
            'description_spectacle' => 'Description test',
            'type_spectacle_id' => '1'
        ];
        // Pas d'utilisateur connecté (donc pas admin)
        ob_start();
        $result = $this->controller->ajouter();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testAjouterGetMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->ajouter();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testAjouterPostMethodWithInvalidCSRF()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['csrf_token'] = 'valid_token';
        $_POST = 
        [
            'csrf_token' => 'invalid_token', // Token CSRF invalide
            'titre_spectacle' => 'Test Spectacle',
            'description_spectacle' => 'Description test',
            'type_spectacle_id' => '1'
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->ajouter();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testAjouterPostMethodWithMissingTitre()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            // 'titre_spectacle' manquant
            'description_spectacle' => 'Description test',
            'type_spectacle_id' => '1'
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->ajouter();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testAjouterPostMethodWithEmptyTitre()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'titre_spectacle' => '', // Titre vide
            'description_spectacle' => 'Description test',
            'type_spectacle_id' => '1'
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->ajouter();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testAjouterPostMethodWithMissingDescription()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'titre_spectacle' => 'Test Spectacle',
            // 'description_spectacle' manquant
            'type_spectacle_id' => '1'
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->ajouter();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testAjouterPostMethodWithMissingTypeId()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'titre_spectacle' => 'Test Spectacle',
            'description_spectacle' => 'Description test'
            // 'type_spectacle_id' manquant
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->ajouter();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testAjouterPostMethodWithInvalidTypeId()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'titre_spectacle' => 'Test Spectacle',
            'description_spectacle' => 'Description test',
            'type_spectacle_id' => 'invalid_type' // Type invalide
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->ajouter();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    // ===== Tests pour cloturer() =====

    public function testCloturerPostMethodWithoutAdmin()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'spectacle_id' => '1'
        ];
        // Pas d'utilisateur connecté (donc pas admin)
        ob_start();
        $result = $this->controller->cloturer();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testCloturerGetMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->cloturer();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testCloturerPostMethodWithMissingSpectacleId()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken
            // 'spectacle_id' manquant
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->cloturer();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testCloturerPostMethodWithInvalidSpectacleId()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'spectacle_id' => 'invalid_id' // ID invalide
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->cloturer();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    // ===== Tests pour apiListSpectacles() =====

    public function testApiListSpectaclesWithoutAdmin()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        // Pas d'utilisateur connecté (donc pas admin)
        ob_start();
        $result = $this->controller->apiListSpectacles();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testApiListSpectaclesPostMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->apiListSpectacles();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    // ===== Tests avec utilisateur non-admin =====

    public function testAjouterWithSecretaireRole()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'titre_spectacle' => 'Test Spectacle',
            'description_spectacle' => 'Description test',
            'type_spectacle_id' => '1'
        ];
        // Créer un utilisateur secrétaire (role 2)
        $secretaireUser = $this->createTestUser(['role' => 2]);
        $this->loginTestUser($secretaireUser);
        ob_start();
        $result = $this->controller->ajouter();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testCloturerWithSecretaireRole()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'spectacle_id' => '1'
        ];
        // Créer un utilisateur secrétaire (role 2)
        $secretaireUser = $this->createTestUser(['role' => 2]);
        $this->loginTestUser($secretaireUser);
        ob_start();
        $result = $this->controller->cloturer();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    // ===== Tests avec caractères spéciaux =====

    public function testAjouterWithSpecialCharactersInTitre()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'titre_spectacle' => 'Test@#$%Spectacle', // Titre avec caractères spéciaux
            'description_spectacle' => 'Description test',
            'type_spectacle_id' => '1'
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->ajouter();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('status', $responseData);
    }

    public function testAjouterWithVeryLongTitre()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'titre_spectacle' => str_repeat('A', 1000), // Titre très long
            'description_spectacle' => 'Description test',
            'type_spectacle_id' => '1'
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->ajouter();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('status', $responseData);
    }

    public function testAjouterWithNegativeTypeId()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'titre_spectacle' => 'Test Spectacle',
            'description_spectacle' => 'Description test',
            'type_spectacle_id' => '-1' // Type négatif
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->ajouter();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }
}