<?php

namespace App\Core;

use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Envoi technique des e-mails via SMTP (PHPMailer). La configuration vient des variables d'environnement :
 * Mailpit en local, un vrai fournisseur SMTP en production. Aucun identifiant n'est écrit dans le code.
 */
class Mailer
{
    public function envoyer(string $destinataire, string $sujet, string $html, ?string $repondreA = null): bool
    {
        $hote = getenv('MAIL_HOST');
        if (!$hote) {
            error_log("[MAIL] Envoi non configuré : « $sujet » non envoyé à $destinataire");
            return false;
        }

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = $hote;
            $mail->Port = (int) (getenv('MAIL_PORT') ?: 587);
            $mail->CharSet = PHPMailer::CHARSET_UTF8;

            if (getenv('MAIL_USER')) {
                $mail->SMTPAuth = true;
                $mail->Username = getenv('MAIL_USER');
                $mail->Password = (string) getenv('MAIL_PASSWORD');
            }

            match (getenv('MAIL_ENCRYPTION')) {
                'ssl' => $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS,
                'tls' => $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS,
                'none' => $mail->SMTPAutoTLS = false,
                default => null,
            };

            $mail->setFrom(getenv('MAIL_FROM') ?: 'noreply@vite-gourmand.fr', 'Vite & Gourmand');
            $mail->addAddress($destinataire);
            if ($repondreA !== null) {
                $mail->addReplyTo($repondreA);
            }

            $mail->isHTML(true);
            $mail->Subject = $sujet;
            $mail->Body = $html;
            $mail->AltBody = trim(html_entity_decode(strip_tags(str_replace(['<br>', '</p>'], "\n", $html))));

            $mail->send();
            return true;
        } catch (PHPMailerException $e) {
            // Un e-mail non envoyé ne doit pas bloquer l'action de l'utilisateur : on journalise seulement
            error_log('[MAIL] ' . $mail->ErrorInfo);
            return false;
        }
    }
}
