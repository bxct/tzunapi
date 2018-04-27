<?php

require_once('../../init/common.php');

class SignetTestPerformance extends \PHPUnit_Framework_TestCase {
    
    private static $d = array (
            'input_body' => '',
            'public_key' => 'dsfasdfasdf',
            'private_key' => 'asdfasdfasdfasdf',
            'service' => 'bundle_apps_0_1',
            'operation' => 'read',
            'version' => '0.1',
            'bundle_id' => '123',
            'app_id' => '456',
            'id' => '70',
            'submit' => 'Submit',
            'action' => 'bundle_apps_0_1/read/333/444',
            'test_signature' => '3231312313',
            'something_else' => '33',
            'filxxes' =>
                array (
                    'fccile' =>
                        array (
                            'name' => '',
                            'type' => '',
                            'tmp_name' => '',
                            'error' => 4,
                            'size' => 0,
                            'dsd' => array(
                                'random_array' => array(
                                    'ddd' => array(
                                        'a', 
                                        'b', 
                                        'c' => array(
                                            'd', 
                                            'e')
                                    ),
                                ),
                            )
                    ),
                ),
        );
    
    private static $d1 = array (
            'input_body' => '',
            'public_key' => 'dsfasdfasdf',
            'private_key' => 'asdfasdfasdfasdf',
            'service' => 'bundle_apps_0_1',
            'operation' => 'read',
            'version' => '0.1',
            'bundle_id' => '123',
            'app_id' => '456',
            'id' => '70',
            'submit' => 'Submit',
            'action' => 'bundle_apps_0_1/read/333/444',
            'test_signature' => '3231312313',
            'something_else' => '33',
        );
    
    public function test_more_considerable_keys() {
        
        $privateKey = uniqid();
        
        self::$d['timestamp'] = time();
        
        $tStart = microtime(true);
        $signet1 = new \Signet();
        for($i = 0; $i<=10000; $i++) {
            $signet1->plainSha1(self::$d, $privateKey);
        }
        $tDiff = microtime(true) - $tStart;
        
        $tStart2 = microtime(true);
        $signet2 = new \Signet(array_keys(self::$d));
        for($i = 0; $i<=10000; $i++) {
            $signet2->plainSha1(self::$d, $privateKey);
        }
        $tDiff2 = microtime(true) - $tStart2;
        
        echo "r\nLess data gives: " . $tDiff . "\r\n";        
        echo 'More data gives: ' . $tDiff2 . "\r\n";
        
        $this->assertLessThan($tDiff2, $tDiff, 'More keys is faster!');
        
        echo "\r\n===================================================\r\n";
        echo "\r\n===================================================\r\n";
    }
    
//    public function test_prepareSignatureOrigin_old_vs_new() {
//        
//        self::$d1['timestamp'] = time();
//        
//        $tStart = microtime(true);
//        for($i = 0; $i<=30000; $i++) {
//            $so1 = \Signet::prepareSignatureOriginCrossplatform(self::$d1);
//        }
//        $tDiff = microtime(true) - $tStart;
//        
//        echo "\r\nHash 1: ", $so1, "\r\n";
//        echo 'New result is: ' . $tDiff . "\r\n";
//        
//        $tStart2 = microtime(true);
//        for($i = 0; $i<=30000; $i++) {
//            $so2 = \Signet::prepareSignatureOriginCrossplatformOld(self::$d1);
//        }
//        $tDiff2 = microtime(true) - $tStart2;
//        
//        echo "\r\nHash 2: ", $so2, "\r\n";
//        echo 'Old result is: ' . $tDiff2 . "\r\n";
//        
//        $this->assertLessThan($tDiff2, $tDiff, 'Older is faster');
//        
//        echo "\r\n===================================================\r\n";
//        echo "\r\n===================================================\r\n";
//    }
    
    public function test_prepareSignatureOriginCrossplatform() {
        
        self::$d['timestamp'] = time();
        
        $tStart = microtime(true);
        for($i = 0; $i<=30000; $i++) {
            $so1 = \Signet::prepareSignatureOriginCrossplatform(self::$d, true);
        }
        $tDiff = microtime(true) - $tStart;
        
        echo "\r\nHash 1: ", $so1, "\r\n";
        echo 'Result is: ' . $tDiff . "\r\n";
        
        $this->assertTrue((boolean)preg_match('/[a-z0-9\=]/i', $so1), 'Wrong format');
        
        echo "\r\n===================================================\r\n";
        echo "\r\n===================================================\r\n";
        
    }
    
    private function wasteTime() { 
        for ($i = 0; $i <= 100000; $i++) {
            md5('Proin volutpat rutrum nibh id mattis. Etiam nec convallis lectus. Curabitur ac posuere libero. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Integer sagittis ornare risus, vel mattis lectus tempus non. Donec vel luctus dolor, vulputate vehicula diam. In vehicula finibus molestie. In non sem facilisis orci sodales mollis eu sed tortor. Lorem ipsum dolor sit amet, consectetur adipiscing elit. In tincidunt vel risus in sollicitudin. Etiam tincidunt metus egestas, congue augue id, vulputate dui. Aliquam dictum consectetur sodales. Mauris dictum ligula ut ultricies convallis. Quisque eu semper massa, ac imperdiet arcu. Aenean eu tincidunt odio, nec eleifend neque. Ut lorem dui, suscipit ac turpis id, pulvinar ultrices quam.');
        }
    }
    
    

}

?>