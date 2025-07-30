<?php

class TokenResetPassword
{
    // Attributs de la classe    
    private $tokenValue = null;
    private $tokenExpirationDate = null;
    private $userId = null;
    public const tokenSize = 32;

    public function getUserId()
    {
        return $this->userId;
    }

    // Constructeur de la classe
    public function __construct($newTokenValue = null, $newTokenExpirationDate = null)
    {
        if (!$newTokenValue)
        {
            $newTokenValue = bin2hex(random_bytes(self::tokenSize));
        }
        if (!$newTokenExpirationDate)
        {
            $newTokenExpirationDate = date("Y-m-d H:i:s", strtotime("+1 hour"));
        }

        $this->tokenValue = $newTokenValue;
        $this->tokenExpirationDate = $newTokenExpirationDate;
    }

    // Fonction pour get un token
    public function getToken()
    {
        return $this->tokenValue;
    }

    public function storeUserIdWithValidToken()
    {
        if (empty($this->tokenValue))
        {
            return false;
        }

        $sql = "SELECT utilisateur_id FROM utilisateur WHERE token_utilisateur = :token AND token_utilisateur IS NOT NULL AND NOW() < date_exp_token_utilisateur AND actif_utilisateur = 1";
        
        // Préparation de la requête
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare($sql);

        // Lier les paramètres avec les valeurs correspondantes
        $stmt->bindParam(':token', $this->tokenValue, PDO::PARAM_STR);
        $stmt->execute();

        // Récupérer la première colonne (utilisateur_id) ou false si il n'y a rien
        $result = $stmt->fetchColumn();
        if ($result !== false)
        {
            $this->userId = $result;
            return true;
        }

        return false;
    }

    

    // Fonction pour vérifier si le token est toujours valide
    public function isTokenValid()
    {
        // Vérifier la validité du token
        $userId = $this->StoreUserIdWithValidToken();
        if ($userId === false) 
        {
            return false;
        }

        return true;
    }

        // Fonction pour set le token de l'utilisateur
    public function setUserToken($userId)
    {
        $sql = "UPDATE utilisateur 
                SET token_utilisateur = :newToken, 
                date_exp_token_utilisateur = :newDateExp
                WHERE utilisateur_id = :userId";

        // Préparation de la requête
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare($sql);

        // Lier les paramètres avec les valeurs correspondantes
        $stmt->bindParam(':newToken', $this->tokenValue, PDO::PARAM_STR);
        $stmt->bindParam(':newDateExp', $this->tokenExpirationDate, PDO::PARAM_STR);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);

        // Exécuter la requête et retourner le résultat
        return $stmt->execute();
    }

        // Fonction pour supprimer le token de l'utilisateur
    public function resetUserToken()
    {
        if (empty($this->userId))
        {
            return false;
        }

        $sql = "UPDATE utilisateur SET token_utilisateur = NULL, 
        date_exp_token_utilisateur = NULL 
        WHERE utilisateur_id = :userId;";

        // Préparation de la requête
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare($sql);

        // Lier les paramètres avec les valeurs correspondantes
        $stmt->bindParam(':userId', $this->userId, PDO::PARAM_INT);

        // Exécuter la requête
        $stmt->execute();
        return true;
    }

}