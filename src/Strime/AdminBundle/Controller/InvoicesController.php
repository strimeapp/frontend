<?php

namespace Strime\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Strime\GlobalBundle\Payment\Payment;

use Strime\GlobalBundle\Entity\TaxRate;

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

class InvoicesController extends Controller
{
    /**
     * @Route("/invoices/{action}/{invoice_id}", defaults={"action": NULL, "invoice_id": NULL}, name="admin_invoices")
     * @Template("StrimeAdminBundle:Admin:invoices.html.twig")
     */
    public function invoicesAction(Request $request, $action, $invoice_id)
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

        // Get the invoices
        $invoice_helper = $this->container->get('strime.invoice.admin_helper');
        $invoice_helper->headers = $headers;
        $invoice_helper->strime_api_url = $strime_api_url;
        $invoices = $invoice_helper->getInvoicesList();
        $downloadable_zip = FALSE;

        // Check if the files exist on the server
        foreach ($invoices as $invoice) {
            if(($invoice->{'user'} == NULL) || (!file_exists(__DIR__.'/../../../../web/invoices/'.$invoice->{'user'}->{'user_id'}.'/'.$invoice->{'invoice_id'}.'.pdf'))) {
                $invoice->{'file_exists'} = FALSE;
            }
            else {
                $invoice->{'file_exists'} = TRUE;
            }
        }

        // Create the filter invoices form
        $filter_invoices_form = $form->createFilterInvoicesForm();

        // Handle the request and set the result variable
        $filter_invoices_form->handleRequest($request);
        $filter_invoices_form_results = NULL;

        // Check if the form has been submitted
        if($filter_invoices_form->isSubmitted()) {

            // If the submitted form is valid
            if($filter_invoices_form->isValid()) {

                // Get the data
                $start = $filter_invoices_form->get('start')->getData();
                $stop = $filter_invoices_form->get('stop')->getData();

                // Set dates as unix timestamps
                $start = $start->format('U');
                $stop = $stop->format('U');

                // Collect the invoices from the API
                $invoice_helper = $this->container->get('strime.invoice.admin_helper');
                $invoice_helper->headers = $headers;
                $invoice_helper->strime_api_url = $strime_api_url;
                $invoice_helper->start = $start;
                $invoice_helper->stop = $stop;
                $invoices = $invoice_helper->getInvoicesOnPeriodList();

                // ZIP all the invoices returned
                if($invoices != NULL) {
                    $files = array();
                    foreach ($invoices as $invoice) {
                        if($invoice->{'user'} != NULL)
                            array_push($files, __DIR__.'/../../../../web/invoices/'.$invoice->{'user'}->{'user_id'}.'/'.$invoice->{'invoice_id'}.'.pdf' );
                        else
                            array_push($files, __DIR__.'/../../../../web/invoices/'.$invoice->{'deleted_user_id'}.'/'.$invoice->{'invoice_id'}.'.pdf' );
                    }

                    $zip = new \ZipArchive();
                    $zip_name = 'invoices/strime-invoices.zip';

                    if( file_exists(__DIR__.'/../../../../web/'.$zip_name) )
                        unlink( __DIR__.'/../../../../web/'.$zip_name );

                    $zip->open($zip_name,  \ZipArchive::CREATE);
                    foreach ($files as $file) {
                        if(file_exists($file)) {
                            $zip->addFromString( basename($file), file_get_contents($file) );
                        }
                    }
                    $zip->close();
                    $downloadable_zip = TRUE;
                }
            }
        }


