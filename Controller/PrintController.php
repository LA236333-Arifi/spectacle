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
     
 }
