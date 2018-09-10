<?php

namespace Strime\BackBundle\Helpers;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Session\Session;

class Contacts {

    private $container;
    public $request;

    public function __construct(Container $container) {

        $this->container = $container;
    }


    /**
     * Function which saves the ID and email of a contact in the session.
     *
     */

    public function saveContactDetailsInSession($contact_id, $contact_email) {

        $session = new Session();
        $session->set('contact_id', $contact_id);
        $session->set('contact_email', $contact_email);
        $session->save();
    }
}
