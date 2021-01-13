<?php

namespace RestApi\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Network\Exception\UnauthorizedException;
use Cake\Network\Response;
use Firebase\JWT\JWT;
use RestApi\Routing\Exception\InvalidTokenException;
use RestApi\Routing\Exception\InvalidTokenFormatException;
use RestApi\Routing\Exception\MissingTokenException;

/**
 * Access control component class.
 *
 * Handles user authentication and permission.
 */
class AccessControlComponent extends Component
{

    /**
     * beforeFilter method.
     *
     * Handles request authentication using JWT.
     *
     * @param Event $event The startup event
     * @return Response
     */
    public function beforeFilter(Event $event)
    {
        if (Configure::read('ApiRequest.jwtAuth.enabled')) {
            return $this->_performTokenValidation($event);
        }

        return true;
    }

    /**
     * Performs token validation.
     *
     * @param Event $event The startup event
     * @return bool
     * @throws UnauthorizedException If missing or invalid token
     */
    protected function _performTokenValidation(Event $event)
    {
        $request = $event->getSubject()->request;

        if (!empty($request->getParam('allowWithoutToken')) && $request->getParam('allowWithoutToken')) {
            return true;
        }

        $token = '';

        $header = $request->getHeader('Authorization');

        if (!empty($header[0])) {
            $parts = explode(' ', $header[0]);

            if (count($parts) < 2 || empty($parts[0]) || !preg_match('/^Bearer$/i', $parts[0])) {
                throw new InvalidTokenFormatException();
            }

            $token = $parts[1];
        } elseif (!empty($this->request->getQuery('token'))) {
            $token = $this->request->getQuery('token');
        } elseif (!empty($request->getParam('token'))) {
            $token = $request->getParam('token');
        } else {
            throw new MissingTokenException();
        }

        try {
            $payload = JWT::decode($token, Configure::read('ApiRequest.jwtAuth.cypherKey'), [Configure::read('ApiRequest.jwtAuth.tokenAlgorithm')]);
        } catch (\Exception $e) {
            throw new InvalidTokenException();
        }

        if (empty($payload)) {
            throw new InvalidTokenException();
        }

        $controller = $this->_registry->getController();

        $controller->jwtPayload = $payload;

        $controller->jwtToken = $token;

        Configure::write('accessToken', $token);

        return true;
    }
}
