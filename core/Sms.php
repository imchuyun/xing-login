<?php
namespace Core;

/**
 * 短信发送类 (支持腾讯云、阿里云)
 */
class Sms
{
    private $provider;
    private $config;

    public function __construct($provider = '', $config = [])
    {
        $this->provider = $provider;
        $this->config = $config;
    }

    /**
     * 发送短信验证码
     */
    public function sendCode($phone, $code, $type = 'register')
    {
        if (empty($this->provider)) {
            return ['success' => false, 'message' => '短信服务未配置'];
        }

        switch ($this->provider) {
            case 'tencent':
                return $this->sendTencent($phone, $code, $type);
            case 'aliyun':
                return $this->sendAliyun($phone, $code, $type);
            default:
                return ['success' => false, 'message' => '不支持的短信服务商'];
        }
    }

    /**
     * 腾讯云短信
     */
    private function sendTencent($phone, $code, $type)
    {
        $secretId = $this->config['secret_id'] ?? '';
        $secretKey = $this->config['secret_key'] ?? '';
        $sdkAppId = $this->config['sdk_app_id'] ?? '';
        $signName = $this->config['sign_name'] ?? '';
        $templateId = $this->config['template_id'] ?? '';

        if (empty($secretId) || empty($secretKey) || empty($sdkAppId)) {
            return ['success' => false, 'message' => '腾讯云短信配置不完整'];
        }

        $host = 'sms.tencentcloudapi.com';
        $service = 'sms';
        $action = 'SendSms';
        $version = '2021-01-11';
        $region = 'ap-guangzhou';
        $timestamp = time();

        $payload = json_encode([
            'PhoneNumberSet' => ['+86' . $phone],
            'SmsSdkAppId' => $sdkAppId,
            'SignName' => $signName,
            'TemplateId' => $templateId,
            'TemplateParamSet' => [$code],
        ]);
        $date = gmdate('Y-m-d', $timestamp);
        $hashedPayload = hash('sha256', $payload);
        $httpRequestMethod = 'POST';
        $canonicalUri = '/';
        $canonicalQueryString = '';
        $canonicalHeaders = "content-type:application/json; charset=utf-8\nhost:{$host}\nx-tc-action:" . strtolower($action) . "\n";
        $signedHeaders = 'content-type;host;x-tc-action';
        $canonicalRequest = "{$httpRequestMethod}\n{$canonicalUri}\n{$canonicalQueryString}\n{$canonicalHeaders}\n{$signedHeaders}\n{$hashedPayload}";
        
        $credentialScope = "{$date}/{$service}/tc3_request";
        $hashedCanonicalRequest = hash('sha256', $canonicalRequest);
        $stringToSign = "TC3-HMAC-SHA256\n{$timestamp}\n{$credentialScope}\n{$hashedCanonicalRequest}";

        $secretDate = hash_hmac('sha256', $date, "TC3{$secretKey}", true);
        $secretService = hash_hmac('sha256', $service, $secretDate, true);
        $secretSigning = hash_hmac('sha256', 'tc3_request', $secretService, true);
        $signature = hash_hmac('sha256', $stringToSign, $secretSigning);

        $authorization = "TC3-HMAC-SHA256 Credential={$secretId}/{$credentialScope}, SignedHeaders={$signedHeaders}, Signature={$signature}";

        $headers = [
            'Authorization: ' . $authorization,
            'Content-Type: application/json; charset=utf-8',
            'Host: ' . $host,
            'X-TC-Action: ' . $action,
            'X-TC-Version: ' . $version,
            'X-TC-Timestamp: ' . $timestamp,
            'X-TC-Region: ' . $region,
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => "https://{$host}",
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);
        
        // 检查是否有API级别的错误
        if (isset($result['Response']['Error'])) {
            return [
                'success' => false, 
                'message' => ($result['Response']['Error']['Code'] ?? '') . ': ' . ($result['Response']['Error']['Message'] ?? '发送失败')
            ];
        }
        
        if (isset($result['Response']['SendStatusSet'][0]['Code']) && $result['Response']['SendStatusSet'][0]['Code'] === 'Ok') {
            return ['success' => true, 'message' => '发送成功'];
        }
        
        // 返回具体的发送状态错误
        $sendStatus = $result['Response']['SendStatusSet'][0] ?? [];
        $errorMsg = $sendStatus['Message'] ?? ($sendStatus['Code'] ?? '发送失败');

        return ['success' => false, 'message' => $errorMsg];
    }

    /**
     * 阿里云短信
     */
    private function sendAliyun($phone, $code, $type)
    {
        $accessKeyId = $this->config['access_key_id'] ?? '';
        $accessKeySecret = $this->config['access_key_secret'] ?? '';
        $signName = $this->config['sign_name'] ?? '';
        $templateCode = $this->config['template_code'] ?? '';

        if (empty($accessKeyId) || empty($accessKeySecret)) {
            return ['success' => false, 'message' => '阿里云短信配置不完整'];
        }

        $params = [
            'AccessKeyId' => $accessKeyId,
            'Action' => 'SendSms',
            'Format' => 'JSON',
            'PhoneNumbers' => $phone,
            'RegionId' => 'cn-hangzhou',
            'SignName' => $signName,
            'SignatureMethod' => 'HMAC-SHA1',
            'SignatureNonce' => uniqid(),
            'SignatureVersion' => '1.0',
            'TemplateCode' => $templateCode,
            'TemplateParam' => json_encode(['code' => $code]),
            'Timestamp' => gmdate('Y-m-d\TH:i:s\Z'),
            'Version' => '2017-05-25',
        ];

        ksort($params);
        $queryString = '';
        foreach ($params as $key => $value) {
            $queryString .= '&' . $this->percentEncode($key) . '=' . $this->percentEncode($value);
        }
        $queryString = substr($queryString, 1);

        $stringToSign = 'GET&' . $this->percentEncode('/') . '&' . $this->percentEncode($queryString);
        $signature = base64_encode(hash_hmac('sha1', $stringToSign, $accessKeySecret . '&', true));
        
        $url = 'https://dysmsapi.aliyuncs.com/?' . $queryString . '&Signature=' . urlencode($signature);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);
        if (isset($result['Code']) && $result['Code'] === 'OK') {
            return ['success' => true, 'message' => '发送成功'];
        }

        return ['success' => false, 'message' => $result['Message'] ?? '发送失败'];
    }

    private function percentEncode($str)
    {
        $str = urlencode($str);
        $str = str_replace(['+', '*'], ['%20', '%2A'], $str);
        $str = preg_replace('/%7E/', '~', $str);
        return $str;
    }
}
