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

     public function supprimerGroupe()
    {
        $query = "DELETE FROM Groupe_Spectacle WHERE groupe_id = ?";
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute([$this->groupeId]);
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