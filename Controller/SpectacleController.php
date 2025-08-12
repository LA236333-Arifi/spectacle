<?php

class SpectacleController
{
    private $security;

    public function __construct()
    {
        $security = new Security(true);
    }

    private function check_POST_Admin_CSRF()
    {
        if (RequestUtils::isPostMethod() == false)
        {
            // Définir un code HTTP 405 (Method Not Allowed)
            http_response_code(405);
            ViewRenderer::error(new MessageErreur("Chargement de la page impossible", "Méthode non supportée"));
            return false;
        }

        if (UserConnectionUtils::isAdminConnected() == false)
        {
            http_response_code(400);
            ViewRenderer::error(new MessageErreur("Chargement de la page impossible", "Il faut être connecté en tant qu'admin pour visualiser cette page."));
            return false;
        }

        if ($this->security->checkCSRFToken() == false)
        {
            header('Content-Type: application/json');
            http_response_code(403);
                echo json_encode([
                    'status' => 'error',
                    'message' => "Token CSRF invalide."
                ]);
            return false;
        }

        return true;
    }

    public function ajouter()
    {
        if (RequestUtils::isGetMethod())
        {
            if (UserConnectionUtils::isAdminConnected() == false)
            {
                http_response_code(400);
                ViewRenderer::error(new MessageErreur("Chargement de la page impossible", "Il faut être connecté en tant qu'admin pour visualiser cette page."));
                return false;
            }

            $viewData = ['token_csrf' => $this->security->genererCSRFToken()];
            $viewRenderer = new ViewRenderer("View/Spectacle.php", $viewData);
        }
        else if ($this->check_POST_Admin_CSRF() == false)
        {
            return false;
        }

        http_response_code(400);
        header('Content-Type: application/json');

        $spectacle = new Spectacle();
        if ($spectacle->addSpectacle($_POST) == false)
        {
            $errors = $spectacle->getErrors();
            echo json_encode([
                    'status' => 'error',
                    'message' => "Le spectacle n'a pas pu être ajouté pour ces raisons: " . implode(", ", $errors)
                ]);

            return false;
        }

        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'message' => "Le spectacle a été ajouté avec succès."
        ]);

        return true;
    }

        public function modifier()
    {
        if ($this->check_POST_Admin_CSRF() == false)
        {
            return false;
        }

        http_response_code(400);
        header('Content-Type: application/json');

        $spectacle = new Spectacle();
        if ($spectacle->modifySpectacle($_POST) == false)
        {
            echo json_encode([
                    'status' => 'error',
                    'message' => "Le spectacle n'a pas pu être modifié."
                ]);
            return false;
        }

        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'message' => "Le spectacle a été modifié avec succès."
        ]);

        return true;
    }

        public function cloturer()
    {
        if ($this->check_POST_Admin_CSRF() == false)
        {
            return false;
        }

        http_response_code(400);
        header('Content-Type: application/json');

        $spectacle = new Spectacle();
        if ($spectacle->cloturerSpectacle() == false)
        {
            echo json_encode([
                    'status' => 'error',
                    'message' => "Le spectacle n'a pas pu être cloturé."
                ]);
            return false;
        }

        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'message' => "Le spectacle a été cloturé avec succès."
        ]);

        return true;
    }

     /**
     * Supprime de la DB le spectacle ainsi que les séances et auteur/metteur en scène
     * Utilisé si le spectacle est complètement annulé et donc n'a plus lieu d'être.
     * 
     * Pour l'instant, on n'utilise pas cette fonctionnalité pour conserver l'intègrité 
     * des données (mêmes si en théorie, les données devraient rester intègres)
     */
    public function supprimer()
    {
        if ($this->check_POST_Admin_CSRF() == false)
        {
            return false;
        }

        header('Content-Type: application/json');

        $spectacle = new Spectacle();
        if ($spectacle->deleteSpectacle() == false)
        {
            http_response_code(400);
            echo json_encode([
                    'status' => 'error',
                    'message' => "Le spectacle n'a pas pu être supprimé."
                ]);
            return false;
        }

        echo json_encode([
            'status' => 'success',
            'message' => "Le spectacle a été supprimé avec succès."
        ]);

        return true;
    }


}    
