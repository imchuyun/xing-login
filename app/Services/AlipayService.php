<?php
namespace App\Services;

/**
 * 支付宝支付服务 - 电脑网站支付 & 手机网站支付
 */
class AlipayService
{
    private string $appId;
    private string $privateKey;
    private string $alipayPublicKey;
    private string $notifyUrl;
    private string $returnUrl;
    private string $gateway = 'https://openapi.alipay.com/gateway.do';
    
    public function __construct(array $config)
    {
        $this->appId = $config['app_id'] ?? '';
        $this->privateKey = $config['private_key'] ?? '';
        $this->alipayPublicKey = $config['alipay_public_key'] ?? '';
        $this->notifyUrl = $config['notify_url'] ?? '';
        $this->returnUrl = $config['return_url'] ?? '';
    }
    
    /**
     * 创建电脑网站支付订单
     */
    public function createPagePayOrder(string $orderNo, string $subject, float $amount): string
    {
        $bizContent = [
            'out_trade_no' => $orderNo,
            'total_amount' => sprintf('%.2f', $amount),
            'subject' => $subject,
            'product_code' => 'FAST_INSTANT_TRADE_PAY',
        ];
        
        $params = $this->buildParams('alipay.trade.page.pay', $bizContent);
        
        return $this->buildFormHtml($params);
    }
    
    /**
     * 创建当面付预下单（扫码支付）
     * 返回二维码链接
     */
    public function createQrPayOrder(string $orderNo, string $subject, float $amount): array
    {
        $bizContent = [
            'out_trade_no' => $orderNo,
            'total_amount' => sprintf('%.2f', $amount),
            'subject' => $subject,
        ];
        
        $params = [
            'app_id' => $this->appId,
            'method' => 'alipay.trade.precreate',
            'format' => 'JSON',
            'charset' => 'utf-8',
            'sign_type' => 'RSA2',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0',
            'notify_url' => $this->notifyUrl,
            'biz_content' => json_encode($bizContent, JSON_UNESCAPED_UNICODE),
        ];
        
        $params['sign'] = $this->generateSign($params);
        
        // 发送请求
        $response = $this->httpPost($this->gateway, $params);
        
        if (!$response) {
            return ['success' => false, 'message' => '请求支付宝接口失败'];
        }
        
        $result = json_decode($response, true);
        $responseKey = 'alipay_trade_precreate_response';
        
        if (!isset($result[$responseKey])) {
            return ['success' => false, 'message' => '支付宝返回数据格式错误'];
        }
        
        $data = $result[$responseKey];
        
        if ($data['code'] !== '10000') {
            return [
                'success' => false,
                'message' => $data['sub_msg'] ?? $data['msg'] ?? '创建订单失败'
            ];
        }
        
        return [
            'success' => true,
            'qr_code' => $data['qr_code'],
            'out_trade_no' => $orderNo
        ];
    }
    
    /**
     * HTTP POST 请求
     */
    private function httpPost(string $url, array $params): ?string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            error_log("[Alipay] CURL Error: " . $error);
            return null;
        }
        
        return $response;
    }
    
    /**
     * 验证异步通知签名
     */
    public function verifyNotify(array $params): bool
    {
        if (empty($params['sign'])) {
            return false;
        }
        
        $sign = $params['sign'];
        $signType = $params['sign_type'] ?? 'RSA2';
        
        // 移除签名相关参数
        unset($params['sign'], $params['sign_type']);
        
        // 排序并拼接
        ksort($params);
        $signData = '';
        foreach ($params as $k => $v) {
            if ($v !== '' && $v !== null && !is_array($v)) {
                $signData .= $k . '=' . $v . '&';
            }
        }
        $signData = rtrim($signData, '&');
        
        // 验证签名
        $publicKey = $this->formatPublicKey($this->alipayPublicKey);
        $res = openssl_pkey_get_public($publicKey);
        if (!$res) {
            return false;
        }
        
        $result = openssl_verify(
            $signData,
            base64_decode($sign),
            $res,
            $signType === 'RSA2' ? OPENSSL_ALGO_SHA256 : OPENSSL_ALGO_SHA1
        );
        
        return $result === 1;
    }
    
    /**
     * 验证同步返回签名
     */
    public function verifyReturn(array $params): bool
    {
        return $this->verifyNotify($params);
    }
    
    /**
     * 构建请求参数
     */
    private function buildParams(string $method, array $bizContent): array
    {
        $params = [
            'app_id' => $this->appId,
            'method' => $method,
            'format' => 'JSON',
            'return_url' => $this->returnUrl,
            'charset' => 'utf-8',
            'sign_type' => 'RSA2',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0',
            'notify_url' => $this->notifyUrl,
            'biz_content' => json_encode($bizContent, JSON_UNESCAPED_UNICODE),
        ];
        
        $params['sign'] = $this->generateSign($params);
        
        return $params;
    }
    
    /**
     * 生成签名
     */
    private function generateSign(array $params): string
    {
        ksort($params);
        $signData = '';
        foreach ($params as $k => $v) {
            if ($k !== 'sign' && $v !== '' && $v !== null) {
                $signData .= $k . '=' . $v . '&';
            }
        }
        $signData = rtrim($signData, '&');
        
        $privateKey = $this->formatPrivateKey($this->privateKey);
        $res = openssl_pkey_get_private($privateKey);
        if (!$res) {
            throw new \Exception('支付宝私钥格式错误');
        }
        
        openssl_sign($signData, $sign, $res, OPENSSL_ALGO_SHA256);
        
        return base64_encode($sign);
    }
    
    /**
     * 构建自动提交表单HTML
     */
    private function buildFormHtml(array $params): string
    {
        $html = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>正在跳转支付宝...</title></head><body>';
        $html .= '<form id="alipayForm" action="' . $this->gateway . '" method="POST">';
        foreach ($params as $key => $value) {
            $html .= '<input type="hidden" name="' . $key . '" value="' . htmlspecialchars($value) . '">';
        }
        $html .= '</form>';
        $html .= '<script>document.getElementById("alipayForm").submit();</script>';
        $html .= '</body></html>';
        
        return $html;
    }
    
    /**
     * 格式化私钥
     */
    private function formatPrivateKey(string $key): string
    {
        $key = str_replace(["\r", "\n", ' '], '', $key);
        if (strpos($key, '-----BEGIN') === false) {
            $key = "-----BEGIN RSA PRIVATE KEY-----\n" . wordwrap($key, 64, "\n", true) . "\n-----END RSA PRIVATE KEY-----";
        }
        return $key;
    }
    
    /**
     * 格式化公钥
     */
    private function formatPublicKey(string $key): string
    {
        $key = str_replace(["\r", "\n", ' '], '', $key);
        if (strpos($key, '-----BEGIN') === false) {
            $key = "-----BEGIN PUBLIC KEY-----\n" . wordwrap($key, 64, "\n", true) . "\n-----END PUBLIC KEY-----";
        }
        return $key;
    }
}
