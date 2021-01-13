<?php

namespace RestApi\Controller;

use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\Event;

/**
 * Application Controller
 *
 */
class AppController extends Controller
{

    /**
     * HTTP Status Code
     *
     * @var int
     */
    public $httpStatusCode = 200;

    /**
     * Status value in API response
     *
     * @var string
     */
    public $responseStatus = "";

    /**
     * Response format configuration
     *
     * @var array
     */
    public $responseFormat = [];

    /**
     * API response data
     *
     * @var array
     */
    public $apiResponse = [];

    /**
     * payload value from JWT token
     *
     * @var mixed
     */
    public $jwtPayload = null;

    /**
     * JWT token for current request
     *
     * @var string
     */
    public $jwtToken = "";

    /**
     * Initialization hook method.
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();

        $this->responseFormat = [
            'statusKey' => (null !== Configure::read('ApiRequest.responseFormat.statusKey')) ? Configure::read('ApiRequest.responseFormat.statusKey') : 'status',
            'statusOkText' => (null !== Configure::read('ApiRequest.responseFormat.statusOkText')) ? Configure::read('ApiRequest.responseFormat.statusOkText') : 'OK',
            'statusNokText' => (null !== Configure::read('ApiRequest.responseFormat.statusNokText')) ? Configure::read('ApiRequest.responseFormat.statusNokText') : 'NOK',
            'resultKey' => (null !== Configure::read('ApiRequest.responseFormat.resultKey')) ? Configure::read('ApiRequest.responseFormat.resultKey') : 'result',
            'messageKey' => (null !== Configure::read('ApiRequest.responseFormat.messageKey')) ? Configure::read('ApiRequest.responseFormat.messageKey') : 'message',
            'defaultMessageText' => (null !== Configure::read('ApiRequest.responseFormat.defaultMessageText')) ? Configure::read('ApiRequest.responseFormat.defaultMessageText') : 'Empty response!',
            'errorKey' => (null !== Configure::read('ApiRequest.responseFormat.errorKey')) ? Configure::read('ApiRequest.responseFormat.errorKey') : 'error',
            'defaultErrorText' => (null !== Configure::read('ApiRequest.responseFormat.defaultErrorText')) ? Configure::read('ApiRequest.responseFormat.defaultErrorText') : 'Unknown request!'
        ];

        $this->responseStatus = $this->responseFormat['statusOkText'];

        $this->loadComponent('RequestHandler');
        $this->loadComponent('RestApi.AccessControl');
    }

    /**
     * Before render callback.
     *
     * @param Event $event The beforeRender event.
     * @return \Cake\Network\Response|null
     */
    public function beforeRender(Event $event)
    {
        parent::beforeRender($event);

        $this->response->getStatusCode($this->httpStatusCode);

        if (200 != $this->httpStatusCode) {
            $this->responseStatus = $this->responseFormat['statusNokText'];
        }

        $response = [
            $this->responseFormat['statusKey'] => $this->responseStatus
        ];

        if (!empty($this->apiResponse)) {
            $response[$this->responseFormat['resultKey']] = $this->apiResponse;
        }

        $this->set('response', $response);
        $this->set('responseFormat', $this->responseFormat);

        return null;
    }
}
