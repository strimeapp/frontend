<?php

namespace Strime\FrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

use Strime\GlobalBundle\Token\TokenGenerator;
use Strime\GlobalBundle\Entity\ResetPwdToken;
use Strime\Slackify\Webhooks\Webhook;
use Google_Client;


class AjaxController extends Controller
{
    /**
     * @Route("/ajax/signup", name="ajax_signup")
     * @Template("StrimeFrontBundle:Misc:empty-view.html.twig")
     */
    public function ajaxSignupAction(Request $request)
    {

        // Create the signup form
        $form = $this->container->get('strime.helpers.form');
        $form->request = $request;
        $signup_form = $form->createSignupForm($request);
        $signup_form->handleRequest($request);

        // Create the users helper
        $users_helper = $this->container->get('strime.back_helpers.users');
        $users_helper->request = $request;

        if($signup_form->isSubmitted()) {

            // If the submitted form is valid
            if($signup_form->isValid()) {

                // Set the API parameters
                $strime_api_url = $this->container->getParameter('strime_api_url');
                $strime_api_token = $this->container->getParameter('strime_api_token');

                // Get the data
                $first_name = $signup_form->get('first_name')->getData();
                $last_name = $signup_form->get('last_name')->getData();
                $email = $signup_form->get('email')->getData();
                $password = $signup_form->get('password')->getData();
                $offer_id = $signup_form->get('offer_id')->getData();
                $opt_in = $signup_form->get('opt_in')->getData();

                // Check if the email is a valid email address
                if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
                    echo json_encode(
                        array(
                            "status" => "error",
                            "error_source" => "invalid_email"
                        )
                    );
                    die;
                }

                // Set a proper value for opt_in if the checkbox was unchecked
                if(count($opt_in) == 0)
                    $opt_in = 0;
                else
                    $opt_in = 1;

                // Set the parameters for adding the user to the API
                $params = array(
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $email,
                    'password' => $password,
                    'opt_in' => $opt_in,
                    'offer_id' => $offer_id,
                    'locale' => $request->getLocale()
                );

                // Set the endpoint
                $endpoint = $strime_api_url."user/add";

                // Set the headers
                $headers = array(
                    'Accept' => 'application/json',
                    'X-Auth-Token' => $strime_api_token,
                    'Content-type' => 'application/json'
                );

                $client = new \GuzzleHttp\Client();
                $json_response = $client->request('POST', $endpoint, [
                    'headers' => $headers,
                    'http_errors' => false,
                    'json' => $params
                ]);

                $curl_status = $json_response->getStatusCode();
                $json_response = $json_response->getBody();

                // If the user has been created
                // - create a session
                // - redirect the user to the app dashboard
                if($curl_status == 201) {

                    // Decode the signup response
                    $json_response = json_decode($json_response);

                    // Get the user details
                    $user_id = $json_response->{'user_id'};

                    // Set the endpoint
                    $endpoint = $strime_api_url."user/".$user_id."/get";

                    $client = new \GuzzleHttp\Client();
                    $user_json_response = $client->request('GET', $endpoint, [
                        'headers' => $headers,
                        'http_errors' => false,
                    ]);

                    $user_curl_status = $user_json_response->getStatusCode();
                    $user_json_response = $user_json_response->getBody();

                    // Create a session
                    $session = new Session();
                    $session->set('user_id', $user_id);

                    // Add user data to the session
                    if($user_curl_status == 200) {

                        // Decode the user response
                        $user_json_response = json_decode($user_json_response);
                        $session->set('first_name', $user_json_response->{'results'}->{'first_name'});
                        $session->set('last_name', $user_json_response->{'results'}->{'last_name'});
                        $session->set('offer', $user_json_response->{'results'}->{'offer'}->{'offer_id'});
                        $session->set('offer_price', $user_json_response->{'results'}->{'offer'}->{'price'});
                        $session->set('email', $user_json_response->{'results'}->{'email'});
                        $session->set('status', $user_json_response->{'results'}->{'status'});
                        $session->set('role', $user_json_response->{'results'}->{'role'});
                        $session->set('country', $user_json_response->{'results'}->{'country'});
                        $session->set('avatar', $user_json_response->{'results'}->{'avatar'});
                        $session->set('rights', $user_json_response->{'results'}->{'rights'});

                        // Calculate the storage used
                        $storage_used = (int)round($user_json_response->{'results'}->{'storage_used'} / $this->container->getParameter('strime_api_storage_multiplier')); // To get storage in Mo.
                        $session->set('storage_used', $storage_used);

                        $storage_allowed = $user_json_response->{'results'}->{'offer'}->{'storage_allowed'} * $this->container->getParameter('strime_api_storage_allowed_multiplier'); // To get storage in Mo.
                        $session->set('storage_allowed', $storage_allowed);

                        $storage_used_in_percent = (int)round(($storage_used / $storage_allowed) * 100);
                        $session->set('storage_used_in_percent', $storage_used_in_percent);
                    }

                    // Set the gravatar image
                    if($user_json_response->{'results'}->{'avatar'} == NULL)
                        $session = $users_helper->setAvatar($email);
                    else
                        $session->set('avatar', $user_json_response->{'results'}->{'avatar'});

                    // Save the session
                    $session->save();

                    // Save the email confirmation token
                    $signup_helper = $this->container->get('strime.helpers.signup');
                    $email_confirmation_token = $signup_helper->saveEmailConfirmationToken( $user_id );

                    // Send a confirmation email
                    $message = \Swift_Message::newInstance()
                        ->setSubject( 'Strime - ' . $this->get('translator')->trans('front.controller_pages.ajax_signup.confirm_signup', array(), 'front_controller_pages') )
                        ->setFrom(array('contact@strime.io' => 'Strime'))
                        ->setTo( array( $email => $first_name." ".$last_name ) )
                        ->setBody(
                            $this->renderView(
                                // app/Resources/views/debug/debug.html.twig
                                'emails/signup_confirmation.html.twig',
                                array(
                                    'current_year' => date('Y'),
                                    'first_name' => $first_name
                                )
                            ),
                            'text/html'
                        )
                        /*
                         * If you also want to include a plaintext version of the message
                        ->addPart(
                            $this->renderView(
                                'Emails/registration.txt.twig',
                                array('name' => $name)
                            ),
                            'text/plain'
                        )
                        */
                    ;
                    $this->get('mailer')->send($message);
                    $transport = $this->container->get('mailer')->getTransport();
                    $spool = $transport->getSpool();
                    $spool->flushQueue($this->container->get('swiftmailer.transport.real'));

                    // Send an email with the link to confirm the email address
                    $message = \Swift_Message::newInstance()
                        ->setSubject( 'Strime - ' . $this->get('translator')->trans('front.controller_pages.ajax_signup.confirm_email', array(), 'front_controller_pages') )
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
                                    'locale' => $request->getLocale()
                                )
                            ),
                            'text/html'
                        )
                    ;
                    $this->get('mailer')->send($message);
                    $transport = $this->container->get('mailer')->getTransport();
                    $spool = $transport->getSpool();
                    $spool->flushQueue($this->container->get('swiftmailer.transport.real'));

