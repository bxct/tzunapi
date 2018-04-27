<?php

require_once('../../init/common.php');

class SignetTest extends \PHPUnit_Framework_TestCase {

    private static $d = array(
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
        'files' =>
        array(
            'file' =>
            array(
                'name' => '',
                'type' => '',
                'tmp_name' => '',
                'error' => 4,
                'size' => 0,
            ),
        ),
    );

    public function test_exception_1() {
        $privateKey = uniqid();
        $this->setExpectedException('InvalidArgumentException');
        \Signet::spSignature(self::$d, $privateKey);
    }

    public function test_exception_2() {
        $this->setExpectedException('InvalidArgumentException');
        \Signet::prepareSignatureOrigin(self::$d);
    }

    public function test_prepareSignatureOrigin() {

        self::$d['timestamp'] = time();

        $tStart = microtime(true);
        for ($i = 0; $i <= 10000; $i++) {
            \Signet::prepareSignatureOrigin(self::$d);
        }
        $tDiff = microtime(true) - $tStart;

        $tStart2 = microtime(true);
        $this->wasteTime();
        $tDiff2 = microtime(true) - $tStart2;

        $this->assertLessThan($tDiff2, $tDiff, 'Too long for origin generation');
    }

    public function test_spSignature() {

        $privateKey = uniqid();

        self::$d['timestamp'] = time();

        $tStart = microtime(true);
        for ($i = 0; $i <= 10000; $i++) {
            \Signet::spSignature(self::$d, $privateKey);
        }
        $tDiff = microtime(true) - $tStart;

        $tStart2 = microtime(true);
        $this->wasteTime();
        $tDiff2 = microtime(true) - $tStart2;

        $this->assertLessThan($tDiff2, $tDiff, 'Too long for sealing via custom signature');
    }

    public function test_hmac() {

        $privateKey = uniqid();

        self::$d['timestamp'] = time();

        $tStart = microtime(true);
        for ($i = 0; $i <= 10000; $i++) {
            \Signet::hmac(self::$d, $privateKey);
        }
        $tDiff = microtime(true) - $tStart;

        $tStart2 = microtime(true);
        $this->wasteTime();
        $tDiff2 = microtime(true) - $tStart2;

        $this->assertLessThan($tDiff2, $tDiff, 'Too long for sealing via hmac');
    }

    public function test_plainMd5() {

        $privateKey = uniqid();

        self::$d['timestamp'] = time();

        $tStart = microtime(true);
        for ($i = 0; $i <= 10000; $i++) {
            \Signet::plainMd5(self::$d, $privateKey);
        }
        $tDiff = microtime(true) - $tStart;

        $tStart2 = microtime(true);
        $this->wasteTime();
        $tDiff2 = microtime(true) - $tStart2;

        $this->assertLessThan($tDiff2, $tDiff, 'Too long for sealing via md5');
    }

    public function test_plainSha1() {

        $privateKey = uniqid();

        self::$d['timestamp'] = time();

        $tStart = microtime(true);
        for ($i = 0; $i <= 10000; $i++) {
            \Signet::plainSha1(self::$d, $privateKey);
        }
        $tDiff = microtime(true) - $tStart;

        $tStart2 = microtime(true);
        $this->wasteTime();
        $tDiff2 = microtime(true) - $tStart2;

        $this->assertLessThan($tDiff2, $tDiff, 'Too long for sealing via sha1');
    }

    private function wasteTime() {
        for ($i = 0; $i <= 100000; $i++) {
            md5('Proin volutpat rutrum nibh id mattis. Etiam nec convallis lectus. Curabitur ac posuere libero. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Integer sagittis ornare risus, vel mattis lectus tempus non. Donec vel luctus dolor, vulputate vehicula diam. In vehicula finibus molestie. In non sem facilisis orci sodales mollis eu sed tortor. Lorem ipsum dolor sit amet, consectetur adipiscing elit. In tincidunt vel risus in sollicitudin. Etiam tincidunt metus egestas, congue augue id, vulputate dui. Aliquam dictum consectetur sodales. Mauris dictum ligula ut ultricies convallis. Quisque eu semper massa, ac imperdiet arcu. Aenean eu tincidunt odio, nec eleifend neque. Ut lorem dui, suscipit ac turpis id, pulvinar ultrices quam.');
        }
    }

}

?>