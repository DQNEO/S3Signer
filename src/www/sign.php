<?php
//
// customize this file as you need
//
use \DQNEO\S3Signer\Signer;

require_once __DIR__ . '/../../vendor/autoload.php';
$cred = require_once __DIR__ . '/credentials.php';

$now = time();
//$now = 1234567890;

$expires= $now + (60 * 5); // 5 minutes later
$bucket = $_GET['bucket'];
$objectKey=$_GET['key'];
$mimeType=$_GET['type'];
$acl = $_GET['acl'];

// meta data. you should remove or customize this.
$meta1 = $_GET['foo'];
$metas = [
    'foo' => $meta1,
    ];


$json =  Signer::getSignedURL('PUT', $cred['key'], $cred['secret'], Signer::ENDPOINT_TOKYO, $bucket, $objectKey, $expires, $mimeType, $acl, $metas);
header("Content-typte: application/json");
echo $json;
