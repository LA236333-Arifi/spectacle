<?php

class UserActivity
{
    private $userId = null;
    private $userActif = null;

    public function __construct($newId)
    {
        $this->userId = $newId;
    }

    public function getUserActif()
    {
        return $this->userActif;
    }

    public function storeUserActivity()
    {
        if (!isset($this->userId))
        {
            return false;
        }

        $db = Database::getInstance()->getConnection();

        // Récupérer le statut actuel
        $stmt = $db->prepare("SELECT actif_utilisateur FROM utilisateur WHERE utilisateur_id = ?");
        $stmt->execute([$this->userId]);
        $this->userActif = $stmt->fetchColumn();

        return true;
    }
}