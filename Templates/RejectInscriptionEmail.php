<?php

class RejectInscriptionEmail extends TemplateEmail
{
    public static function getSubject($data): string
    {
        return "Votre demande d'accès professionnel a été refusée";
    }

    public static function getEmailContent($data): string
    {
        return "
        Bonjour,

        Nous avons bien pris en compte votre demande d'accès professionnel, mais malheureusement, elle n'a pas pu être acceptée pour le moment.

        Si vous avez des questions ou souhaitez plus de détails, n'hésitez pas à nous contacter.

        À bientôt,
        L'équipe de la salle des spectacles
        ";
    }
}
