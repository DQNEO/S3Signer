<?php
//
// Change the following settings
//

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

class S3Signature
{
public static function getSignedURL($httpVerb, $key, $secret, $endpoint, $bucket, $objectKey, $expires, $contentType, $acl, array $metas)
{
    $amzHeaders = [];
    $amzHeaders[] = "x-amz-acl:" . $_GET['acl'];

    foreach($metas as $k => $v) {
        $amzHeaders[] = sprintf("x-amz-meta-%s:%s", $k, $v);
    }

    $sig = self::getSignature($httpVerb, $bucket, $objectKey, $amzHeaders, $contentType, $expires, $secret);
    $url = sprintf("%s/%s/%s?AWSAccessKeyId=%s&Expires=%s&Signature=%s", $endpoint, $bucket, $objectKey   , $key, $expires, urlencode($sig));
    return urlencode($url);
}

public static function getSignature($httpVerb, $bucket, $objectKey, array $amzHeaders, $contentType, $expires, $secret)
{
    // for calculation of Signature, see
    // http://docs.aws.amazon.com/AmazonS3/latest/dev/RESTAuthentication.html#ConstructingTheAuthenticationHeader
    $httpVerb = "PUT";
    $contentMD5 = "";
    $canonicalizedResource = sprintf("/%s/%s", $bucket, $objectKey);
    $canonicalizedAmzHeaders =  join("\n", $amzHeaders) . "\n";

    $stringToSign = $httpVerb . "\n"
        . $contentMD5 . "\n"
        . $contentType . "\n"
        . $expires . "\n"
        . $canonicalizedAmzHeaders
        . $canonicalizedResource;

    return base64_encode(hash_hmac('sha1', $stringToSign, $secret, true));
}
}
