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

class DashboardController extends Controller
{
    /**
     * @Route("/dashboard", name="admin_dashboard")
     * @Template("StrimeAdminBundle:Admin:dashboard.html.twig")
     */
    public function dashboardAction(Request $request)
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

        // Get the total number of videos
        $nb_videos = $this->getTotalNumberOfVideos($headers, $strime_api_url);

        // Get the total number of images
        $nb_images = $this->getTotalNumberOfImages($headers, $strime_api_url);

        // Get the total number of audio files
        $nb_audios = $this->getTotalNumberOfAudios($headers, $strime_api_url);

        // Get the total number of contacts
        $nb_contacts = $this->getTotalNumberOfContacts($headers, $strime_api_url);

        // Get the total number of comments posted on videos
        $nb_comments = $this->getTotalNumberOfComments($headers, $strime_api_url);

        // Get the total number of comments posted on images
        $nb_image_comments = $this->getTotalNumberOfImageComments($headers, $strime_api_url);

        // Get the total number of comments posted on audio files
        $nb_audio_comments = $this->getTotalNumberOfAudioComments($headers, $strime_api_url);

        // Get the number of users per day
        $nb_users_per_day = $this->getNumberOfUsersPerDay($headers, $strime_api_url, NULL, NULL);

        // Get the number of active users per day
        $percentage_active_users_per_day = $this->getNumberOfActiveUsersPerDay($headers, $strime_api_url, NULL, NULL);

        // Get the number of users per offer
        $nb_users_per_offer = $this->getNumberOfUsersPerOffer($headers, $strime_api_url);

        // Get the number of users per locale
        $nb_users_per_locale = $this->getNumberOfUsersPerLocale($headers, $strime_api_url);

        // Get the number of projects per day
        $nb_projects_per_day = $this->getNumberOfProjectsPerDay($headers, $strime_api_url, NULL, NULL);

        // Get the number of videos per day
        $nb_videos_per_day = $this->getNumberOfVideosPerDay($headers, $strime_api_url, NULL, NULL);

        // Get the number of images per day
        $nb_images_per_day = $this->getNumberOfImagesPerDay($headers, $strime_api_url, NULL, NULL);

        // Get the number of images per day
        $nb_audios_per_day = $this->getNumberOfAudiosPerDay($headers, $strime_api_url, NULL, NULL);

        // Get the number of comments posted on videos per day
        $nb_comments_per_day = $this->getNumberOfCommentsPerDay($headers, $strime_api_url, NULL, NULL);

        // Get the number of comments posted on images per day
        $nb_image_comments_per_day = $this->getNumberOfImageCommentsPerDay($headers, $strime_api_url, NULL, NULL);

        // Get the number of comments posted on audio files per day
        $nb_audio_comments_per_day = $this->getNumberOfAudioCommentsPerDay($headers, $strime_api_url, NULL, NULL);

        // Get the number of contacts per day
        $nb_contacts_per_day = $this->getNumberOfContactsPerDay($headers, $strime_api_url, NULL, NULL);

        // Define the average number of videos per user
        if($nb_users != 0)
            $average_number_of_videos_per_user = round($nb_videos / $nb_users, 2, PHP_ROUND_HALF_UP);
        else
            $average_number_of_videos_per_user = 0;

        // Define the average number of images per user
        if($nb_users != 0)
            $average_number_of_images_per_user = round($nb_images / $nb_users, 2, PHP_ROUND_HALF_UP);
        else
            $average_number_of_images_per_user = 0;

        // Define the average number of audio files per user
        if($nb_users != 0)
            $average_number_of_audios_per_user = round($nb_audios / $nb_users, 2, PHP_ROUND_HALF_UP);
        else
            $average_number_of_audios_per_user = 0;

        // Get the average size of videos
        $average_video_size = $this->getAverageVideoSize($headers, $strime_api_url);
        $average_video_size = $average_video_size / $this->container->getParameter('strime_api_storage_multiplier');
        $average_video_size = round($average_video_size, 2);

