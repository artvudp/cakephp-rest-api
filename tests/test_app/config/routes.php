<?php

use Cake\Core\Plugin;
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\Routing\Route\DashedRoute;

Router::defaultRouteClass(DashedRoute::class);

Router::scope('/', function (RouteBuilder $routes) {
    $routes->connect('/foo/public-action', ['controller' => 'Foo', 'action' => 'publicAction', 'allowWithoutToken' => true]);
    $routes->connect('/foo/bar', ['controller' => 'Foo', 'action' => 'bar', 'allowWithoutToken' => true]);
    $routes->fallbacks(DashedRoute::class);
});

//Load all plugin routes
Plugin::routes();
