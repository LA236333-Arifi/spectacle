<?php

/**
 * Base classe pour les templates des emails
 */
abstract class TemplateEmail
{
    abstract public static function getSubject($data): string;

    abstract public static function getEmailContent($data): string;
}
