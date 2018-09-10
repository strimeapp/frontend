<?php

namespace Strime\BackBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Strime\GlobalBundle\Payment\Payment;
use Strime\GlobalBundle\Assets\Avatar;
use Strime\Slackify\Webhooks\Webhook;

use Strime\GlobalBundle\Entity\TaxRate;
use Strime\GlobalBundle\Entity\EmailConfirmationToken;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class ProfileController extends Controller
{
    /**
     * @Route("/profile/password", name="app_profile_password")
     * @Template("StrimeBackBundle:App:profile-password.html.twig")
     */
    public function profilePasswordAction(Request $request)
    {
        // Get the session data
        $session = $request->getSession();
        $bag = $session->getBag("attributes");

        // Get the user ID
        $user_id = $bag->get('user_id');

        // Check if the user is logged in
        if($user_id == NULL)
            return $this->redirectToRoute('home');

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

        // Create the form object
        $form = $this->container->get('strime.back_helpers.form');
        $form->request = $request;

        // Create the form
        $edit_profile_password_form = $this->createFormBuilder()
            ->add('old_password', PasswordType::class, array('label' => '', 'attr' => array('placeholder' => 'back.controller_app.profile_pwd.current_pwd')))
            ->add('new_password', PasswordType::class, array('label' => '', 'attr' => array('placeholder' => 'back.controller_app.profile_pwd.new_pwd')))
            ->add('submit', SubmitType::class, array(
                    'attr' => array('class' => '')
            ))
            ->getForm();

        // Create the share by email form
        $feedback_form = $form->createFeedbackBoxForm($user_id, $bag->get('email'), $bag->get('first_name')." ".$bag->get('last_name'));

        // Handle the request
        $edit_profile_password_form->handleRequest($request);

        // If the form is submitted
        if($edit_profile_password_form->isSubmitted()) {

            // If the submitted form is valid
            if($edit_profile_password_form->isValid()) {

                // Get the data
                $old_password = $edit_profile_password_form->get('old_password')->getData();
                $new_password = $edit_profile_password_form->get('new_password')->getData();

                // Get the parameters
                $strime_api_url = $this->container->getParameter('strime_api_url');
                $strime_api_token = $this->container->getParameter('strime_api_token');

                // Set the headers
                $headers = array(
                    'Accept' => 'application/json',
                    'X-Auth-Token' => $strime_api_token,
                    'Content-type' => 'application/json'
                );

                // Set the endpoint
                $endpoint = $strime_api_url."user/".$user_id."/edit";

                // Prepare the parameters
                $params = array(
                    'old_password' => $old_password,
                    'new_password' => $new_password,
                );

                // Send the request
                $client = new \GuzzleHttp\Client();
                $json_response = $client->request('PUT', $endpoint, [
                    'headers' => $headers,
                    'http_errors' => false,
                    'json' => $params
                ]);
                $curl_status = $json_response->getStatusCode();
                $response = json_decode( $json_response->getBody() );

                // Return the result
                if(($curl_status == 400) && (strcmp($response->{'error_source'}, 'password_incorrect') == 0)) {
                    $this->addFlash(
                        'error',
                        $this->get('translator')->trans('back.controller_app.profile_pwd.incorrect_pwd', array(), 'back_controller_app')
                    );
                }
                elseif($curl_status == 200) {

                    // Prepare the flash message
                    $this->addFlash(
                        'success',
                        $this->get('translator')->trans('back.controller_app.profile_pwd.new_pwd_saved', array(), 'back_controller_app')
                    );
                }
                else {
                    $this->addFlash(
                        'error',
                        $this->get('translator')->trans('back.controller_app.profile_pwd.error_occured_while_updating_pwd', array(), 'back_controller_app')
                    );
                }
            }

            // If the submitted form is not valid
            else {
                $this->addFlash(
                    'error',
                    $this->get('translator')->trans('back.controller_app.profile_pwd.all_fields_not_provided', array(), 'back_controller_app')
                );
            }
        }

        return array(
            'body_classes' => 'profile',
            'profile_section' => 'profile_password',
            'signup_form' => $signup['signup_form'],
            'signup_message' => $signup['signup_message'],
            'login_form' => $login['login_form'],
            'login_message' => $login['login_message'],
            'forgotten_password_form' => $forgotten_password_form->createView(),
            "user_id" => $user_id,
            "edit_profile_password_form" => $edit_profile_password_form->createView(),
            "feedback_form" => $feedback_form->createView(),
        );
    }



    /**
     * @Route("/profile/billing/{action}/{parameter}", defaults={"action": NULL, "parameter": NULL}, name="app_profile_billing")
     * @Template("StrimeBackBundle:App:profile-billing.html.twig")
     */
    public function profileBillingAction(Request $request, $action, $parameter)
    {
        // Get the session data
        $session = $request->getSession();
        $bag = $session->getBag("attributes");

        // Get the user ID
        $user_id = $bag->get('user_id');

        // Check if the user is logged in
        if($user_id == NULL)
            return $this->redirectToRoute('home');

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

        // Create the form object
        $form = $this->container->get('strime.back_helpers.form');
        $form->request = $request;

        // Get the user details
        $offer_id = $bag->get('offer');

        // Create the feedback form
        $feedback_form = $form->createFeedbackBoxForm($user_id, $bag->get('email'), $bag->get('first_name')." ".$bag->get('last_name'));

        // Get the entity manager
        $em = $this->getDoctrine()->getManager();

        // Get the API parameters
        $strime_api_url = $this->container->getParameter('strime_api_url');
        $strime_api_token = $this->container->getParameter('strime_api_token');

        // Get the invoice rate
        $tax_rate_details = new TaxRate;
        $tax_rate_details = $em->getRepository('StrimeGlobalBundle:TaxRate')->findOneBy(array('country_code' => $bag->get('country')));

        // Set the headers
        $headers = array(
            'Accept' => 'application/json',
            'X-Auth-Token' => $strime_api_token,
            'Content-type' => 'application/json'
        );

        // Get the list of all the offers
        // Set the endpoint
        $endpoint = $strime_api_url."offers/get";

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $curl_status = $json_response->getStatusCode();
        $json_response = json_decode( $json_response->getBody() );

        if($curl_status == 200) {

            $offers = $json_response->{'results'};
        }
        else {
            $offers = NULL;
        }

        // Get the details of the user
        // Set the endpoint to get the details of the user
        $endpoint = $strime_api_url."user/".$user_id."/get";

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $curl_status = $json_response->getStatusCode();
        $user_details = json_decode( $json_response->getBody() );
        $user_details = $user_details->{'results'};


        // Create the form to edit the billing details of the user
        $edit_billing_profile_form = $this->createFormBuilder()
            ->add('company', TextType::class, array('label' => '', 'attr' => array('placeholder' => 'back.controller_app.profile_billing.company'), 'data' => $user_details->{'company'}))
            ->add('vat_number', TextType::class, array('label' => '', 'attr' => array('placeholder' => 'back.controller_app.profile_billing.vat_number'), 'data' => $user_details->{'vat_number'}))
            ->add('address', TextType::class, array('label' => '', 'attr' => array('placeholder' => 'back.controller_app.profile_billing.address'), 'data' => $user_details->{'address'}))
            ->add('address_more', TextType::class, array('label' => '', 'attr' => array('placeholder' => 'back.controller_app.profile_billing.address_more_details'), 'data' => $user_details->{'address_more'}))
            ->add('zip', TextType::class, array('label' => '', 'attr' => array('placeholder' => 'back.controller_app.profile_billing.postal_code'), 'data' => $user_details->{'zip'}))
            ->add('state', TextType::class, array('label' => '', 'attr' => array('placeholder' => 'back.controller_app.profile_billing.province'), 'data' => $user_details->{'state'}))
            ->add('city', TextType::class, array('label' => '', 'attr' => array('placeholder' => 'back.controller_app.profile_billing.city'), 'data' => $user_details->{'city'}))
            ->add('country', CountryType::class, array('label' => '', 'attr' => array('placeholder' => 'back.controller_app.profile_billing.country'), 'data' => $user_details->{'country'}, 'preferred_choices' => array('FR', 'GB', 'US', 'ES')))
            ->add('submit', SubmitType::class, array(
                    'attr' => array('class' => '')
            ))
            ->getForm();

        // Handle the request
        if(isset($action) && (strcmp($action, "edit-billing-profile") == 0)) {
            $edit_billing_profile_form->handleRequest($request);

            // If the form is submitted
            if($edit_billing_profile_form->isSubmitted()) {

                // If the submitted form is valid
                if($edit_billing_profile_form->isValid()) {

                    // Get the data
                    $company = trim( $edit_billing_profile_form->get('company')->getData() );
                    $vat_number = trim( $edit_billing_profile_form->get('vat_number')->getData() );
                    $address = trim( $edit_billing_profile_form->get('address')->getData() );
                    $address_more = trim( $edit_billing_profile_form->get('address_more')->getData() );
                    $zip = trim( $edit_billing_profile_form->get('zip')->getData() );
                    $city = trim( $edit_billing_profile_form->get('city')->getData() );
                    $state = trim( $edit_billing_profile_form->get('state')->getData() );
                    $country = trim( $edit_billing_profile_form->get('country')->getData() );
                    $vat_number = trim( $edit_billing_profile_form->get('vat_number')->getData() );

                    // If the user is a paying user
                    if(($user_details->{'offer'}->{'price'} != 0) &&
                        ((strlen($address) == 0) || (strlen($zip) == 0) || (strlen($city) == 0) || (strlen($country) == 0))) {

                        $this->addFlash(
                            'error',
                            $this->get('translator')->trans('back.controller_app.profile_billing.address_is_mandatory_for_invoices', array(), 'back_controller_app')
                        );
                    }

                    // If the address is valid depending on the offer chosen by the user
                    // Update his information
                    else {


                        // Prepare the parameters
                        $params = array(
                            'user_id' => $user_id,
                            'company' => $company,
                            'address' => $address,
                            'address_more' => $address_more,
                            'zip' => $zip,
                            'city' => $city,
                            'state' => $state,
                            'country' => $country,
                            'vat_number' => $vat_number
                        );

                        if(strlen($company) == 0)
                            $params['empty_company'] = 1;
                        if(strlen($address) == 0)
                            $params['empty_address'] = 1;
                        if(strlen($address_more) == 0)
                            $params['empty_address_more'] = 1;
                        if(strlen($zip) == 0)
                            $params['empty_zip'] = 1;
                        if(strlen($city) == 0)
                            $params['empty_city'] = 1;
                        if(strlen($state) == 0)
                            $params['empty_state'] = 1;
                        if(strlen($country) == 0)
                            $params['empty_country'] = 1;
                        if(strlen($vat_number) == 0)
                            $params['empty_vat_number'] = 1;

                        // Set the endpoint to get the details of the user
                        $endpoint = $strime_api_url."user/".$user_id."/edit";

                        // Send the request
                        $client = new \GuzzleHttp\Client();
                        $json_response = $client->request('PUT', $endpoint, [
                            'headers' => $headers,
                            'http_errors' => false,
                            'json' => $params
                        ]);
                        $curl_status = $json_response->getStatusCode();
                        $response = json_decode( $json_response->getBody() );

                        // Return the result
                        if($curl_status == 200) {

                            // Update the session
                            $session->set('company', $company);
                            $session->set('vat_number', $vat_number);
                            $session->set('address', $address);
                            $session->set('address_more', $address_more);
                            $session->set('zip', $zip);
                            $session->set('city', $city);
                            $session->set('state', $state);
                            $session->set('country', $country);
                            $session->set('vat_number', $vat_number);
                            $session->save();

                            // Prepare the flash message
                            $this->addFlash(
                                'success',
                                $this->get('translator')->trans('back.controller_app.profile_billing.changes_have_been_saved', array(), 'back_controller_app')
                            );
                        }
                        else {
                            $this->addFlash(
                                'error',
                                $this->get('translator')->trans('back.controller_app.profile_billing.error_occured_while_updating_your_profile', array(), 'back_controller_app')
                            );
                        }
                    }
                }

                // We redirect the user to the URL without parameters
                return $this->redirectToRoute('app_profile_billing');
            }
        }


        // Get the details of the offer
        $offer_details = NULL;

        if($offers != NULL) {
            foreach ($offers as $offer) {

                if(strcmp($offer->{'offer_id'}, $bag->get('offer')) == 0)
                    $offer_details = $offer;
            }
        }


        // Recalculate the storage used
        $storage_allowed = $offer_details->{'storage_allowed'} * $this->container->getParameter('strime_api_storage_allowed_multiplier'); // To get storage in Mo.
        $storage_used_in_percent = (int)round(($bag->get('storage_used') / $storage_allowed) * 100);
        $session->set('storage_allowed', $storage_allowed);
        $session->set('storage_used_in_percent', $storage_used_in_percent);
        $session->save();



        // Get the list of invoices of the user
        // Set the endpoint to get the list of invoices
        $endpoint = $strime_api_url."invoices/".$user_id."/get";

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $invoices_curl_status = $json_response->getStatusCode();
        $invoices_json_response = json_decode( $json_response->getBody() );

        $invoices = NULL;

        // If we properly got the list of answers
        if($invoices_curl_status == 200) {

            // Create a variable
            $invoices = $invoices_json_response->{'results'};
        }

        return array(
            'body_classes' => 'profile',
            'profile_section' => 'profile_billing',
            'signup_form' => $signup['signup_form'],
            'signup_message' => $signup['signup_message'],
            'login_form' => $login['login_form'],
            'login_message' => $login['login_message'],
            'forgotten_password_form' => $forgotten_password_form->createView(),
            "user_id" => $user_id,
            "offer" => $offer_details,
            "offers" => $offers,
            "edit_billing_profile_form" => $edit_billing_profile_form->createView(),
            "feedback_form" => $feedback_form->createView(),
            "invoices" => $invoices,
            "user" => $user_details
        );
    }



    /**
     * @Route("/profile/email-notifications", name="app_profile_notifications")
     * @Template("StrimeBackBundle:App:profile-email-notifications.html.twig")
     */
    public function profileEmailNotificationsAction(Request $request)
    {
        // Get the session data
        $session = $request->getSession();
        $bag = $session->getBag("attributes");

        // Get the user ID
        $user_id = $bag->get('user_id');

        // Check if the user is logged in
        if($user_id == NULL)
            return $this->redirectToRoute('home');

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

        // Create the form object
        $form = $this->container->get('strime.back_helpers.form');
        $form->request = $request;

        // Create the share by email form
        $feedback_form = $form->createFeedbackBoxForm($user_id, $bag->get('email'), $bag->get('first_name')." ".$bag->get('last_name'));


        // Get the details of the user
        // Get the API parameters
        $strime_api_url = $this->container->getParameter('strime_api_url');
        $strime_api_token = $this->container->getParameter('strime_api_token');

        // Set the headers
        $headers = array(
            'Accept' => 'application/json',
            'X-Auth-Token' => $strime_api_token,
            'Content-type' => 'application/json'
        );

        // Set the endpoint to get the details of the user
        $endpoint = $strime_api_url."user/".$user_id."/get";

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $user_details_curl_status = $json_response->getStatusCode();
        $user_details_json_response = json_decode( $json_response->getBody() );

        $user_details = NULL;

        // If we properly got the list of answers
        if($user_details_curl_status == 200) {

            // Create a variable
            $user_details = $user_details_json_response->{'results'};
        }

        // Create the share by email form
        $edit_notifications_form = $form->createEditNotificationsForm($user_details);

        // Handle the request
        $edit_notifications_form->handleRequest($request);

        // If the form is submitted
        if($edit_notifications_form->isSubmitted()) {

            // If the submitted form is valid
            if($edit_notifications_form->isValid()) {

                // Get the data
                $opt_in = $edit_notifications_form->get('opt_in')->getData();
                $mail_notification = $edit_notifications_form->get('mail_notification')->getData();
                $slack_webhook_url = $edit_notifications_form->get('slack_webhook_url')->getData();

                // Set the endpoint
                $endpoint = $strime_api_url."user/".$user_id."/edit";

                // Prepare the parameters
                $params = array(
                    'opt_in' => $opt_in,
                    'mail_notification' => $mail_notification,
                    'slack_webhook_url' => $slack_webhook_url,
                );

                // Send the request
                $client = new \GuzzleHttp\Client();
                $json_response = $client->request('PUT', $endpoint, [
                    'headers' => $headers,
                    'http_errors' => false,
                    'json' => $params
                ]);
                $curl_status = $json_response->getStatusCode();
                $response = json_decode( $json_response->getBody() );

                // If the modifications have been saved
                if($curl_status == 200) {

                    // Prepare the flash message
                    $this->addFlash(
                        'success',
                        $this->get('translator')->trans('back.controller_app.profile.changes_saved', array(), 'back_controller_app')
                    );

                    // Set the endpoint to get the details of the user
                    $endpoint = $strime_api_url."user/".$user_id."/get";

                    // Send the request
                    $client = new \GuzzleHttp\Client();
                    $json_response = $client->request('GET', $endpoint, [
                        'headers' => $headers,
                        'http_errors' => false
                    ]);
                    $user_details_curl_status = $json_response->getStatusCode();
                    $user_details_json_response = json_decode( $json_response->getBody() );

                    $user_details = NULL;

                    // If we properly got the list of answers
                    if($user_details_curl_status == 200) {

                        // Create a variable
                        $user_details = $user_details_json_response->{'results'};
                    }

                    // Re-create the share by email form
                    $edit_notifications_form = $form->createEditNotificationsForm($user_details);
                }

                // If the modifications have NOT been saved
                else {
                    $this->addFlash(
                        'error',
                        $this->get('translator')->trans('back.controller_app.profile_notifications.error_occured_while_saving_settings', array(), 'back_controller_app')
                    );
                }
            }

            // If the submitted form is not valid
            else {

                $this->addFlash(
                    'error',
                    $this->get('translator')->trans('back.controller_app.profile.all_fields_not_provided', array(), 'back_controller_app')
                );
            }
        }

        return array(
            'body_classes' => 'profile',
            'profile_section' => 'profile_notifications',
            'signup_form' => $signup['signup_form'],
            'signup_message' => $signup['signup_message'],
            'login_form' => $login['login_form'],
            'login_message' => $login['login_message'],
            'forgotten_password_form' => $forgotten_password_form->createView(),
            "user_id" => $user_id,
            "feedback_form" => $feedback_form->createView(),
            "edit_notifications_form" => $edit_notifications_form->createView(),
            "user" => $user_details
        );
    }



    /**
     * @Route("/profile/integrations", name="app_profile_integrations")
     * @Template("StrimeBackBundle:App:profile-integrations.html.twig")
     */
    public function profileIntegrationsAction(Request $request)
    {
        // Get the session data
        $session = $request->getSession();
        $bag = $session->getBag("attributes");

        // Get the user ID
        $user_id = $bag->get('user_id');

        // Check if the user is logged in
        if($user_id == NULL)
            return $this->redirectToRoute('home');

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

        // Create the form object
        $form = $this->container->get('strime.back_helpers.form');
        $form->request = $request;

        // Create the share by email form
        $feedback_form = $form->createFeedbackBoxForm($user_id, $bag->get('email'), $bag->get('first_name')." ".$bag->get('last_name'));


        // Get the details of the user
        // Get the API parameters
        $strime_api_url = $this->container->getParameter('strime_api_url');
        $strime_api_token = $this->container->getParameter('strime_api_token');

        // Set the headers
        $headers = array(
            'Accept' => 'application/json',
            'X-Auth-Token' => $strime_api_token,
            'Content-type' => 'application/json'
        );

        // Set the endpoint to get the details of the user
        $endpoint = $strime_api_url."user/".$user_id."/get";

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $user_details_curl_status = $json_response->getStatusCode();
        $user_details_json_response = json_decode( $json_response->getBody() );

        $user_details = NULL;

        // If we properly got the list of answers
        if($user_details_curl_status == 200) {

            // Create a variable
            $user_details = $user_details_json_response->{'results'};
        }

        return array(
            'body_classes' => 'profile',
            'profile_section' => 'profile_integrations',
            'signup_form' => $signup['signup_form'],
            'signup_message' => $signup['signup_message'],
            'login_form' => $login['login_form'],
            'login_message' => $login['login_message'],
            'forgotten_password_form' => $forgotten_password_form->createView(),
            "user_id" => $user_id,
            "feedback_form" => $feedback_form->createView(),
            "user" => $user_details
        );
    }



    /**
     * @Route("/profile/{target}/{action}", defaults={"target": NULL, "action": NULL}, name="app_profile")
     * @Template("StrimeBackBundle:App:profile.html.twig")
     */
    public function profileAction(Request $request, $target, $action)
    {
        // Get the session data
        $session = $request->getSession();
        $bag = $session->getBag("attributes");

        // Get the user ID
        $user_id = $bag->get('user_id');
        $avatar = $bag->get('avatar');

        // Check if the user is logged in
        if($user_id == NULL)
            return $this->redirectToRoute('home');

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

        // Create the form object
        $form = $this->container->get('strime.back_helpers.form');
        $form->request = $request;

        // Create the feedback form
        $feedback_form = $form->createFeedbackBoxForm($user_id, $bag->get('email'), $bag->get('first_name')." ".$bag->get('last_name'));

        // Get the user details to check if a locale is saved in his profile
        $users_helper = $this->container->get('strime.back_helpers.users');
        $user_details = $users_helper->getUserDetails( $user_id );

        // Set the current locale
        if(($user_details == NULL) || ($user_details->{'locale'} == NULL)) {
            $current_locale = "fr";
        }
        else {
            $current_locale = $user_details->{'locale'};
        }


        // Create the form
        $edit_profile_form = $form->createUpdateProfileForm($bag->get('first_name'), $bag->get('last_name'), $bag->get('email'), $current_locale);

        // Handle the request
        $edit_profile_form->handleRequest($request);

        // If the form is submitted
        if($edit_profile_form->isSubmitted()) {

            // If the submitted form is valid
            if($edit_profile_form->isValid()) {

                // Get the data
                $first_name = trim( $edit_profile_form->get('first_name')->getData() );
                $last_name = trim( $edit_profile_form->get('last_name')->getData() );
                $email = trim( $edit_profile_form->get('email')->getData() );
                $locale = $edit_profile_form->get('locale')->getData();

                // Make sure that all the required information are provided
                if((strlen($first_name) == 0) || (strlen($last_name) == 0) || (strlen($email) == 0)) {
                    $this->addFlash(
                        'error',
                        $this->get('translator')->trans('back.controller_app.profile.you_must_provide_firstname_lastname_email', array(), 'back_controller_app')
                    );
                }

                // If all the required data are provided
                else {

                    // Check if the email address changed
                    if(strcmp($email, $bag->get('email')) != 0) {

                        // Set a flag to check if the email has changed
                        $email_has_changed = TRUE;

                        // Check if the email is a valid email address
                        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
                            $this->addFlash(
                                'error',
                                $this->get('translator')->trans('back.controller_app.profile.email_address_invalid', array(), 'back_controller_app')
                            );

                            // We redirect the user to the URL without parameters
                            return $this->redirectToRoute('app_profile');
                        }
                    }
                    else {

                        // Set a flag to check if the email has changed
                        $email_has_changed = FALSE;
                    }

                    // Set upload parameters
                    $folder =  __DIR__.'/../../../../web/uploads/avatars/';
                    $name = "";

                    foreach($request->files as $uploadedFile) {

                        if($uploadedFile["avatar"] != NULL) {

                            // Get the original filename
                            $image_name = $uploadedFile["avatar"]->getClientOriginalName();

                            // Explode the filename to get the extension
                            $image_name_parts = explode(".", $image_name);
                            $image_extension = $image_name_parts[ count($image_name_parts) - 1 ];

                            // Remove the previous file
                            if( file_exists( $session->get('avatar') ) )
                                unlink($session->get('avatar'));

                            // Set the filename
                            $name = $user_id . "-" . time() . "." . $image_extension;

                            // Check if the file exists, if yes, we delete it
                            if( file_exists($folder.$name) )
                                unlink($folder.$name);

                            // Upload the file
                            $file = $uploadedFile["avatar"]->move($folder, $name);

                            // Prepare the data for the API
                            $avatar = "uploads/avatars/".$name;
                        }

                        // If no avatar has been uploaded
                        else {
                            $avatar = NULL;
                        }
                    }

                    // Get the parameters
                    $strime_api_url = $this->container->getParameter('strime_api_url');
                    $strime_api_token = $this->container->getParameter('strime_api_token');

                    // Set the headers
                    $headers = array(
                        'Accept' => 'application/json',
                        'X-Auth-Token' => $strime_api_token,
                        'Content-type' => 'application/json'
                    );

                    // Set the endpoint
                    $endpoint = $strime_api_url."user/".$user_id."/edit";

                    // Prepare the parameters
                    $params = array(
                        'user_id' => $user_id,
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                        'email' => $email,
                        'avatar' => $avatar,
                        'locale' => $locale
                    );

                    // Send the request
                    $client = new \GuzzleHttp\Client();
                    $json_response = $client->request('PUT', $endpoint, [
                        'headers' => $headers,
                        'http_errors' => false,
                        'json' => $params
                    ]);
                    $curl_status = $json_response->getStatusCode();
                    $response = json_decode( $json_response->getBody() );

                    // Return the result
                    if(($curl_status == 400) && (strcmp($response->{'error_source'}, 'email_already_used') == 0)) {

                        // Reset the avatar to prevent an error of display
                        $avatar = $bag->get('avatar');

                        $this->addFlash(
                            'error',
                            $this->get('translator')->trans('back.controller_app.profile.email_already_used', array(), 'back_controller_app')
                        );
                    }
                    elseif($curl_status == 200) {

                        // Change the data in the session
                        $session->set('first_name', $first_name);
                        $session->set('last_name', $last_name);
                        $session->set('email', $email);

                        // If the avatar is set, update it in the session
                        if($avatar != NULL)
                            $session->set('avatar', $avatar);

                        // If the avatar variable is set to NULL, set it back to the session value for the rendering
                        else
                            $avatar = $bag->get('avatar');

                        // If the locale changed, update it in the session.
                        if(strcmp($locale, $session->get('_locale')) != 0) {
                            $session->set('_locale', $locale);
                            $request->setLocale( $locale );
                            $this->container->get('translator')->setLocale( $locale );

                            // Update the form
                            $edit_profile_form = $form->createUpdateProfileForm($first_name, $last_name, $email, $locale);
                        }
                        $session->save();

                        // If the email has changed
                        if($email_has_changed) {

                            // Create a new email confirmation token
                            $signup_helper = $this->container->get('strime.helpers.signup');
                            $email_confirmation_token = $signup_helper->saveEmailConfirmationToken( $user_id );

                            // Send an email with the link to confirm the email address
                            $message = \Swift_Message::newInstance()
                                ->setSubject('Strime - ' . $this->get('translator')->trans('back.controller_app.profile.confirm_email_address', array(), 'back_controller_app'))
                                ->setFrom(array('contact@strime.io' => 'Strime'))
                                ->setTo( array( $email => $first_name." ".$last_name ) )
                                ->setBody(
                                    $this->renderView(
                                        // app/Resources/views/debug/debug.html.twig
                                        'emails/email_confirmation.html.twig',
                                        array(
                                            'current_year' => date('Y'),
                                            'first_name' => $first_name,
                                            'user_id' => $user_id,
                                            'email_confirmation_token' => $email_confirmation_token,
                                            'locale' => $session->get('_locale')
                                        )
                                    ),
                                    'text/html'
                                )
                            ;
                            $this->get('mailer')->send($message);
                            $transport = $this->container->get('mailer')->getTransport();
                            $spool = $transport->getSpool();
                            $spool->flushQueue($this->container->get('swiftmailer.transport.real'));
                        }

                        // We get the details of the user
                        // Set the endpoint to get the details of the user
                        $endpoint = $strime_api_url."user/".$user_id."/get";

                        // Send the request
                        $client = new \GuzzleHttp\Client();
                        $json_response = $client->request('GET', $endpoint, [
                            'headers' => $headers,
                            'http_errors' => false
                        ]);
                        $curl_status = $json_response->getStatusCode();
                        $user_details = json_decode( $json_response->getBody() );

                        // If the user has a Stripe profile
                        if($user_details->{'results'}->{'stripe_id'} != NULL) {

                            // Update the descrition of the user in Stripe
                            // Create the customer object
                            $stripe_secret_key = $this->container->getParameter('stripe_secret_key');

                            $payment = new Payment;
                            $payment->stripe_customer_id = $user_details->{'results'}->{'stripe_id'};
                            $payment->stripe_secret_key = $stripe_secret_key;
                            $payment->user_id = $user_id;
                            $payment_result = $payment->updateCustomer(NULL, $email);
                        }

                        // Prepare the flash message
                        $this->addFlash(
                            'success',
                            $this->get('translator')->trans('back.controller_app.profile.changes_saved', array(), 'back_controller_app')
                        );
                    }
                    else {
                        // Reset the avatar to prevent an error of display
                        $avatar = $bag->get('avatar');

                        $this->addFlash(
                            'error',
                            $this->get('translator')->trans('back.controller_app.profile.error_while_updating_profile', array(), 'back_controller_app')
                        );
                    }
                }
            }

            // If the submitted form is not valid
            else {

                $this->addFlash(
                    'error',
                    $this->get('translator')->trans('back.controller_app.profile.all_fields_not_provided', array(), 'back_controller_app')
                );
            }
        }

        // If we get a request to delete the avatar
        if(isset($target) && isset($action) && (strcmp($target, "avatar") == 0) && (strcmp($action, "delete") == 0)) {

            // Get the parameters
            $strime_api_url = $this->container->getParameter('strime_api_url');
            $strime_api_token = $this->container->getParameter('strime_api_token');

            // Set the headers
            $headers = array(
                'Accept' => 'application/json',
                'X-Auth-Token' => $strime_api_token,
                'Content-type' => 'application/json'
            );

            // Set the endpoint
            $endpoint = $strime_api_url."user/".$user_id."/edit";

            // Prepare the parameters
            $params = array(
                'user_id' => $user_id,
                'empty_avatar' => 1
            );

            // Send the request
            $client = new \GuzzleHttp\Client();
            $json_response = $client->request('PUT', $endpoint, [
                'headers' => $headers,
                'http_errors' => false,
                'json' => $params
            ]);
            $curl_status = $json_response->getStatusCode();
            $json_response = $json_response->getBody();

            if($curl_status == 200) {

                // Delete the file
                if( file_exists( realpath($avatar) ) )
                    unlink( realpath($avatar) );

                // Check if the user has a Google image
                if(($user_details->{'user_google_details'} != NULL) && ($user_details->{'user_google_details'}->{'google_image'} != NULL)) {

                    $gravatar = $user_details->{'user_google_details'}->{'google_image'};
                }

                // If the user doesn't have a Google image, set the gravatar
                else {

                    // Change the data in the session
                    $new_avatar = new Avatar;
                    $new_avatar->email = $user_email;
                    $new_avatar->size = 80;
                    $new_avatar->default = "https://www.strime.io/bundles/strimeback/img/player/icon-avatar-40px.png";
                    $gravatar = $new_avatar->setGravatar();
                }

                // Set the URL in the session
                $session->set("avatar", $gravatar);

                // Save the session
                $session->save();

                // Prepare the flash message
                $this->addFlash(
                    'success',
                    $this->get('translator')->trans('back.controller_app.profile.changes_saved', array(), 'back_controller_app')
                );
            }
            else {
                $this->addFlash(
                    'error',
                    $this->get('translator')->trans('back.controller_app.profile.error_while_updating_profile', array(), 'back_controller_app')
                );
            }

            // We redirect the user to the URL without parameters
            return $this->redirectToRoute('app_profile');
        }

        return array(
            'body_classes' => 'profile',
            'profile_section' => 'profile',
            'signup_form' => $signup['signup_form'],
            'signup_message' => $signup['signup_message'],
            'login_form' => $login['login_form'],
            'login_message' => $login['login_message'],
            'forgotten_password_form' => $forgotten_password_form->createView(),
            "user_id" => $user_id,
            "edit_profile_form" => $edit_profile_form->createView(),
            "feedback_form" => $feedback_form->createView()
        );
    }



    /**
     * @Route("/account/delete", name="app_account_delete")
     * @Template()
     */
    public function deleteAccountAction(Request $request)
    {

        // Get the session data
        $session = $request->getSession();
        $bag = $session->getBag("attributes");

        // Get the user ID
        $user_id = $bag->get('user_id');

        // Check if the user is logged in
        if($user_id == NULL)
            return $this->redirectToRoute('home');

        // Create the form object
        $form = $this->container->get('strime.helpers.form');
        $form->request = $request;

        // Get the parameters
        $strime_api_url = $this->container->getParameter('strime_api_url');
        $strime_api_token = $this->container->getParameter('strime_api_token');

        // Set the headers
        $headers = array(
            'Accept' => 'application/json',
            'X-Auth-Token' => $strime_api_token,
            'Content-type' => 'application/json'
        );

        // Delete the user account
        // Set the endpoint to get the details of the user
        $endpoint = $strime_api_url."user/".$user_id."/get";

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $curl_status = $json_response->getStatusCode();
        $user_details = json_decode( $json_response->getBody() );

        if($curl_status == 200) {

            // Set the endpoint to delete the user
            $endpoint = $strime_api_url."user/".$user_id."/delete";

            // Send the request
            $client = new \GuzzleHttp\Client();
            $json_response = $client->request('DELETE', $endpoint, [
                'headers' => $headers,
                'http_errors' => false
            ]);
            $curl_status = $json_response->getStatusCode();
            $json_response = $json_response->getBody();

            // If the request was properly executed
            if($curl_status == 204) {

                // Cancel his Stripe plan if there is one
                if(isset($user_details->{'results'}->{'stripe_sub_id'}) && ($user_details->{'results'}->{'stripe_sub_id'} != NULL)) {
                    $stripe_secret_key = $this->container->getParameter('stripe_secret_key');
                    \Stripe\Stripe::setApiKey( $stripe_secret_key );

                    // Set the headers
                    $stripe_headers = array(
                        'Content-type' => 'application/json'
                    );

                    // Create the Payment object
                    $payment = new Payment;
                    $payment->stripe_secret_key = $stripe_secret_key;
                    $payment->strime_api_url = $strime_api_url;
                    $payment->headers = $stripe_headers;
                    $payment->user_id = $user_id;
                    $payment->stripe_customer_id = $user_details->{'results'}->{'stripe_id'};
                    $payment->stripe_subscription_id = $user_details->{'results'}->{'stripe_sub_id'};
                    $payment->update_offer_id = TRUE;
                    $payment_result = $payment->cancelSubscription();
                }

                // Get the entity manager
                $em = $this->getDoctrine()->getManager();

                // Delete any token to confirm the email address
                $email_confirmation_tokens = $em->getRepository('StrimeGlobalBundle:EmailConfirmationToken')->findAll(array('user_id' => $user_id));

                foreach ($email_confirmation_tokens as $email_confirmation_token) {

                    $em->remove( $email_confirmation_token );
                    $em->flush();
                }

                // Send a notification to Slack
                $slack_webhook = new Webhook( $this->container->getParameter('slack_strime_billing_channel') );
                $slack_webhook->setAttachments(
                    array(
                        array(
                            "fallback" => "Présentation de l'utilisateur",
                            "text" => "ID: ".$user_details->{'results'}->{'user_id'}."\nEmail: ".$user_details->{'results'}->{'email'},
                            "color" => "danger",
                            "author" => array(
                                "author_name" => "Mr Robot"
                            ),
                            "title" => $user_details->{'results'}->{'first_name'}." ".$user_details->{'results'}->{'last_name'},
                            "fields" => array(
                                "title" => $user_details->{'results'}->{'first_name'}." ".$user_details->{'results'}->{'last_name'},
                                "value" => "ID: ".$user_details->{'results'}->{'user_id'}."\nEmail: ".$user_details->{'results'}->{'email'},
                                "short" => FALSE
                            ),
                            "footer_icon" => "https://www.strime.io/bundles/strimeglobal/img/icon-strime.jpg",
                            "ts" => time()
                        )
                    )
                );
                $slack_webhook->sendMessage(array(
                    "message" => "Suppression d'un utilisateur",
                    "username" => "[".ucfirst( $this->container->getParameter("kernel.environment") )."] Strime.io",
                    "icon" => ":shit:"
                ));

                // Redirect to the homepage
                return $this->redirect( $this->generateUrl('signout') );
            }
            elseif($curl_status == 400) {
                $this->addFlash(
                    'error',
                    $this->get('translator')->trans('back.controller_app.delete_profile.unrecognized_account_cannot_be_deleted', array(), 'back_controller_app')
                );
            }
            else {
                $this->addFlash(
                    'error',
                    $this->get('translator')->trans('back.controller_app.delete_profile.error_occured_while_deleting_account', array(), 'back_controller_app')
                );
            }
        }
        else {
            $this->addFlash(
                'error',
                $this->get('translator')->trans('back.controller_app.delete_profile.unrecognized_account_cannot_be_deleted', array(), 'back_controller_app')
            );
        }

        // Redirect to the homepage
        return $this->redirect( $this->generateUrl('app_profile') );
    }
}
