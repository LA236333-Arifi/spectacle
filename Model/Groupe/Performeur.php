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

    public function modifierNom($nom)
    {
        $query = "UPDATE Performeur_Spectacle SET nom_performeur = ? WHERE performeur_id = ?";
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute([$nom, $this->performeurId]);
    }

    public function modifierPrenom($prenom)
    {
        $query = "UPDATE Performeur_Spectacle SET prenom_performeur = ? WHERE performeur_id = ?";
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute([$prenom, $this->performeurId]);
    }

    public function modifierRole(int $roleId)
    {
        $query = "UPDATE Performeur_Spectacle SET role_performeur_id = ? WHERE performeur_id = ?";
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute([$roleId, $this->performeurId]);
    }

    public function supprimerPerformeurSafe()
    {
        // Vérifier qu'aucun groupe n'est lié à ce performeur
        $queryCheck = "SELECT COUNT(*) FROM Liaison_Groupe WHERE performeur_id = ?";
        $stmtCheck = $this->pdo->prepare($queryCheck);
        $stmtCheck->execute([$this->performeurId]);
        $groupeCount = (int)$stmtCheck->fetchColumn();

        if ($groupeCount > 0)
        {
            return 
            [
                'success' => false,
                'message' => "Impossible de supprimer le performeur : il est lié à un ou plusieurs groupes."
            ];
        }

        try
        {
            $this->pdo->beginTransaction();

            // Supprimer le performeur
            $queryDelete = "DELETE FROM Performeur_Spectacle WHERE performeur_id = ?";
            $stmtDelete = $this->pdo->prepare($queryDelete);
            $stmtDelete->execute([$this->performeurId]);

            $this->pdo->commit();

            return 
            [
                'success' => true,
                'message' => "Performeur supprimé avec succès"
            ];
        }
        catch (Exception $e)
        {
            $this->pdo->rollBack();
            return 
            [
                'success' => false,
                'message' => "Erreur lors de la suppression du performeur."
            ];
        }
    }
}