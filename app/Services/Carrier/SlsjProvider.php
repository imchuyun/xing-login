<?php
namespace App\Services\Carrier;

/**
 * 随联数聚运营商三要素认证供应商实现
 */
class SlsjProvider implements CarrierProviderInterface
{
    private string $memberId;
    private string $appKey;
    private string $apiUrl;
    
    /**
     * 运营商类型映射
     */
    private const CARRIER_TYPES = [
        '1' => '移动',
        '2' => '联通',
        '3' => '电信',
        '4' => '广电'
    ];
    
    public function __construct(string $memberId, string $appKey, string $apiUrl = 'https://api.slsj.com')
    {
        $this->memberId = $memberId;
        $this->appKey = $appKey;
        $this->apiUrl = rtrim($apiUrl, '/');
    }

    /**
     * 执行三要素认证
     */
    public function verify(string $name, string $idCard, string $mobile, string $orderId): array
    {
        $dateTime = (string)(time() * 1000);
        $version = 'v1';
        $signMd5 = $this->generateSign($dateTime, $version);
        $authToken = $this->buildAuthToken($signMd5, $version, $dateTime);
        $response = $this->httpPost(
            $this->apiUrl . '/api/carriers/carriers-auth/query',
            [
                'orderId' => $orderId,
                'name' => $name,
                'idCard' => $idCard,
                'mobile' => $mobile,
            ],
            [
                'Content-Type: application/x-www-form-urlencoded',
                'Auth_Token: ' . $authToken,
                'Sign_Type: md5',
            ]
        );
        return $this->parseResponse($response);
    }
    
    /**
     * 获取供应商标识
     */
    public function getProviderName(): string
    {
        return 'slsj';
    }
    
    /**
     * 生成MD5签名
     */
    public function generateSign(string $dateTime, string $version): string
    {
        $signStr = "memberId={$this->memberId}dateTime={$dateTime}version={$version}key={$this->appKey}";
        return strtolower(md5($signStr));
    }
    
    /**
     * 构建Auth_Token请求头
     */
    public function buildAuthToken(string $signMd5, string $version, string $dateTime): string
    {
        return json_encode([
            'signMd5' => $signMd5,
            'version' => $version,
            'dateTime' => $dateTime,
            'memberId' => $this->memberId,
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * 解析API响应
     */
    public function parseResponse($response): array
    {
        if ($response === false) {
            return [
                'success' => false,
                'matched' => false,
                'carrier_type' => '',
                'carrier_name' => '',
                'message' => '网络请求失败',
                'raw_data' => []
            ];
        }
        
        $data = json_decode($response, true);
        if (!$data) {
            return [
                'success' => false,
                'matched' => false,
                'carrier_type' => '',
                'carrier_name' => '',
                'message' => '响应解析失败',
                'raw_data' => []
            ];
        }
        
        if ($data['code'] !== 0) {
            return [
                'success' => false,
                'matched' => false,
                'carrier_type' => '',
                'carrier_name' => '',
                'message' => $data['msg'] ?? '认证失败',
                'raw_data' => $data,
            ];
        }
        
        $result = $data['result'] ?? [];
        $matched = ($result['result'] ?? '') === '01';
        $carrierType = $result['type'] ?? '';
        
        return [
            'success' => true,
            'matched' => $matched,
            'carrier_type' => $carrierType,
            'carrier_name' => self::CARRIER_TYPES[$carrierType] ?? '未知',
            'message' => $result['remark'] ?? ($matched ? '认证一致' : '认证不一致'),
            'raw_data' => $data,
        ];
    }
    
    /**
     * 发送HTTP POST请求
     */
    private function httpPost(string $url, array $data, array $headers)
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER => $headers,
        ]);
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            error_log("SlsjProvider HTTP Error: " . $error);
            return false;
        }
        
        return $response;
    }
}
