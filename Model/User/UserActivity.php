<?php

class UserActivity
{
    private $userId = null;
    private $userStatut = null;

    public function __construct($newId)
    {
        $this->userId = $newId;
    }

    public function getUserStatut()
    {
        return $this->userStatut;
    }

    public function isUserStatutActif()
    {
        return $this->userStatut == UserStatut::Valide_Et_Actif;
    }

    public function storeUserActivity()
    {
        if (!isset($this->userId))
        {
            return false;
        }

        $db = Database::getInstance()->getConnection();

        // Récupérer le statut actuel
        $stmt = $db->prepare("SELECT statut_utilisateur_id FROM utilisateur WHERE utilisateur_id = ?");
        $stmt->execute([$this->userId]);
        $this->userStatut = $stmt->fetchColumn();

        return $this->userStatut != UserStatut::NonValide_Et_Inactif;
    }

    public function toggleUserActivity()
    {
        $db = Database::getInstance()->getConnection();

        if (!isset($this->userStatut))
        {
            return false;
        }

        // Inverser le statut
        $newStatus = ($this->userStatut == UserStatut::Valide_Et_Inactif) ? 
                                           UserStatut::Valide_Et_Actif : 
                                           UserStatut::Valide_Et_Inactif;
        $stmt = $db->prepare("UPDATE utilisateur SET statut_utilisateur_id = ? WHERE utilisateur_id = ?");
        if ($stmt->execute([$newStatus, $this->userId]))
        {
            $this->userStatut = $newStatus;
            return true;
        }

        return false;
    }
}