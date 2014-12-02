<?php
//
// Change the following settings
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

$myname = $_GET['myname'];
$metas = [
    'myname' => $myname,
    ];
$acl = $_GET['acl'];

$url = Signer::getSignedURL('PUT', $cred['key'], $cred['secret'], Signer::ENDPOINT_TOKYO, $bucket, $objectKey, $expires, $mimeType, $acl, $metas);
header("Content-typte: application/json");
echo json_encode(['url' =>$url]);
