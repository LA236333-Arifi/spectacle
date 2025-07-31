<?php

class PerformeurData
{
    private string $nom;
    private string $prenom;
    private int $roleId;

    public function __construct(string $nom, string $prenom, int $roleId)
    {
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->roleId = $roleId;
    }

    public function getNom()     { return $this->nom; }
    public function getPrenom()  { return $this->prenom; }
    public function getRoleId()  { return $this->roleId; }
}
