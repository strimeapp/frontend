<?php

namespace Strime\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Strime\GlobalBundle\Payment\Payment;

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

class OffersController extends Controller
{
    /**
     * @Route("/offers/{action}/{offer_id}", defaults={"action": NULL, "offer_id": NULL}, name="admin_offers")
     * @Template("StrimeAdminBundle:Admin:offers.html.twig")
     */
    public function offersAction(Request $request, $action, $offer_id)
    {
        // Get the session data
        $session = $request->getSession();
        $bag = $session->getBag("attributes");

        // Get the user ID
        $user_id = $bag->get('user_id');
        $user_role = $bag->get('role');

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

        // Create the add tax rate form
        $add_offer_form = $this->createOfferForm($request);

        // Handle the request and set the result variable
        $add_offer_form->handleRequest($request);
        $add_offer_form_results = NULL;

        // Check if the form has been submitted
        if($add_offer_form->isSubmitted()) {

            // If the submitted form is valid
            if($add_offer_form->isValid()) {

                // Get the data
                $name = $add_offer_form->get('name')->getData();
                $price = $add_offer_form->get('price')->getData();
                $nb_videos = $add_offer_form->get('nb_videos')->getData();
                $storage_allowed = $add_offer_form->get('storage_allowed')->getData();

                if(!is_numeric($price) || !is_numeric($nb_videos) || !is_numeric($storage_allowed)) {

                    $this->addFlash(
                        'error',
                        'Le format des données n\'est pas bon.'
                    );
                }
                else {
                    $price = (int)$price;
                    $nb_videos = (int)$nb_videos;
                    $storage_allowed = (float)$storage_allowed;

                    // Update the user with the new offer
                    // Set the endpoint
                    $endpoint = $strime_api_url."offer/add";

                    // Prepare the parameters
                    $params = array(
                        'name' => $name,
                        'price' => $price,
                        'nb_videos' => $nb_videos,
                        'storage_allowed' => $storage_allowed,
                    );

                    // Send the request
                    $client = new \GuzzleHttp\Client();
                    $json_response = $client->request('POST', $endpoint, [
                        'headers' => $headers,
                        'http_errors' => false,
                        'json' => $params
                    ]);
                    $curl_status = $json_response->getStatusCode();
                    $response = json_decode( $json_response->getBody() );

                    // Return the result
                    if($curl_status == 201) {

                        // Prepare the flash message
                        $this->addFlash(
                            'success',
                            'L\'offre "'.$name.'" a bien été créée.'
                        );
                    }
                    else {

                        $this->addFlash(
                            'error',
                            'Une erreur est survenue lors de la création de cette offre.'
                        );
                    }
                }
            }
            else {

                $this->addFlash(
                    'error',
                    'Les données soumises ne sont pas valides.'
                );
            }
        }

        // If there is a request to delete an offer
        if(isset($action) && isset($offer_id) && (strcmp($action, "delete") == 0)) {

            // Send the request to delete the user
            // Set the endpoint
            $endpoint = $strime_api_url."offer/".$offer_id."/delete";

            // Send the request
            $client = new \GuzzleHttp\Client();
            $json_response = $client->request('DELETE', $endpoint, [
                'headers' => $headers,
                'http_errors' => false
            ]);
            $curl_status = $json_response->getStatusCode();
            $response = json_decode( $json_response->getBody() );

            // If the request was properly executed
            if($curl_status == 204) {

                $this->addFlash(
                    'success',
                    'Cette offre a bien été supprimée.'
                );
            }
            elseif($curl_status == 400) {

                $this->addFlash(
                    'error',
                    'Cette offre n\'existe pas. Elle ne peut donc être supprimée.'
                );
            }
            else {

                $this->addFlash(
                    'error',
                    'Une erreur est survenue lors de la suppression de cette offre.'
                );
            }

            // Redirect to the route without parameters
            return $this->redirectToRoute('admin_offers');
        }

        // Get the list of offers
        $offers = $this->getOffersList($headers, $strime_api_url);

        // Get the number of users per offer
        $nb_users_per_offer = $this->getNumberOfUsersPerOffer($headers, $strime_api_url);

        // Set the number of users per offer in the offers variable.
        if($offers != NULL) {
            foreach ($offers as $offer) {

                $offer->{'nb_users'} = 0;

                foreach ($nb_users_per_offer as $stat) {
                    if(strcmp($offer->{'name'}, $stat['offer']) == 0) {
                        $offer->{'nb_users'} = $stat['nb_users'];
                    }
                }
            }
        }

        return array(
            'body_classes' => 'users',
            'login_form' => $login['login_form'],
            'login_message' => $login['login_message'],
            'forgotten_password_form' => $forgotten_password_form->createView(),
            'add_offer_form' => $add_offer_form->createView(),
            "offers" => $offers
        );
    }




    /***************************************************************/
    /******************     Private functions     ******************/
    /***************************************************************/


    /**
     * Private function which gets the number of users per offer
     *
     */

    private function getNumberOfUsersPerOffer($headers, $strime_api_url) {

        // Set the endpoint to get the number of users registered
        $endpoint = $strime_api_url."stats/users/per-offer/get";

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
     * Private function which gets the list of offers
     *
     */

    private function getOffersList($headers, $strime_api_url) {

        // Set the endpoint to get the number of contacts registered
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
     * Private function which creates the form to add an offer
     *
     */

    private function createOfferForm($request) {

        // Set the login form
        $add_offer_form = $this->createFormBuilder()
            ->add('name', TextType::class, array('label' => '', 'attr' => array('placeholder' => 'Nom de l\'offre')))
            ->add('price', NumberType::class, array('label' => '', 'attr' => array('placeholder' => 'Prix')))
            ->add('nb_videos', NumberType::class, array('label' => '', 'attr' => array('placeholder' => 'Nombre de vidéos')))
            ->add('storage_allowed', NumberType::class, array('label' => '', 'attr' => array('placeholder' => 'Espace (en Go)')))
            ->add('submit', SubmitType::class, array(
                    'attr' => array('class' => '')
            ))
            ->getForm();

        return $add_offer_form;
    }

}
