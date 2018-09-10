<?php

namespace Strime\GlobalBundle\Helpers;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\RouterInterface;

class Sitemap
{
    private $router;
    private $em;
    private $container;

    public function __construct(RouterInterface $router, ObjectManager $em, Container $container)
    {
        $this->router = $router;
        $this->em = $em;
        $this->container = $container;
    }
	
    /**
     * Génère l'ensemble des valeurs des balises <url> du sitemap.
     *
     * @return array Tableau contenant l'ensemble des balise url du sitemap.
     */
    public function generate()
    {
        $urls = array(
            array(
                'url' => $this->container->getParameter('strime_app_url'),
                'last_modified' => '2016-10-30',
                'change_freq' => 'monthly',
                'priority' => 1.0
            ),
            array(
                'url' => $this->container->getParameter('strime_app_url') . 'offers',
                'last_modified' => '2016-10-30',
                'change_freq' => 'monthly',
                'priority' => 0.9
            ),
            array(
                'url' => $this->container->getParameter('strime_app_url') . 'contact',
                'last_modified' => '2016-10-30',
                'change_freq' => 'monthly',
                'priority' => 0.8
            ),
            array(
                'url' => $this->container->getParameter('strime_app_url') . 'about',
                'last_modified' => '2016-10-30',
                'change_freq' => 'monthly',
                'priority' => 0.8
            ),
            array(
                'url' => $this->container->getParameter('strime_app_url') . 'tos',
                'last_modified' => '2016-10-30',
                'change_freq' => 'monthly',
                'priority' => 0.7
            ),
            array(
                'url' => $this->container->getParameter('strime_app_url') . 'trust-badge',
                'last_modified' => '2016-10-30',
                'change_freq' => 'monthly',
                'priority' => 0.7
            ),
            array(
                'url' => $this->container->getParameter('strime_app_url') . 'new-features',
                'last_modified' => '2016-10-30',
                'change_freq' => 'weekly',
                'priority' => 0.9
            ),
            array(
                'url' => $this->container->getParameter('strime_app_url') . 'faq',
                'last_modified' => '2016-10-30',
                'change_freq' => 'monthly',
                'priority' => 0.8
            )
        );

        return $urls;
    }
} 