<?php

namespace Strime\BackBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use Strime\GlobalBundle\Entity\Upload;


class AjaxUploadController extends Controller
{
    /**
     * @Route("/ajax/upload", name="app_ajax_upload")
     * @Template()
     */
    public function ajaxUploadAction(Request $request)
    {

        // Create the signup form
        $upload_form = $this->createFormBuilder()->getForm();
        $upload_form->handleRequest($request);

        if($upload_form->isSubmitted()) {

            // If the submitted form is valid
            if($upload_form->isValid()) {

                // Get the session data
                $session = $request->getSession();
                $bag = $session->getBag("attributes");
                $user_id = $bag->get('user_id');

                // Get the data
                $asset = $request->files->get('asset', NULL);

                // Set parameters
                $folder =  __DIR__.'/../../../../web/uploads/assets/';
                $name = "";

                foreach($request->files as $uploadedFile) {

                    // Get the original filename
                    $asset_name = $uploadedFile->getClientOriginalName();

                    // Explode the filename to get the extension
                    $asset_name_parts = explode(".", $asset_name);
                    $asset_extension = $asset_name_parts[ count($asset_name_parts) - 1 ];

                    // Set the filename
                    $name = $user_id . "." . $asset_extension;

                    // Check if the file exists, if yes, we delete it
                    if( file_exists($folder.$name) )
                        unlink($folder.$name);

                    // Upload the file
                    $file = $uploadedFile->move($folder, $name);
                }

                // Return the result
                echo json_encode(array("status" => "success", "asset" => $name, "user_id" => $user_id));
                die;
            }

            // If the submitted form is not valid
            else {

                // Return the result
                echo json_encode(array("status" => "error"));
                die;
            }
        }

        // If the form has not been submitted
        else {

            // Return the result
            echo json_encode(array("status" => "error"));
            die;
        }
    }



    /**
     * @Route("/ajax/upload/add", name="app_ajax_add_upload")
     * @Template()
     */
    public function ajaxAddUploadAction(Request $request)
    {
        // Get the session data
        $session = $request->getSession();
        $bag = $session->getBag("attributes");
        $user_id = $bag->get('user_id');

        if($user_id != NULL) {

            // Get the entity manager
            $em = $this->getDoctrine()->getManager();

            // Create the upload in the database
            $upload = new Upload;
            $upload->setUserID( $user_id );
            $em->persist( $upload );
            $em->flush();

            echo json_encode(array("status" => "success"));
            die;
        }
        else {

            // The user is probably not connected or disconnected.
            // Generate an error
            echo json_encode(array("status" => "error"));
            die;
        }
    }



    /**
     * @Route("/ajax/upload/delete", name="app_ajax_delete_upload")
     * @Template()
     */
    public function ajaxDeleteUploadAction(Request $request)
    {
        // Get the session data
        $session = $request->getSession();
        $bag = $session->getBag("attributes");
        $user_id = $bag->get('user_id');

        // Get the entity manager
        $em = $this->getDoctrine()->getManager();

        // Get the upload for the current user
        $upload = new Upload;
        $upload = $em->getRepository('StrimeGlobalBundle:Upload')->findOneBy(array('user_id' => $user_id));

        if($upload != NULL) {
            $em->remove( $upload );
            $em->flush();
        }

        echo json_encode(array("status" => "success"));
        die;
    }
}
