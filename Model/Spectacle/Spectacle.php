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

    public function addSpectacle(array $data)
    {
        $pdo = Database::getInstance()->getConnection();

        $spectacleDataValidator = new SpectacleDataValidator($data);
        if (!$spectacleDataValidator->isValid())
        {
            $this->errors = $spectacleDataValidator->getErrors();
            return false;
        }

        $spectacleData = $spectacleDataValidator->getData();

        try
        {
            $pdo->beginTransaction();

            // 1. Déterminer le groupe_id
            if ($spectacleData->hasGroupe())
            {
                $groupeId = $spectacleData->getGroupeId();
                if ($this->checkGroupeId($groupeId) == false)
                {
                    throw new Exception("Le groupeId est invalide.");
                }
            }
            else
            {
                $query = "INSERT INTO Groupe_Spectacle (nom_groupe, date_formation_groupe) VALUES (?, NOW())";
                $stmt = $pdo->prepare($query);
                $stmt->execute([$spectacleData->getNomGroupe()]);
                $groupeId = $pdo->lastInsertId();
            }

            if ($this->checkTypeSpectacleId($spectacleData->getType()) == false)
            {
                throw new Exception("Le spectacleTypeId est invalide.");
            }

            if ($this->checkStatutSpectacleId($spectacleData->getStatutSpectacleId()) == false)
            {
                throw new Exception("Le statutId est invalide.");
            }

            if ($this->checkUtilisateurId($spectacleData->getUtilisateurId()) == false)
            {
                throw new Exception("L'utilisateurId est invalide.");
            }

            // 2. Créer le spectacle
            $query = "INSERT INTO Spectacle (
                        nom_spectacle, texte_accroche_spectacle, prix_spectacle, duree_minutes_spectacle, 
                        date_creation_spectacle, derniere_date_modification_spectacle,
                        statut_spectacle_id, utilisateur_id, groupe_id, type_spectacle_id
                    ) VALUES (?, ?, ?, ?, NOW(), NOW(), ?, ?, ?, ?)";

            $stmt = $pdo->prepare($query);
            $stmt->execute([
                $spectacleData->getNom(),
                $spectacleData->getTexteAccroche(),
                $spectacleData->getPrix(),
                $spectacleData->getDureeMinutes(),
                $spectacleData->getStatutSpectacleId(),
                $spectacleData->getUtilisateurId(),
                $groupeId,
                $spectacleData->getType()
            ]);

            $spectacleId = $pdo->lastInsertId();
            $this->spectacleId = $spectacleId;

            // 3. Ajouter auteur/metteur en scène si requis
            if (in_array($spectacleData->getType(), [SpectacleType::Humoriste, SpectacleType::Theatre, SpectacleType::Danse]))
            {
                $query = "INSERT INTO Auteur_Spectacle (nom_auteur, nom_metteur_en_scene, spectacle_id) VALUES (?, ?, ?)";
                $stmt = $pdo->prepare($query);
                $stmt->execute([
                    $spectacleData->getNomAuteur(),
                    $spectacleData->getNomMetteurEnScene(),
                    $spectacleId
                ]);
            }

            // 4. Ajouter les performeurs et les lier au groupe
            foreach ($spectacleData->getPerformeurs() as $performeur)
            {
                $query = "INSERT INTO Performeur_Spectacle (nom_performeur, prenom_performeur, role_performeur_id) VALUES (?, ?, ?)";
                $stmt = $pdo->prepare($query);
                $stmt->execute([
                    $performeur->getNom(),
                    $performeur->getPrenom(),
                    $performeur->getRoleId()
                ]);
                $performeurId = $pdo->lastInsertId();

                $query = "INSERT INTO Liaison_Groupe (groupe_id, performeur_id) VALUES (?, ?)";
                $stmt = $pdo->prepare($query);
                $stmt->execute([$groupeId, $performeurId]);
            }

            $pdo->commit();
            return true;
        }
        catch (Exception $e)
        {
            $pdo->rollBack();
            error_log("Erreur lors de l'ajout du spectacle : " . $e->getMessage());
            return false;
        }
    }

}