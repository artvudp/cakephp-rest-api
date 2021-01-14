<?php

if (empty($response[$responseFormat['resultKey']])) {
    $response[$responseFormat['resultKey']] = [
        $responseFormat['messageKey'] => $responseFormat['defaultMessageText']
    ];
}
