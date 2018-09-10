<?php

namespace Strime\GlobalBundle\Helpers;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Session\Session;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class Form {

    private $container;
    private $translator;
    public $request;

    public function __construct(Container $container) {

        $this->container = $container;
        $this->translator = $this->container->get('translator');
    }



    /**
     * @return object The form
     */
    public function createLoginForm()
    {
        // Check if there is a session
        $session = $this->request->getSession();

        // If no session exists yet
        if(($session == FALSE) || ($session == NULL) || (count($session) == 0)) {
            $session = new Session();
        }

        // If a session exists, check if a user_id is already saved
        else {
            $user_id = $session->get('user_id');
        }

        // Set the login form
        $login_form = $this->container->get('form.factory')->createBuilder()
            ->add('email', EmailType::class, array('label' => '', 'attr' => array('placeholder' => 'front.index.form.email'), 'translation_domain' => 'front_index'))
            ->add('password', PasswordType::class, array('label' => '', 'attr' => array('placeholder' => 'front.index.form.password'), 'translation_domain' => 'front_index'))
            ->add('submit', SubmitType::class, array(
                    'attr' => array('class' => '')
            ))
            ->getForm();

        return $login_form;
    }



    /**
     * @return array An array containing the messages
     */
    public function prepareLoginMessages($login_form) {

        $result = array();
        $session = $this->request->getSession();

        // Check if the signup form is NULL
        if($login_form != NULL) {
            $result['login_form'] = $login_form->createView();
            $result['login_message'] = "";
        }
        else {
            $result['login_form'] = NULL;
            $user_id = $session->get('user_id');

            if(isset($user_id)) {
                $result['login_message'] = $this->translator->trans('front.index.form_message.already_logged_in', array(), 'front_index');
            }
            else {
                $result['login_message'] = $this->translator->trans('front.index.form_message.no_network', array(), 'front_index');
            }
        }

        return $result;
    }


    /**
     * @return object The form
     */
    public function createForgottenPasswordForm() {

        // Set the forgotten password form
        $forgotten_password_form = $this->container->get('form.factory')->createBuilder()
            ->add('email', EmailType::class, array('label' => '', 'attr' => array('placeholder' => 'front.index.form.email'), 'translation_domain' => 'front_index'))
            ->add('submit', SubmitType::class, array(
                    'attr' => array('class' => '')
            ))
            ->getForm();

        return $forgotten_password_form;
    }



    /**
     * @return object The form
     */
    public function createSignupForm() {

        // Check if there is a session
        $session = $this->request->getSession();

        // If no session exists yet
        if(($session == FALSE) || ($session == NULL) || (count($session) == 0)) {
            $session = new Session();
        }

        // If a session exists, check if a user_id is already saved
        else {
            $user_id = $session->get('user_id');
            if(isset($user_id)) {
                return $signup_form = NULL;
            }
        }

        // Get the offers
        $strime_api_url = $this->container->getParameter('strime_api_url');
        $strime_api_token = $this->container->getParameter('strime_api_token');

        // Set the headers
        $headers = array(
            'Accept' => 'application/json',
            'X-Auth-Token' => $strime_api_token,
            'Content-type' => 'application/json'
        );

        // Set the endpoint
        $endpoint = $strime_api_url."offers/get";

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $curl_status = $json_response->getStatusCode();
        $response = json_decode( $json_response->getBody() );

        // Get the free offer ID
        $offer_id = NULL;

        if($curl_status == 200) {
            foreach ($response->{'results'} as $offer) {
                if(($offer->{'price'} == 0) && (strcmp(strtolower($offer->{'name'}), "gratuite") == 0))
                    $offer_id = $offer->{'offer_id'};
            }

            // Set the signup form
            $signup_form = $this->container->get('form.factory')->createBuilder()
                ->add('offer_id', HiddenType::class, array('data' => $offer_id))
                ->add('first_name', TextType::class, array('label' => '', 'attr' => array('placeholder' => 'front.index.form.first_name'), 'translation_domain' => 'front_index'))
                ->add('last_name', TextType::class, array('label' => '', 'attr' => array('placeholder' => 'front.index.form.name'), 'translation_domain' => 'front_index'))
                ->add('email', EmailType::class, array('label' => '', 'attr' => array('placeholder' => 'front.index.form.email'), 'translation_domain' => 'front_index'))
                ->add('password', PasswordType::class, array('label' => '', 'attr' => array('placeholder' => 'front.index.form.password'), 'translation_domain' => 'front_index'))
                ->add('opt_in', ChoiceType::class, array(
                        'choices' => array( 'front.index.form.optin_newsletter' => '1' ),
                        'expanded' => TRUE,
                        'multiple' => TRUE,
                        'choices_as_values' => TRUE, 
                        'translation_domain' => 'front_index'
                ))
                ->add('submit', SubmitType::class, array(
                        'attr' => array('class' => '')
                ))
                ->getForm();
        }
        else {

            // Set the signup form
            $signup_form = $this->container->get('form.factory')->createBuilder()
                ->add('offer_id', HiddenType::class, array('data' => $offer_id))
                ->add('submit', SubmitType::class, array(
                        'attr' => array('class' => '')
                ))
                ->getForm();
        }

        return $signup_form;
    }



