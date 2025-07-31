<?php

class UserDataValidator
{
    private const LongueurMinimumPassword = 8;
    private const LongueurMaximumPassword = 64;
    private const LongueurMinimumName = 2;

    public static function verifyNameFormat($name)
    {
        return !empty($name) && strlen($name) > self::LongueurMinimumName && preg_match('/^(?!\s+$)[a-zA-ZÀ-ÿ\s-]+$/', $name);
    }

    public static function verifyEmailFormat($email): bool
    {
        if (empty($email))
        {
            return false;
        }

        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public static function verifyStrongPassword($password): bool
    {
        // On vérifie que la taille reste dans les limites choisies
        // Et que le mot de passe possède au moins 1 miniscule/majuscule/chiffre
        $length = strlen($password);
        $LongueurMinimumPassword = $_ENV['LONGUEUR_MINIMUM_PASSWORD'] ?? self::LongueurMinimumPassword;
        $LongueurMaximumPassword = $_ENV['LONGUEUR_MAXIMUM_PASSWORD'] ?? self::LongueurMaximumPassword;
        return
            $length >= $LongueurMinimumPassword &&
            $length <= $LongueurMaximumPassword
            && preg_match('/[A-Z]/', $password)
            && preg_match('/[a-z]/', $password)
            && preg_match('/[0-9]/', $password);
    }
}
