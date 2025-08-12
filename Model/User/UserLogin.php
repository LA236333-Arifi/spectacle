<?php

require_once "User.php";
require_once "Database.php";

class UserLogin extends User 
{
    private $nom;
    private $prenom;
    private $email;
    private $role;

    public function __construct($email)
    {
        $this->email = $email;
    }

    public function getNom()
    {
        return $this->nom;
    }

    public function getPrenom()
    {
        return $this->prenom;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function verifyUserMail(): bool
    {
        return UserDataValidator::verifyEmailFormat($this->getEmail());
    }

    public function getActiveUserIdWithEmail() 
    {
        // Connexion à la base de données
        $pdo = Database::getInstance()->getConnection();

        // Vérifier si l'email existe dans la base de données
        $stmt = $pdo->prepare("SELECT utilisateur_id FROM utilisateur WHERE mail_utilisateur = :email AND statut_utilisateur_id = 3");

        // Liaison du paramètre
        $stmt->bindParam(':email', $this->email, PDO::PARAM_STR);

        // Exécution du statement
        $stmt->execute();

        // Retour du résultat
        return $stmt->fetchColumn();
    }

    public function verifyActiveUserPassword($password): bool
    {
        // Connexion à la base de données
        $pdo = Database::getInstance()->getConnection();
        $requete = $pdo->prepare("
            SELECT mdp_utilisateur
            FROM utilisateur 
            WHERE mail_utilisateur = :email AND statut_utilisateur_id = 3");

        $requete->bindParam(':email', $this->email);
        $requete->execute();
        $userData = $requete->fetchColumn();

        if ($userData === false)
        {
            return false;
        }

        return password_verify($password, $userData['mdp_utilisateur']);
    }

    public function createUserSession(): bool
    {
        // Connexion à la base de données
        $pdo = Database::getInstance()->getConnection();
        $requete = $pdo->prepare("
            SELECT nom_utilisateur, prenom_utilisateur, role_utilisateur_id
            FROM utilisateur 
            WHERE mail_utilisateur = ? AND statut_utilisateur_id = 3");

        $requete->bindParam(':email', $this->email);
        $requete->execute();
        $userData = $requete->fetchColumn();

        if ($userData === false)
        {
            return false;
        }

        // On set les attributs de notre object
        $this->setId($pdo->lastInsertId());
        $this->nom = $userData['nom_utilisateur'];
        $this->prenom = $userData['prenom_utilisateur'];
        $this->role = $userData['role_utilisateur_id'];

        // Et on crée la session avec
        $_SESSION['user'] = 
        [
            'id' => $this->getId(),
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'email' => $this->email,
            'role_id' => $this->role
        ];

        return true;
    }
}