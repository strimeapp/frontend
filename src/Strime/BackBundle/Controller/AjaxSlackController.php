<?php

namespace Strime\BackBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

use Strime\Slackify\Webhooks\Webhook;



class AjaxSlackController extends Controller
{
    /**
     * @Route("/ajax/slack/send-test-notification", name="app_ajax_slack_send_test_notification")
     * @Template()
     */
    public function ajaxSlackSendTestNotificationAction(Request $request)
    {
        // Get the session data
        $session = $request->getSession();
        $bag = $session->getBag("attributes");

        // Get the user ID
        $user_id = $bag->get('user_id');

        // Get the details of the user
        // Get the API parameters
        $strime_api_url = $this->container->getParameter('strime_api_url');
        $strime_api_token = $this->container->getParameter('strime_api_token');

        // Set the headers
        $headers = array(
            'Accept' => 'application/json',
            'X-Auth-Token' => $strime_api_token,
            'Content-type' => 'application/json'
        );

        // Set the endpoint to get the details of the user
        $endpoint = $strime_api_url."user/".$user_id."/get";

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $user_details_curl_status = $json_response->getStatusCode();
        $user_details_json_response = json_decode( $json_response->getBody() );

        $user_details = NULL;

        // If we properly got the list of answers
        if($user_details_curl_status == 200) {

            // Create a variable
            $user_details = $user_details_json_response->{'results'};
        }
        else {

            // Return the JSON response
            echo json_encode(
                array(
                    "status" => "error",
                    "response_code" => $user_details_curl_status,
                    "response" => $user_details_json_response
                )
            );
            die;
        }

        if(($user_details->{'user_slack_details'} != NULL) && ($user_details->{'user_slack_details'}->{'webhook_url'} != NULL)) {
            $webhook_url = $user_details->{'user_slack_details'}->{'webhook_url'};
        }
        else {
            // Return the JSON response
            echo json_encode(
                array(
                    "status" => "error",
                    "response_code" => 400,
                    "response" => "This user doesn't have any webhook"
                )
            );
            die;
        }

        $slack_webhook = new Webhook( $webhook_url );
        $slack_webhook->setAttachments(
            array(
                array(
                    "fallback" => $this->get('translator')->trans('back.controller_ajax_slack.send_test.successful_test', array(), 'back_controller_ajax_slack'),
                    "text" => $this->get('translator')->trans('back.controller_ajax_slack.send_test.webhook_perfectly_set_up', array(), 'back_controller_ajax_slack'),
                    "color" => "success",
                    "author" => array(
                        "author_name" => "Strime"
                    ),
                    "title" => $this->get('translator')->trans('back.controller_ajax_slack.send_test.successful_test', array(), 'back_controller_ajax_slack'),
                    "fields" => array(
                        "title" => $this->get('translator')->trans('back.controller_ajax_slack.send_test.successful_test', array(), 'back_controller_ajax_slack'),
                        "value" => $this->get('translator')->trans('back.controller_ajax_slack.send_test.webhook_perfectly_set_up', array(), 'back_controller_ajax_slack'),
                        "short" => FALSE
                    ),
                    "footer_icon" => "https://www.strime.io/bundles/strimeglobal/img/icon-strime.jpg",
                    "ts" => time()
                )
            )
        );
        $slack_webhook->sendMessage(array(
            "message" => $this->get('translator')->trans('back.controller_ajax_slack.send_test.well_done', array(), 'back_controller_ajax_slack'),
            "username" => "Strime",
            "link" => "https://www.strime.io",
            "link_text" => "Strime",
            "icon" => "https://www.strime.io/bundles/strimeglobal/img/icon-strime.jpg"
        ));

        // Return the JSON response
        echo json_encode(
            array(
                "status" => "success",
                "response_code" => 200
            )
        );
        die;
    }



    /**
     * @Route("/ajax/user/revoke-slack-comment-notification", name="app_ajax_user_revoke_slack_comment_notification")
     * @Template()
     */
    public function ajaxUserRevokeSlackCommentNotificationAction(Request $request)
    {
        // Get the session data
        $session = $request->getSession();
        $bag = $session->getBag("attributes");

        // Get the user ID
        $user_id = $bag->get('user_id');

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
        $endpoint = $strime_api_url."user/".$user_id."/revoke-slack";

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
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
}
