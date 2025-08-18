<?php

class Auteur
{
    private $pdo;
    
    private $nom;
    private $prenom;

    public function __construct($nom = null, $prenom = null)
    {
        $this->pdo = Database::getInstance()->getConnection();
        $this->nom = $nom;
        $this->prenom = $prenom;
    }

    // Ajouter un auteur dans la table Auteur_Spectacle
    public function addAuteur()
    {
        if (UserDataValidator::verifyNameFormat($this->nom) == false
         || UserDataValidator::verifyNameFormat($this->prenom) == false)
        {
            return false;
        }
        // Vérifier si l'auteur existe déjà (en fonction du nom et prénom)
        $stmt = $this->pdo->prepare("SELECT * FROM Auteur_Spectacle WHERE nom_auteur = ? AND prenom_auteur = ?");
        $stmt->execute([$this->nom, $this->prenom]);
        
        // Si l'auteur existe déjà, ne rien faire et retourner false
        if ($stmt->rowCount() > 0) {
            return false;
        }

        // Si l'auteur n'existe pas, l'ajouter
        $stmt = $this->pdo->prepare("INSERT INTO Auteur_Spectacle (nom_auteur, prenom_auteur) VALUES (?, ?)");
        return $stmt->execute([$this->nom, $this->prenom]);
    }

    // Ajouter un metteur en scène dans la table MetteurScene_Spectacle
    public function addMetteurScene()
    {
        if (UserDataValidator::verifyNameFormat($this->nom) == false
         || UserDataValidator::verifyNameFormat($this->prenom) == false)
        {
            return false;
        }
        
        // Vérifier si le metteur en scène existe déjà
        $stmt = $this->pdo->prepare("SELECT * FROM MetteurScene_Spectacle WHERE nom_metteur_scene = ? AND prenom_metteur_scene = ?");
        $stmt->execute([$this->nom, $this->prenom]);
        
        // Si le metteur en scène existe déjà, ne rien faire et retourner false
        if ($stmt->rowCount() > 0) {
            return false;
        }

        // Si le metteur en scène n'existe pas, l'ajouter
        $stmt = $this->pdo->prepare("INSERT INTO MetteurScene_Spectacle (nom_metteur_scene, prenom_metteur_scene) VALUES (?, ?)");
        return $stmt->execute([$this->nom, $this->prenom]);
    }

    /**
     * Récupère tous les auteurs 
     */
    public static function getAllAuteurs()
    {
        $db = Database::getInstance()->getConnection();

        $sql = "SELECT auteur_id, nom_auteur, prenom_auteur
                FROM Auteur_Spectacle
                ORDER BY nom_auteur, prenom_auteur";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère tous les metteurs en scène
     */
    public static function getAllMetteurs()
    {
        $db = Database::getInstance()->getConnection();

        $sql = "SELECT metteur_scene_id, nom_metteur_scene, prenom_metteur_scene
                FROM MetteurScene_Spectacle
                ORDER BY nom_metteur_scene, prenom_metteur_scene";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}