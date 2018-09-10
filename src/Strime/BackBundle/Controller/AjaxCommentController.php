<?php

namespace Strime\BackBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Strime\Slackify\Webhooks\Webhook;


class AjaxCommentController extends Controller
{

    /**
     * @Route("/ajax/comment/add", name="app_ajax_add_comment")
     * @Template()
     */
    public function ajaxAddCommentAction(Request $request)
    {
        // Get the session data
        $session = $request->getSession();
        $bag = $session->getBag("attributes");

        // Get the user ID
        $user_id = $bag->get('user_id');

        // If the user is logged in
        // Get extra data
        if($user_id != NULL) {
            $user_first_name = $bag->get('first_name');
            $user_last_name = $bag->get('last_name');
            $user_email = $bag->get('email');
        }
        else {
            $user_first_name = NULL;
            $user_last_name = NULL;
            $user_email = NULL;
        }

        // Get the data
        $asset_type = $request->request->get('asset_type', 'video');
        $asset_id = $request->request->get('asset_id', NULL);
        $comment = $request->request->get('comment', NULL);
        $time = $request->request->get('time', NULL);
        $user_id = $request->request->get('user_id', NULL);
        $contact_id = $request->request->get('contact_id', NULL);
        $answer_to = $request->request->get('answer_to', NULL);
        $top = $request->request->get('top', NULL);
        $left = $request->request->get('left', NULL);
        $bottom = $request->request->get('bottom', NULL);
        $right = $request->request->get('right', NULL);
        $author = $request->request->get('author', NULL);
        $is_owner = $request->request->get('is_owner', NULL);
        $answer_to_author_id = $request->request->get('answer_to_author_id', NULL);
        $answer_to_author_type = $request->request->get('answer_to_author_type', NULL);
        $answer_to_author = NULL;
        $user_mail_notification_preference = 'now';

        // Convert the value
        if($is_owner == 1)
            $is_owner = TRUE;
        else
            $is_owner = FALSE;

        // Set the area
        $area = NULL;
        if(($bottom == NULL) || ($right == NULL)) {
            $area = $top."-".$left;
        }
        else {
            $area = $top."-".$left.";".$bottom."-".$right;
        }

        // Set the API parameters
        $strime_api_url = $this->container->getParameter('strime_api_url');
        $strime_api_token = $this->container->getParameter('strime_api_token');

        // Set the headers
        $headers = array(
            'Accept' => 'application/json',
            'X-Auth-Token' => $strime_api_token,
            'Content-type' => 'application/json'
        );

        // Add the contacts to the asset
        switch ($asset_type) {
            case 'video':
                $endpoint = $strime_api_url."comment/add";
                break;
            case 'image':
                $endpoint = $strime_api_url."image/comment/add";
                break;
            case 'audio':
                $endpoint = $strime_api_url."audio/comment/add";
                break;

            default:
                $endpoint = $strime_api_url."comment/add";
                break;
        }

        // Prepare the params for the request
        $params = array(
            'comment' => $comment,
            'area' => $area,
        );

        switch ($asset_type) {
            case 'video':
                $params['video_id'] = $asset_id;
                break;
            case 'image':
                $params['image_id'] = $asset_id;
                break;
            case 'audio':
                $params['audio_id'] = $asset_id;
                break;

            default:
                $params['video_id'] = $asset_id;
                break;
        }

        if($contact_id != NULL) {
            $params["contact_id"] = $contact_id;
        }
        elseif($user_id != NULL) {
            $params["user_id"] = $user_id;
        }

        if($time != NULL) {
            $params["time"] = $time;
        }

        if($answer_to != NULL) {
            $params["answer_to"] = $answer_to;
        }

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('POST', $endpoint, [
            'headers' => $headers,
            'http_errors' => false,
            'json' => $params
        ]);
        $curl_status = $json_response->getStatusCode();
        $json_response = $json_response->getBody();
        $comment_json = json_decode( $json_response );

        // If the comment has been created
        if($curl_status == 201) {

            // Get the asset details
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

            // Create the cURL request
            $client = new \GuzzleHttp\Client();
            $json_response_asset = $client->request('GET', $endpoint, [
                'headers' => $headers,
                'http_errors' => false
            ]);
            $asset_curl_status = $json_response_asset->getStatusCode();
            $asset_details_json = json_decode( $json_response_asset->getBody() );

            // If the request occured properly
            if($asset_curl_status == 200) {

                // Extract the asset details
                $asset_details = $asset_details_json->{'results'};

                // Format the time for the email
                $time_formatted = gmdate("H:i:s", $time);

                // Prepare the parameter answer_to
                // And get the details of the person we are supposed to answer to
                if($answer_to != NULL) {
                    $answer_to_boolean = TRUE;

                    if(($answer_to_author_id != NULL) && ($answer_to_author_type != NULL)) {

                        // Get the person to which the answer will be sent
                        $endpoint = $strime_api_url.$answer_to_author_type."/".$answer_to_author_id."/get";

                        // Create the cURL request
                        $client = new \GuzzleHttp\Client();
                        $json_response_author = $client->request('GET', $endpoint, [
                            'headers' => $headers,
                            'http_errors' => false
                        ]);
                        $contact_curl_status = $json_response_author->getStatusCode();
                        $contact_details_json = json_decode( $json_response_author->getBody() );

                        // If the request occured properly
                        if($contact_curl_status == 200) {

                            $answer_to_author = $contact_details_json->{'results'}->{'email'};
                        }
                    }
                }
                else {
                    $answer_to_boolean = FALSE;
                }

                // Use the asset owner details as the contact information.
                if(isset($asset_details->{'user'}->{'email'})) {
                    $user_email = $asset_details->{'user'}->{'email'};
                    $user_first_name = $asset_details->{'user'}->{'first_name'};
                    $user_last_name = $asset_details->{'user'}->{'last_name'};
                    $user_mail_notification_preference = $asset_details->{'user'}->{'mail_notification'};
                }


                // Set who will get the email

                // If the person who posts the comment is the owner,
                // define if this is an answer to a previous comment
                // and send the email to this author
                if( $answer_to != NULL ) {
                    $send_to = $answer_to_author;
                }

                // If the person who posts the comment is not the owner,
                // send the email to the owner
                else {
                    $send_to = $user_email;
                }

                // Prepare the token to log in the owner when he will click on the link in the email
                // Only if the person to whom we are sending the link is the owner of the asset.
                if(!$is_owner && (strcmp($asset_details->{'user'}->{'email'}, $send_to) == 0)) {
                    $auto_login_token = hash('md5', $asset_details->{'user'}->{'user_id'}."-".$comment_json->{'comment_id'}."-".$this->container->getParameter('secret'));
                }
                else {
                    $auto_login_token = NULL;
                }

                // Send the comment by email, if it's not to the owner of the asset.
                // The owner of the asset will receive the comment with a slight delay, but with a screenshot.
                if((strcmp($user_mail_notification_preference, 'now') == 0) && (strcmp($asset_details->{'user'}->{'email'}, $send_to) != 0)) {
                    switch ($asset_type) {
                        case 'video':
                            $email_subject_params = array("%video%" => $asset_details->{'name'});
                            break;
                        case 'image':
                            $email_subject_params = array("%image%" => $asset_details->{'name'});
                            break;
                        case 'audio':
                            $email_subject_params = array("%audio%" => $asset_details->{'name'});
                            break;

                        default:
                            $email_subject_params = array("%video%" => $asset_details->{'name'});
                            break;
                    }

                    $message = \Swift_Message::newInstance()
                        ->setSubject('Strime - '.$this->get('translator')->trans('back.controller_ajax.add_comment.a_comment_has_been_posted_on_'.$asset_type, $email_subject_params, 'back_controller_ajax'))
                        ->setFrom( array( 'contact@strime.io' => 'Strime' ) )
                        ->setTo( $send_to )
                        ->setBody(
                            $this->renderView(
                                'emails/comment.html.twig',
                                array(
                                    'current_year' => date('Y'),
                                    'first_name' => $user_first_name,
                                    'comment' => $comment,
                                    'comment_id' => $comment_json->{'comment_id'},
                                    'time' => $time_formatted,
                                    'answer_to' => $answer_to_boolean,
                                    'asset' => $asset_details,
                                    'is_owner' => $is_owner,
                                    'author' => $author,
                                    'answer_to_author' => $answer_to_author,
                                    'auto_login_token' => $auto_login_token
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

                // Get the details of the owner
                $endpoint = $strime_api_url."user/".$asset_details->{'user'}->{'user_id'}."/get";

                // Create the cURL request
                $client = new \GuzzleHttp\Client();
                $json_response_asset = $client->request('GET', $endpoint, [
                    'headers' => $headers,
                    'http_errors' => false
                ]);
                $owner_curl_status = $json_response_asset->getStatusCode();
                $owner_details_json = json_decode( $json_response_asset->getBody() );

                if($owner_curl_status == 200) {

                    // Check if the owner of the asset has activated Slack notifications
                    $owner_details = $owner_details_json->{'results'};
                    if(($owner_details->{'user_slack_details'} != NULL) && ($owner_details->{'user_slack_details'}->{'webhook_url'} != NULL)) {

                        // Check if the owner is commenting on his own asset
                        if(!$is_owner) {

                            // Prepare the comment to be displayed in Slack
                            if($auto_login_token != NULL) {
                                $params = array(
                                    'action' => 'user',
                                    'url_data' => $auto_login_token
                                );
                            }
                            else {
                                $params = array();
                            }

                            $fallback_params = array();


                            switch ($asset_type) {
                                case 'video':
                                    $params['video_id'] = $asset_details->{'asset_id'};
                                    $slack_parameters["link"] = $this->generateUrl('app_video', $params, TRUE);
                                    $fallback_params["%video%"] = $asset_details->{'name'};
                                    break;
                                case 'image':
                                    $params['image_id'] = $asset_details->{'asset_id'};
                                    $slack_parameters["link"] = $this->generateUrl('app_image', $params, TRUE);
                                    $fallback_params["%image%"] = $asset_details->{'name'};
                                    break;
                                case 'audio':
                                    $params['audio_id'] = $asset_details->{'asset_id'};
                                    $slack_parameters["link"] = $this->generateUrl('app_audio', $params, TRUE);
                                    $fallback_params["%audio%"] = $asset_details->{'name'};
                                    break;

                                default:
                                    $params['video_id'] = $asset_details->{'asset_id'};
                                    $slack_parameters["link"] = $this->generateUrl('app_video', $params, TRUE);
                                    $fallback_params["%video%"] = $asset_details->{'name'};
                                    break;
                            }

                            // Prepare the URL
                            $webhook_url = $owner_details->{'user_slack_details'}->{'webhook_url'};

                            // Prepare the attachments
                            $slack_webhook = new Webhook( $webhook_url );
                            $slack_webhook->setAttachments(
                                array(
                                    array(
                                        "fallback" => $this->get('translator')->trans('back.controller_ajax.add_comment.a_comment_has_been_posted_on_'.$asset_type, $fallback_params, 'back_controller_ajax'),
                                        "text" => $comment,
                                        "color" => "#0CAC9A",
                                        "author" => array(
                                            "author_name" => $author
                                        ),
                                        "title" => $asset_details->{'name'},
                                        "fields" => array(
                                            "title" => $asset_details->{'name'},
                                            "value" => $comment,
                                            "short" => FALSE
                                        ),
                                        "footer_icon" => "https://www.strime.io/bundles/strimeglobal/img/icon-strime.jpg",
                                        "ts" => time()
                                    )
                                )
                            );

                            // Prepare the message parameters
                            $message_params = $fallback_params;
                            $message_params["%author_name%"] = $author;
                            if(strcmp($asset_type, "video") == 0) {
                                $message_params["%at%"] = $time_formatted;
                            }

                            // Send the notification
                            $slack_parameters = array(
                                "message" => $this->get('translator')->trans('back.controller_ajax.add_comment.a_comment_has_been_posted_on_'.$asset_type.'_by', $message_params, 'back_controller_ajax'),
                                "username" => "Strime",
                                "link_text" => $this->get('translator')->trans('back.controller_ajax.add_comment.see_the_'.$asset_type, array(), 'back_controller_ajax'),
                                "icon" => "https://www.strime.io/bundles/strimeglobal/img/icon-strime.jpg"
                            );
                            $slack_webhook->sendMessage($slack_parameters);
                        }
                    }
                }
            }

            echo $json_response;
            die;
        }

        // If the comment has not been created, return the JSON to the browser
        else {

            echo $json_response;
            die;
        }
    }


    /**
     * @Route("/ajax/comment/edit", name="app_ajax_edit_comment")
     * @Template()
     */
    public function ajaxEditCommentAction(Request $request)
    {
        // Get the session data
        $session = $request->getSession();
        $bag = $session->getBag("attributes");

        // Get the user ID
        $user_id = $bag->get('user_id');

        // Get the data
        $comment_id = $request->request->get('comment_id', NULL);
        $done = $request->request->get('done', NULL);
        $comment = $request->request->get('comment', NULL);
        $top = $request->request->get('top', NULL);
        $left = $request->request->get('left', NULL);
        $bottom = $request->request->get('bottom', NULL);
        $right = $request->request->get('right', NULL);
        $asset_type = $request->request->get('asset_type', NULL);

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
                $endpoint = $strime_api_url."comment/".$comment_id."/edit";
                break;
            case 'image':
                $endpoint = $strime_api_url."image/comment/".$comment_id."/edit";
                break;
            case 'audio':
                $endpoint = $strime_api_url."audio/comment/".$comment_id."/edit";
                break;

            default:
                $endpoint = $strime_api_url."comment/".$comment_id."/edit";
                break;
        }

        // Prepare the params for the request
        $params = array();

        if($done != NULL)
            $params["done"] = $done;
        if($comment != NULL)
            $params["comment"] = $comment;

        // Set the area
        if(($top != NULL) && ($left != NULL)) {
            if(($bottom == NULL) || ($right == NULL)) {
                $area = $top."-".$left;
            }
            else {
                $area = $top."-".$left.";".$bottom."-".$right;
            }

            $params["area"] = $area;
        }


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
                "response_code" => $curl_status,
                "response" => json_decode($json_response)
            )
        );
        die;
    }


    /**
     * @Route("/ajax/comment/delete", name="app_ajax_delete_comment")
     * @Template()
     */
    public function ajaxDeleteCommentAction(Request $request)
    {
        // Get the session data
        $session = $request->getSession();
        $bag = $session->getBag("attributes");

        // Get the user ID
        $user_id = $bag->get('user_id');

        // Get the data
        $comment_id = $request->request->get('comment_id', NULL);
        $asset_id = $request->request->get('asset_id', NULL);
        $asset_type = $request->request->get('asset_type', NULL);

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
                $endpoint = $strime_api_url."comment/".$comment_id."/delete";
                break;
            case 'image':
                $endpoint = $strime_api_url."image/comment/".$comment_id."/delete";
                break;
            case 'audio':
                $endpoint = $strime_api_url."audio/comment/".$comment_id."/delete";
                break;

            default:
                $endpoint = $strime_api_url."comment/".$comment_id."/delete";
                break;
        }

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('DELETE', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $delete_curl_status = $json_response->getStatusCode();
        $json_response = $json_response->getBody();

        // Get the number of comments for the asset
        switch ($asset_type) {
            case 'video':
                $endpoint = $strime_api_url."comments/".$asset_id."/count";
                break;
            case 'image':
                $endpoint = $strime_api_url."image/comments/".$asset_id."/count";
                break;
            case 'audio':
                $endpoint = $strime_api_url."audio/comments/".$asset_id."/count";
                break;

            default:
                $endpoint = $strime_api_url."comments/".$asset_id."/count";
                break;
        }

        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $nb_comments_curl_status = $json_response->getStatusCode();
        $nb_comments_json = json_decode( $json_response->getBody() );

        // Return the JSON response
        echo json_encode(
            array(
                "response_code" => $delete_curl_status,
                "nb_comments" => $nb_comments_json->{'nb_comments'}
            )
        );
        die;
    }


    /**
     * @Route("/ajax/comments/get-parents-ids", name="app_ajax_comments_get_parents_ids")
     * @Template()
     */
    public function ajaxCommentsGetParentsIdsAction(Request $request)
    {
        // Get the session data
        $session = $request->getSession();
        $bag = $session->getBag("attributes");

        // Get the user ID
        $user_id = $bag->get('user_id');

        // Get the data
        $asset_id = $request->request->get('asset_id', NULL);
        $asset_type = $request->request->get('asset_type', NULL);

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
                $endpoint = $strime_api_url."comments/".$asset_id."/get";
                break;
            case 'image':
                $endpoint = $strime_api_url."image/comments/".$asset_id."/get";
                break;
            case 'audio':
                $endpoint = $strime_api_url."audio/comments/".$asset_id."/get";
                break;

            default:
                $endpoint = $strime_api_url."comments/".$asset_id."/get";
                break;
        }

        // Get the comments related to this asset
        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $curl_status = $json_response->getStatusCode();
        $response = json_decode( $json_response->getBody() );

        // If the request was properly executed, set variables.
        if($curl_status == 200) {
            $comments = $response->{'results'};
        }
        else {
            $comments = NULL;
        }

        // Create an array to store the list of initial comments (eg without parent)
        // which we will use to go to the previous or next comment
        if(is_array($comments))
            $comments_list_for_buttons = $comments;
        else
            $comments_list_for_buttons = array();

        // Remove the comments with parents
        foreach ($comments_list_for_buttons as $key => $value) {
            if($value->{'answer_to'} != NULL) {
                unset($comments_list_for_buttons[$key]);
            }
        }

        // Sort them by creation date
        usort($comments_list_for_buttons, function($a, $b) {
            if(isset($a->{'time'}) && isset($b->{'time'})) {
                return $a->{'time'} - $b->{'time'};
            }
            else {
                return $a->{'created_at'}->{'date'} - $b->{'created_at'}->{'date'};
            }
        });

        // Return the JSON response
        echo json_encode(
            array(
                "response_code" => $curl_status,
                "comments" => $comments_list_for_buttons
            )
        );
        die;
    }
}
