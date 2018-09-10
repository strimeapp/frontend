<?php

namespace Strime\BackBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class DownloadController extends Controller
{

    /**
     * @Route("/app/download/{user_id}/{invoice_id}", defaults={"user_id": NULL, "invoice_id": NULL}, name="app_download_invoice")
     * @Template()
     */
    public function downloadInvoiceAction(Request $request, $user_id, $invoice_id)
    {
        // Get the file
        $filename = $invoice_id.".pdf";
        $full_path = $this->get('kernel')->getRootDir() . "/../web/invoices/".$user_id."/".$filename;

        // Generate response
        $response = new Response();

        // Set headers
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', mime_content_type($full_path));
        $response->headers->set('Content-Disposition', 'attachment; filename="' . basename($filename) . '";');
        $response->headers->set('Content-length', filesize($full_path));

        // Send headers before outputting anything
        $response->sendHeaders();

        // Set the content
        $response->setContent(file_get_contents($full_path));

        // Return the response
        return $response;
    }
}
