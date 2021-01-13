<?php

namespace RestApi\Test\TestCase\Controller\Component;

use Cake\Controller\ComponentRegistry;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Network\Request;
use Cake\Network\Response;
use PHPUnit\Framework\TestCase;
use RestApi\Controller\Component\AccessControlComponent;

/**
 * RestApi\Controller\Component\AccessControlComponent Test Case
 */
class AccessControlComponentTest extends TestCase
{

    /**
     *
     * @var AccessControlComponent
     */
    public $AccessControlComponent;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $request = new Request();
        $response = new Response();
        $this->controller = $this->getMockBuilder('TestApp\Controller\FooController')
            ->setConstructorArgs([$request, $response])
            ->setMethods(null)
            ->getMock();

        $registry = new ComponentRegistry($this->controller);
        $this->AccessControlComponent = new AccessControlComponent($registry);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->AccessControlComponent);

        parent::tearDown();
    }

    /**
     * Test beforeFilter method
     *
     * @return void
     */
    public function testBeforeFilter()
    {
        $config = [
            'log' => false,
            'jwtAuth' => [
                'enabled' => true,
                'cypherKey' => 'R1a#2%dY2fX@3g8r5&s4Kf6*sd(5dHs!5gD4s',
                'tokenAlgorithm' => 'HS256'
            ],
            'cors' => [
                'enabled' => true,
                'origin' => '*',
                'allowedMethods' => ['GET', 'POST', 'OPTIONS'],
                'allowedHeaders' => ['Content-Type, Authorization, Accept, Origin'],
                'maxAge' => 2628000
            ]
        ];
        Configure::write('ApiRequest', $config);

        $this->expectException('RestApi\Routing\Exception\MissingTokenException');

        $event = new Event('Controller.beforeFilter', $this->controller);
        $this->assertEquals($this->AccessControlComponent->beforeFilter($event), true);
    }

    public function testPublicAction()
    {
        $config = [
            'jwtAuth' => [
                'enabled' => false
            ]
        ];
        Configure::write('ApiRequest', $config);

        $event = new Event('Controller.beforeFilter', $this->controller);
        $this->assertEquals($this->AccessControlComponent->beforeFilter($event), true);
    }

    public function testAccessToken()
    {
        $config = [
            'jwtAuth' => [
                'enabled' => true
            ]
        ];
        Configure::write('ApiRequest', $config);

        $request = new Request();
        $request->params['allowWithoutToken'] = true;
        $Controller = new \TestApp\Controller\FooController($request);
        $registry = new ComponentRegistry($Controller);
        $Component = new AccessControlComponent($registry);
        $event = new Event('Controller.beforeFilter', $Controller);
        $this->assertEquals($Component->beforeFilter($event), true);
        unset($Controller);
        unset($Component);
    }

    public function testAccessTokenInQuery()
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
            'email' => 'foo@example.com'
        ];
        $token = \RestApi\Utility\JwtToken::generateToken($payload);
        $requestConfig = [
            'params' => ['allowWithoutToken' => false],
            'query' => ['token' => $token]
        ];
        $request = new Request($requestConfig);

        $Controller = new \TestApp\Controller\FooController($request);
        $registry = new ComponentRegistry($Controller);
        $Component = new AccessControlComponent($registry);
        $event = new Event('Controller.beforeFilter', $Controller);
        $this->assertEquals($Component->beforeFilter($event), true);
        unset($Controller);
        unset($Component);
    }

    public function testAccessTokenInPost()
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
            'email' => 'foo@example.com'
        ];
        $token = \RestApi\Utility\JwtToken::generateToken($payload);
        $requestConfig = [
            'params' => ['allowWithoutToken' => false],
            'post' => ['token' => $token]
        ];
        $request = new Request($requestConfig);

        $Controller = new \TestApp\Controller\FooController($request);
        $registry = new ComponentRegistry($Controller);
        $Component = new AccessControlComponent($registry);
        $event = new Event('Controller.beforeFilter', $Controller);
        $this->assertEquals($Component->beforeFilter($event), true);
        unset($Controller);
        unset($Component);
    }

    public function testAccessTokenInHeader()
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
            'email' => 'foo@example.com'
        ];
        $token = \RestApi\Utility\JwtToken::generateToken($payload);
        $requestConfig = [
            'params' => ['allowWithoutToken' => false]
        ];
        $request = new Request($requestConfig);
        $request->env('HTTP_AUTHORIZATION', "Bearer {$token}");
        $Controller = new \TestApp\Controller\FooController($request);
        $registry = new ComponentRegistry($Controller);
        $Component = new AccessControlComponent($registry);
        $event = new Event('Controller.beforeFilter', $Controller);
        $this->assertEquals($Component->beforeFilter($event), true);
        unset($Controller);
        unset($Component);
    }

    public function testAccessTokenFormat()
    {
        $config = [
            'jwtAuth' => [
                'enabled' => true,
                'cypherKey' => 'R1a#2%dY2fX@3g8r5&s4Kf6*sd(5dHs!5gD4s',
                'tokenAlgorithm' => 'HS256'
            ]
        ];
        Configure::write('ApiRequest', $config);

        $this->expectException('RestApi\Routing\Exception\InvalidTokenException');

        $token = 'invalid token format';
        $requestConfig = [
            'params' => ['allowWithoutToken' => false],
            'query' => ['token' => $token]
        ];
        $request = new Request($requestConfig);

        $Controller = new \TestApp\Controller\FooController($request);
        $registry = new ComponentRegistry($Controller);
        $Component = new AccessControlComponent($registry);
        $event = new Event('Controller.beforeFilter', $Controller);
        $Component->beforeFilter($event);

        unset($Controller);
        unset($Component);
    }

    public function testInvalidTokenFormat()
    {
        $config = [
            'jwtAuth' => [
                'enabled' => true,
                'cypherKey' => 'R1a#2%dY2fX@3g8r5&s4Kf6*sd(5dHs!5gD4s',
                'tokenAlgorithm' => 'HS256'
            ]
        ];
        Configure::write('ApiRequest', $config);

        $this->expectException('RestApi\Routing\Exception\InvalidTokenFormatException');

        $requestConfig = [
            'params' => ['allowWithoutToken' => false]
        ];
        $request = new Request($requestConfig);
        $request->env('HTTP_AUTHORIZATION', "NotBearer jwt.token");

        $Controller = new \TestApp\Controller\FooController($request);
        $registry = new ComponentRegistry($Controller);
        $Component = new AccessControlComponent($registry);
        $event = new Event('Controller.beforeFilter', $Controller);
        $Component->beforeFilter($event);

        unset($Controller);
        unset($Component);
    }

    public function testInvalidToken()
    {
        $config = [
            'jwtAuth' => [
                'enabled' => true,
                'cypherKey' => 'R1a#2%dY2fX@3g8r5&s4Kf6*sd(5dHs!5gD4s',
                'tokenAlgorithm' => 'HS256'
            ]
        ];
        Configure::write('ApiRequest', $config);

        $this->expectException('RestApi\Routing\Exception\InvalidTokenException');

        $requestConfig = [
            'params' => ['allowWithoutToken' => false]
        ];
        $request = new Request($requestConfig);
        $request->env('HTTP_AUTHORIZATION', "Bearer jwt.token");

        $Controller = new \TestApp\Controller\FooController($request);
        $registry = new ComponentRegistry($Controller);
        $Component = new AccessControlComponent($registry);
        $event = new Event('Controller.beforeFilter', $Controller);
        $Component->beforeFilter($event);

        unset($Controller);
        unset($Component);
    }

    public function testMissingAccessToken()
    {
        $config = [
            'jwtAuth' => [
                'enabled' => true,
                'cypherKey' => 'R1a#2%dY2fX@3g8r5&s4Kf6*sd(5dHs!5gD4s',
                'tokenAlgorithm' => 'HS256'
            ]
        ];
        Configure::write('ApiRequest', $config);

        $this->expectException('RestApi\Routing\Exception\MissingTokenException');

        $requestConfig = [
            'params' => ['allowWithoutToken' => false]
        ];
        $request = new Request($requestConfig);

        $Controller = new \TestApp\Controller\FooController($request);
        $registry = new ComponentRegistry($Controller);
        $Component = new AccessControlComponent($registry);
        $event = new Event('Controller.beforeFilter', $Controller);
        $Component->beforeFilter($event);

        unset($Controller);
        unset($Component);
    }
}
