<?php

namespace Strime\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Strime\GlobalBundle\Invoice\Invoice;
use Strime\GlobalBundle\Payment\Payment;
use Strime\Slackify\Webhooks\Webhook;

use Strime\GlobalBundle\Entity\TaxRate;

class StripeController extends Controller
{
    /**
     * @Route("/stripe/check-payment", name="admin_stripe_check_payment")
     * @Template()
     */
    public function checkPaymentAction(Request $request)
    {
        // Set your secret key: remember to change this to your live secret key in production
        // See your keys here https://dashboard.stripe.com/account/apikeys
        $stripe_secret_key = $this->container->getParameter('stripe_secret_key');
        \Stripe\Stripe::setApiKey($stripe_secret_key);

        // Retrieve the request's body and parse it as JSON
        $input = @file_get_contents("php://input");
        $event_json = json_decode($input);

        // Check that the event received has an ID
        if(isset($event_json->id)) {

            // Check that the event received is a proper Stripe event
            $event = \Stripe\Event::retrieve($event_json->id);

            // In case the payment failed
            if (isset($event) && $event->type == "invoice.payment_failed") {
                $customer = \Stripe\Customer::retrieve($event->data->object->customer);
                $email = $customer->email;
                $customer_id = $customer->id;
                // Sending your customers the amount in pennies is weird, so convert to dollars
                $amount = sprintf('%0.2f', $event->data->object->amount_due / 100.0);

                $this->paymentFailureAction($customer_id, $email, $amount);
            }

            // In case the payment succeedeed
            elseif (isset($event) && $event->type == "customer.subscription.deleted") {
                $customer = \Stripe\Customer::retrieve($event->data->object->customer);
                $email = $customer->email;
                $customer_id = $customer->id;

                $this->cancelSubscriptionAction($customer_id, $email);
            }

            // In case the payment succeedeed
            elseif (isset($event) && $event->type == "invoice.payment_succeeded") {
                $customer = \Stripe\Customer::retrieve($event->data->object->customer);
                $email = $customer->email;
                $customer_id = $customer->id;
                // Sending your customers the amount in pennies is weird, so convert to dollars
                $amount = sprintf('%0.2f', $event->data->object->amount_due / 100.0);
                $current_period_start = $event->data->object->lines->data[0]->period->start;
                $current_period_end = $event->data->object->lines->data[0]->period->end;

                $this->paymentSuccessAction($customer_id, $email, $amount, $current_period_start, $current_period_end);
            }

            // If the customer's info changes
            elseif (isset($event) && $event->type == "customer.source.updated") {

                // For now, do nothing
            }
        }

        http_response_code(200); // PHP 5.4 or greater
        $json = json_encode(array("status" => "success"));
        return new JsonResponse($json, 200);
        exit;
    }



    /**
     * @Route("/stripe/check-tls", name="admin_stripe_check_tls")
     * @Template("StrimeAdminBundle:Admin:stripe-check-tls.html.twig")
     */
    public function checkTLSAction(Request $request)
    {
        return $this->redirectToRoute('home');

        // Set your secret key: remember to change this to your live secret key in production
        // See your keys here https://dashboard.stripe.com/account/apikeys
        /*$stripe_secret_key = $this->container->getParameter('stripe_secret_key');

        $payment = new Payment;
        $payment->stripe_secret_key = $stripe_secret_key;
        $support_tls = $payment->supportTLS();

        return array(
            "support_tls" => $support_tls
        );*/
    }




    /***************************************************************/
    /******************     Private functions     ******************/
    /***************************************************************/


    /**
     * Private function which is triggered if there is a payment error on stripe on a recurring payment
     *
     */

