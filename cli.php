#!/usr/bin/php
<?php
require_once __DIR__ . '/init/common.php';

/**
 * Runtime configuration
 */
$publicKey = \Config::read('cli_public_key');
$privateKey = \Config::read('cli_private_key');
$requestMethod = 'CLI';
//end of runtime configuration

if (array_key_exists(1, $argv) && $argv[1]) {
    $detectedInputData = array();

    /**
     * Collect command line query string which is the default way to pass parameters in
     */
    if (array_key_exists(2, $argv) && $argv[2]) {
        parse_str($argv[2], $detectedInputData);
    }

    $detectedInputData = array_merge($detectedInputData, [
        'action' => $argv[1],
        'timestamp' => time(),
    ]);

    $detectedInputData['public_key'] = $publicKey;
    $detectedInputData['signature'] = \Signet::plainSha1($detectedInputData, $privateKey);

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
        if (\Config::read('debug')) {
            $response = '<br/>' . $ex->getFile() . ' (Line:' . $ex->getLine() . ')' . '<pre>' . print_r($ex->getTrace(), true) . '</pre>' . '<br/>' . $ex->getMessage() . '<br/>' . $ex->getPrevious() . '<br/>';
        }
        $response .= '\Dispatcher chrashed';
    }
} else {
    $response = 'Action argument is missing' . "\r\n";
}

die($response . "\r\n");
