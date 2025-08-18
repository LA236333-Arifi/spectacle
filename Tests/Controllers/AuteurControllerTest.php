<?php

if (!defined("BASE_URL")) {
    define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'));
}

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../autoload.php';
require_once __DIR__ . '/../BaseTestClass.php';

class AuteurControllerTest extends BaseTestClass
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
        $this->controller = new AuteurController();
        // Créer un utilisateur admin pour les tests
        $this->testUser = $this->createTestUser(['role' => 1]); // 1 = Gérant (admin)
    }

    // ===== Tests pour addAuteur() =====

    public function testAddAuteurPostMethodWithoutAdmin()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'nom' => 'Auteur',
            'prenom' => 'Test'
        ];
        // Pas d'utilisateur connecté (donc pas admin)
        ob_start();
        $result = $this->controller->addAuteur();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testAddAuteurGetMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->addAuteur();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testAddAuteurPostMethodWithInvalidCSRF()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['csrf_token'] = 'valid_token';
        $_POST = 
        [
            'csrf_token' => 'invalid_token', // Token CSRF invalide
            'nom' => 'Auteur',
            'prenom' => 'Test'
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->addAuteur();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testAddAuteurPostMethodWithMissingCSRF()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['csrf_token'] = 'valid_token';
        $_POST = 
        [
            // Token CSRF manquant
            'nom' => 'Auteur',
            'prenom' => 'Test'
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->addAuteur();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testAddAuteurPostMethodWithMissingNom()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            // 'nom' manquant
            'prenom' => 'Test'
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->addAuteur();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testAddAuteurPostMethodWithMissingPrenom()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'nom' => 'Auteur',
            // 'prenom' manquant
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->addAuteur();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testAddAuteurPostMethodWithEmptyNom()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'nom' => '', // Nom vide
            'prenom' => 'Test'
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->addAuteur();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testAddAuteurPostMethodWithEmptyPrenom()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'nom' => 'Auteur',
            'prenom' => '' // Prénom vide
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->addAuteur();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testAddAuteurPostMethodWithSpacesInNom()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'nom' => '   ', // Nom avec seulement des espaces
            'prenom' => 'Test'
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->addAuteur();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    // ===== Tests pour addMetteurScene() =====

    public function testAddMetteurScenePostMethodWithoutAdmin()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'nom' => 'Metteur',
            'prenom' => 'Scene'
        ];
        // Pas d'utilisateur connecté (donc pas admin)
        ob_start();
        $result = $this->controller->addMetteurScene();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testAddMetteurSceneGetMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->addMetteurScene();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testAddMetteurScenePostMethodWithInvalidCSRF()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['csrf_token'] = 'valid_token';
        $_POST = 
        [
            'csrf_token' => 'invalid_token', // Token CSRF invalide
            'nom' => 'Metteur',
            'prenom' => 'Scene'
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->addMetteurScene();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testAddMetteurScenePostMethodWithMissingNom()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            // 'nom' manquant
            'prenom' => 'Scene'
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->addMetteurScene();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testAddMetteurScenePostMethodWithEmptyFields()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'nom' => '',
            'prenom' => ''
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->addMetteurScene();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    // ===== Tests pour apiListAuteurs() =====

    public function testApiListAuteursWithoutAdmin()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        // Pas d'utilisateur connecté (donc pas admin)
        ob_start();
        $result = $this->controller->apiListAuteurs();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testApiListAuteursPostMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->apiListAuteurs();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testApiListAuteursPutMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->apiListAuteurs();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testApiListAuteursDeleteMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->apiListAuteurs();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    // ===== Tests pour apiListMetteurs() =====

    public function testApiListMetteursWithoutAdmin()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        // Pas d'utilisateur connecté (donc pas admin)
        ob_start();
        $result = $this->controller->apiListMetteurs();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testApiListMetteursPostMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->apiListMetteurs();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testApiListMetteursPatchMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'PATCH';
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->apiListMetteurs();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testApiListMetteursOptionsMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'OPTIONS';
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->apiListMetteurs();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    // ===== Tests avec utilisateur non-admin =====

    public function testAddAuteurWithSecretaireRole()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'nom' => 'Auteur',
            'prenom' => 'Test'
        ];
        // Créer un utilisateur secrétaire (role 2)
        $secretaireUser = $this->createTestUser(['role' => 2]);
        $this->loginTestUser($secretaireUser);
        ob_start();
        $result = $this->controller->addAuteur();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testApiListAuteursWithSecretaireRole()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        // Créer un utilisateur secrétaire (role 2)
        $secretaireUser = $this->createTestUser(['role' => 2]);
        $this->loginTestUser($secretaireUser);
        ob_start();
        $result = $this->controller->apiListAuteurs();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    // ===== Tests avec caractères spéciaux =====

    public function testAddAuteurWithSpecialCharactersInNom()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'nom' => 'Auteur@#$%', // Nom avec caractères spéciaux
            'prenom' => 'Test'
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->addAuteur();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        // Selon la logique métier, cela pourrait réussir ou échouer
        // Ici on teste que le controller gère bien ces caractères
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('status', $responseData);
    }

    public function testAddMetteurSceneWithNumericNames()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'nom' => '12345', // Nom numérique
            'prenom' => '67890' // Prénom numérique
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->addMetteurScene();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        // Test que le controller traite ces données
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('status', $responseData);
    }

}
