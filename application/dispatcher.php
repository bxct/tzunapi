<?php

/**
 * Performs combining of available data into groups of parametes. 
 * It also detects required service class and passes all parameters accordingly and generates output
 * in format requested by client application
 */
class Dispatcher {

    /**
     * Holds the data on currently authorized account (AKA session data for service)
     * 
     * @var array
     */
    private $authData = array();
    
    /**
     * The service class containing the business logic for supported operations (AKA controller)
     * 
     * @var \ApiService
     */
    private $service = null;
    
    /**
     * The detected name of requested operation
     * 
     * @var string
     */
    private $operation = '';
    
    /**
     * All the data received through input
     * 
     * @var array
     */
    private $detectedInputData = ['public_key' => null];
    
    /**
     * Parameters which were passed via URL
     * 
     * @var array
     */
    private $overrideParams = [];
    
    /**
     * Result of the request
     * Possible values: 'processing', 'success', 'failure'
     * 
     * @var string
     */
    private $status = 'processing';
    
    /**
     * The stack of output data
     * 
     * @var mixed
     */
    private $output = '';
    
    /**
     * The object which is capable of interpreting the $this->output data into one of supported text formats
     * 
     * @var \OutputProcessor 
     */
    private $outputProcessor = null;
    
    private $supportedFormats = [
        'json', 'serialize', 'xml', 'html'
    ];

    /**
     * Detects the service and parses input data
     * 
     * @param string $requestMethod  * Request method should always correspond the API call purpose
     *      GET /orders - Retrieves a list of orders
     *      GET /orders/7 - Retrieves a specific order
     *      POST /orders - Creates a new order
     *      POST /orders/create - Creates a new order
     *      PUT /orders/7 - Updates order #7
     *      PATCH /orders/7 - Partially updates order #7
     *      DELETE /orders/7 - Deletes order #7
     * @param array $detectedInputData request variables (includes 'input_body' - bare PHP input)
    
     */
    public function __construct($requestMethod, &$detectedInputData) {
        
        if(array_key_exists('format', $detectedInputData)) {
            $format = strtolower($detectedInputData['format']);
        } else {
            $format = \Config::read('default_output_format');
        }
        
        if(empty($format) || !in_array($format, $this->supportedFormats)) {
            throw \ExceptionHandler::exception(__('Output format is not supported'));
        }
        
        $opClassName = '\OutputProcessors\\' . ucfirst($format);
        
        $this->outputProcessor = new $opClassName();
        
        /**
        * Detect various types of request (POST/GET/PUT/DELETE etc)
        * @todo Check for efficient options of (non-)parsing data for some combinations of request methods and data
        * (e.g. when encoded data is passed in bare POST request body)
        */
        foreach($detectedInputData as $inputKey => &$inputVal) {
            $this->detectedInputData[$inputKey] = $inputVal;
        }
        
        switch ($requestMethod) {
            case 'HEAD':
            case 'PUT':
                if(!isset($operationGuess)) {
                    $operationGuess = 'update';
                }
            case 'DELETE':
                if(!isset($operationGuess)) {
                    $operationGuess = 'delete';
                }
            case 'TRACE':
            case 'OPTIONS':
            case 'CONNECT':
            case 'PATCH':
                parse_str($detectedInputData['input_body'], $inputBodyData);
                if(!empty($inputBodyData)) {
                    foreach($inputBodyData as $parameter => $value){
                        $this->detectedInputData[$parameter] = $value;
                    }
                }
            case 'POST':
                if(!isset($operationGuess)) {
                    if(array_key_exists('id', $this->detectedInputData) && !empty($this->detectedInputData['id'])) {
                        $operationGuess = 'update';
                    } else {
                        $operationGuess = 'create';
                    }
                }
            case 'GET':
                if(!isset($operationGuess)) {
                    $operationGuess = 'index';
                }
            default:
                /**
                 * Sealing and finding auth credentials HERE => before any new data elements are detected
                 */
                $sealed = false;
                if ($this->authData = $this->getAuth($this->detectedInputData['public_key'])) {
                    $signature = \Signet::plainSha1($this->detectedInputData, $this->authData->getPrivateKey());
                    //The client-side encoded data and data being verified must be the same.
                    $sealed = ((!empty($this->detectedInputData['signature'])) && ($this->detectedInputData['signature'] === $signature));
                }
                if ($requestMethod != 'CLI' && $this->detectedInputData['public_key'] == \Config::read('cli_public_key')) {
                    $sealed = false;
                }
                break;
        }
        
        /**
         * Mix into $detectedInputData the following parameters: 'request_method', 'user_id', 'sealed'.
         * Those elements could be considered having reserved names => if parameters with same names were passed, values will be overridden
         */
        $this->detectedInputData['request_method'] = $requestMethod;
        if($this->authData) {
            $this->detectedInputData['user_id'] = $this->authData->getId();
        } else {
            $this->detectedInputData['user_id'] = false;
        }
        $this->detectedInputData['sealed'] = $sealed;
        
        /**
         * Detect service name ...
         */
        if(!array_key_exists('version', $this->detectedInputData)) {
            $this->detectedInputData['version'] = \Config::read('stable_version');
        }
        
        /**
         * ...  and operation to call
         * @todo log requests and send errors response if response quota reached
         */
        if(array_key_exists('action', $this->detectedInputData) && !empty($this->detectedInputData['action'])) {
            if($parsedAction = $this->parseActionString($this->detectedInputData['action'], isset($operationGuess)?$operationGuess:false)) {
                $this->detectedInputData['service'] = array_shift($parsedAction);
                $this->detectedInputData['operation'] = array_shift($parsedAction);
                $this->overrideParams = array_shift($parsedAction);
            }
        }
        
        if(!array_key_exists('operation', $this->detectedInputData) || empty($this->detectedInputData['operation'])) {
            if(isset($operationGuess)) {
                $this->detectedInputData['operation'] = $operationGuess;
            } else {
                $this->detectedInputData['operation'] = 'index';
            }
        }
        
        if(!array_key_exists('service', $this->detectedInputData) || empty($this->detectedInputData['service'])) {
            $this->detectedInputData['service'] = 'Hello_0_1';
        }
    }
    
