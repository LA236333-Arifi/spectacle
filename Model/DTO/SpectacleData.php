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
    private ?string $nomAuteur;
    private ?string $prenomAuteur;
    private ?string $nomMetteurEnScene;
    private ?string $prenomMetteurEnScene;
    private ?string $nomGroupe;
    private array $performeurs; // tableau d’objets PerformeurData

    public function __construct(
        string $nom,
        string $texteAccroche,
        float $prix,
        float $dureeMinutes,
        int $statutSpectacleId,
        int $utilisateurId,
        int $type,
        ?int $groupeId,
        ?string $nomAuteur,
        ?string $prenomAuteur,
        ?string $nomMetteurEnScene,
        ?string $prenomMetteurEnScene,
        ?string $nomGroupe,
        array $performeurs
    ) {
        $this->nom = $nom;
        $this->texteAccroche = $texteAccroche;
        $this->prix = $prix;
        $this->dureeMinutes = $dureeMinutes;
        $this->statutSpectacleId = $statutSpectacleId;
        $this->utilisateurId = $utilisateurId;
        $this->type = $type;
        $this->groupeId = $groupeId;
        $this->nomAuteur = $nomAuteur;
        $this->prenomAuteur = $prenomAuteur;
        $this->nomMetteurEnScene = $nomMetteurEnScene;
        $this->prenomMetteurEnScene = $prenomMetteurEnScene;
        $this->nomGroupe = $nomGroupe;
        $this->performeurs = $performeurs;
    }

    // Getters
    public function getNom()                    { return $this->nom; }
    public function getTexteAccroche()          { return $this->texteAccroche; }
    public function getPrix()                   { return $this->prix; }
    public function getDureeMinutes()           { return $this->dureeMinutes; }
    public function getStatutSpectacleId()      { return $this->statutSpectacleId; }
    public function getUtilisateurId()          { return $this->utilisateurId; }
    public function getType()                   { return $this->type; }
    public function hasGroupe()                 { return $this->groupeId !== null; }
    public function getGroupeId()               { return $this->groupeId; }
    public function getNomAuteur()              { return $this->nomAuteur; }
    public function getPrenomAuteur()           { return $this->prenomAuteur; }
    public function getNomMetteurEnScene()      { return $this->nomMetteurEnScene; }
    public function getPrenomMetteurEnScene()   { return $this->prenomMetteurEnScene; }
    public function getNomGroupe()              { return $this->nomGroupe; }
    public function getPerformeurs()            { return $this->performeurs; }
}
