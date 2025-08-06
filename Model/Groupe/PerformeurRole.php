<?php

class PerformeurRole
{
    public const Invalid = 0;
    public const Danseur = 1;
    public const Humouriste = 2;
    public const Acteur = 3;
    public const Chanteur = 4;
    public const Musicien = 5;
    public const Max = 6;
    
    public static function isRoleValid($roleId)
    {
        return $roleId > self::Invalid && $roleId < self::Max;
    }
}