<?php

namespace Strime\GlobalBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class LocaleController extends Controller
{
    /**
     * @Route("/change-locale/{locale}/{back_path}/{back_path_parameter_name}/{back_path_parameter}/{action}/{url_data}", defaults={"locale": NULL, "back_path": NULL, "back_path_parameter_name": NULL, "back_path_parameter": NULL, "action": NULL, "url_data": NULL}, name="change_locale")
     * @Template("StrimeFrontBundle:Misc:empty-view.html.twig")
     */
    public function changeLocaleAction(Request $request, $locale, $back_path, $back_path_parameter_name, $back_path_parameter, $action, $url_data)
    {
        // If none of the 2 parameters have been set
        if(($locale == NULL) && ($back_path == NULL))
            return $this->redirect( $this->generateUrl('home') );

        // If the locale cannot be set.
        if($locale == NULL)
            return $this->redirect( $this->generateUrl( $back_path ) );

        // Set the locale
        $request->setLocale( $locale );
        $this->container->get('translator')->setLocale( $locale );

        // Set the locale in the session
        $session = $request->getSession();
        $session->set('_locale', $locale);

        // Set the parameters
        $params = array();

        if($back_path_parameter != NULL) {
            $params[ $back_path_parameter_name ] = $back_path_parameter;
        }
        if($action != NULL) {
            $params[ "action" ] = $action;
        }
        if($url_data != NULL) {
            $params[ "url_data" ] = $url_data;
        }

        // Redirect the request to the original path
        return $this->redirect( $this->generateUrl( $back_path, $params ) );
    }


    /**
     * @Route("/fr", name="change_locale_to_french")
     * @Template("StrimeFrontBundle:Misc:empty-view.html.twig")
     */
    public function changeLocaleToFrenchAction(Request $request)
    {
        // Set the locale
        $locale = "fr";
        $request->setLocale( $locale );
        $this->container->get('translator')->setLocale( $locale );

        // Set the locale in the session
        $session = $request->getSession();
        $session->set('_locale', $locale);

        // Redirect the request to the home page
        return $this->redirect( $this->generateUrl('home', $request->query->all()) );
    }


    /**
     * @Route("/en", name="change_locale_to_english")
     * @Template("StrimeFrontBundle:Misc:empty-view.html.twig")
     */
    public function changeLocaleToEnglishAction(Request $request)
    {
        // Set the locale
        $locale = "en";
        $request->setLocale( $locale );
        $this->container->get('translator')->setLocale( $locale );

        // Set the locale in the session
        $session = $request->getSession();
        $session->set('_locale', $locale);

        // Redirect the request to the home page
        return $this->redirect( $this->generateUrl('home', $request->query->all()) );
    }


    /**
     * @Route("/es", name="change_locale_to_spanish")
     * @Template("StrimeFrontBundle:Misc:empty-view.html.twig")
     */
    public function changeLocaleToSpanishAction(Request $request)
    {
        // Set the locale
        $locale = "es";
        $request->setLocale( $locale );
        $this->container->get('translator')->setLocale( $locale );

        // Set the locale in the session
        $session = $request->getSession();
        $session->set('_locale', $locale);

        // Redirect the request to the home page
        return $this->redirect( $this->generateUrl('home', $request->query->all()) );
    }
}
