<?php

if(!class_exists('Signet')) {

    /**
     * Class which performs sealing and hashing
     */
    class Signet {

        /**
         * Fields values of which are taken from $data array if present to be included in hash generation
         * 
         * @var array
         */
        private static $considerableFields = ['public_key', 'service', 'operation', 'version', 'action', 'id', 'timestamp'];

        /**
         * Standard constructor. Allows specification of additional  data fields to be considered in resultin hash
         * 
         * @param array $considerableFields additional fields to be considered in hashed string
         */
        public function __construct($considerableFields = array()) {
            if(!empty($considerableFields)) {
                foreach($considerableFields as $f) {
                    if(preg_match('/[a-z]+/', $f)) {
                        self::$considerableFields[] = $f;
                    }
                }
            }
        }

        /**
         * Generates string to be hashed using values of incoming data array
         * 
         * @param array $data
         * @param boolean $encode
         * 
         * @return string
         * 
         * @throws InvalidArgumentException
         */
        public static function prepareSignatureOrigin($data, $encode = true) {

            unset($data['files']);

            if(!array_key_exists('timestamp', $data) || !preg_match('/[0-9]{10,11}/', $data['timestamp'])) {
                throw new InvalidArgumentException('Can not generate signature. Data is missing a correct `timestamp` field.');
            }

            $signatureOrigin = array_intersect_key($data, array_flip(self::$considerableFields));

            ksort($signatureOrigin);

            if($encode) {
                $signatureOrigin = base64_encode(serialize($signatureOrigin));
            } else {
                $signatureOrigin = serialize($signatureOrigin);
            }

            return $signatureOrigin;
        }

        /**
         * Generates string to be hashed using values of incoming data array.
         * Logic is more suitable for platforms different than PHP
         * 
         * @param array $data
         * @param boolean $encode
         * @param boolean $firstPass notifies of initial pass while recursive action
         * 
         * @return string
         * 
         * @throws InvalidArgumentException
         */
        public static function prepareSignatureOriginCrossplatform($data, $encode = true, $firstPass = true) {

            //Only for initial top-level pass
            if($firstPass) {
                unset($data['files']);
                if(!array_key_exists('timestamp', $data)) {
                    throw new InvalidArgumentException('Can not generate signature. Data array is missing `timestamp` field.');
                }
                $data = array_intersect_key($data, array_flip(self::$considerableFields));
                ksort($data);
            }

            $result = '';

            foreach($data as $key => $value) {
                if(is_array($value)) {
                    $result .= self::prepareSignatureOriginCrossplatform($value, $encode, false);
                } else {
                    $result .= ($encode?base64_encode($value):$value);
                }
            }

            return $result;
        }

        /**
         * Generates hash using customized algorithm
         * 
         * @param array $data
         * @param string $privateKey
         * 
         * @return string base64-encoded hash generated from input data
         */
        public static function spSignature($data, $privateKey) {
            return base64_encode(self::hmac_sha1($privateKey, self::prepareSignatureOriginCrossplatform($data)));
        }

        /**
         * Generates hash using simple sha1() algorithm
         * 
         * @param array $data
         * @param string $privateKey
         * 
         * @return string base64-encoded hash generated from input data
         */
        public static function plainSha1($data, $privateKey) {
            return base64_encode(sha1(self::prepareSignatureOriginCrossplatform($data) . $privateKey));
        }

        /**
         * Generates hash using simple md5() algorithm
         * 
         * @param array $data
         * @param string $privateKey
         * 
         * @return string base64-encoded hash generated from input data
         */
        public static function plainMd5($data, $privateKey) {
            return base64_encode(md5(self::prepareSignatureOriginCrossplatform($data) . $privateKey));
        }

        /**
         * Generates hash using specified algorithm (sha256 by default)
         * 
         * @param array $data
         * @param string $privateKey
         * @param string $algo
         * 
         * @return string base64-encoded hash generated from input data
         */
        public static function hmac($data, $privateKey, $algo = 'sha256') {
            return base64_encode(hash_hmac($algo, self::prepareSignatureOriginCrossplatform($data), $privateKey));
        }

        /**
         * Generates hash using customized algorithm
         * 
         * @param string $privateKey
         * @param string $s string to be encoded
         * 
         * @return string base64-encoded hash generated from input data
         */
        public static function hmac_sha1($privateKey, $s) {
            return pack("H*", sha1((str_pad($privateKey, 64, chr(0x00)) ^ (str_repeat(chr(0x5c), 64))) . pack("H*", sha1((str_pad($privateKey, 64, chr(0x00)) ^ (str_repeat(chr(0x36), 64))) . $s))));
        }

    }
    
}