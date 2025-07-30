<?php

include_once 'TemplateEmail.php';

class WelcomeEmail extends TemplateEmail
{
    public static function getSubject($data): string
    {
        return "Bienvenue sur " . SITE_NAME;
    }

    public static function getEmailContent($data): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Bienvenue</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background: linear-gradient(135deg, #28a745, #1e7e34); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
                <h1 style='margin: 0; font-size: 28px;'> " . SITE_NAME . "</h1>
                <p style='margin: 10px 0 0 0; font-size: 16px; opacity: 0.9;'>Bienvenue !</p>
            </div>
            
            <div style='background: #f8f9fa; padding: 40px; border-radius: 0 0 10px 10px; border: 1px solid #dee2e6;'>
                <h2 style='color: #28a745; margin-top: 0;'>Bonjour " . htmlspecialchars($data['username']) . ",</h2>
                
                <p>Bienvenue sur " . SITE_NAME . " ! Votre compte a été créé avec succès.</p>
                
                <p>Vous pouvez maintenant accéder à :</p>
                <ul>
                    <li>La gestion de la programmation</li>
                    <li>Les outils d'administration</li>
                    <li>La consultation des statistiques</li>
                </ul>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='" . SITE_URL . "login' 
                       style='background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block; font-size: 16px;'>
                         Accéder à mon espace
                    </a>
                </div>
                
                <hr style='border: none; border-top: 1px solid #dee2e6; margin: 30px 0;'>
                
                <p style='font-size: 12px; color: #6c757d; text-align: center;'>
                    © " . date('Y') . " " . SITE_NAME . " - Tous droits réservés
                </p>
            </div>
        </body>
        </html>";
    } 
}
