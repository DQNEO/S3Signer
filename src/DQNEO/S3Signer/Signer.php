<?php
namespace DQNEO\S3Signer;
/**
 * a URL generator for AmazonS3 direct uploading
 * 
 */
class Signer
{
    const ENDPOINT_TOKYO = 'https://s3-ap-northeast-1.amazonaws.com';

    /**
     * generate a URL
     * @return string $url
     */
    public static function getSignedURL($httpVerb, $key, $secret, $endpoint, $bucket, $objectKey, $expires, $contentType, $acl, array $metas)
    {
        $amzHeaders = [];
        $amzHeaders[] = "x-amz-acl:" . $_GET['acl'];

        foreach($metas as $k => $v) {
            $amzHeaders[] = sprintf("x-amz-meta-%s:%s", $k, $v);
        }

        ksort($amzHeaders);


        $sig = self::getSignature($httpVerb, $bucket, $objectKey, $amzHeaders, $contentType, $expires, $secret);
        $query = sprintf("AWSAccessKeyId=%s&Expires=%s&Signature=%s",
                         $key, $expires, urlencode($sig));
        return json_encode([
                               'query' => urlencode($query)
                               ]);
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
