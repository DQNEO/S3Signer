<?php
//
// Change the following settings
//

$cred = require_once __DIR__ . '/credentials.php';
$S3_KEY=$cred['key'];
$S3_SECRET=$cred['secret'];

$S3_BUCKET='/tmpdqneo';

$EXPIRE_TIME=(60 * 5); // 5 minutes
$S3_URL='http://s3-ap-northeast-1.amazonaws.com';

$objectName='/' . $_GET['name'];

$mimeType=$_GET['type'];
$expires = time() + $EXPIRE_TIME;
$amzHeaders= "x-amz-acl:public-read";
$stringToSign = "PUT\n\n$mimeType\n$expires\n$amzHeaders\n$S3_BUCKET$objectName";
$sig = urlencode(base64_encode(hash_hmac('sha1', $stringToSign, $S3_SECRET, true)));
$url = urlencode("$S3_URL$S3_BUCKET$objectName?AWSAccessKeyId=$S3_KEY&Expires=$expires&Signature=$sig");

echo $url;
