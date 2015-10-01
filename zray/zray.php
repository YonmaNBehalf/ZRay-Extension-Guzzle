<?php

use Guzzle\Http as GuzzleHttp;
use GuzzleHttp\Client;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Message\Request;

$zre = new ZrayExtension('Requests');

$zre->setEnabledAfter('GuzzleHttp\Client::send');

/// collect features mapping calls
$zre->traceFunction('GuzzleHttp\Client::send', function($context, &$storage){}, function($context, &$storage) use ($zre) {


    $request = $context['functionArgs'][0]; // @var $request Request

    if (! ($request instanceof Request)) {
        // not a single request
        return ;
    }

    $result = $context['returnValue']; /* @var $result Response */
    if (! ($result instanceof Response)) {
        /// no response provided
        return ;
    }

    $body = $result->getBody(true);
    $statusCode = $result->getStatusCode();

    $params = array();
    if (! in_array($request->getMethod(), array('post', 'put', 'patch'))) {
        $params = json_encode($request->getQuery());
    }

    $jsonBody = json_decode($body);

    $storage['Guzzle'][] = array(
        'method' => $request->getMethod(),
        'url' => $result->getEffectiveUrl(),
        'headers' => json_encode($request->getHeaders()),
        'params' => ($params),
        'responseRawBody' => $body,
        'responsePayload' => $jsonBody ? $jsonBody : $body,
        'responseCode' => $statusCode,
        'duration' => $context['durationInclusive']
    );

});