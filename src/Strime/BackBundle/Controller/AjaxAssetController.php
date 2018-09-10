<?php

namespace Strime\BackBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class AjaxAssetController extends Controller
{

    /**
     * @Route("/ajax/asset/add", name="app_ajax_add_asset")
     * @Template()
     */
    public function ajaxAddAssetAction(Request $request)
    {
        // Get the session data
        $session = $request->getSession();
        $bag = $session->getBag("attributes");

        // Get the user ID
        $user_id = $bag->get('user_id');

        // Get the form helper
        $form = $this->container->get('strime.back_helpers.form');
        $form->request = $request;

        // Create the add video form
        $add_asset_form = $form->createAddAssetForm();
        $add_asset_form->handleRequest($request);

        if($add_asset_form->isSubmitted()) {

            // If the submitted form is valid
            if($add_asset_form->isValid()) {

                // Set the API parameters
                $strime_api_url = $this->container->getParameter('strime_api_url');
                $strime_api_token = $this->container->getParameter('strime_api_token');

                // Set the headers
                $headers = array(
                    'Accept' => 'application/json',
                    'X-Auth-Token' => $strime_api_token,
                    'Content-type' => 'application/json'
                );

                // Get the data
                $name = $add_asset_form->get('name')->getData();
                $asset_type = $add_asset_form->get('asset_type')->getData();
                $asset = $add_asset_form->get('asset')->getData();
                $project = $add_asset_form->get('project')->getData();
                $new_project_name = $add_asset_form->get('new_project_name')->getData();
                $send_to_email = $add_asset_form->get('email')->getData();
                $send_to_emails_list = $add_asset_form->get('emails_list')->getData();

                if($asset != NULL) {

                    // If the user wants to create a new project
                    if(strcmp($project, "1") == 0) {

                        // Create the project
                        $endpoint = $strime_api_url."project/add";

                        $params = array(
                            'user_id' => $user_id,
                            'name' => $new_project_name,
                        );

                        $client = new \GuzzleHttp\Client();
                        $json_response = $client->request('POST', $endpoint, [
                            'headers' => $headers,
                            'http_errors' => false,
                            'json' => $params
                        ]);

                        $curl_status = $json_response->getStatusCode();
                        $response = json_decode($json_response->getBody());

                        // If there was an error while creating the project, send back an error
                        if($curl_status != 201) {
                            echo json_encode(array("status" => "error", "error_source" => $response->{"error_source"}, "response_code" => $curl_status));
                            die;
                        }

                        // If the project was properly created, get the project ID
                        else {
                            $project = $response->{"project_id"};
                        }
                    }

                    // If the user doesn't want to link this video to a project, set it to NULL
                    elseif(strcmp($project, "0") == 0) {
                        $project = NULL;
                    }

                    // Check if $receiver_email has content
                    if((strlen($send_to_email) != 0) && (strlen($send_to_emails_list) != 0)) {
                        $send_to_emails_list .= ",".$send_to_email;
                    }
                    elseif(strlen($send_to_email) != 0) {
                        $send_to_emails_list = $send_to_email;
                    }

                    // Create the helper
                    $users_helper = $this->container->get('strime.back_helpers.users');

                    // Get the user's contacts
                    $contacts = $users_helper->getUsersContacts($user_id);

                    // Prepare the contacts as an array
                    $contacts_list = array();
                    if(is_array($contacts) && (count($contacts) > 0)) {
                        foreach ($contacts as $contact) {
                            $contacts_list[ $contact->{'contact_id'} ] = $contact->{'email'};
                        }
                    }

                    // Prepare the receiver email to accept multiple addresses separated by coma
                    $send_to_email = str_replace(' ', '', $send_to_emails_list);
                    $send_to_emails_list = explode(",", $send_to_email);

                    // Remove duplicates in the array
                    $send_to_emails_list = array_unique($send_to_emails_list, SORT_STRING);

                    // Validate the email addresses
                    // If the email address is not valid, remove it from the table
                    foreach ($send_to_emails_list as $key => $send_to_email) {
                        if(!filter_var($send_to_email, FILTER_VALIDATE_EMAIL)) {
                            unset( $send_to_emails_list[$key] );
                        }
                    }

                    // Prepare an array for contacts IDs
                    $contacts_ids = array();
                    $final_contacts_list = array();

                    // Check if there are new emails and add the contacts
                    foreach ($send_to_emails_list as $send_to_email) {

                        // Set a flag
                        $contact_email_exists = FALSE;

                        // Loop through the contacts of the user
                        foreach ($contacts_list as $contact_id => $contact_email) {

                            // If the contact already exists, we just save its ID in the array.
                            if(strcmp($send_to_email, $contact_email) == 0) {

                                $contact_email_exists = TRUE;
                                $contacts_ids[] = $contact_id;
                                $final_contacts_list[ $contact_id ] = $send_to_email;
                            }
                        }

                        // If this email address is not yet a contact, create it.
                        if(!$contact_email_exists) {

                            // Set the endpoint
                            $endpoint = $strime_api_url."contact/add";

                            $params = array(
                                'email' => $send_to_email,
                                'user_id' => $user_id,
                            );

                            $client = new \GuzzleHttp\Client();
                            $json_response = $client->request('POST', $endpoint, [
                                'headers' => $headers,
                                'http_errors' => false,
                                'json' => $params
                            ]);

                            $curl_status = $json_response->getStatusCode();
                            $json_response = json_decode($json_response->getBody());

                            // If the contact has been created
                            if($curl_status == 201) {
                                $contacts_ids[] = $json_response->{'contact_id'};
                                $final_contacts_list[ $json_response->{'contact_id'} ] = $send_to_email;
                            }
                        }
                    }

                    // Add the asset
                    $asset_name = $asset;
                    $asset = realpath( __DIR__.'/../../../../web/uploads/assets/'.$asset );

                    // Check if we got a proper path
                    if($asset == FALSE) {

                        $json_response = array(
                            "status" => "error",
                            "response_code" => "400",
                            "response" => $this->get('translator')->trans('back.controller_ajax.add_video.file_not_properly_uploaded', array(), 'back_controller_ajax'),
                        );

                        echo json_encode($json_response);
                        die;
                    }

                    // Set the headers
                    $headers = array(
                        'X-Auth-Token' => $strime_api_token,
                    );

                    $endpoint = $strime_api_url.$asset_type."/add";

                    $logger = $this->get("logger");
                    $logger->info("Endpoint: ".$endpoint);

                    $client = new \GuzzleHttp\Client();
                    $json_response = $client->request('POST', $endpoint, [
                        'headers' => $headers,
                        'http_errors' => false,
                        'multipart' => [
                            [
                                'name' => 'user_id',
                                'contents' => $user_id,
                            ],
                            [
                                'name' => 'project_id',
                                'contents' => $project,
                            ],
                            [
                                'name' => 'name',
                                'contents' => $name,
                            ],
                            [
                                'name' => 'file',
                                'contents' => fopen($asset, 'r'),
                                'filename' => $asset_name
                            ]
                        ]
                    ]);

                    $curl_status = $json_response->getStatusCode();
                    $response = json_decode( $json_response->getBody() );

                    // Return the result
                    if($curl_status != 201) {
                        $json_response = array(
                            "status" => "error",
                            "response_code" => $curl_status,
                            "response" => $response,
                            "params" => array(
                                'user_id' => $user_id,
                                'project_id' => $project,
                                'name' => $name,
                                'asset_type' => $asset_type,
                                "asset" => $asset
                            ),
                        );

                        if(isset($response->{"error_source"}))
                            $json_response["error_source"] = $response->{"error_source"};

                        echo json_encode($json_response);
                        die;
                    }
                    else {
                        // Reset the headers
                        $headers = array(
                            'Accept' => 'application/json',
                            'X-Auth-Token' => $strime_api_token,
                            'Content-type' => 'application/json'
                        );

                        // Add the contacts to the video
                        $endpoint = $strime_api_url.$asset_type."/".$response->{$asset_type}->{"asset_id"}."/edit";
                        $params = array(
                            "contacts" => $contacts_ids
                        );

                        $client = new \GuzzleHttp\Client();
                        $json_response = $client->request('PUT', $endpoint, [
                            'headers' => $headers,
                            'http_errors' => false,
                            'json' => $params
                        ]);

                        $curl_status_edit_contacts = $json_response->getStatusCode();
                        $json_response = json_decode( $json_response->getBody() );

                        // If the project / asset has been updated
                        if($curl_status_edit_contacts == 200) {

                            // Nothing special to do, we send the email anyway...
                        }
                        else {
                            // We do nothing for now
                        }

                        // If the upload occured properly, remove the asset file
                        unlink($asset);

                        $final_response = array(
                            "status" => "success",
                            "response_code" => $curl_status,
                            "asset_id" => $response->{$asset_type}->{"asset_id"},
                            "asset_name" => $response->{$asset_type}->{"name"},
                            "asset_thumbnail" => $response->{$asset_type}->{"thumbnail"},
                            "asset_created_at" => strtolower( date('d M Y', $response->{$asset_type}->{"created_at"}) ),
                            "asset" => $asset
                        );
                        if(strcmp($asset_type, "video") == 0) {
                            $final_response["video_screenshot"] = $response->{$asset_type}->{"thumbnail"};
                            $final_response["encoding_job_id"] = $response->{'encoding_job'}->{"encoding_job_id"};
                        }
                        if(strcmp($asset_type, "audio") == 0) {
                            $final_response["audio_screenshot"] = $response->{$asset_type}->{"thumbnail"};
                            $final_response["encoding_job_id"] = $response->{'encoding_job'}->{"encoding_job_id"};
                        }
                        if($response->{'project'} != NULL) {
                            $final_response["project"] = array(
                                "project_id" => $response->{'project'}->{"project_id"},
                                "project_name" => $response->{'project'}->{"name"},
                                "asset" => $asset
                            );
                        }
                        echo json_encode($final_response);
                        die;
                    }
                }

                // If the asset is NULL, there's nothing to upload, return an error.
                else {
                    echo json_encode(array("status" => "error", "asset" => $asset));
                    die;
                }
            }

            // If the submitted form is not valid
            else {
                echo json_encode(array("status" => "error", "asset" => $asset));
                die;
            }
        }
    }


