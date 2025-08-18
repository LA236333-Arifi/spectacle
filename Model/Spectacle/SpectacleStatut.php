<?php

class SpectacleStatut
{
    private const Invalid = 0;
    public const SansSeance = 1;
    public const EnCours = 2;
    public const Cloturer = 3;
    private const Max = 4;

    public static function getSpectacleStatutToString(): array
    {
        return 
        [
            self::SansSeance => 'Sans séance',
            self::EnCours    => 'En cours',
            self::Cloturer   => 'Clôturé'
        ];
    }

    public static function isValid($type)
    {
        return $type > self::Invalid && $type < self::Max;
    }
}
