<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public $userName;
    public $resetCode;
    public $resetLink;

    public function __construct($userName, $resetCode, $resetLink)
    {
        $this->userName = $userName;
        $this->resetCode = $resetCode;
        $this->resetLink = $resetLink;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Réinitialisation de votre mot de passe Mbio',
        );
    }

    public function content()
    {
        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background: linear-gradient(135deg, #1A56DB, #1447C0); color: white; padding: 30px; border-radius: 12px; text-align: center;'>
                <h1 style='margin: 0 0 20px 0; font-size: 28px; font-weight: bold;'>🔐 Mbio</h1>
                <p style='margin: 0 0 10px 0; font-size: 16px;'>Réinitialisation de mot de passe</p>
            </div>

            <div style='background: #f8f9fa; padding: 30px; border-radius: 12px; margin-top: 20px;'>
                <p style='margin: 0 0 15px 0; color: #333; font-size: 16px;'>
                    Bonjour <strong>{$this->userName}</strong>,
                </p>
                
                <p style='margin: 0 0 15px 0; color: #333; font-size: 16px;'>
                    Vous avez demandé la réinitialisation de votre mot de passe. Utilisez l'une des méthodes ci-dessous :
                </p>

                <div style='background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #1A56DB;'>
                    <h3 style='margin: 0 0 15px 0; color: #1A56DB; font-size: 18px;'>📱 Méthode 1 : Code de récupération</h3>
                    <p style='margin: 0 0 10px 0; color: #666; font-size: 14px;'>
                        Entrez ce code à 6 chiffres dans l'application :
                    </p>
                    <div style='background: #1A56DB; color: white; padding: 15px; border-radius: 8px; text-align: center; font-size: 24px; font-weight: bold; letter-spacing: 3px;'>
                        {$this->resetCode}
                    </div>
                </div>

                <div style='background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #1447C0;'>
                    <h3 style='margin: 0 0 15px 0; color: #1447C0; font-size: 18px;'>🔗 Méthode 2 : Lien direct</h3>
                    <p style='margin: 0 0 10px 0; color: #666; font-size: 14px;'>
                        Cliquez sur ce lien pour réinitialiser votre mot de passe :
                    </p>
                    <a href='{$this->resetLink}' style='background: #1447C0; color: white; padding: 12px 24px; border-radius: 6px; text-decoration: none; display: inline-block; font-weight: bold; font-size: 16px;'>
                        Réinitialiser mon mot de passe
                    </a>
                </div>

                <div style='background: #fff3cd; padding: 15px; border-radius: 8px; margin: 20px 0; border: 1px solid #ffeaa7;'>
                    <p style='margin: 0; color: #856404; font-size: 14px; text-align: center;'>
                        ⚠️ Ce lien expire dans <strong>60 minutes</strong>
                    </p>
                </div>

                <p style='margin: 30px 0 0 0; color: #666; font-size: 14px; text-align: center;'>
                    Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.
                </p>

                <p style='margin: 10px 0 0 0; color: #666; font-size: 14px; text-align: center;'>
                    Cordialement,<br>
                    L'équipe Mbio 🚕
                </p>
            </div>
        </div>
        ";
    }

    public function attachments(): array
    {
        return [];
    }
}
