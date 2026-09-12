<?php

namespace App\Controller;

use App\Core\Flash;
use App\Exception\MetierException;
use App\Service\ContactService;

class ContactController extends AbstractController
{
    public function __construct(private ContactService $contact = new ContactService())
    {
    }

    public function index(): void
    {
        if ($this->estPost()) {
            $this->verifierCsrf('contact.php');

            try {
                $this->contact->envoyer($_POST);
                Flash::succes('Votre message a bien été envoyé ! Nous vous répondrons par e-mail.');
            } catch (MetierException $e) {
                Flash::erreur($e->getMessage());
                Flash::conserverSaisie($_POST);
            }

            $this->rediriger('contact.php');
        }

        $this->render('contact', [
            'pageTitle' => 'Contact',
            'currentPage' => 'contact',
            'saisie' => Flash::saisie(),
        ]);
    }
}