    /**
     * @Route("/ajax/video/edit", name="app_ajax_edit_asset")
     * @Template()
     */
    public function ajaxEditAssetAction(Request $request)
    {
        // Get the session data
        $session = $request->getSession();
        $bag = $session->getBag("attributes");

        // Get the user ID
        $user_id = $bag->get('user_id');

        // Get the data
        $asset_id = $request->request->get('asset_id', NULL);
        $asset_type = $request->request->get('asset_type', 'video');
        $project_id = $request->request->get('project_id', NULL);
        $name = $request->request->get('name', NULL);
        $description = $request->request->get('description', NULL);

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
        switch ($asset_type) {
            case 'video':
                $endpoint = $strime_api_url."video/".$asset_id."/edit";
                break;
            case 'image':
                $endpoint = $strime_api_url."image/".$asset_id."/edit";
                break;
            case 'audio':
                $endpoint = $strime_api_url."audio/".$asset_id."/edit";
                break;

            default:
                $endpoint = $strime_api_url."video/".$asset_id."/edit";
                break;
        }

        // Prepare the params for the request
        $params = array();

        if($project_id != NULL)
            $params["project_id"] = $project_id;
        if($name != NULL)
            $params["name"] = $name;
        if($description != NULL)
            $params["description"] = $description;

        $logger = $this->get("logger");

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
     * @Route("/ajax/asset/get", name="app_ajax_get_asset_details")
     * @Template()
     */
    public function ajaxGetAssetDetailsAction(Request $request)
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
            $asset_id = $request->request->get('asset_id', NULL);
            $asset_type = $request->request->get('asset_type', NULL);

            // Set the endpoint
            switch ($asset_type) {
                case 'video':
                    $endpoint = $strime_api_url."video/".$asset_id."/get";
                    break;
                case 'image':
                    $endpoint = $strime_api_url."image/".$asset_id."/get";
                    break;
                case 'audio':
                    $endpoint = $strime_api_url."audio/".$asset_id."/get";
                    break;

                default:
                    $endpoint = $strime_api_url."video/".$asset_id."/get";
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
                        "asset" => $json_response->{'results'},
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
