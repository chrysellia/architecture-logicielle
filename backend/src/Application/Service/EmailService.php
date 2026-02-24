<?php

declare(strict_types=1);

namespace App\Application\Service;

class EmailService
{
    private string $fromEmail;
    private string $fromName;

    public function __construct()
    {
        $this->fromEmail = $_ENV['MAIL_FROM_EMAIL'] ?? 'noreply@mini-erp.com';
        $this->fromName = $_ENV['MAIL_FROM_NAME'] ?? 'Mini ERP';
    }

    public function sendPasswordResetEmail(string $toEmail, string $resetToken): bool
    {
        $resetUrl = "http://localhost:5173/reset-password?token=" . urlencode($resetToken);
        
        $subject = "Réinitialisation de votre mot de passe - Mini ERP";
        
        $message = $this->getEmailTemplate($resetUrl);
        
        $headers = [
            'From' => "{$this->fromName} <{$this->fromEmail}>",
            'Reply-To' => $this->fromEmail,
            'Content-Type' => 'text/html; charset=UTF-8',
            'MIME-Version' => '1.0'
        ];

        // En développement, on logue l'email au lieu de l'envoyer
        if ($_ENV['APP_ENV'] === 'development') {
            $this->logEmail($toEmail, $subject, $message);
            return true;
        }

        return mail($toEmail, $subject, $message, $headers);
    }

    private function getEmailTemplate(string $resetUrl): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Réinitialisation de mot de passe</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #2563eb; color: white; padding: 20px; text-align: center; }
                .content { padding: 30px; background: #f9fafb; }
                .button { display: inline-block; background: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 20px 0; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                .security-note { background: #fef3c7; border: 1px solid #f59e0b; padding: 15px; border-radius: 6px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🔐 Mini ERP</h1>
                    <p>Réinitialisation de votre mot de passe</p>
                </div>
                
                <div class='content'>
                    <p>Bonjour,</p>
                    
                    <p>Vous avez demandé la réinitialisation de votre mot de passe pour votre compte Mini ERP.</p>
                    
                    <p>Cliquez sur le bouton ci-dessous pour définir un nouveau mot de passe :</p>
                    
                    <div style='text-align: center;'>
                        <a href='{$resetUrl}' class='button'>Réinitialiser mon mot de passe</a>
                    </div>
                    
                    <div class='security-note'>
                        <strong>⚠️ Important :</strong><br>
                        - Ce lien expire dans 1 heure<br>
                        - Si vous n'avez pas demandé cette réinitialisation, ignorez cet email<br>
                        - Ne partagez jamais ce lien avec personne
                    </div>
                    
                    <p>Si le bouton ne fonctionne pas, copiez et collez ce lien dans votre navigateur :</p>
                    <p style='word-break: break-all; color: #2563eb;'>{$resetUrl}</p>
                </div>
                
                <div class='footer'>
                    <p>Cet email a été envoyé automatiquement par Mini ERP</p>
                    <p>© 2026 Mini ERP - Tous droits réservés</p>
                </div>
            </div>
        </body>
        </html>";
    }

    private function logEmail(string $toEmail, string $subject, string $message): void
    {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'to' => $toEmail,
            'subject' => $subject,
            'message' => $message
        ];
        
        $logFile = '/tmp/email_log.json';
        $logs = [];
        
        if (file_exists($logFile)) {
            $logs = json_decode(file_get_contents($logFile), true) ?: [];
        }
        
        $logs[] = $logEntry;
        
        // Garder seulement les 10 derniers emails
        if (count($logs) > 10) {
            $logs = array_slice($logs, -10);
        }
        
        file_put_contents($logFile, json_encode($logs, JSON_PRETTY_PRINT));
        
        // En développement, afficher aussi dans les logs Symfony
        error_log("Email sent to: {$toEmail}, Subject: {$subject}");
        error_log("Reset URL: http://localhost:5173/reset-password?token=" . substr($message, strpos($message, 'token=') + 6, 50));
    }
}
