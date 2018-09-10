<?php

namespace Strime\BackBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Strime\GlobalBundle\Payment\Payment;
use Strime\GlobalBundle\Invoice\Invoice;
use Strime\GlobalBundle\Assets\Avatar;
use Strime\GlobalBundle\Helpers\StrimeForm;
use Strime\Slackify\Webhooks\Webhook;

use Strime\GlobalBundle\Entity\TaxRate;
use Strime\GlobalBundle\Entity\Upload;
use Strime\GlobalBundle\Entity\NewFeature;
use Strime\GlobalBundle\Entity\HasReadNewFeature;
use Strime\GlobalBundle\Entity\EmailConfirmationToken;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class AppController extends Controller
{
    /**
     * @Route("/dashboard/{action}/{content_type}/{content_id}", defaults={"action": NULL, "content_type": NULL, "content_id": NULL}, name="app_dashboard")
     * @Template("StrimeBackBundle:App:dashboard.html.twig")
     */
    public function dashboardAction(Request $request, $action, $content_type, $content_id)
    {
        // Get the session data
        $session = $request->getSession();
        $bag = $session->getBag("attributes");

        // Get the user ID
        $user_id = $bag->get('user_id');

        // Check if the user is logged in
        if($user_id == NULL)
            return $this->redirectToRoute('home');

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

        // Get the storage available
        $storage_available = $bag->get('storage_allowed') - $bag->get('storage_used');

        // Create the add project form
        $add_project_form = $form->createAddProjectForm();

        // Get the entity manager
        $em = $this->getDoctrine()->getManager();

        // Get the parameters
        $strime_api_url = $this->container->getParameter('strime_api_url');
        $strime_api_token = $this->container->getParameter('strime_api_token');

        // Set the headers
        $headers = array(
            'Accept' => 'application/json',
            'X-Auth-Token' => $strime_api_token,
            'Content-type' => 'application/json'
        );

        // If there is a request to delete an upload
        if(isset($action) && isset($content_type) && (strcmp($action, "delete") == 0) && (strcmp($content_type, "upload") == 0)) {

            // Get the current upload
            $upload = new Upload;
            $upload = $em->getRepository('StrimeGlobalBundle:Upload')->findOneBy(array('user_id' => $user_id));

            // If the upload is defined, remove it
            if($upload != NULL) {
                $em->remove( $upload );
                $em->flush();
            }
        }

        // If there is a request to delete content
        elseif(isset($action) && isset($content_type) && isset($content_id) && (strcmp($action, "delete") == 0)) {

            // Set variables
            $asset_to_delete_is_part_of_project = FALSE;
            $asset_to_delete_project_id = NULL;

            // Check if it is part of a project

            // Send a request to get the details of the content
            // Set the endpoint
            $endpoint = $strime_api_url.$content_type."/".$content_id."/get";

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
                $asset = $response->{'results'};

                // If the video is part of a project, get the project_id
                if(isset($asset->{'project'}) && ($asset->{'project'} != NULL) && ($asset->{'project'}->{'project_id'} != NULL)) {
                    $asset_to_delete_is_part_of_project = TRUE;
                    $asset_to_delete_project_id = $asset->{'project'}->{'project_id'};
                }
            }

            // Send a request to delete the asset
            // Set the endpoint
            $endpoint = $strime_api_url.$content_type."/".$content_id."/delete";

            // Send the request
            $client = new \GuzzleHttp\Client();
            $json_response = $client->request('DELETE', $endpoint, [
                'headers' => $headers,
                'http_errors' => false
            ]);
            $curl_status = $json_response->getStatusCode();
            $json_response = $json_response->getBody();

            // If the request was properly executed
            if($curl_status == 204) {
                if(strcmp($content_type, "video") == 0) {
                    $this->addFlash(
                        'success',
                        $this->get('translator')->trans('back.controller_app.dashboard.video_properly_deleted', array(), 'back_controller_app')
                    );
                }
                elseif(strcmp($content_type, "project") == 0) {
                    $this->addFlash(
                        'success',
                        $this->get('translator')->trans('back.controller_app.dashboard.project_properly_deleted', array(), 'back_controller_app')
                    );
                }
                elseif(strcmp($content_type, "image") == 0) {
                    $this->addFlash(
                        'success',
                        $this->get('translator')->trans('back.controller_app.dashboard.image_properly_deleted', array(), 'back_controller_app')
                    );
                }
                elseif(strcmp($content_type, "audio") == 0) {
                    $this->addFlash(
                        'success',
                        $this->get('translator')->trans('back.controller_app.dashboard.audio_properly_deleted', array(), 'back_controller_app')
                    );
                }
                else {
                    $this->addFlash(
                        'success',
                        $this->get('translator')->trans('back.controller_app.dashboard.file_properly_deleted', array(), 'back_controller_app')
                    );
                }
            }
            elseif($curl_status == 400) {
                if(strcmp($content_type, "video") == 0) {
                    $this->addFlash(
                        'error',
                        $this->get('translator')->trans('back.controller_app.dashboard.video_doesnt_exist_cant_be_deleted', array(), 'back_controller_app')
                    );
                }
                elseif(strcmp($content_type, "project") == 0) {
                    $this->addFlash(
                        'error',
                        $this->get('translator')->trans('back.controller_app.dashboard.project_doesnt_exist_cant_be_deleted', array(), 'back_controller_app')
                    );
                }
                elseif(strcmp($content_type, "image") == 0) {
                    $this->addFlash(
                        'error',
                        $this->get('translator')->trans('back.controller_app.dashboard.image_doesnt_exist_cant_be_deleted', array(), 'back_controller_app')
                    );
                }
                elseif(strcmp($content_type, "audio") == 0) {
                    $this->addFlash(
                        'error',
                        $this->get('translator')->trans('back.controller_app.dashboard.audio_doesnt_exist_cant_be_deleted', array(), 'back_controller_app')
                    );
                }
                else {
                    $this->addFlash(
                        'error',
                        $this->get('translator')->trans('back.controller_app.dashboard.file_doesnt_exist_cant_be_deleted', array(), 'back_controller_app')
                    );
                }
            }
            else {
                if(strcmp($content_type, "video") == 0) {
                    $this->addFlash(
                        'error',
                        $this->get('translator')->trans('back.controller_app.dashboard.error_occured_while_deleting_video', array(), 'back_controller_app')
                    );
                }
                elseif(strcmp($content_type, "project") == 0) {
                    $this->addFlash(
                        'error',
                        $this->get('translator')->trans('back.controller_app.dashboard.error_occured_while_deleting_project', array(), 'back_controller_app')
                    );
                }
                elseif(strcmp($content_type, "image") == 0) {
                    $this->addFlash(
                        'error',
                        $this->get('translator')->trans('back.controller_app.dashboard.error_occured_while_deleting_image', array(), 'back_controller_app')
                    );
                }
                elseif(strcmp($content_type, "audio") == 0) {
                    $this->addFlash(
                        'error',
                        $this->get('translator')->trans('back.controller_app.dashboard.error_occured_while_deleting_audio', array(), 'back_controller_app')
                    );
                }
                else {
                    $this->addFlash(
                        'error',
                        $this->get('translator')->trans('back.controller_app.dashboard.error_occured_while_deleting_file', array(), 'back_controller_app')
                    );
                }
            }


            // Get the user details
            $users_helper = $this->container->get('strime.back_helpers.users');
            $user_details = $users_helper->getUserDetails( $user_id );


            // Update the storage used
            $storage_used = (int)round($user_details->{'storage_used'} / $this->container->getParameter('strime_api_storage_multiplier')); // To get storage in Mo.
            $session->set('storage_used', $storage_used);

            $storage_allowed = $user_details->{'offer'}->{'storage_allowed'} * $this->container->getParameter('strime_api_storage_allowed_multiplier'); // To get storage in Mo.
            $session->set('storage_allowed', $storage_allowed);

            $storage_used_in_percent = (int)round(($storage_used / $storage_allowed) * 100);
            $session->set('storage_used_in_percent', $storage_used_in_percent);

            $session->save();


            // If the video was part of a project, we redirect the user to this project
            if($asset_to_delete_is_part_of_project && ($asset_to_delete_project_id != NULL))
                return $this->redirectToRoute('app_project', array('project_id' => $asset_to_delete_project_id));

            // Otherwise, if the video was not part of a project, we redirect the user to the dashboard
            else
                return $this->redirectToRoute('app_dashboard');
        }

        // If there is a request to create a projet
        if(isset($action) && isset($content_type) && (strcmp($action, "add") == 0) && (strcmp($content_type, "project") == 0)) {

            // Handle the request
            $add_project_form->handleRequest($request);

            // Check if the form has been submitted
            if($add_project_form->isSubmitted()) {

                // If the submitted form is valid
                if($add_project_form->isValid()) {

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
                    $name = $add_project_form->get('name')->getData();

                    // Create the project
                    // Set the endpoint
                    $endpoint = $strime_api_url."project/add";

                    // Set the parameters
                    $params = array(
                        'user_id' => $user_id,
                        'name' => $name,
                    );

                    // Send the request
                    $client = new \GuzzleHttp\Client();
                    $json_response = $client->request('POST', $endpoint, [
                        'headers' => $headers,
                        'http_errors' => false,
                        'json' => $params
                    ]);
                    $curl_status = $json_response->getStatusCode();
                    $response = json_decode( $json_response->getBody() );

                    // If there was an error while creating the project, send back an error
                    if($curl_status != 201) {
                        $this->addFlash(
                            'error',
                            $this->get('translator')->trans('back.controller_app.dashboard.folder_name_already_exists', array(), 'back_controller_app')
                        );
                    }

                    // If the project was properly created, get the project ID
                    else {
                        $this->addFlash(
                            'success',
                            $this->get('translator')->trans('back.controller_app.dashboard.project_successfully_created', array(), 'back_controller_app')
                        );
                    }

                    // We redirect the user to the URL without parameters
                    return $this->redirectToRoute('app_dashboard');
                }
            }
        }

        // Check if the user has a current upload
        $upload = new Upload;
        $upload = $em->getRepository('StrimeGlobalBundle:Upload')->findOneBy(array('user_id' => $user_id));
        $is_uploading = FALSE;
        if($upload != NULL) {
            $is_uploading = TRUE;
        }

        // Create the add video form
        $add_asset_form = $form->createAddAssetForm();

        // Create the share by email form
        $share_by_email_form = $form->createShareByEmailForm();

        // Create the share by email form
        $feedback_form = $form->createFeedbackBoxForm($user_id, $bag->get('email'), $bag->get('first_name')." ".$bag->get('last_name'));

        // Create the upload form
        $upload_form = $this->createFormBuilder()->getForm();

        // Get the user's videos
        $videos = $users_helper->getUsersVideos($user_id);

        // Get the user's images
        $images = $users_helper->getUsersImages($user_id);

        // Get the user's audio files
        $audios = $users_helper->getUsersAudios($user_id);

        // Get the user's projects
        $empty_projects = $users_helper->getUsersEmptyProjects($user_id);

        // Get the user's contacts
        $contacts = $users_helper->getUsersContacts($user_id);

        // Prepare the contacts for JS
        $contacts_list = $users_helper->prepareContactsList($contacts);

        // Merge the videos and the empty projects and order them by date
        $assets_all = $users_helper->mergeAssetsLists($videos, $empty_projects);

        // Merge the images and the videos and order them by date
        $assets_all = $users_helper->mergeAssetsLists($assets_all, $images);

        // Merge the assets and the audios and order them by date
        $assets_all = $users_helper->mergeAssetsLists($assets_all, $audios);

        // Remove duplicate projects
        $assets_all = $users_helper->removeProjectsInDoublonFromList($assets_all);

        // Get the last "new feature" message
        $new_feature_disclaimer = $em->getRepository('StrimeGlobalBundle:NewFeature')->findOneBy(array(), array('id' => 'DESC'));

        // Check if the user has marked as read the last new feature
        $has_read_new_feature = $em->getRepository('StrimeGlobalBundle:HasReadNewFeature')->findOneBy(array('user_id' => $user_id, 'new_feature' => $new_feature_disclaimer));

        // If we found that the user has marked as read this new feature, we don't display the disclaimer.
        if($has_read_new_feature != NULL) {
            $new_feature_disclaimer = NULL;
        }

        // Send a request to get the details of the user
        // Set the endpoint
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
            $user = $response->{'results'};
            $user_needs_to_confirm_email = $user->{'needs_to_confirm_email'};

            // Check if there is a token to confirm the email address
            $email_confirmation_token = $em->getRepository('StrimeGlobalBundle:EmailConfirmationToken')->findOneBy(array('user_id' => $user->{'user_id'}));

            // If a token has been found
            if(($user_needs_to_confirm_email == TRUE) && ($email_confirmation_token != NULL)) {

                // Check how much days there are left before the deactivation of the user account
                $signup_helper = $this->container->get('strime.helpers.signup');
                $days_interval = $signup_helper->checkEmailConfirmationDateToken( $email_confirmation_token );

                $days_to_confirm_email = $this->container->getParameter('user_days_to_confirm_email');
                $days_to_display_email_popup = $this->container->getParameter('user_days_to_display_email_popup');

                if(($days_interval > $days_to_display_email_popup) && ($days_interval < $days_to_confirm_email)) {
                    $user_days_before_deactivation = $days_to_confirm_email - $days_interval;
                    $display_email_confirmation_popup = TRUE;
                }
                else {
                    $user_days_before_deactivation = NULL;
                    $display_email_confirmation_popup = FALSE;
                }
            }
            // If no token has been found
            else {
                $user_days_before_deactivation = NULL;
                $display_email_confirmation_popup = FALSE;
                $user_needs_to_confirm_email = FALSE;
            }
        }

        // If we couldn't get properly the details of the user
        else {
            $user_days_before_deactivation = NULL;
            $display_email_confirmation_popup = FALSE;
            $user_needs_to_confirm_email = FALSE;
        }

        return array(
            'body_classes' => 'dashboard',
            'signup_form' => $signup['signup_form'],
            'signup_message' => $signup['signup_message'],
            'login_form' => $login['login_form'],
            'login_message' => $login['login_message'],
            'forgotten_password_form' => $forgotten_password_form->createView(),
            "user_id" => $user_id,
            "user_needs_to_confirm_email" => $user_needs_to_confirm_email,
            "user_days_before_deactivation" => $user_days_before_deactivation,
            "display_email_confirmation_popup" => $display_email_confirmation_popup,
            "storage_available" => $storage_available,
            "storage_multiplier" => $this->container->getParameter('strime_api_storage_multiplier'),
            "upload_form" => $upload_form->createView(),
            "add_video_form" => $add_asset_form->createView(),
            "add_project_form" => $add_project_form->createView(),
            "share_by_email_form" => $share_by_email_form->createView(),
            "feedback_form" => $feedback_form->createView(),
            "assets" => $assets_all,
            "empty_projects" => $empty_projects,
            "contacts" => $contacts,
            "contacts_list" => $contacts_list,
            "is_uploading" => $is_uploading,
            "new_feature_disclaimer" => $new_feature_disclaimer,
        );
    }



    /**
     * @Route("/project/{project_id}/{action}/{contact_id}", defaults={"action": NULL, "contact_id": NULL}, name="app_project")
     * @Template("StrimeBackBundle:App:project.html.twig")
     */
    public function projectAction(Request $request, $project_id, $action, $contact_id)
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

        // Check if the user is logged in
        // Get the user details
        if($user_id != NULL) {

            // Get the entity manager
            $em = $this->getDoctrine()->getManager();

            // Send a request to get the details of the user
            // Set the endpoint
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
                $user = $response->{'results'};
                $user_needs_to_confirm_email = $user->{'needs_to_confirm_email'};

                // Delete any token to confirm the email address
                $email_confirmation_token = $em->getRepository('StrimeGlobalBundle:EmailConfirmationToken')->findOneBy(array('user_id' => $user->{'user_id'}));

                // If a token has been found
                if($email_confirmation_token != NULL) {

                    // Check how much days there are left before the deactivation of the user account
                    $signup_helper = $this->container->get('strime.helpers.signup');
                    $days_interval = $signup_helper->checkEmailConfirmationDateToken( $email_confirmation_token );

                    $days_to_confirm_email = $this->container->getParameter('user_days_to_confirm_email');
                    $days_to_display_email_popup = $this->container->getParameter('user_days_to_display_email_popup');

                    if(($days_interval > $days_to_display_email_popup) && ($days_interval < $days_to_confirm_email)) {
                        $user_days_before_deactivation = $days_to_confirm_email - $days_interval;
                        $display_email_confirmation_popup = TRUE;
                    }
                    else {
                        $user_days_before_deactivation = NULL;
                        $display_email_confirmation_popup = FALSE;
                    }
                }

                // If no token has been found
                else {
                    $user_days_before_deactivation = NULL;
                    $display_email_confirmation_popup = FALSE;
                }
            }
            else {
                $user = NULL;
                $user_days_before_deactivation = NULL;
                $display_email_confirmation_popup = FALSE;
            }

            // Create the add video form
            $add_asset_form = $form->createAddAssetForm($project_id);

            // Create the share by email form
            $share_by_email_form = $form->createShareByEmailForm('video');

            // Create the upload form
            $upload_form = $this->createFormBuilder()->getForm();
            $upload_form->handleRequest($request);

            // Check if the user has a current upload
            $upload = new Upload;
            $upload = $em->getRepository('StrimeGlobalBundle:Upload')->findOneBy(array('user_id' => $user_id));
            $is_uploading = FALSE;
            if($upload != NULL) {
                $is_uploading = TRUE;
            }

            // Get the user's contacts if the user is logged in
            $contacts = $users_helper->getUsersContacts($user_id);

            // Prepare the contacts for JS
            $contacts_list = $users_helper->prepareContactsList($contacts);

            // Create the share by email form
            $feedback_form = $form->createFeedbackBoxForm($user_id, $bag->get('email'), $bag->get('first_name')." ".$bag->get('last_name'));

            // Get the storage available
            $storage_available = $bag->get('storage_allowed') - $bag->get('storage_used');
        }

        // If the user is not logged in
        else {
            $user_days_before_deactivation = NULL;
            $user_needs_to_confirm_email = NULL;
            $display_email_confirmation_popup = FALSE;
            $add_asset_form = NULL;
            $share_by_email_form = NULL;
            $upload_form = NULL;
            $contacts = NULL;
            $contacts_list = "[]";
            $is_uploading = FALSE;
            $storage_available = NULL;

            // Create the share by email form
            $feedback_form = $form->createFeedbackBoxForm();
        }

        // Get the contact details
        if(($action != NULL) && (strcmp($action, "contact") == 0) && ($contact_id != NULL)) {
            $invited_contact = $users_helper->getContactDetails($contact_id);
        }
        else {
            $invited_contact = NULL;
        }

        // Get the details of the project
        // Set the endpoint
        $endpoint = $strime_api_url."project/".$project_id."/get";

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
            $project = $response->{'results'};
        }
        else {
            $this->addFlash(
                'error',
                $this->get('translator')->trans('back.controller_app.project.project_doesnt_exist', array(), 'back_controller_app')
            );

            return $this->redirectToRoute('app_dashboard');
        }

        // Get the videos of this project
        // Set the endpoint
        $endpoint = $strime_api_url."videos/project/".$project_id."/get";

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
            $videos = $response->{'results'};
        }
        else {
            $videos = NULL;
        }

        // Get the images of this project
        // Set the endpoint
        $endpoint = $strime_api_url."images/project/".$project_id."/get";

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
            $images = $response->{'results'};
        }
        else {
            $images = NULL;
        }

        // Get the audio files of this project
        // Set the endpoint
        $endpoint = $strime_api_url."audios/project/".$project_id."/get";

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
            $audios = $response->{'results'};
        }
        else {
            $audios = NULL;
        }

        // Merge the videos and the images and order them by date
        $assets_all = $users_helper->mergeAssetsLists($videos, $images);

        // Merge the assets and the audios and order them by date
        $assets_all = $users_helper->mergeAssetsLists($assets_all, $audios);

        // Check if the user is the owner
        if(strcmp($user_id, $project->{'user'}->{'user_id'}) == 0)
            $is_owner = TRUE;
        else
            $is_owner = FALSE;

        $twig_parameters = array(
            'body_classes' => 'project',
            'signup_form' => $signup['signup_form'],
            'signup_message' => $signup['signup_message'],
            'login_form' => $login['login_form'],
            'login_message' => $login['login_message'],
            'forgotten_password_form' => $forgotten_password_form->createView(),
            "user_id" => $user_id,
            "current_path" => "app_project",
            "user_needs_to_confirm_email" => $user_needs_to_confirm_email,
            "user_days_before_deactivation" => $user_days_before_deactivation,
            "display_email_confirmation_popup" => $display_email_confirmation_popup,
            "storage_available" => $storage_available,
            "storage_multiplier" => $this->container->getParameter('strime_api_storage_multiplier'),
            "assets" => $assets_all,
            "contacts" => $contacts,
            "contacts_list" => $contacts_list,
            "project" => $project,
            "invited_contact" => $invited_contact,
            "feedback_form" => $feedback_form->createView(),
            "is_uploading" => $is_uploading,
            "is_owner" => $is_owner
        );

        // If the user is logged in
        if($user_id != NULL) {

            // Get the last "new feature" message
            $new_feature_disclaimer = $em->getRepository('StrimeGlobalBundle:NewFeature')->findOneBy(array(), array('id' => 'DESC'));

            // Check if the user has marked as read the last new feature
            $has_read_new_feature = $em->getRepository('StrimeGlobalBundle:HasReadNewFeature')->findOneBy(array('user_id' => $user_id, 'new_feature' => $new_feature_disclaimer));

            // If we found that the user has marked as read this new feature, we don't display the disclaimer.
            if($has_read_new_feature != NULL) {
                $new_feature_disclaimer = NULL;
            }

            $twig_parameters["upload_form"] = $upload_form->createView();
            $twig_parameters["add_video_form"] = $add_asset_form->createView();
            $twig_parameters["share_by_email_form"] = $share_by_email_form->createView();
            $twig_parameters["new_feature_disclaimer"] = $new_feature_disclaimer;
        }
        else {
            $twig_parameters["upload_form"] = NULL;
            $twig_parameters["add_video_form"] = NULL;
            $twig_parameters["share_by_email_form"] = NULL;
            $twig_parameters["new_feature_disclaimer"] = NULL;
        }

        return $twig_parameters;
    }
}
