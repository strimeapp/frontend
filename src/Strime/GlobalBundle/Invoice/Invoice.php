<?php

namespace Strime\GlobalBundle\Invoice;

use \Twig_Loader_Filesystem;
use \Twig_Environment;
use Symfony\Component\Validator\Constraints\DateTime;

class Invoice {

    private $knp;
    private $templating;
    public $strime_api_url;
    public $headers;
    public $user_id;
    public $total_amount;
    public $amount_wo_taxes;
    public $taxes;
    public $taxes_liquidation;
    public $taxes_liquidation_rate;
    public $tax_rate;
    public $coupon;
    public $invoice_id;
    public $date;
    public $company;
    public $address;
    public $address_more;
    public $zip;
    public $city;
    public $country;
    public $contact;
    public $vat_number;
    public $plan;
    public $plan_nb_occurrences;
    public $plan_occurrence;
    public $plan_start;
    public $plan_end;
    public $quantity;
    public $unit_price;
    public $total_price;
    public $plan_start_date;
    public $plan_end_date;
    public $year;
    public $month;
    public $day;
    public $regeneration;

    public function __construct($pdf, $templating) {
        
        $this->knp = $pdf;
        $this->templating = $templating;
        $this->quantity = 1;
        $this->day = date('d');
        $this->month = date('m');
        $this->year = date('Y');
        $this->coupon = NULL;
        $this->taxes_liquidation = NULL;
        $this->taxes_liquidation_rate = NULL;
        $this->regeneration = FALSE;
        $this->date = date("d/m/Y");
    }



    /**
     * @param string $stripe_token The token returned by Stripe JS
     * @param string $plan The to which the user wants to subscribe
     * @param string $email The user email
     * @return array The result of the payment
     */
    public function generateInvoice()
    {
        // Create the folders if needed
        $invoices_folder =  __DIR__.'/../../../../web/invoices/'.$this->user_id.'/';
        
        if(!file_exists($invoices_folder))
            mkdir($invoices_folder, 0755, TRUE);

        // If this is not a regeneration
        if(!$this->regeneration) {
            
            // Create an invoice in the API
            // Set the endpoint
            $endpoint = $this->strime_api_url."invoice/add";

            // Prepare the parameters
            $params = array(
                'user_id' => $this->user_id,
                'total_amount' => $this->total_amount,
                'amount_wo_taxes' => $this->amount_wo_taxes,
                'taxes' => $this->taxes,
                'tax_rate' => $this->tax_rate,
                'day' => $this->day,
                'month' => $this->month,
                'year' => $this->year,
                'plan_start_date' => $this->plan_start_date,
                'plan_end_date' => $this->plan_end_date,
                'status' => 1
            );

            // Send the request
            $client = new \GuzzleHttp\Client();
            $json_response = $client->request('POST', $endpoint, [
                'headers' => $this->headers,
                'http_errors' => false,
                'json' => $params
            ]);
            $add_invoice_curl_status = $json_response->getStatusCode();
            $response = json_decode( $json_response->getBody() );

            if($add_invoice_curl_status != 201) {
                $invoice_error = "id_not_saved";
                $result = array(
                    "status" => "error",
                    "status_boolean" => FALSE,
                    "error_code" => $invoice_error
                );

                $this->invoice_id = $this->year.$this->month.$this->day."-error";

                return $result;
                exit;
            }
            else {
                $this->invoice_id = $response->{'invoice_id'};
            }
        }
        else {

            // Update the invoice in the API
            // Set the endpoint
            $endpoint = $this->strime_api_url."invoice/".$this->invoice_id."/edit";

            // Prepare the parameters
            $params = array(
                'tax_rate' => $this->tax_rate,
                'plan_start_date' => $this->plan_start_date,
                'plan_end_date' => $this->plan_end_date
            );

            // Send the request
            $client = new \GuzzleHttp\Client();
            $json_response = $client->request('PUT', $endpoint, [
                'headers' => $this->headers,
                'http_errors' => false,
                'json' => $params
            ]);
            $add_invoice_curl_status = $json_response->getStatusCode();
            $add_invoice_json_response = json_decode( $json_response->getBody() );

            if($add_invoice_curl_status != 200) {
                $invoice_error = "invoice_not_updated";
                $result = array(
                    "status" => "error",
                    "status_boolean" => FALSE,
                    "error_code" => $invoice_error
                );

                $this->invoice_id = $this->year.$this->month.$this->day."-error";

                return $result;
                exit;
            }
        }

        // Set the file name
        $file = $invoices_folder.$this->invoice_id.'.pdf';

        // Remove the file if it exists to prevent errors
        if(file_exists($file)) {
            unlink($file);
        }

        // If a coupon has been applied, calculate the amount off
        if($this->coupon != NULL) {
            if($this->coupon->{'percent_off'} != NULL) {
                $amount_off = round( $this->total_amount * $this->coupon->{'percent_off'} / 100, 2, PHP_ROUND_HALF_DOWN );
            }
            elseif($this->coupon->{'amount_off'} != NULL) {
                $amount_off = round( $this->coupon->{'amount_off'} / (100 + $this->tax_rate), 2, PHP_ROUND_HALF_DOWN );
            }
            else {
                $amount_off = NULL;
            }
        }
        else {
            $amount_off = NULL;
        }

        // Set the data
        $data = array(
            'total_amount' => $this->total_amount,
            'amount_wo_taxes' => $this->amount_wo_taxes,
            'taxes' => $this->taxes,
            'tax_rate' => $this->tax_rate,
            'taxes_liquidation' => $this->taxes_liquidation,
            'taxes_liquidation_rate' => $this->taxes_liquidation_rate,
            'coupon' => $this->coupon,
            'amount_off' => $amount_off,
            'invoice_id' => $this->invoice_id,
            'date' => $this->date,
            'company' => $this->company,
            'address' => $this->address,
            'address_more' => $this->address_more,
            'zip' => $this->zip,
            'city' => $this->city,
            'country' => $this->country,
            'contact' => $this->contact,
            'vat_number' => $this->vat_number,
            'plan' => $this->plan,
            'plan_nb_occurrences' => $this->plan_nb_occurrences,
            'plan_occurrence' => $this->plan_occurrence,
            'plan_start' => $this->plan_start_date,
            'plan_end' => $this->plan_end_date,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'total_price' => $this->total_price
        );
        
        // Generate the PDF
        $this->knp->generateFromHtml(
            $this->templating->render(
                'StrimeGlobalBundle:PDF:invoice.html.twig',
                $data
            ),
            $file
        );

        return $file;
    }
}