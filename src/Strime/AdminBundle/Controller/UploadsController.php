<?php

namespace Strime\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Strime\GlobalBundle\Payment\Payment;

use Strime\GlobalBundle\Entity\Upload;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class UploadsController extends Controller
{
    /**
     * @Route("/uploads/{action}/{content_id}", defaults={"action": NULL, "content_id": NULL}, name="admin_uploads")
     * @Template("StrimeAdminBundle:Admin:uploads.html.twig")
     */
    public function uploadsAction(Request $request, $action, $content_id)
    {
        // Get the session data
        $session = $request->getSession();
        $bag = $session->getBag("attributes");

        // Check if the user is logged in
        if(($bag->get('user_id') == NULL) || (strcmp($bag->get('role'), "admin") != 0))
            return $this->redirectToRoute('home');

        // Create the form object
        $form = $this->container->get('strime.helpers.form');
        $form->request = $request;

        // Create the login form
        $login_form = $form->createLoginForm();

        // Prepare messages for the login form
        $login = $form->prepareLoginMessages($login_form);

        // Create the forgotten password form
        $forgotten_password_form = $form->createForgottenPasswordForm();

        // Get the parameters
        $strime_api_url = $this->container->getParameter('strime_api_url');
        $strime_api_token = $this->container->getParameter('strime_api_token');

        // Set the headers
        $headers = array(
            'Accept' => 'application/json',
            'X-Auth-Token' => $strime_api_token,
            'Content-type' => 'application/json'
        );

        // Get the entity manager
        $em = $this->getDoctrine()->getManager();

        // If there is a request to delete an upload
        if(isset($action) && isset($content_id) && (strcmp($action, "delete") == 0)) {
            $upload = new Upload;
            $upload = $em->getRepository('StrimeGlobalBundle:Upload')->findOneBy(array('id' => $content_id));

            // If the upload is defined, remove it
            if($upload != NULL) {
                $em->remove( $upload );
                $em->flush();

                $this->addFlash(
                    'success',
                    'Cet upload a été annulé.'
                );
            }
            else {

                $this->addFlash(
                    'error',
                    'Une erreur est survenue lors de l\'annulation de cet upload.'
                );
            }

            // Redirect to the route without parameters
            return $this->redirectToRoute('admin_uploads');
        }

        // Count the number of current uploads
        $nb_uploads = $this->countNbCurrentUploads();

        // Get the list of uploads
        if($nb_uploads > 0) {
            $uploads = new Upload;
            $uploads = $em->getRepository('StrimeGlobalBundle:Upload')->findAll();

            if($uploads != NULL) {

                // For each upload, get the name of the user
                foreach ($uploads as $upload) {

                    // Send the request to get the details of the user
                    // Set the endpoint
                    $endpoint = $strime_api_url."user/".$upload->getUserId()."/get";

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

                        // Save the user name
                        $upload->{'user_name'} = $response->{'results'}->{'first_name'}." ".$response->{'results'}->{'last_name'};
                    }
                }
            }
        }
        else {
            $uploads = NULL;
        }

        return array(
            'body_classes' => 'dashboard',
            'login_form' => $login['login_form'],
            'login_message' => $login['login_message'],
            'forgotten_password_form' => $forgotten_password_form->createView(),
            "nb_uploads" => $nb_uploads,
            "uploads" => $uploads,
        );
    }




    /***************************************************************/
    /******************     Private functions     ******************/
    /***************************************************************/


    /**
     * Private function which gets the total number of uploads
     *
     */

    private function countNbCurrentUploads() {

        // Get the entity manager
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQueryBuilder();
        $query->select( 'count(app_upload.id)' );
        $query->from( 'StrimeGlobalBundle:Upload','app_upload' );
        $nb_uploads = $query->getQuery()->getSingleScalarResult();

        return $nb_uploads;
    }

}
