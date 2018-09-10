<?php

namespace Strime\BackBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;


class AjaxFeedbackController extends Controller
{

    /**
     * @Route("/ajax/feedback/send", name="app_ajax_send_feedback")
     * @Template()
     */
    public function ajaxSendFeedbackAction(Request $request)
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
            $user_name = $user_first_name." ".$user_last_name;
        }
        else {
            $user_first_name = NULL;
            $user_last_name = NULL;
            $user_email = '';
            $user_name = 'Anonyme';
        }

        // Get the form helper
        $form = $this->container->get('strime.back_helpers.form');
        $form->request = $request;

        $feedback_form = $form->createFeedbackBoxForm($user_id, $user_email, $user_name);
        $feedback_form->handleRequest($request);

        if($feedback_form->isSubmitted()) {

            // If the submitted form is valid
            if($feedback_form->isValid()) {

                // Get the data
                $email = $feedback_form->get('email')->getData();
                $message = $feedback_form->get('message')->getData();
                $type = $feedback_form->get('type')->getData();
                $browser = $feedback_form->get('browser')->getData();
                $browser_version = $feedback_form->get('browser_version')->getData();
                $browser_size = $feedback_form->get('browser_size')->getData();
                $os = $feedback_form->get('os')->getData();
                $cookies_enabled = $feedback_form->get('cookies_enabled')->getData();
                $user_agent = $_SERVER['HTTP_USER_AGENT'];
                $locale = $request->getLocale();

                // Set the subject
                if(strcmp($type, "improvement") == 0) {
                    $subject = $this->get('translator')->trans('back.controller_ajax.send_feedback.suggest_improvement', array(), 'back_controller_ajax');
                }
                else {
                    $subject = $this->get('translator')->trans('back.controller_ajax.send_feedback.signal_issue', array(), 'back_controller_ajax');
                }

                // Set the sender
                if($user_name != NULL) {
                    $sender = array( $email => $user_name );
                }
                else {
                    $sender = $email;
                }

                try {

                    // Send the comment by email
                    $message = \Swift_Message::newInstance()
                        ->setSubject('[Feedback] - '.$subject)
                        ->setFrom( $sender )
                        ->setTo( 'contact@strime.io' )
                        ->setBody(
                            $this->renderView(
                                'emails/feedback.html.twig',
                                array(
                                    'subject' => $subject,
                                    'current_year' => date('Y'),
                                    'name' => $user_name,
                                    'message' => $message,
                                    'email' => $email,
                                    'type' => $type,
                                    'browser' => $browser,
                                    'browser_version' => $browser_version,
                                    'browser_size' => $browser_size,
                                    'os' => $os,
                                    'cookies_enabled' => $cookies_enabled,
                                    'user_agent' => $user_agent,
                                    'locale' => $locale
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

                    $json_response = array(
                        'status' => 'success',
                        'message' => $this->get('translator')->trans('back.controller_ajax.send_feedback.message_sent', array(), 'back_controller_ajax')
                    );
                }
                catch (Exception $e) {
                    $json_response = array(
                        'status' => 'danger',
                        'exception' => $e->getMessage(),
                        'message' => $this->get('translator')->trans('back.controller_ajax.send_feedback.error_occured_while_sending_message', array(), 'back_controller_ajax')
                    );
                }
            }
            else {
                $json_response = array(
                    'status' => 'danger',
                    'exception' => 'The form is not valid.',
                    'message' => $this->get('translator')->trans('back.controller_ajax.send_feedback.error_occured_while_sending_message', array(), 'back_controller_ajax'),
                );
            }
        }

        echo json_encode( $json_response );
        die;
    }
}
