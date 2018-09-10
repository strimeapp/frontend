<?php

namespace Strime\BackBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

use Strime\GlobalBundle\Entity\EmailConfirmationToken;
use Strime\GlobalBundle\Entity\NewFeature;
use Strime\GlobalBundle\Entity\HasReadNewFeature;



class AjaxUserController extends Controller
{
    /**
     * @Route("/ajax/user/revoke-google", name="app_ajax_user_revoke_google")
     * @Template()
     */
    public function ajaxUserRevokeGoogleAction(Request $request)
    {
        // Get the session data
        $session = $request->getSession();
        $bag = $session->getBag("attributes");

        // Get the user ID
        $user_id = $bag->get('user_id');

        // Set the API parameters
        $strime_api_url = $this->container->getParameter('strime_api_url');
        $strime_api_token = $this->container->getParameter('strime_api_token');

        // Set the headers
        $headers = array(
            'Accept' => 'application/json',
            'X-Auth-Token' => $strime_api_token,
            'Content-type' => 'application/json'
        );

        // Set the endpoint
        $endpoint = $strime_api_url."user/".$user_id."/revoke-google";

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $curl_status = $json_response->getStatusCode();
        $json_response = $json_response->getBody();

        // Return the JSON response
        echo json_encode(
            array(
                "response_code" => $curl_status
            )
        );
        die;
    }



    /**
     * @Route("/ajax/user/revoke-facebook", name="app_ajax_user_revoke_facebook")
     * @Template()
     */
    public function ajaxUserRevokeFacebookAction(Request $request)
    {
        // Get the session data
        $session = $request->getSession();
        $bag = $session->getBag("attributes");

        // Get the user ID
        $user_id = $bag->get('user_id');

        // Set the API parameters
        $strime_api_url = $this->container->getParameter('strime_api_url');
        $strime_api_token = $this->container->getParameter('strime_api_token');

        // Set the headers
        $headers = array(
            'Accept' => 'application/json',
            'X-Auth-Token' => $strime_api_token,
            'Content-type' => 'application/json'
        );

        // Set the endpoint
        $endpoint = $strime_api_url."user/".$user_id."/revoke-facebook";

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $curl_status = $json_response->getStatusCode();
        $json_response = $json_response->getBody();

        // Return the JSON response
        echo json_encode(
            array(
                "response_code" => $curl_status
            )
        );
        die;
    }



    /**
     * @Route("/ajax/user/revoke-youtube", name="app_ajax_user_revoke_youtube")
     * @Template()
     */
    public function ajaxUserRevokeYoutubeAction(Request $request)
    {
        // Get the session data
        $session = $request->getSession();
        $bag = $session->getBag("attributes");

        // Get the user ID
        $user_id = $bag->get('user_id');

        // Revoke Youtube token
        $OAUTH2_CLIENT_ID = $this->container->getParameter('strime_google_api_console_id');
        $OAUTH2_CLIENT_SECRET = $this->container->getParameter('strime_google_api_console_secret');

        // Create the Google Client object
        $google_client = new \Google_Client();
        $google_client->setClientId($OAUTH2_CLIENT_ID);
        $google_client->setClientSecret($OAUTH2_CLIENT_SECRET);
        $google_client->setScopes('https://www.googleapis.com/auth/youtube');
        $google_client->revokeToken( $bag->get('youtube_token') );

        // Update the  session
        $session->remove('youtube_token');
        $session->save();

        // Set the API parameters
        $strime_api_url = $this->container->getParameter('strime_api_url');
        $strime_api_token = $this->container->getParameter('strime_api_token');

        // Set the headers
        $headers = array(
            'Accept' => 'application/json',
            'X-Auth-Token' => $strime_api_token,
            'Content-type' => 'application/json'
        );

        // Set the endpoint
        $endpoint = $strime_api_url."user/".$user_id."/revoke-youtube";

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $curl_status = $json_response->getStatusCode();
        $json_response = $json_response->getBody();

        // Return the JSON response
        echo json_encode(
            array(
                "response_code" => $curl_status
            )
        );
        die;
    }



