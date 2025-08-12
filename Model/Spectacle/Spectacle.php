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

    public function modifySpectacle(array $data)
    {
        if (empty($this->spectacleId))
        {
            return false;
        }

        $pdo = Database::getInstance()->getConnection();

        // Champs modifiables dans Spectacle
        $allowedFields = [
            'nom_spectacle',
            'texte_accroche_spectacle',
            'prix_spectacle',
            'duree_minutes_spectacle',
            'statut_spectacle_id',
            'utilisateur_id',
            'groupe_id',
            'type_spectacle_id'
        ];

        $fieldsToUpdate = [];
        $params = [];

        foreach ($data as $key => $value)
        {
            if (in_array($key, $allowedFields))
            {
                $fieldsToUpdate[] = "$key = ?";
                $params[] = $value;
            }
        }

        // Toujours mettre à jour la date de modification
        $fieldsToUpdate[] = "derniere_date_modification_spectacle = NOW()";

        if (empty($fieldsToUpdate))
        {
            return false;
        }

        // Récupérer l'ancien type de spectacle
        $queryType = "SELECT type_spectacle_id FROM Spectacle WHERE spectacle_id = ?";
        $stmtType = $pdo->prepare($queryType);
        $stmtType->execute([$this->spectacleId]);
        $oldType = (int)$stmtType->fetchColumn();

        $newType = isset($data['type_spectacle_id']) ? (int)$data['type_spectacle_id'] : $oldType;

        try
        {
            $pdo->beginTransaction();

            // Si on passe d'un type avec auteur/metteur en scène à un type sans, on supprime la ligne Auteur_Spectacle
            if (SpectacleType::needsAuteur($oldType) && !SpectacleType::needsAuteur($newType))
            {
                $queryDeleteAuteur = "DELETE FROM Auteur_Spectacle WHERE spectacle_id = ?";
                $stmtDeleteAuteur = $pdo->prepare($queryDeleteAuteur);
                $stmtDeleteAuteur->execute([$this->spectacleId]);
            }

            // Si on passe d'un type sans auteur/metteur en scène à un type avec, on doit vérifier la présence des champs
            if (!SpectacleType::needsAuteur($oldType) && SpectacleType::needsAuteur($newType))
            {
                if (empty($data['nom_auteur']) || empty($data['nom_metteur_en_scene']))
                {
                    $pdo->rollBack();
                    return false;
                }

                // On insère la ligne Auteur_Spectacle
                $queryInsertAuteur = "INSERT INTO Auteur_Spectacle (nom_auteur, nom_metteur_en_scene, spectacle_id) VALUES (?, ?, ?)";
                $stmtInsertAuteur = $pdo->prepare($queryInsertAuteur);
                $stmtInsertAuteur->execute([
                    $data['nom_auteur'],
                    $data['nom_metteur_en_scene'],
                    $this->spectacleId
                ]);
            }

            // Si on tente de modifier nom_auteur ou nom_metteur_en_scene alors que le type est 2 ou 3, on annule
            if (!SpectacleType::needsAuteur($newType) && (isset($data['nom_auteur']) || isset($data['nom_metteur_en_scene'])))
            {
                $pdo->rollBack();
                return false;
            }

            // Vérification lors de la modification de nom_auteur ou nom_metteur_en_scene
            if (SpectacleType::needsAuteur($newType))
            {
                $auteurFields = [];
                $auteurParams = [];

                if (isset($data['nom_auteur']))
                {
                    $auteurFields[] = "nom_auteur = ?";
                    $auteurParams[] = $data['nom_auteur'];
                }
                if (isset($data['nom_metteur_en_scene']))
                {
                    $auteurFields[] = "nom_metteur_en_scene = ?";
                    $auteurParams[] = $data['nom_metteur_en_scene'];
                }

                if (!empty($auteurFields))
                {
                    $auteurQuery = "UPDATE Auteur_Spectacle SET " . implode(', ', $auteurFields) . " WHERE spectacle_id = ?";
                    $auteurParams[] = $this->spectacleId;
                    $stmtAuteur = $pdo->prepare($auteurQuery);
                    $stmtAuteur->execute($auteurParams);
                }
            }

            $query = "UPDATE Spectacle SET " . implode(', ', $fieldsToUpdate) . " WHERE spectacle_id = ?";
            $params[] = $this->spectacleId;

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);

            $pdo->commit();
            return true;
        }
        catch (Exception $e)
        {
            $pdo->rollBack();
            error_log("Erreur lors de la modification du spectacle : " . $e->getMessage());
            return false;
        }
    }

    public function cloturerSpectacle()
    {
        if (isset($this->spectacleId) == false)
        {
            return false;
        }

        $pdo = Database::getInstance()->getConnection();

        // Mettre à jour le statut du spectacle et la date de clôture
        $query = "UPDATE Spectacle 
                SET statut_spectacle_id = 3, date_cloture_spectacle = NOW() 
                WHERE spectacle_id = ?";
        $stmt = $pdo->prepare($query);
        $success = $stmt->execute([$this->spectacleId]);

        // Mettre toutes les séances à venir au statut "Annulé" (2)
        $querySeance = "UPDATE Seance 
                        SET statut_seance_id = 2 
                        WHERE spectacle_id = ? 
                        AND date_soiree_seance > NOW() 
                        AND statut_seance_id != 2";
        $stmtSeance = $pdo->prepare($querySeance);
        $success &= $stmtSeance->execute([$this->spectacleId]);

        return $success;
    }

    public function deleteSpectacle()
    {
        if (empty($this->spectacleId)) 
        {
            return false;
        }

        $pdo = Database::getInstance()->getConnection();

        try 
        {
            $pdo->beginTransaction();

            $querySeance = "DELETE FROM Seance WHERE spectacle_id = ?";
            $queryAuteur = "DELETE FROM Auteur_Spectacle WHERE spectacle_id = ?";
            $querySpectacle = "DELETE FROM Spectacle WHERE spectacle_id = ?";
            
            // Supprimer toutes les séances liées au spectacle
            $stmt = $pdo->prepare($querySeance);
            $stmt->execute([$this->spectacleId]);

            // Supprimer l'auteur lié au spectacle
            $stmt = $pdo->prepare($queryAuteur);
            $stmt->execute([$this->spectacleId]);

            // Supprimer le spectacle
            $stmt = $pdo->prepare($querySpectacle);
            $stmt->execute([$this->spectacleId]);

            $pdo->commit();
            return true;
        } 
        catch (Exception $e) 
        {
            $pdo->rollBack();
            error_log("Erreur lors de la suppression du spectacle : " . $e->getMessage());
            return false;
        }
    }
}