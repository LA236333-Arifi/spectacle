<?php

class UserConnectionUtils
{
    // Vérifie si c'est bien l'admin est connecté
    public static function isAdminConnected()
    {
        // On lance une session sécurisée
        $securityObj = new Security(true);

        $roleId = $_SESSION['user']['role_id'] ?? null;
        if (empty($roleId))
        {
            return false;
        }

        return Role::isSameRole($roleId, Role::GERANT);
    }

    public static function isSecretaireConnected()
    {
        // On lance une session sécurisée
        $securityObj = new Security(true);

        $roleId = $_SESSION['user']['role_id'] ?? null;
        if (empty($roleId))
        {
            return false;
        }

        return Role::isSameRole($roleId, Role::SECRETAIRE);
    }

    // Vérifie si l'utilisateur est connecté
    public static function isUserConnected()
    {
        // On lance une session sécurisée
        $securityObj = new Security(true);

        return isset($_SESSION['user']['id']);
    }

    // Retourne l'utilisateur actuellement connecté
    public static function getConnectedUserId()
    {
        // On lance une session sécurisée
        $securityObj = new Security(true);

        $ret = $_SESSION['user']['id'] ?? null;
        return $ret;
    }

    public static function getConnectedUserRole()
    {
        // On lance une session sécurisée
        $securityObj = new Security(true);

        $ret = $_SESSION['user']['role_id'] ?? null;
        return $ret;
    }
}