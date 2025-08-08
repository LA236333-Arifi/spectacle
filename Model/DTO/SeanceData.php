<?php

class SeanceData
{
    private string $dateSoiree;
    private string $dateAjout;
    private int $statutSeanceId;
    private int $utilisateurId;
    private int $spectacleId;

    public function __construct(
        string $dateSoiree,
        $statutSeanceId, 
        $utilisateurId = null, 
        $spectacleId = null
    )
    {
        $this->dateSoiree = $dateSoiree;
        $this->statutSeanceId = $statutSeanceId;
        $this->utilisateurId = $utilisateurId;
        $this->spectacleId = $spectacleId;
    }

    public function getSpectacleId()
    {
        return $this->spectacleId;
    }

    public function getUtilisateurId()
    {
        return $this->utilisateurId;
    }

    public function getDateSoiree()
    {
        return $this->dateSoiree;
    }

    public function getDateAjout()
    {
        return $this->dateAjout;
    }

    public function getStatutSeanceId()
    {
        return $this->statutSeanceId;
    }
}