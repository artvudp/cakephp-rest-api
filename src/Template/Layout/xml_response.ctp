<?php

use Cake\Core\Configure;
use Cake\Utility\Xml;

echo $this->element('RestApi.response_default');

echo Xml::fromArray([Configure::read('ApiRequest.xmlResponseRootNode') => $response], 'tags')->asXML();
