<?php

class Spectacle
{
    private $spectacleId;
    private $errors;

    public function __construct($spectacleId = null)
    {
        $this->spectacleId = $spectacleId;
    }

    public function getSpectacleId()
    {
        return $this->spectacleId;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    private function checkGroupeId($groupeId): bool
    {
        $pdo = Database::getInstance()->getConnection();

        $query = "SELECT COUNT(*) FROM Groupe_Spectacle WHERE groupe_id = :id";
        $stmt = $pdo->prepare($query);

        $stmt->bindValue(':id', $groupeId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }

    private function checkTypeSpectacleId($typeId): bool
    {
        $pdo = Database::getInstance()->getConnection();

        $query = "SELECT COUNT(*) FROM Type_Spectacle WHERE type_spectacle_id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':id', $typeId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }

    private function checkStatutSpectacleId($statutId): bool
    {
        $pdo = Database::getInstance()->getConnection();

        $query = "SELECT COUNT(*) FROM Statut_Spectacle WHERE statut_spectacle_id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':id', $statutId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }

    private function checkUtilisateurId($utilisateurId): bool
    {
        $pdo = Database::getInstance()->getConnection();

        $query = "SELECT COUNT(*) FROM Utilisateur WHERE utilisateur_id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':id', $utilisateurId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }
}