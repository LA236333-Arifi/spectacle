<?php

class SeanceDataValidator
{
    private SeanceData $data;
    private array $errors = [];

    public function __construct(SeanceData $data)
    {
        $this->data = $data;
        $this->validate();
    }

    private function validate()
    {
        // Vérifie la date de la séance (format DD-MM-YYYY)
        if (empty($this->data->getDateSoiree()) || !preg_match('/^\d{2}-\d{2}-\d{4}$/', $this->data->getDateSoiree()))
        {
            $this->errors['date_soiree'] = "Date de séance invalide ou manquante (format attendu : DD-MM-YYYY)";
        }

        if (empty($this->data->getUtilisateurId()) || $this->data->getUtilisateurId() < 1)
        {
            $this->errors['utilisateur_id'] = "Utilisateur associé invalide";
        }

        if (empty($this->data->getSpectacleId()) || $this->data->getSpectacleId() < 1)
        {
            $this->errors['spectacle_id'] = "Spectacle associé invalide";
        }

        // Vérifie le statut de la séance  
        if (!SeanceStatut::isStatutValid($this->data->getStatutSeanceId()))
        {
            $this->errors['statut_seance_id'] = "Statut de séance invalide";
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
}