<?php

namespace Strime\BackBundle\Controller;

use Strime\GlobalBundle\Controller\TokenAuthenticatedController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use FOS\RestBundle\Controller\FOSRestController;
use Strime\Slackify\Webhooks\Webhook;

use Strime\GlobalBundle\Auth\HeadersAuthorization;

class WebhookController extends FOSRestController implements TokenAuthenticatedController
{
    /**
     * @Route("/webhook/encoding/done", name="app_webhook_encoding_done")
     * @Template()
     */
    public function encodingDoneAction(Request $request)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('strime_app_name'),
            "version" => $this->container->getParameter('strime_app_version'),
            "method" => "/webhook/encoding/done"
        );

        // Get the parameters
        $strime_api_url = $this->container->getParameter('strime_api_url');
        $strime_api_token = $this->container->getParameter('strime_api_token');

        // Set the headers
        $headers = array(
            'Accept' => 'application/json',
            'X-Auth-Token' => $strime_api_token,
            'Content-type' => 'application/json'
        );

        // Get the variables
        $first_name = $request->request->get('first_name', NULL);
        $last_name = $request->request->get('last_name', NULL);
        $email = $request->request->get('email', NULL);
        $asset_id = $request->request->get('asset_id', NULL);
        $asset_type = $request->request->get('asset_type', NULL);
        $locale = $request->request->get('locale', NULL);
        switch ($asset_type) {
            case 'video':
                $url = $this->container->getParameter('strime_app_url') . "app/video/" . $asset_id;
                break;
            case 'audio':
                $url = $this->container->getParameter('strime_app_url') . "app/audio/" . $asset_id;
                break;

            default:
                $url = $this->container->getParameter('strime_app_url') . "app/video/" . $asset_id;
                break;
        }
        $emails_list = array();

        // Set the locale based on user preferences
        $request->setLocale( $locale );
        $this->container->get('translator')->setLocale( $locale );

        // Set the locale in the session
        $session = $request->getSession();
        $session->set('_locale', $locale);

        // Get the details of the asset
        // Set the endpoint
        switch ($asset_type) {
            case 'video':
                $endpoint = $strime_api_url."video/".$asset_id."/get";
                break;
            case 'audio':
                $endpoint = $strime_api_url."audio/".$asset_id."/get";
                break;

            default:
                $endpoint = $strime_api_url."video/".$asset_id."/get";
                break;
        }

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $curl_status = $json_response->getStatusCode();
        $response = json_decode( $json_response->getBody() );

        // If the request was properly executed
        if(($curl_status == 200) && isset($response->{'results'})) {

            $asset = $response->{'results'};
            $asset_name = $asset->{'name'};

            // If the asset has contacts
            if($asset->{'contacts'} != NULL) {

                // Foreach contact, send him an email
                foreach ($asset->{'contacts'} as $contact) {

                    $emails_list[] = $contact->{'email'};

                    // Send an email to the contacts associated to the video
                    // Set variables
                    $contact_url = $url . "/contact/" . $contact->{'contact_id'};
                    switch ($asset_type) {
                        case 'video':
                            $subject = "Strime - " . $this->get('translator')->trans('back.controller_webhook.encoding_done.invites_you_to_comment_video', array("%first_name%" => $first_name), 'back_controller_webhook');
                            break;
                        case 'audio':
                            $subject = "Strime - " . $this->get('translator')->trans('back.controller_webhook.encoding_done.invites_you_to_comment_audio', array("%first_name%" => $first_name), 'back_controller_webhook');
                            break;

                        default:
                            $subject = "Strime - " . $this->get('translator')->trans('back.controller_webhook.encoding_done.invites_you_to_comment_file', array("%first_name%" => $first_name), 'back_controller_webhook');
                            break;
                    }

                    $message = \Swift_Message::newInstance()
                        ->setSubject($subject)
                        ->setFrom(array($email => $first_name." ".$last_name))
                        ->setTo( $contact->{'email'} )
                        ->setBody(
                            $this->renderView(
                                'emails/share_url.html.twig',
                                array(
                                    'current_year' => date('Y'),
                                    'first_name' => $first_name,
                                    'last_name' => $last_name,
                                    'message' => NULL,
                                    'url' => $contact_url,
                                    'type' => $asset_type,
                                    'content_name' => $asset_name,
                                    'locale' => $locale
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
                }
            }
        }


        // Change the format of the emails list
        if(count($emails_list) > 0)
            $emails_list = implode(", ", $emails_list);
        else
            $emails_list = NULL;



        // Send an email to the user to warn him that the encoding is done.
        switch ($asset_type) {
            case 'video':
                $subject = "Strime - " . $this->get('translator')->trans('back.controller_webhook.encoding_done.video_encoding_done', array("%first_name%" => $first_name), 'back_controller_webhook');
                break;
            case 'audio':
                $subject = "Strime - " . $this->get('translator')->trans('back.controller_webhook.encoding_done.audio_encoding_done', array("%first_name%" => $first_name), 'back_controller_webhook');
                break;

            default:
                $subject = "Strime - " . $this->get('translator')->trans('back.controller_webhook.encoding_done.file_encoding_done', array("%first_name%" => $first_name), 'back_controller_webhook');
                break;
        }

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom(array('contact@strime.io' => 'Strime'))
            ->setTo( array( $email => $first_name." ".$last_name ) )
            ->setBody(
                $this->renderView(
                    'emails/encoding_done.html.twig',
                    array(
                        'current_year' => date('Y'),
                        'first_name' => $first_name,
                        'emails_list' => $emails_list,
                        'asset_type' => $asset_type,
                        'locale' => $locale
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

        $json["status"] = "success";
        $json["response_code"] = "200";
        return new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        exit;
    }




    /**
     * @Route("/webhook/encoding/killed", name="app_webhook_encoding_killed")
     * @Template()
     */
    public function encodingKilledAction(Request $request)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('strime_app_name'),
            "version" => $this->container->getParameter('strime_app_version'),
            "method" => "/webhook/encoding/killed"
        );

        // Get the variables
        $first_name = $request->request->get('first_name', NULL);
        $last_name = $request->request->get('last_name', NULL);
        $email = $request->request->get('email', NULL);
        $asset = $request->request->get('asset', NULL);
        $asset_type = $request->request->get('asset_type', NULL);
        $locale = $request->request->get('locale', NULL);

        // Set the locale based on user preferences
        $request->setLocale( $locale );
        $this->container->get('translator')->setLocale( $locale );

        // Set the locale in the session
        $session = $request->getSession();
        $session->set('_locale', $locale);

        // Send an email to the user to warn him that the encoding has been killed.
        switch ($asset_type) {
            case 'video':
                $subject = $this->get('translator')->trans('back.controller_webhook.encoding_killed.video_encoding_killed', array("%first_name%" => $first_name), 'back_controller_webhook');
                break;
            case 'audio':
                $subject = $this->get('translator')->trans('back.controller_webhook.encoding_killed.audio_encoding_killed', array("%first_name%" => $first_name), 'back_controller_webhook');
                break;

            default:
                $subject = $this->get('translator')->trans('back.controller_webhook.encoding_killed.encoding_killed', array("%first_name%" => $first_name), 'back_controller_webhook');
                break;
        }
        $message = \Swift_Message::newInstance()
            ->setSubject('Strime - ' . $subject)
            ->setFrom(array('contact@strime.io' => 'Strime'))
            ->setTo( array( $email => $first_name." ".$last_name ) )
            ->setBcc( 'technique@strime.io' )
            ->setBody(
                $this->renderView(
                    'emails/encoding_killed.html.twig',
                    array(
                        'current_year' => date('Y'),
                        'first_name' => $first_name,
                        'asset' => $asset,
                        'asset_type' => $asset_type,
                        'locale' => $locale
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

        $json["status"] = "success";
        $json["response_code"] = "200";
        return new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        exit;
    }




    /**
     * @Route("/webhook/resend-email-confirmation-message/{user_id}/{days}", defaults={"user_id": NULL, "days": NULL}, name="app_webhook_resend_email_confirmation_message")
     * @Template()
     */
    public function resendEmailConfirmationMessageAction(Request $request, $user_id, $days)
    {
        // Get the entity manager
        $em = $this->getDoctrine()->getManager();

        // Check if there is a token for this user
        $email_confirmation_token = $em->getRepository('StrimeGlobalBundle:EmailConfirmationToken')->findOneBy(array('user_id' => $user_id));

        if($email_confirmation_token != NULL) {

            // Get the parameters
            $strime_api_url = $this->container->getParameter('strime_api_url');
            $strime_api_token = $this->container->getParameter('strime_api_token');

            // Set the headers
            $headers = array(
                'Accept' => 'application/json',
                'X-Auth-Token' => $strime_api_token,
                'Content-type' => 'application/json'
            );

            // Send a request to get the details of the user
            // Set the endpoint
            $endpoint = $strime_api_url."user/".$user_id."/get";

            // Send the request
            $client = new \GuzzleHttp\Client();
            $json_response = $client->request('GET', $endpoint, [
                'headers' => $headers,
                'http_errors' => false
            ]);
            $curl_status = $json_response->getStatusCode();
            $response = json_decode( $json_response->getBody() );

            // If the request was properly executed
            if($curl_status == 200) {
                $user = $response->{'results'};

                // Set the locale based on user preferences
                $request->setLocale( $user->{'locale'} );
                $this->container->get('translator')->setLocale( $user->{'locale'} );

                // Set the locale in the session
                $session = $request->getSession();
                $session->set('_locale', $user->{'locale'});
            }
            else {
                $user = NULL;
            }

            if($user != NULL) {

                // Send an email with the link to confirm the email address
                $message = \Swift_Message::newInstance()
                    ->setSubject('Strime - ' . $this->get('translator')->trans('back.controller_webhook.resend_confirmation_email.confirm_email', array("%first_name%" => $user->{'first_name'}), 'back_controller_webhook'))
                    ->setFrom(array('contact@strime.io' => 'Strime'))
                    ->setTo( array( $user->{'email'} => $user->{'first_name'}." ".$user->{'last_name'} ) )
                    ->setBody(
                        $this->renderView(
                            // app/Resources/views/debug/debug.html.twig
                            'emails/email_confirmation_last_message.html.twig',
                            array(
                                'current_year' => date('Y'),
                                'user_id' => $user_id,
                                'first_name' => $user->{'first_name'},
                                'days' => $days,
                                'email_confirmation_token' => $email_confirmation_token->getToken(),
                                'locale' => $user->{'locale'}
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

            $json["status"] = "success";
            $json["response_code"] = "200";
            return new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            exit;
        }
        else {

            // There is no token for this user
            // Generate an error
            $json["status"] = "error";
            $json["response_code"] = "400";
            return new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            exit;
        }
    }




    /**
     * @Route("/webhook/remove-email-confirmation-token/{user_id}", defaults={"user_id": NULL}, name="app_webhook_remove_email_confirmation_token")
     * @Template()
     */
    public function removeEmailConfirmationTokenAction(Request $request, $user_id)
    {
        // Get the entity manager
        $em = $this->getDoctrine()->getManager();

        // Check if there is a token for this user
        $email_confirmation_token = $em->getRepository('StrimeGlobalBundle:EmailConfirmationToken')->findOneBy(array('user_id' => $user_id));

        if($email_confirmation_token != NULL) {

            $em->remove( $email_confirmation_token );
            $em->flush();

            $json["status"] = "success";
            $json["response_code"] = "200";
            return new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            exit;
        }
        else {

            // There is no token for this user
            // Generate an error
            $json["status"] = "error";
            $json["response_code"] = "400";
            return new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            exit;
        }
    }




    /**
     * @Route("/webhook/send-comments-daily/{user_id}", defaults={"user_id": NULL}, name="app_webhook_send_comments_daily")
     * @Template()
     */
    public function sendCommentsDailyNotificationsAction(Request $request, $user_id)
    {
        // Set a global flag to check if there are errors with the responses of the application
        $errors_in_api_requests = FALSE;

        // Get the entity manager
        $em = $this->getDoctrine()->getManager();

        // Get the request parameters
        $comments = $request->request->get('comments', NULL);
        $comments = json_decode($comments);

        // Get the parameters
        $strime_api_url = $this->container->getParameter('strime_api_url');
        $strime_api_token = $this->container->getParameter('strime_api_token');

        // Set the headers
        $headers = array(
            'Accept' => 'application/json',
            'X-Auth-Token' => $strime_api_token,
            'Content-type' => 'application/json'
        );

        // Send a request to get the details of the user
        // Set the endpoint
        $endpoint = $strime_api_url."user/".$user_id."/get";

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $curl_status = $json_response->getStatusCode();
        $response = json_decode( $json_response->getBody() );

        // If the request was properly executed
        if($curl_status == 200) {
            $user = $response->{'results'};

            // Set the locale based on user preferences
            $request->setLocale( $user->{'locale'} );
            $this->container->get('translator')->setLocale( $user->{'locale'} );

            // Set the locale in the session
            $session = $request->getSession();
            $session->set('_locale', $user->{'locale'});
        }
        else {
            $user = NULL;
            $errors_in_api_requests = TRUE;
        }

        if($user != NULL) {

            // Get the details of all the comments
            $comments_list = array();

            // If there are comments
            if(count($comments) > 0) {

                // Browse the comments
                foreach ($comments as $comment) {

                    // Get the details of the comment
                    // Set the endpoint
                    switch ($comment->{'asset_type'}) {
                        case 'video':
                            $endpoint = $strime_api_url."comment/".$comment->{'comment_id'}."/get";
                            break;
                        case 'image':
                            $endpoint = $strime_api_url."image/comment/".$comment->{'comment_id'}."/get";
                            break;

                        default:
                            $endpoint = $strime_api_url."comment/".$comment->{'comment_id'}."/get";
                            break;
                    }

                    // Send the request
                    $client = new \GuzzleHttp\Client();
                    $json_response = $client->request('GET', $endpoint, [
                        'headers' => $headers,
                        'http_errors' => false
                    ]);
                    $curl_status = $json_response->getStatusCode();
                    $response = json_decode( $json_response->getBody() );

                    // If the request was properly executed
                    if($curl_status == 200) {
                        $comment_details = $response->{'results'};
                    }
                    else {
                        $comment_details = NULL;
                        $errors_in_api_requests = TRUE;
                    }

                    if($comment_details != NULL) {
                        $comment_details->{'auto_login_token'} = hash('md5', $user->{'user_id'}."-".$comment_details->{'comment_id'}."-".$this->container->getParameter('secret'));
                        $comment_details->{'asset_type'} = $comment->{'asset_type'};
                        $comments_list[] = $comment_details;
                    }
                }

                // Send an email with the all the comments
                $message = \Swift_Message::newInstance()
                    ->setSubject('Strime - ' . $this->container->get('translator')->trans('back.controller_webhook.send_comments_notification.files_commented', array("%first_name%" => $user->{'first_name'}), 'back_controller_webhook'))
                    ->setFrom(array('contact@strime.io' => 'Strime'))
                    ->setTo( array( $user->{'email'} => $user->{'first_name'}." ".$user->{'last_name'} ) )
                    ->setBody(
                        $this->renderView(
                            'emails/comments_notification.html.twig',
                            array(
                                'current_year' => date('Y'),
                                'user_id' => $user_id,
                                'first_name' => $user->{'first_name'},
                                'comments' => $comments_list,
                                'locale' => $user->{'locale'}
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
        }

        if($errors_in_api_requests == FALSE) {
            $json["status"] = "success";
            $json["response_code"] = "200";
            return new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }
        else {
            $json["status"] = "error";
            $json["response_code"] = "400";
            return new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }
        exit;
    }




    /**
     * @Route("/webhook/send-comments-right-away/{user_id}/{asset_type}/{comment}", defaults={"user_id": NULL, "asset_type": "video", "comment": NULL}, name="app_webhook_send_comment_right_away")
     * @Template()
     */
    public function sendCommentsInstantNotificationsAction(Request $request, $user_id, $asset_type, $comment)
    {
        // If there is no comment, return an error
        if($comment == NULL) {

            $json["status"] = "error";
            $json["response_code"] = "400";
            $json["error_message"] = "no_comment_set";
            return new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }
        else {

            // Set a global flag to check if there are errors with the responses of the application
            $errors_in_api_requests = FALSE;

            // Get the entity manager
            $em = $this->getDoctrine()->getManager();

            // Get the parameters
            $strime_api_url = $this->container->getParameter('strime_api_url');
            $strime_api_token = $this->container->getParameter('strime_api_token');

            // Set the headers
            $headers = array(
                'Accept' => 'application/json',
                'X-Auth-Token' => $strime_api_token,
                'Content-type' => 'application/json'
            );

            // Send a request to get the details of the user
            // Set the endpoint
            $endpoint = $strime_api_url."user/".$user_id."/get";

            // Send the request
            $client = new \GuzzleHttp\Client();
            $json_response = $client->request('GET', $endpoint, [
                'headers' => $headers,
                'http_errors' => false
            ]);
            $curl_status = $json_response->getStatusCode();
            $response = json_decode( $json_response->getBody() );

            // If the request was properly executed
            if($curl_status == 200) {
                $user = $response->{'results'};

                // Set the locale based on user preferences
                $request->setLocale( $user->{'locale'} );
                $this->container->get('translator')->setLocale( $user->{'locale'} );

                // Set the locale in the session
                $session = $request->getSession();
                $session->set('_locale', $user->{'locale'});
            }
            else {
                $user = NULL;
                $errors_in_api_requests = TRUE;
            }

            if($user != NULL) {

                // Get the details of the comment
                // Set the endpoint
                switch ($asset_type) {
                    case 'video':
                        $endpoint = $strime_api_url."comment/".$comment."/get";
                        break;
                    case 'image':
                        $endpoint = $strime_api_url."image/comment/".$comment."/get";
                        break;

                    default:
                        $endpoint = $strime_api_url."comment/".$comment."/get";
                        break;
                }

                // Send the request
                $client = new \GuzzleHttp\Client();
                $json_response = $client->request('GET', $endpoint, [
                    'headers' => $headers,
                    'http_errors' => false
                ]);
                $curl_status = $json_response->getStatusCode();
                $response = json_decode( $json_response->getBody() );

                // If the request was properly executed
                if($curl_status == 200) {

                    $comment = $response->{'results'};

                    // Get the video details
                    // Set the endpoint
                    switch ($asset_type) {
                        case 'video':
                            $endpoint = $strime_api_url."video/".$comment->{'video'}->{'video_id'}."/get";
                            break;
                        case 'image':
                            $endpoint = $strime_api_url."image/".$comment->{'image'}->{'image_id'}."/get";
                            break;

                        default:
                            $endpoint = $strime_api_url."video/".$comment->{'video'}->{'video_id'}."/get";
                            break;
                    }

                    // Send the request
                    $client = new \GuzzleHttp\Client();
                    $json_response = $client->request('GET', $endpoint, [
                        'headers' => $headers,
                        'http_errors' => false
                    ]);
                    $curl_status = $json_response->getStatusCode();
                    $response = json_decode( $json_response->getBody() );

                    if($curl_status == 200) {

                        // Set a variable with the details of the video
                        $asset_details = $response->{'results'};

                        // Set the auto-login token
                        $auto_login_token = hash('md5', $user->{'user_id'}."-".$comment->{'comment_id'}."-".$this->container->getParameter('secret'));
                    }

                    else {
                        $asset_details = NULL;
                        $errors_in_api_requests = TRUE;
                    }
                }
                else {
                    $comment = NULL;
                    $errors_in_api_requests = TRUE;
                }

                // If we collected all the required information
                if(!$errors_in_api_requests && ($user != NULL) && ($asset_details != NULL) && ($comment != NULL)) {

                    // Set the time
                    $time_formatted = date("H:i:s");

                    // Check if it's an answer or not
                    if($comment->{'answer_to'} == NULL)
                        $answer_to_boolean = FALSE;
                    else
                        $answer_to_boolean = TRUE;

                    // Set additional params
                    switch ($asset_type) {
                        case 'video':
                            $mail_additional_params = array("%video%" => $asset_details->{'name'});
                            $view = 'emails/comment.html.twig';
                            break;
                        case 'image':
                            $mail_additional_params = array("%image%" => $asset_details->{'name'});
                            $view = 'emails/comment-image.html.twig';
                            break;

                        default:
                            $mail_additional_params = array("%video%" => $asset_details->{'name'});
                            $view = 'emails/comment.html.twig';
                            break;
                    }

                    // Send an email with the all the comments
                    $message = \Swift_Message::newInstance()
                        ->setSubject('Strime - '.$this->get('translator')->trans('back.controller_ajax.add_comment.a_comment_has_been_posted_on_'.$asset_type, $mail_additional_params, 'back_controller_ajax'))
                        ->setFrom(array('contact@strime.io' => 'Strime'))
                        ->setTo( array( $user->{'email'} => $user->{'first_name'}." ".$user->{'last_name'} ) )
                        ->setBody(
                            $this->renderView(
                                $view,
                                array(
                                    'current_year' => date('Y'),
                                    'user_id' => $user_id,
                                    'locale' => $user->{'locale'},
                                    'first_name' => $user->{'first_name'},
                                    'comment' => $comment,
                                    'comment_id' => $comment->{'comment_id'},
                                    'time' => $time_formatted,
                                    'answer_to' => $answer_to_boolean,
                                    'asset_type' => $asset_type,
                                    'asset' => $asset_details,
                                    'is_owner' => TRUE,
                                    'author' => $comment->{'author'}->{'name'},
                                    'auto_login_token' => $auto_login_token
                                )
                            ),
                            'text/html'
                        )
                    ;
                    $this->get('mailer')->send($message);
                    $transport = $this->container->get('mailer')->getTransport();
                    $spool = $transport->getSpool();
                    $spool->flushQueue($this->container->get('swiftmailer.transport.real'));

                    // Check if the owner of the video has activated Slack notifications
                    if(($user->{'user_slack_details'} != NULL) && ($user->{'user_slack_details'}->{'webhook_url'} != NULL)) {

                        // Prepare the comment to be displayed in Slack
                        // Send the notification
                        $webhook_url = $user->{'user_slack_details'}->{'webhook_url'};

                        $slack_webhook = new Webhook( $webhook_url );
                        $slack_webhook->setAttachments(
                            array(
                                array(
                                    "fallback" => $this->get('translator')->trans('back.controller_ajax.add_comment.a_comment_has_been_posted_on_'.$asset_type, $mail_additional_params, 'back_controller_ajax'),
                                    "text" => $comment,
                                    "color" => "#0CAC9A",
                                    "author" => array(
                                        "author_name" => $comment->{'author'}->{'name'}
                                    ),
                                    "title" => "[".$asset_type."] " . $asset_details->{'name'},
                                    "fields" => array(
                                        "title" => $asset_details->{'name'},
                                        "value" => $comment,
                                        "short" => FALSE
                                    ),
                                    "footer_icon" => "https://www.strime.io/bundles/strimeglobal/img/icon-strime.jpg",
                                    "ts" => time()
                                )
                            )
                        );
                        switch ($asset_type) {
                            case 'video':
                                $title_additional_params = array(
                                    "%video%" => $asset_details->{'name'},
                                    "%author_name%" => $comment->{'author'}->{'name'},
                                    "%at%" => $time_formatted
                                );
                                break;
                            case 'image':
                                $title_additional_params = array(
                                    "%image%" => $asset_details->{'name'},
                                    "%author_name%" => $comment->{'author'}->{'name'},
                                );
                                break;

                            default:
                                $title_additional_params = array(
                                    "%author_name%" => $comment->{'author'}->{'name'},
                                    "%at%" => $time_formatted
                                );
                                break;
                        }
                        $slack_parameters = array(
                            "message" => $this->get('translator')->trans('back.controller_ajax.add_comment.a_comment_has_been_posted_on_'.$asset_type.'_by', $title_additional_params, 'back_controller_ajax'),
                            "username" => "Strime",
                            "link_text" => $this->get('translator')->trans('back.controller_ajax.add_comment.see_the_'.$asset_type, array(), 'back_controller_ajax'),
                            "icon" => "https://www.strime.io/bundles/strimeglobal/img/icon-strime.jpg"
                        );
                        if($auto_login_token != NULL) {
                            $slack_additional_params = array(
                                'action' => 'user',
                                'url_data' => $auto_login_token
                            );
                        }
                        else {
                            $slack_additional_params = array();
                        }
                        switch ($asset_type) {
                            case 'video':
                                $slack_additional_params['video_id'] = $asset_details->{'asset_id'};
                                $slack_parameters["link"] = $this->generateUrl('app_video', $slack_additional_params, TRUE);
                                break;
                            case 'image':
                                $slack_additional_params['image_id'] = $asset_details->{'asset_id'};
                                $slack_parameters["link"] = $this->generateUrl('app_image', $slack_additional_params, TRUE);
                                break;

                            default:
                                $slack_additional_params['video_id'] = $asset_details->{'asset_id'};
                                $slack_parameters["link"] = $this->generateUrl('app_video', $slack_additional_params, TRUE);
                                break;
                        }
                        $slack_webhook->sendMessage($slack_parameters);
                    }
                }
            }

            if($errors_in_api_requests == FALSE) {
                $json["status"] = "success";
                $json["response_code"] = "200";
                return new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            }
            else {
                $json["status"] = "error";
                $json["response_code"] = "400";
                return new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            }
        }
        exit;
    }
}
