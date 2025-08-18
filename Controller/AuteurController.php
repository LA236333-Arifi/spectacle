<?php

class AuteurController
{
    private $security;

    public function __construct()
    {
        $this->security = new Security(true);
    }

    public function addAuteur()
    {
        header('Content-Type: application/json');

        if (!UserConnectionUtils::isAdminConnected()) 
        {
            // Définir un code HTTP 405 (Unauthorized)
            http_response_code(405);

            // On setup le message d'erreur pour la vue
            echo json_encode(
            [
                'status' => 'error',
                'message' => "Il faut se connecter en tant qu'administrateur pour utiliser ce endpoint"
            ]);

            return false;
        }

        if (RequestUtils::isPostMethod() == false)
        {
            http_response_code(405);
            echo json_encode
            ([
                'status' => 'error',
                'message' => "Seule la méthode GET est supportée",
            ]);
            return false;
        }

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

        http_response_code(400);

        $nom = $_POST['nom'] ?? null;
        $prenom = $_POST['prenom'] ?? null;

        if (empty($nom) || empty($prenom))
        {
            echo json_encode([
                'status' => 'error',
                'message' => "Champs nom ou prénom manquants."
            ]);
            return false;
        }

        $auteur = new Auteur($nom, $prenom);
        $result = $auteur->addAuteur();
        if ($result == false)
        {
            echo json_encode([
                'status' => 'error',
                'message' => "L'auteur n'a pas pu être ajouté."
            ]);
            return false;
        }

        http_response_code(200);
        echo json_encode([
                'status' => 'success',
                'message' => "L'auteur a été ajouté avec succès"
            ]);
        return true;
    }

    public function addMetteurScene()
    {
        header('Content-Type: application/json');

        // Vérifier si l'utilisateur est un administrateur
        if (!UserConnectionUtils::isAdminConnected()) 
        {
            // Définir un code HTTP 405 (Unauthorized)
            http_response_code(405);

            // Message d'erreur
            echo json_encode([
                'status' => 'error',
                'message' => "Il faut se connecter en tant qu'administrateur pour utiliser ce endpoint"
            ]);

            return false;
        }

        // Vérifier si la méthode HTTP est bien GET
        if (RequestUtils::isPostMethod() == false)
        {
            http_response_code(405);
            echo json_encode([
                'status' => 'error',
                'message' => "Seule la méthode GET est supportée",
            ]);
            return false;
        }

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

        http_response_code(400);

        // Récupérer les données de la requête
        $nom = $_POST['nom'] ?? null;
        $prenom = $_POST['prenom'] ?? null;

        // Vérifier que les champs nécessaires sont remplis
        if (empty($nom) || empty($prenom))
        {
            echo json_encode([
                'status' => 'error',
                'message' => "Champs nom ou prénom manquants."
            ]);
            return false;
        }

        // Créer un objet Auteur pour ajouter un metteur en scène
        $metteurScene = new Auteur($nom, $prenom);

        // Appeler la méthode pour ajouter le metteur en scène
        $result = $metteurScene->addMetteurScene();

        // Si l'ajout a échoué
        if ($result == false)
        {
            echo json_encode([
                'status' => 'error',
                'message' => "Le metteur en scène n'a pas pu être ajouté. Il est probable qu'il soit déjà dans la base de données."
            ]);
            return false;
        }

        // Réponse si tout se passe bien
        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'message' => "Le metteur en scène a été ajouté avec succès"
        ]);
        return true;
    }

    public function apiListAuteurs()
    {
        header('Content-Type: application/json');

        if (!UserConnectionUtils::isAdminConnected()) 
        {
            // Définir un code HTTP 405 (Unauthorized)
            http_response_code(405);

            // On setup le message d'erreur pour la vue
            echo json_encode(
                [
                    'status' => 'error',
                    'message' => "Il faut se connecter en tant qu'administrateur pour utiliser ce endpoint"
                ]);
            return false;
        }

        if (RequestUtils::isGetMethod() == false)
        {
            // Définir un code HTTP 403 (Method Not Allowed)
            http_response_code(403);

            // On setup le message d'erreur pour la vue
            echo json_encode(
                [
                    'status' => 'error',
                    'message' => "La méthode n'est pas supportée, veuillez utiliser du GET"
                ]);
            return false;
        }

        $auteurs = Auteur::getAllAuteurs();
        echo json_encode($auteurs);
        return true;
    }

    public function apiListMetteurs()
    {
        header('Content-Type: application/json');

        if (!UserConnectionUtils::isAdminConnected()) 
        {
            // Définir un code HTTP 405 (Unauthorized)
            http_response_code(405);

            // On setup le message d'erreur pour la vue
            echo json_encode(
                [
                    'status' => 'error',
                    'message' => "Il faut se connecter en tant qu'administrateur pour utiliser ce endpoint"
                ]);
            return false;
        }

        if (RequestUtils::isGetMethod() == false)
        {
            // Définir un code HTTP 403 (Method Not Allowed)
            http_response_code(403);

            // On setup le message d'erreur pour la vue
            echo json_encode(
                [
                    'status' => 'error',
                    'message' => "La méthode n'est pas supportée, veuillez utiliser du GET"
                ]);
            return false;
        }

        $auteurs = Auteur::getAllMetteurs();
        echo json_encode($auteurs);
        return true;
    }
}