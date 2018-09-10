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
use Strime\BackBundle\Form\TaxRateType;

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

class TaxRatesController extends Controller
{
    /**
     * @Route("/tax-rates/{action}/{tax_rate_id}", defaults={"action": NULL, "tax_rate_id": NULL}, name="admin_tax_rates")
     * @Template("StrimeAdminBundle:Admin:tax-rates.html.twig")
     */
    public function taxRatesAction(Request $request, $action, $tax_rate_id)
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

        // Get the entity manager
        $em = $this->getDoctrine()->getManager();

        // If there is a request to delete a tax rate
        if(isset($action) && isset($tax_rate_id) && (strcmp($action, "delete") == 0)) {
            $tax_rate = new TaxRate;
            $tax_rate = $em->getRepository('StrimeGlobalBundle:TaxRate')->findOneBy(array('id' => $tax_rate_id));

            // If the tax_rate is defined, remove it
            if($tax_rate != NULL) {
                $em->remove( $tax_rate );
                $em->flush();

                $this->addFlash(
                    'success',
                    'Ce taux de TVA a été supprimé.'
                );
            }
            else {
                $this->addFlash(
                    'error',
                    'Une erreur est survenue lors de la suppression de ce taux de TVA.'
                );
            }

            return $this->redirectToRoute('admin_tax_rates');
        }

        // Create the add tax rate form
        $tax_rate = new TaxRate();
        $add_tax_rate_form = $this->createForm(TaxRateType::class, $tax_rate);

        // Handle the request and set the result variable
        $add_tax_rate_form->handleRequest($request);
        $add_tax_rate_form_results = NULL;

        // Check if the form has been submitted and the submitted form is valid
        if($add_tax_rate_form->isSubmitted() && $add_tax_rate_form->isValid()) {

            $em->persist( $tax_rate );
            $em->flush();

            $this->addFlash(
                'success',
                'Le taux de TVA a bien été ajouté.'
            );
        }

        // Get the saved tax rates
        $tax_rates = $em->getRepository('StrimeGlobalBundle:TaxRate')->findBy(array(), array('country' => 'ASC'));

        return array(
            'body_classes' => 'dashboard',
            'login_form' => $login['login_form'],
            'login_message' => $login['login_message'],
            'forgotten_password_form' => $forgotten_password_form->createView(),
            'add_tax_rate_form' => $add_tax_rate_form->createView(),
            "tax_rates" => $tax_rates,
        );
    }

}
