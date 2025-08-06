<?php

class Performeur
{
    private $performeurId;
    private $pdo;

    public function __construct($performeurId = null)
    {
        $this->performeurId = $performeurId;
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function ajouterPerformeur(PerformeurData $data)
    {
        $query = "INSERT INTO Performeur_Spectacle (nom_performeur, prenom_performeur, role_performeur_id) VALUES (?, ?, ?)";
        $stmt = $this->pdo->prepare($query);
        if ($stmt->execute([$data->getNom(), $data->getPrenom(), $data->getRoleId()]))
        {
            $this->performeurId = $this->pdo->lastInsertId();
            return $this->performeurId;
        }
        return false;
    }

       public function getPerformeurData()
    {
        $query = "SELECT nom_performeur, prenom_performeur, role_performeur_id FROM Performeur_Spectacle WHERE performeur_id = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$this->performeurId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row)
        {
            return new PerformeurData($row['nom_performeur'], $row['prenom_performeur'], (int)$row['role_performeur_id']);
        }
        return null;
    }

      public function modifierNomPrenom(PerformeurData $data)
    {
        $query = "UPDATE Performeur_Spectacle SET nom_performeur = ?, prenom_performeur = ? WHERE performeur_id = ?";
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute([$data->getNom(), $data->getPrenom(), $this->performeurId]);
    }

    public function modifierRole(int $roleId)
    {
        $query = "UPDATE Performeur_Spectacle SET role_performeur_id = ? WHERE performeur_id = ?";
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute([$roleId, $this->performeurId]);
    }
}