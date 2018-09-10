<?php

namespace Strime\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class ImagesController extends Controller
{
    /**
     * @Route("/images/{action}/{image_id}", defaults={"action": NULL, "image_id": NULL}, name="admin_images")
     * @Template("StrimeAdminBundle:Admin:images.html.twig")
     */
    public function imagesAction(Request $request, $action, $image_id)
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


        // If there is a request to delete an image
        if(isset($action) && isset($image_id) && (strcmp($action, "delete") == 0)) {

            // Set the endpoint to delete the user
            $endpoint = $strime_api_url."image/".$image_id."/delete";

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

                $this->addFlash(
                    'success',
                    'Cette image a bien été supprimée, ainsi que tous ses commentaires.'
                );
            }
            elseif($curl_status == 400) {

                $this->addFlash(
                    'error',
                    'Cette image n\'existe pas. Elle ne peut donc être supprimée.'
                );
            }
            else {

                $this->addFlash(
                    'error',
                    'Une erreur est survenue lors de la suppression de cette image.'
                );
            }

            // Redirect to the route without parameters
            return $this->redirectToRoute('admin_images');
        }

        // Get all the images
        $images = $this->getImages($headers, $strime_api_url);
        $images_list = array();

        if($images != NULL) {

            foreach ($images as $image) {

                // Extract the original extension of the image
                $image->{'extension'} = pathinfo( $image->{'file'}, PATHINFO_EXTENSION );

                // Get the size in Mo
                $image->{'size'} = (int)round($image->{'size'} / $this->container->getParameter('strime_api_storage_multiplier'));

                // Add the image to the list
                $images_list[] = $image;
            }
        }

        return array(
            'body_classes' => 'images',
            'login_form' => $login['login_form'],
            'login_message' => $login['login_message'],
            'forgotten_password_form' => $forgotten_password_form->createView(),
            "images_list" => $images_list,
        );
    }




    /***************************************************************/
    /******************     Private functions     ******************/
    /***************************************************************/


    /**
     * Private function which gets the whole list of images
     *
     */

    private function getImages($headers, $strime_api_url) {

        // Get the data
        // Set the endpoint
        $endpoint = $strime_api_url."images/get";

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $curl_status = $json_response->getStatusCode();
        $response = json_decode( $json_response->getBody() );

        // Set the variable
        $images = NULL;

        // If the request was properly executed
        if($curl_status == 200) {
            $images = $response->{'results'};
        }

        return $images;
    }

}
