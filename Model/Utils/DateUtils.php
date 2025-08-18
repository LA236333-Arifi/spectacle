<?php

class DateUtils
{
    public static function normalizeDate($date)
    {
        if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $date, $m)) 
        {
            return $m[3].'-'.$m[2].'-'.$m[1]; // dd-mm-yyyy -> yyyy-mm-dd
        }

        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date)) 
        {
            return $date; // déjà au bon format
        }

        return null;
    }

    public static function isYearInLimit($year)
    {
        return is_numeric($year) && $year >= 2025 && $year <= 2026; 
    }
}