    /**
     * @return object The form
     */
    public function createSimpleSignupForm() {

        // Check if there is a session
        $session = $this->request->getSession();

        // If no session exists yet
        if(($session == FALSE) || ($session == NULL) || (count($session) == 0)) {
            $session = new Session();
        }

        // If a session exists, check if a user_id is already saved
        else {
            
            // Set the signup form
            $signup_form = $this->container->get('form.factory')->createBuilder()
                ->add('submit', SubmitType::class, array(
                        'attr' => array('class' => '')
                ))
                ->getForm();
        }

        // Get the offers
        $strime_api_url = $this->container->getParameter('strime_api_url');
        $strime_api_token = $this->container->getParameter('strime_api_token');

        // Set the headers
        $headers = array(
            'Accept' => 'application/json',
            'X-Auth-Token' => $strime_api_token,
            'Content-type' => 'application/json'
        );

        // Set the endpoint
        $endpoint = $strime_api_url."offers/get";

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $curl_status = $json_response->getStatusCode();
        $response = json_decode( $json_response->getBody() );

        // Get the free offer ID
        $offer_id = NULL;

        if($curl_status == 200) {
            foreach ($response->{'results'} as $offer) {
                if(($offer->{'price'} == 0) && (strcmp(strtolower($offer->{'name'}), "gratuite") == 0))
                    $offer_id = $offer->{'offer_id'};
            }

            // Set the signup form
            $signup_form = $this->container->get('form.factory')->createBuilder()
                ->add('offer_id', HiddenType::class, array('data' => $offer_id))
                ->add('name', TextType::class, array('label' => '', 'attr' => array('placeholder' => 'front.index.form.name'), 'translation_domain' => 'front_index'))
                ->add('email', EmailType::class, array('label' => '', 'attr' => array('placeholder' => 'front.index.form.email'), 'translation_domain' => 'front_index'))
                ->add('password', PasswordType::class, array('label' => '', 'attr' => array('placeholder' => 'front.index.form.password'), 'translation_domain' => 'front_index'))
                ->add('submit', SubmitType::class, array(
                        'attr' => array('class' => '')
                ))
                ->getForm();
        }
        else {

            // Set the signup form
            $signup_form = $this->container->get('form.factory')->createBuilder()
                ->add('offer_id', HiddenType::class, array('data' => $offer_id))
                ->add('submit', SubmitType::class, array(
                        'attr' => array('class' => '')
                ))
                ->getForm();
        }

        return $signup_form;
    }


    /**
     * @return array An array containing the messages
     */
    public function prepareSignupMessages($signup_form) {

        $result = array();
        $session = $this->request->getSession();

        // Check if the signup form is NULL
        if($signup_form != NULL) {
            $result['signup_form'] = $signup_form->createView();
            $result['signup_message'] = "";
        }
        else {
            $result['signup_form'] = NULL;
            $user_id = $session->get('user_id');

            if(isset($user_id)) {
                $result['signup_message'] = $this->translator->trans('front.index.form_message.already_logged_in', array(), 'front_index');
            }
            else {
                $result['signup_message'] = $this->translator->trans('front.index.form_message.no_network', array(), 'front_index');
            }
        }

        return $result;
    }



    /**
     * Function which creates the search user form
     * 
     */

    public function createFilterInvoicesForm() {

        // Set the filer invoices form
        $filter_invoices_form = $this->container->get('form.factory')->createBuilder()
            ->add('start', DateType::class, array('format' => 'dd/MM/yyyy', 'label' => '', 'attr' => array('placeholder' => 'Mois')))
            ->add('stop', DateType::class, array('format' => 'dd/MM/yyyy', 'label' => '', 'attr' => array('placeholder' => 'Année')))
            ->add('submit', SubmitType::class, array(
                    'attr' => array('class' => '')
            ))
            ->getForm();

        return $filter_invoices_form;
    }
}