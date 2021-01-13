<?php

use Cake\Core\Configure;
use Cake\Utility\Xml;

echo $this->element('RestApi.error_default');

echo Xml::fromArray([Configure::read('ApiRequest.xmlResponseRootNode') => $response], 'tags')->asXML();
