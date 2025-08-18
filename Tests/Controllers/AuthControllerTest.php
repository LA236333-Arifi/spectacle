<?php

if (!defined("BASE_URL")) {
    define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'));
}

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../BaseTestClass.php';
require_once __DIR__ . '/../../autoload.php';

class AuthControllerTest extends BaseTestClass
{
    private $controller;

    protected function setUp(): void
    {
        $_POST = [];
        $_GET = [];
        $_SESSION = [];
        self::clearTestTables();
        self::insertReferenceData();
        $this->controller = new AuthController();
    }

    public function testRegisterPostMethodWithMissingFields()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
        ];
        ob_start();
        $result = $this->controller->register();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testLoginPostMethodWithMissingFields()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            // Champs mail_utilisateur et mdp_utilisateur manquants
        ];
        ob_start();
        $result = $this->controller->login();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testRegisterPostMethodWithInvalidRole()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'nom_utilisateur' => 'Test',
            'prenom_utilisateur' => 'User',
            'mail_utilisateur' => 'test@example.com',
            'mail_utilisateur_confirm' => 'test@example.com',
            'mdp_utilisateur' => 'Password123',
            'mdp_utilisateur_confirm' => 'Password123',
            'role_utilisateur' => 'invalid_role' // Role invalide
        ];
        ob_start();
        $result = $this->controller->register();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testRegisterPostMethodWithInvalidRoleNumber()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'nom_utilisateur' => 'Test',
            'prenom_utilisateur' => 'User',
            'mail_utilisateur' => 'test@example.com',
            'mail_utilisateur_confirm' => 'test@example.com',
            'mdp_utilisateur' => 'Password123',
            'mdp_utilisateur_confirm' => 'Password123',
            'role_utilisateur' => '999' // Role numérique invalide
        ];
        ob_start();
        $result = $this->controller->register();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testRegisterPostMethodWithMismatchedEmails()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'nom_utilisateur' => 'Test',
            'prenom_utilisateur' => 'User',
            'mail_utilisateur' => 'test@example.com',
            'mail_utilisateur_confirm' => 'different@example.com', // Email différent
            'mdp_utilisateur' => 'Password123',
            'mdp_utilisateur_confirm' => 'Password123',
            'role_utilisateur' => '1'
        ];
        ob_start();
        $result = $this->controller->register();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testRegisterPostMethodWithMismatchedPasswords()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'nom_utilisateur' => 'Test',
            'prenom_utilisateur' => 'User',
            'mail_utilisateur' => 'test@example.com',
            'mail_utilisateur_confirm' => 'test@example.com',
            'mdp_utilisateur' => 'Password123',
            'mdp_utilisateur_confirm' => 'DifferentPassword456', // Mot de passe différent
            'role_utilisateur' => '1'
        ];
        ob_start();
        $result = $this->controller->register();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testLoginPostMethodWithInvalidEmailFormat()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'mail_utilisateur' => 'invalid-email-format', // Format email invalide
            'mdp_utilisateur' => 'password123'
        ];
        ob_start();
        $result = $this->controller->login();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testLoginPostMethodWithInvalidCredentials()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'mail_utilisateur' => 'nonexistent@example.com', // Email qui n'existe pas
            'mdp_utilisateur' => 'wrongpassword'
        ];
        ob_start();
        $result = $this->controller->login();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testRegisterPostMethodWithInvalidCSRF()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['csrf_token'] = 'valid_token';
        $_POST = 
        [
            'csrf_token' => 'invalid_token', // Token CSRF invalide
            'nom_utilisateur' => 'Test',
            'prenom_utilisateur' => 'User',
            'mail_utilisateur' => 'test@example.com',
            'mail_utilisateur_confirm' => 'test@example.com',
            'mdp_utilisateur' => 'Password123',
            'mdp_utilisateur_confirm' => 'Password123',
            'role_utilisateur' => '1'
        ];
        ob_start();
        $result = $this->controller->register();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testLoginPostMethodWithInvalidCSRF()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['csrf_token'] = 'valid_token';
        $_POST = 
        [
            'csrf_token' => 'invalid_token', // Token CSRF invalide
            'mail_utilisateur' => 'test@example.com',
            'mdp_utilisateur' => 'Password123'
        ];
        ob_start();
        $result = $this->controller->login();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testRegisterPostMethodWithEmptyName()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'nom_utilisateur' => '', // Nom vide
            'prenom_utilisateur' => 'User',
            'mail_utilisateur' => 'test@example.com',
            'mail_utilisateur_confirm' => 'test@example.com',
            'mdp_utilisateur' => 'Password123',
            'mdp_utilisateur_confirm' => 'Password123',
            'role_utilisateur' => '1'
        ];
        ob_start();
        $result = $this->controller->register();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testRegisterPostMethodWithEmptyFirstName()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'nom_utilisateur' => 'Test',
            'prenom_utilisateur' => '', // Prénom vide
            'mail_utilisateur' => 'test@example.com',
            'mail_utilisateur_confirm' => 'test@example.com',
            'mdp_utilisateur' => 'Password123',
            'mdp_utilisateur_confirm' => 'Password123',
            'role_utilisateur' => '1'
        ];
        ob_start();
        $result = $this->controller->register();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testRegisterPostMethodWithEmptyEmail()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'nom_utilisateur' => 'Test',
            'prenom_utilisateur' => 'User',
            'mail_utilisateur' => '', // Email vide
            'mail_utilisateur_confirm' => '',
            'mdp_utilisateur' => 'Password123',
            'mdp_utilisateur_confirm' => 'Password123',
            'role_utilisateur' => '1'
        ];
        ob_start();
        $result = $this->controller->register();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testRegisterPostMethodWithEmptyPassword()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'nom_utilisateur' => 'Test',
            'prenom_utilisateur' => 'User',
            'mail_utilisateur' => 'test@example.com',
            'mail_utilisateur_confirm' => 'test@example.com',
            'mdp_utilisateur' => '', // Mot de passe vide
            'mdp_utilisateur_confirm' => '',
            'role_utilisateur' => '1'
        ];
        ob_start();
        $result = $this->controller->register();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testLoginPostMethodWithEmptyEmail()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'mail_utilisateur' => '', // Email vide
            'mdp_utilisateur' => 'Password123'
        ];
        ob_start();
        $result = $this->controller->login();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testLoginPostMethodWithEmptyPassword()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'mail_utilisateur' => 'test@example.com',
            'mdp_utilisateur' => '' // Mot de passe vide
        ];
        ob_start();
        $result = $this->controller->login();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testRegisterPostMethodWithMissingCSRF()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['csrf_token'] = 'valid_token';
        $_POST = 
        [
            // Token CSRF complètement manquant
            'nom_utilisateur' => 'Test',
            'prenom_utilisateur' => 'User',
            'mail_utilisateur' => 'test@example.com',
            'mail_utilisateur_confirm' => 'test@example.com',
            'mdp_utilisateur' => 'Password123',
            'mdp_utilisateur_confirm' => 'Password123',
            'role_utilisateur' => '1'
        ];
        ob_start();
        $result = $this->controller->register();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testLoginPostMethodWithMissingCSRF()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['csrf_token'] = 'valid_token';
        $_POST = 
        [
            // Token CSRF complètement manquant
            'mail_utilisateur' => 'test@example.com',
            'mdp_utilisateur' => 'Password123'
        ];
        ob_start();
        $result = $this->controller->login();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testRegisterPostMethodWithRoleZero()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'nom_utilisateur' => 'Test',
            'prenom_utilisateur' => 'User',
            'mail_utilisateur' => 'test@example.com',
            'mail_utilisateur_confirm' => 'test@example.com',
            'mdp_utilisateur' => 'Password123',
            'mdp_utilisateur_confirm' => 'Password123',
            'role_utilisateur' => '0' // Role 0 invalide
        ];
        ob_start();
        $result = $this->controller->register();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testRegisterPostMethodWithNegativeRole()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'nom_utilisateur' => 'Test',
            'prenom_utilisateur' => 'User',
            'mail_utilisateur' => 'test@example.com',
            'mail_utilisateur_confirm' => 'test@example.com',
            'mdp_utilisateur' => 'Password123',
            'mdp_utilisateur_confirm' => 'Password123',
            'role_utilisateur' => '-1' // Role négatif invalide
        ];
        ob_start();
        $result = $this->controller->register();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testRegisterPostMethodWithFloatRole()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'nom_utilisateur' => 'Test',
            'prenom_utilisateur' => 'User',
            'mail_utilisateur' => 'test@example.com',
            'mail_utilisateur_confirm' => 'test@example.com',
            'mdp_utilisateur' => 'Password123',
            'mdp_utilisateur_confirm' => 'Password123',
            'role_utilisateur' => '1.5' // Role décimal invalide
        ];
        ob_start();
        $result = $this->controller->register();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testRegisterPostMethodWithSpacesInEmail()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'nom_utilisateur' => 'Test',
            'prenom_utilisateur' => 'User',
            'mail_utilisateur' => ' test@example.com ', // Email avec espaces
            'mail_utilisateur_confirm' => ' test@example.com ',
            'mdp_utilisateur' => 'Password123',
            'mdp_utilisateur_confirm' => 'Password123',
            'role_utilisateur' => '1'
        ];
        ob_start();
        $result = $this->controller->register();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testLoginPostMethodWithSpacesInEmail()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'mail_utilisateur' => ' test@example.com ', // Email avec espaces
            'mdp_utilisateur' => 'Password123'
        ];
        ob_start();
        $result = $this->controller->login();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testRegisterPostMethodWithSpecialCharactersInName()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'nom_utilisateur' => 'Test@#$%', // Nom avec caractères spéciaux
            'prenom_utilisateur' => 'User123!',
            'mail_utilisateur' => 'test@example.com',
            'mail_utilisateur_confirm' => 'test@example.com',
            'mdp_utilisateur' => 'Password123',
            'mdp_utilisateur_confirm' => 'Password123',
            'role_utilisateur' => '1'
        ];
        ob_start();
        $result = $this->controller->register();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testRegisterPostMethodWithWeakPassword()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'nom_utilisateur' => 'Test',
            'prenom_utilisateur' => 'User',
            'mail_utilisateur' => 'test@example.com',
            'mail_utilisateur_confirm' => 'test@example.com',
            'mdp_utilisateur' => '123', // Mot de passe trop faible
            'mdp_utilisateur_confirm' => '123',
            'role_utilisateur' => '1'
        ];
        ob_start();
        $result = $this->controller->register();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testRegisterPostMethodWithNoUppercasePassword()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'nom_utilisateur' => 'Test',
            'prenom_utilisateur' => 'User',
            'mail_utilisateur' => 'test@example.com',
            'mail_utilisateur_confirm' => 'test@example.com',
            'mdp_utilisateur' => 'password123', // Pas de majuscule
            'mdp_utilisateur_confirm' => 'password123',
            'role_utilisateur' => '1'
        ];
        ob_start();
        $result = $this->controller->register();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testRegisterPostMethodWithNoDigitPassword()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'nom_utilisateur' => 'Test',
            'prenom_utilisateur' => 'User',
            'mail_utilisateur' => 'test@example.com',
            'mail_utilisateur_confirm' => 'test@example.com',
            'mdp_utilisateur' => 'Password', // Pas de chiffre
            'mdp_utilisateur_confirm' => 'Password',
            'role_utilisateur' => '1'
        ];
        ob_start();
        $result = $this->controller->register();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testRegisterPostMethodWithEmailWithoutAt()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'nom_utilisateur' => 'Test',
            'prenom_utilisateur' => 'User',
            'mail_utilisateur' => 'testexample.com', // Email sans @
            'mail_utilisateur_confirm' => 'testexample.com',
            'mdp_utilisateur' => 'Password123',
            'mdp_utilisateur_confirm' => 'Password123',
            'role_utilisateur' => '1'
        ];
        ob_start();
        $result = $this->controller->register();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testRegisterPostMethodWithEmailWithoutDomain()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'nom_utilisateur' => 'Test',
            'prenom_utilisateur' => 'User',
            'mail_utilisateur' => 'test@', // Email sans domaine
            'mail_utilisateur_confirm' => 'test@',
            'mdp_utilisateur' => 'Password123',
            'mdp_utilisateur_confirm' => 'Password123',
            'role_utilisateur' => '1'
        ];
        ob_start();
        $result = $this->controller->register();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testLoginPostMethodWithEmailWithoutAt()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'mail_utilisateur' => 'testexample.com', // Email sans @
            'mdp_utilisateur' => 'Password123'
        ];
        ob_start();
        $result = $this->controller->login();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }
}