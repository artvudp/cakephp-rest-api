<?php

namespace RestApi\Error;

use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Error\ExceptionRenderer;
use Cake\Network\Response;
use Cake\Utility\Xml;
use Exception;
use RestApi\Controller\ApiErrorController;
use RestApi\Routing\Exception\InvalidTokenException;
use RestApi\Routing\Exception\InvalidTokenFormatException;
use RestApi\Routing\Exception\MissingTokenException;

/**
 * API Exception Renderer.
 *
 * Captures and handles all unhandled exceptions. Displays valid json response.
 */
class ApiExceptionRenderer extends ExceptionRenderer
{

    /**
     * Returns error handler controller.
     *
     * @return ApiErrorController
     */
    protected function _getController(): Controller
    {
        return new ApiErrorController();
    }

    /**
     * Handles MissingTokenException.
     *
     * @param MissingTokenException $exception MissingTokenException
     *
     * @return type
     */
    public function missingToken($exception)
    {
        //called from ExceptionRenderer.php by render function
        return $this->__prepareResponse($exception, ['customMessage' => true]);
    }

    /**
     * Handles InvalidTokenFormatException.
     *
     * @param InvalidTokenFormatException $exception InvalidTokenFormatException
     *
     * @return Response
     */
    public function invalidTokenFormat($exception)
    {
        //called from ExceptionRenderer.php by render function
        return $this->__prepareResponse($exception, ['customMessage' => true]);
    }

    /**
     * Handles InvalidTokenException.
     *
     * @param InvalidTokenException $exception InvalidTokenException
     *
     * @return Response
     */
    public function invalidToken($exception)
    {
        //called from ExceptionRenderer.php by render function
        return $this->__prepareResponse($exception, ['customMessage' => true]);
    }

    /**
     * Prepare response.
     *
     * @param Exception $exception Exception
     * @param array     $options   Array of options
     *
     * @return Response
     */
    private function __prepareResponse($exception, $options = [])
    { 
        $response = $this->_getController()->getResponse();
        $responseFormat = $this->_getController()->responseFormat;
        Configure::write('apiExceptionMessage', $exception->getMessage());
        $responseData = [
            $responseFormat['statusKey'] => !empty($options['responseStatus']) ? $options['responseStatus'] : $responseFormat['statusNokText'],
            $responseFormat['resultKey'] => [
                $responseFormat['errorKey'] => ($response->getStatusCode() < 500) ? 'Not Found' : 'An Internal Error Has Occurred.',
            ],
        ];
        
        if ((isset($options['customMessage']) && $options['customMessage']) || Configure::read('ApiRequest.debug')) {
            $responseData[$responseFormat['resultKey']][$responseFormat['errorKey']] = $exception->getMessage();
        }

        if ('xml' === Configure::read('ApiRequest.responseType')) {
            $body = $response->getBody();
            $body->write(Xml::fromArray([Configure::read('ApiRequest.xmlResponseRootNode') => $responseData], 'tags')->asXML());
            $response->withBody($body);
        } else {
            $body = $response->getBody();
            $body->write(json_encode($responseData));
            $response->withBody($body);
        }
        $this->controller->response = $response;

        return $response;
    }
}
