<?php

require_once 'autoload.php';

/**
 * Contrôleur pour la gestion des mots de passe
 * Gère la réinitialisation et le changement de mots de passe
 */
class PasswordController 
{
    private $security;
    
    public function __construct() 
    {
        $this->security = new Security(true);
    }

    public function resetPassword()
    {
        if (UserConnectionUtils::isUserConnected())
        {
            // Code 400 (Bad request)
            http_response_code(400);
            ViewRenderer::error(new MessageErreur("Chargement de la page impossible", "Veuillez vous déconnecter pour réinitialiser votre mot de passe."));
            return false;
        }

        if (RequestUtils::isGetMethod())
        {
            // Génére le token CSRF
            $csrf_token = $this->security->genererCSRFToken();
            
            $viewRenderer = new ViewRenderer("View/ResetPassword.php", ['token_csrf' => $csrf_token]);
            $viewRenderer->render();
            
            return true;
        }
        else if (RequestUtils::isPostMethod()) 
        {
            // Définir un code HTTP 400 (Bad Request) par défaut
            http_response_code(400);

            // On renvoie du JSON par défaut (AJAX)
            header("Content-Type: application/json");

            // Vérification du token CSRF
            if (!$this->security->checkCSRFToken())
            {
                http_response_code(403);
                echo json_encode([
                    'status' => 'error',
                    'message' => "Token CSRF invalide."
                ]);
                return false;
            }

            // On vérifie que l'email a bien été fournie
            $email = $_POST['mail_utilisateur'] ?? null;
            if (empty($email)) 
            {
                // Réponse JSON avec le message d'erreur
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Adresse email invalide.'
                ]);
                return false;
            }

