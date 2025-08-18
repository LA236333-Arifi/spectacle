<?php

require_once 'autoload.php';

class DashboardController
{
    private $security;
    
    public function __construct() 
    {
        // On crée notre session sécurisée via le constructeur
        $this->security = new Security(true);
    }

    public function index()
    {
        // Ici on définit la page d'accueil comme étant celle de la programmation
        header('Location:' . BASE_URL . '/programmation');
    }

    public function programmation()
    {
        $viewData = 
        [
            'types'     => SpectacleType::getSpectacleTypeToString(),
            'statuts'   => SeanceStatut::getSeanceStatutToString()
        ];

        $viewRenderer = new ViewRenderer("View/Visitor/Programmation.php", $viewData);
        $viewRenderer->render();
    }
    
    public function calendrier()
    {
        $viewRenderer = new ViewRenderer("View/Visitor/Calendrier.php", []);
        $viewRenderer->render();
    }
}
