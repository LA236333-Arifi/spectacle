<?php

enum RegisterStatus
{
    case Valid;
    case MauvaisNomFormat;
    case MauvaisPrenomFormat;
    case MauvaisMailFormat;
    case MauvaisPasswordFormat;
    case EmailDejaPris;
}

class UserCredentials extends User
{
    private UserData $userData;

    public function __construct(UserData $userData)
    {
        $this->userData = $userData;
    }

    public function verifyRegisterData(): RegisterStatus
    {
        if (UserDataValidator::verifyNameFormat($this->userData->getNomUtilisateur()) == false)
        {
            return RegisterStatus::MauvaisNomFormat;
        }

        if (UserDataValidator::verifyNameFormat($this->userData->getPrenomUtilisateur()) == false)
        {
            return RegisterStatus::MauvaisPrenomFormat;
        }

        if (UserDataValidator::verifyEmailFormat($this->userData->getMailUtilisateur()) == false)
        {
            return RegisterStatus::MauvaisMailFormat;
        }

        if (UserDataValidator::verifyStrongPassword($this->userData->getMdpUtilisateur()) == false)
        {
            return RegisterStatus::MauvaisPasswordFormat;
        }

        if ($this->checkEmailAvailable($this->userData->getMailUtilisateur()) == false)
        {
            return RegisterStatus::EmailDejaPris;
        }

        return RegisterStatus::Valid;
    }

    public static function checkEmailAvailable($email)
    {
        // Connexion à la base de données
        $pdo = Database::getInstance()->getConnection();

        // Vérifier si l'email existe dans la base de données, même pour les comptes désactivés.
        $stmt = $pdo->prepare("SELECT utilisateur_id FROM utilisateur WHERE mail_utilisateur = :email");

        // Liaison du paramètre
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);

        // Exécution du statement
        $stmt->execute();

        // Retour du résultat
        return $stmt->fetchColumn() === false;
    }

    // Méthode statique pour mettre à jour le mot de passe d'un utilisateur par son ID
    public static function updateUserPasswordById($userId, $newPassword)
    {
        // Connexion à la base de données
        $pdo = Database::getInstance()->getConnection();

        // Requête SQL pour mettre à jour le mot de passe de l'utilisateur
        $sql = "UPDATE utilisateur SET mdp_utilisateur = :password WHERE utilisateur_id = :userId";

        // Préparation de la requête SQL
        $stmt = $pdo->prepare($sql);

        // On hash le mot de passe pour convenir aux standards de sécurité
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Lier les paramètres à la requête SQL
        $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);  // Lien du mot de passe haché
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);             // Lien de l'ID de l'utilisateur

        // Exécution de la requête et retour de son résultat (soit true soit false)
        return $stmt->execute();
    }

    /**
     * Création d'un nouvel utilisateur.
     * La vérification des données à insérer se fait avant d'appeler la fonction
     * @param UserData $data
     * @return bool
     */
    public function insertUser(): bool
    {   
        $query = "INSERT INTO Utilisateur (nom_utilisateur, prenom_utilisateur, mail_utilisateur, mdp_utilisateur, token_utilisateur, date_exp_token_utilisateur, statut_utilisateur_id, role_utilisateur_id) VALUES (?, ?, ?, ?, NULL, NULL, ?, ?)";
        
        // On hash le mot de passe pour convenir aux standards de sécurité
        $hashedPassword = password_hash($this->userData->getMdpUtilisateur(), PASSWORD_DEFAULT);
        
        // Quand on ajoute un user, il est par défaut inactif. C'est à l'admin de l'activer au besoin
        $actifDefaultValue = UserStatut::NonValide_Et_Inactif;

        try 
        {
            $pdo = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare($query);
            $result = $stmt->execute
            (
                [
                    $this->userData->getNomUtilisateur(), 
                    $this->userData->getPrenomUtilisateur(), 
                    $this->userData->getMailUtilisateur(),
                    $hashedPassword,
                    $actifDefaultValue,
                    $this->userData->getRoleUtilisateur()
                ]
            );

            if ($result)
            {
                $this->setId($pdo->lastInsertId());
            }

            return $result;
        } 
        catch (Exception $e) 
        {
            die($e->getMessage());
            return false;
        }
    }
}
