<?php
namespace App\Services\Carrier;

/**
 * 数勋科技运营商三要素认证供应商实现
 */
class ShuxunProvider implements CarrierProviderInterface
{
    private string $appKey;
    private string $appSecret;
    private string $apiUrl;
    
    /**
     * 运营商类型映射
     */
    private const CARRIER_TYPES = [
        'cmcc' => '移动',
        'cucc' => '联通',
        'ctcc' => '电信',
        'gdcc' => '广电'
    ];
    
    /**
     * 错误码映射
     */
    private const ERROR_CODES = [
        '0' => '成功',
        '1' => '参数错误',
        '3' => '第三方服务异常',
        '4' => '签名错误，请联系管理员',
        '5' => '余额不足，请联系管理员',
        '6' => '调用频率超限，请稍后重试',
        '7' => '账号停用，请联系管理员',
        '8' => '接口已停用，请联系管理员',
        '9' => '接口权限未开通，请联系管理员',
        '10' => 'IP不在白名单，请联系管理员',
        '11' => '系统异常，请稍后重试',
        '12' => '实名状态错误',
        '99' => '其他异常'
    ];
    
    public function __construct(string $appKey, string $appSecret, string $apiUrl = 'https://api.shuxuntech.com')
    {
        $this->appKey = $appKey;
        $this->appSecret = $appSecret;
        $this->apiUrl = rtrim($apiUrl, '/');
    }

    /**
     * 执行三要素认证
     */
    public function verify(string $name, string $idCard, string $mobile, string $orderId): array
    {
        $timestamp = (string)(time() * 1000);
        $sign = $this->generateSign($timestamp);
        $headers = $this->buildHeaders($timestamp, $sign);
        $response = $this->httpPost(
            $this->apiUrl . '/v1/mobile3/check',
            [
                'name' => $name,
                'mobile' => $mobile,
                'idCard' => $idCard,
            ],
            $headers
        );
        return $this->parseResponse($response);
    }
    
    /**
     * 获取供应商标识
     */
    public function getProviderName(): string
    {
        return 'shuxun';
    }
    
    /**
     * 生成SHA256签名
     * 签名规则：sha256(appKey + timestamp + appSecret)
     */
    public function generateSign(string $timestamp): string
    {
        $signStr = $this->appKey . $timestamp . $this->appSecret;
        return hash('sha256', $signStr);
    }
    
    /**
     * 构建请求头
     */
    public function buildHeaders(string $timestamp, string $sign): array
    {
        return [
            'appKey: ' . $this->appKey,
            'timestamp: ' . $timestamp,
            'sign: ' . $sign,
            'Content-Type: application/x-www-form-urlencoded',
        ];
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
        
        if ($code !== '0') {
            $errorMsg = self::ERROR_CODES[$code] ?? ($data['msg'] ?? '认证失败');
            if ($code === '99' && !empty($data['msg'])) {
                $errorMsg = $data['msg'];
            }
            
            return [
                'success' => false,
                'matched' => false,
                'carrier_type' => '',
                'carrier_name' => '',
                'message' => $errorMsg,
                'raw_data' => $data,
            ];
        }
        
        $resultData = $data['data'] ?? [];
        $result = $resultData['result'] ?? 0;
        $matched = ($result === 1);
        $carrierType = $resultData['mobileChannel'] ?? '';
        $resultDesc = $resultData['resultDesc'] ?? '';
        if (empty($resultDesc)) {
            if ($result === 1) {
                $resultDesc = '核验一致';
            } elseif ($result === 2) {
                $resultDesc = '核验不一致';
            } elseif ($result === 3) {
                $resultDesc = '无记录';
            }
        }
        
        return [
            'success' => true,
            'matched' => $matched,
            'carrier_type' => $carrierType,
            'carrier_name' => self::CARRIER_TYPES[$carrierType] ?? '未知',
            'message' => $resultDesc,
            'raw_data' => $data,
        ];
    }

    
    /**
     * 获取错误码对应的错误信息
     */
    public function getErrorMessage(string $code): string
    {
        return self::ERROR_CODES[$code] ?? '未知错误';
    }
    
    /**
     * 映射运营商类型
     */
    public function mapCarrierType(string $carrierType): string
    {
        return self::CARRIER_TYPES[$carrierType] ?? '未知';
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
            error_log("ShuxunProvider HTTP Error: " . $error);
            return false;
        }
        
        return $response;
    }
}
