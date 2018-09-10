<?php

namespace Strime\BackBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Strime\GlobalBundle\Assets\Avatar;

use Strime\GlobalBundle\Entity\NewFeature;
use Strime\GlobalBundle\Entity\HasReadNewFeature;


class ImageController extends Controller
{
    /**
     * @Route("/image/{image_id}/{action}/{url_data}", defaults={"action": NULL, "url_data": NULL}, name="app_image")
     * @Template("StrimeBackBundle:App:image.html.twig")
     */
    public function imageAction(Request $request, $image_id, $action, $url_data)
    {
        // Get the session data
        $session = $request->getSession();
        $bag = $session->getBag("attributes");

        // Get the user ID
        $user_id = $bag->get('user_id');

        // Create the form object
        $form = $this->container->get('strime.helpers.form');
        $form->request = $request;

        // Create the signup form
        $signup_form = $form->createSignupForm();

        // Prepare messages for the signup form
        $signup = $form->prepareSignupMessages($signup_form);

        // Create the login form
        $login_form = $form->createLoginForm();

        // Prepare messages for the login form
        $login = $form->prepareLoginMessages($login_form);

        // Create the forgotten password form
        $forgotten_password_form = $form->createForgottenPasswordForm();

        // Get the form helper
        $form = $this->container->get('strime.back_helpers.form');
        $form->request = $request;

        // Create the users helper
        $users_helper = $this->container->get('strime.back_helpers.users');

        // Get the parameters
        $strime_api_url = $this->container->getParameter('strime_api_url');
        $strime_api_token = $this->container->getParameter('strime_api_token');

        // Set the headers
        $headers = array(
            'Accept' => 'application/json',
            'X-Auth-Token' => $strime_api_token,
            'Content-type' => 'application/json'
        );

        // Set the entity manager
        $em = $this->getDoctrine()->getManager();


        // Get the image details
        // Set the endpoint
        $endpoint = $strime_api_url."image/".$image_id."/get";

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
            $image = $response->{'results'};
        }
        else {

            // If the image cannot be found,
            // redirect the user to the broken link page
            $image = NULL;
            return $this->redirect( $this->generateUrl('app_broken') );
        }


