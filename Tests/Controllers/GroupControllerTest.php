<?php

if (!defined("BASE_URL")) {
    define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'));
}

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../autoload.php';
require_once __DIR__ . '/../BaseTestClass.php';

class GroupControllerTest extends BaseTestClass
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
        $this->controller = new GroupController();
        // Créer un utilisateur admin pour les tests
        $this->testUser = $this->createTestUser(['role' => 1]); // 1 = Gérant (admin)
    }

    // ===== Tests pour addGroup() =====

    public function testAddGroupPostMethodWithoutAdmin()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'nom_groupe' => 'Test Group',
            'performeurs' => [
                [
                    'nom_performeur' => 'TestNom',
                    'prenom_performeur' => 'TestPrenom',
                    'role_performeur_id' => '1'
                ]
            ]
        ];
        // Pas d'utilisateur connecté (donc pas admin)
        ob_start();
        $result = $this->controller->addGroup();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testAddGroupGetMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->addGroup();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testAddGroupPostMethodWithInvalidCSRF()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['csrf_token'] = 'valid_token';
        $_POST = 
        [
            'csrf_token' => 'invalid_token', // Token CSRF invalide
            'nom_groupe' => 'Test Group',
            'performeurs' => [
                [
                    'nom_performeur' => 'TestNom',
                    'prenom_performeur' => 'TestPrenom',
                    'role_performeur_id' => '1'
                ]
            ]
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->addGroup();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testAddGroupPostMethodWithMissingCSRF()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['csrf_token'] = 'valid_token';
        $_POST = 
        [
            // Token CSRF manquant
            'nom_groupe' => 'Test Group',
            'performeurs' => [
                [
                    'nom_performeur' => 'TestNom',
                    'prenom_performeur' => 'TestPrenom',
                    'role_performeur_id' => '1'
                ]
            ]
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->addGroup();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testAddGroupPostMethodWithMissingNom()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            // 'nom_groupe' manquant
            'performeurs' => [
                [
                    'nom_performeur' => 'TestNom',
                    'prenom_performeur' => 'TestPrenom',
                    'role_performeur_id' => '1'
                ]
            ]
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->addGroup();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testAddGroupPostMethodWithEmptyNom()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'nom_groupe' => '', // Nom vide
            'performeurs' => [
                [
                    'nom_performeur' => 'TestNom',
                    'prenom_performeur' => 'TestPrenom',
                    'role_performeur_id' => '1'
                ]
            ]
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->addGroup();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    // ===== Tests pour apiList() =====

    public function testApiListWithoutAdmin()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        // Pas d'utilisateur connecté (donc pas admin)
        ob_start();
        $result = $this->controller->apiList();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testApiListPostMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->apiList();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testApiListPutMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->apiList();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testApiListDeleteMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->apiList();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    // ===== Tests avec utilisateur non-admin =====

    public function testAddGroupWithSecretaireRole()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'nom_groupe' => 'Test Group',
            'performeurs' => [
                [
                    'nom_performeur' => 'TestNom',
                    'prenom_performeur' => 'TestPrenom',
                    'role_performeur_id' => '1'
                ]
            ]
        ];
        // Créer un utilisateur secrétaire (role 2)
        $secretaireUser = $this->createTestUser(['role' => 2]);
        $this->loginTestUser($secretaireUser);
        ob_start();
        $result = $this->controller->addGroup();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testApiListWithSecretaireRole()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        // Créer un utilisateur secrétaire (role 2)
        $secretaireUser = $this->createTestUser(['role' => 2]);
        $this->loginTestUser($secretaireUser);
        ob_start();
        $result = $this->controller->apiList();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    // ===== Tests avec caractères spéciaux =====

    public function testAddGroupWithSpecialCharactersInNom()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'nom_groupe' => 'Test@#$%Group', // Nom avec caractères spéciaux
            'performeurs' => [
                [
                    'nom_performeur' => 'TestNom',
                    'prenom_performeur' => 'TestPrenom',
                    'role_performeur_id' => '1'
                ]
            ]
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->addGroup();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        // Selon la logique métier, cela pourrait réussir ou échouer
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('status', $responseData);
    }

    public function testAddGroupWithNumericNom()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'nom_groupe' => '12345', // Nom numérique
            'performeurs' => [
                [
                    'nom_performeur' => 'TestNom',
                    'prenom_performeur' => 'TestPrenom',
                    'role_performeur_id' => '1'
                ]
            ]
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->addGroup();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('status', $responseData);
    }

    public function testAddGroupWithInvalidPerformeurRoleId()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'nom_groupe' => 'Test Group',
            'performeurs' => [
                [
                    'nom_performeur' => 'TestNom',
                    'prenom_performeur' => 'TestPrenom',
                    'role_performeur_id' => 'invalid_role' // Role invalide
                ]
            ]
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->addGroup();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        // Le contrôleur ignore les performeurs avec des rôles invalides mais crée quand même le groupe
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('status', $responseData);
    }

    public function testAddGroupWithNegativePerformeurRoleId()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'nom_groupe' => 'Test Group',
            'performeurs' => [
                [
                    'nom_performeur' => 'TestNom',
                    'prenom_performeur' => 'TestPrenom',
                    'role_performeur_id' => '-1' // Role négatif
                ]
            ]
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->addGroup();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        // Le contrôleur ignore les performeurs avec des rôles invalides
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('status', $responseData);
    }

    // ===== Tests pour deleteGroupSafe() =====

    public function testDeleteGroupSafeWithoutAdmin()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'groupe_id' => '1'
        ];
        // Pas d'utilisateur connecté
        ob_start();
        $result = $this->controller->deleteGroupSafe();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testDeleteGroupSafeGetMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->deleteGroupSafe();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testDeleteGroupSafeMissingGroupId()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken
            // 'groupe_id' manquant
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->deleteGroupSafe();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testDeleteGroupSafeInvalidGroupId()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'groupe_id' => 'invalid_id' // ID invalide
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->deleteGroupSafe();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    // ===== Tests pour addPerformeur() =====

    public function testAddPerformeurWithoutAdmin()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'nom_performeur' => 'TestNom',
            'prenom_performeur' => 'TestPrenom',
            'role_performeur_id' => '1'
        ];
        // Pas d'utilisateur connecté
        ob_start();
        $result = $this->controller->addPerformeur();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testAddPerformeurMissingNom()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            // 'nom_performeur' manquant
            'prenom_performeur' => 'TestPrenom',
            'role_performeur_id' => '1'
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->addPerformeur();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testAddPerformeurMissingPrenom()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'nom_performeur' => 'TestNom',
            // 'prenom_performeur' manquant
            'role_performeur_id' => '1'
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->addPerformeur();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

    public function testAddPerformeurMissingRoleId()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_POST = 
        [
            'csrf_token' => $csrfToken,
            'nom_performeur' => 'TestNom',
            'prenom_performeur' => 'TestPrenom'
            // 'role_performeur_id' manquant
        ];
        $this->loginTestUser($this->testUser);
        ob_start();
        $result = $this->controller->addPerformeur();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);
        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
    }

}