<?php

namespace Strime\GlobalBundle\Helpers;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Doctrine\Common\Persistence\ObjectManager;
use Strime\GlobalBundle\Token\TokenGenerator;
use Strime\GlobalBundle\Entity\EmailConfirmationToken;

class Signup {
    
    private $em;
    private $container;

    public function __construct(Container $container, ObjectManager $em) {
        $this->container = $container;
        $this->em = $em;
    }



    /**
     * @return NULL
     */
    public function saveEmailConfirmationToken( $user_id )
    {
        // Check if there already is a token for this user
        $token_exists = $this->em->getRepository('StrimeGlobalBundle:EmailConfirmationToken')->findOneBy(array('user_id' => $user_id));

        // Generate a token for the email confirmation action
        $token_generator = new TokenGenerator();
        $email_confirmation_token = $token_generator->generateToken(20);

        // Save the token
        if($token_exists != NULL) {
            $email_confirmation = $token_exists;
        }
        else {
            $email_confirmation = new EmailConfirmationToken;
        }

        $email_confirmation->setUserId( $user_id );
        $email_confirmation->setToken( $email_confirmation_token );
        $this->em->persist( $email_confirmation );
        $this->em->flush();
        
        return $email_confirmation_token;
    }



    /**
     * @return NULL
     */
    public function checkEmailConfirmationDateToken( $email_confirmation_token )
    {

        $now = new \DateTime();
        $token_creation_date = $email_confirmation_token->getCreatedAt();
        $interval = $token_creation_date->diff($now);
        
        return $interval->days;
    }
}