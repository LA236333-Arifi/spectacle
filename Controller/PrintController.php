<?php

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

        $viewRenderer = new ViewRenderer("View/PrintPdf.php", []);
        $viewRenderer->render();
        return true;

     }  

      /**
     * GET /spectacle/print?id={id}
     */
    private function printSpectacle(int $id)
    {
        if (UserConnectionUtils::isUserConnected() == false)
        {
            return false;
        }

        if (RequestUtils::isGetMethod() == false)
        {
            return false;
        }

        if (empty($id))
        {
            http_response_code(400);
            header('Content-Type: application/json');
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
        // L'admin et le secretaire peut générer un pdf, donc on utilise "isUserConnected()"
        if (UserConnectionUtils::isUserConnected() == false)
        {
            return false;
        }

        $listSpectacles = SpectaclePrinter::generateSpectaclePDF();
        return !empty($listSpectacles);
    }
 }
