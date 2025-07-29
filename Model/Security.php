<?php

class Security
{
    public function __construct($initSession = true)
    {
        // On setup la session sécurisée uniquement si souhaité
        if ($initSession)
        {
            $this->setCookieParams();
        }
    }
    
    private function setCookieParams()
    {
        // Démarrage de session ABSOLUMENT EN PREMIER
        if (session_status() === PHP_SESSION_NONE) 
        {
            // Configurer les paramètres du cookie de session
            session_set_cookie_params
            ([
                'httponly' => true,
                'secure' => false, // à activer uniquement en HTTPS
                'samesite' => 'Strict'
            ]);

            // Démarrer la session
            session_start();
        }
    }

    public function genererCSRFToken()
    {
        // On setup la session securisée
        $this->setCookieParams();
    
        // On génère un token csrf si on en a pas déjà un
        if (empty($_SESSION['csrf_token'])) 
        {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        // On le retourne
        return $_SESSION['csrf_token'];
    }    

    public function checkCSRFToken($tokenRecu)
    {
        $this->setCookieParams();

        $valid = isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $tokenRecu);
        
        return $valid;
    }
}
