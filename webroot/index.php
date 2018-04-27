<?php

require_once '../init/common.php';

$requestMethod = $_SERVER['REQUEST_METHOD'];

$detectedInputData = [
    'input_body' => file_get_contents('php://input'),
];

/**
 * Collect post data for all (including older (< PHP5.6)) PHP versions
 */
if($requestMethod === 'POST') {
    foreach ($_POST AS $key => $value) {
        $detectedInputData[$key] = $value;
    }
}

/**
 * Collect GET vars as well
 */
foreach ($_GET AS $key => $value) {
    $detectedInputData[$key] = $value;
}

/**
 * Collect also files' data if present
 */
if(!empty($_FILES)) {
    $detectedInputData['files'] = $_FILES;
}

$response = '';

try {
    /**
     * Create instance of dispatcher and pass further processing over to it
     */
    $dispatcher = new \Dispatcher($requestMethod, $detectedInputData);
    try {
        $response = $dispatcher->run()->response();
    } catch (Exception $ex) {
        $response = $dispatcher->errorResponse($ex);
    }
} catch (Exception $ex) {
    /**
     * Something failed => show details in debugging mode and response message
     */
    if(\Config::read('debug')) {
        $response = '<br/>'. $ex->getFile(). ' (Line:'. $ex->getLine(). ')' . '<pre>' . print_r($ex->getTrace(), true). '</pre>'.  '<br/>'. $ex->getMessage(). '<br/>'. $ex->getPrevious(). '<br/>';
    }
    $response .= '\Dispatcher chrashed';
}

die($response);