        // Get the average size of images
        $average_image_size = $this->getAverageImageSize($headers, $strime_api_url);
        $average_image_size = $average_image_size / $this->container->getParameter('strime_api_storage_multiplier');
        $average_image_size = round($average_image_size, 2);

        // Get the average size of audio files
        $average_audio_size = $this->getAverageAudioSize($headers, $strime_api_url);
        $average_audio_size = $average_audio_size / $this->container->getParameter('strime_api_storage_multiplier');
        $average_audio_size = round($average_audio_size, 2);

        return array(
            'body_classes' => 'dashboard',
            'login_form' => $login['login_form'],
            'login_message' => $login['login_message'],
            'forgotten_password_form' => $forgotten_password_form->createView(),
        	"nb_users" => $nb_users,
        	"nb_videos" => $nb_videos,
            "nb_images" => $nb_images,
            "nb_audios" => $nb_audios,
        	"nb_contacts" => $nb_contacts,
        	"nb_comments" => $nb_comments,
            "nb_image_comments" => $nb_image_comments,
            "nb_audio_comments" => $nb_audio_comments,
            "nb_users_per_day" => $nb_users_per_day,
            "nb_projects_per_day" => $nb_projects_per_day,
            "nb_videos_per_day" => $nb_videos_per_day,
            "nb_images_per_day" => $nb_images_per_day,
            "nb_audios_per_day" => $nb_audios_per_day,
            "nb_comments_per_day" => $nb_comments_per_day,
            "nb_image_comments_per_day" => $nb_image_comments_per_day,
            "nb_audio_comments_per_day" => $nb_audio_comments_per_day,
            "nb_contacts_per_day" => $nb_contacts_per_day,
            "percentage_active_users_per_day" => $percentage_active_users_per_day,
            "nb_users_per_offer" => $nb_users_per_offer,
            "nb_users_per_locale" => $nb_users_per_locale,
            "average_number_of_videos_per_user" => $average_number_of_videos_per_user,
            "average_number_of_images_per_user" => $average_number_of_images_per_user,
            "average_number_of_audios_per_user" => $average_number_of_audios_per_user,
            "average_video_size" => $average_video_size,
            "average_image_size" => $average_image_size,
            "average_audio_size" => $average_audio_size,
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
     * Private function which gets the total number of videos
     *
     */

    private function getTotalNumberOfVideos($headers, $strime_api_url) {

        // Set the endpoint to get the number of videos registered
        $endpoint = $strime_api_url."stats/videos/number/get";

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
            $nb_videos = $response->{'results'};
        }

        return $nb_videos;
    }


    /**
     * Private function which gets the total number of images
     *
     */

    private function getTotalNumberOfImages($headers, $strime_api_url) {

        // Set the endpoint to get the number of images registered
        $endpoint = $strime_api_url."stats/images/number/get";

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $curl_status = $json_response->getStatusCode();
        $response = json_decode( $json_response->getBody() );

        // Set the variable
        $nb_images = 0;

        // If the request was properly executed
        if($curl_status == 200) {
            $nb_images = $response->{'results'};
        }

        return $nb_images;
    }


    /**
     * Private function which gets the total number of audio files
     *
     */

    private function getTotalNumberOfAudios($headers, $strime_api_url) {

        // Set the endpoint to get the number of audio files registered
        $endpoint = $strime_api_url."stats/audios/number/get";

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $curl_status = $json_response->getStatusCode();
        $response = json_decode( $json_response->getBody() );

        // Set the variable
        $nb_audios = 0;

        // If the request was properly executed
        if($curl_status == 200) {
            $nb_audios = $response->{'results'};
        }

        return $nb_audios;
    }


    /**
     * Private function which gets the total number of contacts
     *
     */

    private function getTotalNumberOfContacts($headers, $strime_api_url) {

        // Set the endpoint to get the number of contacts registered
        $endpoint = $strime_api_url."stats/contacts/number/get";

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $curl_status = $json_response->getStatusCode();
        $response = json_decode( $json_response->getBody() );

        // Set the variable
        $nb_contacts = 0;

        // If the request was properly executed
        if($curl_status == 200) {
            $nb_contacts = $response->{'results'};
        }

        return $nb_contacts;
    }


