<?php

namespace Strime\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class VideosController extends Controller
{
    /**
     * @Route("/videos/{action}/{video_id}", defaults={"action": NULL, "video_id": NULL}, name="admin_videos")
     * @Template("StrimeAdminBundle:Admin:videos.html.twig")
     */
    public function videosAction(Request $request, $action, $video_id)
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


        // If there is a request to delete a video
        if(isset($action) && isset($video_id) && (strcmp($action, "delete") == 0)) {

            // Set the endpoint to delete the user
            $endpoint = $strime_api_url."video/".$video_id."/delete";

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
                    'Cette vidéo a bien été supprimée, ainsi que tous ses commentaires.'
                );
            }
            elseif($curl_status == 400) {

                $this->addFlash(
                    'error',
                    'Cette vidéo n\'existe pas. Elle ne peut donc être supprimée.'
                );
            }
            else {

                $this->addFlash(
                    'error',
                    'Une erreur est survenue lors de la suppression de cette vidéo.'
                );
            }

            // Redirect to the route without parameters
            return $this->redirectToRoute('admin_videos');
        }

        // Get the last 10 users
        // $last_users = $this->getLastUsers($headers, $strime_api_url, 10);

        // Get all the videos
        $videos = $this->getVideos($headers, $strime_api_url);
        $videos_list = array();

        if($videos != NULL) {

            foreach ($videos as $video) {

                $default_names_list = explode(",", strtolower( $this->container->getParameter('strime_api_default_video_names') ));

                // Check if the video is a default video
                if(!in_array(strtolower( $video->{'name'} ), $default_names_list)) {

                    // Extract the original extension of the video
                    $video->{'extension'} = pathinfo( $video->{'file'}, PATHINFO_EXTENSION );

                    // Get the size in Mo
                    $video->{'size'} = (int)round($video->{'size'} / $this->container->getParameter('strime_api_storage_multiplier'));

                    // Add the video to the list
                    $videos_list[] = $video;
                }
            }
        }

        return array(
            'body_classes' => 'videos',
            'login_form' => $login['login_form'],
            'login_message' => $login['login_message'],
            'forgotten_password_form' => $forgotten_password_form->createView(),
            "videos_list" => $videos_list,
        );
    }




    /***************************************************************/
    /******************     Private functions     ******************/
    /***************************************************************/


    /**
     * Private function which gets the whole list of videos
     *
     */

    private function getVideos($headers, $strime_api_url) {

        // Get the data
        // Set the endpoint
        $endpoint = $strime_api_url."videos/get";

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $curl_status = $json_response->getStatusCode();
        $response = json_decode( $json_response->getBody() );

        // Set the variable
        $videos = NULL;

        // If the request was properly executed
        if($curl_status == 200) {
            $videos = $response->{'results'};
        }

        return $videos;
    }

}
