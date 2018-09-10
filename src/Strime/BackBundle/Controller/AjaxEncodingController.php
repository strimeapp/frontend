<?php

namespace Strime\BackBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;


class AjaxEncodingController extends Controller
{

    /**
     * @Route("/ajax/encoding-job/status/get", name="app_ajax_get_encoding_job_status")
     * @Template()
     */
    public function ajaxGetEncodingJobStatusAction(Request $request)
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
            $encoding_job_id = $request->request->get('encoding_job_id', NULL);
            $asset_type = $request->request->get('asset_type', NULL);

            // Set the endpoint
            switch ($asset_type) {
                case 'video':
                    $endpoint = $strime_api_url."encoding-job/video/".$encoding_job_id."/get";
                    break;
                case 'audio':
                    $endpoint = $strime_api_url."encoding-job/audio/".$encoding_job_id."/get";
                    break;

                default:
                    $endpoint = $strime_api_url."encoding-job/video/".$encoding_job_id."/get";
                    break;
            }

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
                        "encoding_job_id" => $json_response->{'results'}->{'encoding_job_id'},
                        "encoding_status" => $json_response->{'results'}->{'status'}
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
