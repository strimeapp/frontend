<?php

namespace Strime\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Strime\GlobalBundle\Payment\Payment;

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

class CouponsController extends Controller
{
    /**
     * @Route("/discounts/{action}/{coupon_id}", defaults={"action": NULL, "coupon_id": NULL}, name="admin_discounts")
     * @Template("StrimeAdminBundle:Admin:discounts.html.twig")
     */
    public function discountsAction(Request $request, $action, $coupon_id)
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

        // Create the add tax rate form
        $add_coupon_form = $this->createCouponForm($request, $headers, $strime_api_url);

        // Handle the request and set the result variable
        $add_coupon_form->handleRequest($request);
        $add_coupon_form_results = NULL;

        // Check if the form has been submitted
        if($add_coupon_form->isSubmitted()) {

            // If the submitted form is valid
            if($add_coupon_form->isValid()) {

                // Get the data
                $stripe_id = $add_coupon_form->get('stripe_id')->getData();
                $offers = $add_coupon_form->get('offers')->getData();
                $amount_off = $add_coupon_form->get('amount_off')->getData();
                $percent_off = $add_coupon_form->get('percent_off')->getData();
                $currency = $add_coupon_form->get('currency')->getData();
                $duration = $add_coupon_form->get('duration')->getData();
                $duration_in_months = $add_coupon_form->get('duration_in_months')->getData();
                $redeem_by = $add_coupon_form->get('redeem_by')->getData();
                $max_redemptions = $add_coupon_form->get('max_redemptions')->getData();

                // Check if this ID is free to use
                $endpoint = $strime_api_url."coupon/".strtoupper($stripe_id)."/get";

                // Send the request
                $client = new \GuzzleHttp\Client();
                $json_response = $client->request('GET', $endpoint, [
                    'headers' => $headers,
                    'http_errors' => false
                ]);
                $curl_status = $json_response->getStatusCode();
                $response = json_decode( $json_response->getBody() );

                // If there is already a result with this ID
                if( isset( $response->{'results'}->{'stripe_id'} )) {

                    $this->addFlash(
                        'error',
                        'Cet identifiant est déjà utilisé.'
                    );
                }

                // Check if the data are in the proper format
                elseif((($amount_off != NULL) && !is_numeric($amount_off)) || (($percent_off != NULL) && !is_numeric($percent_off))
                    || (($duration_in_months != NULL) && !is_numeric($duration_in_months))) {

                    $this->addFlash(
                        'error',
                        'Le format des données n\'est pas bon.'
                    );
                }
                else {
                    $stripe_id = strtoupper($stripe_id);
                    $redeem_by = strtotime($redeem_by->format('Y-m-d H:i:s'));
                    $amount_off = (float)$amount_off * 100;
                    $percent_off = (float)$percent_off;
                    $duration_in_months = (int)$duration_in_months;

                    // Create the coupon in Stripe
                    $payment = new Payment;
                    $payment->stripe_secret_key = $this->container->getParameter('stripe_secret_key');
                    $payment->coupon_id = $stripe_id;
                    $payment->amount_off = $amount_off;
                    $payment->percent_off = $percent_off;
                    $payment->currency = $currency;
                    $payment->duration = $duration;
                    $payment->duration_in_months = $duration_in_months;
                    $payment->redeem_by = $redeem_by;
                    $payment->max_redemptions = $max_redemptions;
                    $payment->offers = $offers;
                    $payment->headers = $headers;
                    $payment->strime_api_url = $strime_api_url;
                    $payment_result = $payment->createCoupon();

                    // Return the result
                    if($payment_result) {

                        // Prepare the flash message
                        $this->addFlash(
                            'success',
                            'Le coupon "'.$stripe_id.'" a bien été créée.'
                        );
                    }
                    else {
                        $this->addFlash(
                            'error',
                            'Une erreur est survenue lors de la création de ce coupon.'
                        );
                    }
                }
            }
            else {
                $this->addFlash(
                    'error',
                    'Les données soumises ne sont pas valides.'
                );
            }
        }


