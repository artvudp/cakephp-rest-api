<?php

namespace RestApi\Test\TestCase\Utility;

use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use RestApi\Utility\JwtToken;

/**
 * JWT token utility class.
 */
class JwtTokenTest extends TestCase
{

    public function testGenerateToken()
    {
        $config = [
            'jwtAuth' => [
                'enabled' => true,
                'cypherKey' => 'R1a#2%dY2fX@3g8r5&s4Kf6*sd(5dHs!5gD4s',
                'tokenAlgorithm' => 'HS256'
            ]
        ];
        Configure::write('ApiRequest', $config);

        $payload = [
            'id' => 1,
            'email' => 'foo@bar.com'
        ];

        $this->assertNotEmpty(JwtToken::generateToken($payload));
    }

    public function testNoPayload()
    {
        $config = [
            'jwtAuth' => [
                'enabled' => true,
                'cypherKey' => 'R1a#2%dY2fX@3g8r5&s4Kf6*sd(5dHs!5gD4s',
                'tokenAlgorithm' => 'HS256'
            ]
        ];
        Configure::write('ApiRequest', $config);

        $this->assertFalse(JwtToken::generateToken());
    }
}
