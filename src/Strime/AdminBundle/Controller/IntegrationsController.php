<?php

namespace Strime\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Strime\GlobalBundle\Payment\Payment;

class IntegrationsController extends Controller
{
    /**
     * @Route("/integrations", name="admin_integrations")
     * @Template("StrimeAdminBundle:Admin:integrations.html.twig")
     */
    public function integrationsAction(Request $request)
    {
        // Get the session data
    	$session = $request->getSession();
        $bag = $session->getBag("attributes");

        // Check if the user is logged in
        if(($bag->get('user_id') == NULL) || (strcmp($bag->get('role'), "admin") != 0))
            return $this->redirectToRoute('home');

        // Create the form object
        $form = $this->container->get('strime.helpers.form');
        $form->request = $request;

        // Create the login form
        $login_form = $form->createLoginForm();

        // Prepare messages for the login form
        $login = $form->prepareLoginMessages($login_form);

        // Create the forgotten password form
        $forgotten_password_form = $form->createForgottenPasswordForm();

        // Get the parameters
        $strime_api_url = $this->container->getParameter('strime_api_url');
        $strime_api_token = $this->container->getParameter('strime_api_token');

        // Set the headers
        $headers = array(
            'Accept' => 'application/json',
            'X-Auth-Token' => $strime_api_token,
            'Content-type' => 'application/json'
        );

        // Get the total number of users
        $nb_users = $this->getTotalNumberOfUsers($headers, $strime_api_url);

        // Get the total number of users using Google Signin
        $nb_users_google_signin = $this->getTotalNumberOfUsersUsingGoogleSignin($headers, $strime_api_url);

        // Get the total number of users using Facebook Signin
        $nb_users_facebook_signin = $this->getTotalNumberOfUsersUsingFacebookSignin($headers, $strime_api_url);

        // Get the total number of users using Slack
        $nb_users_slack = $this->getTotalNumberOfUsersUsingSlack($headers, $strime_api_url);

        // Define the ratio of users using Google Signin
        if($nb_users != 0)
            $google_signin_ratio = round($nb_users_google_signin / $nb_users, 2, PHP_ROUND_HALF_UP);
        else
            $google_signin_ratio = 0;

        // Define the ratio of users using Facebook Signin
        if($nb_users != 0)
            $facebook_signin_ratio = round($nb_users_facebook_signin / $nb_users, 2, PHP_ROUND_HALF_UP);
        else
            $facebook_signin_ratio = 0;

        // Define the ratio of users using Slack
        if($nb_users != 0)
            $slack_ratio = round($nb_users_slack / $nb_users, 2, PHP_ROUND_HALF_UP);
        else
            $slack_ratio = 0;

        return array(
            'body_classes' => 'dashboard',
            'login_form' => $login['login_form'],
            'login_message' => $login['login_message'],
            'forgotten_password_form' => $forgotten_password_form->createView(),
        	"nb_users" => $nb_users,
        	"nb_users_google_signin" => $nb_users_google_signin,
            "nb_users_facebook_signin" => $nb_users_facebook_signin,
        	"nb_users_slack" => $nb_users_slack,
        );
    }




    /***************************************************************/
    /******************     Private functions     ******************/
    /***************************************************************/


    /**
     * Private function which gets the total number of users
     *
     */

    private function getTotalNumberOfUsers($headers, $strime_api_url) {

        // Set the endpoint to get the number of users registered
        $endpoint = $strime_api_url."stats/users/number/get";

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $curl_status = $json_response->getStatusCode();
        $response = json_decode( $json_response->getBody() );

        // Set the variable
        $nb_users = 0;

        // If the request was properly executed
        if($curl_status == 200) {
            $nb_users = $response->{'results'};
        }

        return $nb_users;
    }


    /**
     * Private function which gets the total number of users using Google Signin
     *
     */

    private function getTotalNumberOfUsersUsingGoogleSignin($headers, $strime_api_url) {

        // Set the endpoint to get the number of users registered
        $endpoint = $strime_api_url."stats/users-google-signin/number/get";

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $curl_status = $json_response->getStatusCode();
        $response = json_decode( $json_response->getBody() );

        // Set the variable
        $nb_users_google_signin = 0;

        // If the request was properly executed
        if($curl_status == 200) {
            $nb_users_google_signin = $response->{'results'};
        }

        return $nb_users_google_signin;
    }


    /**
     * Private function which gets the total number of users using Facebook Signin
     *
     */

    private function getTotalNumberOfUsersUsingFacebookSignin($headers, $strime_api_url) {

        // Set the endpoint to get the number of users registered
        $endpoint = $strime_api_url."stats/users-facebook-signin/number/get";

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $curl_status = $json_response->getStatusCode();
        $response = json_decode( $json_response->getBody() );

        // Set the variable
        $nb_users_facebook_signin = 0;

        // If the request was properly executed
        if($curl_status == 200) {
            $nb_users_facebook_signin = $response->{'results'};
        }

        return $nb_users_facebook_signin;
    }


    /**
     * Private function which gets the total number of users using Slack
     *
     */

    private function getTotalNumberOfUsersUsingSlack($headers, $strime_api_url) {

        // Set the endpoint to get the number of users registered
        $endpoint = $strime_api_url."stats/users-slack/number/get";

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $curl_status = $json_response->getStatusCode();
        $response = json_decode( $json_response->getBody() );

        // Set the variable
        $nb_users_slack = 0;

        // If the request was properly executed
        if($curl_status == 200) {
            $nb_users_slack = $response->{'results'};
        }

        return $nb_users_slack;
    }

}
