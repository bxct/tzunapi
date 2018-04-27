<?php

/**
 * @todo this is a temporary solution only for use paired to API sources itself. 
 * When API version is released, this file is to contain a complete static list of available services and operations
 */

if(\Config::read('available_operations') === false) {

    $servicesDir = FS_ROOT . FS_DS . 'application' . FS_DS . 'services' . FS_DS . '0.1';

    $availableServices = array();

    if ($handle = opendir($servicesDir)) {
        /* This is the correct way to loop over the directory. */
        while (false !== ($entry = readdir($handle))) {
            if ($entry !== '..' and $entry !== '.' and $entry !== 'baseservice.php' and strpos($entry, '.php')) {
                $availableServices[] = (explode('.', $entry)[0]);
            }
        }
    }

    $availableOperations = array();

    foreach($availableServices as $serviceFileName){
        $fileFunctions = array();
        $serviceContent = file_get_contents($servicesDir . FS_DS . $serviceFileName . '.php');
        preg_match_all('/[(public\s)]*function\s([a-zA-Z0-9\_]*)[\(]/', $serviceContent, $fileFunctions);
        if(array_key_exists(1, $fileFunctions)) {
            foreach($fileFunctions[1] as $functionName){
                preg_match('/' . $functionName . '\((.*)\)/', $serviceContent, $argumentMatches);
                $arguments = array();
                if(array_key_exists(1, $argumentMatches)) {
                    $arguments = array_map(function($elt){return trim($elt);}, explode(',', $argumentMatches[1]));
                    if(!empty($arguments)) {
                        foreach($arguments as $k => $arg) {
                            if(strpos($arg, '=')){
                                $argParts = array_map(function($elt){return trim($elt);}, explode('=', $arg));
                                $arguments[str_replace('$', '', $argParts[0])] = $argParts[1];
                            } else {
                                $arguments[str_replace('$', '', $arg)] = false;
                            }
                            unset($arguments[$k]);
                        }
                    }
                }
                $availableOperations[$serviceFileName . '_0_1'][$functionName] = $arguments;
            }
        }
    }
        
    if(!empty($availableOperations)) {
        \Config::write('available_operations', $availableOperations);
    }

}