            // Vérifier le format de l'email
            if (!UserDataValidator::verifyEmailFormat($email)) 
            {
                // Réponse JSON avec le message d'erreur
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Le format de l\'adresse email est invalide.'
                ]);
                return false;
            }

            // Vérifier si l'email existe dans la base de données
            $userId = (new UserLogin($email))->getActiveUserIdWithEmail();

            // Si l'utilisateur n'existe pas, on ne renvoie pas d'info
            if ($userId === false) 
            {
                // Réponse JSON avec le message d'erreur
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Aucun compte valide associé à cet email.'
                ]);
                return false;
            }

            // Sauvegarder le token en base de données
            $token = new TokenResetPassword();
            if (!$token->setUserToken($userId)) 
            {
                // Réponse JSON avec le message d'erreur
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Erreur: Utilisateur invalide'
                ]);

                return false;
            }

            // On forme le lien suivant : localhost/{nom_du_site}/password/change?token={token_value}
            $reset_link = $_ENV['SITE_URL'] ?? "localhost";  
            $reset_link .= BASE_URL . "/password/change?token=" . $token->getToken();

            $emailSender = new EmailSender($email);
            $emailSender->setSubject(PasswordResetEmail::getSubject([]));
            $emailSender->setBody(PasswordResetEmail::getEmailContent(['resetLink' => $reset_link]));

            // On vérifie que l'email a bien été envoyé
            $mailEnvoye = $emailSender->sendMail();
            if ($mailEnvoye)
            {
                // Définir un code HTTP 200 (Succès)
                http_response_code(200);

                // Réponse JSON avec le message de succès
                $retourJson = 
                [
                    'status' => 'success',
                    'message' => 'Un email de réinitialisation a été envoyé.'
                ];
            }
            else 
            {
                // Réponse JSON avec le message d'erreur
                $retourJson = 
                [
                    'status' => 'error',
                    'message' => "'L'envoi de l'email a échoué."
                ];
            }

            // Si jamais on veut debug le token pour le reset de mdp
            $debugMailToken = filter_var($_ENV['DEBUG_MAIL_TOKEN'] ?? false, FILTER_VALIDATE_BOOLEAN);
            if ($debugMailToken)
            {
                $retourJson['debug'] = 'Debug: voici le lien pour reset le mot de passe : ' . $reset_link;
            }

            echo json_encode($retourJson);
            return $mailEnvoye;
        } 
        else
        {
            // Définir un code HTTP 405 (Method Not Allowed)
            http_response_code(405);

            // On setup le message d'erreur pour la vue
            ViewRenderer::error(new MessageErreur("Chargement de la page impossible", "Méthode non supportée"));
            return false;
        }
    }
      
      // Fonction pour changer le mot de passe
    public function changePassword()
    {
        // Définir un code HTTP 400 (Bad Request) par défaut
        http_response_code(400);

        // Vérifier si la requête est en POST
        if (RequestUtils::isPostMethod()) 
        {
            // On renvoie du JSON par défaut (AJAX)
            header("Content-Type: application/json");

            // Vérification du token CSRF
            if (!$this->security->checkCSRFToken())
            {
                http_response_code(403);
                echo json_encode([
                    'status' => 'error',
                    'message' => "Token CSRF invalide."
                ]);
                return false;
            }

            // On vérifie qu'on a bien le nouveau mot de passe
            $newPassword = $_POST['new_password'] ?? null;
            if (empty($newPassword)) 
            {
                // Réponse JSON avec le message d'erreur
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Mot de passe manquant.'
                ]);
                return false;
            }

            // On vérifie qu'on a bien le token associé
            $tokenValue = $_POST['token'] ?? null;

            // On crée l'objet de token
            $token = new TokenResetPassword($tokenValue);

            // On vérifie qu'on a bien un token valide. UserId est null si le result est false
            $stored = $token->storeUserIdWithValidToken();
            if ($stored === false)
            {
                // Réponse JSON avec le message d'erreur
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Token invalide ou expiré. Impossible de charger la page'
                    ]);
                return false;
            }

            // Vérifier la robustesse du mot de passe
            if (!UserDataValidator::verifyStrongPassword($newPassword)) 
            {
                // Réponse JSON avec le message d'erreur
                echo json_encode([
                    'status' => 'error',
                    'message' => "Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule et un chiffre."
                ]);
                return false;
            }

            // Mise à jour du mot de passe dans la base de donnéess
            if (UserCredentials::updateUserPasswordById($token->getUserId(), $newPassword)) {
                // Définir un code HTTP 200 (Succès) par défaut
                http_response_code(200);

                // Reset le token après modification
                $token->resetUserToken();

                // Réponse JSON avec le message de succès
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Votre mot de passe a été changé avec succès.',
                    'redirect' => BASE_URL . '/connexion'
                ]);
                return true;
            } 
            else 
            {
                // Réponse JSON avec le message d'erreur
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Une erreur est survenue, veuillez réessayer plus tard.'
                ]);
                return false;
            }
        } 
        else if (RequestUtils::isGetMethod()) 
        {
            // Affiche la page si la méthode n'est pas POST (en cas de simple visite de la page)
            $tokenValue = $_GET['token'] ?? null;

            $token = new TokenResetPassword($tokenValue);

            // On vérifie qu'on a bien un token valide
            $userId = $token->isTokenValid();
            if ($userId === false)
            {
                // On setup le message d'erreur pour la vue
                ViewRenderer::error(new MessageErreur("Chargement de la page impossible", "Token invalide ou expiré"));
                return false;
            }

            // Définir un code HTTP 200 (succès)
            http_response_code(200);

            // Génération du token csrf 
            $csrf_token = $this->security->genererCSRFToken();
            $viewRenderer = new ViewRenderer("View/ChangerPassword.php", ['token_csrf' => $csrf_token]);
            $viewRenderer->render();
            return true;
        }
        else
        {
            // Définir un code HTTP 405 (Method Not Allowed)
            http_response_code(405);

            // On setup le message d'erreur pour la vue
            ViewRenderer::error(new MessageErreur("Chargement de la page impossible", "Méthode non supportée"));
            return false;
        }
    }
}