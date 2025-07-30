<?php

include_once 'TemplateEmail.php';

class PasswordResetEmail extends TemplateEmail
{
    public static function getSubject($data): string
    {
        return "Réinitialisation de votre mot de passe - " . SITE_NAME;
    }

    public static function getEmailContent($data): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Réinitialisation de mot de passe</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background: linear-gradient(135deg, #007bff, #0056b3); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
                <h1 style='margin: 0; font-size: 28px;'> " . SITE_NAME . "</h1>
                <p style='margin: 10px 0 0 0; font-size: 16px; opacity: 0.9;'>Réinitialisation de mot de passe</p>
            </div>
            
            <div style='background: #f8f9fa; padding: 40px; border-radius: 0 0 10px 10px; border: 1px solid #dee2e6;'>
                <h2 style='color: #007bff; margin-top: 0;'>Bonjour, </h2>
                
                <p>Vous avez demandé la réinitialisation de votre mot de passe pour votre compte sur " . SITE_NAME . ".</p>
                
                <p>Cliquez sur le bouton ci-dessous pour créer un nouveau mot de passe :</p>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='" . $data['resetLink'] . "' 
                       style='background: #007bff; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block; font-size: 16px;'>
                         Réinitialiser mon mot de passe
                    </a>
                </div>
                
                <p style='font-size: 14px; color: #6c757d; border-left: 4px solid #ffc107; padding-left: 15px; margin: 20px 0;'>
                    <strong> Important :</strong> Ce lien est valide pendant 1 heure seulement. 
                    Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.
                </p>
                
                <p>Si le bouton ne fonctionne pas, copiez et collez ce lien dans votre navigateur :</p>
                <p style='background: #e9ecef; padding: 10px; border-radius: 5px; word-break: break-all; font-family: monospace; font-size: 12px;'>
                    " . $data['resetLink'] . "
                </p>
                
                <hr style='border: none; border-top: 1px solid #dee2e6; margin: 30px 0;'>
                
                <p style='font-size: 12px; color: #6c757d; text-align: center;'>
                    Cet email a été envoyé automatiquement, merci de ne pas y répondre.<br>
                    © " . date('Y') . " " . SITE_NAME . " - Tous droits réservés
                </p>
            </div>
        </body>
        </html>";
    } 
}
