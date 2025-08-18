<?php

class Role 
{
    public const ROLE_INVALIDE  = 0;
    public const GERANT         = 1;
    public const SECRETAIRE     = 2;
    public const ROLE_MAX       = 3;

    public static function getRoleToString(): array
    {
        return 
        [
            self::GERANT     => 'Gérant',
            self::SECRETAIRE => 'Secrétaire',
        ];
    }

    // Fonction qui retourne le nom du role et qui permet de savoir si le role est valide
    public static function isSameRole($roleId, $roleCompare)
    {
        if (empty($roleId))
        {
            return false;
        }

        return intval($roleId) == $roleCompare;
    }

    // Fonction qui retourne le nom du role et qui permet de savoir si le role est valide
    public static function isRoleValid(int $role): bool
    {
        return $role > self::ROLE_INVALIDE && $role < self::ROLE_MAX;
    }
}