    /**
     * Executes the service operation if allowed
     * 
     * @return \Dispatcher
     * 
     * @throws Exception
     */
    public function run() {
        /**
        * Handle service version and instantiate a service
        */
        if ($serviceName = $this->checkFullServiceName($this->detectedInputData['service'], $this->detectedInputData['version'])) {
            $this->service = new $serviceName($this->authData, $this->detectedInputData['sealed']);
        } else {
            throw \ExceptionHandler::wrongParametersException(__('Service does not exist'));
        }

        $params = (new \ReflectionMethod($this->service, $this->detectedInputData['operation']))->getParameters();
        //In case function expects some parameters => collect values from array of detecte input
        if(!empty($params)) {
            $i = 0;
            foreach ($params as $k => $reflectionParameter) {
                if (array_key_exists($reflectionParameter->getName(), $this->detectedInputData)) {
                    $params[$k] = $this->detectedInputData[$reflectionParameter->getName()];
                } else if (array_key_exists($i, $this->overrideParams)) {
                    $params[$k] = &$this->overrideParams[$i];
                } else {
                    $params[$k] = false;
                }
                $i++;
            }
        }
        try {
            //Log input if in debug mode
            if(\Config::debug()) {
                \Logger::request(array('service' => get_class($this->service), 'operation' => $this->detectedInputData['operation'], 'request' => $this->detectedInputData));
            }
            //Run operation with parameters passed
            $this->setStatus('success');
            $this->output = call_user_func_array(array($this->service, $this->detectedInputData['operation']), $params);
            
            //Log output if in debug mode
            if(\Config::debug()) {
                \Logger::response(array('service' => get_class($this->service), 'operation' => $this->detectedInputData['operation'], 'response' => $this->output));
            }
        } catch(Exception $ex) {
            //Catch any types of uncaught exceptions
            $this->setStatus('failure');
            $response = new \stdClass();
            $response->message = $ex->getMessage();
            $this->output = $response;
        }
        return $this;
    }
    
