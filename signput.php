<?php
//
// Change the following settings
//

$cred = require_once __DIR__ . '/credentials.php';

$expire_seconds=(60 * 5); // 5 minutes
$endpoint='http://s3-ap-northeast-1.amazonaws.com';
$bucket = $_GET['bucket'];

$objectName=$_GET['name'];

$mimeType=$_GET['type'];
$acl = "public-read";

echo getURL($cred['key'], $cred['secret'], $endpoint, $bucket, $objectName, $expire_seconds, $acl, $mimeType);

function getURL($key, $secret, $endpoint, $bucket, $objectKey, $expires, $acl, $mimeType)
{
    $expires = time() + $expires;
    $amzHeaders= "x-amz-acl:" . $acl;
    $stringToSign = sprintf("PUT\n\n%s\n%s\n%s\n/%s/%s", $mimeType, $expires, $amzHeaders, $bucket, $objectKey);

    $sig = urlencode(base64_encode(hash_hmac('sha1', $stringToSign, $secret, true)));
    $url = urlencode(sprintf("%s/%s/%s?AWSAccessKeyId=%s&Expires=%s&Signature=%s", $endpoint, $bucket, $objectKey   , $key, $expires, $sig));
    return $url;
}
