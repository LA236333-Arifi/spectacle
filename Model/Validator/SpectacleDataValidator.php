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

        if (empty($utilisateurId))
        {
            $this->errors[] = "Utilisateur invalide.";
        }

        if (empty($type) || !SpectacleType::isValid($type))
        {
            $this->errors[] = "Type de spectacle invalide.";
        }

        $auteurId = null;
        $metteurId = null;

        // Auteur & metteur en scène si requis
        if (SpectacleType::needsAuteur($type)) 
        {
            // Cas où on reçoit les IDs
            $hasAuteurEtMetteur = isset($this->postData['auteur_id']) && isset($this->postData['metteur_id']);
            if ($hasAuteurEtMetteur) 
            {
                $auteurId = filter_var($this->postData['auteur_id'], FILTER_VALIDATE_INT);
                $metteurId = filter_var($this->postData['metteur_id'], FILTER_VALIDATE_INT);

                if ($auteurId === false || $metteurId === false) 
                {
                    $this->errors[] = "Identifiant auteur ou metteur en scène invalide.";
                } 
            }
            else
            {
                $this->errors[] = "Identifiant auteur ou metteur en scène manquants.";
            }
        }

        // GroupeID
        $groupeId = filter_var($this->postData['groupe_id'], FILTER_VALIDATE_INT);
        if ($groupeId === false)
        {
            $this->errors[] = "Identifiant de groupe invalide.";
        }

        // Si tout est bon, construire le DTO
        if (empty($this->errors))
        {
            $this->data = new SpectacleData(
                $nom,
                $texteAccroche,
                $prix,
                $duree,
                SpectacleStatut::SansSeance,
                $utilisateurId,
                $type,
                $groupeId,
                $auteurId,
                $metteurId
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

