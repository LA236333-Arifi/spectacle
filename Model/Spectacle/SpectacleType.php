<?php

class SpectacleType
{
    private const Invalid = 0;
    public const Theatre = 1;
    public const ConcertRock = 2;
    public const ConcertClassique = 3;
    public const Humoriste = 4;
    public const Danse = 5;
    private const Max = 6;

    public static function isValid($type)
    {
        return $type > self::Invalid && $type < self::Max;
    }

    public static function needsAuteur($type)
    {
        return in_array($type, [self::Theatre, self::Humoriste, self::Danse]);  
    }
}