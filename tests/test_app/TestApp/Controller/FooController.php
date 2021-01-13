<?php

namespace TestApp\Controller;

use RestApi\Controller\ApiController;

/**
 * Foo Controller
 *
 */
class FooController extends ApiController
{

    /**
     * bar method
     *
     * @return Response|null
     */
    public function bar()
    {
        $this->apiResponse['foo'] = 'bar';
    }

    /**
     * publicAction method
     *
     * @return Response|null
     */
    public function publicAction()
    {
        $this->apiResponse['foo'] = 'bar';
    }
}
