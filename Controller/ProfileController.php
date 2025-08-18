<?php

require_once 'autoload.php';

class ProfileController
{
    private $security;
    
    public function __construct()
    {
        $this->security = new Security(true);
    }
    
    public function index()
    {
        if (RequestUtils::isGetMethod() == false)
        {
            http_response_code(405);
            ViewRenderer::error(new MessageErreur("Chargement de la page impossible", "La méthode n'est pas supportée."));
            return false;
        }

        if (UserConnectionUtils::isUserConnected()) 
        {
            // Affectation des variables suivantes pour la vue ModifierProfil.php
            $userNom = $_SESSION['user']['nom'] ?? 'Nom introuvable';
            $userPrenom = $_SESSION['user']['prenom'] ?? 'Prénom introuvable';
            $userEmail = $_SESSION['user']['email'] ?? 'Email introuvable';

            $viewData = 
            [
                'token_csrf' => $this->security->genererCSRFToken(),
                'nom' => $userNom,
                'prenom' => $userPrenom,
                'email' => $userEmail
            ];
            $viewRenderer = new ViewRenderer("View/Secretaire/ModifierProfil.php", $viewData);
            $viewRenderer->render();
            return true;
        } 
        else 
        {
            http_response_code(400);
            ViewRenderer::error(new MessageErreur("Chargement de la page impossible", "Veuillez vous connecter pour changer votre profil."));
            return false;
        }
    }

    // Fonction pour modifier le profil de l'utilisateur
    public function updateProfile()
    {
        header("Content-Type: application/json");

        if (RequestUtils::isPostMethod() == false)
        {
            http_response_code(405);
            echo json_encode(
            [
                'status' => 'error',
                'message' => "La méthode n'est pas supportée. Utilisez du POST"
            ]);
            return false;
        }

        // Vérifier que le user est connecté (secretaire + gerant)
        if (UserConnectionUtils::isUserConnected() == false) 
        {
            http_response_code(400);
            echo json_encode(
            [
                'status' => 'error',
                'message' => "Veuillez vous connecter pour changer votre profil."
            ]);
            return false;
        }

        http_response_code(400);
        
        // Vérification du token CSRF
        if (!$this->security->checkCSRFToken()) 
        {
            echo json_encode([
                'status' => 'error',
                'message' => "Token CSRF invalide."
            ]);
            return false;
        }

        // On récupère le userId qui est forcément valide car on a vérifié qu'on était connecté
        $userId = UserConnectionUtils::getConnectedUserId();

        $nom = $_POST['nom_utilisateur'] ?? null;
        $prenom = $_POST['prenom_utilisateur'] ?? null;
        $email = $_POST['mail_utilisateur'] ?? null;
        $mot_de_passe = $_POST['mdp_utilisateur'] ?? null;

        $champsAChanger = [];
        $params = [];

        if (!empty($nom)) 
        {
            if (!UserDataValidator::verifyNameFormat($nom)) 
            {
                echo json_encode([
                    'status' => 'error',
                    'message' => "Le format du nom est invalide."
                ]);
                return false;
            }

            $champsAChanger[] = "nom_utilisateur = :nom_utilisateur";
            $params[':nom_utilisateur'] = $nom;
        }
        
        if (!empty($prenom)) 
        {
            if (!UserDataValidator::verifyNameFormat($prenom)) 
            {
                echo json_encode([
                    'status' => 'error',
                    'message' => "Le format du prénom est invalide"
                ]);
                return false;
            }

            $champsAChanger[] = "prenom_utilisateur = :prenom_utilisateur";
            $params[':prenom_utilisateur'] = $prenom;
        }

        if (!empty($email)) 
        {
            if (!UserDataValidator::verifyEmailFormat($email)) 
            {
                echo json_encode([
                    'status' => 'error',
                    'message' => "Le format de l'email est invalide"
                ]);
                return false;
            }

            if (UserCredentials::checkEmailAvailable($email)) 
            {
                echo json_encode([
                    'status' => 'error',
                    'message' => "Erreur : L'email est déjà utilisée"
                ]);
                return false;
            }

            $champsAChanger[] = "mail_utilisateur = :mail_utilisateur";
            $params[':mail_utilisateur'] = $email;
        }

        if (!empty($mot_de_passe)) 
        {
            if (!UserDataValidator::verifyStrongPassword($mot_de_passe)) 
            {
                echo json_encode([
                    'status' => 'error',
                    'message' => "Erreur : Le nouveau mot de passe n'est pas valide. Il faut au moins 8 caractères avec une minuscule, une majuscule et un chiffre"
                ]);
                return false;
            }

            $champsAChanger[] = "mdp_utilisateur = :mdp_utilisateur";
            $params[':mdp_utilisateur'] = password_hash($mot_de_passe, PASSWORD_DEFAULT);
        }

        if (empty($champsAChanger)) 
        {
            echo json_encode([
                'status' => 'error',
                'message' => "Aucun champ à modifier n'est fourni"
            ]);
            return false;
        }

        $userProfile = new UserProfile($userId);
        $result = $userProfile->changeProfile($champsAChanger, $params);

        if ($result) 
        {
            http_response_code(200);
            if (!empty($prenom))
            {
                $_SESSION['user']['prenom'] = $prenom;
            }
            if (!empty($nom))
            {
                $_SESSION['user']['nom'] = $nom;
            }
            if (!empty($email))
            {
                $_SESSION['user']['mail'] = $email;
            }
            echo json_encode([
                'status' => 'success',
                'message' => 'Votre profil a été changé avec succès.'
            ]);
        } 
        else 
        {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Une erreur est survenue, veuillez réessayer.'
            ]);
        }

        return $result;
    }
}