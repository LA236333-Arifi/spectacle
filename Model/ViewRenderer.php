<?php

class ViewRenderer
{
    private $page;
    private $viewData;

    /**
     * Le constructeur -- Il faut spécifier le chemin complet à partir de View/ (inclus)
     * Par exemple: $pagePath = "View/Auth/Register.php"
     * La viewData est un tableau associatif qui contient les données utilisées par la vue.
     */
    public function __construct($pagePath, $viewData)
    {
        $this->page = $pagePath;
        $this->viewData = $viewData;
    }

    public static function error(MessageErreur $errorData)
    {
        // On require la vue
        $error = $errorData;
        //require 'View/Error.php';
    }

    public function render($skipHeader = false, $skipFooter = false)
    {
        if (!$skipHeader)
        {
            require 'View/layout/header.php';
        }

        // On copie le contenu de viewData dans une variable locale $view qui sera disponible 
        // à utiliser dans la page qu'on require. Elle contient les infos dynamiques dont la 
        // page a besoin pour son affichage. La variable DOIT s'appeler '$view' dans la vue
        $view = $this->viewData;
        require $page;

        if ($skipFooter)
        {
            require 'View/layout/footer.php';
        }
    }
}
