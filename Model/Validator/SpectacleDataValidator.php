<?php

class SpectacleDataValidator
{
    private array $postData;
    private array $errors = [];
    private ?SpectacleData $data = null;

    public function __construct(array $postData)
    {
        $this->postData = $postData;
        $this->validate();
    }

    private function validate()
    {
        $nom = trim($this->postData['nom_spectacle'] ?? '');
        $texteAccroche = trim($this->postData['texte_accroche_spectacle'] ?? '');
        $prix = filter_var($this->postData['prix_spectacle'] ?? null, FILTER_VALIDATE_FLOAT);
        $duree = filter_var($this->postData['duree_minutes_spectacle'] ?? null, FILTER_VALIDATE_FLOAT);
        $statutId = filter_var($this->postData['statut_spectacle_id'] ?? null, FILTER_VALIDATE_INT);
        $utilisateurId = filter_var($this->postData['utilisateur_id'] ?? null, FILTER_VALIDATE_INT);
        $type = filter_var($this->postData['type_spectacle_id'] ?? null, FILTER_VALIDATE_INT);

        // Validation des champs de base
        if (empty($nom))
        {
            $this->errors[] = "Le nom du spectacle est requis.";
        }

        if (empty($texteAccroche))
        {
            $this->errors[] = "Le texte d'accroche est requis.";
        }

        if (empty($prix) || $prix <= 0)
        {
            $this->errors[] = "Le prix doit être un nombre positif.";
        }

        if (empty($duree) || $duree <= 0)
        {
            $this->errors[] = "La durée doit être un nombre supérieur à 0.";
        }

        if (empty($statutId))
        {
            $this->errors[] = "Statut du spectacle invalide.";
        }

        if (empty($utilisateurId))
        {
            $this->errors[] = "Utilisateur invalide.";
        }

        if (empty($type) || !SpectacleType::isValid($type))
        {
            $this->errors[] = "Type de spectacle invalide.";
        }

        // Auteur & metteur en scène si requis
        $nomAuteur = null;
        $nomMetteurEnScene = null;

        // Groupe : soit un ID, soit un nom de groupe
        $groupeId = null;
        $nomGroupe = null;

        if (!empty($this->postData['groupe_id']))
        {
            $groupeId = filter_var($this->postData['groupe_id'], FILTER_VALIDATE_INT);
            if ($groupeId === false)
            {
                $this->errors[] = "Identifiant de groupe invalide.";
            }
        }
        else
        {
            $nomGroupe = trim($this->postData['nom_groupe'] ?? '');
            if ($nomGroupe === '')
            {
                $this->errors[] = "Le nom du groupe est requis si aucun groupe existant n'est sélectionné.";
            }
        }

        // Performeurs
        $performeurs = [];
        if (empty($this->postData['groupe_id']))
        {
            if (!isset($this->postData['performeurs']) || !is_array($this->postData['performeurs']))
            {
                $this->errors[] = "Aucun performeur fourni.";
            }
            else
            {
                foreach ($this->postData['performeurs'] as $p)
                {
                    $pNom = trim($p['nom'] ?? '');
                    $pPrenom = trim($p['prenom'] ?? '');
                    $pRoleId = filter_var($p['role_id'] ?? null, FILTER_VALIDATE_INT);

                    if (empty($pNom) || empty($pPrenom) || empty($pRoleId) === false)
                    {
                        $this->errors[] = "Performeur invalide (nom, prénom ou rôle manquant).";
                        continue;
                    }

                    $performeurs[] = new PerformeurData($pNom, $pPrenom, $pRoleId);
                }
            }
        }

        // Si tout est bon, construire le DTO
        if (empty($this->errors))
        {
            $this->data = new SpectacleData(
                $nom,
                $texteAccroche,
                $prix,
                $duree,
                $statutId,
                $utilisateurId,
                $type,
                $groupeId,
                $nomAuteur,
                $nomMetteurEnScene,
                $nomGroupe,
                $performeurs
            );
        }
    }

    public function isValid(): bool
    {
        return empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getData(): ?SpectacleData
    {
        return $this->data;
    }
}