    private function paymentFailureAction($customer_id, $email, $amount) {

        // Set the API variables
        $strime_api_url = $this->container->getParameter('strime_api_url');
        $strime_api_token = $this->container->getParameter('strime_api_token');

        // Set the headers
        $headers = array(
            'Accept' => 'application/json',
            'X-Auth-Token' => $strime_api_token,
            'Content-type' => 'application/json'
        );

        // Get the user details
        // Set the endpoint
        $endpoint = $strime_api_url."user/".$customer_id."/get-by-stripe-id";

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $user_curl_status = $json_response->getStatusCode();
        $response = json_decode( $json_response->getBody() );

        if($user_curl_status == 200) {

            // Get the details of the user
            $user = $response->{'results'};
        }
        else {

            // Send an email to the team
            $message = \Swift_Message::newInstance()
                ->setSubject('Strime - Erreur : utilisateur non reconnu suite à une erreur de paiement')
                ->setFrom( array('technique@strime.io' => 'Strime') )
                ->setTo( array('contact@strime.io' => 'Strime') )
                ->setBody(
                    $this->renderView(
                        'emails/stripe-error.html.twig',
                        array(
                            'current_year' => date('Y'),
                            'name' => "Unknown",
                            'stripe_id' => $customer_id,
                            'email' => $email,
                            'error_type' => "Erreur de paiement. Le client aurait dû repasser sur l'offre gratuite mais nous n'avons pas pu l'identifier.",
                            'todo' => "Vérifier que le client existe. Si oui, forcer son passage sur l'offre gratuite et lui envoyer un mail. Si non, mettre à jour Stripe en désactivant son abonnement.",
                            'time_period' => $first_day." - ".$last_day,
                            'amount' => $amount,
                            'subject' => 'utilisateur non reconnu suite à une erreur de paiement',
                            'curl_status' => $user_curl_status,
                        )
                    ),
                    'text/html'
                )
            ;
            $this->get('mailer')->send($message);
            $transport = $this->container->get('mailer')->getTransport();
            $spool = $transport->getSpool();
            $spool->flushQueue($this->container->get('swiftmailer.transport.real'));

            // Send a message to Slack
            $slack_webhook = new Webhook( $this->container->getParameter('slack_strime_billing_channel') );
            $slack_webhook->sendMessage(array(
                "message" => "Erreur : utilisateur non reconnu suite à une erreur de paiement.\nStripe customer ID: ".$customer_id.".",
                "username" => "[".ucfirst( $this->container->getParameter("kernel.environment") )."] Strime.io",
                "icon" => ":shit:"
            ));

            return FALSE;
        }

        // Get the details of the offers
        // Set the endpoint
        $endpoint = $strime_api_url."offers/get";

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $curl_status = $json_response->getStatusCode();
        $response = json_decode( $json_response->getBody() );

        // Get the free offer ID
        $offer_id = NULL;

        // If we got the list of offers
        if($curl_status == 200) {

            // Get the ID of the free offer
            foreach ($response->{'results'} as $offer) {
                if(($offer->{'price'} == 0) && (strcmp(strtolower($offer->{'name'}), "gratuite") == 0))
                    $offer_id = $offer->{'offer_id'};
            }

            // Change the offer of the user to the free offer
            // Set the endpoint
            $endpoint = $strime_api_url."user/".$user->{'user_id'}."/edit";

            // Prepare the parameters
            $params = array(
                'offer_id' => (string)$offer_id
            );

            // Send the request
            $client = new \GuzzleHttp\Client();
            $json_response = $client->request('PUT', $endpoint, [
                'headers' => $headers,
                'http_errors' => false,
                'json' => $params
            ]);
            $edit_user_curl_status = $json_response->getStatusCode();
            $edit_user_json_response = $json_response->getBody();

            // If the offer has been changed
            if($edit_user_curl_status == 200) {

                // Send an email to the user
                $message = \Swift_Message::newInstance()
                    ->setSubject('Strime - Erreur de paiement')
                    ->setFrom( array('contact@strime.io' => 'Strime') )
                    ->setTo( array( $user->{'email'} => $user->{'first_name'}." ".$user->{'last_name'} ) )
                    ->setBody(
                        $this->renderView(
                            'emails/payment-failure.html.twig',
                            array(
                                'current_year' => date('Y'),
                                'first_day' => $first_day,
                                'last_day' => $last_day,
                                'first_name' => $user->{'first_name'}
                            )
                        ),
                        'text/html'
                    )
                ;
                $this->get('mailer')->send($message);
                $transport = $this->container->get('mailer')->getTransport();
                $spool = $transport->getSpool();
                $spool->flushQueue($this->container->get('swiftmailer.transport.real'));

                // Send a message to Slack
                $slack_webhook = new Webhook( $this->container->getParameter('slack_strime_billing_channel') );
                $slack_webhook->setAttachments(
                    array(
                        array(
                            "fallback" => "Présentation de l'utilisateur",
                            "text" => "ID: ".$user->{'user_id'}."\nEmail: ".$user->{'email'},
                            "color" => "warning",
                            "author" => array(
                                "author_name" => "Mr Robot"
                            ),
                            "title" => $user->{'first_name'}." ".$user->{'last_name'},
                            "fields" => array(
                                "title" => $user->{'first_name'}." ".$user->{'last_name'},
                                "value" => "ID: ".$user->{'user_id'}."\nEmail: ".$user->{'email'},
                                "short" => FALSE
                            ),
                            "footer_icon" => "https://www.strime.io/bundles/strimeglobal/img/icon-strime.jpg",
                            "ts" => time()
                        )
                    )
                );
                $slack_webhook->sendMessage(array(
                    "message" => "Erreur : un utilisateur a été repassé sur une offre gratuite suite à une erreur de paiement.\nStripe customer ID: ".$customer_id."\nNom du client : ".$user->{'first_name'}." ".$user->{'last_name'},
                    "username" => "[".ucfirst( $this->container->getParameter("kernel.environment") )."] Strime.io",
                    "icon" => ":broken_heart:"
                ));
            }

            // If the offer has not been changed
            else {

                // Send an email to the team
                $message = \Swift_Message::newInstance()
                    ->setSubject('Strime - Erreur : utilisateur non mis à jour suite à une erreur de paiement')
                    ->setFrom( array('technique@strime.io' => 'Strime') )
                    ->setTo( array('contact@strime.io' => 'Strime') )
                    ->setBody(
                        $this->renderView(
                            'emails/stripe-error.html.twig',
                            array(
                                'current_year' => date('Y'),
                                'name' => $user->{'first_name'}." ".$user->{'first_name'},
                                'email' => $user->{'email'},
                                'error_type' => "Erreur de paiement. Le client aurait dû repasser sur l'offre gratuite mais une erreur s'est produite dans la mise à jour de son offre.",
                                'todo' => "Forcer son passage à l'offre gratuite et lui envoyer un mail.",
                                'time_period' => $first_day." - ".$last_day,
                                'amount' => $amount,
                                'subject' => 'utilisateur non mis à jour suite à une erreur de paiement',
                                'curl_status' => $edit_user_curl_status,
                            )
                        ),
                        'text/html'
                    )
                ;
                $this->get('mailer')->send($message);
                $transport = $this->container->get('mailer')->getTransport();
                $spool = $transport->getSpool();
                $spool->flushQueue($this->container->get('swiftmailer.transport.real'));

                $slack_webhook = new Webhook( $this->container->getParameter('slack_strime_billing_channel') );
                $slack_webhook->setAttachments(
                    array(
                        array(
                            "fallback" => "Présentation de l'utilisateur",
                            "text" => "ID: ".$user->{'user_id'}."\nEmail: ".$user->{'email'},
                            "color" => "danger",
                            "author" => array(
                                "author_name" => "Mr Robot"
                            ),
                            "title" => $user->{'first_name'}." ".$user->{'last_name'},
                            "fields" => array(
                                "title" => $user->{'first_name'}." ".$user->{'last_name'},
                                "value" => "ID: ".$user->{'user_id'}."\nEmail: ".$user->{'email'},
                                "short" => FALSE
                            ),
                            "footer_icon" => "https://www.strime.io/bundles/strimeglobal/img/icon-strime.jpg",
                            "ts" => time()
                        )
                    )
                );
                $slack_webhook->sendMessage(array(
                    "message" => "Erreur : un utilisateur aurait dû repasser sur une offre gratuite suite à une erreur de paiement, mais ce changement d'offre n'a pas fonctionné.\nStripe customer ID: ".$customer_id."\nNom du client : ".$user->{'first_name'}." ".$user->{'last_name'},
                    "username" => "[".ucfirst( $this->container->getParameter("kernel.environment") )."] Strime.io",
                    "icon" => ":shit:"
                ));

                return FALSE;
            }
        }

        // If we got an error getting the list of offers
        else {

            // Send an email to the team
            $message = \Swift_Message::newInstance()
                ->setSubject('Strime - Erreur : utilisateur non mis à jour suite à une erreur de paiement')
                ->setFrom( array('technique@strime.io' => 'Strime') )
                ->setTo( array('contact@strime.io' => 'Strime') )
                ->setBody(
                    $this->renderView(
                        'emails/stripe-error.html.twig',
                        array(
                            'current_year' => date('Y'),
                            'name' => $user->{'first_name'}." ".$user->{'first_name'},
                            'email' => $user->{'email'},
                            'error_type' => "Erreur de paiement. Le client aurait dû repasser sur l'offre gratuite mais une erreur s'est produite dans la récupération des offres.",
                            'todo' => "Forcer son passage à l'offre gratuite et lui envoyer un mail.",
                            'time_period' => $first_day." - ".$last_day,
                            'amount' => $amount,
                            'subject' => 'utilisateur non mis à jour suite à une erreur de paiement',
                            'curl_status' => $curl_status,
                        )
                    ),
                    'text/html'
                )
            ;
            $this->get('mailer')->send($message);
            $transport = $this->container->get('mailer')->getTransport();
            $spool = $transport->getSpool();
            $spool->flushQueue($this->container->get('swiftmailer.transport.real'));

            // Send a message to Slack
            $slack_webhook = new Webhook( $this->container->getParameter('slack_strime_billing_channel') );
            $slack_webhook->setAttachments(
                array(
                    array(
                        "fallback" => "Présentation de l'utilisateur",
                        "text" => "ID: ".$user->{'user_id'}."\nEmail: ".$user->{'email'},
                        "color" => "danger",
                        "author" => array(
                            "author_name" => "Mr Robot"
                        ),
                        "title" => $user->{'first_name'}." ".$user->{'last_name'},
                        "fields" => array(
                            "title" => $user->{'first_name'}." ".$user->{'last_name'},
                            "value" => "ID: ".$user->{'user_id'}."\nEmail: ".$user->{'email'},
                            "short" => FALSE
                        ),
                        "footer_icon" => "https://www.strime.io/bundles/strimeglobal/img/icon-strime.jpg",
                        "ts" => time()
                    )
                )
            );
            $slack_webhook->sendMessage(array(
                "message" => "Erreur : un client aurait dû repasser sur l'offre gratuite mais une erreur s'est produite dans la récupération des offres.\nStripe customer ID: ".$customer_id."\nNom du client : ".$user->{'first_name'}." ".$user->{'last_name'},
                "username" => "[".ucfirst( $this->container->getParameter("kernel.environment") )."] Strime.io",
                "icon" => ":shit:"
            ));

            return FALSE;
        }
    }




