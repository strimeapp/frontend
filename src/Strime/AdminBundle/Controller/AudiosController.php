<?php

namespace Strime\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class AudiosController extends Controller
{
    /**
     * @Route("/audios/{action}/{audio_id}", defaults={"action": NULL, "audio_id": NULL}, name="admin_audios")
     * @Template("StrimeAdminBundle:Admin:audios.html.twig")
     */
    public function audiosAction(Request $request, $action, $audio_id)
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


        // If there is a request to delete an audio file
        if(isset($action) && isset($audio_id) && (strcmp($action, "delete") == 0)) {

            // Set the endpoint to delete the user
            $endpoint = $strime_api_url."audio/".$audio_id."/delete";

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
                    'Ce fichier audio a bien été supprimé, ainsi que tous ses commentaires.'
                );
            }
            elseif($curl_status == 400) {

                $this->addFlash(
                    'error',
                    'Ce fichier audio n\'existe pas. Il ne peut donc être supprimé.'
                );
            }
            else {

                $this->addFlash(
                    'error',
                    'Une erreur est survenue lors de la suppression de ce fichier audio.'
                );
            }

            // Redirect to the route without parameters
            return $this->redirectToRoute('admin_audios');
        }

        // Get all the audio files
        $audios = $this->getAudios($headers, $strime_api_url);
        $audios_list = array();

        if($audios != NULL) {

            foreach ($audios as $audio) {

                // Extract the original extension of the audio file
                $audio->{'extension'} = pathinfo( $audio->{'file'}, PATHINFO_EXTENSION );

                // Get the size in Mo
                $audio->{'size'} = (int)round($audio->{'size'} / $this->container->getParameter('strime_api_storage_multiplier'));

                // Add the audio file to the list
                $audios_list[] = $audio;
            }
        }

        return array(
            'body_classes' => 'audios',
            'login_form' => $login['login_form'],
            'login_message' => $login['login_message'],
            'forgotten_password_form' => $forgotten_password_form->createView(),
            "audios_list" => $audios_list,
        );
    }




    /***************************************************************/
    /******************     Private functions     ******************/
    /***************************************************************/


    /**
     * Private function which gets the whole list of audio files
     *
     */

    private function getAudios($headers, $strime_api_url) {

        // Get the data
        // Set the endpoint
        $endpoint = $strime_api_url."audios/get";

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $curl_status = $json_response->getStatusCode();
        $response = json_decode( $json_response->getBody() );

        // Set the variable
        $audios = NULL;

        // If the request was properly executed
        if($curl_status == 200) {
            $audios = $response->{'results'};
        }

        return $audios;
    }

}
