<?php

$publicKey = '1234567890987654321';
$privateKey = '0987654321234567890';

require_once './client.php';

$client = new Client($publicKey, $privateKey);
