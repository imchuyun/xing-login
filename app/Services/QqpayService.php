<?php
namespace App\Services;

/**
 * QQ钱包支付服务 - Native支付（扫码支付）& H5支付
 */
class QqpayService
{
    private string $mchId;
    private string $apiKey;
    private string $notifyUrl;
    private string $gateway = 'https://qpay.qq.com/cgi-bin/pay/qpay_unified_order.cgi';
    
    public function __construct(array $config)
    {
        $this->mchId = $config['mch_id'] ?? '';
        $this->apiKey = $config['api_key'] ?? '';
        $this->notifyUrl = $config['notify_url'] ?? '';
    }
    
    /**
     * 创建Native支付订单（扫码支付）
     */
    public function createNativeOrder(string $orderNo, string $body, float $amount, string $clientIp = ''): array
    {
        $params = [
            'mch_id' => $this->mchId,
            'nonce_str' => $this->generateNonceStr(),
            'body' => $body,
            'out_trade_no' => $orderNo,
            'fee_type' => 'CNY',
            'total_fee' => (int)($amount * 100),
            'spbill_create_ip' => $clientIp ?: $this->getClientIp(),
            'notify_url' => $this->notifyUrl,
            'trade_type' => 'NATIVE',
        ];
        
        $params['sign'] = $this->generateSign($params);
        
        $xml = $this->arrayToXml($params);
        $response = $this->postXml($this->gateway, $xml);
        $result = $this->xmlToArray($response);
        
        if (!$result) {
            return ['success' => false, 'message' => 'QQ钱包接口返回数据解析失败'];
        }
        
        if ($result['return_code'] !== 'SUCCESS') {
            return ['success' => false, 'message' => $result['return_msg'] ?? 'QQ钱包接口调用失败'];
        }
        
        if ($result['result_code'] !== 'SUCCESS') {
            return ['success' => false, 'message' => $result['err_code_des'] ?? $result['err_code'] ?? '创建订单失败'];
        }
        
        return [
            'success' => true,
            'code_url' => $result['code_url'],
            'prepay_id' => $result['prepay_id'],
        ];
    }
    
    /**
     * 验证回调通知签名
     */
    public function verifyNotify(array $data): bool
    {
        return $this->verifySign($data);
    }
    
    /**
     * 生成回调响应
     */
    public function notifyResponse(bool $success, string $message = 'OK'): string
    {
        if ($success) {
            return '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
        }
        return '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[' . $message . ']]></return_msg></xml>';
    }
    
    /**
     * 生成签名
     */
    private function generateSign(array $params): string
    {
        ksort($params);
        $signStr = '';
        foreach ($params as $k => $v) {
            if ($k !== 'sign' && $v !== '' && $v !== null) {
                $signStr .= $k . '=' . $v . '&';
            }
        }
        $signStr .= 'key=' . $this->apiKey;
        return strtoupper(md5($signStr));
    }
    
    /**
     * 验证签名
     */
    private function verifySign(array $data): bool
    {
        if (empty($data['sign'])) {
            return false;
        }
        $sign = $data['sign'];
        return $sign === $this->generateSign($data);
    }
    
    /**
     * 生成随机字符串
     */
    private function generateNonceStr(int $length = 32): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $str;
    }
    
    /**
     * 数组转XML
     */
    private function arrayToXml(array $data): string
    {
        $xml = '<xml>';
        foreach ($data as $key => $value) {
            if (is_numeric($value)) {
                $xml .= '<' . $key . '>' . $value . '</' . $key . '>';
            } else {
                $xml .= '<' . $key . '><![CDATA[' . $value . ']]></' . $key . '>';
            }
        }
        $xml .= '</xml>';
        return $xml;
    }
    
    /**
     * XML转数组
     */
    private function xmlToArray(string $xml): ?array
    {
        if (empty($xml)) {
            return null;
        }
        libxml_disable_entity_loader(true);
        $result = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($result === false) {
            return null;
        }
        return json_decode(json_encode($result), true);
    }
    
    /**
     * 发送XML请求
     */
    private function postXml(string $url, string $xml): string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: text/xml']);
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new \Exception('QQ钱包请求失败: ' . $error);
        }
        
        return $response;
    }
    
    /**
     * 获取客户端IP
     */
    private function getClientIp(): string
    {
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        }
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
}
