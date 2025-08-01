<?php

require_once "User.php";
require_once "Utils/UserConnectionUtils.php";
require_once "Database.php";
require_once "UserData.php";

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
            return RegisterStatus::MauvaisPasswordFormat;
        }

        return RegisterStatus::Valid;
    }

    public function checkEmailAvailable($email)
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
}
