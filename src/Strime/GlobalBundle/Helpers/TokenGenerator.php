<?php

namespace Strime\GlobalBundle\Helpers;

class TokenGenerator {

	public function __construct() {

	}

	/**
     * @param int $length Length of the token
     * @return string The requested token
     */
    public function generateToken($length) 
    {
        return substr( hash("sha512", date('Y-m-d H:i:s').rand()), 0, $length );
    }
}