        // Get the comments related to this image
        // Set the endpoint
        $endpoint = $strime_api_url."image/comments/".$image_id."/get";

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
            $nb_comments = $response->{'nb_comments'};
        }
        else {
            $comments = NULL;

            if(isset($response->{'nb_comments'}))
                $nb_comments = $response->{'nb_comments'};
            else
                $nb_comments = 0;
        }

        // If there is an auto-login action required
        if(($action != NULL) && (strcmp($action, "user") == 0) && ($url_data != NULL) && ($user_id == NULL)) {

            // Prepare a variable to check if the user is logged in.
            $user_logged_in = FALSE;

            // Foreach comment, check if we can recreate the auto-login token.
            if($nb_comments > 0) {
                foreach ($comments as $comment) {

                    $auto_login_token_check = hash('md5', $image->{'user'}->{'user_id'}."-".$comment->{'comment_id'}."-".$this->container->getParameter('secret'));

                    if(strcmp($url_data, $auto_login_token_check) == 0) {
                        $user_logged_in = TRUE;
                    }
                }
            }

            // If the token is correct, log the user in.
            if($user_logged_in) {

                // Get the user details
                // Set the endpoint
                $user_id = $image->{'user'}->{'user_id'};
                $endpoint = $strime_api_url."user/".$user_id."/get";

                // Send the request
                $client = new \GuzzleHttp\Client();
                $json_response = $client->request('GET', $endpoint, [
                    'headers' => $headers,
                    'http_errors' => false
                ]);
                $user_curl_status = $json_response->getStatusCode();
                $user_json_response = json_decode( $json_response->getBody() );

                // Set the session
                $session = new Session();
                $session->set('user_id', $user_id);

                // Add user data to the session
                if($user_curl_status == 200) {

                    // Decode the user response
                    $session->set('first_name', $user_json_response->{'results'}->{'first_name'});
                    $session->set('last_name', $user_json_response->{'results'}->{'last_name'});
                    $session->set('offer', $user_json_response->{'results'}->{'offer'}->{'offer_id'});
                    $session->set('offer_price', $user_json_response->{'results'}->{'offer'}->{'price'});
                    $session->set('email', $user_json_response->{'results'}->{'email'});
                    $session->set('status', $user_json_response->{'results'}->{'status'});
                    $session->set('role', $user_json_response->{'results'}->{'role'});
                    $session->set('country', $user_json_response->{'results'}->{'country'});
                    $session->set('avatar', $user_json_response->{'results'}->{'avatar'});

                    // Calculate the storage used
                    $storage_used = (int)round($user_json_response->{'results'}->{'storage_used'} / $this->container->getParameter('strime_api_storage_multiplier')); // To get storage in Mo.
                    $session->set('storage_used', $storage_used);

                    $storage_allowed = $user_json_response->{'results'}->{'offer'}->{'storage_allowed'} * $this->container->getParameter('strime_api_storage_allowed_multiplier'); // To get storage in Mo.
                    $session->set('storage_allowed', $storage_allowed);

                    $storage_used_in_percent = (int)round(((int)$storage_used / ($storage_allowed * (int)$this->container->getParameter('strime_api_storage_multiplier'))) * 100);
                    $session->set('storage_used_in_percent', $storage_used_in_percent);
                }

                // Set the gravatar image
                if($user_json_response->{'results'}->{'avatar'} == NULL) {
                    $set_avatar_result = $users_helper->setAvatar($user_json_response->{'results'}->{'email'});

                    // If the result of the setAvatar function is not FALSE
                    if(!is_bool($set_avatar_result) && ($set_avatar_result != FALSE)) {
                        $session = $set_avatar_result;
                    }
                }
                else
                    $session->set('avatar', $user_json_response->{'results'}->{'avatar'});

                // Save the session
                $session->save();

                // Reinitialize varibales
                $bag = $session->getBag("attributes");
            }
        }

        // Get the user details
        if($user_id != NULL) {

            // Create the share by email form
            $share_by_email_form = $form->createShareByEmailForm('video');

            // Get the user's contacts if the user is logged in
            $contacts = $users_helper->getUsersContacts($user_id);

            // Prepare the contacts for JS
            $contacts_list = $users_helper->prepareContactsList($contacts);

            // Create the share by email form
            $feedback_form = $form->createFeedbackBoxForm($user_id, $bag->get('email'), $bag->get('first_name')." ".$bag->get('last_name'));
        }
        else {
            $share_by_email_form = NULL;
            $contacts = NULL;
            $contacts_list = NULL;

            // Create the share by email form
            $feedback_form = $form->createFeedbackBoxForm();
        }

        // Get the contact details
        if(($action != NULL) && (strcmp($action, "contact") == 0) && ($url_data != NULL)) {
            $invited_contact = $users_helper->getContactDetails($url_data);
        }
        elseif(($user_id == NULL) && ($bag->get('contact_id') != NULL)) {
            $invited_contact = $users_helper->getContactDetails( $bag->get('contact_id') );
        }
        else {
            $invited_contact = NULL;
        }

        // If we got the details of the contact, save them in the session
        if(($invited_contact != NULL) && isset($invited_contact->{'contact_id'}) && isset($invited_contact->{'email'})) {
            $contacts_helper = $this->container->get('strime.back_helpers.contacts');
            $contacts_helper->saveContactDetailsInSession($invited_contact->{'contact_id'}, $invited_contact->{'email'});
        }


        // Get the details of each comment
        if($comments != NULL) {
            if($nb_comments > 0) {
                foreach ($comments as $comment) {

                    // Format the time when the comment has been posted
                    $comment->{'when'} = $this->get('translator')->trans('back.controller_app.image.one_minute', array(), 'back_controller_app');
                    $when = strtotime($comment->{'created_at'}->{'date'});
                    $now = time();
                    $difference = $now - $when;

                    // If the comment has been posted less than an hour ago
                    if($difference < (60 * 60)) {
                        if($difference > 120) {
                            $minutes = floor($difference / 60);
                            $comment->{'when'} = $this->get('translator')->transChoice('back.controller_app.image.multiple_minutes', $minutes, array('%nb_minutes%' => $minutes), 'back_controller_app');
                        }
                    }
                    elseif($difference < (60 * 60 * 24)) {
                        if($difference < (2 * 60 * 60)) {
                            $comment->{'when'} = $this->get('translator')->trans('back.controller_app.image.one_hour', array(), 'back_controller_app');
                        }
                        else {
                            $hours = floor($difference / (60 * 60));
                            $comment->{'when'} = $this->get('translator')->transChoice('back.controller_app.image.multiple_hours', $hours, array('%nb_hours%' => $hours), 'back_controller_app');
                        }
                    }
                    else {
                        if($difference < (2 * 60 * 60 * 24)) {
                            $comment->{'when'} = $this->get('translator')->trans('back.controller_app.image.one_day', array(), 'back_controller_app');
                        }
                        else {
                            $days = floor($difference / (60 * 60 * 24));
                            $comment->{'when'} = $this->get('translator')->transChoice('back.controller_app.image.multiple_days', $days, array('%nb_days%' => $days), 'back_controller_app');
                        }
                    }

                    // Get the top and left position of the marker
                    $area_elts = explode(";", $comment->{'area'});
                    $marker_elts = explode("-", $area_elts[0]);
                    $comment->{'marker_top'} = $marker_elts[0];
                    $comment->{'marker_left'} = $marker_elts[1];

                    // Get the avatar of the author
                    if(is_object($comment->{'author'})) {
                        if($comment->{'author'}->{'avatar'} == NULL) {
                            if(strcmp($comment->{'author'}->{'author_id'}, $user_id) == 0) {
                                $comment->{'author'}->{'avatar'} = $session->get('avatar');
                            }
                            else {
                                // Set the parameterss
                                $default = $this->container->getParameter('strime_app_url') . "bundles/strimeback/img/player/icon-avatar.png";
                                $size = 80;

                                // Define the URL
                                $grav_url = "https://www.gravatar.com/avatar/" . md5( strtolower( trim( $comment->{'author'}->{'email'} ) ) ) . "?d=" . urlencode( $default ) . "&s=" . $size;

                                // Save the URL
                                $comment->{'author'}->{'avatar'} = $grav_url;
                            }
                        }
                        else {

                            // Set a full URL for the avatar of the commenter.
                            if( !preg_match('/www\.gravatar\.com/', $comment->{'author'}->{'avatar'}) )
                                $comment->{'author'}->{'avatar'} = "/" . $comment->{'author'}->{'avatar'};
                            else
                                $comment->{'author'}->{'avatar'} = $comment->{'author'}->{'avatar'};
                        }
                    }
                    else {
                        // Set the object
                        $comment->{'author'} = new \stdClass();

                        // Set the name
                        $comment->{'author'}->{'name'} = $this->get('translator')->trans('back.controller_app.image.anonymous', array(), 'back_controller_app');

                        // Set the parameterss
                        $comment_avatar = NULL;

                        // Save the URL
                        $comment->{'author'}->{'avatar'} = $comment_avatar;
                    }
                }
            }
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
            return strcmp($a->{'created_at'}->{'date'}, $b->{'created_at'}->{'date'});
        });


        // Check if the user is the owner of the video
        if(($user_id != NULL) && (strcmp($user_id, $image->{'user'}->{'user_id'}) == 0)) {
            $is_owner = TRUE;
        }
        else {
            $is_owner = FALSE;
        }

        // If the user is not connected
        if($user_id == NULL) {

            // Get the contacts associated to the video
            $contacts = array();
            foreach ($image->{'contacts'} as $contact) {

                // Get the gravatar of the contact
                $new_avatar = new Avatar;
                $new_avatar->email = $contact->{'email'};
                $new_avatar->size = 40;
                $new_avatar->default = "https://www.strime.io/bundles/strimeback/img/player/icon-avatar-40px.png";
                $contacts[] = array(
                    "contact_id" => $contact->{'contact_id'},
                    "email" => $contact->{'email'},
                    "avatar" => $new_avatar->setGravatar()
                );
            }

            // Create the form the add a contact to the image
            $add_image_contact_form = $form->createAddAssetContactForm($image_id, $image->{'user'}->{'user_id'}, "image");
        }

        // If the user is connected
        else {
            $contacts = NULL;

            // Create the form the add a contact to the image
            $add_image_contact_form = NULL;
        }

        $twig_parameters = array(
            'body_classes' => 'image asset',
            'signup_form' => $signup['signup_form'],
            'signup_message' => $signup['signup_message'],
            'login_form' => $login['login_form'],
            'login_message' => $login['login_message'],
            'forgotten_password_form' => $forgotten_password_form->createView(),
            "user_id" => $user_id,
            "current_path" => "app_image",
            "image" => $image,
            "image_id" => $image_id,
            "is_owner" => $is_owner,
            "comments" => $comments,
            "nb_comments" => $nb_comments,
            "contacts" => $contacts,
            "contacts_list" => $contacts_list,
            "invited_contact" => $invited_contact,
            "comments_list_for_buttons" => $comments_list_for_buttons,
            "feedback_form" => $feedback_form->createView(),
        );

        // If the user is logged in, pass the share form to Twig
        if($user_id != NULL) {

            // Get the last "new feature" message
            $new_feature_disclaimer = $em->getRepository('StrimeGlobalBundle:NewFeature')->findOneBy(array(), array('id' => 'DESC'));

            // Check if the user has marked as read the last new feature
            $has_read_new_feature = $em->getRepository('StrimeGlobalBundle:HasReadNewFeature')->findOneBy(array('user_id' => $user_id, 'new_feature' => $new_feature_disclaimer));

            // If we found that the user has marked as read this new feature, we don't display the disclaimer.
            if($has_read_new_feature != NULL) {
                $new_feature_disclaimer = NULL;
            }

            $twig_parameters["share_by_email_form"] = $share_by_email_form->createView();
            $twig_parameters["add_video_contact_form"] = NULL;
            $twig_parameters["new_feature_disclaimer"] = $new_feature_disclaimer;
        }
        else {
            $twig_parameters["share_by_email_form"] = NULL;
            $twig_parameters["add_image_contact_form"] = $add_image_contact_form->createView();
            $twig_parameters["new_feature_disclaimer"] = NULL;
        }

        return $twig_parameters;
    }
}
