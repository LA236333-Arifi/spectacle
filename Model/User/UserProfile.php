<?php

class UserProfile extends User
{
    private $pdo;

    public function __construct($newUserId)
    {
        $this->setId($newUserId);
        $this->pdo = Database::getInstance()->getConnection();
    }
    
    public function changeProfile($champsAChanger, $params)
    {
        // Quitter si on a ni paramètres ni champs à changer
        if (empty($champsAChanger) || empty($params)) 
        {
            return false;
        }

        // Préparation du SQL avec le format UPDATE dynamique et safe 
        $sql = "UPDATE Utilisateur SET " . implode(', ', $champsAChanger) . " WHERE utilisateur_id = :id";
        $stmt = $this->pdo->prepare($sql);

        // On lie le paramètre manquant qui est l'user ID
        $params[':id'] = $this->getId();

        // On retourne si l'execution est bien passé ou pas
        return $stmt->execute($params);
    }
    
    public function getUserData(): array 
    {
        // Connexion à la base de données
        $pdo = Database::getInstance()->getConnection();
        
        // Requête SQL pour récupérer les informations de l'utilisateur
        $stmt = $pdo->prepare("SELECT * FROM Utilisateur WHERE utilisateur_id = ? AND statut_utilisateur_id = 3");
        
        // Exécution de la requête SQL
        $stmt->execute([$this->getId()]);

        // Retour du résultat
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
