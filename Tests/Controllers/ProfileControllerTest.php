<?php

if (!defined("BASE_URL")) {
    define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'));
}

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../autoload.php';
require_once __DIR__ . '/../BaseTestClass.php';

class ProfileControllerTest extends BaseTestClass
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
        $this->controller = new ProfileController();
        // Créer un utilisateur pour les tests
        $this->testUser = $this->createTestUser(['role' => 2]); // Secrétaire
    }

    // ===== Tests pour index() =====

    public function testIndexWithoutConnection()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        // Pas d'utilisateur connecté
        ob_start();
        $result = $this->controller->index();
        $output = ob_get_clean();
        $this->assertFalse($result);
    }

    public function testIndexWithConnection()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->index();
        $output = ob_get_clean();
        $this->assertTrue($result);
    }

    public function testIndexPostMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->index();
        $output = ob_get_clean();
        $this->assertFalse($result);
    }

    public function testIndexPutMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->index();
        $output = ob_get_clean();
        $this->assertFalse($result);
    }

    // ===== Tests pour updateProfile() =====

    public function testUpdateProfileWithoutConnection()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'nom_utilisateur' => 'Nouveau Nom',
            'prenom_utilisateur' => 'Nouveau Prénom',
            'mail_utilisateur' => 'nouveau@example.com'
        ];
        // Pas d'utilisateur connecté
        ob_start();
        $result = $this->controller->updateProfile();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testUpdateProfileGetMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->updateProfile();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testUpdateProfileWithInvalidCSRF()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['csrf_token'] = 'valid_token';
        $_POST = 
        [
            'csrf_token' => 'invalid_token', // Token CSRF invalide
            'nom_utilisateur' => 'Nouveau Nom',
            'prenom_utilisateur' => 'Nouveau Prénom',
            'mail_utilisateur' => 'nouveau@example.com'
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->updateProfile();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testUpdateProfileWithMissingNom()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            // 'nom_utilisateur' manquant
            'prenom_utilisateur' => 'Nouveau Prénom',
            'mail_utilisateur' => 'nouveau@example.com'
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->updateProfile();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testUpdateProfileWithEmptyNom()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'nom_utilisateur' => '', // Nom vide
            'prenom_utilisateur' => 'Nouveau Prénom',
            'mail_utilisateur' => 'nouveau@example.com'
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->updateProfile();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testUpdateProfileWithMissingPrenom()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'nom_utilisateur' => 'Nouveau Nom',
            // 'prenom_utilisateur' manquant
            'mail_utilisateur' => 'nouveau@example.com'
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->updateProfile();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testUpdateProfileWithInvalidEmail()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'nom_utilisateur' => 'Nouveau Nom',
            'prenom_utilisateur' => 'Nouveau Prénom',
            'mail_utilisateur' => 'invalid-email-format' // Format email invalide
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->updateProfile();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testUpdateProfileWithSpecialCharactersInNom()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'nom_utilisateur' => 'Nom@#$%', // Nom avec caractères spéciaux
            'prenom_utilisateur' => 'Nouveau Prénom',
            'mail_utilisateur' => 'nouveau@example.com'
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->updateProfile();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testUpdateProfileWithVeryLongNom()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'nom_utilisateur' => str_repeat('A', 500), // Nom très long
            'prenom_utilisateur' => 'Nouveau Prénom',
            'mail_utilisateur' => 'nouveau@example.com'
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->updateProfile();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testUpdateProfileWithSpacesInFields()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'nom_utilisateur' => '   ', // Nom avec seulement des espaces
            'prenom_utilisateur' => '   ', // Prénom avec seulement des espaces
            'mail_utilisateur' => ' nouveau@example.com ' // Email avec espaces
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->updateProfile();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testUpdateProfileWithNumericNames()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'nom_utilisateur' => '12345', // Nom numérique
            'prenom_utilisateur' => '67890', // Prénom numérique
            'mail_utilisateur' => 'nouveau@example.com'
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->updateProfile();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testUpdateProfileWithEmailWithoutAt()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'nom_utilisateur' => 'Nouveau Nom',
            'prenom_utilisateur' => 'Nouveau Prénom',
            'mail_utilisateur' => 'nouveauexample.com' // Email sans @
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->updateProfile();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testUpdateProfileWithEmailWithoutDomain()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'nom_utilisateur' => 'Nouveau Nom',
            'prenom_utilisateur' => 'Nouveau Prénom',
            'mail_utilisateur' => 'nouveau@' // Email sans domaine
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->updateProfile();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    // ===== Tests avec méthodes non supportées =====

    public function testUpdateProfileDeleteMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->updateProfile();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testUpdateProfilePatchMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'PATCH';
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->updateProfile();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

}