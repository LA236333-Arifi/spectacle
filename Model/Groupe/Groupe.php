<?php 

enum GroupeActionResult
{
    case Valid;
    case Invalid; // L'action est invalide sans savoir exactement pourquoi
    case NomDejaPris; // Le nom est déjà pris
}

class Groupe
{
    private $groupeId;
    private $pdo;

    public function __construct($groupeId = null)
    {
        $this->groupeId = $groupeId;
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function getId()
    {
        return $this->groupeId;
    }

    public static function getAll()
    {
        $db = Database::getInstance()->getConnection();

        $sql = "SELECT groupe_id, nom_groupe, date_formation_groupe
                FROM Groupe_Spectacle
                ORDER BY nom_groupe ASC, date_formation_groupe ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function creerGroupe($nomGroupe)
    {
        // Vérifier si le nom du groupe existe déjà
        $query = "SELECT * FROM Groupe_Spectacle WHERE nom_groupe = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$nomGroupe]);

        // Si un groupe avec ce nom existe, retourner une erreur
        if ($stmt->rowCount() > 0) 
        {
            return GroupeActionResult::NomDejaPris;
        }

        // Si le nom n'est pas pris, insérer le nouveau groupe
        $query = "INSERT INTO Groupe_Spectacle (nom_groupe, date_formation_groupe) VALUES (?, NOW())";
        $stmt = $this->pdo->prepare($query);

        if ($stmt->execute([$nomGroupe])) 
        {
            $this->groupeId = $this->pdo->lastInsertId();
            return GroupeActionResult::Valid;
        }

        return false; // Si l'insertion échoue
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

    public function supprimerGroupeSafe()
    {
        // Vérifier qu'aucun spectacle n'est lié à ce groupe
        $queryCheck = "SELECT COUNT(*) FROM Spectacle WHERE groupe_id = ?";
        $stmtCheck = $this->pdo->prepare($queryCheck);
        $stmtCheck->execute([$this->groupeId]);
        $spectacleCount = (int)$stmtCheck->fetchColumn();

        if ($spectacleCount > 0)
        {
            return 
            [
                'success' => false,
                'message' => "Impossible de supprimer le groupe : il est lié à un ou plusieurs spectacles."
            ];
        }

        try
        {
            $this->pdo->beginTransaction();

            // Supprimer les liaisons avec les performeurs
            $queryDeleteLiaison = "DELETE FROM Liaison_Groupe WHERE groupe_id = ?";
            $stmtDeleteLiaison = $this->pdo->prepare($queryDeleteLiaison);
            $stmtDeleteLiaison->execute([$this->groupeId]);

            // Supprimer le groupe
            $queryDeleteGroupe = "DELETE FROM Groupe_Spectacle WHERE groupe_id = ?";
            $stmtDeleteGroupe = $this->pdo->prepare($queryDeleteGroupe);
            $stmtDeleteGroupe->execute([$this->groupeId]);

            $this->pdo->commit();

            return 
            [
                'success' => true,
                'message' => "Groupe supprimé avec succès"
            ];
        }
        catch (Exception $e)
        {
            $this->pdo->rollBack();
            return 
            [
                'success' => false,
                'message' => "Erreur lors de la suppression du groupe."
            ];
        }
    }

    public function ajouterPerformeurAuGroupe($performeurId)
    {
        // On suppose que le performeur existe déjà en DB, il faut donc son id
        // Si besoin de créer le performeur ici, il faut instancier la classe Performeur
        $query = "INSERT INTO Liaison_Groupe (groupe_id, performeur_id) VALUES (?, ?)";
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute([$this->groupeId, $performeurId]);
    }

    public function retirerPerformeurDuGroupe($performeurId)
    {
        $query = "DELETE FROM Liaison_Groupe WHERE groupe_id = ? AND performeur_id = ?";
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute([$this->groupeId, $performeurId]);
    }

    public function getPerformeursDuGroupe()
    {
        $query = "SELECT p.nom_performeur, p.prenom_performeur, p.role_performeur_id, p.performeur_id 
                  FROM Performeur_Spectacle p
                  INNER JOIN Liaison_Groupe lg ON p.performeur_id = lg.performeur_id
                  WHERE lg.groupe_id = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$this->groupeId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $performeurs = [];
        foreach ($rows as $row)
        {
            $performeurs[] = new PerformeurData($row['nom_performeur'], $row['prenom_performeur'], (int)$row['role_performeur_id']);
        }
        return $performeurs;
    }
}
