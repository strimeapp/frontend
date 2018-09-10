<?php

namespace Strime\GlobalBundle\Invoice;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Component\Form\FormFactory;

class AdminHelper {

    private $container;
    public $headers;
    public $strime_api_url;
    public $invoice_id;
    public $start;
    public $stop;

    public function __construct(Container $container) {
        
        $this->container = $container;
    }


    /**
     * Function which gets the list of invoices generated
     * 
     */

    public function getInvoicesList() {

        // Set the endpoint to get the average size of videos registered
        $endpoint = $this->strime_api_url."invoices/get";

        // Get the data
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $this->headers,
            'http_errors' => false
        ]);
        $curl_status = $json_response->getStatusCode();
        $response = json_decode( $json_response->getBody() );

        return $response->{'results'};
    }


    /**
     * Function which gets the list of invoices generated on a given period
     * 
     */

    public function getInvoicesOnPeriodList() {

        // Set the endpoint to get the average size of videos registered
        $endpoint = $this->strime_api_url."invoices/get/period/start/".$this->start."/stop/".$this->stop;

        // Get the data
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $this->headers,
            'http_errors' => false
        ]);
        $curl_status = $json_response->getStatusCode();
        $response = json_decode( $json_response->getBody() );

        return $response->{'results'};
    }


    /**
     * Function which gets the details of a specific invoice
     * 
     */

    public function getInvoiceDetails() {

        // Set the endpoint to get the average size of videos registered
        $endpoint = $this->strime_api_url."invoice/".$this->invoice_id."/get";

        // Get the data
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $endpoint, [
            'headers' => $this->headers,
            'http_errors' => false
        ]);
        $curl_status = $json_response->getStatusCode();
        $response = json_decode( $json_response->getBody() );

        return $response->{'results'};
    }
}