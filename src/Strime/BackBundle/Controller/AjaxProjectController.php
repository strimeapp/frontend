<?php

namespace Strime\BackBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;


class AjaxProjectController extends Controller
{

    /**
     * @Route("/ajax/project/edit", name="app_ajax_edit_project")
     * @Template()
     */
    public function ajaxEditProjectAction(Request $request)
    {
        // Get the session data
        $session = $request->getSession();
        $bag = $session->getBag("attributes");

        // Get the user ID
        $user_id = $bag->get('user_id');

        // Get the data
        $project_id = $request->request->get('project_id', NULL);
        $name = $request->request->get('name', NULL);

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
        $endpoint = $strime_api_url."project/".$project_id."/edit";

        // Prepare the params for the request
        $params = array();

        if($name != NULL)
            $params["name"] = $name;

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('PUT', $endpoint, [
            'headers' => $headers,
            'http_errors' => false,
            'json' => $params
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
     * @Route("/ajax/project/get", name="app_ajax_get_project_details")
     * @Template()
     */
    public function ajaxGetProjectDetailsAction(Request $request)
    {
        // Get the session data
        $session = $request->getSession();
        $bag = $session->getBag("attributes");
        $user_id = $bag->get('user_id');

        // Check if the user is logged in
        if($user_id != NULL) {

            // Get the API parameters
            $strime_api_url = $this->container->getParameter('strime_api_url');
            $strime_api_token = $this->container->getParameter('strime_api_token');

            // Set the headers
            $headers = array(
                'Accept' => 'application/json',
                'X-Auth-Token' => $strime_api_token,
                'Content-type' => 'application/json'
            );

            // Get the data
            $project_id = $request->request->get('project_id', NULL);

            // Set the endpoint
            $endpoint = $strime_api_url."project/".$project_id."/get";

            // Send the request
            $client = new \GuzzleHttp\Client();
            $json_response = $client->request('GET', $endpoint, [
                'headers' => $headers,
                'http_errors' => false
            ]);
            $curl_status = $json_response->getStatusCode();
            $json_response = json_decode( $json_response->getBody() );

            // If the encoding job has been found
            if($curl_status == 200) {

                echo json_encode(
                    array(
                        "status" => "success",
                        "response_code" => 200,
                        "project" => $json_response->{'results'},
                    )
                );
                die;
            }
            else {

                echo json_encode(array("status" => "error", "response_code" => 400, "error_source" => "encoding_job_not_found"));
                die;
            }
        }
        else {

            // The user is probably not connected or disconnected.
            // Generate an error
            echo json_encode(array(
                "status" => "error",
                "error_source" => "not_logged_in",
                "error_message" => $this->get('translator')->trans('back.controller_ajax.global.you_must_be_logged_in', array(), 'back_controller_ajax')
            ));
            die;
        }
    }
}
