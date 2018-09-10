<?php

namespace Strime\BackBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

use Strime\GlobalBundle\Assets\Avatar;



class AjaxContactController extends Controller
{
    /**
     * @Route("/ajax/contact/save/", name="app_ajax_save_contact_in_session")
     * @Template()
     */
    public function ajaxSaveContactInSession(Request $request) {

        // Get the session data
        $session = $request->getSession();
        $bag = $session->getBag("attributes");

        // Get the user ID
        $user_id = $bag->get('user_id');

        // Check if the user is logged in
        // If the user is not logged in, save the contact details in the session
        if($user_id == NULL) {

            $contact_id = $request->request->get('contact_id', NULL);
            $contact_email = $request->request->get('contact_email', NULL);

            $contacts_helper = $this->container->get('strime.back_helpers.contacts');
            $contacts_helper->saveContactDetailsInSession($contact_id, $contact_email);

            echo json_encode(array("logged_in" => FALSE, "saved" => TRUE));
        }
        else {
            echo json_encode(array("logged_in" => TRUE, "saved" => FALSE));
        }

        die;
    }

    /**
     * @Route("/ajax/share/email", name="app_ajax_share_by_email")
     * @Template()
     */
    public function ajaxShareByEmailAction(Request $request)
    {
        // Get the session data
        $session = $request->getSession();
        $bag = $session->getBag("attributes");

        // Get the user ID
        $user_id = $bag->get('user_id');
        $first_name = $bag->get('first_name');
        $last_name = $bag->get('last_name');
        $sender_email = $bag->get('email');

        // Get the form helper
        $form = $this->container->get('strime.back_helpers.form');
        $form->request = $request;

        // Create the share by email form
        $share_by_email_form = $form->createShareByEmailForm($request);
        $share_by_email_form->handleRequest($request);

        if($share_by_email_form->isSubmitted()) {

            // If the submitted form is valid
            if($share_by_email_form->isValid()) {

                // Get the parameters
                $strime_api_url = $this->container->getParameter('strime_api_url');
                $strime_api_token = $this->container->getParameter('strime_api_token');

                // Set the headers
                $headers = array(
                    'Accept' => 'application/json',
                    'X-Auth-Token' => $strime_api_token,
                    'Content-type' => 'application/json'
                );

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

                // Get the data
                $send_to_email = $share_by_email_form->get('email')->getData();
                $send_to_emails_list = $share_by_email_form->get('emails_list')->getData();
                $user_message = $share_by_email_form->get('message')->getData();
                $url = $share_by_email_form->get('url')->getData();
                $type = $share_by_email_form->get('type')->getData();
                $content_name = $share_by_email_form->get('content_name')->getData();
                $content_id = $share_by_email_form->get('content_id')->getData();

                // Check if $receiver_email has content
                if((strlen($send_to_email) != 0) && (strlen($send_to_emails_list) != 0)) {
                    $send_to_emails_list .= ",".$send_to_email;
                }
                elseif(strlen($send_to_email) != 0) {
                    $send_to_emails_list = $send_to_email;
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

                        // Set the parameters
                        $params = array(
                            'email' => $send_to_email,
                            'user_id' => $user_id,
                        );

                        // Send the request
                        $client = new \GuzzleHttp\Client();
                        $json_response = $client->request('POST', $endpoint, [
                            'headers' => $headers,
                            'http_errors' => false,
                            'json' => $params
                        ]);
                        $curl_status = $json_response->getStatusCode();
                        $json_response = json_decode( $json_response->getBody() );

                        // If the contact has been created
                        if($curl_status == 201) {
                            $contacts_ids[] = $json_response->{'contact_id'};
                            $final_contacts_list[ $json_response->{'contact_id'} ] = $send_to_email;
                        }
                    }
                }

                // For each email, add the corresponding contact to the project / video
                // Set the endpoint
                $endpoint = $strime_api_url.$type."/".$content_id."/edit";

                // Set the parameters
                $params = array(
                    'contacts' => $contacts_ids
                );

                // Send the request
                $client = new \GuzzleHttp\Client();
                $json_response = $client->request('PUT', $endpoint, [
                    'headers' => $headers,
                    'http_errors' => false,
                    'json' => $params
                ]);
                $curl_status = $json_response->getStatusCode();
                $json_response = json_decode( $json_response->getBody() );

                // If the project / video has been updated
                if($curl_status == 201) {

                    // Nothing special to do, we send the email anyway...
                }
                else {
                    // We do nothing for now
                }

                // Set the subject
                if(strcmp($type, "video") == 0)
                    $subject = "Strime - ".$this->get('translator')->trans('back.controller_ajax.share_by_email.invites_you_to_comment_a_video', array("%first_name%" => $first_name), 'back_controller_ajax');
                if(strcmp($type, "image") == 0)
                    $subject = "Strime - ".$this->get('translator')->trans('back.controller_ajax.share_by_email.invites_you_to_comment_an_image', array("%first_name%" => $first_name), 'back_controller_ajax');
                else
                    $subject = "Strime - ".$this->get('translator')->trans('back.controller_ajax.share_by_email.invites_you_to_comment_a_project', array("%first_name%" => $first_name), 'back_controller_ajax');

                // Send the messages
                if(is_array($final_contacts_list) && (count($final_contacts_list) > 0)) {
                    foreach ($final_contacts_list as $contact_id => $contact_email) {

                        // Set the contact URL
                        $contact_url = $url . "/contact/" . $contact_id;

                        $message = \Swift_Message::newInstance()
                            ->setSubject($subject)
                            ->setFrom(array($sender_email => $first_name." ".$last_name))
                            ->setTo( $contact_email )
                            ->setBody(
                                $this->renderView(
                                    'emails/share_url.html.twig',
                                    array(
                                        'current_year' => date('Y'),
                                        'first_name' => $first_name,
                                        'last_name' => $last_name,
                                        'message' => $user_message,
                                        'url' => $contact_url,
                                        'type' => $type,
                                        'content_name' => $content_name,
                                        'locale' => $bag->get('locale')
                                    )
                                ),
                                'text/html'
                            )
                            /*
                             * If you also want to include a plaintext version of the message
                            ->addPart(
                                $this->renderView(
                                    'Emails/registration.txt.twig',
                                    array('name' => $name)
                                ),
                                'text/plain'
                            )
                            */
                        ;
                        $this->get('mailer')->send($message);
                        $transport = $this->container->get('mailer')->getTransport();
                        $spool = $transport->getSpool();
                        $spool->flushQueue($this->container->get('swiftmailer.transport.real'));
                    }
                }

                echo json_encode(array("status" => "success", "response_code" => 200, "contacts_list" => $final_contacts_list));
                die;
            }

            // If the submitted form is not valid
            else {
                echo json_encode(array("status" => "error", "response_code" => 400));
                die;
            }
        }
    }



    /**
     * @Route("/ajax/asset/contact/add", name="app_ajax_add_asset_contact")
     * @Template()
     */
    public function ajaxAddAssetContactAction(Request $request)
    {
        // Get the session data
        $session = $request->getSession();
        $bag = $session->getBag("attributes");

        // Get the form helper
        $form = $this->container->get('strime.back_helpers.form');
        $form->request = $request;

        // Create the share by email form
        $add_asset_contact_form = $form->createAddAssetContactForm();
        $add_asset_contact_form->handleRequest($request);

        if($add_asset_contact_form->isSubmitted()) {

            // If the submitted form is valid
            if($add_asset_contact_form->isValid()) {

                // Get the parameters
                $strime_api_url = $this->container->getParameter('strime_api_url');
                $strime_api_token = $this->container->getParameter('strime_api_token');

                // Set the headers
                $headers = array(
                    'Accept' => 'application/json',
                    'X-Auth-Token' => $strime_api_token,
                    'Content-type' => 'application/json'
                );

                // Get the data
                $email = $add_asset_contact_form->get('email')->getData();
                $content_id = $add_asset_contact_form->get('content_id')->getData();
                $content_type = $add_asset_contact_form->get('content_type')->getData();
                $user_id = $add_asset_contact_form->get('user_id')->getData();

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

                // Validate the email address
                if(filter_var($email, FILTER_VALIDATE_EMAIL)) {

                    // Prepare an array for contacts IDs
                    $contacts_ids = array();
                    $final_contacts_list = array();

                    // Set a flag
                    $contact_email_exists = FALSE;
                    $current_contact_id = NULL;

                    // Loop through the contacts of the user
                    foreach ($contacts_list as $contact_id => $contact_email) {

                        // If the contact already exists, we just save its ID in the array.
                        if(strcmp($email, $contact_email) == 0) {

                            $contact_email_exists = TRUE;
                            $contacts_ids[] = $contact_id;
                            $final_contacts_list[ $contact_id ] = $email;
                            $current_contact_id = $contact_id;
                        }
                    }

                    // If this email address is not yet a contact, create it.
                    if(!$contact_email_exists) {

                        // Set the endpoint
                        $endpoint = $strime_api_url."contact/add";

                        // Set the parameters
                        $params = array(
                            'email' => $email,
                            'user_id' => $user_id,
                        );

                        // Send the request
                        $client = new \GuzzleHttp\Client();
                        $json_response = $client->request('POST', $endpoint, [
                            'headers' => $headers,
                            'http_errors' => false,
                            'json' => $params
                        ]);
                        $curl_status = $json_response->getStatusCode();
                        $add_json_response = json_decode( $json_response->getBody() );

                        // If the contact has been created
                        if($curl_status == 201) {

                            // Update the list of contacts
                            $contacts_ids[] = $add_json_response->{'contact_id'};
                            $final_contacts_list[ $add_json_response->{'contact_id'} ] = $email;

                            // Add the corresponding contact to the project / video
                            // Set the endpoint
                            $endpoint = $strime_api_url.$content_type."/".$content_id."/edit";

                            // Set the parameters
                            $params = array(
                                'contacts' => $contacts_ids
                            );

                            // Send the request
                            $client = new \GuzzleHttp\Client();
                            $json_response = $client->request('PUT', $endpoint, [
                                'headers' => $headers,
                                'http_errors' => false,
                                'json' => $params
                            ]);
                            $curl_status = $json_response->getStatusCode();
                            $edit_json_response = json_decode( $json_response->getBody() );

                            // If the project / video has been updated
                            if($curl_status == 200) {

                                // Get the gravatar of the new contact
                                $new_avatar = new Avatar;
                                $new_avatar->email = $email;
                                $new_avatar->size = 40;
                                $new_avatar->default = "https://www.strime.io/bundles/strimeback/img/player/icon-avatar-40px.png";

                                echo json_encode(
                                    array(
                                        "status" => "success",
                                        "response_code" => 200,
                                        "contacts_list" => $final_contacts_list,
                                        "contact_avatar" => $new_avatar->setGravatar(),
                                        "contact_id" => $add_json_response->{'contact_id'},
                                        "contact_email" => $email
                                    )
                                );
                                die;
                            }
                            else {

                                echo json_encode(array("status" => "error", "response_code" => 400, "error_source" => "video_not_updated"));
                                die;
                            }
                        }
                        else {

                            echo json_encode(array("status" => "error", "response_code" => 400, "error_source" => "contact_not_created"));
                            die;
                        }
                    }

                    // If the contact already exists, we add it to the asset
                    else {

                        // Set the endpoint
                        $endpoint = $strime_api_url.$content_type."/".$content_id."/edit";

                        // Set the parameters
                        $params = array(
                            'contacts' => $contacts_ids
                        );

                        // Send the request
                        $client = new \GuzzleHttp\Client();
                        $json_response = $client->request('PUT', $endpoint, [
                            'headers' => $headers,
                            'http_errors' => false,
                            'json' => $params
                        ]);
                        $curl_status = $json_response->getStatusCode();
                        $json_response = json_decode( $json_response->getBody() );

                        // If the project / video has been updated
                        if($curl_status == 200) {

                            // Get the gravatar of the new contact
                            $new_avatar = new Avatar;
                            $new_avatar->email = $email;
                            $new_avatar->size = 40;
                            $new_avatar->default = "https://www.strime.io/bundles/strimeback/img/player/icon-avatar-40px.png";

                            echo json_encode(
                                array(
                                    "status" => "success",
                                    "response_code" => 200,
                                    "contacts_list" => $final_contacts_list,
                                    "contact_avatar" => $new_avatar->setGravatar(),
                                    "contact_id" => $current_contact_id,
                                    "contact_email" => $email
                                )
                            );
                            die;
                        }
                        else {

                            echo json_encode(array("status" => "error", "response_code" => 400, "error_source" => "video_not_updated"));
                            die;
                        }
                    }
                }

                // If the email address is not valid, send a message back
                else {

                    echo json_encode(array("status" => "error", "response_code" => 400, "error_source" => "email_not_valid"));
                    die;
                }
            }

            // If the submitted form is not valid
            else {
                echo json_encode(array("status" => "error", "response_code" => 400, "error_source" => "form_not_updated"));
                die;
            }
        }
    }
}
