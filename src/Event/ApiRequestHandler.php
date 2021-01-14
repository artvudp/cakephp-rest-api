<?php

namespace RestApi\Event;

use Cake\Event\EventListenerInterface;

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
    public function implementedEvents(): array
    {
        return [];
    }
}