    /**
     * Parses the action string passed through GET into service, operation name and parameters array
     * 
     * @param string $actionString
     * @return array service and action name array
     */
    public static function parseActionString($actionString, $operationGuess = false) {
        $actionParams = array();
        if($actionParts = explode('/' , $actionString)) {
            $service = array_shift($actionParts);
            $operation = array_shift($actionParts);
            if(is_numeric($operation) && $operationGuess) {
                array_unshift($actionParts, $operation);
                $operation = $operationGuess;
            }
            if($operation === 'create' && (array_key_exists(0, $actionParts) && !empty($actionParts[0]))) {
                $operation = 'update';
            }
            $actionParams = array(
                $service,
                $operation,
                $actionParts
            );
        }
        return $actionParams;
    }
    
    /**
     * Combines data available for service identification if possible
     * 
     * @param string $serviceName
     * @param string $version
     * @return string
     * @throws Exception
     */
    public static function checkFullServiceName($serviceName, $version = null) {
        $className = '';
        $namespaceName = '';
        if(strpos($serviceName, '_')) {
            if(preg_match('/^[a-zA-Z\_]+\_[0-9]{1,2}\_[0-9]{1}$/', $serviceName)) {
                //Usecase A. Service and version are specified in action (full service name in request URL)
                //Usecase B. Service with version is specified in parameters (full service name in paramter)
                $serviceNameParts = explode('_', $serviceName);
                foreach($serviceNameParts as $part) {
                    if(is_numeric($part)) {
                        $namespaceName .= '_' . $part;
                    } else {
                        $className .= ucfirst($part);
                    }
                }
            } else {
                if(preg_match('/^[0-9]{1,2}\.[0-9\.]+$/', $version)) {
                    //Usecase C. Service is specified in action URL, version is passed via params
                    $serviceNameParts = explode('_', $serviceName);
                    $serviceName = '';
                    foreach($serviceNameParts as $part) {
                        $serviceName .= ucfirst($part);
                    }
                    $namespaceName = '_' . implode('_', explode('.', $version));
                    $className = ucfirst($serviceName);
                }
            }
        } else {
            if(preg_match('/^[0-9]{1,2}\.[0-9\.]+$/', $version)) {
                //Usecase C. Service is specified in action URL, version is passed via params
                //Usecase D. Service with version is specified in parameters
                $className = ucfirst($serviceName);
                $namespaceName = '_' . implode('_', explode('.', $version));
            }
        }
        
        if(empty($className)) {
            throw \ExceptionHandler::wrongParametersException(__('Could not reliably determine service and/or version'));
        }

        $serviceClassName = '\Services' . $namespaceName . '\\' . $className;
        
        if(!class_exists($serviceClassName)) {
            throw \ExceptionHandler::wrongParametersException(__('Service does not exist'));
        }
        
        return $serviceClassName;
    }
    
    /**
     * Assign current processing status using one of allowed values
     * 
     * @param string $status
     * @return boolean
     */
    public function setStatus($status) {
        if(in_array($status, array('processing', 'success', 'failure'))) {
            $this->status = $status;
            return true;
        }
        return false;
    }
    
    /**
     * Returns current processing status
     * 
     * @return string
     */
    public function getStatus(){
        return $this->status;
    }

    public function response($status = false) {
        if(!$status) {
            $status = $this->getStatus();
        }
        return $this->outputProcessor->generate($status, $this->output);
    }

    public function errorResponse($exception) {
        /**
         * @todo Handle error codes
         * 400 Bad Request
         * 403 Forbidden
         * 404 Not Found
         * 405 Method Not Allowed 
         * 409 (??) Conflict 
         * 411 Length Required
         * 500 Internal Server Error 
         * 503 Service Unavailable
         */
        $this->setStatus('failure');
        $response = new \stdClass();
        $response->message = $exception->getMessage();
        return $this->outputProcessor->generate($this->getStatus(), $response);
    }
    
    /**
     * Retrieves and returns the details of the user associated with given private_key
     * 
     * @param string $publicKey
     * @return mixed
     */
    private function getAuth($publicKey = false) {
        return \Auth\Provider::getUser($publicKey);
    }

}
