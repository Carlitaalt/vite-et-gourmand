<?php

namespace App\Service;

use App\Exception\MetierException;

class ContactService
{
    public function __construct(private NotificationService $notifications = new NotificationService())
    {
    }

    /** Envoie la demande du visiteur par e-mail à l'entreprise. */
    public function envoyer(array $donnees): void
    {
        $titre = trim($donnees['titre'] ?? '');
        $description = trim($donnees['description'] ?? '');
        $email = trim($donnees['email'] ?? '');

        if ($titre === '' || $description === '' || $email === '') {
            throw new MetierException('Veuillez remplir tous les champs.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new MetierException('Adresse e-mail invalide.');
        }
        if (mb_strlen($titre) > 150 || mb_strlen($description) > 5000) {
            throw new MetierException('Votre message est trop long.');
        }

        if (!$this->notifications->messageContact($titre, $description, $email)) {
            throw new MetierException("Votre message n'a pas pu être envoyé. Merci de réessayer plus tard.");
        }
    }
}