        // If there is a request to delete a coupon
        if(isset($action) && (strcmp($action, "delete") == 0) && isset($coupon_id)) {

            // Update the coupon in Stripe.
            $payment = new Payment;
            $payment->stripe_secret_key = $this->container->getParameter('stripe_secret_key');
            $payment->coupon_id = $coupon_id;
            $payment->headers = $headers;
            $payment->strime_api_url = $strime_api_url;
            $payment_result = $payment->deleteCoupon();

            if($payment_result) {

                $this->addFlash(
                    'success',
                    'Le coupon "'.$coupon_id.'" a été supprimé.'
                );
            }
            else {

                $this->addFlash(
                    'error',
                    'Une erreur est survenue lors de la suppression de ce coupon.'
                );
            }

            // Redirect to the route without parameters
            return $this->redirectToRoute('admin_discounts');
        }



        // Get the list of discounts
        $payment = new Payment;
        $payment->stripe_secret_key = $this->container->getParameter('stripe_secret_key');
        $coupons = $payment->getCoupons();
        $coupons_additional_information = $this->getCouponsList($headers, $strime_api_url);

        // Prepare the coupons variable
        if(isset($coupons->{'data'}))
            $coupons = $coupons->{'data'};
        else
            $coupons = NULL;


        return array(
            'body_classes' => 'users',
            'login_form' => $login['login_form'],
            'login_message' => $login['login_message'],
            'forgotten_password_form' => $forgotten_password_form->createView(),
            'add_coupon_form' => $add_coupon_form->createView(),
            "coupons" => $coupons,
            "coupons_additional_information" => $coupons_additional_information
        );
    }




    /***************************************************************/
    /******************     Private functions     ******************/
    /***************************************************************/


    /**
     * Private function which gets the list of offers
     *
     */

    private function getOffersList($headers, $strime_api_url) {

        // Set the endpoint to get the number of contacts registered
        $endpoint = $strime_api_url."offers/get";

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $curl_status = $json_response->getStatusCode();
        $response = json_decode( $json_response->getBody() );

        // Set the variable
        $offers = NULL;

        // If the request was properly executed
        if($curl_status == 200) {
            $offers = $response->{'results'};
        }

        return $offers;
    }


    /**
     * Private function which gets the list of discounts created
     *
     */

    private function getCouponsList($headers, $strime_api_url) {

        // Set the endpoint to get the list of discounts created
        $endpoint = $strime_api_url."coupons/get";

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $curl_status = $json_response->getStatusCode();
        $response = json_decode( $json_response->getBody() );

        // Set the variable
        $coupons = NULL;

        // If the request was properly executed
        if($curl_status == 200) {
            $coupons = $response->{'results'};
        }

        return $coupons;
    }


    /**
     * Private function which creates the form to create a coupon
     *
     */

    private function createCouponForm($request, $headers, $strime_api_url) {

        // Get the list of offers
        $offers = $this->getOffersList($headers, $strime_api_url);

        // Set the offers parameter
        $offers_list = array();

        if($offers != NULL) {
            foreach ($offers as $offer) {
                $offers_list[$offer->{'name'}] = $offer->{'offer_id'};
            }
        }

        // Set the login form
        $add_coupon_form = $this->createFormBuilder()
            ->add('stripe_id', TextType::class, array('label' => '', 'attr' => array('placeholder' => 'QUEBEC0416')))
            ->add('offers', ChoiceType::class, array(
                'choices' => $offers_list,
                'expanded' => TRUE,
                'multiple' => TRUE,
                'choices_as_values' => TRUE
            ))
            ->add('amount_off', NumberType::class, array('label' => '', 'attr' => array('placeholder' => '')))
            ->add('percent_off', NumberType::class, array('label' => '', 'attr' => array('placeholder' => '')))
            ->add('currency', HiddenType::class, array('data' => 'eur'))
            ->add('duration', ChoiceType::class, array(
                'choices' => array(
                    'Illimitée' => 'forever',
                    'Récurrente' => 'repeating',
                    'Une fois' => 'once'
                ),
                'expanded' => FALSE,
                'multiple' => FALSE,
                'choices_as_values' => TRUE
            ))
            ->add('duration_in_months', NumberType::class, array('label' => '', 'attr' => array('placeholder' => 'Si récurrente')))
            ->add('max_redemptions', NumberType::class, array('label' => '', 'attr' => array('placeholder' => '')))
            ->add('redeem_by', DateType::class, array('label' => '', 'data' => new \Datetime("+1 month")))
            ->add('submit', SubmitType::class, array(
                    'attr' => array('class' => '')
            ))
            ->getForm();

        return $add_coupon_form;
    }

}
