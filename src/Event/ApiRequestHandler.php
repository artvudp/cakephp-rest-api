<?php

namespace RestApi\Event;

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use RestApi\Utility\ApiRequestLogger;

/**
 * Event listner for API requests.
 *
 * This class binds the different events and performs required operations.
 */
class ApiRequestHandler implements EventListenerInterface
{

    /**
     * Event bindings.
     *
     * @return array
     */
    public function implementedEvents()
    {
        return [
            'Dispatcher.beforeDispatch' => [
                'callable' => 'beforeDispatch',
                'priority' => 0,
            ],
            'Dispatcher.afterDispatch' => [
                'callable' => 'afterDispatch',
                'priority' => 9999,
            ],
            'Controller.shutdown' => [
                'callable' => 'shutdown',
                'priority' => 9999,
            ],
        ];
    }

    /**
     * Handles incoming request and its data.
     *
     * @param Event $event The beforeDispatch event
     */
    public function beforeDispatch(Event $event)
    {
        $this->buildResponse($event);
        Configure::write('requestLogged', false);
        $request = $event->getData()['request'];
        if ('OPTIONS' === $request->getMethod()) {
            $event->stopPropagation();
            $response = $event->getData()['response'];
            $response->getStatusCode(200);

            return $response;
        }

        if (empty($request->getData())) {
//            $request->data = $request->input('json_decode', true);
        }
    }

    /**
     * Updates response headers.
     *
     * @param Event $event The afterDispatch event
     */
    public function afterDispatch(Event $event)
    {
        $this->buildResponse($event);
    }

    /**
     * Logs the request and response data into database.
     *
     * @param Event $event The shutdown event
     */
    public function shutdown(Event $event)
    {
        $request = $event->getSubject()->request;
        if ('OPTIONS' === $request->getMethod()) {
            return;
        }

        if (!Configure::read('requestLogged') && Configure::read('ApiRequest.log')) {
            if (Configure::read('ApiRequest.logOnlyErrors')) {
                $responseCode = $event->getSubject()->httpStatusCode;
                $logOnlyErrorCodes = Configure::read('ApiRequest.logOnlyErrorCodes');
                if (empty($logOnlyErrorCodes) && $responseCode !== 200) {
                    ApiRequestLogger::log($request, $event->getSubject()->response);
                } elseif (in_array($responseCode, $logOnlyErrorCodes)) {
                    ApiRequestLogger::log($request, $event->getSubject()->response);
                }
            } else {
                ApiRequestLogger::log($request, $event->getSubject()->response);
            }
        }
    }

    /**
     * Prepares the response object with content type and cors headers.
     *
     * @param Event $event The event object either beforeDispatch or afterDispatch
     *
     * @return bool true
     */
    private function buildResponse(Event $event)
    {
        $request = $event->getData()['request'];
        $response = $event->getData()['response'];

        if ('xml' === Configure::read('ApiRequest.responseType')) {
            $response->withType('xml');
        } else {
            $response->withType('json');
        }

        $response->cors($request)
                ->allowOrigin(Configure::read('ApiRequest.cors.origin'))
                ->allowMethods(Configure::read('ApiRequest.cors.allowedMethods'))
                ->allowHeaders(Configure::read('ApiRequest.cors.allowedHeaders'))
                ->allowCredentials()
                ->maxAge(Configure::read('ApiRequest.cors.maxAge'))
                ->build();

        return true;
    }
}
