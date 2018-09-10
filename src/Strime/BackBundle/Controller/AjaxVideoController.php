<?php

namespace Strime\BackBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class AjaxVideoController extends Controller
{
    /**
     * @Route("/ajax/video/volume/save", name="app_ajax_save_video_volume")
     * @Template()
     */
    public function ajaxSaveVideoVolumeAction(Request $request)
    {
        // Get the session data
        $session = $request->getSession();
        $bag = $session->getBag("attributes");
        $video_volume = $bag->get('video_volume');

        // Get the data
        $new_volume = $request->request->get('volume', NULL);

        if($new_volume != NULL) {

            // Set the data in the session
            $session->set('video_volume', $new_volume);

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



    /**
     * @Route("/video/export/youtube", name="app_video_export_youtube")
     * @Template("StrimeBackBundle:Misc:empty-view.html.twig")
     */
    public function videoExportYoutubeAction(Request $request)
    {
        // Get the session data
        $session = $request->getSession();
        $bag = $session->getBag("attributes");

        // Get the user ID
        $user_id = $bag->get('user_id');

        // Check if the user is logged in or if there is no video passed to the endpoint
        if($user_id == NULL)
            return $this->redirectToRoute('home');

        // Get the video ID
        $video_id = $request->request->get('video_id', NULL);
        $youtube_name = trim( $request->request->get('youtube_name', NULL) );
        $youtube_description = $request->request->get('youtube_description', NULL);
        $youtube_tags = $request->request->get('youtube_tags', NULL);
        $youtube_status = $request->request->get('youtube_status', NULL);

        if($video_id != NULL) {
            if(($youtube_name == NULL) || ($youtube_name == "")) {
                $youtube_name = "Strime";
            }

            // Delete old files
            if(file_exists(__DIR__.'/../../../../web/youtube/'.$video_id.'-name.txt')) {
                unlink(__DIR__.'/../../../../web/youtube/'.$video_id.'-name.txt');
            }
            if(file_exists(__DIR__.'/../../../../web/youtube/'.$video_id.'-description.txt')) {
                unlink(__DIR__.'/../../../../web/youtube/'.$video_id.'-description.txt');
            }
            if(file_exists(__DIR__.'/../../../../web/youtube/'.$video_id.'-tags.txt')) {
                unlink(__DIR__.'/../../../../web/youtube/'.$video_id.'-tags.txt');
            }
            if(file_exists(__DIR__.'/../../../../web/youtube/'.$video_id.'-status.txt')) {
                unlink(__DIR__.'/../../../../web/youtube/'.$video_id.'-status.txt');
            }

            // Save the data in buffer files
            if(($youtube_tags != NULL) && ($youtube_tags != "")) {
                file_put_contents( __DIR__.'/../../../../web/youtube/'.$video_id.'-tags.txt', $youtube_tags );
            }

            if(($youtube_description != NULL) && ($youtube_description != "")) {
                file_put_contents( __DIR__.'/../../../../web/youtube/'.$video_id.'-description.txt', $youtube_description );
            }

            file_put_contents( __DIR__.'/../../../../web/youtube/'.$video_id.'-name.txt', $youtube_name );
            file_put_contents( __DIR__.'/../../../../web/youtube/'.$video_id.'-status.txt', $youtube_status );
        }

        // If a video ID is set, pass it to the session
        if($video_id != NULL) {
            $session->set('video_id', $video_id);
            $session->save();
        }
        else {
            $video_id = $bag->get('video_id');
            $session->remove('video_id');
            $session->save();
        }

        // Get the parameters
        $strime_api_url = $this->container->getParameter('strime_api_url');
        $strime_api_token = $this->container->getParameter('strime_api_token');

        // Set the headers
        $headers = array(
            'Accept' => 'application/json',
            'X-Auth-Token' => $strime_api_token,
            'Content-type' => 'application/json'
        );

        // Use the Google API PHP Client to deal with the upload of the video and the oAuth 2.0 connection.
        // Source: https://developers.google.com/youtube/v3/code_samples/php

        /*
         * You can acquire an OAuth 2.0 client ID and client secret from the
         * Google Developers Console <https://console.developers.google.com/>
         * For more information about using OAuth 2.0 to access Google APIs, please see:
         * <https://developers.google.com/youtube/v3/guides/authentication>
         * Please ensure that you have enabled the YouTube Data API for your project.
         */
        $OAUTH2_CLIENT_ID = $this->container->getParameter('strime_google_api_console_id');
        $OAUTH2_CLIENT_SECRET = $this->container->getParameter('strime_google_api_console_secret');

        // Create the Google Client object
        $google_client = new \Google_Client();
        $google_client->setClientId($OAUTH2_CLIENT_ID);
        $google_client->setClientSecret($OAUTH2_CLIENT_SECRET);
        $google_client->setScopes('https://www.googleapis.com/auth/youtube');

        // Set the redirection URL
        $redirect = filter_var( $this->generateUrl('app_video_export_youtube', array(), UrlGeneratorInterface::ABSOLUTE_URL), FILTER_SANITIZE_URL);
        if(strcmp($this->container->get( 'kernel' )->getEnvironment(), "dev") != 0) {
            $redirect = str_replace('http:', 'https:', $redirect);
        }
        $google_client->setRedirectUri($redirect);

        // Define an object that will be used to make all API requests.
        $youtube = new \Google_Service_YouTube($google_client);

        if(isset($_GET['code'])) {
            if(($session->get('state') !== NULL) && (strval($session->get('state')) !== strval($_GET['state']))) {
                $response = array(
                    "response_code" => 400,
                    "message" => $this->get('translator')->trans('back.controller_app.video_export_youtube.session_state_did_not_match', array(), 'back_controller_app'),
                    "message_code" => "session_state_did_not_match"
                );

                echo json_encode($response);
                die;
            }

            // Authenticate the user
            $google_client->authenticate($_GET['code']);

            // Save the Youtube token
            $youtube_token = $google_client->getAccessToken();
            $youtube_access_token = json_encode( $youtube_token );
            $session->set('youtube_token', $youtube_token);
            $session->save();

            // If the Google ID of the user is not NULL
            // Save it.
            if($youtube_access_token != NULL) {

                // Update the Google information of the user
                $endpoint = $strime_api_url."user/".$user_id."/edit";
                $params = array(
                    'user_youtube_id' => $youtube_access_token
                );

                // Send the request
                $client = new \GuzzleHttp\Client();
                $edit_json_response = $client->request('PUT', $endpoint, [
                    'headers' => $headers,
                    'http_errors' => false,
                    'json' => $params
                ]);

                $edit_curl_status = $edit_json_response->getStatusCode();
                $edit_json_response = json_decode( $edit_json_response->getBody() );

                // If the edit user failed, add an element in the JSON
                if($edit_curl_status != 200) {

                    $response = array(
                        "response_code" => 400,
                        "message" => $this->get('translator')->trans('back.controller_app.video_export_youtube.google_id_couldnt_be_saved', array(), 'back_controller_app'),
                        "message_code" => "google_id_not_saved"
                    );

                    echo json_encode($response);
                    die;
                }
            }

            $this->redirect($redirect);
        }

        if($bag->get('youtube_token') !== NULL) {

            // If the token has been stored in the session and is an object
            if(is_object( $bag->get('youtube_token') )) {

                // Check if the token is still valid
                $token_creation_date = (int)$bag->get('youtube_token')->{'created'};
                $token_lifetime = (int)$bag->get('youtube_token')->{'expires_in'};
                $token_death = $token_creation_date + $token_lifetime;

                // If the token is no more valid, revoke it, and redirect the user to the auth screen
                if($token_death < time()) {
                    $google_client->revokeToken( $bag->get('youtube_token') );
                }

                // If the token is still valid, use it
                else {
                    $google_client->setAccessToken( (array)$bag->get('youtube_token') );
                }
            }

            else {
                $google_client->setAccessToken( $bag->get('youtube_token') );
            }
        }

        // Check to ensure that the access token was successfully acquired.
        if ($google_client->getAccessToken()) {
            try{

                // Get the details of the video
                // Set the endpoint
                $endpoint = $strime_api_url."video/".$video_id."/get";

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
                    $video = $response->{'results'};
                }
                else {
                    $video = NULL;
                }

                if($video == NULL) {

                    $redirect = filter_var( $this->generateUrl('app_video', array('video_id' => $video_id), UrlGeneratorInterface::ABSOLUTE_URL), FILTER_SANITIZE_URL);

                    $response = array(
                        "response_code" => 400,
                        "message" => $this->get('translator')->trans('back.controller_app.video_export_youtube.google_id_couldnt_be_saved', array(), 'back_controller_app'),
                        "message_code" => "video_not_found",
                        "redirect" => $redirect
                    );

                    echo json_encode($response);
                    die;
                }

                // Copy the video locally to be able to upload it to Youtube
                // Set the base path
                $base_path = __DIR__.'/../../../../web/youtube/';

                // Create a folder for this video
                if( !file_exists( $base_path ) )
                    mkdir( $base_path, 0755, TRUE );

                $base_path = realpath( $base_path );

                // Set the path to the local copy
                // $video_path = $base_path . '/' . basename( $video->{'s3_url'} );
                $video_path = $base_path . '/' . basename( $video->{'video_mp4'} );

                // Copy the file locally
                try {
                    // $flag_copy = @copy($video->{'s3_url'}, $video_path);
                    $flag_copy = @copy($video->{'video_mp4'}, $video_path);
                }
                catch (Exception $e) {
                    $response = array(
                        "response_code" => 400,
                        "message" => $this->get('translator')->trans('back.controller_app.video_export_youtube.error_copying_video', array(), 'back_controller_app'),
                        "message_code" => "youtube_error_copying_video"
                    );

                    echo json_encode($response);
                    die;
                }


                // Read the details of the video from the files
                $file_youtube_name = fopen($base_path. '/' . $video_id . '-name.txt', "r");
                $youtube_name = fread($file_youtube_name, filesize($base_path. '/' . $video_id . '-name.txt'));
                fclose($file_youtube_name);

                if(file_exists($base_path. '/' . $video_id . '-description.txt')) {
                    $file_youtube_description = fopen($base_path. '/' . $video_id . '-description.txt', "r");
                    $youtube_description = fread($file_youtube_description, filesize($base_path. '/' . $video_id . '-description.txt'));
                    fclose($file_youtube_description);
                }
                else {
                    $youtube_description = NULL;
                }

                if(file_exists($base_path. '/' . $video_id . '-tags.txt')) {
                    $file_youtube_tags = fopen($base_path. '/' . $video_id . '-tags.txt', "r");
                    $youtube_tags = fread($file_youtube_tags, filesize($base_path. '/' . $video_id . '-tags.txt'));
                    fclose($file_youtube_tags);
                }
                else {
                    $youtube_tags = NULL;
                }

                $file_youtube_status = fopen($base_path. '/' . $video_id . '-status.txt', "r");
                $youtube_status = fread($file_youtube_status, filesize($base_path. '/' . $video_id . '-status.txt'));
                fclose($file_youtube_status);

                // Delete the files
                if(file_exists($base_path. '/' . $video_id . '-name.txt')) {
                    unlink($base_path. '/' . $video_id . '-name.txt');
                }
                if(file_exists($base_path. '/' . $video_id . '-description.txt')) {
                    unlink($base_path. '/' . $video_id . '-description.txt');
                }
                if(file_exists($base_path. '/' . $video_id . '-tags.txt')) {
                    unlink($base_path. '/' . $video_id . '-tags.txt');
                }
                if(file_exists($base_path. '/' . $video_id . '-status.txt')) {
                    unlink($base_path. '/' . $video_id . '-status.txt');
                }

                // Prepare the tags
                $youtube_tags = explode(",", $youtube_tags);
                foreach ($youtube_tags as $key => $value) {
                    $youtube_tags[$key] = trim($value);
                }


                // SEND THE VIDEO
                // Create a snippet with title, description, tags and category ID
                // Create an asset resource and set its snippet metadata and type.
                // This example sets the video's title, description, keyword tags, and
                // video category.
                $snippet = new \Google_Service_YouTube_VideoSnippet();
                $snippet->setTitle( $youtube_name );
                if($youtube_description != NULL) {
                    $snippet->setDescription( $youtube_description );
                }
                if($youtube_tags != NULL) {
                    $snippet->setTags( $youtube_tags );
                }

                // Numeric video category. See
                // https://developers.google.com/youtube/v3/docs/videoCategories/list
                // $snippet->setCategoryId("22");

                // Set the video's status to "public". Valid statuses are "public",
                // "private" and "unlisted".
                $status = new \Google_Service_YouTube_VideoStatus();
                $status->privacyStatus = $youtube_status;

                // Associate the snippet and status objects with a new video resource.
                $youtube_video = new \Google_Service_YouTube_Video();
                $youtube_video->setSnippet($snippet);
                $youtube_video->setStatus($status);

                // Specify the size of each chunk of data, in bytes. Set a higher value for
                // reliable connection as fewer chunks lead to faster uploads. Set a lower
                // value for better recovery on less reliable connections.
                $chunk_size_bytes = 2 * 1024 * 1024;

                // Setting the defer flag to true tells the client to return a request which can be called
                // with ->execute(); instead of making the API call immediately.
                $google_client->setDefer(true);

                // Create a request for the API's videos.insert method to create and upload the video.
                $insert_request = $youtube->videos->insert("status,snippet", $youtube_video);

                // Create a MediaFileUpload object for resumable uploads.
                $media = new \Google_Http_MediaFileUpload(
                    $google_client,
                    $insert_request,
                    'video/*',
                    null,
                    true,
                    $chunk_size_bytes
                );
                $media->setFileSize(filesize($video_path));


                // Read the media file and upload it chunk by chunk.
                $status = false;
                $handle = fopen($video_path, "rb");
                while (!$status && !feof($handle)) {
                  $chunk = fread($handle, $chunk_size_bytes);
                  $status = $media->nextChunk($chunk);
                }

                fclose($handle);

                // If you want to make other calls after the file upload, set setDefer back to false
                $google_client->setDefer(false);

                // Set Youtube video ID
                $youtube_video_id = $status['id'];

                // Save the Youtube ID in the API
                // Set the endpoint
                $endpoint = $strime_api_url."video/".$video_id."/edit";

                // Set the params
                $params = array(
                    'video_youtube_id' => $youtube_video_id
                );

                // Send the request
                $client = new \GuzzleHttp\Client();
                $json_response = $client->request('PUT', $endpoint, [
                    'headers' => $headers,
                    'http_errors' => false,
                    'json' => $params
                ]);
                $curl_status = $json_response->getStatusCode();
                $response = json_decode( $json_response->getBody() );

                // If an error occured during the request
                if($curl_status != 200) {
                    $response = array(
                        "response_code" => 400,
                        "message" => $this->get('translator')->trans('back.controller_app.video_export_youtube.video_youtube_id_not_saved', array("%code%" => $e->getMessage()), 'back_controller_app'),
                        "message_code" => "youtube_id_not_saved"
                    );

                    echo json_encode($response);
                }

                // If the request was properly executed
                else {

                    // If we arrived here after a redirection from Google, reload the video page
                    if(isset($_GET['code'])) {

                        $redirect = filter_var( $this->generateUrl('app_video', array('video_id' => $video_id), UrlGeneratorInterface::ABSOLUTE_URL), FILTER_SANITIZE_URL);
                        return $this->redirect( $redirect );
                    }

                    // Else return a JSON
                    else {
                        $response = array(
                            "response_code" => 200,
                            "message" => $this->get('translator')->trans('back.controller_app.video_export_youtube.video_exported', array(), 'back_controller_app')
                        );

                        echo json_encode($response);
                    }
                }

                // Remove the temporary video file
                unlink($video_path);
                die;
            }
            catch (\Google_Service_Exception $e) {
                $response = array(
                    "response_code" => 400,
                    "message" => $this->get('translator')->trans('back.controller_app.video_export_youtube.a_service_error_occured', array("%code%" => $e->getMessage()), 'back_controller_app'),
                    "message_code" => "youtube_error"
                );

                echo json_encode($response);
                die;
            }
            catch (\Google_Exception $e) {
                $response = array(
                    "response_code" => 400,
                    "message" => $this->get('translator')->trans('back.controller_app.video_export_youtube.a_client_error_occured', array("%code%" => $e->getMessage()), 'back_controller_app'),
                    "message_code" => "client_error"
                );

                echo json_encode($response);
                die;
            }
        }
        else {
            // If the user hasn't authorized the app, initiate the OAuth flow
            $state = mt_rand();
            $google_client->setState($state);
            $session->set('state', $state);
            $session->save();
            $auth_url = $google_client->createAuthUrl();

            $response = array(
                "response_code" => 302,
                "redirect" => $auth_url
            );

            echo json_encode($response);
            die;
        }
    }
}