    /**
     * @Route("/ajax/email-confirmation-message/resend", name="app_ajax_resend_email_confirmation_message")
     * @Template()
     */
    public function ajaxResendEmailConfirmationMessageAction(Request $request)
    {
        // Get the session data
        $session = $request->getSession();
        $bag = $session->getBag("attributes");
        $user_id = $bag->get('user_id');
        $email = $bag->get('email');
        $first_name = $bag->get('first_name');
        $last_name = $bag->get('last_name');

        if($user_id != NULL) {

            // Get the entity manager
            $em = $this->getDoctrine()->getManager();
            $email_confirmation_token = $em->getRepository('StrimeGlobalBundle:EmailConfirmationToken')->findOneBy(array('user_id' => $user_id));

            if($email_confirmation_token != NULL) {
                // Send an email with the link to confirm the email address
                $message = \Swift_Message::newInstance()
                    ->setSubject('Strime - ' . $this->get('translator')->trans('back.controller_ajax_user.resend_email_confirmation.confirm_email_address', array(), 'back_controller_ajax_user'))
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
                                'email_confirmation_token' => $email_confirmation_token->getToken(),
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

                echo json_encode(array("status" => "success"));
                die;
            }
            else {

                // There is no token for this user
                // Generate an error
                echo json_encode(array("status" => "error", "error_source" => "no_token"));
                die;
            }
        }
        else {

            // The user is probably not connected or disconnected.
            // Generate an error
            echo json_encode(array("status" => "error", "error_source" => "not_logged_in"));
            die;
        }
    }



    /**
     * @Route("/ajax/new-feature-disclaimer/mark-as-read", name="app_ajax_mark_new_feature_disclaimer_as_read")
     * @Template()
     */
    public function ajaxMarkNewFeatureDisclaimerAsReadAction(Request $request)
    {
        // Get the session data
        $session = $request->getSession();
        $bag = $session->getBag("attributes");
        $user_id = $bag->get('user_id');

        if($user_id != NULL) {

            // Get the entity manager
            $em = $this->getDoctrine()->getManager();

            // Find the new feature entity
            $feature_id = $request->request->get('feature_id', NULL);
            $new_feature = $em->getRepository('StrimeGlobalBundle:NewFeature')->findOneBy(array('secret_id' => $feature_id));

            // If the feature has been found, update the has_read table
            if($new_feature != NULL) {

                // Save in the database the fact that this user has read the announcement
                $has_read_new_feature = new HasReadNewFeature;
                $has_read_new_feature->setUserID( $user_id );
                $has_read_new_feature->setNewFeature( $new_feature );

                $em->persist( $has_read_new_feature );
                $em->flush();

                echo json_encode(array("status" => "success"));
                die;
            }

            // If the feature has not been found, return an error message
            else {
                echo json_encode(array(
                    "status" => "error",
                    "error_message" => $this->get('translator')->trans('back.controller_ajax_user.mark_new_feature.feature_not_found', array(), 'back_controller_ajax_user'),
                    "feature_id" => $feature_id
                ));
                die;
            }
        }
        else {

            // The user is probably not connected or disconnected.
            // Generate an error
            echo json_encode(array(
                "status" => "error",
                "error_message" => $this->get('translator')->trans('back.controller_ajax_user.mark_new_feature.user_not_found', array(), 'back_controller_ajax_user')
            ));
            die;
        }
    }



    /**
     * @Route("/ajax/is-logged-in", name="app_ajax_is_logged_in")
     * @Template()
     */
    public function ajaxIsLoggedIn(Request $request) {

        // Get the session data
        $session = $request->getSession();
        $bag = $session->getBag("attributes");

        // Get the user ID
        $user_id = $bag->get('user_id');
        $user_role = $bag->get('role');

        // Check if the user is logged in
        // If the user is not logged in
        if($user_id == NULL) {

            // Generate an new CSRF token
            $csrf = $this->get('security.csrf.token_manager');
            // The intention must be the name of the form
            $intention = "form";
            $token = $csrf->getToken($intention);

            echo json_encode(array("logged_in" => FALSE, "token" => $token->getValue()));
        }
        else {
            echo json_encode(array("logged_in" => TRUE));
        }

        die;
    }
}
