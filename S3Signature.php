<?php
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
