<?php

namespace Strime\BackBundle\Helpers;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Session\Session;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class Form {

    private $container;
    private $translator;
    public $request;

    public function __construct(Container $container) {

        $this->container = $container;
        $this->translator = $this->container->get('translator');
    }



    /**
     * @return object The form
     */
    public function createAddAssetForm($project_id = NULL) {

        // Check if there is a session
        $session = $this->request->getSession();

        // If no session exists yet
        if(($session == FALSE) || ($session == NULL) || (count($session) == 0)) {
            return FALSE;
        }

        // If a session exists, check if a user_id is already saved
        else {
            $user_id = $session->get('user_id');
        }

        // Set the variables
        $projects = array(
            'back.helpers_form.add_asset.dont_associate_to_project' => 0,
            'back.helpers_form.add_asset.new_project' => 1
        );

        // Get the parameters
        $strime_api_url = $this->container->getParameter('strime_api_url');
        $strime_api_token = $this->container->getParameter('strime_api_token');

        // Set the headers
        $headers = array(
            'Accept' => 'application/json',
            'X-Auth-Token' => $strime_api_token,
            'Content-type' => 'application/json'
        );

        // Set the endpoint to get the projects of this user
        $endpoint = $strime_api_url."projects/".$user_id."/get";

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

            // If the results property is an array, it means that there are projects
            if(is_array($response->{'results'})) {
                foreach ($response->{'results'} as $project) {
                    $projects[ $project->{'name'} ] = $project->{'project_id'};
                }
            }
        }

        // Set the add asset form
        if($project_id == NULL) {
            $add_asset_form = $this->container->get('form.factory')->createBuilder()
                ->add('upload_id', HiddenType::class, array('data' => $user_id))
                ->add('asset_type', HiddenType::class, array('data' => 'video'))
                ->add('asset', HiddenType::class, array())
                ->add('emails_list', HiddenType::class, array('data' => ''))
                ->add('email', EmailType::class, array('label' => '', 'attr' => array('placeholder' => 'back.helpers_form.add_asset.email', 'class' => 'typeahead'), 'translation_domain' => 'back_helpers_form'))
                ->add('name', TextType::class, array('label' => '', 'attr' => array('placeholder' => 'back.helpers_form.add_asset.video_name'), 'translation_domain' => 'back_helpers_form'))
                ->add('project', ChoiceType::class, array(
                    'choices' => $projects,
                    'expanded' => FALSE,
                    'multiple' => FALSE,
                    'choices_as_values' => TRUE,
                    'translation_domain' => 'back_helpers_form'
                ))
                ->add('new_project_name', TextType::class, array('label' => '', 'attr' => array('placeholder' => 'back.helpers_form.add_asset.project_name'), 'translation_domain' => 'back_helpers_form'))
                ->add('submit', SubmitType::class, array(
                        'attr' => array('class' => '')
                ))
                ->getForm();
        }
        else {
            $add_asset_form = $this->container->get('form.factory')->createBuilder()
                ->add('upload_id', HiddenType::class, array('data' => $user_id))
                ->add('asset_type', HiddenType::class, array('data' => 'video'))
                ->add('asset', HiddenType::class, array('data' => $user_id))
                ->add('emails_list', HiddenType::class, array('data' => ''))
                ->add('email', EmailType::class, array('label' => '', 'attr' => array('placeholder' => 'back.helpers_form.add_asset.email', 'class' => 'typeahead'), 'translation_domain' => 'back_helpers_form'))
                ->add('project', HiddenType::class, array('data' => $project_id))
                ->add('name', TextType::class, array('label' => '', 'attr' => array('placeholder' => 'back.helpers_form.add_asset.video_name'), 'translation_domain' => 'back_helpers_form'))
                ->add('new_project_name', TextType::class, array('label' => '', 'attr' => array('placeholder' => 'back.helpers_form.add_asset.project_name'), 'translation_domain' => 'back_helpers_form'))
                ->add('submit', SubmitType::class, array(
                        'attr' => array('class' => '')
                ))
                ->getForm();
        }

