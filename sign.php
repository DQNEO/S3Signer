<?php
//
// Change the following settings
//
require_once __DIR__ . '/S3Signature.php';
$cred = require_once __DIR__ . '/credentials.php';

$now = time();
//$now = 1234567890;

$expires= $now + (60 * 5); // 5 minutes later
$endpoint='http://s3-ap-northeast-1.amazonaws.com';
$bucket = $_GET['bucket'];
$objectKey=$_GET['key'];
$mimeType=$_GET['type'];

$myname = $_GET['myname'];
$metas = [
    'myname' => $myname,
    ];
$acl = $_GET['acl'];

$url = S3Signature::getSignedURL('PUT', $cred['key'], $cred['secret'], $endpoint, $bucket, $objectKey, $expires, $mimeType, $acl, $metas);
header("Content-typte: application/json");
echo json_encode(['url' =>$url]);
