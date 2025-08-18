<?php

class UserActivity
{
    private $userId = null;
    private $userStatut = null;
    private $pdo;

    public function __construct($newId)
    {
        $this->userId = $newId;
        $this->pdo = Database::getInstance()->getConnection();
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

        // Récupérer le statut actuel
        $stmt = $this->pdo->prepare("SELECT statut_utilisateur_id FROM utilisateur WHERE utilisateur_id = ?");
        $stmt->execute([$this->userId]);
        $this->userStatut = $stmt->fetchColumn();

        return $this->userStatut != UserStatut::NonValide_Et_Inactif;
    }

    public function toggleUserActivity()
    {
        if (!isset($this->userStatut))
        {
            return false;
        }

        // Inverser le statut
        $newStatus = ($this->userStatut == UserStatut::Valide_Et_Inactif) ? 
                                           UserStatut::Valide_Et_Actif : 
                                           UserStatut::Valide_Et_Inactif;
        $stmt = $this->pdo->prepare("UPDATE utilisateur SET statut_utilisateur_id = ? WHERE utilisateur_id = ?");
        if ($stmt->execute([$newStatus, $this->userId]))
        {
            $this->userStatut = $newStatus;
            return true;
        }

        return false;
    }

    public function acceptUser()
    {
        if (!isset($this->userId))
        {
            return false;
        }

        $stmt = $this->pdo->prepare("UPDATE utilisateur SET statut_utilisateur_id = 3 WHERE utilisateur_id = ?");
        if ($stmt->execute([$this->userId]))
        {
            $this->userStatut = UserStatut::Valide_Et_Actif;
            return true;
        }

        return false;
    }

    public function refuseUser()
    {
        if (!isset($this->userId))
        {
            return false;
        }

        $stmt = $this->pdo->prepare("DELETE FROM utilisateur WHERE utilisateur_id = ?");
        if ($stmt->execute([$this->userId]))
        {
            $this->userId = null;
            $this->userStatut = null;
            return true;
        }

        return false;
    }

    public function getUserEmail()
    {
        if (!isset($this->userId))
        {
            return null;
        }

        $stmt = $this->pdo->prepare("SELECT mail_utilisateur FROM utilisateur WHERE utilisateur_id = ?");
        $stmt->execute([$this->userId]);
        $email = $stmt->fetchColumn();

        return $email;
    }
}