    /**
     * Private function which is triggered if there is a payment success on stripe on a recurring payment
     *
     */

    private function paymentSuccessAction($customer_id, $email, $amount, $current_period_start, $current_period_end) {

        // Set the API variables
        $strime_api_url = $this->container->getParameter('strime_api_url');
        $strime_api_token = $this->container->getParameter('strime_api_token');

        // Set the headers
        $headers = array(
            'Accept' => 'application/json',
            'X-Auth-Token' => $strime_api_token,
            'Content-type' => 'application/json'
        );

        // Set the dates
        $first_day = new \DateTime('@'.$current_period_start);
        $last_day = new \DateTime('@'.$current_period_end);
        $plan_start_date = $first_day->format('d/m/Y');
        $plan_end_date = $last_day->format('d/m/Y');

        // Get the user details
        // Set the endpoint
        $endpoint = $strime_api_url."user/".$customer_id."/get-by-stripe-id";

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $user_curl_status = $json_response->getStatusCode();
        $response = json_decode( $json_response->getBody() );

        if($user_curl_status == 200) {

            // Get the details of the user
            $user = $response->{'results'};
        }
        else {

            // Send an email to the team
            $message = \Swift_Message::newInstance()
                ->setSubject('Strime - Erreur : utilisateur non reconnu suite à un paiement')
                ->setFrom( array('technique@strime.io' => 'Strime') )
                ->setTo( array('contact@strime.io' => 'Strime') )
                ->setBody(
                    $this->renderView(
                        'emails/stripe-error.html.twig',
                        array(
                            'current_year' => date('Y'),
                            'name' => 'Unknown',
                            'stripe_id' => $customer_id,
                            'email' => $email,
                            'error_type' => "Erreur suite à un paiement. Le client aurait dû recevoir un mail avec sa facture.",
                            'todo' => "Vérifier que le client existe. Si oui, forcer la génération de sa facture et la lui envoyer par mail. Si non, mettre à jour Stripe en désactivant son abonnement.",
                            'time_period' => $plan_start_date." - ".$plan_end_date,
                            'amount' => $amount,
                            'subject' => 'utilisateur non reconnu suite à un paiement',
                            'curl_status' => $user_curl_status,
                        )
                    ),
                    'text/html'
                )
            ;
            $this->get('mailer')->send($message);
            $transport = $this->container->get('mailer')->getTransport();
            $spool = $transport->getSpool();
            $spool->flushQueue($this->container->get('swiftmailer.transport.real'));

            // Send a message to Slack
            $slack_webhook = new Webhook( $this->container->getParameter('slack_strime_billing_channel') );
            $slack_webhook->sendMessage(array(
                "message" => "Erreur : un client aurait dû recevoir un mail avec sa facture.\nVérifier que le client existe. Si oui, forcer la génération de sa facture et la lui envoyer par mail. Si non, mettre à jour Stripe en désactivant son abonnement.\nStripe customer ID: ".$customer_id."\nEmail du client : ".$email,
                "username" => "[".ucfirst( $this->container->getParameter("kernel.environment") )."] Strime.io",
                "icon" => ":shit:"
            ));

            return FALSE;
        }


        /***/
        // Invoice generation
        /***/


        // Get the invoice rate
        $em = $this->getDoctrine()->getManager();
        $tax_rate_details = new TaxRate;
        $tax_rate_details = $em->getRepository('StrimeGlobalBundle:TaxRate')->findOneBy(array('country_code' => $user->{'country'}));
        $taxes_liquidation = NULL;
        $taxes_liquidation_rate = NULL;

        // If the user is in a country with a tax rate and doesn't have a VAT number
        if((($tax_rate_details != NULL) && (strcmp($user->{'country'}, "FR") == 0))
            || (($tax_rate_details != NULL) && (strcmp($user->{'country'}, "FR") != 0) && ($user->{'vat_number'} == NULL))) {

            $tax_rate = $tax_rate_details->getTaxRate();
        }
        elseif(($tax_rate_details != NULL) && (strcmp($user->{'country'}, "FR") != 0) && ($user->{'vat_number'} != NULL)) {
            $tax_rate = 0;
            $taxes_liquidation_rate = -(int)$tax_rate_details->getTaxRate();
        }
        else {
            $tax_rate = 0;
        }


        // Set the variables for the invoice
        if($taxes_liquidation_rate != NULL) {
            $amount_wo_taxes = round( $amount / ( 1 + (-$taxes_liquidation_rate / 100) ) , 2, PHP_ROUND_HALF_DOWN);
            $taxes = round( ($amount - $amount_wo_taxes), 2, PHP_ROUND_HALF_DOWN);
            $taxes_liquidation = -$taxes;

            // If the user is part of the EU and has a VAT number,
            // The amound_wo_taxes should be the same than the total amount
            if(($tax_rate_details != NULL) && (strcmp($user->{'country'}, "FR") != 0) && ($user->{'vat_number'} != NULL)) {
                $amount_wo_taxes = $amount;
            }
        }
        else {
            $amount_wo_taxes = round( $amount / ( 1 + ($tax_rate / 100) ) , 2, PHP_ROUND_HALF_DOWN);
            $taxes = round( ($amount - $amount_wo_taxes), 2, PHP_ROUND_HALF_DOWN);
        }
        $total_amount = $amount;

        // Generate the invoice
        $invoice = $this->container->get('strime.invoice');
        $invoice->strime_api_url = $strime_api_url;
        $invoice->headers = $headers;
        $invoice->user_id = $user->{'user_id'};
        $invoice->total_amount = $total_amount;
        $invoice->amount_wo_taxes = $amount_wo_taxes;
        $invoice->taxes = $taxes;
        $invoice->tax_rate = $tax_rate;
        $invoice->company = $user->{'company'};
        $invoice->vat_number = $user->{'vat_number'};
        $invoice->address = $user->{'address'};
        $invoice->address_more = $user->{'address_more'};
        $invoice->zip = $user->{'zip'};
        $invoice->city = $user->{'city'};
        $invoice->country = $user->{'country'};
        $invoice->contact = $user->{'first_name'}." ".$user->{'last_name'};
        $invoice->plan = $user->{'offer'}->{'name'};
        $invoice->plan_nb_occurrences = 1;
        $invoice->plan_occurrence = "mois";
        $invoice->unit_price = $amount_wo_taxes;
        $invoice->total_price = $amount_wo_taxes;
        $invoice->plan_start_date = $plan_start_date;
        $invoice->plan_end_date = $plan_end_date;
        $invoice->year = date('Y', $current_period_start);
        $invoice->month = date('m', $current_period_start);
        $invoice->day = date('d', $current_period_start);
        $invoice->date = date("d/m/Y", $current_period_start);

        if($taxes_liquidation != NULL)
            $invoice->taxes_liquidation = $taxes_liquidation;

        if($taxes_liquidation_rate != NULL)
            $invoice->taxes_liquidation_rate = $taxes_liquidation_rate;

        $path_to_invoice = $invoice->generateInvoice();

        if(!is_array($path_to_invoice)) {

            // Send an email with the invoice
            $message = \Swift_Message::newInstance()
                ->setSubject('Strime - Votre facture')
                ->setFrom( array('contact@strime.io' => 'Strime') )
                ->setTo( array( $user->{'email'} => $user->{'first_name'}." ".$user->{'last_name'} ) )
                ->setBody(
                    $this->renderView(
                        'emails/invoice.html.twig',
                        array(
                            'current_year' => date('Y'),
                            'first_day' => $first_day,
                            'last_day' => $last_day,
                            'first_name' => $user->{'first_name'}
                        )
                    ),
                    'text/html'
                )
                ->attach(\Swift_Attachment::fromPath($path_to_invoice))
            ;
            $this->get('mailer')->send($message);
            $transport = $this->container->get('mailer')->getTransport();
            $spool = $transport->getSpool();
            $spool->flushQueue($this->container->get('swiftmailer.transport.real'));

            // Send a message to Slack
            $slack_webhook = new Webhook( $this->container->getParameter('slack_strime_billing_channel') );
            $slack_webhook->setAttachments(
                array(
                    array(
                        "fallback" => "Présentation de l'utilisateur",
                        "text" => "ID: ".$user->{'user_id'}."\nEmail: ".$user->{'email'},
                        "color" => "good",
                        "author" => array(
                            "author_name" => "Mr Robot"
                        ),
                        "title" => $user->{'first_name'}." ".$user->{'last_name'},
                        "fields" => array(
                            "title" => $user->{'first_name'}." ".$user->{'last_name'},
                            "value" => "ID: ".$user->{'user_id'}."\nEmail: ".$user->{'email'},
                            "short" => FALSE
                        ),
                        "footer_icon" => "https://www.strime.io/bundles/strimeglobal/img/icon-strime.jpg",
                        "ts" => time()
                    )
                )
            );
            $slack_webhook->sendMessage(array(
                "message" => "Renouvellement d'abo : un client vient de voir son abonnement renouvelé.\nStripe customer ID: ".$customer_id."\nEmail du client : ".$user->{'email'}."\nNom du client : ".$user->{'first_name'}." ".$user->{'last_name'},
                "username" => "[".ucfirst( $this->container->getParameter("kernel.environment") )."] Strime.io",
                "icon" => ":metal:"
            ));
        }
    }


