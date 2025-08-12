<?php

class DashboardController
{
    public function index()
    {
        // Ici on définit la page d'accueil comme étant celle de la programmation
        header('Location:' . BASE_URL . '/programmation');
    }

    public function programmation()
    {
        $viewRenderer = new ViewRenderer("Visitor/Programmation", []);
        $viewRenderer->render();
    }
    
    public function calendrier()
    {
        $viewRenderer = new ViewRenderer("Visitor/Calendrier", []);
        $viewRenderer->render();
    }
}
