<?php

namespace RestApi\Test\TestCase\Controller;

use Cake\Event\Event;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\TestSuite\IntegrationTestCase;

/**
 * RestApi\Controller\ApiController Test Case
 */
class ApiControllerTest extends IntegrationTestCase
{

    public $controller = null;

    /**
     * Setup method.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $request = new Request();
        $response = new Response();
        $this->controller = $this->getMockBuilder('RestApi\Controller\ApiController')
            ->setConstructorArgs([$request, $response])
            ->setMethods(null)
            ->getMock();
    }

    /**
     * Test beforeRender method
     *
     * @return void
     */
    public function testBeforeRender()
    {
        $this->controller->beforeRender(new Event('Controller.beforeRender'));
        $viewClass = $this->controller->viewBuilder()->className();
        $this->assertEquals('RestApi.Api', $viewClass);
    }

    /**
     * Test response data
     *
     * @return void
     */
    public function testResponseData()
    {
        $this->controller->beforeRender(new Event('Controller.beforeRender'));

        $this->assertNotEmpty($this->controller->httpStatusCode);
        $this->assertNotEmpty($this->controller->responseStatus);
    }
}
