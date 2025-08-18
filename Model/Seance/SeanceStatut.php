<?php

class SeanceStatut
{
    public const Invalid = 0;
    public const Planifier = 1;
    public const Annuler = 2;
    public const Max = 3;

    public static function getSeanceStatutToString(): array
    {
        return 
        [
            self::Planifier => 'Planifié',
            self::Annuler    => 'Annulé'
        ];
    }

    public static function isStatutValid($statut)
    {
        return $statut > self::Invalid && $statut < self::Max;
    }
}
