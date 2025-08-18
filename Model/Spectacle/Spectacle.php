<?php

use function PHPUnit\Framework\isNull;

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

    private function checkAuteurId(int $auteurId): bool
    {
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare("SELECT 1 FROM Auteur_Spectacle WHERE auteur_id = ? LIMIT 1");
        $stmt->execute([$auteurId]);
        return (bool) $stmt->fetchColumn();
    }

    private function checkMetteurSceneId(int $metteurSceneId): bool
    {
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare("SELECT 1 FROM MetteurScene_Spectacle WHERE metteur_scene_id = ? LIMIT 1");
        $stmt->execute([$metteurSceneId]);
        return (bool) $stmt->fetchColumn();
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

    private function checkSpectacleNameIsValid($nomSpectacle): bool
    {
        // Connexion à la base de données
        $db = Database::getInstance()->getConnection();

        // Requête SQL pour vérifier si un spectacle avec le même nom existe avec un statut différent de 3
        $sql = "
            SELECT COUNT(*) 
            FROM Spectacle 
            WHERE nom_spectacle = :nom_spectacle
            AND statut_spectacle_id != 3
        ";

        // Préparer et exécuter la requête
        $stmt = $db->prepare($sql);
        $stmt->execute([':nom_spectacle' => $nomSpectacle]);

        // Récupérer le nombre de spectacles trouvés
        $count = $stmt->fetchColumn();

        // Si le nombre est supérieur à 0, un spectacle avec le même nom et un statut différent de 3 existe
        if ($count > 0) 
        {
            return false;
        }

        // Aucun spectacle avec ce nom et un statut != 3, donc le nom est valide
        return true;
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
            $groupeId = $spectacleData->getGroupeId();
            if ($this->checkGroupeId($groupeId) == false)
            {
                throw new Exception("Le groupeId est invalide.");
            }

            if (SpectacleType::isValid($spectacleData->getType()) == false)
            {
                throw new Exception("Le spectacleTypeId est invalide.");
            }

            if (SpectacleStatut::isValid($spectacleData->getStatutSpectacleId()) == false)
            {
                throw new Exception("Le statutId est invalide.");
            }

            if ($this->checkUtilisateurId($spectacleData->getUtilisateurId()) == false)
            {
                throw new Exception("L'utilisateurId est invalide.");
            }

            if ($this->checkSpectacleNameIsValid($spectacleData->getNom()) == false)
            {
                throw new Exception("Le nom du spectacle est déjà pris. Vous pouvez cloturer le spectacle qui en dispose du nom.");
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
            if (SpectacleType::needsAuteur($spectacleData->getType()))
            {
                $auteurId = $spectacleData->getAuteurId();            // attendu depuis le POST: auteur_id
                $metteurId = $spectacleData->getMetteurSceneId();     // attendu depuis le POST: metteur_id

                if (empty($auteurId) || empty($metteurId)) 
                {
                    throw new Exception("Auteur et Metteur en scène sont requis pour ce type de spectacle.");
                }

                if ($this->checkAuteurId($auteurId) === false) 
                {
                    throw new Exception("L'auteurId est invalide.");
                }

                if ($this->checkMetteurSceneId($metteurId) === false) 
                {
                    throw new Exception("Le metteurSceneId est invalide.");
                }

                // Lier au spectacle (UNIQUE(spectacle_id) garantit un seul couple par spectacle)
                $queryLiaison = "INSERT INTO Auteur_MetteurScene_Spectacle (auteur_id, metteur_scene_id, spectacle_id)
                                VALUES (?, ?, ?)";
                $stmtLiaison = $pdo->prepare($queryLiaison);
                $stmtLiaison->execute([$auteurId, $metteurId, $spectacleId]);
            }

            $pdo->commit();
            return true;
        }
        catch (Exception $e)
        {
            $pdo->rollBack();
            $this->errors[] = $e->getMessage();
            error_log("Erreur lors de l'ajout du spectacle : " . $e->getMessage());
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
            $querySpectacle = "DELETE FROM Spectacle WHERE spectacle_id = ?";
            
            $queryGetLiaison = "SELECT auteur_id, metteur_scene_id FROM Auteur_MetteurScene_Spectacle WHERE spectacle_id = ?";
            $stmtGetLiaison = $pdo->prepare($queryGetLiaison);
            $stmtGetLiaison->execute([$this->spectacleId]);
            $liaison = $stmtGetLiaison->fetch(PDO::FETCH_ASSOC);

            if ($liaison) 
            {
                $pdo->prepare("DELETE FROM Auteur_MetteurScene_Spectacle WHERE spectacle_id = ?")
                ->execute([$this->spectacleId]);

                $pdo->prepare("DELETE FROM Auteur_Spectacle WHERE auteur_id = ?")
                ->execute([$liaison['auteur_id']]);

                $pdo->prepare("DELETE FROM MetteurScene_Spectacle WHERE metteur_scene_id = ?")
                ->execute([$liaison['metteur_scene_id']]);
            }

            // Supprimer toutes les séances liées au spectacle
            $stmt = $pdo->prepare($querySeance);
            $stmt->execute([$this->spectacleId]);;

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