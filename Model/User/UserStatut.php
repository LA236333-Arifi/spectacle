<?php

class UserStatut
{
    public const Invalid                = 0;
    public const NonValide_Et_Inactif   = 1;
    public const Valide_Et_Inactif      = 2;
    public const Valide_Et_Actif        = 3;
    public const Max                    = 4;

    public static function isValid($type)
    {
        return $type > self::Invalid && $type < self::Max;
    }
}
