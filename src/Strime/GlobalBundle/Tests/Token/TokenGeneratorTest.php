<?php

namespace Strime\GlobalBundle\Tests\Token;

use Strime\GlobalBundle\Token\TokenGenerator;

class TokenGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerateToken()
    {
        $token_generator = new TokenGenerator();
        $token = $token_generator->generateToken(10);

        $this->assertTrue(
        	strlen($token) == 10,
        	'Failed asserting that the length of the token is correct.'
        );
    }
}