    /**
     * Private function which gets the total number of comments posted on videos
     *
     */

    private function getTotalNumberOfComments($headers, $strime_api_url) {

        // Set the endpoint to get the number of comments registered
        $endpoint = $strime_api_url."stats/comments/number/get";

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $curl_status = $json_response->getStatusCode();
        $response = json_decode( $json_response->getBody() );

        // Set the variable
        $nb_comments = 0;

        // If the request was properly executed
        if($curl_status == 200) {
            $nb_comments = $response->{'results'};
        }

        return $nb_comments;
    }


    /**
     * Private function which gets the total number of comments posted on images
     *
     */

    private function getTotalNumberOfImageComments($headers, $strime_api_url) {

        // Set the endpoint to get the number of comments posted on images
        $endpoint = $strime_api_url."stats/image/comments/number/get";

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $curl_status = $json_response->getStatusCode();
        $response = json_decode( $json_response->getBody() );

        // Set the variable
        $nb_comments = 0;

        // If the request was properly executed
        if($curl_status == 200) {
            $nb_comments = $response->{'results'};
        }

        return $nb_comments;
    }


    /**
     * Private function which gets the total number of comments posted on audio files
     *
     */

    private function getTotalNumberOfAudioComments($headers, $strime_api_url) {

        // Set the endpoint to get the number of comments posted on audio files
        $endpoint = $strime_api_url."stats/audio/comments/number/get";

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $curl_status = $json_response->getStatusCode();
        $response = json_decode( $json_response->getBody() );

        // Set the variable
        $nb_comments = 0;

        // If the request was properly executed
        if($curl_status == 200) {
            $nb_comments = $response->{'results'};
        }

        return $nb_comments;
    }


    /**
     * Private function which gets the average size of videos
     *
     */

    private function getAverageVideoSize($headers, $strime_api_url) {

        // Set the endpoint to get the average size of videos registered
        $endpoint = $strime_api_url."stats/videos/average-size/get";

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $curl_status = $json_response->getStatusCode();
        $response = json_decode( $json_response->getBody() );

        // Set the variable
        $average_video_size = 0;

        // If the request was properly executed
        if($curl_status == 200) {
            $average_video_size = $response->{'results'};
        }

        return $average_video_size;
    }


    /**
     * Private function which gets the average size of images
     *
     */

    private function getAverageImageSize($headers, $strime_api_url) {

        // Set the endpoint to get the average size of images registered
        $endpoint = $strime_api_url."stats/images/average-size/get";

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $curl_status = $json_response->getStatusCode();
        $response = json_decode( $json_response->getBody() );

        // Set the variable
        $average_image_size = 0;

        // If the request was properly executed
        if($curl_status == 200) {
            $average_image_size = $response->{'results'};
        }

        return $average_image_size;
    }


    /**
     * Private function which gets the average size of audio files
     *
     */

    private function getAverageAudioSize($headers, $strime_api_url) {

        // Set the endpoint to get the average size of images registered
        $endpoint = $strime_api_url."stats/audios/average-size/get";

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $curl_status = $json_response->getStatusCode();
        $response = json_decode( $json_response->getBody() );

        // Set the variable
        $average_audio_size = 0;

        // If the request was properly executed
        if($curl_status == 200) {
            $average_audio_size = $response->{'results'};
        }

        return $average_audio_size;
    }


    /**
     * Private function which gets the list of clients addresses
     *
     */

