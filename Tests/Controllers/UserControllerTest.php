<?php

if (!defined("BASE_URL")) {
    define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'));
}

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../autoload.php';
require_once __DIR__ . '/../BaseTestClass.php';

class UserControllerTest extends BaseTestClass
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
        $this->controller = new UserController();
        // Créer un utilisateur admin pour les tests
        $this->testUser = $this->createTestUser(['role' => 1]); // 1 = Gérant (admin)
    }

    // ===== Tests pour apiListUsers() =====

    public function testApiListUsersWithoutAdmin()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        // Pas d'utilisateur connecté (donc pas admin)
        ob_start();
        $result = $this->controller->apiListUsers();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testApiListUsersPostMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->apiListUsers();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testApiListUsersWithInvalidPageParameter()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['page'] = 'invalid_page'; // Page invalide
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->apiListUsers();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testApiListUsersWithInvalidLimitParameter()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['limit'] = 'invalid_limit'; // Limite invalide
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->apiListUsers();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testApiListUsersWithZeroPage()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['page'] = '0'; // Page zéro
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->apiListUsers();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        // Devrait utiliser page 1 par défaut selon max(1, $_GET['page'])
        $this->assertTrue($result);
    }

    // ===== Tests pour toggleStatus() =====

    public function testToggleStatusWithoutAdmin()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'id' => '1'
        ];
        // Pas d'utilisateur connecté (donc pas admin)
        ob_start();
        $result = $this->controller->toggleStatus();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testToggleStatusGetMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->toggleStatus();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testToggleStatusWithInvalidCSRF()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['csrf_token'] = 'valid_token';
        $_POST = 
        [
            'csrf_token' => 'invalid_token', // Token CSRF invalide
            'id' => '1'
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->toggleStatus();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testToggleStatusWithMissingId()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken
            // 'id' manquant
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->toggleStatus();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testToggleStatusWithInvalidId()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'id' => 'invalid_id' // ID invalide
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->toggleStatus();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
    }

    // ===== Tests pour accept() =====

    public function testAcceptWithoutAdmin()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'id' => '1'
        ];
        // Pas d'utilisateur connecté (donc pas admin)
        ob_start();
        $result = $this->controller->accept();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testAcceptGetMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->accept();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testAcceptWithMissingId()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken
            // 'id' manquant
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->accept();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testAcceptWithInvalidId()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'id' => 'invalid_id' // ID invalide
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->accept();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    // ===== Tests pour refuse() =====

    public function testRefuseWithoutAdmin()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'id' => '1'
        ];
        // Pas d'utilisateur connecté (donc pas admin)
        ob_start();
        $result = $this->controller->refuse();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testRefuseGetMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->refuse();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testRefuseWithMissingId()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken
            // 'id' manquant
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->refuse();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testRefuseWithInvalidId()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'id' => 'invalid_id' // ID invalide
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->refuse();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    // ===== Tests pour apiListAccess() =====

    public function testApiListAccessWithoutAdmin()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        // Pas d'utilisateur connecté (donc pas admin)
        ob_start();
        $result = $this->controller->apiListAccess();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testApiListAccessPostMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->apiListAccess();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testApiListAccessWithInvalidPageParameter()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['page'] = 'invalid_page'; // Page invalide
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->apiListAccess();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    // ===== Tests avec utilisateur non-admin =====

    public function testApiListUsersWithSecretaireRole()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        // Créer un utilisateur secrétaire (role 2)
        $secretaireUser = $this->createTestUser(['role' => 2]);
        $this->loginTestUser($secretaireUser);
        ob_start();
        $result = $this->controller->apiListUsers();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testToggleStatusWithSecretaireRole()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'id' => '1'
        ];
        // Créer un utilisateur secrétaire (role 2)
        $secretaireUser = $this->createTestUser(['role' => 2]);
        $this->loginTestUser($secretaireUser);
        ob_start();
        $result = $this->controller->toggleStatus();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    // ===== Tests avec paramètres limites =====

    public function testApiListUsersWithVeryHighPage()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['page'] = '999999'; // Page très élevée
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->apiListUsers();
        $jsonOutput = ob_get_clean();
        // Devrait retourner une liste vide ou la dernière page
        $this->assertTrue($result);
    }

    public function testApiListUsersWithVeryHighLimit()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['limit'] = '999999'; // Limite très élevée
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->apiListUsers();
        $jsonOutput = ob_get_clean();
        $this->assertTrue($result);
    }

}