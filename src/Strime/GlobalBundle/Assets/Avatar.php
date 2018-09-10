<?php

namespace Strime\GlobalBundle\Assets;

class Avatar {

    public $email;
    public $default;
    public $size;
    public $avatar;

    public function __contruct() {
        
    }

    /**
     * @param string $stripe_token The token returned by Stripe JS
     * @param string $plan The to which the user wants to subscribe
     * @param string $email The user email
     * @return array The result of the payment
     */
    public function setGravatar()
    {
        $grav_url = "https://www.gravatar.com/avatar/" . md5( strtolower( trim( $this->email ) ) ) . "?d=" . urlencode( $this->default ) . "&s=" . $this->size;
        return $grav_url;
    }
}