<?php
// autoload.php

function myAutoloader($class) 
{
    // Répertoire racine du projet
    $baseDir = __DIR__ . '/';  

    // Liste des répertoires à parcourir
    $directories = 
    [
        'Controller/',
        'Model/',
        'Model/Utils/',
        'Model/User/',
        'Model/Spectacle/',
        'Model/Seance/',
        'Model/DTO/',
        'Model/Validator/',
        'Model/Groupe/',
        'Model/Tokens',
        'Templates'
    ];

    // Cherche dans chaque dossier
    foreach ($directories as $directory) 
    {
        $filePath = $baseDir . $directory . $class . '.php';
        if (file_exists($filePath)) 
        {
            require_once $filePath;  // Inclut la classe si elle est trouvée
            return;
        }
    }
}

spl_autoload_register('myAutoloader');  // Enregistre la fonction d'autoloading
