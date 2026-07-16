<?php
namespace App\Services;

class WechatOfficialAccountService
{
    protected $appId;
    protected $appSecret;
    protected $token;

    public function __construct($appId, $appSecret, $token = '')
    {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
        $this->token = $token;
    }

    public static function parseScopeConfig($scope)
    {
        $scope = trim((string)$scope);
        $config = [
            'login_mode' => $scope === 'mp_subscribe' ? 'mp_subscribe' : 'open_platform',
            'mp_token' => '',
        ];

        if ($scope !== '' && $scope[0] === '{') {
            $json = json_decode($scope, true);
            if (is_array($json)) {
                $config['login_mode'] = $json['login_mode'] ?? $config['login_mode'];
                $config['mp_token'] = $json['mp_token'] ?? '';
            }
        }

        return $config;
    }

    public static function buildScopeConfig($loginMode, $mpToken = '')
    {
        if ($loginMode !== 'mp_subscribe') {
            return 'snsapi_login';
        }

        return json_encode([
            'login_mode' => 'mp_subscribe',
            'mp_token' => trim($mpToken),
        ], JSON_UNESCAPED_UNICODE);
    }

    public function checkSignature($signature, $timestamp, $nonce)
    {
        if ($this->token === '') {
            return true;
        }

        $parts = [$this->token, $timestamp, $nonce];
        sort($parts, SORT_STRING);
        return sha1(implode($parts)) === $signature;
    }

    public function createLoginQrCode($scene, $expireSeconds = 600)
    {
        $accessToken = $this->getAccessToken();
        if ($accessToken === '') {
            return null;
        }

        $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=' . urlencode($accessToken);
        $payload = [
            'expire_seconds' => $expireSeconds,
            'action_name' => 'QR_STR_SCENE',
            'action_info' => [
                'scene' => [
                    'scene_str' => $scene,
                ],
            ],
        ];
        $response = $this->httpPostJson($url, $payload);
        $data = json_decode($response, true);

        if (empty($data['ticket'])) {
            return null;
        }

        return 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . urlencode($data['ticket']);
    }

    public function getUserInfo($openid)
    {
        $accessToken = $this->getAccessToken();
        if ($accessToken === '') {
            return [];
        }

        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?' . http_build_query([
            'access_token' => $accessToken,
            'openid' => $openid,
            'lang' => 'zh_CN',
        ]);
        $response = $this->httpGet($url);
        $data = json_decode($response, true);

        return is_array($data) && empty($data['errcode']) ? $data : [];
    }

    public function parseMessage($rawXml)
    {
        if (trim($rawXml) === '') {
            return [];
        }

        $xml = @simplexml_load_string($rawXml, 'SimpleXMLElement', LIBXML_NOCDATA);
        if (!$xml) {
            return [];
        }

        return json_decode(json_encode($xml, JSON_UNESCAPED_UNICODE), true) ?: [];
    }

    public function replyText($toUser, $fromUser, $content)
    {
        $time = time();
        $content = htmlspecialchars($content, ENT_XML1 | ENT_COMPAT, 'UTF-8');

        return "<xml><ToUserName><![CDATA[{$toUser}]]></ToUserName><FromUserName><![CDATA[{$fromUser}]]></FromUserName><CreateTime>{$time}</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA[{$content}]]></Content></xml>";
    }

    protected function getAccessToken()
    {
        if ($this->appId === '' || $this->appSecret === '') {
            return '';
        }

        $cacheFile = ML_ROOT . '/storage/cache/wechat_mp_' . md5($this->appId) . '.json';
        if (is_file($cacheFile)) {
            $cache = json_decode(file_get_contents($cacheFile), true);
            if (!empty($cache['access_token']) && (int)($cache['expire_at'] ?? 0) > time() + 60) {
                return $cache['access_token'];
            }
        }

        $url = 'https://api.weixin.qq.com/cgi-bin/token?' . http_build_query([
            'grant_type' => 'client_credential',
            'appid' => $this->appId,
            'secret' => $this->appSecret,
        ]);
        $response = $this->httpGet($url);
        $data = json_decode($response, true);

        if (empty($data['access_token'])) {
            return '';
        }

        if (!is_dir(dirname($cacheFile))) {
            mkdir(dirname($cacheFile), 0755, true);
        }
        file_put_contents($cacheFile, json_encode([
            'access_token' => $data['access_token'],
            'expire_at' => time() + (int)($data['expires_in'] ?? 7200),
        ]));

        return $data['access_token'];
    }

    protected function httpGet($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response ?: '';
    }

    protected function httpPostJson($url, array $data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response ?: '';
    }
}
