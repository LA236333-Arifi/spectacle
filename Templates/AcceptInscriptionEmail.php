<?php

class AcceptInscriptionEmail extends TemplateEmail
{
    public static function getSubject($data): string
    {
        return "Votre demande d'accès professionnel a été acceptée";
    }

    public static function getEmailContent($data): string
    {
        return "
        Bonjour,

        Félicitations ! Nous avons le plaisir de vous informer que votre demande d'accès professionnel a été acceptée. Vous pouvez désormais accéder à toutes les fonctionnalités réservées à votre rôle.

        Nous vous remercions de votre confiance. Si vous avez des questions, n'hésitez pas à nous contacter à tout moment.

        Cordialement,
        L'équipe de la salle des spectacles
        ";
    }
}
