<?php 

class Groupe
{
    private $groupeId;
    private $pdo;

    public function __construct($groupeId = null)
    {
        $this->groupeId = $groupeId;
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function creerGroupe($nomGroupe)
    {
        $query = "INSERT INTO Groupe_Spectacle (nom_groupe, date_formation_groupe) VALUES (?, NOW())";
        $stmt = $this->pdo->prepare($query);
        if ($stmt->execute([$nomGroupe]))
        {
            $this->groupeId = $this->pdo->lastInsertId();
            return $this->groupeId;
        }
        return false;
    }
       
    public function getGroupeData()
    {
        $query = "SELECT * FROM Groupe_Spectacle WHERE groupe_id = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$this->groupeId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function modifierNomGroupe($nouveauNom)
    {
        $query = "UPDATE Groupe_Spectacle SET nom_groupe = ? WHERE groupe_id = ?";
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute([$nouveauNom, $this->groupeId]);
    }
}