        // If we get a request to regenerate an invoice
        if(isset($action) && (strcmp($action, "regenerate") == 0) && isset($invoice_id)) {

            // Get the details of the invoice
            $invoice_helper = $this->container->get('strime.invoice.admin_helper');
            $invoice_helper->headers = $headers;
            $invoice_helper->strime_api_url = $strime_api_url;
            $invoice_helper->invoice_id = $invoice_id;
            $invoice_details = $invoice_helper->getInvoiceDetails();

            // Get the invoice rate
            if($invoice_details->{'tax_rate'} == NULL) {
                $tax_rate = $invoice_details->{'taxes'} / $invoice_details->{'amount_wo_taxes'};
                $tax_rate = (int)floor($tax_rate * 100);
            }
            else {
                $tax_rate = $invoice_details->{'tax_rate'};
            }

            // Get the tax rate liquidation
            if(($tax_rate != 0) && (strcmp($invoice_details->{'user'}->{'country'}, "FR") != 0) && ($invoice_details->{'user'}->{'vat_number'} != NULL)) {
                $tax_rate_details = new TaxRate;
                $tax_rate_details = $em->getRepository('StrimeGlobalBundle:TaxRate')->findOneBy(array('country_code' => $invoice_details->{'user'}->{'country'}));

                $taxes_liquidation_rate = -(int)$tax_rate_details->getTaxRate();
                $taxes_liquidation = -$invoice_details->{'taxes'};
            }
            else {
                $taxes_liquidation_rate = NULL;
                $taxes_liquidation = NULL;
                $tax_rate_details = $tax_rate;
            }

            // If the user is part of the EU and has a VAT number,
            // The amound_wo_taxes should be the same than the total amount
            if(($tax_rate_details != NULL) && (strcmp($invoice_details->{'user'}->{'country'}, "FR") != 0) && ($invoice_details->{'user'}->{'vat_number'} != NULL)) {
                $invoice_details->{'amount_wo_taxes'} = $invoice_details->{'total_amount'};
            }

            // Set the plan start date and end date
            if($invoice_details->{'plan_start_date'} == NULL) {
                $plan_start_date = $invoice_details->{'day'}."/".$invoice_details->{'month'}."/".$invoice_details->{'year'};
                $plan_start_date_time = strtotime( $invoice_details->{'month'}."/".$invoice_details->{'day'}."/".$invoice_details->{'year'} );
                $plan_end_date = date('d/m/Y', strtotime("+1 month", $plan_start_date_time));
            }
            else {
                $plan_start_date = $invoice_details->{'plan_start_date'};
                $plan_end_date = $invoice_details->{'plan_end_date'};
            }


            // Generate the invoice
            $invoice = $this->container->get('strime.invoice');
            $invoice->strime_api_url = $strime_api_url;
            $invoice->headers = $headers;
            $invoice->invoice_id = $invoice_details->{'invoice_id'};
            $invoice->user_id = $invoice_details->{'user'}->{'user_id'};
            $invoice->total_amount = $invoice_details->{'total_amount'};
            $invoice->amount_wo_taxes = $invoice_details->{'amount_wo_taxes'};
            $invoice->taxes = $invoice_details->{'taxes'};
            $invoice->tax_rate = $tax_rate;
            $invoice->company = $invoice_details->{'user'}->{'company'};
            $invoice->vat_number = $invoice_details->{'user'}->{'vat_number'};
            $invoice->address = $invoice_details->{'user'}->{'address'};
            $invoice->address_more = $invoice_details->{'user'}->{'address_more'};
            $invoice->zip = $invoice_details->{'user'}->{'zip'};
            $invoice->city = $invoice_details->{'user'}->{'city'};
            $invoice->country = $invoice_details->{'user'}->{'country'};
            $invoice->contact = $invoice_details->{'user'}->{'first_name'}." ".$invoice_details->{'user'}->{'last_name'};
            $invoice->plan = $invoice_details->{'user'}->{'offer'}->{'name'};
            $invoice->plan_nb_occurrences = 1;
            $invoice->plan_occurrence = "mois";
            $invoice->unit_price = $invoice_details->{'amount_wo_taxes'};
            $invoice->total_price = $invoice_details->{'amount_wo_taxes'};
            $invoice->plan_start_date = $plan_start_date;
            $invoice->plan_end_date = $plan_end_date;
            $invoice->year = $invoice_details->{'year'};
            $invoice->month = $invoice_details->{'month'};
            $invoice->day = $invoice_details->{'day'};
            $invoice->date = date("d/m/Y", strtotime($invoice_details->{'created_at'}->{'date'}));
            $invoice->regeneration = TRUE;

            if($taxes_liquidation != NULL)
                $invoice->taxes_liquidation = $taxes_liquidation;

            if($taxes_liquidation_rate != NULL)
                $invoice->taxes_liquidation_rate = $taxes_liquidation_rate;

            $path_to_invoice = $invoice->generateInvoice();

            if(!is_array($path_to_invoice)) {

                $this->addFlash(
                    'success',
                    'Cette facture a bien été regénérée.'
                );
            }
            else {

                $this->addFlash(
                    'error',
                    $path_to_invoice["error_code"]
                );
            }

            // Redirect to the route without parameters
            return $this->redirectToRoute('admin_invoices');
        }


        return array(
            'body_classes' => 'dashboard',
            'login_form' => $login['login_form'],
            'login_message' => $login['login_message'],
            'forgotten_password_form' => $forgotten_password_form->createView(),
            'filter_invoices_form' => $filter_invoices_form->createView(),
            "invoices" => $invoices,
            "downloadable_zip" => $downloadable_zip,
        );
    }
}
