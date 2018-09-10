<?php

namespace Strime\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

use Strime\GlobalBundle\Helpers\TokenGenerator;

use Strime\GlobalBundle\Entity\NewFeature;
use Strime\GlobalBundle\Form\NewFeatureType;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class NewFeaturesController extends Controller
{
    /**
     * @Route("/new-features/{action}/{new_feature_id}", name="admin_new_features", defaults={"action": NULL, "new_feature_id": NULL})
     * @Template("StrimeAdminBundle:Admin:new-features.html.twig")
     */
    public function newFeaturesAction(Request $request, $action, $new_feature_id)
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

        // Set the entity manager
        $em = $this->getDoctrine()->getManager();


        // If we got a request to kill a job
        if(($action != NULL) && ($new_feature_id != NULL) && (strcmp($action, "delete") == 0)) {

            // Get the entity
            $new_feature = $em->getRepository('StrimeGlobalBundle:NewFeature')->findOneBy(array('secret_id' => $new_feature_id));

            if($new_feature != NULL) {
                $em->remove( $new_feature );
                $em->flush();

                $this->addFlash(
                    'success',
                    'Ce message a été supprimé.'
                );
            }
            else {
                $this->addFlash(
                    'error',
                    'Le message que vous tentez de supprimer n\'existe pas.'
                );
            }
        }

        // Get the list of new features added
        $new_features = $em->getRepository('StrimeGlobalBundle:NewFeature')->findAll();


        return array(
            'body_classes' => 'dashboard',
            'login_form' => $login['login_form'],
            'login_message' => $login['login_message'],
            'forgotten_password_form' => $forgotten_password_form->createView(),
        	"new_features" => $new_features,
        );
    }



    /**
     * @Route("/new-feature-add", defaults={"action": NULL, "new_feature_id": NULL}, name="admin_add_new_feature")
     * @Template("StrimeAdminBundle:Admin:new-feature-add.html.twig")
     */
    public function addNewFeatureAction(Request $request, $action, $new_feature_id)
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

        // Set the entity manager
        $em = $this->getDoctrine()->getManager();

        // Create a new new_feature entity
        $new_feature = new NewFeature();
        $em = $this->getDoctrine()->getManager();

        // Create the form
        $new_feature_form = $this->createFormBuilder($new_feature)
            ->add('titleFr', TextType::class, array('label' => '', 'attr' => array('placeholder' => 'Titre [FR]')))
            ->add('titleEn', TextType::class, array('label' => '', 'attr' => array('placeholder' => 'Titre [EN]')))
            ->add('titleEs', TextType::class, array('label' => '', 'attr' => array('placeholder' => 'Titre [ES]')))
            ->add('blogUrlFr', UrlType::class, array('label' => '', 'attr' => array('placeholder' => 'URL [FR]')))
            ->add('blogUrlEn', UrlType::class, array('label' => '', 'attr' => array('placeholder' => 'URL [EN]')))
            ->add('blogUrlEs', UrlType::class, array('label' => '', 'attr' => array('placeholder' => 'URL [ES]')))
            ->add('descriptionFr', TextareaType::class, array('label' => '', 'attr' => array('placeholder' => 'Description [FR]')))
            ->add('descriptionEn', TextareaType::class, array('label' => '', 'attr' => array('placeholder' => 'Description [EN]')))
            ->add('descriptionEs', TextareaType::class, array('label' => '', 'attr' => array('placeholder' => 'Description [ES]')))
            ->add('add', SubmitType::class, array('label' => 'Ajouter ce message'))
            ->getForm();


        // Handle the form if submitted
        $new_feature_form->handleRequest($request);

        if ($new_feature_form->isSubmitted() && $new_feature_form->isValid()) {

            // We generate a secret_id
            $secret_id_exists = TRUE;
            $token_generator = new TokenGenerator();
            while($secret_id_exists != NULL) {
                $secret_id = $token_generator->generateToken(10);
                $secret_id_exists = $em->getRepository('StrimeGlobalBundle:NewFeature')->findOneBy(array('secret_id' => $secret_id));
            }

            // Set the secret_id
            $new_feature->setSecretId($secret_id);

            // Save the data
            $em->persist($new_feature);
            $em->flush();

            $this->addFlash(
                'success',
                'Ce message a été ajouté.'
            );
        }


        return array(
            'body_classes' => 'dashboard',
            'login_form' => $login['login_form'],
            'login_message' => $login['login_message'],
            'forgotten_password_form' => $forgotten_password_form->createView(),
            "new_feature_form" => $new_feature_form->createView(),
        );
    }

}