    private function getAddressesList($headers, $strime_api_url) {

        // Set the endpoint to get the average size of videos registered
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
     * Private function which gets the number of users per day
     *
     */

    private function getNumberOfUsersPerDay($headers, $strime_api_url, $start_date = NULL, $end_date = NULL) {

        // Set the endpoint to get the number of users registered
        $endpoint = $strime_api_url."stats/users/per-day/get";
        if($start_date != NULL)
            $endpoint .= "/".$start_date;
        if(($start_date != NULL) && ($end_date != NULL))
            $endpoint .= "/".$end_date;

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $curl_status = $json_response->getStatusCode();
        $response = json_decode( $json_response->getBody() );

        // Set the variable
        $nb_users_per_day = array();

        // If the request was properly executed
        if($curl_status == 200) {
            foreach ($response->{'results'} as $result) {

                $date = new \DateTime();
                $date->setTimestamp( $result->{'date_time'} );
                $date->setTimezone( new \DateTimeZone('Europe/Paris') );

                $nb_users_per_day[] = array(
                    "date" => $date->format("d M Y"),
                    "nb_users" => $result->{'nb_users'},
                    "total_nb_users" => $result->{'total_nb_users'}
                );
            }
        }

        return $nb_users_per_day;
    }


    /**
     * Private function which gets the number of active users per day
     *
     */

    private function getNumberOfActiveUsersPerDay($headers, $strime_api_url, $start_date = NULL, $end_date = NULL) {

        // Set the endpoint to get the number of users registered
        $endpoint = $strime_api_url."stats/users/active/per-day/get";
        if($start_date != NULL)
            $endpoint .= "/".$start_date;
        if(($start_date != NULL) && ($end_date != NULL))
            $endpoint .= "/".$end_date;

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $curl_status = $json_response->getStatusCode();
        $response = json_decode( $json_response->getBody() );

        // Set the variable
        $nb_users_per_day = array();

        // If the request was properly executed
        if($curl_status == 200) {
            foreach ($response->{'results'} as $result) {

                $date = new \DateTime();
                $date->setTimestamp( $result->{'date_time'} );
                $date->setTimezone( new \DateTimeZone('Europe/Paris') );

                $nb_users_per_day[] = array(
                    "date" => $date->format("d M Y"),
                    "percentage_active_users" => $result->{'percentage_active_users'}
                );
            }
        }

        return $nb_users_per_day;
    }


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
     * Private function which gets the number of users per locale
     *
     */

    private function getNumberOfUsersPerLocale($headers, $strime_api_url) {

        // Set the endpoint to get the number of users registered
        $endpoint = $strime_api_url."stats/users/per-locale/get";

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $curl_status = $json_response->getStatusCode();
        $response = json_decode( $json_response->getBody() );

        // Set the variable
        $nb_users_per_locale = array();

        // If the request was properly executed
        if($curl_status == 200 && ($response->{'results'} != NULL)) {

            $results = json_decode( $response->{'results'} );

            foreach ($results as $result) {

                $nb_users_per_locale[] = array("locale" => $result->{'locale_name'}, "nb_users" => $result->{'nb_users'});
            }
        }

        return $nb_users_per_locale;
    }


    /**
     * Private function which gets the number of projects per day
     *
     */

    private function getNumberOfProjectsPerDay($headers, $strime_api_url, $start_date = NULL, $end_date = NULL) {

        // Set the endpoint to get the number of projects created
        $endpoint = $strime_api_url."stats/projects/per-day/get";
        if($start_date != NULL)
            $endpoint .= "/".$start_date;
        if(($start_date != NULL) && ($end_date != NULL))
            $endpoint .= "/".$end_date;

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $curl_status = $json_response->getStatusCode();
        $response = json_decode( $json_response->getBody() );

        // Set the variable
        $nb_projects_per_day = array();

        // If the request was properly executed
        if($curl_status == 200) {
            foreach ($response->{'results'} as $result) {

                $date = new \DateTime();
                $date->setTimestamp( $result->{'date_time'} );
                $date->setTimezone( new \DateTimeZone('Europe/Paris') );

                $nb_projects_per_day[] = array(
                    "date" => $date->format("d M Y"),
                    "nb_projects" => $result->{'nb_projects'},
                    "total_nb_projects" => $result->{'total_nb_projects'}
                );
            }
        }

        return $nb_projects_per_day;
    }


    /**
     * Private function which gets the number of videos per day
     *
     */

    private function getNumberOfVideosPerDay($headers, $strime_api_url, $start_date = NULL, $end_date = NULL) {

        // Set the endpoint to get the number of videos uploaded
        $endpoint = $strime_api_url."stats/videos/per-day/get";
        if($start_date != NULL)
            $endpoint .= "/".$start_date;
        if(($start_date != NULL) && ($end_date != NULL))
            $endpoint .= "/".$end_date;

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $curl_status = $json_response->getStatusCode();
        $response = json_decode( $json_response->getBody() );

        // Set the variable
        $nb_videos_per_day = array();

        // If the request was properly executed
        if($curl_status == 200) {
            foreach ($response->{'results'} as $result) {

                $date = new \DateTime();
                $date->setTimestamp( $result->{'date_time'} );
                $date->setTimezone( new \DateTimeZone('Europe/Paris') );

                $nb_videos_per_day[] = array(
                    "date" => $date->format("d M Y"),
                    "nb_videos" => $result->{'nb_videos'},
                    "total_nb_videos" => $result->{'total_nb_videos'}
                );
            }
        }

        return $nb_videos_per_day;
    }


    /**
     * Private function which gets the number of images per day
     *
     */

    private function getNumberOfImagesPerDay($headers, $strime_api_url, $start_date = NULL, $end_date = NULL) {

        // Set the endpoint to get the number of images uploaded
        $endpoint = $strime_api_url."stats/images/per-day/get";
        if($start_date != NULL)
            $endpoint .= "/".$start_date;
        if(($start_date != NULL) && ($end_date != NULL))
            $endpoint .= "/".$end_date;

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $curl_status = $json_response->getStatusCode();
        $response = json_decode( $json_response->getBody() );

        // Set the variable
        $nb_images_per_day = array();

        // If the request was properly executed
        if($curl_status == 200) {
            foreach ($response->{'results'} as $result) {

                $date = new \DateTime();
                $date->setTimestamp( $result->{'date_time'} );
                $date->setTimezone( new \DateTimeZone('Europe/Paris') );

                $nb_images_per_day[] = array(
                    "date" => $date->format("d M Y"),
                    "nb_images" => $result->{'nb_images'},
                    "total_nb_images" => $result->{'total_nb_images'}
                );
            }
        }

        return $nb_images_per_day;
    }


    /**
     * Private function which gets the number of audios per day
     *
     */

    private function getNumberOfAudiosPerDay($headers, $strime_api_url, $start_date = NULL, $end_date = NULL) {

        // Set the endpoint to get the number of audio files uploaded
        $endpoint = $strime_api_url."stats/audios/per-day/get";
        if($start_date != NULL)
            $endpoint .= "/".$start_date;
        if(($start_date != NULL) && ($end_date != NULL))
            $endpoint .= "/".$end_date;

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $curl_status = $json_response->getStatusCode();
        $response = json_decode( $json_response->getBody() );

        // Set the variable
        $nb_audios_per_day = array();

        // If the request was properly executed
        if($curl_status == 200) {
            foreach ($response->{'results'} as $result) {

                $date = new \DateTime();
                $date->setTimestamp( $result->{'date_time'} );
                $date->setTimezone( new \DateTimeZone('Europe/Paris') );

                $nb_audios_per_day[] = array(
                    "date" => $date->format("d M Y"),
                    "nb_audios" => $result->{'nb_audios'},
                    "total_nb_audios" => $result->{'total_nb_audios'}
                );
            }
        }

        return $nb_audios_per_day;
    }


    /**
     * Private function which gets the number of comments posted on videos per day
     *
     */

    private function getNumberOfCommentsPerDay($headers, $strime_api_url, $start_date = NULL, $end_date = NULL) {

        // Set the endpoint to get the number of comments posted
        $endpoint = $strime_api_url."stats/comments/per-day/get";
        if($start_date != NULL)
            $endpoint .= "/".$start_date;
        if(($start_date != NULL) && ($end_date != NULL))
            $endpoint .= "/".$end_date;

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $curl_status = $json_response->getStatusCode();
        $response = json_decode( $json_response->getBody() );

        // Set the variable
        $nb_comments_per_day = array();

        // If the request was properly executed
        if($curl_status == 200) {
            foreach ($response->{'results'} as $result) {

                $date = new \DateTime();
                $date->setTimestamp( $result->{'date_time'} );
                $date->setTimezone( new \DateTimeZone('Europe/Paris') );

                $nb_comments_per_day[] = array(
                    "date" => $date->format("d M Y"),
                    "nb_comments" => $result->{'nb_comments'},
                    "total_nb_comments" => $result->{'total_nb_comments'}
                );
            }
        }

        return $nb_comments_per_day;
    }


    /**
     * Private function which gets the number of comments posted on images per day
     *
     */

    private function getNumberOfImageCommentsPerDay($headers, $strime_api_url, $start_date = NULL, $end_date = NULL) {

        // Set the endpoint to get the number of comments posted
        $endpoint = $strime_api_url."stats/image/comments/per-day/get";
        if($start_date != NULL)
            $endpoint .= "/".$start_date;
        if(($start_date != NULL) && ($end_date != NULL))
            $endpoint .= "/".$end_date;

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $curl_status = $json_response->getStatusCode();
        $response = json_decode( $json_response->getBody() );

        // Set the variable
        $nb_comments_per_day = array();

        // If the request was properly executed
        if($curl_status == 200) {
            foreach ($response->{'results'} as $result) {

                $date = new \DateTime();
                $date->setTimestamp( $result->{'date_time'} );
                $date->setTimezone( new \DateTimeZone('Europe/Paris') );

                $nb_comments_per_day[] = array(
                    "date" => $date->format("d M Y"),
                    "nb_comments" => $result->{'nb_comments'},
                    "total_nb_comments" => $result->{'total_nb_comments'}
                );
            }
        }

        return $nb_comments_per_day;
    }


    /**
     * Private function which gets the number of comments posted on audio files per day
     *
     */

    private function getNumberOfAudioCommentsPerDay($headers, $strime_api_url, $start_date = NULL, $end_date = NULL) {

        // Set the endpoint to get the number of comments posted
        $endpoint = $strime_api_url."stats/audio/comments/per-day/get";
        if($start_date != NULL)
            $endpoint .= "/".$start_date;
        if(($start_date != NULL) && ($end_date != NULL))
            $endpoint .= "/".$end_date;

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $curl_status = $json_response->getStatusCode();
        $response = json_decode( $json_response->getBody() );

        // Set the variable
        $nb_comments_per_day = array();

        // If the request was properly executed
        if($curl_status == 200) {
            foreach ($response->{'results'} as $result) {

                $date = new \DateTime();
                $date->setTimestamp( $result->{'date_time'} );
                $date->setTimezone( new \DateTimeZone('Europe/Paris') );

                $nb_comments_per_day[] = array(
                    "date" => $date->format("d M Y"),
                    "nb_comments" => $result->{'nb_comments'},
                    "total_nb_comments" => $result->{'total_nb_comments'}
                );
            }
        }

        return $nb_comments_per_day;
    }


    /**
     * Private function which gets the number of projects per day
     *
     */

    private function getNumberOfContactsPerDay($headers, $strime_api_url, $start_date = NULL, $end_date = NULL) {

        // Set the endpoint to get the number of contacts created
        $endpoint = $strime_api_url."stats/contacts/per-day/get";
        if($start_date != NULL)
            $endpoint .= "/".$start_date;
        if(($start_date != NULL) && ($end_date != NULL))
            $endpoint .= "/".$end_date;

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $curl_status = $json_response->getStatusCode();
        $response = json_decode( $json_response->getBody() );

        // Set the variable
        $nb_contacts_per_day = array();

        // If the request was properly executed
        if($curl_status == 200) {
            foreach ($response->{'results'} as $result) {

                $date = new \DateTime();
                $date->setTimestamp( $result->{'date_time'} );
                $date->setTimezone( new \DateTimeZone('Europe/Paris') );

                $nb_contacts_per_day[] = array(
                    "date" => $date->format("d M Y"),
                    "nb_contacts" => $result->{'nb_contacts'},
                    "total_nb_contacts" => $result->{'total_nb_contacts'}
                );
            }
        }

        return $nb_contacts_per_day;
    }

}
