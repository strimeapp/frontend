<?php

namespace Strime\BackBundle\Helpers;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Session\Session;

use Strime\GlobalBundle\Assets\Avatar;

class Users {

    private $container;
    public $request;

    public function __construct(Container $container) {

        $this->container = $container;
    }


    /**
     * Function which gets the details of a user
     *
     */

    public function getUserDetails($user_id) {

        // Get the parameters
        $strime_api_url = $this->container->getParameter('strime_api_url');
        $strime_api_token = $this->container->getParameter('strime_api_token');

        // Set the headers
        $headers = array(
            'Accept' => 'application/json',
            'X-Auth-Token' => $strime_api_token,
            'Content-type' => 'application/json'
        );

        // Set the endpoint to get the videos of this user
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
            $user_details = $response->{'results'};
        }
        else {
            $user_details = NULL;
        }

        return $user_details;
    }


    /**
     * Function which gets the videos of a user
     *
     */

    public function getUsersVideos($user_id) {

        // Get the parameters
        $strime_api_url = $this->container->getParameter('strime_api_url');
        $strime_api_token = $this->container->getParameter('strime_api_token');

        // Set the headers
        $headers = array(
            'Accept' => 'application/json',
            'X-Auth-Token' => $strime_api_token,
            'Content-type' => 'application/json'
        );

        // Set the endpoint to get the videos of this user
        $endpoint = $strime_api_url."videos/user/".$user_id."/get";

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
            $videos_list = $response->{'results'};

            // Group videos by project
            $videos = array();
            $projects = array();
            $count_videos = 0;

            if(is_array($videos_list) && (count($videos_list) > 0)) {
                foreach ($videos_list as $video) {
                    if($video->{'project'} == NULL) {
                        $videos[$count_videos] = $video;
                    }
                    else {
                        if(!in_array($video->{'project'}->{'project_id'}, $projects)) {
                            $videos[ $count_videos ] = $video;
                            $projects[] = $video->{'project'}->{'project_id'};
                        }
                    }
                    $count_videos++;
                }
            }
            else {
                $videos = NULL;
            }
        }
        else {
            $videos = NULL;
        }

        return $videos;
    }


    /**
     * Function which gets the images of a user
     *
     */

    public function getUsersImages($user_id) {

        // Get the parameters
        $strime_api_url = $this->container->getParameter('strime_api_url');
        $strime_api_token = $this->container->getParameter('strime_api_token');

        // Set the headers
        $headers = array(
            'Accept' => 'application/json',
            'X-Auth-Token' => $strime_api_token,
            'Content-type' => 'application/json'
        );

        // Set the endpoint to get the images of this user
        $endpoint = $strime_api_url."images/user/".$user_id."/get";

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
            $images_list = $response->{'results'};

            // Group videos by project
            $images = array();
            $projects = array();
            $count_images = 0;

            if(is_array($images_list) && (count($images_list) > 0)) {
                foreach ($images_list as $image) {
                    if($image->{'project'} == NULL) {
                        $images[$count_images] = $image;
                    }
                    else {
                        if(!in_array($image->{'project'}->{'project_id'}, $projects)) {
                            $images[ $count_images ] = $image;
                            $projects[] = $image->{'project'}->{'project_id'};
                        }
                    }
                    $count_images++;
                }
            }
            else {
                $images = NULL;
            }
        }
        else {
            $images = NULL;
        }

        return $images;
    }


    /**
     * Function which gets the audio files of a user
     *
     */

    public function getUsersAudios($user_id) {

        // Get the parameters
        $strime_api_url = $this->container->getParameter('strime_api_url');
        $strime_api_token = $this->container->getParameter('strime_api_token');

        // Set the headers
        $headers = array(
            'Accept' => 'application/json',
            'X-Auth-Token' => $strime_api_token,
            'Content-type' => 'application/json'
        );

        // Set the endpoint to get the audio files of this user
        $endpoint = $strime_api_url."audios/user/".$user_id."/get";

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
            $audios_list = $response->{'results'};

            // Group audio files by project
            $audios = array();
            $projects = array();
            $count_audios = 0;

            if(is_array($audios_list) && (count($audios_list) > 0)) {
                foreach ($audios_list as $audio) {
                    if($audio->{'project'} == NULL) {
                        $audios[$count_audios] = $audio;
                    }
                    else {
                        if(!in_array($audio->{'project'}->{'project_id'}, $projects)) {
                            $audios[ $count_audios ] = $audio;
                            $projects[] = $audio->{'project'}->{'project_id'};
                        }
                    }
                    $count_audios++;
                }
            }
            else {
                $audios = NULL;
            }
        }
        else {
            $audios = NULL;
        }

        return $audios;
    }



    /**
     * Function which gets the contacts of a user
     *
     */

    public function getUsersContacts($user_id) {

        // Get the API parameters
        $strime_api_url = $this->container->getParameter('strime_api_url');
        $strime_api_token = $this->container->getParameter('strime_api_token');

        // Set the headers
        $headers = array(
            'Accept' => 'application/json',
            'X-Auth-Token' => $strime_api_token,
            'Content-type' => 'application/json'
        );

        // Set the endpoint
        $endpoint = $strime_api_url."contacts/".$user_id."/get";

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
            $contacts_list = $response->{'results'};

            // If the contacts list is not an array of contacts
            if(!is_array($contacts_list) || (count($contacts_list) == 0)) {
                $contacts_list = NULL;
            }
        }

        // If the request has not been properly executed
        else {
            $contacts_list = NULL;
        }

        return $contacts_list;
    }


    /**
     * Function which counts the videos of a user
     *
     */

    public function countUsersVideos($user_id) {

        // Get the parameters
        $strime_api_url = $this->container->getParameter('strime_api_url');
        $strime_api_token = $this->container->getParameter('strime_api_token');

        // Set the headers
        $headers = array(
            'Accept' => 'application/json',
            'X-Auth-Token' => $strime_api_token,
            'Content-type' => 'application/json'
        );

        // Set the endpoint to get the videos of this user
        $endpoint = $strime_api_url."videos/user/".$user_id."/get";

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $curl_status = $json_response->getStatusCode();
        $response = json_decode( $json_response->getBody() );

        // Set the variable
        $nb_videos = 0;

        // If the request was properly executed
        if($curl_status == 200) {
            $videos_list = $response->{'results'};

            if(is_array($videos_list) && (count($videos_list) > 0))
                $nb_videos = count($videos_list);
        }

        return $nb_videos;
    }



    /**
     * Function which gets the videos of a user
     *
     */

    public function getUsersEmptyProjects($user_id) {

        // Get the parameters
        $strime_api_url = $this->container->getParameter('strime_api_url');
        $strime_api_token = $this->container->getParameter('strime_api_token');

        // Set the headers
        $headers = array(
            'Accept' => 'application/json',
            'X-Auth-Token' => $strime_api_token,
            'Content-type' => 'application/json'
        );

        // Set the endpoint to get the videos of this user
        $endpoint = $strime_api_url."projects/".$user_id."/get";

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
            $projects = $response->{'results'};
        }
        else {
            $projects = NULL;
        }

        // Unset the projects with videos
        $empty_projects = array();

        if(($projects != NULL) && is_array($projects)) {
            foreach ($projects as $project) {
                if((int)$project->{'nb_assets'} == 0) {
                    $project->{'is_empty_project'} = TRUE;
                    $empty_projects[] = $project;
                }
            }
        }

        return $empty_projects;
    }



    /**
     * Function which deletes the projects in doublon.
     *
     */

    public function removeProjectsInDoublonFromList($assets_all) {

        $listed_projects = array();
        foreach ($assets_all as $key => $asset_elt) {
            if(isset($asset_elt->{'project'}->{'project_id'}) && in_array($asset_elt->{'project'}->{'project_id'}, $listed_projects)) {
                unset($assets_all[$key]);
            }
            elseif(isset($asset_elt->{'project'}->{'project_id'})) {
                $listed_projects[] = $asset_elt->{'project'}->{'project_id'};
            }
        }

        return $assets_all;
    }



    /**
     * Function which merges lists of assets and order them by date
     *
     */

    public function mergeAssetsLists($assets_list1, $assets_list2) {

        $assets_all = array();

        if(is_array($assets_list1) && is_array($assets_list2)) {
            $assets_all = array_merge($assets_list1, $assets_list2);
            $assets_all = array_reverse($assets_all);
            usort($assets_all, function($a, $b) {
                // return strtotime($a['start_date']) - strtotime($b['start_date']);
                return strcmp($b->{'created_at'}->{'date'}, $a->{'created_at'}->{'date'});
            });
        }
        elseif(is_array($assets_list1)) {
            $assets_all = $assets_list1;
        }
        elseif(is_array($assets_list2)) {
            $assets_all = $assets_list2;
        }

        return $assets_all;
    }



    /**
     * Function which creates a set of data for JS with the list of contacts emails
     *
     */

    public function prepareContactsList($contacts) {
        $contacts_list = "[";
        $count_contacts = 0;
        if(is_array($contacts) && (count($contacts) > 0)) {
            foreach ($contacts as $contact) {
                if($count_contacts > 0)
                    $contacts_list .= ",'".$contact->{'email'}."'";
                else
                    $contacts_list .= "'".$contact->{'email'}."'";

                $count_contacts++;
            }
        }
        $contacts_list .= "]";

        return $contacts_list;
    }



    /**
     * Function which gets the contacts of a user
     *
     */

    public function getContactDetails($contact_id) {

        // Get the parameters
        $strime_api_url = $this->container->getParameter('strime_api_url');
        $strime_api_token = $this->container->getParameter('strime_api_token');

        // Set the headers
        $headers = array(
            'Accept' => 'application/json',
            'X-Auth-Token' => $strime_api_token,
            'Content-type' => 'application/json'
        );

        // Set the endpoint to get the videos of this user
        $endpoint = $strime_api_url."contact/".$contact_id."/get";

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
            $contact = $response->{'results'};

            // If the contacts list is not an array of contacts
            if(!is_object($contact)) {
                $contact = NULL;
            }
        }

        // If the request has not been properly executed
        else {
            $contact = NULL;
        }

        return $contact;
    }


    /**
     * Function which sets the gravatar image.
     *
     */

    public function setAvatar($email) {

        // Check if there is a session
        if (($this->request === NULL) || !$this->request->hasPreviousSession()) {
            return FALSE;
        }

        $session = $this->request->getSession();

        // If no session exists yet
        if(($session == FALSE) || ($session == NULL) || (count($session) == 0)) {
            return FALSE;
        }

        $avatar = new Avatar;
        $avatar->email = $email;
        $avatar->size = 80;
        $avatar->default = "https://www.strime.io/bundles/strimeback/img/player/icon-avatar-40px.png";
        $gravatar = $avatar->setGravatar();

        // Set the URL in the session
        $session->set("avatar", $gravatar);

        // Save the session
        $session->save();

        return $session;
    }
}
