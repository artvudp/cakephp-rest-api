<?php

namespace RestApi\Middleware;

use Cake\Core\App;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Event\EventManager;
use Cake\Utility\Inflector;
use RestApi\Event\ApiRequestHandler;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;

class RestApiMiddleware extends ErrorHandlerMiddleware
{
    /**
     * Wrap the remaining middleware with error handling.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @param \Psr\Http\Server\RequestHandlerInterface $handler The request handler.
     * @return \Psr\Http\Message\ResponseInterface A response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $params = (array)$request->getAttribute('params', []);
            if (isset($params['controller'])) {
                $controllerName = $params['controller'];
                $firstChar = substr($controllerName, 0, 1);
                if (strpos($controllerName, '\\') !== false ||
                    strpos($controllerName, '/') !== false ||
                    strpos($controllerName, '.') !== false ||
                    $firstChar === strtolower($firstChar)
                ) {
                    return $handler->handle($request);
                }
                $type = 'Controller';
                
                if (isset($params['prefix']) && $params['prefix']) {
                    $prefix = Inflector::camelize($params['prefix']);
                    $type = 'Controller/' . $prefix;
                }
                $className = App::className($controllerName, $type, 'Controller');
                
                $controller = ($className) ? new $className() : null;
                
                if ($controller && is_subclass_of($controller, 'RestApi\Controller\ApiController')) {
                    
                    if (isset($this->renderer)) {
                        $this->_config['renderer'] = 'RestApi\Error\ApiExceptionRenderer';
                    } else {
                        $this->_config['exceptionRenderer'] = 'RestApi\Error\ApiExceptionRenderer';
                    }
                    
                    EventManager::instance()->on(new ApiRequestHandler());
                }
                unset($controller);
            }

            return $handler->handle($request);
        } catch (\Exception $e) {
            return $this->handleException($e, $request);
        }
    }
}
