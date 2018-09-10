<?php

namespace Strime\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Strime\GlobalBundle\Payment\Payment;

use Strime\GlobalBundle\Entity\EmailConfirmationToken;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class UsersController extends Controller
{
    /**
     * @Route("/users/map", name="admin_users_map")
     * @Template("StrimeAdminBundle:Admin:users-map.html.twig")
     */
    public function usersMapAction(Request $request)
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

        // Get the list of addresses
        $addresses = $this->getAddressesList($headers, $strime_api_url);

        return array(
            'body_classes' => 'dashboard',
            'login_form' => $login['login_form'],
            'login_message' => $login['login_message'],
            'forgotten_password_form' => $forgotten_password_form->createView(),
            "addresses" => $addresses,
        );
    }



    /**
     * @Route("/users/{action}/{client_id}", defaults={"action": NULL, "client_id": NULL}, name="admin_users")
     * @Template("StrimeAdminBundle:Admin:users.html.twig")
     */
    public function usersAction(Request $request, $action, $client_id)
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


        // If there is a request to delete an user
        if(isset($action) && isset($client_id) && (strcmp($action, "delete") == 0)) {

            // Set the endpoint to get the details of the user
            $endpoint = $strime_api_url."user/".$client_id."/get";

            // Send the request
            $client = new \GuzzleHttp\Client();
            $json_response = $client->request('GET', $endpoint, [
                'headers' => $headers,
                'http_errors' => false
            ]);
            $curl_status = $json_response->getStatusCode();
            $user_details = json_decode( $json_response->getBody() );

            // Set the endpoint to delete the user
            $endpoint = $strime_api_url."user/".$client_id."/delete";

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
                if($user_details->{'results'}->{'stripe_sub_id'} != NULL) {
                    $stripe_secret_key = $this->container->getParameter('stripe_secret_key');
                    \Stripe\Stripe::setApiKey( $stripe_secret_key );

                    // Set the headers
                    $headers = array(
                        'Content-type' => 'application/json'
                    );

                    // Create the Payment object
                    $payment = new Payment;
                    $payment->stripe_secret_key = $stripe_secret_key;
                    $payment->stripe_customer_id = $user_details->{'results'}->{'stripe_id'};
                    $payment->stripe_subscription_id = $user_details->{'results'}->{'stripe_sub_id'};
                    $payment_result = $payment->cancelSubscription();
                }

                // Get the entity manager
                $em = $this->getDoctrine()->getManager();

                // Delete any token to confirm the email address
                $email_confirmation_tokens = $em->getRepository('StrimeGlobalBundle:EmailConfirmationToken')->findAll(array('user_id' => $client_id));

                foreach ($email_confirmation_tokens as $email_confirmation_token) {

                    $em->remove( $email_confirmation_token );
                    $em->flush();
                }

                $this->addFlash(
                    'success',
                    'Cet utilisateur a bien été supprimé, ainsi que tous ses contenus.'
                );
            }
            elseif($curl_status == 400) {

                $this->addFlash(
                    'error',
                    'Cet utilisateur n\'existe pas. Il ne peut donc être supprimé.'
                );
            }
            else {

                $this->addFlash(
                    'error',
                    'Une erreur est survenue lors de la suppression de cet utilisateur.'
                );
            }

            // Redirect to the route without parameters
            return $this->redirectToRoute('admin_users');
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

        // Get the last 10 users
        // $last_users = $this->getLastUsers($headers, $strime_api_url, 10);

        // Get all the users
        $users_list = $this->getUsers($headers, $strime_api_url);

        // Set the avatar image for these users
        if($users_list != NULL) {
            foreach ($users_list as $user) {
                if($user->{'avatar'} == NULL)
                    $user->{'avatar'} = $this->setGravatar( $user->{'email'}, 30 );
            }
        }

        $users_list = array_reverse($users_list);

        return array(
            'body_classes' => 'users',
            'login_form' => $login['login_form'],
            'login_message' => $login['login_message'],
            'forgotten_password_form' => $forgotten_password_form->createView(),
            "users_list" => $users_list,
        );
    }



    /**
     * @Route("/user/{client_id}/{action}/{parameter}", defaults={"client_id": NULL, "action": NULL, "parameter": NULL}, name="admin_user")
     * @Template("StrimeAdminBundle:Admin:user.html.twig")
     */
    public function userAction(Request $request, $client_id, $action, $parameter)
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

        // Get the entity manager
        $em = $this->getDoctrine()->getManager();


        // If we get a request to change the user offer
        if(($action != NULL) && ($parameter != NULL) && (strcmp($action, "change-offer") == 0)) {

            // Update the user with the new offer
            // Set the endpoint
            $endpoint = $strime_api_url . "user/".$client_id."/edit";

            // Prepare the parameters
            $params = array(
                'offer' => $parameter,
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
            if($curl_status == 200) {

                // Prepare the flash message
                $this->addFlash(
                    'success',
                    'L\'offre de cet utilisateur a bien été changée.'
                );
            }
            else {
                $this->addFlash(
                    'error',
                    'Une erreur est survenue lors de la mise à jour de l\'offre de cet utilisateur.'
                );
            }
        }


        // Get the user details
        $user = $this->getUserDetails($headers, $strime_api_url, $client_id);
        if($user == NULL) {
            return $this->redirectToRoute('admin_users');
        }
        else {
            if($user->{'avatar'} == NULL)
                $user->{'avatar'} = $this->setGravatar( $user->{'email'}, 60 );

            // Calculate the storage used in percent
            $user->{'storage_used'} = round( (int)$user->{'storage_used'} / $this->container->getParameter('strime_api_storage_multiplier'), 2 );
            $user->{'storage_allowed'} = $user->{'offer'}->{'storage_allowed'} * $this->container->getParameter('strime_api_storage_allowed_multiplier'); // To get storage in Mo.
            $user->{'storage_used_in_percent'} = (int)round( ( (int)$user->{'storage_used'} / $user->{'storage_allowed'} ) * 100 );
        }

        if($user->{'stripe_id'} != NULL) {
            $payment = new Payment;
            $payment->stripe_secret_key = $this->container->getParameter('stripe_secret_key');
            $payment->stripe_customer_id = $user->{'stripe_id'};
            $user->{'credit_card_last_digits'} = $payment->getLastDigits();
            $user->{'credit_card_brand'} = $payment->getCardBrand();
        }
        else {
            $user->{'credit_card_last_digits'} = NULL;
            $user->{'credit_card_brand'} = NULL;
        }

        // Create the users helper
        $users_helper = $this->container->get('strime.back_helpers.users');

        // Get the list of videos of the user
        $videos = $users_helper->getUsersVideos($client_id);

        // Get the user's images
        $images = $users_helper->getUsersImages($client_id);

        // Merge the videos and images
        if(is_array($videos) && is_array($images)) {
            $assets_all = array_merge($videos, $images);
            $assets_all = array_reverse($assets_all);
            usort($assets_all, function($a, $b) {
                // return strtotime($a['start_date']) - strtotime($b['start_date']);
                return strcmp($b->{'created_at'}->{'date'}, $a->{'created_at'}->{'date'});
            });
        }
        elseif(is_array($videos)) {
            $assets_all = $videos;
        }
        elseif(is_array($images)) {
            $assets_all = $images;
        }

        // Get the list of invoices of the user
        $invoices = $this->getUserInvoices($headers, $strime_api_url, $client_id);

        // Get the list of offers
        $offers = $this->getOffersList($headers, $strime_api_url);

        return array(
            'body_classes' => 'user',
            'login_form' => $login['login_form'],
            'login_message' => $login['login_message'],
            'forgotten_password_form' => $forgotten_password_form->createView(),
            "user" => $user,
            "assets" => $assets_all,
            "invoices" => $invoices,
            "offers" => $offers
        );
    }




    /***************************************************************/
    /******************     Private functions     ******************/
    /***************************************************************/


    /**
     * Private function which gets the list of clients addresses
     *
     */

    private function getAddressesList($headers, $strime_api_url) {

        // Get the data
        // Set the endpoint
        $endpoint = $strime_api_url."stats/addresses/list/get";

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $curl_status = $json_response->getStatusCode();
        $response = json_decode( $json_response->getBody() );

        if(isset($response->{'results'}))
            return $response->{'results'};
        else
            return array();
    }


    /**
     * Private function which gets the number of users per offer
     *
     */

    private function getNumberOfUsersPerOffer($headers, $strime_api_url) {

        // Get the data
        // Set the endpoint
        $endpoint = "stats/users/per-offer/get";

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $curl_status = $json_response->getStatusCode();
        $response = json_decode( $json_response->getBody() );

        // Set the variable
        $nb_users_per_offer = array();

        // If the request was properly executed
        if($curl_status == 200 && ($response->{'results'} != NULL)) {

            $results = json_decode( $response->{'results'} );

            foreach ($results as $result) {

                $nb_users_per_offer[] = array("offer" => $result->{'offer_name'}, "nb_users" => $result->{'nb_users'});
            }
        }

        return $nb_users_per_offer;
    }


    /**
     * Private function which gets the last n users registered
     *
     */

    private function getLastUsers($headers, $strime_api_url, $nb_users) {

        // Get the data
        // Set the endpoint
        $endpoint = $strime_api_url."users/get/last/".$nb_users;

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $curl_status = $json_response->getStatusCode();
        $response = json_decode( $json_response->getBody() );

        // Set the variable
        $users = NULL;

        // If the request was properly executed
        if($curl_status == 200) {
            $users = $response->{'results'};
        }

        return $users;
    }


    /**
     * Private function which gets the last n users registered
     *
     */

    private function getUsers($headers, $strime_api_url) {

        // Get the data
        // Set the endpoint
        $endpoint = $strime_api_url."users/get";

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $curl_status = $json_response->getStatusCode();
        $response = json_decode( $json_response->getBody() );

        // Set the variable
        $users = NULL;

        // If the request was properly executed
        if($curl_status == 200) {
            $users = $response->{'results'};
        }

        return $users;
    }


    /**
     * Private function which gets the list of offers
     *
     */

    private function getOffersList($headers, $strime_api_url) {

        // Get the data
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

        // Set the variable
        $offers = NULL;

        // If the request was properly executed
        if($curl_status == 200) {
            $offers = $response->{'results'};
        }

        return $offers;
    }


    /**
     * Private function which sets the gravatar image.
     *
     */

    private function setGravatar($email, $size) {

        // Set the parameterss
        $default = "https://www.strime.io/bundles/strimeback/img/player/icon-avatar-40px.png";

        // Define the URL
        $grav_url = "https://www.gravatar.com/avatar/" . md5( strtolower( trim( $email ) ) ) . "?d=" . urlencode( $default ) . "&s=" . $size;

        return $grav_url;
    }


    /**
     * Private function which gets the last n users registered
     *
     */

    private function getUserDetails($headers, $strime_api_url, $user_id) {

        // Get the data
        // Set the endpoint
        $endpoint = $strime_api_url."user/".$user_id."/get";

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $user_curl_status = $json_response->getStatusCode();
        $user_json_response = json_decode( $json_response->getBody() );

        $user = NULL;

        // Add user data to the session
        if($user_curl_status == 200) {

            // Decode the user response
            $user = $user_json_response->{'results'};
        }

        return $user;
    }


    /**
     * Private function which gets the invoices of a specific user
     *
     */

    private function getUserInvoices($headers, $strime_api_url, $user_id) {

        // Get the data
        // Set the endpoint
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

        // Add user data to the session
        if($invoices_curl_status == 200) {

            // Decode the user response
            $invoices = $invoices_json_response->{'results'};
        }

        return $invoices;
    }


    /**
     * Private function which creates the search user form
     *
     */

    private function createSearchUserForm($request) {

        // Check if there is a session
        $session = $request->getSession();

        // Set the login form
        $login_form = $this->createFormBuilder()
            ->add('search', TextType::class, array('label' => '', 'attr' => array('placeholder' => 'Email, prénom ou nom')))
            ->add('submit', SubmitType::class, array(
                    'attr' => array('class' => '')
            ))
            ->getForm();

        return $login_form;
    }

}