    /**
     * Private function which is triggered if Stripe cancels a subscription due to payment failure
     *
     */

    private function cancelSubscriptionAction($customer_id, $email) {

        // Set the API variables
        $strime_api_url = $this->container->getParameter('strime_api_url');
        $strime_api_token = $this->container->getParameter('strime_api_token');

        // Set the headers
        $headers = array(
            'Accept' => 'application/json',
            'X-Auth-Token' => $strime_api_token,
            'Content-type' => 'application/json'
        );

        // Get the user details
        // Set the endpoint
        $endpoint = $strime_api_url."user/".$customer_id."/get-by-stripe-id";

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $user_curl_status = $json_response->getStatusCode();
        $response = json_decode( $json_response->getBody() );

        if($user_curl_status == 200) {

            // Get the details of the user
            $user = $response->{'results'};
        }
        else {

            // Send an email to the team
            $message = \Swift_Message::newInstance()
                ->setSubject('Strime - Erreur : utilisateur non reconnu suite à une erreur de paiement')
                ->setFrom( array('technique@strime.io' => 'Strime') )
                ->setTo( array('contact@strime.io' => 'Strime') )
                ->setBody(
                    $this->renderView(
                        'emails/stripe-error.html.twig',
                        array(
                            'current_year' => date('Y'),
                            'name' => "Unknown",
                            'stripe_id' => $customer_id,
                            'email' => $email,
                            'error_type' => "Erreur de paiement (CancelSubscription). Le client aurait dû repasser sur l'offre gratuite mais nous n'avons pas pu l'identifier.",
                            'todo' => "Vérifier que le client existe. Si oui, forcer son passage sur l'offre gratuite et lui envoyer un mail.",
                            'time_period' => 'None',
                            'amount' => 0,
                            'subject' => 'utilisateur non reconnu suite à une erreur de paiement',
                            'curl_status' => $user_curl_status,
                        )
                    ),
                    'text/html'
                )
            ;
            $this->get('mailer')->send($message);
            $transport = $this->container->get('mailer')->getTransport();
            $spool = $transport->getSpool();
            $spool->flushQueue($this->container->get('swiftmailer.transport.real'));

            // Send a message to Slack
            $slack_webhook = new Webhook( $this->container->getParameter('slack_strime_billing_channel') );
            $slack_webhook->sendMessage(array(
                "message" => "Erreur de paiement (CancelSubscription) : client aurait dû repasser sur l'offre gratuite mais nous n'avons pas pu l'identifier.\nVérifier que le client existe. Si oui, forcer son passage sur l'offre gratuite et lui envoyer un mail.\nStripe customer ID: ".$customer_id,
                "username" => "[".ucfirst( $this->container->getParameter("kernel.environment") )."] Strime.io",
                "icon" => ":shit:"
            ));

            return FALSE;
        }

        // Get the details of the offers
        // Set the endpoint
        $endpoint = $strime_api_url."offers/get";

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $headers,
            'http_errors' => false
        ]);
        $curl_status = $json_response->getStatusCode();
        $response = json_decode( $json_response->getBody() );

        // Get the free offer ID
        $offer_id = NULL;

        // If we got the list of offers
        if($curl_status == 200) {

            // Get the ID of the free offer
            foreach ($response->{'results'} as $offer) {
                if(($offer->{'price'} == 0) && (strcmp(strtolower($offer->{'name'}), "gratuite") == 0))
                    $offer_id = $offer->{'offer_id'};
            }

            // Change the offer of the user to the free offer
            // Set the endpoint
            $endpoint = $strime_api_url."user/".$user->{'user_id'}."/edit";

            // Prepare the parameters
            $params = array(
                'offer_id' => (string)$offer_id
            );

            // Send the request
            $client = new \GuzzleHttp\Client();
            $json_response = $client->request('PUT', $endpoint, [
                'headers' => $headers,
                'http_errors' => false,
                'json' => $params
            ]);
            $edit_user_curl_status = $json_response->getStatusCode();
            $edit_user_json_response = $json_response->getBody();

            // If the offer has been changed
            if($edit_user_curl_status == 200) {

                // Send an email to the user
                $message = \Swift_Message::newInstance()
                    ->setSubject('Strime - Erreur de paiement')
                    ->setFrom( array('contact@strime.io' => 'Strime') )
                    ->setTo( array( $user->{'email'} => $user->{'first_name'}." ".$user->{'last_name'} ) )
                    ->setBody(
                        $this->renderView(
                            'emails/payment-automated-cancellation.html.twig',
                            array(
                                'current_year' => date('Y'),
                                'first_name' => $user->{'first_name'}
                            )
                        ),
                        'text/html'
                    )
                ;
                $this->get('mailer')->send($message);
                $transport = $this->container->get('mailer')->getTransport();
                $spool = $transport->getSpool();
                $spool->flushQueue($this->container->get('swiftmailer.transport.real'));

                // Send a message to Slack
                $slack_webhook = new Webhook( $this->container->getParameter('slack_strime_billing_channel') );
                $slack_webhook->setAttachments(
                    array(
                        array(
                            "fallback" => "Présentation de l'utilisateur",
                            "text" => "ID: ".$user->{'user_id'}."\nEmail: ".$user->{'email'},
                            "color" => "warning",
                            "author" => array(
                                "author_name" => "Mr Robot"
                            ),
                            "title" => $user->{'first_name'}." ".$user->{'last_name'},
                            "fields" => array(
                                "title" => $user->{'first_name'}." ".$user->{'last_name'},
                                "value" => "ID: ".$user->{'user_id'}."\nEmail: ".$user->{'email'},
                                "short" => FALSE
                            ),
                            "footer_icon" => "https://www.strime.io/bundles/strimeglobal/img/icon-strime.jpg",
                            "ts" => time()
                        )
                    )
                );
                $slack_webhook->sendMessage(array(
                    "message" => "Erreur de paiement (CancelSubscription) : le client a été rebasculé sur l'offre gratuite.\nStripe customer ID: ".$customer_id."\nEmail du client : ".$user->{'email'}."\nNom du client : ".$user->{'first_name'}." ".$user->{'last_name'},
                    "username" => "[".ucfirst( $this->container->getParameter("kernel.environment") )."] Strime.io",
                    "icon" => ":shit:"
                ));
            }

            // If the offer has not been changed
            else {

                // Send an email to the team
                $message = \Swift_Message::newInstance()
                    ->setSubject('Strime - Erreur (CancelSubscription) : utilisateur non mis à jour suite à une erreur de paiement')
                    ->setFrom( array('technique@strime.io' => 'Strime') )
                    ->setTo( array('contact@strime.io' => 'Strime') )
                    ->setBody(
                        $this->renderView(
                            'emails/stripe-error.html.twig',
                            array(
                                'current_year' => date('Y'),
                                'name' => $user->{'first_name'}." ".$user->{'first_name'},
                                'email' => $user->{'email'},
                                'error_type' => "Erreur de paiement (CancelSubscription). Le client aurait dû repasser sur l'offre gratuite mais une erreur s'est produite dans la mise à jour de son offre.",
                                'todo' => "Forcer son passage à l'offre gratuite et lui envoyer un mail.",
                                'time_period' => $plan_start_date." - ".$plan_end_date,
                                'amount' => 0,
                                'subject' => 'utilisateur non mis à jour suite à une erreur de paiement',
                                'curl_status' => $edit_user_curl_status,
                            )
                        ),
                        'text/html'
                    )
                ;
                $this->get('mailer')->send($message);
                $transport = $this->container->get('mailer')->getTransport();
                $spool = $transport->getSpool();
                $spool->flushQueue($this->container->get('swiftmailer.transport.real'));

                // Send a message to Slack
                $slack_webhook = new Webhook( $this->container->getParameter('slack_strime_billing_channel') );
                $slack_webhook->setAttachments(
                    array(
                        array(
                            "fallback" => "Présentation de l'utilisateur",
                            "text" => "ID: ".$user->{'user_id'}."\nEmail: ".$user->{'email'},
                            "color" => "danger",
                            "author" => array(
                                "author_name" => "Mr Robot"
                            ),
                            "title" => $user->{'first_name'}." ".$user->{'last_name'},
                            "fields" => array(
                                "title" => $user->{'first_name'}." ".$user->{'last_name'},
                                "value" => "ID: ".$user->{'user_id'}."\nEmail: ".$user->{'email'},
                                "short" => FALSE
                            ),
                            "footer_icon" => "https://www.strime.io/bundles/strimeglobal/img/icon-strime.jpg",
                            "ts" => time()
                        )
                    )
                );
                $slack_webhook->sendMessage(array(
                    "message" => "Erreur de paiement (CancelSubscription). Le client aurait dû repasser sur l'offre gratuite mais une erreur s'est produite dans la mise à jour de son offre.\nForcer son passage à l'offre gratuite et lui envoyer un mail.\nStripe customer ID: ".$customer_id."\nEmail du client : ".$user->{'email'}."\nNom du client : ".$user->{'first_name'}." ".$user->{'last_name'},
                    "username" => "[".ucfirst( $this->container->getParameter("kernel.environment") )."] Strime.io",
                    "icon" => ":shit:"
                ));

                return FALSE;
            }
        }

        // If we got an error getting the list of offers
        else {

            // Send an email to the team
            $message = \Swift_Message::newInstance()
                ->setSubject('Strime - Erreur (CancelSubscription) : utilisateur non mis à jour suite à une erreur de paiement')
                ->setFrom( array('technique@strime.io' => 'Strime') )
                ->setTo( array('contact@strime.io' => 'Strime') )
                ->setBody(
                    $this->renderView(
                        'emails/stripe-error.html.twig',
                        array(
                            'current_year' => date('Y'),
                            'name' => $user->{'first_name'}." ".$user->{'first_name'},
                            'email' => $user->{'email'},
                            'error_type' => "Erreur de paiement (CancelSubscription). Le client aurait dû repasser sur l'offre gratuite mais une erreur s'est produite dans la récupération des offres.",
                            'todo' => "Forcer son passage à l'offre gratuite et lui envoyer un mail.",
                            'time_period' => $first_day." - ".$last_day,
                            'amount' => 0,
                            'subject' => 'utilisateur non mis à jour suite à une erreur de paiement',
                            'curl_status' => $curl_status,
                        )
                    ),
                    'text/html'
                )
            ;
            $this->get('mailer')->send($message);
            $transport = $this->container->get('mailer')->getTransport();
            $spool = $transport->getSpool();
            $spool->flushQueue($this->container->get('swiftmailer.transport.real'));

            // Send a message to Slack
            $slack_webhook = new Webhook( $this->container->getParameter('slack_strime_billing_channel') );
            $slack_webhook->setAttachments(
                array(
                    array(
                        "fallback" => "Présentation de l'utilisateur",
                        "text" => "ID: ".$user->{'user_id'}."\nEmail: ".$user->{'email'},
                        "color" => "danger",
                        "author" => array(
                            "author_name" => "Mr Robot"
                        ),
                        "title" => $user->{'first_name'}." ".$user->{'last_name'},
                        "fields" => array(
                            "title" => $user->{'first_name'}." ".$user->{'last_name'},
                            "value" => "ID: ".$user->{'user_id'}."\nEmail: ".$user->{'email'},
                            "short" => FALSE
                        ),
                        "footer_icon" => "https://www.strime.io/bundles/strimeglobal/img/icon-strime.jpg",
                        "ts" => time()
                    )
                )
            );
            $slack_webhook->sendMessage(array(
                "message" => "Erreur de paiement (CancelSubscription). Le client aurait dû repasser sur l'offre gratuite mais une erreur s'est produite dans la récupération des offres.\nForcer son passage à l'offre gratuite et lui envoyer un mail.\nStripe customer ID: ".$customer_id."\nEmail du client : ".$user->{'email'}."\nNom du client : ".$user->{'first_name'}." ".$user->{'last_name'},
                "username" => "[".ucfirst( $this->container->getParameter("kernel.environment") )."] Strime.io",
                "icon" => ":shit:"
            ));

            return FALSE;
        }
    }
}