        return $add_asset_form;
    }


    /**
     * Function which creates the add project form
     *
     */

    public function createAddProjectForm() {

        // Check if there is a session
        $session = $this->request->getSession();

        // If no session exists yet
        if(($session == FALSE) || ($session == NULL) || (count($session) == 0)) {
            return FALSE;
        }

        // If a session exists, check if a user_id is already saved
        else {
            $user_id = $session->get('user_id');
        }

        // Set the add project form
        $add_project_form = $this->container->get('form.factory')->createBuilder()
            ->add('name', TextType::class, array('label' => '', 'attr' => array('placeholder' => 'back.helpers_form.add_project.folder_name'), 'translation_domain' => 'back_helpers_form'))
            ->add('submit', SubmitType::class, array(
                    'attr' => array('class' => '')
            ))
            ->getForm();

        return $add_project_form;
    }



    /**
     * Function which creates the share by email form
     *
     */

    public function createShareByEmailForm($type = '') {

        $share_by_email_form = $this->container->get('form.factory')->createBuilder()
            ->add('url', HiddenType::class, array('data' => ''))
            ->add('type', HiddenType::class, array('data' => $type))
            ->add('content_id', HiddenType::class, array('data' => ''))
            ->add('emails_list', HiddenType::class, array('data' => ''))
            ->add('content_name', HiddenType::class, array('data' => ''))
            ->add('email', EmailType::class, array('label' => '', 'attr' => array('placeholder' => 'back.helpers_form.share_by_email.email', 'class' => 'typeahead'), 'translation_domain' => 'back_helpers_form'))
            ->add('message', TextareaType::class, array('label' => '', 'attr' => array('placeholder' => 'back.helpers_form.share_by_email.your_message'), 'translation_domain' => 'back_helpers_form'))
            ->add('submit', SubmitType::class, array('attr' => array('class' => '')))
            ->getForm();

        return $share_by_email_form;
    }



    /**
     * Function which creates the add contact form
     *
     */

    public function createAddAssetContactForm($asset_id = NULL, $user_id = NULL, $content_type = 'video') {

        $add_asset_contact_form = $this->container->get('form.factory')->createBuilder()
            ->add('content_id', HiddenType::class, array('data' => $asset_id))
            ->add('content_type', HiddenType::class, array('data' => $content_type))
            ->add('user_id', HiddenType::class, array('data' => $user_id))
            ->add('email', EmailType::class, array('label' => '', 'attr' => array('placeholder' => 'back.helpers_form.add_contact.type_in_email', 'class' => ''), 'translation_domain' => 'back_helpers_form'))
            ->add('submit', SubmitType::class, array('attr' => array('class' => '')))
            ->getForm();

        return $add_asset_contact_form;
    }


    /**
     * Function which creates the feedback box form
     *
     */

    public function createFeedbackBoxForm($user_id = NULL, $email = '', $name = "Anonyme") {

        $feedback_form = $this->container->get('form.factory')->createBuilder()
            ->add('type', HiddenType::class, array('data' => ''))
            ->add('browser', HiddenType::class, array('data' => ''))
            ->add('browser_version', HiddenType::class, array('data' => ''))
            ->add('browser_size', HiddenType::class, array('data' => ''))
            ->add('os', HiddenType::class, array('data' => ''))
            ->add('cookies_enabled', HiddenType::class, array('data' => ''))
            ->add('name', HiddenType::class, array('data' => $name))
            ->add('email', EmailType::class, array('label' => '', 'data' => $email, 'attr' => array('placeholder' => 'back.helpers_form.feedback.email'), 'translation_domain' => 'back_helpers_form'))
            ->add('message', TextareaType::class, array('label' => '', 'attr' => array('placeholder' => 'back.helpers_form.feedback.your_message'), 'translation_domain' => 'back_helpers_form'))
            ->add('submit', SubmitType::class, array('attr' => array('class' => 'orange')))
            ->getForm();

        return $feedback_form;
    }



    /**
     * Function which creates the update profile form
     *
     */

    public function createUpdateProfileForm($first_name = '', $last_name = '', $email = '', $current_locale = 'fr') {

        $update_profile_form = $this->container->get('form.factory')->createBuilder()
            ->add('avatar', FileType::class, array('label' => $this->container->get('translator')->trans('back.controller_app.profile.change_avatar', array(), 'back_controller_app')))
            ->add('first_name', TextType::class, array('label' => '', 'attr' => array('placeholder' => 'back.controller_app.profile.first_name'), 'data' => $first_name))
            ->add('last_name', TextType::class, array('label' => '', 'attr' => array('placeholder' => 'back.controller_app.profile.name'), 'data' => $last_name))
            ->add('email', EmailType::class, array('label' => '', 'attr' => array('placeholder' => 'back.controller_app.profile.email'), 'data' => $email))
            ->add('locale', HiddenType::class, array('data' => $current_locale))
            ->add('submit', SubmitType::class, array(
                    'attr' => array('class' => '')
            ))
            ->getForm();

        return $update_profile_form;
    }



    /**
     * Function which creates the update profile form
     *
     */

    public function createEditNotificationsForm($user_details) {

        if(($user_details->{'user_slack_details'} != NULL) && ($user_details->{'user_slack_details'}->{'webhook_url'} != NULL)) {
            $webhook_url = $user_details->{'user_slack_details'}->{'webhook_url'};
        }
        else {
            $webhook_url = "";
        }

        $edit_notifications_form = $this->container->get('form.factory')->createBuilder()
            ->add('opt_in', HiddenType::class, array('data' => $user_details->{'opt_in'}))
            ->add('mail_notification', HiddenType::class, array('data' => $user_details->{'mail_notification'}))
            ->add('slack_webhook_url', UrlType::class, array('data' => $webhook_url, 'attr' => array('placeholder' => 'https://hooks.slack.com/...')))
            ->add('submit', SubmitType::class, array(
                    'attr' => array('class' => '')
            ))
            ->getForm();

        return $edit_notifications_form;
    }
}
