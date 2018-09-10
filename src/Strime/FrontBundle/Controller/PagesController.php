<?php

namespace Strime\FrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

use Strime\GlobalBundle\Token\TokenGenerator;
use Strime\Slackify\Webhooks\Webhook;

use Strime\GlobalBundle\Entity\EmailConfirmationToken;
use Strime\GlobalBundle\Entity\ResetPwdToken;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class PagesController extends Controller
{
    /**
     * @Route("/", name="home")
     * @Template("StrimeFrontBundle:Pages:index.html.twig")
     */
    public function homeAction(Request $request)
    {
        // Check if there is a session
        $session = $request->getSession();
        $bag = $session->getBag("attributes");

        // Create the form object
        $form = $this->container->get('strime.helpers.form');
        $form->request = $request;

        // Create the signup form
        $signup_form = $form->createSignupForm();
        $signup_form_bottom = $form->createSimpleSignupForm();

        // Prepare messages for the signup form
        $signup = $form->prepareSignupMessages($signup_form);

        // Create the login form
        $login_form = $form->createLoginForm();

        // Prepare messages for the login form
        $login = $form->prepareLoginMessages($login_form);

        // Create the forgotten password form
        $forgotten_password_form = $form->createForgottenPasswordForm();

        return array(
            'body_classes' => 'home',
            'current_path' => 'home',
            'signup_form' => $signup['signup_form'],
            'signup_form_bottom' => $signup_form_bottom->createView(),
            'signup_message' => $signup['signup_message'],
            'login_form' => $login['login_form'],
            'login_message' => $login['login_message'],
            'forgotten_password_form' => $forgotten_password_form->createView()
        );
    }

    /**
     * @Route("/offers", name="offers")
     * @Template("StrimeFrontBundle:Pages:offers.html.twig")
     */
    public function offersAction(Request $request)
    {
        return $this->redirectToRoute('home', array(), 301);
    }

    /**
     * @Route("/about", name="about")
     * @Template("StrimeFrontBundle:Pages:about.html.twig")
     */
    public function aboutAction(Request $request)
    {
        // Create the form object
        $form = $this->container->get('strime.helpers.form');
        $form->request = $request;

        // Create the signup form
        $signup_form = $form->createSignupForm();
        $signup_form_bottom = $form->createSimpleSignupForm();

        // Prepare messages for the signup form
        $signup = $form->prepareSignupMessages($signup_form);

        // Create the login form
        $login_form = $form->createLoginForm();

        // Prepare messages for the login form
        $login = $form->prepareLoginMessages($login_form);

        // Create the forgotten password form
        $forgotten_password_form = $form->createForgottenPasswordForm();

        return array(
            'body_classes' => 'about',
            'current_path' => 'about',
            'signup_form' => $signup['signup_form'],
            'signup_message' => $signup['signup_message'],
            'login_form' => $login['login_form'],
            'login_message' => $login['login_message'],
            'forgotten_password_form' => $forgotten_password_form->createView()
        );
    }

    /**
     * @Route("/contact", name="contact")
     * @Template("StrimeFrontBundle:Pages:contact.html.twig")
     */
    public function contactAction(Request $request)
    {
        // Check if there is a session
        $session = $request->getSession();
        $bag = $session->getBag("attributes");

        // Create the form object
        $form = $this->container->get('strime.helpers.form');
        $form->request = $request;

        // Create the signup form
        $signup_form = $form->createSignupForm();
        $signup_form_bottom = $form->createSimpleSignupForm();

        // Prepare messages for the signup form
        $signup = $form->prepareSignupMessages($signup_form);

        // Create the login form
        $login_form = $form->createLoginForm();

        // Prepare messages for the login form
        $login = $form->prepareLoginMessages($login_form);

        // Create the forgotten password form
        $forgotten_password_form = $form->createForgottenPasswordForm();

        // Set the contact form
        $subjects = array(
            'front.controller_pages.contact.subject' => 0,
            'front.controller_pages.contact.technical_issue' => 1,
            'front.controller_pages.contact.new_feature' => 2,
            'front.controller_pages.contact.question_on_the_offers' => 3,
            'front.controller_pages.contact.candidature' => 4,
            'front.controller_pages.contact.other' => 5
        );

        // Create the user is logged in form
        $user_id = $bag->get('user_id');

        if($user_id != NULL) {

            // Set the data
            $user_name = $bag->get('first_name')." ".$bag->get('last_name');
            $user_email = $bag->get('email');

            $contact_form = $this->createFormBuilder()
                ->add('name', TextType::class, array(
                    'label' => '',
                    'attr' => array(
                        'placeholder' => 'front.controller_pages.contact.name'
                    ),
                    'data' => $user_name,
                    'translation_domain' => 'front_controller_pages'))
                ->add('email', EmailType::class, array(
                    'label' => '',
                    'attr' => array(
                        'placeholder' => 'front.controller_pages.contact.email'
                    ),
                    'data' => $user_email,
                    'translation_domain' => 'front_controller_pages'))
                ->add('subject', ChoiceType::class, array(
                        'choices' => $subjects,
                        'expanded' => FALSE,
                        'multiple' => FALSE,
                        'choices_as_values' => TRUE,
                        'translation_domain' => 'front_controller_pages'
                    ))
                ->add('message', TextareaType::class, array(
                    'label' => '',
                    'attr' => array(
                        'placeholder' => 'front.controller_pages.contact.your_message'
                    ),
                    'translation_domain' => 'front_controller_pages'))
                ->add('submit', SubmitType::class, array(
                        'attr' => array('class' => '')
                ))
                ->getForm();
        }

        // If the user is not logged in
        else {
            $contact_form = $this->createFormBuilder()
                ->add('name', TextType::class, array(
                    'label' => '',
                    'attr' => array(
                        'placeholder' => 'front.controller_pages.contact.name'
                    ),
                    'translation_domain' => 'front_controller_pages'))
                ->add('email', TextType::class, array(
                    'label' => '',
                    'attr' => array(
                        'placeholder' => 'front.controller_pages.contact.email'
                    ),
                    'translation_domain' => 'front_controller_pages'))
                ->add('subject', ChoiceType::class, array(
                        'choices' => $subjects,
                        'expanded' => FALSE,
                        'multiple' => FALSE,
                        'choices_as_values' => TRUE,
                        'translation_domain' => 'front_controller_pages'
                    ))
                ->add('message', TextareaType::class, array(
                    'label' => '',
                    'attr' => array(
                        'placeholder' => 'front.controller_pages.contact.your_message'
                    ),
                    'translation_domain' => 'front_controller_pages'))
                ->add('submit', submitType::class, array(
                        'attr' => array('class' => '')
                ))
                ->getForm();
        }

        // Handle the request
        $contact_form->handleRequest($request);

        // If the form is submitted
        if($contact_form->isSubmitted()) {

            // If the submitted form is valid
            if($contact_form->isValid()) {

                // Get the data
                $contact_name = $contact_form->get('name')->getData();
                $contact_email = $contact_form->get('email')->getData();
                $subject = $contact_form->get('subject')->getData();
                $contact_message = $contact_form->get('message')->getData();

                // If the user has chosen a subject
                if($subject != 0) {

                    foreach ($subjects as $key => $value) {
                        if($value == $subject) {
                            $subject = $key;
                        }
                    }

                    // Send the message email
                    $message = \Swift_Message::newInstance()
                        ->setSubject('Strime - Contact : '.$this->get('translator')->trans($subject, array(), 'front_controller_pages'))
                        ->setFrom( array($contact_email => $contact_name) )
                        ->setTo( array( 'contact@strime.io' ) )
                        ->setBody(
                            $this->renderView(
                                // app/Resources/views/debug/debug.html.twig
                                'emails/contact.html.twig',
                                array(
                                    'current_year' => date('Y'),
                                    'name' => $contact_name,
                                    'email' => $contact_email,
                                    'subject' => $this->get('translator')->trans($subject, array(), 'front_controller_pages'),
                                    'message' => $contact_message,
                                    'browser' => $_SERVER['HTTP_USER_AGENT'],
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

                    $this->addFlash(
                        'success',
                        $this->get('translator')->trans('front.controller_pages.contact.message_sent_will_answer_asap', array(), 'front_controller_pages')
                    );

                    // Send a request to Hubspot to create or update a contact
                    // Get the parameters
                    $hubspot_api_key = $this->container->getParameter('hubspot_api_key');

                    // Set the endpoint
                    $endpoint = 'https://api.hubapi.com/contacts/v1/contact/createOrUpdate/email/'.$contact_email.'/?hapikey='.$hubspot_api_key;

                    // Set the parameters
                    $name_elts = explode(" ", $contact_name);
                    if(isset($name_elts[0]))
                        $contact_first_name = $name_elts[0];
                    else
                        $contact_first_name = "";

                    if(isset($name_elts[1]))
                        $contact_last_name = $name_elts[1];
                    else
                        $contact_last_name = "";

                    $params = array(
                        "properties" => array(
                            array(
                                "property" => "email",
                                "value" => $contact_email
                            ),
                            array(
                                "property" => "firstname",
                                "value" => $contact_first_name
                            ),
                            array(
                                "property" => "lastname",
                                "value" => $contact_last_name
                            ),
                        )
                    );

                    $client = new \GuzzleHttp\Client();
                    $json_response = $client->request('POST', $endpoint, [
                        'http_errors' => false,
                        'json' => $params
                    ]);

                    $curl_status = $json_response->getStatusCode();
                    $response = json_decode($json_response->getBody());

                    if($curl_status != 200) {
                        // Send a notification to Slack
                        $slack_webhook = new Webhook( $this->container->getParameter('slack_strime_billing_channel') );
                        $slack_webhook->setAttachments(
                            array(
                                array(
                                    "fallback" => "Détails de l'erreur",
                                    "text" => $response->{'message'},
                                    "color" => "alert",
                                    "author" => array(
                                        "author_name" => "Mr Robot"
                                    ),
                                    "title" => "Détails de l'erreur",
                                    "fields" => array(
                                        "title" => "Détails de l'erreur",
                                        "value" => $response->{'message'},
                                        "short" => FALSE
                                    ),
                                    "footer_icon" => "https://www.strime.io/bundles/strimeglobal/img/icon-strime.jpg",
                                    "ts" => time()
                                )
                            )
                        );
                        $slack_webhook->sendMessage(array(
                            "message" => "Erreur lors de l'ajout d'un contact à Hubspot",
                            "username" => "[".ucfirst( $this->container->getParameter("kernel.environment") )."] Strime.io",
                            "icon" => ":shit:"
                        ));
                    }
                }

                // If the user hasn't chosen a subject
                else {
                    $this->addFlash(
                        'error',
                        $this->get('translator')->trans('front.controller_pages.contact.please_choose_subject', array(), 'front_controller_pages')
                    );
                }

                // We recreate the form with the submitted data
                unset($contact_form);

                $contact_form = $this->createFormBuilder()
                    ->add('name', TextType::class, array(
                        'label' => '',
                        'attr' => array(
                            'placeholder' => $this->get('translator')->trans('front.controller_pages.contact.name', array(), 'front_controller_pages')
                        ),
                        'data' => $contact_name))
                    ->add('email', TextType::class, array(
                        'label' => '',
                        'attr' => array(
                            'placeholder' => $this->get('translator')->trans('front.controller_pages.contact.email', array(), 'front_controller_pages')
                        ),
                        'data' => $contact_email))
                    ->add('subject', ChoiceType::class, array(
                            'choices' => $subjects,
                            'expanded' => FALSE,
                            'multiple' => FALSE,
                            'data' => $subject,
                            'choices_as_values' => TRUE,
                            'translation_domain' => 'front_controller_pages'
                    ))
                    ->add('message', TextareaType::class, array(
                        'label' => '',
                        'attr' => array(
                            'placeholder' => $this->get('translator')->trans('front.controller_pages.contact.your_message', array(), 'front_controller_pages')
                        ),
                        'data' => $contact_message))
                    ->add('submit', SubmitType::class, array(
                            'attr' => array('class' => '')
                    ))
                    ->getForm();
            }

            // If the submitted form is not valid
            else {
                $this->addFlash(
                    'error',
                    $this->get('translator')->trans('front.controller_pages.contact.all_the_fields_not_provided', array(), 'front_controller_pages')
                );
            }
        }

        return array(
            'body_classes' => 'contact',
            'current_path' => 'contact',
            'signup_form' => $signup['signup_form'],
            'signup_message' => $signup['signup_message'],
            'login_form' => $login['login_form'],
            'login_message' => $login['login_message'],
            'forgotten_password_form' => $forgotten_password_form->createView(),
            'contact_form' => $contact_form->createView()
        );
    }

    /**
     * @Route("/tos", name="tos")
     * @Template("StrimeFrontBundle:Pages:tos.html.twig")
     */
    public function tosAction(Request $request)
    {
        // Create the form object
        $form = $this->container->get('strime.helpers.form');
        $form->request = $request;

        // Create the signup form
        $signup_form = $form->createSignupForm();
        $signup_form_bottom = $form->createSimpleSignupForm();

        // Prepare messages for the signup form
        $signup = $form->prepareSignupMessages($signup_form);

        // Create the login form
        $login_form = $form->createLoginForm();

        // Prepare messages for the login form
        $login = $form->prepareLoginMessages($login_form);

        // Create the forgotten password form
        $forgotten_password_form = $form->createForgottenPasswordForm();

        return array(
            'body_classes' => 'tos',
            'current_path' => 'tos',
            'signup_form' => $signup['signup_form'],
            'signup_message' => $signup['signup_message'],
            'login_form' => $login['login_form'],
            'login_message' => $login['login_message'],
            'forgotten_password_form' => $forgotten_password_form->createView()
        );
    }

    /**
     * @Route("/faq", name="faq")
     * @Template("StrimeFrontBundle:Pages:faq.html.twig")
     */
    public function faqAction(Request $request)
    {
        // Create the form object
        $form = $this->container->get('strime.helpers.form');
        $form->request = $request;

        // Create the signup form
        $signup_form = $form->createSignupForm();
        $signup_form_bottom = $form->createSimpleSignupForm();

        // Prepare messages for the signup form
        $signup = $form->prepareSignupMessages($signup_form);

        // Create the login form
        $login_form = $form->createLoginForm();

        // Prepare messages for the login form
        $login = $form->prepareLoginMessages($login_form);

        // Create the forgotten password form
        $forgotten_password_form = $form->createForgottenPasswordForm();

        return array(
            'body_classes' => 'faq',
            'current_path' => 'faq',
            'signup_form' => $signup['signup_form'],
            'signup_message' => $signup['signup_message'],
            'login_form' => $login['login_form'],
            'login_message' => $login['login_message'],
            'forgotten_password_form' => $forgotten_password_form->createView()
        );
    }

    /**
     * @Route("/trust-badge", name="trust_badge")
     * @Template("StrimeFrontBundle:Pages:trust-badge.html.twig")
     */
    public function trustBadgeAction(Request $request)
    {
        // Create the form object
        $form = $this->container->get('strime.helpers.form');
        $form->request = $request;

        // Create the signup form
        $signup_form = $form->createSignupForm();
        $signup_form_bottom = $form->createSimpleSignupForm();

        // Prepare messages for the signup form
        $signup = $form->prepareSignupMessages($signup_form);

        // Create the login form
        $login_form = $form->createLoginForm();

        // Prepare messages for the login form
        $login = $form->prepareLoginMessages($login_form);

        // Create the forgotten password form
        $forgotten_password_form = $form->createForgottenPasswordForm();

        return array(
            'body_classes' => 'trust-badge',
            'current_path' => 'trust_badge',
            'signup_form' => $signup['signup_form'],
            'signup_message' => $signup['signup_message'],
            'login_form' => $login['login_form'],
            'login_message' => $login['login_message'],
            'forgotten_password_form' => $forgotten_password_form->createView()
        );
    }

    /**
     * @Route("/new-features", name="new_features")
     * @Template("StrimeFrontBundle:Pages:new-features.html.twig")
     */
    public function newFeaturesAction(Request $request)
    {
        // Create the form object
        $form = $this->container->get('strime.helpers.form');
        $form->request = $request;

        // Create the signup form
        $signup_form = $form->createSignupForm();
        $signup_form_bottom = $form->createSimpleSignupForm();

        // Prepare messages for the signup form
        $signup = $form->prepareSignupMessages($signup_form);

        // Create the login form
        $login_form = $form->createLoginForm();

        // Prepare messages for the login form
        $login = $form->prepareLoginMessages($login_form);

        // Create the forgotten password form
        $forgotten_password_form = $form->createForgottenPasswordForm();

        // Set the entity manager
        $em = $this->getDoctrine()->getManager();

        // Get the list of new features added
        $new_features = $em->getRepository('StrimeGlobalBundle:NewFeature')->findAll(array(), array('created_at' => 'DESC'));

        return array(
            'body_classes' => 'faq',
            'current_path' => 'new_features',
            'signup_form' => $signup['signup_form'],
            'signup_message' => $signup['signup_message'],
            'login_form' => $login['login_form'],
            'login_message' => $login['login_message'],
            'forgotten_password_form' => $forgotten_password_form->createView(),
            'new_features' => $new_features
        );
    }


    /**
     * @Route("/confirm-email/{user_id}/{token}/{locale}", defaults={"user_id": NULL, "token": NULL, "locale": NULL}, name="confirm_email")
     * @Template("StrimeFrontBundle:Pages:confirm-email.html.twig")
     */
    public function confirmEmailAction(Request $request, $user_id, $token, $locale)
    {

        // If a locale has been set, change the locale.
        if($locale != NULL) {

            // Set the locale
            $request->setLocale( $locale );
            $this->container->get('translator')->setLocale( $locale );

            // Set the locale in the session
            $session = $request->getSession();
            $session->set('_locale', $locale);
        }

        // Create the form object
        $form = $this->container->get('strime.helpers.form');
        $form->request = $request;

        // Create the signup form
        $signup_form = $form->createSignupForm();
        $signup_form_bottom = $form->createSimpleSignupForm();

        // Prepare messages for the signup form
        $signup = $form->prepareSignupMessages($signup_form);

        // Create the login form
        $login_form = $form->createLoginForm();

        // Prepare messages for the login form
        $login = $form->prepareLoginMessages($login_form);

        // Create the forgotten password form
        $forgotten_password_form = $form->createForgottenPasswordForm();


        // If one parameter is missing
        if(($user_id == NULL) || ($token == NULL)) {

            // Redirect to the homepage
            return $this->redirect( $this->generateUrl('home') );
        }
        else {

            // Set the variable
            $email_is_confirmed = FALSE;

            // Check that the combination user_id / token is valid
            // Set the entity manager
            $em = $this->getDoctrine()->getManager();

            // Get the token of this user
            $user_needs_to_confirm_email = $em->getRepository('StrimeGlobalBundle:EmailConfirmationToken')->findAll(array('user_id' => $user_id, 'token' => $token));

            // If the confirmation failed
            if($user_needs_to_confirm_email == FALSE) {

                $this->addFlash(
                    'error',
                    $this->get('translator')->trans('front.controller_pages.confirm.not_able_to_confirm_email', array(), 'front_controller_pages')
                );
            }

            // If the confirmation succeeded
            else {

                // Remove the entry from the DB
                foreach ($user_needs_to_confirm_email as $email_to_confirm) {
                    $em->remove( $email_to_confirm );
                }
                $em->flush();

                // Update the user in the API
                $params = array(

                );

                // Get the parameters
                $strime_api_url = $this->container->getParameter('strime_api_url');
                $strime_api_token = $this->container->getParameter('strime_api_token');

                // Set the endpoint
                $endpoint = $strime_api_url.'user/'.$user_id.'/confirm-email';

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
                $response = json_decode($json_response->getBody());

                // Return the result
                if($curl_status == 200) {

                    $email_is_confirmed = TRUE;

                    // Prepare the flash message
                    $this->addFlash(
                        'success',
                        $this->get('translator')->trans('front.controller_pages.confirm.email_confirmed', array(), 'front_controller_pages')
                    );
                }
                else {
                    $this->addFlash(
                        'error',
                        $this->get('translator')->trans('front.controller_pages.confirm.error_while_updating_profile', array(), 'front_controller_pages')
                    );
                }
            }
        }

        return array(
            'body_classes' => 'confirm-email',
            'current_path' => 'confirm_email',
            'signup_form' => $signup['signup_form'],
            'signup_message' => $signup['signup_message'],
            'login_form' => $login['login_form'],
            'login_message' => $login['login_message'],
            'forgotten_password_form' => $forgotten_password_form->createView(),
            'email_is_confirmed' => $email_is_confirmed
        );
    }


    /**
     * @Route("/reset-password/{user_id}/{token}/{locale}", defaults={"user_id": NULL, "token": NULL, "locale": NULL}, name="reset_pwd")
     * @Template("StrimeFrontBundle:Pages:reset-pwd.html.twig")
     */
    public function resetPwdAction(Request $request, $user_id, $token, $locale)
    {

        // If a locale has been set, change the locale.
        if($locale != NULL) {

            // Set the locale
            $request->setLocale( $locale );
            $this->container->get('translator')->setLocale( $locale );

            // Set the locale in the session
            $session = $request->getSession();
            $session->set('_locale', $locale);
        }

        // Create the form object
        $form = $this->container->get('strime.helpers.form');
        $form->request = $request;

        // Create the signup form
        $signup_form = $form->createSignupForm();
        $signup_form_bottom = $form->createSimpleSignupForm();

        // Prepare messages for the signup form
        $signup = $form->prepareSignupMessages($signup_form);

        // Create the login form
        $login_form = $form->createLoginForm();

        // Prepare messages for the login form
        $login = $form->prepareLoginMessages($login_form);

        // Create the forgotten password form
        $forgotten_password_form = $form->createForgottenPasswordForm();

        // Create the form object
        $back_form = $this->container->get('strime.back_helpers.form');
        $back_form->request = $request;

        // Create the form
        $edit_profile_password_form = $this->createFormBuilder()
            ->add('new_password', PasswordType::class, array('label' => '', 'attr' => array('placeholder' => 'front.controller_pages.reset_pwd.new_pwd')))
            ->add('new_password_2', PasswordType::class, array('label' => '', 'attr' => array('placeholder' => 'front.controller_pages.reset_pwd.retype_new_pwd')))
            ->add('submit', SubmitType::class, array(
                    'attr' => array('class' => '')
            ))
            ->getForm();

        // If one parameter is missing
        if(($user_id == NULL) || ($token == NULL)) {

            // Redirect to the homepage
            return $this->redirect( $this->generateUrl('home') );
        }
        else {

            // Set the variables
            $password_is_reseted = FALSE;
            $authorized_to_edit = TRUE;

            // Check that the combination user_id / token is valid
            // Set the entity manager
            $em = $this->getDoctrine()->getManager();

            // Get the token of this user
            $token_is_valid = $em->getRepository('StrimeGlobalBundle:ResetPwdToken')->findAll(array('user_id' => $user_id, 'token' => $token));

            // If the confirmation failed
            if($token_is_valid == FALSE) {

                $authorized_to_edit = FALSE;

                $this->addFlash(
                    'error',
                    $this->get('translator')->trans('front.controller_pages.confirm.not_able_to_reset_password', array(), 'front_controller_pages')
                );
            }

            // If the confirmation succeeded
            else {

                // Handle the request
                $edit_profile_password_form->handleRequest($request);

                // If the form is submitted
                if($edit_profile_password_form->isSubmitted()) {

                    // If the submitted form is valid
                    if($edit_profile_password_form->isValid()) {

                        // Get the data
                        $new_password = $edit_profile_password_form->get('new_password')->getData();
                        $new_password_confirm = $edit_profile_password_form->get('new_password_2')->getData();

                        // If the new passwords are not equal
                        if(strcmp($new_password, $new_password_confirm) != 0) {

                            $this->addFlash(
                                'error',
                                $this->get('translator')->trans('front.controller_pages.confirm.passwords_are_not_identical', array(), 'front_controller_pages')
                            );
                        }

                        // If the passwords are identical, proceed to the profile modification
                        else {

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
                                'old_password' => "strime-reset-password",
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
                                    $this->get('translator')->trans('front.controller_pages.reset_pwd.incorrect_pwd', array(), 'front_controller_pages')
                                );
                            }
                            elseif($curl_status == 200) {

                                $password_is_reseted = TRUE;

                                // Prepare the flash message
                                $this->addFlash(
                                    'success',
                                    $this->get('translator')->trans('front.controller_pages.reset_pwd.new_pwd_saved', array(), 'front_controller_pages')
                                );

                                // Remove the entry from the DB
                                foreach ($token_is_valid as $token_entity) {
                                    $em->remove( $token_entity );
                                }
                                $em->flush();
                            }
                            else {
                                $this->addFlash(
                                    'error',
                                    $this->get('translator')->trans('front.controller_pages.reset_pwd.error_occured_while_updating_pwd', array(), 'front_controller_pages')
                                );
                            }
                        }
                    }

                    // If the submitted form is not valid
                    else {
                        $this->addFlash(
                            'error',
                            $this->get('translator')->trans('back.controller_app.profile_pwd.all_fields_not_provided', array(), 'front_controller_pages')
                        );
                    }
                }
            }
        }

        return array(
            'body_classes' => 'confirm-email',
            'current_path' => 'confirm_email',
            'signup_form' => $signup['signup_form'],
            'signup_message' => $signup['signup_message'],
            'login_form' => $login['login_form'],
            'login_message' => $login['login_message'],
            'forgotten_password_form' => $forgotten_password_form->createView(),
            'edit_profile_password_form' => $edit_profile_password_form->createView(),
            'password_is_reseted' => $password_is_reseted,
            'user_id' => $user_id,
            'token' => $token,
            'authorized_to_edit' => $authorized_to_edit
        );
    }


    /**
     * @Route("/sitemap.{_format}", defaults={"_format": "xml"}, name="sitemap")
     * @Template("StrimeFrontBundle:Sitemaps:main.xml.twig")
     */
    public function sitemapAction(Request $request)
    {
        // Get the list of URLs to include
        $urls = $this->get('strime.sitemap')->generate();

        return array(
            'urls' => $urls
        );
    }

    /**
     * @Route("/signout", name="signout")
     * @Template("StrimeFrontBundle:Pages:signout.html.twig")
     */
    public function signoutAction(Request $request)
    {
        // Get the session
        $session = $request->getSession();

        // If a language is defined in the session, extract it
        $locale = $session->get('_locale');

        // Clear the session
        $session->clear();

        // If a locale was defined, set it again
        if($locale != NULL) {
            $session->set('_locale', $locale);
        }

        // Redirect to the homepage
        return $this->redirect( $this->generateUrl('home') );
    }
}
