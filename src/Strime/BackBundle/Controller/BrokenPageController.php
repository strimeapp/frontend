<?php

namespace Strime\BackBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Strime\GlobalBundle\Helpers\StrimeForm;


class BrokenPageController extends Controller
{
    /**
     * @Route("/broken", name="app_broken")
     * @Template("StrimeBackBundle:App:broken-link.html.twig")
     */
    public function brokenLinkAction(Request $request)
    {
        // Get the session data
        $session = $request->getSession();
        $bag = $session->getBag("attributes");

        // Get the user ID
        $user_id = $bag->get('user_id');

        // Create the form object
        $form = $this->container->get('strime.helpers.form');
        $form->request = $request;

        // Create the signup form
        $signup_form = $form->createSignupForm();

        // Prepare messages for the signup form
        $signup = $form->prepareSignupMessages($signup_form);

        // Create the login form
        $login_form = $form->createLoginForm();

        // Prepare messages for the login form
        $login = $form->prepareLoginMessages($login_form);

        // Create the forgotten password form
        $forgotten_password_form = $form->createForgottenPasswordForm();

        return array(
            'body_classes' => 'broken',
            'signup_form' => $signup['signup_form'],
            'signup_message' => $signup['signup_message'],
            'login_form' => $login['login_form'],
            'login_message' => $login['login_message'],
            'forgotten_password_form' => $forgotten_password_form->createView(),
            "user_id" => $user_id,
            "feedback_form" => NULL,
        );
    }
}
