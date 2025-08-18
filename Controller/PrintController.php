<?php

require_once 'autoload.php';

class PrintController
{
    /**
     * GET /spectacle/print
     */
    public function index()
    {
        if (UserConnectionUtils::isUserConnected() == false)
        {
            return false;
        }

        if (RequestUtils::isGetMethod() == false)
        {
            return false;
        }

        if (isset($_GET['id']))
        {
            $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
            if ($id !== false)
            {
                return $this->printSpectacle($id);
            }
        }

        $viewRenderer = new ViewRenderer("View/Secretaire/PubliciteSpectacle.php", []);
        $viewRenderer->render();
        return true;
     }  

      /**
     * GET /spectacle/print?id={id}
     */
    private function printSpectacle(int $id)
    {
        header('Content-Type: application/json');

        // L'admin et le secretaire peut générer un pdf, donc on utilise "isUserConnected()"
        if (UserConnectionUtils::isUserConnected() == false)
        {
            // Définir un code HTTP 405 (Unauthorized)
            http_response_code(405);
            echo json_encode(
            [
                'status' => 'error',
                'message' => "Il faut se connecter en tant que secrétaire ou gérant pour accéder à cette API"
            ]);
            return false;
        }

        if (RequestUtils::isGetMethod() == false)
        {
            http_response_code(403);
            echo json_encode(
            [
                'status' => 'error',
                'message' => "La méthode n'est pas supportée, veuillez utiliser du GET"
            ]);
            return false;
        }

        if (empty($id))
        {
            echo json_encode(['status' => 'error', 'message' => "ID spectacle invalide"]);
            return false;
        }

        SpectaclePrinter::generateSingleSpectaclePDF((int)$id);
        return true;
    }
     

     /**
     * GET /spectacle/print/all
     */
    public function printList()
    {
        header('Content-Type: application/json');

        // L'admin et le secretaire peut générer un pdf, donc on utilise "isUserConnected()"
        if (UserConnectionUtils::isUserConnected() == false)
        {
            // Définir un code HTTP 405 (Unauthorized)
            http_response_code(405);
            echo json_encode(
            [
                'status' => 'error',
                'message' => "Il faut se connecter en tant que secrétaire ou gérant pour accéder à cette API"
            ]);
            return false;
        }

        if (RequestUtils::isGetMethod() == false)
        {
            http_response_code(403);
            echo json_encode(
            [
                'status' => 'error',
                'message' => "La méthode n'est pas supportée, veuillez utiliser du GET"
            ]);
            return false;
        }

        $result = SpectaclePrinter::generateSpectaclePDF();
        return $result;
    }
 }
