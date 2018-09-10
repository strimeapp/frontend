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

class EncodingJobsController extends Controller
{
    /**
     * @Route("/encoding-jobs/{action}/{encoding_job_id}", defaults={"action": NULL, "encoding_job_id": NULL}, name="admin_encoding_jobs")
     * @Template("StrimeAdminBundle:Admin:encoding-jobs.html.twig")
     */
    public function encodingJobsAction(Request $request, $action, $encoding_job_id)
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


        // If we got a request to relaunch a job
        if(($action != NULL) && ($encoding_job_id != NULL) && (strcmp($action, "relaunch") == 0)) {

            // Update the encoding job details
            // Set the endpoint
            $endpoint = $strime_api_url."encoding-job/video/".$encoding_job_id."/edit";

            // Prepare the parameters
            $params = array(
                'started' => 0,
                'status' => 0
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

            if($curl_status == 200) {

                $this->addFlash(
                    'success',
                    'Ce job a bien été relancé.'
                );
            }
            else {

                $this->addFlash(
                    'error',
                    'Une erreur est survenue lors de la réinitialisation de ce job.'
                );
            }

            // Redirect to the route without parameters
            return $this->redirectToRoute('admin_encoding_jobs');
        }


        // If we got a request to kill a job
        if(($action != NULL) && ($encoding_job_id != NULL) && (strcmp($action, "kill") == 0)) {

            // Update the encoding job details
            // Prepare the parameters
            $endpoint = $strime_api_url."encoding-job/video/".$encoding_job_id."/delete-with-video";

            // Send the request
            $client = new \GuzzleHttp\Client();
            $json_response = $client->request('DELETE', $endpoint, [
                'headers' => $headers,
                'http_errors' => false
            ]);
            $curl_status = $json_response->getStatusCode();
            $response = json_decode( $json_response->getBody() );

            if($curl_status == 204) {

                $this->addFlash(
                    'success',
                    'Ce job a bien été tué.'
                );
            }
            else {

                $this->addFlash(
                    'error',
                    'Une erreur est survenue lors de la suppression de ce job.'
                );
            }

            // Redirect to the route without parameters
            return $this->redirectToRoute('admin_encoding_jobs');
        }

        // Get the stats about the time of encoding jobs
        $stats_encoding_jobs_time = $this->getEncodingJobsTimeStats($headers, $strime_api_url, "video", 100);

        // Get the current encoding jobs
        $encoding_jobs = $this->getEncodingJobsList($headers, $strime_api_url, "video");

        return array(
            'body_classes' => 'users',
            'login_form' => $login['login_form'],
            'login_message' => $login['login_message'],
            'forgotten_password_form' => $forgotten_password_form->createView(),
            'encoding_jobs' => $encoding_jobs,
            'stats_encoding_jobs_time' => $stats_encoding_jobs_time
        );
    }




    /**
     * @Route("/encoding-jobs-audio/{action}/{encoding_job_id}", defaults={"action": NULL, "encoding_job_id": NULL}, name="admin_encoding_jobs_audio")
     * @Template("StrimeAdminBundle:Admin:encoding-jobs-audio.html.twig")
     */
    public function encodingJobsAudioAction(Request $request, $action, $encoding_job_id)
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


        // If we got a request to relaunch a job
        if(($action != NULL) && ($encoding_job_id != NULL) && (strcmp($action, "relaunch") == 0)) {

            // Update the encoding job details
            // Set the endpoint
            $endpoint = $strime_api_url."encoding-job/audio/".$encoding_job_id."/edit";

            // Prepare the parameters
            $params = array(
                'started' => 0,
                'status' => 0
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

            if($curl_status == 200) {

                $this->addFlash(
                    'success',
                    'Ce job a bien été relancé.'
                );
            }
            else {

                $this->addFlash(
                    'error',
                    'Une erreur est survenue lors de la réinitialisation de ce job.'
                );
            }

            // Redirect to the route without parameters
            return $this->redirectToRoute('admin_encoding_jobs_audio');
        }


        // If we got a request to kill a job
        if(($action != NULL) && ($encoding_job_id != NULL) && (strcmp($action, "kill") == 0)) {

            // Update the encoding job details
            // Prepare the parameters
            $endpoint = $strime_api_url."encoding-job/audio/".$encoding_job_id."/delete-with-audio";

            // Send the request
            $client = new \GuzzleHttp\Client();
            $json_response = $client->request('DELETE', $endpoint, [
                'headers' => $headers,
                'http_errors' => false
            ]);
            $curl_status = $json_response->getStatusCode();
            $response = json_decode( $json_response->getBody() );

            if($curl_status == 204) {

                $this->addFlash(
                    'success',
                    'Ce job a bien été tué.'
                );
            }
            else {

                $this->addFlash(
                    'error',
                    'Une erreur est survenue lors de la suppression de ce job.'
                );
            }

            // Redirect to the route without parameters
            return $this->redirectToRoute('admin_encoding_jobs_audio');
        }

        // Get the stats about the time of encoding jobs
        $stats_encoding_jobs_time = $this->getEncodingJobsTimeStats($headers, $strime_api_url, "audio", 100);

        // Get the current encoding jobs
        $encoding_jobs = $this->getEncodingJobsList($headers, $strime_api_url, "audio");

        return array(
            'body_classes' => 'users',
            'login_form' => $login['login_form'],
            'login_message' => $login['login_message'],
            'forgotten_password_form' => $forgotten_password_form->createView(),
            'encoding_jobs' => $encoding_jobs,
            'stats_encoding_jobs_time' => $stats_encoding_jobs_time
        );
    }




    /***************************************************************/
    /******************     Private functions     ******************/
    /***************************************************************//**
     * Private function which gets the list of current encoding jobs
     *
     */

    private function getEncodingJobsList($headers, $strime_api_url, $asset_type = "video") {

        // Set the endpoint to get the average size of videos registered
        switch ($asset_type) {
            case 'video':
                $endpoint = $strime_api_url."encoding-jobs/video/get";
                break;
            case 'audio':
                $endpoint = $strime_api_url."encoding-jobs/audio/get";
                break;

            default:
                $endpoint = $strime_api_url."encoding-jobs/video/get";
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

        return $response->{'results'};
    }


    /**
     * Private function which gets the stats about the time of the encoding jobs
     *
     */

    private function getEncodingJobsTimeStats($headers, $strime_api_url, $asset_type = "video", $number = NULL) {

        // Set the endpoint to get the number of users registered
        switch ($asset_type) {
            case 'video':
                $endpoint = $strime_api_url."stats/encoding-job-time/get/last";
                break;
            case 'audio':
                $endpoint = $strime_api_url."stats/audio/encoding-job-time/get/last";
                break;

            default:
                $endpoint = $strime_api_url."stats/encoding-job-time/get/last";
                break;
        }

        if($number != NULL)
            $endpoint .= "/".$number;

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $curl_status = $json_response->getStatusCode();
        $response = json_decode( $json_response->getBody() );

        // If the request was not properly executed
        if($curl_status != 200) {
            return FALSE;
        }

        return $response->{'results'};
    }

}
