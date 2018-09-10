<?php

namespace Strime\BackBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class AjaxAudioController extends Controller
{
    /**
     * @Route("/ajax/audio/volume/save", name="app_ajax_save_audio_volume")
     * @Template()
     */
    public function ajaxSaveAudioVolumeAction(Request $request)
    {
        // Get the session data
        $session = $request->getSession();
        $bag = $session->getBag("attributes");
        $video_volume = $bag->get('audio_volume');

        // Get the data
        $new_volume = $request->request->get('volume', NULL);

        if($new_volume != NULL) {

            // Set the data in the session
            $session->set('audio_volume', $new_volume);

            echo json_encode(array("status" => "success"));
            die;
        }
        else {

            // An error occured while passing the new value.
            // Generate an error
            echo json_encode(array("status" => "error"));
            die;
        }
    }
}
