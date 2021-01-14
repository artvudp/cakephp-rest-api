<?php

namespace RestApi\Controller;

use Cake\Core\Configure;
use Cake\Event\EventInterface;

/**
 * Api error controller
 *
 * This controller will sets configuration to render errors
 */
class ApiErrorController extends AppController
{
    /**
     * beforeRender callback.
     *
     * @param Event $event Event.
     * @return null
     */
    public function beforeRender(EventInterface $event)
    {
        parent::beforeRender($event);

        $this->viewBuilder()->setClassName('RestApi.ApiError');

        return null;
    }
}
