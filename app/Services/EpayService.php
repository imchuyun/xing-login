<?php

namespace App\Services;

/**
 * 易支付服务类
 * 处理易支付接口的签名验证和支付请求
 * 兼容彩虹易支付及其他易支付平台
 */
class EpayService
{
    private string $apiUrl;
    private string $pid;
    private string $key;

    /**
     * 构造函数
     * 
     * @param array $config 配置数组，包含 api_url, pid, key
     */
    public function __construct(array $config)
    {
        $this->apiUrl = rtrim($config['api_url'] ?? '', '/');
        $this->pid = (string)($config['pid'] ?? '');
        $this->key = $config['key'] ?? '';
    }

    /**
     * 验证签名 - 支持多种签名方式
     * 
     * @param array $params 回调参数
     * @return bool
     */
    public function verifySign(array $params): bool
    {
        if (empty($params['sign'])) {
            error_log("[EpayService] verifySign failed: sign is empty");
            return false;
        }

        $receivedSign = $params['sign'];
        $signType = $params['sign_type'] ?? 'MD5';
        unset($params['sign'], $params['sign_type']);
        ksort($params);
        $queryParts = [];
        $queryPartsDecoded = [];
        
        foreach ($params as $key => $value) {
            $strValue = (string)$value;
            if ($strValue !== '') {
                $queryParts[] = $key . '=' . $strValue;
                $queryPartsDecoded[] = $key . '=' . urldecode($strValue);
            }
        }
        
        $queryString = implode('&', $queryParts);
        $queryStringDecoded = implode('&', $queryPartsDecoded);
        $signs = [
            'standard' => md5($queryString . $this->key),           // 标准方式: str + key
            'decoded' => md5($queryStringDecoded . $this->key),     // urldecode方式
            'with_key' => md5($queryString . '&key=' . $this->key), // 带&key=方式
            'decoded_with_key' => md5($queryStringDecoded . '&key=' . $this->key),
        ];
        $matched = false;
        $matchedMethod = '';
        foreach ($signs as $method => $sign) {
            if ($sign === $receivedSign) {
                $matched = true;
                $matchedMethod = $method;
                break;
            }
        }
        error_log("[EpayService] Received sign: " . $receivedSign);
        error_log("[EpayService] Query string: " . $queryString);
        
        if ($matched) {
            error_log("[EpayService] Sign matched using method: " . $matchedMethod);
        } else {
            error_log("[EpayService] Sign mismatch! Tried methods:");
            foreach ($signs as $method => $sign) {
                error_log("[EpayService]   - {$method}: {$sign}");
            }
            error_log("[EpayService] Raw params: " . json_encode($params, JSON_UNESCAPED_UNICODE));
        }
        
        return $matched;
    }

    /**
     * 生成支付请求签名
     * 
     * @param array $params 请求参数
     * @param string $signType 签名类型 MD5
     * @return string
     */
    public function generateSign(array $params, string $signType = 'MD5'): string
    {
        ksort($params);
        $queryParts = [];
        foreach ($params as $key => $value) {
            $strValue = (string)$value;
            if ($strValue !== '') {
                $queryParts[] = $key . '=' . $strValue;
            }
        }
        $queryString = implode('&', $queryParts);
        
        return $this->calculateSign($queryString, $signType);
    }

    /**
     * 计算签名
     * 彩虹易支付签名算法：MD5(参数字符串 + 密钥)
     * 
     * @param string $queryString 查询字符串
     * @param string $signType 签名类型
     * @return string
     */
    private function calculateSign(string $queryString, string $signType = 'MD5'): string
    {
        $signString = $queryString . $this->key;
        
        return md5($signString);
    }

    /**
     * 构建支付URL
     * 
     * @param array $params 支付参数
     * @return string
     */
    public function buildPayUrl(array $params): string
    {
        $params['pid'] = $this->pid;
        $params['sign'] = $this->generateSign($params);
        $params['sign_type'] = 'MD5';
        
        return $this->apiUrl . '/submit.php?' . http_build_query($params);
    }

    /**
     * 获取API地址
     * 
     * @return string
     */
    public function getApiUrl(): string
    {
        return $this->apiUrl;
    }

    /**
     * 获取商户ID
     * 
     * @return string
     */
    public function getPid(): string
    {
        return $this->pid;
    }
    
    /**
     * 调试方法：获取签名详情
     * 
     * @param array $params 参数
     * @return array 签名详情
     */
    public function getSignDebugInfo(array $params): array
    {
        ksort($params);
        
        $queryParts = [];
        foreach ($params as $key => $value) {
            $strValue = (string)$value;
            if ($strValue !== '') {
                $queryParts[] = $key . '=' . $strValue;
            }
        }
        $queryString = implode('&', $queryParts);
        
        return [
            'sorted_params' => $params,
            'query_string' => $queryString,
            'sign_string' => $queryString . '[KEY_HIDDEN]',
            'sign' => md5($queryString . $this->key)
        ];
    }
}