                    // Send a notification to Slack
                    $slack_webhook = new Webhook( $this->container->getParameter('slack_strime_billing_channel') );
                    $slack_webhook->setAttachments(
                        array(
                            array(
                                "fallback" => "Présentation de l'utilisateur",
                                "text" => "ID: ".$user_id."\nEmail: ".$email,
                                "color" => "good",
                                "author" => array(
                                    "author_name" => "Mr Robot"
                                ),
                                "title" => $first_name." ".$last_name,
                                "fields" => array(
                                    "title" => $first_name." ".$last_name,
                                    "value" => "ID: ".$user_id."\nEmail: ".$email,
                                    "short" => FALSE
                                ),
                                "footer_icon" => "https://www.strime.io/bundles/strimeglobal/img/icon-strime.jpg",
                                "ts" => time()
                            )
                        )
                    );
                    $slack_webhook->sendMessage(array(
                        "message" => "Inscription d'un nouvel utilisateur sur l'offre gratuite",
                        "username" => "[".ucfirst( $this->container->getParameter("kernel.environment") )."] Strime.io",
                        "icon" => ":thumbsup:"
                    ));

                    // Set the response
                    $json_response->{'redirect'} = $this->generateUrl('app_dashboard');
                    $json_response = json_encode($json_response);
                    echo $json_response;
                    die;
                }

                // If the user has not been created, return the JSON to the browser
                else {
                    echo $json_response;
                    die;
                }
            }

            // If the submitted form is not valid
            else {
                echo json_encode(array("status" => "error"));
            }
        }
    }

    /**
     * @Route("/ajax/signup/simple", name="ajax_simple_signup")
     * @Template("StrimeFrontBundle:Misc:empty-view.html.twig")
     */
    public function ajaxSimpleSignupAction(Request $request)
    {

        // Create the signup form
        $form = $this->container->get('strime.helpers.form');
        $form->request = $request;
        $signup_form = $form->createSimpleSignupForm($request);
        $signup_form->handleRequest($request);

        // Create the users helper
        $users_helper = $this->container->get('strime.back_helpers.users');
        $users_helper->request = $request;

        if($signup_form->isSubmitted()) {

            // If the submitted form is valid
            if($signup_form->isValid()) {

                // Set the API parameters
                $strime_api_url = $this->container->getParameter('strime_api_url');
                $strime_api_token = $this->container->getParameter('strime_api_token');

                // Get the data
                $name = $signup_form->get('name')->getData();
                $email = $signup_form->get('email')->getData();
                $password = $signup_form->get('password')->getData();
                $offer_id = $signup_form->get('offer_id')->getData();
                $opt_in = 0;

                // Check if the email is a valid email address
                if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
                    echo json_encode(
                        array(
                            "status" => "error",
                            "error_source" => "invalid_email"
                        )
                    );
                    die;
                }

                // If we get an empty firstname, we check if the full name has been provided in the lastname field
                // and we try to guess what the firstname is
                $name_parts = explode(" ", $name);
                if(count($name_parts) >= 2) {
                    $first_name = $name_parts[0];
                    unset( $name_parts[0] );
                    $last_name = implode(" ", $name_parts);
                }
                else {
                    $first_name = $name;
                    $last_name = " ";
                }

                // Set the parameters for adding the user to the API
                $params = array(
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $email,
                    'password' => $password,
                    'opt_in' => $opt_in,
                    'offer_id' => $offer_id,
                    'locale' => $request->getLocale()
                );

                // Set the endpoint
                $endpoint = $strime_api_url."user/add";

                // Set the headers
                $headers = array(
                    'Accept' => 'application/json',
                    'X-Auth-Token' => $strime_api_token,
                    'Content-type' => 'application/json'
                );

                // Send the request
                $client = new \GuzzleHttp\Client();
                $json_response = $client->request('POST', $endpoint, [
                    'headers' => $headers,
                    'http_errors' => false,
                    'json' => $params
                ]);

                $curl_status = $json_response->getStatusCode();
                $json_response = $json_response->getBody();

                // If the user has been created
                // - create a session
                // - redirect the user to the app dashboard
                if($curl_status == 201) {

                    // Decode the signup response
                    $json_response = json_decode($json_response);

                    // Get the user details
                    $user_id = $json_response->{'user_id'};

                    // Set the endpoint
                    $endpoint = $strime_api_url."user/".$user_id."/get";

                    // Send the request
                    $client = new \GuzzleHttp\Client();
                    $user_json_response = $client->request('GET', $endpoint, [
                        'headers' => $headers,
                        'http_errors' => false,
                    ]);

                    $user_curl_status = $user_json_response->getStatusCode();
                    $user_json_response = $user_json_response->getBody();

                    // Create a session
                    $session = new Session();
                    $session->set('user_id', $user_id);

                    // Add user data to the session
                    if($user_curl_status == 200) {

                        // Decode the user response
                        $user_json_response = json_decode($user_json_response);
                        $session->set('first_name', $user_json_response->{'results'}->{'first_name'});
                        $session->set('last_name', $user_json_response->{'results'}->{'last_name'});
                        $session->set('offer', $user_json_response->{'results'}->{'offer'}->{'offer_id'});
                        $session->set('offer_price', $user_json_response->{'results'}->{'offer'}->{'price'});
                        $session->set('email', $user_json_response->{'results'}->{'email'});
                        $session->set('status', $user_json_response->{'results'}->{'status'});
                        $session->set('role', $user_json_response->{'results'}->{'role'});
                        $session->set('country', $user_json_response->{'results'}->{'country'});
                        $session->set('avatar', $user_json_response->{'results'}->{'avatar'});
                        $session->set('rights', $user_json_response->{'results'}->{'rights'});

                        // Calculate the storage used
                        $storage_used = (int)round($user_json_response->{'results'}->{'storage_used'} / $this->container->getParameter('strime_api_storage_multiplier')); // To get storage in Mo.
                        $session->set('storage_used', $storage_used);

                        $storage_allowed = $user_json_response->{'results'}->{'offer'}->{'storage_allowed'} * $this->container->getParameter('strime_api_storage_allowed_multiplier'); // To get storage in Mo.
                        $session->set('storage_allowed', $storage_allowed);

                        $storage_used_in_percent = (int)round(($storage_used / $storage_allowed) * 100);
                        $session->set('storage_used_in_percent', $storage_used_in_percent);
                    }

                    // Set the gravatar image
                    if($user_json_response->{'results'}->{'avatar'} == NULL)
                        $session = $users_helper->setAvatar($email);
                    else
                        $session->set('avatar', $user_json_response->{'results'}->{'avatar'});

                    // Save the session
                    $session->save();

                    // Save the email confirmation token
                    $signup_helper = $this->container->get('strime.helpers.signup');
                    $email_confirmation_token = $signup_helper->saveEmailConfirmationToken( $user_id );

                    // Send a confirmation email
                    $message = \Swift_Message::newInstance()
                        ->setSubject( 'Strime - ' . $this->get('translator')->trans('front.controller_pages.ajax_signup.confirm_signup', array(), 'front_controller_pages') )
                        ->setFrom(array('contact@strime.io' => 'Strime'))
                        ->setTo( array( $email => $first_name." ".$last_name ) )
                        ->setBody(
                            $this->renderView(
                                // app/Resources/views/debug/debug.html.twig
                                'emails/signup_confirmation.html.twig',
                                array(
                                    'current_year' => date('Y'),
                                    'first_name' => $first_name
                                )
                            ),
                            'text/html'
                        )
                        /*
                         * If you also want to include a plaintext version of the message
                        ->addPart(
                            $this->renderView(
                                'Emails/registration.txt.twig',
                                array('name' => $name)
                            ),
                            'text/plain'
                        )
                        */
                    ;
                    $this->get('mailer')->send($message);
                    $transport = $this->container->get('mailer')->getTransport();
                    $spool = $transport->getSpool();
                    $spool->flushQueue($this->container->get('swiftmailer.transport.real'));

                    // Send an email with the link to confirm the email address
                    $message = \Swift_Message::newInstance()
                        ->setSubject( 'Strime - ' . $this->get('translator')->trans('front.controller_pages.ajax_signup.confirm_email', array(), 'front_controller_pages') )
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
                                    'locale' => $request->getLocale()
                                )
                            ),
                            'text/html'
                        )
                    ;
                    $this->get('mailer')->send($message);
                    $transport = $this->container->get('mailer')->getTransport();
                    $spool = $transport->getSpool();
                    $spool->flushQueue($this->container->get('swiftmailer.transport.real'));

                    // Send a notification to Slack
                    $slack_webhook = new Webhook( $this->container->getParameter('slack_strime_billing_channel') );
                    $slack_webhook->setAttachments(
                        array(
                            array(
                                "fallback" => "Présentation de l'utilisateur",
                                "text" => "ID: ".$user_id."\nEmail: ".$email,
                                "color" => "success",
                                "author" => array(
                                    "author_name" => "Mr Robot"
                                ),
                                "title" => $first_name." ".$last_name,
                                "fields" => array(
                                    "title" => $first_name." ".$last_name,
                                    "value" => "ID: ".$user_id."\nEmail: ".$email,
                                    "short" => FALSE
                                ),
                                "footer_icon" => "https://www.strime.io/bundles/strimeglobal/img/icon-strime.jpg",
                                "ts" => time()
                            )
                        )
                    );
                    $slack_webhook->sendMessage(array(
                        "message" => "Inscription d'un nouvel utilisateur sur l'offre gratuite",
                        "username" => "[".ucfirst( $this->container->getParameter("kernel.environment") )."] Strime.io",
                        "icon" => ":thumbsup:"
                    ));

                    $json_response->{'redirect'} = $this->generateUrl('app_dashboard');
                    $json_response = json_encode($json_response);
                    echo $json_response;
                    die;
                }

                // If the user has not been created, return the JSON to the browser
                else {
                    echo $json_response;
                    die;
                }
            }

            // If the submitted form is not valid
            else {
                echo json_encode(array("status" => "error"));
            }
        }
    }

    /**
     * @Route("/ajax/login", name="ajax_login")
     * @Template("StrimeFrontBundle:Misc:empty-view.html.twig")
     */
    public function ajaxLoginAction(Request $request)
    {

        // Create the login form
        $form = $this->container->get('strime.helpers.form');
        $form->request = $request;
        $login_form = $form->createLoginForm();
        $login_form->handleRequest($request);

        // Create the users helper
        $users_helper = $this->container->get('strime.back_helpers.users');
        $users_helper->request = $request;

        if($login_form->isSubmitted()) {

            // If the submitted form is valid
            if($login_form->isValid()) {
                $email = $login_form->get('email')->getData();
                $password = $login_form->get('password')->getData();

                $strime_api_url = $this->container->getParameter('strime_api_url');
                $strime_api_token = $this->container->getParameter('strime_api_token');

                // Set the endpoint
                $endpoint = $strime_api_url."user/signin";

                // Set the parameters for the request
                $params = array(
                    'email' => $email,
                    'password' => $password
                );

                // Set the headers
                $headers = array(
                    'Accept' => 'application/json',
                    'X-Auth-Token' => $strime_api_token,
                    'Content-type' => 'application/json'
                );

                // Send the request
                $client = new \GuzzleHttp\Client();
                $json_response = $client->request('POST', $endpoint, [
                    'headers' => $headers,
                    'http_errors' => false,
                    'json' => $params
                ]);

                $curl_status = $json_response->getStatusCode();
                $json_response = $json_response->getBody();

                // If the user has been created
                // - create a session
                // - redirect the user to the app dashboard
                if($curl_status == 200) {

                    // Decode the signup response
                    $json_response = json_decode($json_response);

                    // Create a session
                    $session = new Session();
                    $session->set('user_id', $json_response->{'user'}->{'user_id'});
                    $session->set('first_name', $json_response->{'user'}->{'first_name'});
                    $session->set('last_name', $json_response->{'user'}->{'last_name'});
                    $session->set('offer', $json_response->{'user'}->{'offer'}->{'offer_id'});
                    $session->set('offer_price', $json_response->{'user'}->{'offer'}->{'price'});
                    $session->set('email', $json_response->{'user'}->{'email'});
                    $session->set('status', $json_response->{'user'}->{'status'});
                    $session->set('role', $json_response->{'user'}->{'role'});
                    $session->set('country', $json_response->{'user'}->{'country'});
                    $session->set('rights', $json_response->{'user'}->{'rights'});

                    // Calculate the storage used
                    $storage_used = (int)round($json_response->{'user'}->{'storage_used'} / $this->container->getParameter('strime_api_storage_multiplier')); // To get storage in Mo.
                    $session->set('storage_used', $storage_used);

                    $storage_allowed = $json_response->{'user'}->{'offer'}->{'storage_allowed'} * $this->container->getParameter('strime_api_storage_allowed_multiplier'); // To get storage in Mo.
                    $session->set('storage_allowed', $storage_allowed);

                    $storage_used_in_percent = (int)round(($storage_used / $storage_allowed) * 100);
                    $session->set('storage_used_in_percent', $storage_used_in_percent);

                    if($json_response->{'user'}->{'locale'} != NULL) {
                        $session->set('_locale', $json_response->{'user'}->{'locale'});
                        $request->setLocale($json_response->{'user'}->{'locale'});
                    }

                    // If the user has connection with Youtube
                    if(($json_response->{'user'}->{'user_youtube_details'} != NULL) && ($json_response->{'user'}->{'user_youtube_details'}->{'youtube_id'} != NULL)) {
                        $session->set('youtube_token', json_decode( $json_response->{'user'}->{'user_youtube_details'}->{'youtube_id'} ) );
                    }

                    // Set the gravatar image
                    if($json_response->{'user'}->{'avatar'} == NULL)
                        $session = $users_helper->setAvatar($email);
                    else
                        $session->set('avatar', $json_response->{'user'}->{'avatar'});

                    // Save the session
                    $session->save();

                    $json_response->{'redirect'} = $this->generateUrl('app_dashboard');
                    $json_response->{'session'} = $session;
                    $json_response->{'locale'} = $session->get('_locale');
                    $json_response = json_encode($json_response);
                    echo $json_response;
                    die;
                }

                // If the user has not been logged in, return the JSON to the browser
                else {
                    echo $json_response;
                    die;
                }
            }

            // If the submitted form is not valid
            else {
                echo json_encode(
                    array(
                        "status" => "error",
                        "error" => "form_invalid"
                    )
                );
            }
        }

        die;
    }



    /**
     * @Route("/ajax/social/sign-in", name="ajax_social_signin")
     * @Template("StrimeFrontBundle:Misc:empty-view.html.twig")
     */
    public function ajaxSocialSigninAction(Request $request)
    {
        // Get the data
        $first_name = $request->request->get('first_name', NULL);
        $last_name = $request->request->get('last_name', NULL);
        $email = $request->request->get('email', NULL);
        $image = $request->request->get('image', NULL);
        $id_token = $request->request->get('id_token', NULL);
        $social_tool = $request->request->get('social_tool', NULL);
        $opt_in = 1;

        // Create the users helper
        $users_helper = $this->container->get('strime.back_helpers.users');
        $users_helper->request = $request;

        // Set the API parameters
        $strime_api_url = $this->container->getParameter('strime_api_url');
        $strime_api_token = $this->container->getParameter('strime_api_token');

        // Set the headers
        $headers = array(
            'Accept' => 'application/json',
            'X-Auth-Token' => $strime_api_token,
            'Content-type' => 'application/json'
        );

        // If the user signed in with Google
        if(strcmp($social_tool, "google") == 0) {

            // Verify the token
            $google_client = new Google_Client(['client_id' => $this->container->getParameter('strime_google_api_console_id')]);
            $payload = $google_client->verifyIdToken($id_token);
            if ($payload) {
                if(strcmp($payload['aud'], $this->container->getParameter('strime_google_api_console_id')) == 0) {
                    $user_google_id = $payload['sub'];
                }
                else {
                    echo json_encode(array("status" => "error", "error_type" => "invalid_google_api_console_id"));
                    die;
                }
            } else {
                echo json_encode(array("status" => "error", "error_type" => "invalid_google_token"));
                die;
            }
        }

        // Check if the user exists
        // Set the endpoint
        $endpoint = $strime_api_url."user/".$email."/get-by-email";

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);

        $curl_status = $json_response->getStatusCode();
        $json_response = $json_response->getBody();

        // If the user exists
        // - create a session
        // - redirect the user to the app dashboard
        if($curl_status == 200) {

            // Decode the signup response
            $json_response = json_decode($json_response);

            // Create a session
            $session = new Session();
            $session->set('user_id', $json_response->{'results'}->{'user_id'});
            $session->set('first_name', $json_response->{'results'}->{'first_name'});
            $session->set('last_name', $json_response->{'results'}->{'last_name'});
            $session->set('offer', $json_response->{'results'}->{'offer'}->{'offer_id'});
            $session->set('offer_price', $json_response->{'results'}->{'offer'}->{'price'});
            $session->set('email', $json_response->{'results'}->{'email'});
            $session->set('status', $json_response->{'results'}->{'status'});
            $session->set('role', $json_response->{'results'}->{'role'});
            $session->set('country', $json_response->{'results'}->{'country'});
            $session->set('rights', $json_response->{'results'}->{'rights'});

            // If the user signed in with Google
            if(strcmp($social_tool, "google") == 0) {
                $session->set('google_signin', TRUE);
            }
            // If the user signed in with Facebook
            elseif(strcmp($social_tool, "facebook") == 0) {
                $session->set('facebook_signin', TRUE);
            }

            // Calculate the storage used
            $storage_used = (int)round($json_response->{'results'}->{'storage_used'} / $this->container->getParameter('strime_api_storage_multiplier')); // To get storage in Mo.
            $session->set('storage_used', $storage_used);

            $storage_allowed = $json_response->{'results'}->{'offer'}->{'storage_allowed'} * $this->container->getParameter('strime_api_storage_allowed_multiplier'); // To get storage in Mo.
            $session->set('storage_allowed', $storage_allowed);

            $storage_used_in_percent = (int)round(($storage_used / $storage_allowed) * 100);
            $session->set('storage_used_in_percent', $storage_used_in_percent);

            if($json_response->{'results'}->{'locale'} != NULL) {
                $session->set('_locale', $json_response->{'results'}->{'locale'});
                $request->setLocale($json_response->{'results'}->{'locale'});
            }

            // If the user has connection with Youtube
            if(($json_response->{'results'}->{'user_youtube_details'} != NULL) && ($json_response->{'results'}->{'user_youtube_details'}->{'youtube_id'} != NULL)) {
                $session->set('youtube_token', json_decode( $json_response->{'results'}->{'user_youtube_details'}->{'youtube_id'} ) );
            }

            // Set the gravatar image
            if($json_response->{'results'}->{'avatar'} == NULL) {
                $session = $users_helper->setAvatar($email);
            }
            else {
                $session->set('avatar', $json_response->{'results'}->{'avatar'});
            }

            // Save the session
            $session->save();

            // If the user signed in with Google
            if(strcmp($social_tool, "google") == 0) {

                // Update the Google information of the user
                $endpoint = $strime_api_url."user/".$json_response->{'results'}->{'user_id'}."/edit";
                $params = array(
                    'user_google_id' => $user_google_id,
                    'user_google_image' => $image
                );
            }

            // If the user signed in with Facebook
            if(strcmp($social_tool, "facebook") == 0) {

                // Update the Facebook information of the user
                $endpoint = $strime_api_url."user/".$json_response->{'results'}->{'user_id'}."/edit";
                $params = array(
                    'user_facebook_id' => $id_token,
                    'user_facebook_image' => $image
                );
            }

            // Check if he is deactivated
            // If yes, we reactivate him
            if(strcmp($json_response->{'results'}->{'status'}, "deactivated") == 0) {
                $params["status"] = "active";
            }

            // Send the request
            $client = new \GuzzleHttp\Client();
            $edit_json_response = $client->request('PUT', $endpoint, [
                'headers' => $headers,
                'http_errors' => false,
                'json' => $params
            ]);

            $edit_curl_status = $edit_json_response->getStatusCode();
            $edit_json_response = json_decode( $edit_json_response->getBody() );

            // If the edit user failed, add an element in the JSON
            if($edit_curl_status != 200) {

                $json_response->{'edit_user_error'} = $edit_json_response;
            }

            // Check if he needs to confirm his email address
            // If yes, consider his email address is confirmed
            if($json_response->{'results'}->{'needs_to_confirm_email'} == TRUE) {

                // Set the entity manager
                $em = $this->getDoctrine()->getManager();

                // Get the token of this user
                $user_needs_to_confirm_email = $em->getRepository('StrimeGlobalBundle:EmailConfirmationToken')->findBy(array('user_id' => $json_response->{'results'}->{'user_id'}));

                if($user_needs_to_confirm_email != NULL) {

                    // Remove the entry from the DB
                    foreach ($user_needs_to_confirm_email as $email_to_confirm) {
                        $em->remove( $email_to_confirm );
                    }
                    $em->flush();

                    // Set the endpoint
                    $endpoint = $strime_api_url.'user/'.$json_response->{'results'}->{'user_id'}.'/confirm-email';

                    $client = new \GuzzleHttp\Client();
                    $json_response = $client->request('POST', $endpoint, [
                        'headers' => $headers,
                        'http_errors' => false
                    ]);

                    $confirm_email_curl_status = $json_response->getStatusCode();
                    $confirm_email_response = json_decode($json_response->getBody());

                    // Return the result
                    if($confirm_email_curl_status != 200) {

                        $json_response->{'confirm_email_error'} = $confirm_email_response;
                    }
                }
            }

            $json_response->{'redirect'} = $this->generateUrl('app_dashboard');
            $json_response->{'response_code'} = 200;
            $json_response->{'session'} = $session;
            $json_response->{'locale'} = $session->get('_locale');
            $json_response = json_encode($json_response);
            echo $json_response;
            die;
        }

        // If the user doesn't exist,
        // We create a new account
        else {

            // Generate a password
            $token_generator = new TokenGenerator();
            $password = $token_generator->generateToken(10);

            // Get the offers
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
                    if(($offer->{'price'} == 0) && (strcmp(strtolower($offer->{'name'}), "gratuite") == 0)) {
                        $offer_id = $offer->{'offer_id'};
                    }
                }
            }
            else {
                echo json_encode(array("status" => "error", "error_source" => "couldnt_get_offers", "curl_status" => $curl_status));
                die;
            }

            // Check that we got the offer_id
            if($offer_id == NULL) {
                echo json_encode(array("status" => "error", "error_source" => "no_free_offer"));
                die;
            }

            // Set the parameters for adding the user to the API
            $params = array(
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'password' => $password,
                'opt_in' => $opt_in,
                'offer_id' => $offer_id,
                'locale' => $request->getLocale()
            );

            if(strcmp($social_tool, "google") == 0) {
                $params['user_google_id'] = $user_google_id;
                $params['user_google_image'] = $image;
            }
            elseif(strcmp($social_tool, "facebook") == 0) {
                $params['user_facebook_id'] = $id_token;
                $params['user_facebook_image'] = $image;
            }

            // Set the endpoint
            $endpoint = $strime_api_url."user/add";

            $client = new \GuzzleHttp\Client();
            $json_response = $client->request('POST', $endpoint, [
                'headers' => $headers,
                'http_errors' => false,
                'json' => $params
            ]);

            $curl_status = $json_response->getStatusCode();
            $json_response = $json_response->getBody();

            // If the user has been created
            // - create a session
            // - redirect the user to the app dashboard
            if($curl_status == 201) {

                // Decode the signup response
                $json_response = json_decode($json_response);

                // Get the user details
                $user_id = $json_response->{'user_id'};

                // Set the endpoint
                $endpoint = $strime_api_url."user/".$user_id."/get";

                $client = new \GuzzleHttp\Client();
                $user_json_response = $client->request('GET', $endpoint, [
                    'headers' => $headers,
                    'http_errors' => false,
                ]);

                $user_curl_status = $user_json_response->getStatusCode();
                $user_json_response = $user_json_response->getBody();

                // Create a session
                $session = new Session();
                $session->set('user_id', $user_id);

                // Add user data to the session
                if($user_curl_status == 200) {

                    // Decode the user response
                    $user_json_response = json_decode($user_json_response);
                    $session->set('first_name', $user_json_response->{'results'}->{'first_name'});
                    $session->set('last_name', $user_json_response->{'results'}->{'last_name'});
                    $session->set('offer', $user_json_response->{'results'}->{'offer'}->{'offer_id'});
                    $session->set('offer_price', $user_json_response->{'results'}->{'offer'}->{'price'});
                    $session->set('email', $user_json_response->{'results'}->{'email'});
                    $session->set('status', $user_json_response->{'results'}->{'status'});
                    $session->set('role', $user_json_response->{'results'}->{'role'});
                    $session->set('country', $user_json_response->{'results'}->{'country'});
                    $session->set('avatar', $user_json_response->{'results'}->{'avatar'});
                    $session->set('rights', $user_json_response->{'results'}->{'rights'});

                    if(strcmp($social_tool, "google") == 0) {
                        $session->set('google_signin', TRUE);
                    }
                    elseif(strcmp($social_tool, "facebook") == 0) {
                        $session->set('facebook_signin', TRUE);
                    }

                    // Calculate the storage used
                    $storage_used = (int)round($user_json_response->{'results'}->{'storage_used'} / $this->container->getParameter('strime_api_storage_multiplier')); // To get storage in Mo.
                    $session->set('storage_used', $storage_used);

                    $storage_allowed = $user_json_response->{'results'}->{'offer'}->{'storage_allowed'} * $this->container->getParameter('strime_api_storage_allowed_multiplier'); // To get storage in Mo.
                    $session->set('storage_allowed', $storage_allowed);

                    $storage_used_in_percent = (int)round(($storage_used / $storage_allowed) * 100);
                    $session->set('storage_used_in_percent', $storage_used_in_percent);

                    if($user_json_response->{'results'}->{'locale'} != NULL) {
                        $session->set('_locale', $user_json_response->{'results'}->{'locale'});
                        $request->setLocale($user_json_response->{'results'}->{'locale'});
                    }

                    // If the user has connection with Youtube
                    if(($user_json_response->{'results'}->{'user_youtube_details'} != NULL) && ($user_json_response->{'results'}->{'user_youtube_details'}->{'youtube_id'} != NULL)) {
                        $session->set('youtube_token', json_decode( $user_json_response->{'results'}->{'user_youtube_details'}->{'youtube_id'} ) );
                    }
                }

                // Set the gravatar image
                if($user_json_response->{'results'}->{'avatar'} == NULL)
                    $session = $users_helper->setAvatar($email);
                else
                    $session->set('avatar', $user_json_response->{'results'}->{'avatar'});

                // Save the session
                $session->save();

                // Send a confirmation email
                $message = \Swift_Message::newInstance()
                    ->setSubject( 'Strime - ' . $this->get('translator')->trans('front.controller_pages.ajax_signup.confirm_signup', array(), 'front_controller_pages') )
                    ->setFrom(array('contact@strime.io' => 'Strime'))
                    ->setTo( array( $email => $first_name." ".$last_name ) )
                    ->setBody(
                        $this->renderView(
                            // app/Resources/views/debug/debug.html.twig
                            'emails/signup_confirmation.html.twig',
                            array(
                                'current_year' => date('Y'),
                                'first_name' => $first_name
                            )
                        ),
                        'text/html'
                    )
                ;
                $this->get('mailer')->send($message);
                $transport = $this->container->get('mailer')->getTransport();
                $spool = $transport->getSpool();
                $spool->flushQueue($this->container->get('swiftmailer.transport.real'));

                // Send a notification to Slack
                $slack_webhook = new Webhook( $this->container->getParameter('slack_strime_billing_channel') );
                $slack_webhook->setAttachments(
                    array(
                        array(
                            "fallback" => "Présentation de l'utilisateur",
                            "text" => "ID: ".$user_id."\nEmail: ".$email."\nUtilisateur inscrit via ".ucfirst($social_tool),
                            "color" => "good",
                            "author" => array(
                                "author_name" => "Mr Robot"
                            ),
                            "title" => $first_name." ".$last_name,
                            "fields" => array(
                                "title" => $first_name." ".$last_name,
                                "value" => "ID: ".$user_id."\nEmail: ".$email,
                                "short" => FALSE
                            ),
                            "footer_icon" => "https://www.strime.io/bundles/strimeglobal/img/icon-strime.jpg",
                            "ts" => time()
                        )
                    )
                );
                $slack_webhook->sendMessage(array(
                    "message" => "Inscription d'un nouvel utilisateur sur l'offre gratuite",
                    "username" => "[".ucfirst( $this->container->getParameter("kernel.environment") )."] Strime.io",
                    "icon" => ":thumbsup:"
                ));

                // Set the response
                $json_response->{'redirect'} = $this->generateUrl('app_dashboard');
                $json_response->{'response_code'} = 200;
                $json_response->{'session'} = $session;
                $json_response->{'locale'} = $session->get('_locale');
                $json_response = json_encode($json_response);
                echo $json_response;
                die;
            }

            // If the user has not been created, return the JSON to the browser
            else {
                echo $json_response;
                die;
            }
        }
    }


    /**
     * @Route("/ajax/forgotten-password", name="ajax_forgotten_password")
     * @Template("StrimeFrontBundle:Misc:empty-view.html.twig")
     */
    public function ajaxForgottenPasswordAction(Request $request)
    {

        // Create the forgotten password form
        $form = $this->container->get('strime.helpers.form');
        $form->request = $request;
        $forgotten_password_form = $form->createForgottenPasswordForm();
        $forgotten_password_form->handleRequest($request);

        if($forgotten_password_form->isSubmitted()) {

            // If the submitted form is valid
            if($forgotten_password_form->isValid()) {
                $email = $forgotten_password_form->get('email')->getData();

                $strime_api_url = $this->container->getParameter('strime_api_url');
                $strime_api_token = $this->container->getParameter('strime_api_token');

                // Set the endpoint
                $endpoint = $strime_api_url."user/".$email."/get-by-email";

                // Set the headers
                $headers = array(
                    'Accept' => 'application/json',
                    'X-Auth-Token' => $strime_api_token,
                    'Content-type' => 'application/json'
                );

                // Send the request
                $client = new \GuzzleHttp\Client();
                $json_response = $client->request('GET', $endpoint, [
                    'headers' => $headers,
                    'http_errors' => false,
                ]);

                $curl_status = $json_response->getStatusCode();
                $json_response = $json_response->getBody();

                // If the user exists
                // - create a new password
                // - update the user
                if($curl_status == 200) {

                    // Decode the response
                    $json_response = json_decode($json_response);
                    $user_id = $json_response->{'results'}->{'user_id'};

                    // Get user's data
                    $first_name = $json_response->{'results'}->{'first_name'};
                    $last_name = $json_response->{'results'}->{'last_name'};

                    // Generate a new password
                    $token_generator = new TokenGenerator();
                    $token = $token_generator->generateToken(10);

                    // Set the entity manager
                    $em = $this->getDoctrine()->getManager();

                    // Create a ResetPwd entity
                    $reset_password = new ResetPwdToken();
                    $reset_password->setToken( $token );
                    $reset_password->setUserId( $user_id );
                    $em->persist( $reset_password );
                    $em->flush();

                    // Send the email with the info to reset the password
                    $message = \Swift_Message::newInstance()
                        ->setSubject( 'Strime - ' . $this->get('translator')->trans('front.controller_pages.ajax_forgotten_pwd.your_new_password', array(), 'front_controller_pages') )
                        ->setFrom(array('contact@strime.io' => 'Strime'))
                        ->setTo( array( $email => $first_name." ".$last_name ) )
                        ->setBody(
                            $this->renderView(
                                // app/Resources/views/debug/debug.html.twig
                                'emails/new_password.html.twig',
                                array(
                                    'current_year' => date('Y'),
                                    'first_name' => $first_name,
                                    'token' => $token,
                                    'user_id' => $user_id
                                )
                            ),
                            'text/html'
                        )
                        /*
                         * If you also want to include a plaintext version of the message
                        ->addPart(
                            $this->renderView(
                                'Emails/registration.txt.twig',
                                array('name' => $name)
                            ),
                            'text/plain'
                        )
                        */
                    ;
                    $this->get('mailer')->send($message);
                    $transport = $this->container->get('mailer')->getTransport();
                    $spool = $transport->getSpool();
                    $spool->flushQueue($this->container->get('swiftmailer.transport.real'));

                    // return the response
                    $json_response = json_encode(array(
                        "response_code" => 200,
                    ));
                    echo $json_response;
                    die;
                }

                // If the user doesn't exist, return the JSON to the browser
                else {
                    echo $json_response;
                    die;
                }
            }

            // If the submitted form is not valid
            else {
                echo json_encode(array("status" => "error"));
            }
        }
    }
}
