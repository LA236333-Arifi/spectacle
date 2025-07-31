<?php

/**
 * Proxy classe qui contient les informations liées à un utilisateur
 */
class UserData
{
    private $nomUtilisateur;
    private $prenomUtilisateur;
    private $mailUtilisateur;
    private $mdpUtilisateur;
    private $roleUtilisateur;

    public function __construct($nom = "", $prenom = "", $mail = "", $mdp = "", $role = "")
    {
        $this->nomUtilisateur = $nom;
        $this->prenomUtilisateur = $prenom;
        $this->mailUtilisateur = $mail;
        $this->mdpUtilisateur = $mdp;
        $this->roleUtilisateur = $role;
    }

    public function isValid()
    {
        return !empty($this->nomUtilisateur) || 
               !empty($this->prenomUtilisateur) ||
               !empty($this->mailUtilisateur) ||
               !empty($this->mdpUtilisateur) ||
               !empty($this->roleUtilisateur);
    }

    public function getNomUtilisateur()
    {
        return $this->nomUtilisateur;
    }

    public function setNomUtilisateur($nom)
    {
        $this->nomUtilisateur = $nom;
    }

    public function getPrenomUtilisateur()
    {
        return $this->prenomUtilisateur;
    }

    public function setPrenomUtilisateur($prenom)
    {
        $this->prenomUtilisateur = $prenom;
    }

    public function getMailUtilisateur()
    {
        return $this->mailUtilisateur;
    }

    public function setMailUtilisateur($mail)
    {
        $this->mailUtilisateur = $mail;
    }

    public function getMdpUtilisateur()
    {
        return $this->mdpUtilisateur;
    }

    public function setMdpUtilisateur($mdp)
    {
        $this->mdpUtilisateur = $mdp;
    }

    public function getRoleUtilisateur()
    {
        return $this->roleUtilisateur;
    }

    public function setRoleUtilisateur($role)
    {
        $this->roleUtilisateur = $role;
    }
}
