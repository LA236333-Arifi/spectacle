<?php

class RequestUtils
{
    public static function isGetMethod(): bool
    {
        return isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    public static function isPostMethod(): bool
    {
        return isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    public static function isPutMethod(): bool
    {
        return isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'PUT';
    }

    public static function isNotGetAndPostMethod(): bool
    {
        return !self::isGetMethod() && !self::isPostMethod();
    }
}
