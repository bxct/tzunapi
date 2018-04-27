<?php

if(!class_exists('ApiClient')) {

    class ApiClient {

        private $_curl = null;

        private $_apiEndpoint = null;

        private $_publicKey = null;

        private $_privateKey = null;

        private $_format = null;

        private $_errorStack = array();

        private $_responseCode = 404;

        private $_requiredParameters = array(
            'public_key', 'service', 'operation'
        );

        private $_supportedMethods = array(
            'get', 'post', 'put', 'patch', 'delete'
        );

        private $_supportedFormats = array(
            'json', 'serialize', 'xml', 'html'
        );

        private function _errorResponse($errorCode = 400) {
            $this->_setErrorCode($errorCode);
            return implode("\r\n", $this->_errorStack);
        }

        private function _addError($message, $errorCode = false) {
            if(!in_array($message, $this->_errorStack)) {
                $this->_errorStack[] = $message;
            }
            if($errorCode) {
                $this->_setErrorCode($errorCode);
            }
            return $this->_errorStack;
        }

        /**
         * Specifies HTTP response code to be used for errors (@todo in what way???)
         * 
         * @param int $errorCode
         */
        private function _setErrorCode($errorCode) {
            $this->_responseCode = $errorCode;
        }

        /**
         * Checks if all params required for API call are present
         * 
         * @param array $requestParams
         * @return array
         */
        private function validateParams($requestParams) {
            if (!is_array($requestParams)) {
                $this->_addError('Parameter list must be an array');
                $this->_setErrorCode(400);
            }

            $requiredParams = $this->_requiredParameters;

            //'action' can actually be a substitude for 'operation' and 'service' ...
            if(array_key_exists('action', $requestParams)) {
                $requiredParams = array_flip($requiredParams);
                // ... so unset those requirements if action is available
                unset($requiredParams['operation'], $requiredParams['service']);
                $requiredParams = array_flip($requiredParams);
            }

            // check that all required parameters are not empty
            foreach ($requiredParams as $key) {
                if (empty($requestParams[$key])) {
                    $this->_addError("Missing or empty parameter: $key");
                    $this->_setErrorCode(400);
                }
            }

            // validate that requested method is supported
            $requestParams['method'] = strtolower($requestParams['method']);
            if (!in_array($requestParams['method'], $this->_supportedMethods)) {
                $this->_addError('Unsupported Request Method: ' . $requestParams['method']);
                $this->_setErrorCode(400);
            }

            // check that the requested response format is supported
            $this->_format = strtolower($this->_format);
            if (!in_array($this->_format, $this->_supportedFormats)) {
                $this->_addError('Unsupported Format: ' . $this->_format);
                $this->_setErrorCode(400);
            }

            return $this->_errorStack;
        }

        private function seal($data, $privateKey) {
            return \Signet::plainSha1($data, $privateKey);
        }

        public function __construct($publicKey, $privateKey, $apiEndpoint, $format = 'json') {
            $this->_publicKey = $publicKey;
            $this->_privateKey = $privateKey;
            $this->_format = $format;
            $this->_apiEndpoint = $apiEndpoint;
            $this->_curl = new \Curl();
        }

        /**
         * Sends the call to Client API service
         * 
         * @param array $data
         * @param string $requestType the type of HTTP call: POST/GET/PUT etc
         * @param integer $timeout
         * 
         * @return boolean
         */
        public function send($data, $requestType = 'GET', $timeout = false) {
            
            if($timeout) {
                set_time_limit((int)$timeout);             
                $this->_curl->setTimeout((int)$timeout);
            } else {
                set_time_limit(1200);             
                $this->_curl->setTimeout(1200);
            }
            
            $data['timestamp'] = time();
            $data['method'] = strtolower($requestType);
            $data['public_key'] = $this->_publicKey;
            if($errors = $this->validateParams($data)) {
                return $this->_errorResponse();
            }
            $data['signature'] = $this->seal($data, $this->_privateKey);
            if(array_key_exists('action', $data)) {
                $url = str_replace(':/', '://', str_replace('//', '/', $this->_apiEndpoint . '/' . $data['action']));
            } else {
                $url = $this->_apiEndpoint;
            }
    //        var_dump($data); exit;
    //        $data = 'ddddd';
            if($response = $this->_curl->{$data['method']}($url, $data)) {
                return $response;
            }
        }

    }

}