<?php
namespace App\Services\Carrier;

/**
 * 创蓝云智运营商三要素认证供应商实现
 * API文档: https://api.253.com/open/carriers/carriers-auth
 */
class ChuanglanProvider implements CarrierProviderInterface
{
    private string $appId;
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
    
    /**
     * 认证结果映射
     */
    private const RESULT_CODES = [
        '01' => '认证一致',
        '02' => '认证不一致',
        '03' => '不确定',
        '04' => '失败/虚拟号'
    ];
    
    public function __construct(string $appId, string $appKey, string $apiUrl = 'https://api.253.com')
    {
        $this->appId = $appId;
        $this->appKey = $appKey;
        $this->apiUrl = rtrim($apiUrl, '/');
    }

    /**
     * 执行三要素认证
     */
    public function verify(string $name, string $idCard, string $mobile, string $orderId): array
    {
        $requestData = [
            'appId' => $this->appId,
            'appKey' => $this->appKey,
            'name' => $name,
            'idNum' => $idCard,
            'mobile' => $mobile,
        ];
        
        $url = $this->apiUrl . '/open/carriers/carriers-auth';
        
        // 调试日志
        error_log("ChuanglanProvider Request URL: " . $url);
        error_log("ChuanglanProvider Request Data: " . json_encode([
            'appId' => $this->appId,
            'appKey' => substr($this->appKey, 0, 4) . '****', // 隐藏敏感信息
            'name' => $name,
            'idNum' => substr($idCard, 0, 4) . '****',
            'mobile' => substr($mobile, 0, 3) . '****',
        ], JSON_UNESCAPED_UNICODE));
        
        $response = $this->httpPost($url, $requestData);
        
        // 调试日志
        error_log("ChuanglanProvider Response: " . ($response ?: 'false'));
        
        return $this->parseResponse($response);
    }
    
    /**
     * 获取供应商标识
     */
    public function getProviderName(): string
    {
        return 'chuanglan';
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
        
        $code = $data['code'] ?? '';
        
        // 200000 表示成功
        if ($code !== '200000') {
            return [
                'success' => false,
                'matched' => false,
                'carrier_type' => '',
                'carrier_name' => '',
                'message' => $data['message'] ?? '认证失败',
                'raw_data' => $data,
            ];
        }
        
        $resultData = $data['data'] ?? [];
        $result = $resultData['result'] ?? '';
        $matched = ($result === '01'); // 01表示认证一致
        $carrierType = $resultData['type'] ?? '';
        $remark = $resultData['remark'] ?? (self::RESULT_CODES[$result] ?? '未知结果');
        
        return [
            'success' => true,
            'matched' => $matched,
            'carrier_type' => $carrierType,
            'carrier_name' => self::CARRIER_TYPES[$carrierType] ?? '未知',
            'message' => $remark,
            'raw_data' => $data,
        ];
    }
    
    /**
     * 发送HTTP POST请求
     * 创蓝云智API支持两种格式：JSON 和 表单
     * 这里使用表单格式以确保兼容性
     */
    private function httpPost(string $url, array $data)
    {
        $ch = curl_init();
        
        // 使用表单格式提交（更通用）
        $postData = http_build_query($data);
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
                'Accept: application/json',
            ],
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            error_log("ChuanglanProvider HTTP Error: " . $error);
            return false;
        }
        
        error_log("ChuanglanProvider HTTP Code: " . $httpCode);
        
        return $response;
    }
}
