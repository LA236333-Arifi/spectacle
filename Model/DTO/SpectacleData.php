<?php

class SpectacleData
{
    private string $nom;
    private string $texteAccroche;
    private float $prix;
    private float $dureeMinutes;
    private int $statutSpectacleId;
    private int $utilisateurId;
    private int $type;
    private ?int $groupeId;
    private ?string $auteurId;
    private ?string $metteurId;

    public function __construct(
        string $nom,
        string $texteAccroche,
        float $prix,
        float $dureeMinutes,
        int $statutSpectacleId,
        int $utilisateurId,
        int $type,
        ?int $groupeId,
        ?string $auteurId,
        ?string $metteurId,
    ) {
        $this->nom = $nom;
        $this->texteAccroche = $texteAccroche;
        $this->prix = $prix;
        $this->dureeMinutes = $dureeMinutes;
        $this->statutSpectacleId = $statutSpectacleId;
        $this->utilisateurId = $utilisateurId;
        $this->type = $type;
        $this->groupeId = $groupeId;
        $this->auteurId = $auteurId;
        $this->metteurId = $metteurId;
    }

    // Getters
    public function getNom()                    { return $this->nom; }
    public function getTexteAccroche()          { return $this->texteAccroche; }
    public function getPrix()                   { return $this->prix; }
    public function getDureeMinutes()           { return $this->dureeMinutes; }
    public function getStatutSpectacleId()      { return $this->statutSpectacleId; }
    public function getUtilisateurId()          { return $this->utilisateurId; }
    public function getType()                   { return $this->type; }
    public function getGroupeId()               { return $this->groupeId; }
    public function getAuteurId()               { return $this->auteurId; }
    public function getMetteurSceneId()         { return $this->metteurId; }
}
