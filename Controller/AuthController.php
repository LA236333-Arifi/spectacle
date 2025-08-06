<?php

require_once 'Model/Utils/RequestUtils.php';
require_once 'Model/EmailSender.php';
require_once 'Model/Role.php';
require_once 'Model/User/UserCredentials.php';
require_once 'Model/User/UserLogin.php';
require_once 'Templates/WelcomeEmail.php';

/**
 * Contrôleur d'authentification moderne
 * Gère la connexion, inscription, et réinitialisation de mot de passe
 */
class AuthController 
{
    private $security;
    
    public function __construct() 
    {
        // On crée notre session sécurisée via le constructeur
        $this->security = new Security(true);
    }

    public function register()
    {
        // Redirection si déjà connecté
        if (UserConnectionUtils::isUserConnected()) 
        {
            header('Location: /dashboard');
            return false;
        }

        // Si c'est du GET, alors on affiche seulement la page d'inscription avec le token csrf
        if (RequestUtils::isGetMethod())
        {
            $csrfToken = $this->security->genererCSRFToken();

            $viewRenderer = new ViewRenderer("View/Register.php", ['token_csrf' => $csrfToken]);
            $viewRenderer->render();

            return true;
        }
        else if (RequestUtils::isPostMethod())
        {
            header('Content-Type: application/json');

            // Vérification du token CSRF
            if (!$this->security->checkCSRFToken($_POST['csrf_token'] ?? '')) 
            {
                http_response_code(403);
                echo json_encode([
                    'status' => 'error',
                    'message' => "Token CSRF invalide."
                ]);
                return false;
            }

            // Définir un code HTTP 400 (Bad Request) par défaut
            http_response_code(400);

            // On recupere les valeur du formulaire a l'aide des variable POST
            $nom = $_POST['nom_utilisateur'] ?? null;
            $prenom = $_POST['prenom_utilisateur'] ?? null;
            $email  = $_POST['mail_utilisateur'] ?? null;
            $emailConfirm  = $_POST['mail_utilisateur_confirm'] ?? null;
            $password = $_POST['mdp_utilisateur'] ?? null;
            $passwordConfirm = $_POST['mdp_utilisateur_confirm'] ?? null;
            $role = $_POST['role_utilisateur'] ?? null;

            // On map le complément de message d'erreur (le label) à la variable associée
            $champs = 
            [
                'Nom'                        => $nom,
                'Prenom'                     => $prenom,
                'Email'                      => $email,
                'Confirmation Email'         => $emailConfirm,
                'Mot de passe'               => $password,
                'Confirmation Mot de passe'  => $passwordConfirm,
            ];

            $champsManquants = [];

            // Pour chacune des entrées du map, 
            // si la valeur est empty alors on rajoute le label dans les champs manquants
            foreach ($champs as $label => $valeur) 
            {
                if (empty($valeur)) 
                {
                    $champsManquants[] = $label;
                }
            }

            // Si on a des champs manquants, alors on envoie le message d'erreur au client avec ce qu'il doit remplir
            if (empty($champsManquants) == false) 
            {
                $messageErreur = "Veuillez remplir les champs suivants : " . implode(', ', $champsManquants);

                echo json_encode([
                    'status' => 'error',
                    'message' => $messageErreur
                ]);

                return false;
            }

            // On filtre le role pour n'accepter que les nombres (qui sont sous forme de string dans le $_POST)
            $role = filter_var($role, FILTER_VALIDATE_INT);
            
            // Si false est la valeur retournée, alors le format du role est incorrect
            if ($role === false)
            {
                echo json_encode([ 
                    'status' => 'error', 
                    'message' => "Le format du role choisi est invalide."
                ]);

                return false;
            }

            if (Role::isRoleValid($role) == false)
            {
               echo json_encode([ 
                    'status' => 'error', 
                    'message' => "Le role choisi est invalide."
                ]);
                
                return false;
            }

            // On crée nos objets qui manipulent l'aspect "User"
            $userData = new UserData($nom, $prenom, $email, $password, $role);
            $userCredentials = new UserCredentials($userData);

            // On vérifie que les données sont correctes et saines, on recoit une enum avec la 1ère erreur rencontrée
            $registerStatus = $userCredentials->verifyRegisterData();

            // On setup un message d'erreur *vide* qui sera set uniquement en cas d'erreur
            $errorMessage = "";
            switch ($registerStatus)
            {
                // Tout va bien si le status est "Valid"
                case RegisterStatus::Valid:
                    break;
                case RegisterStatus::MauvaisNomFormat:
                    $errorMessage = "Le format du nom est incorrect.";
                    break;
                case RegisterStatus::MauvaisPrenomFormat:
                    $errorMessage = "Le format du prénom est incorrect.";
                    break;
                case RegisterStatus::MauvaisMailFormat:
                    $errorMessage = "Le format de l'email est incorrect.";
                    break;
                case RegisterStatus::MauvaisPasswordFormat:
                    $errorMessage = "Le format du mot de pase est incorrect. Il faut qu'il détienne minimum 8 caractères parmi lesquels une miniscule, une majuscule et un chiffre.";
                    break;
                case RegisterStatus::EmailDejaPris:
                    $errorMessage = "L'email utilisé pour l'inscription est déjà pris.";
                    break;
            }

            // Si le message d'erreur n'est pas vide, alors on envoie l'erreur au client avec le dit message
            if (empty($errorMessage) == false)
            {
                echo json_encode([
                    'status' => 'error',
                    'message' => $errorMessage
                ]);
                
                return false;
            }

            // On vérifie la confirmation des champs (ici email) après les champs eux-mêmes pour vérifier qu'ils soient conformes
            if ($email !== $emailConfirm)
            {
                echo json_encode([
                    'status' => 'error',
                    'message' => "Les adresses email ne correspondent pas."
                ]);
                
                return false;
            }

            // On vérifie la confirmation des champs (ici password) après les champs eux-mêmes pour vérifier qu'ils soient conformes
            if ($password !== $passwordConfirm)
            {
                echo json_encode([
                    'status' => 'error',
                    'message' => "Les mots de passe ne correspondent pas."
                ]);
                
                return false;
            }

            // On tente d'insérer le user dans la DB 
            if ($userCredentials->insertUser() == false)
            {
                // Définir un code HTTP 500 (Internal Server Error)
                http_response_code(500);

                echo json_encode([
                    'status' => 'error',
                    'message' => "L'inscription a échoué. Veuillez réessayer plus tard."
                ]);
                
                return false;
            }

            // Définir un code HTTP 200 pour le succès
            http_response_code(200);

            // On setup le EmailSender pour envoyer l'email de bienvenue
            $emailSender = new EmailSender($email);

            // On ajoute le sujet et le body qui viennent du template "WelcomeEmail"
            $emailSender->setSubject(WelcomeEmail::getSubject([]));
            $emailSender->setBody(WelcomeEmail::getEmailContent(['username' => $prenom]));

            if ($emailSender->sendMail())
            {
                $message = "Votre demande d'inscription a bien été envoyée. Elle sera examinée par un administrateur.";
            }
            else
            {
                $message = "Votre demande d'inscription a bien été envoyée. Elle sera examinée par un administrateur. Cependant l'email de bienvenue n'a pas pu être envoyé.";
            }

            // Réponse JSON avec le message de succès
            echo json_encode([
                'status' => 'success',
                'message' => $message,
                'redirect' => BASE_URL . '/login',
                'crossmessage' => "yes"
            ]);

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

    public function login()
    {
        // Redirection si déjà connecté
        if (UserConnectionUtils::isUserConnected()) 
        {
            header('Location:' . BASE_URL . '/dashboard');
            return false;
        }

        // Si c'est du GET, alors on affiche seulement la page de connexion avec le token csrf
        if (RequestUtils::isGetMethod())
        {
            // Génération du token CSRF pour le formulaire du login
            $csrfToken = $this->security->genererCSRFToken();

            $viewRenderer = new ViewRenderer("View/Login.php", ['token_csrf' => $csrfToken]);
            $viewRenderer->render();
            return true;
        }
        else if (RequestUtils::isPostMethod())
        {
            header('Content-Type: application/json');

            // Vérification du token CSRF
            if (!$this->security->checkCSRFToken($_POST['csrf_token'] ?? '')) 
            {
                http_response_code(403);
                echo json_encode([
                    'status' => 'error',
                    'message' => "Token CSRF invalide."
                ]);
                return false;
            }

            // Définir un code HTTP 400 (Bad Request) par défaut
            http_response_code(400);

            // Recupere les valeur du formulaire a l'aide des variable POST
            $email = $_POST['mail_utilisateur'] ?? null;
            $password = $_POST['mdp_utilisateur'] ?? null;

            // On verifie si le formulaire est complet
            if (empty($email) || empty($password)) 
            {
                // Réponse JSON avec le message d'erreur
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Formulaire incomplet. Veuillez remplir les champs du mail et du mot de passe.'
                ]);

                return false;
            }

            $userLogin = new UserLogin($email);
            if ($userLogin->verifyUserMail() == false)
            {
                // Réponse JSON avec le message d'erreur
                echo json_encode([
                    'status' => 'error',
                    'message' => "Le format de l'email est incorrect. Veuillez suivre l'annotation suivante: abc@def.xyz"
                ]);
                
                return false;
            } 

            // On vérifie que l'utilisateur a rentré le bon mot de passe 
            if ($userLogin->verifyActiveUserPassword($password) == false)
            {
                // Réponse JSON avec le message d'erreur.
                // on reste flou sur la raison de l'échec de connexion pour la sécurité.
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Les informations de connexion sont incorrectes.'
                ]);
                
                return false;
            }

            // On crée la session pour l'utilisateur
            if ($userLogin->createUserSession() == false)
            {
                // Définir un code HTTP 500 (Internal Server Error)
                http_response_code(500);

                echo json_encode([
                    'status' => 'error',
                    'message' => 'Echec de la connexion. Veuillez réessayer plus tard.'
                ]);

                return false;
            }

            // On définit un code HTTP 200 pour le succès
            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'message' => 'Vous êtes connectés',
                'redirect' => BASE_URL . "/dashboard",
                'crossmessage' => 'yes'
            ]);

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

