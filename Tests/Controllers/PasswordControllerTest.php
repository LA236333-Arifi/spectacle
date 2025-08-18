<?php

if (!defined("BASE_URL")) {
    define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'));
}

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../autoload.php';
require_once __DIR__ . '/../BaseTestClass.php';

class PasswordControllerTest extends BaseTestClass
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
        $this->controller = new PasswordController();
        // Créer un utilisateur pour les tests
        $this->testUser = $this->createTestUser(['role' => 1]);
    }

    // ===== Tests pour resetPassword() (demande de reset) =====

    public function testResetPasswordPostMethodWithMissingEmail()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken
            // 'mail_utilisateur' manquant
        ];
        ob_start();
        $result = $this->controller->resetPassword();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testResetPasswordPostMethodWithEmptyEmail()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'mail_utilisateur' => '' // Email vide
        ];
        ob_start();
        $result = $this->controller->resetPassword();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testResetPasswordPostMethodWithInvalidEmail()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'mail_utilisateur' => 'invalid-email-format' // Format email invalide
        ];
        ob_start();
        $result = $this->controller->resetPassword();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testResetPasswordPostMethodWithInvalidCSRF()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['csrf_token'] = 'valid_token';
        $_POST = 
        [
            'csrf_token' => 'invalid_token', // Token CSRF invalide
            'mail_utilisateur' => 'test@example.com'
        ];
        ob_start();
        $result = $this->controller->resetPassword();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testResetPasswordGetMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        ob_start();
        $result = $this->controller->resetPassword();
        $output = ob_get_clean();
        // Pour GET, devrait afficher la vue avec le token CSRF
        $this->assertTrue($result);
    }

    public function testResetPasswordPostMethodWithNonexistentEmail()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'mail_utilisateur' => 'nonexistent@example.com' // Email qui n'existe pas
        ];
        ob_start();
        $result = $this->controller->resetPassword();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    // ===== Tests pour changePassword() =====

    public function testChangePasswordPostMethodWithMissingPassword()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'token' => 'some_token'
            // 'new_password' manquant
        ];
        ob_start();
        $result = $this->controller->changePassword();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testChangePasswordPostMethodWithMissingToken()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            // 'token' manquant
            'new_password' => 'NewPassword123'
        ];
        ob_start();
        $result = $this->controller->changePassword();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testChangePasswordPostMethodWithEmptyPassword()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'token' => 'some_token',
            'new_password' => '' // Mot de passe vide
        ];
        ob_start();
        $result = $this->controller->changePassword();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testChangePasswordPostMethodWithWeakPassword()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'token' => 'some_token',
            'new_password' => '123' // Mot de passe trop faible
        ];
        ob_start();
        $result = $this->controller->changePassword();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testChangePasswordPostMethodWithInvalidToken()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'token' => 'invalid_token', // Token invalide
            'new_password' => 'NewPassword123'
        ];
        ob_start();
        $result = $this->controller->changePassword();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testChangePasswordPostMethodWithInvalidCSRF()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['csrf_token'] = 'valid_token';
        $_POST = 
        [
            'csrf_token' => 'invalid_token', // Token CSRF invalide
            'token' => 'some_token',
            'new_password' => 'NewPassword123'
        ];
        ob_start();
        $result = $this->controller->changePassword();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testChangePasswordGetMethodWithToken()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['token'] = 'some_token';
        ob_start();
        $result = $this->controller->changePassword();
        $output = ob_get_clean();
        // Pour GET avec token, devrait échouer car token invalide dans les tests
        $this->assertFalse($result);
    }

    public function testChangePasswordGetMethodWithoutToken()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        // Pas de token dans $_GET
        ob_start();
        $result = $this->controller->changePassword();
        $output = ob_get_clean();
        // Devrait échouer sans token
        $this->assertFalse($result);
    }

    // ===== Tests avec méthodes non supportées =====

    public function testResetPasswordPutMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        ob_start();
        $result = $this->controller->resetPassword();
        $output = ob_get_clean();
        $this->assertFalse($result);
    }

    public function testChangePasswordDeleteMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        ob_start();
        $result = $this->controller->changePassword();
        $output = ob_get_clean();
        $this->assertFalse($result);
    }

    // ===== Tests avec caractères spéciaux =====

    public function testResetPasswordWithEmailContainingSpaces()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'mail_utilisateur' => ' test@example.com ' // Email avec espaces
        ];
        ob_start();
        $result = $this->controller->resetPassword();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testChangePasswordWithPasswordContainingOnlySpaces()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'token' => 'some_token',
            'new_password' => '        ' // Mot de passe avec seulement des espaces
        ];
        ob_start();
        $result = $this->controller->changePassword();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

}