<?php

namespace OutputProcessors;

/**
 * Class containing logic to pack output data as XML document
 */
class Xml implements \Interfaces\OutputProcessor {
    
    /**
     * Generates XML output in a standard API response form
     * 
     * @todo 
     * 1. play with potentially more convenient options of packing data into XML
     * 2. probably, make XML RPC compliant
     * 
     * @param string $status overall processing status
     * @param mixed $output any output to be encoded
     * @return string
     * @throws Exception
     */
    public function generate($status, $output) {
        $response = [
            'status' => $status,
        ];
        if (is_object($output) && method_exists($output, 'dump')) {
            $response['body'] = get_object_vars($output->dump());
        }
        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();
        return $serializer->serialize($response, 'xml');
    }

}
