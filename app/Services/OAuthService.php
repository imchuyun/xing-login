<?php
/**
 * OAuth第三方登录服务类
 * 支持: GitHub, Google, 微信, QQ, 微博, 钉钉, 飞书, Gitee, 抖音, 支付宝
 */

namespace App\Services;

class OAuthService
{
    protected $platform;
    protected $config;
    protected $db;
    protected static $platformConfigs = [
        'github' => [
            'auth_url' => 'https://github.com/login/oauth/authorize',
            'token_url' => 'https://github.com/login/oauth/access_token',
            'user_url' => 'https://api.github.com/user',
            'scope' => 'read:user user:email',
        ],
        'google' => [
            'auth_url' => 'https://accounts.google.com/o/oauth2/v2/auth',
            'token_url' => 'https://oauth2.googleapis.com/token',
            'user_url' => 'https://www.googleapis.com/oauth2/v2/userinfo',
            'scope' => 'openid email profile',
        ],
        'wx' => [
            'auth_url' => 'https://open.weixin.qq.com/connect/qrconnect',
            'token_url' => 'https://api.weixin.qq.com/sns/oauth2/access_token',
            'user_url' => 'https://api.weixin.qq.com/sns/userinfo',
            'scope' => 'snsapi_login',
        ],
        'qq' => [
            'auth_url' => 'https://graph.qq.com/oauth2.0/authorize',
            'token_url' => 'https://graph.qq.com/oauth2.0/token',
            'openid_url' => 'https://graph.qq.com/oauth2.0/me',
            'user_url' => 'https://graph.qq.com/user/get_user_info',
            'scope' => 'get_user_info',
        ],
        'sina' => [
            'auth_url' => 'https://api.weibo.com/oauth2/authorize',
            'token_url' => 'https://api.weibo.com/oauth2/access_token',
            'user_url' => 'https://api.weibo.com/2/users/show.json',
            'scope' => '',
        ],
        'dingtalk' => [
            'auth_url' => 'https://login.dingtalk.com/oauth2/auth',
            'token_url' => 'https://api.dingtalk.com/v1.0/oauth2/userAccessToken',
            'user_url' => 'https://api.dingtalk.com/v1.0/contact/users/me',
            'scope' => 'openid corpid',
        ],
        'feishu' => [
            'auth_url' => 'https://open.feishu.cn/open-apis/authen/v1/authorize',
            'token_url' => 'https://open.feishu.cn/open-apis/authen/v1/access_token',
            'user_url' => 'https://open.feishu.cn/open-apis/authen/v1/user_info',
            'scope' => '',
        ],
        'gitee' => [
            'auth_url' => 'https://gitee.com/oauth/authorize',
            'token_url' => 'https://gitee.com/oauth/token',
            'user_url' => 'https://gitee.com/api/v5/user',
            'scope' => 'user_info',
        ],
        'douyin' => [
            'auth_url' => 'https://open.douyin.com/platform/oauth/connect',
            'token_url' => 'https://open.douyin.com/oauth/access_token',
            'user_url' => 'https://open.douyin.com/oauth/userinfo',
            'scope' => 'user_info',
        ],
        'alipay' => [
            'auth_url' => 'https://openauth.alipay.com/oauth2/publicAppAuthorize.htm',
            'gateway' => 'https://openapi.alipay.com/gateway.do',
            'scope' => 'auth_user',
        ],
        'baidu' => [
            'auth_url' => 'https://openapi.baidu.com/oauth/2.0/authorize',
            'token_url' => 'https://openapi.baidu.com/oauth/2.0/token',
            'user_url' => 'https://openapi.baidu.com/rest/2.0/passport/users/getInfo',
            'scope' => 'basic',
        ],
        'microsoft' => [
            'auth_url' => 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
            'token_url' => 'https://login.microsoftonline.com/common/oauth2/v2.0/token',
            'user_url' => 'https://graph.microsoft.com/v1.0/me',
            'scope' => 'openid profile email User.Read',
        ],
        'xiaomi' => [
            'auth_url' => 'https://account.xiaomi.com/oauth2/authorize',
            'token_url' => 'https://account.xiaomi.com/oauth2/token',
            'user_url' => 'https://open.account.xiaomi.com/user/profile',
            'scope' => 'profile',
        ],
        'wework' => [
            'auth_url' => 'https://open.work.weixin.qq.com/wwopen/sso/qrConnect',
            'token_url' => 'https://qyapi.weixin.qq.com/cgi-bin/gettoken',
            'user_url' => 'https://qyapi.weixin.qq.com/cgi-bin/auth/getuserinfo',
            'scope' => 'snsapi_base',
        ],
    ];

    public function __construct($platform, $config)
    {
        $this->platform = $platform;
        $this->config = $config;
        $this->db = \Core\Database::getInstance();
    }

    /**
     * 获取授权URL
     */
    public function getAuthUrl($redirectUri, $state = null)
    {
        $state = $state ?: $this->generateState();
        $platformConfig = self::$platformConfigs[$this->platform] ?? null;
        
        if (!$platformConfig) {
            throw new \Exception("不支持的平台: {$this->platform}");
        }

        $params = $this->buildAuthParams($redirectUri, $state);
        if ($this->platform === 'wx') {
            return $platformConfig['auth_url'] . '?' . http_build_query($params) . '#wechat_redirect';
        }
        
        return $platformConfig['auth_url'] . '?' . http_build_query($params);
    }

    /**
     * 构建授权参数
     */
    protected function buildAuthParams($redirectUri, $state)
    {
        $platformConfig = self::$platformConfigs[$this->platform];
        
        switch ($this->platform) {
            case 'github':
                return [
                    'client_id' => $this->config['app_id'],
                    'redirect_uri' => $redirectUri,
                    'scope' => $platformConfig['scope'],
                    'state' => $state,
                ];
            
            case 'google':
                return [
                    'client_id' => $this->config['app_id'],
                    'redirect_uri' => $redirectUri,
                    'response_type' => 'code',
                    'scope' => $platformConfig['scope'],
                    'state' => $state,
                    'access_type' => 'offline',
                ];
            
            case 'wx':
                return [
                    'appid' => $this->config['app_id'],
                    'redirect_uri' => $redirectUri,
                    'response_type' => 'code',
                    'scope' => $platformConfig['scope'],
                    'state' => $state,
                ];
            
            case 'qq':
                return [
                    'client_id' => $this->config['app_id'],
                    'redirect_uri' => $redirectUri,
                    'response_type' => 'code',
                    'scope' => $platformConfig['scope'],
                    'state' => $state,
                ];
            
            case 'sina':
                return [
                    'client_id' => $this->config['app_id'],
                    'redirect_uri' => $redirectUri,
                    'response_type' => 'code',
                    'state' => $state,
                ];
            
            case 'dingtalk':
                return [
                    'client_id' => $this->config['app_id'],
                    'redirect_uri' => $redirectUri,
                    'response_type' => 'code',
                    'scope' => $platformConfig['scope'],
                    'state' => $state,
                    'prompt' => 'consent',
                ];
            
            case 'feishu':
                return [
                    'app_id' => $this->config['app_id'],
                    'redirect_uri' => $redirectUri,
                    'response_type' => 'code',
                    'state' => $state,
                ];
            
            case 'gitee':
                return [
                    'client_id' => $this->config['app_id'],
                    'redirect_uri' => $redirectUri,
                    'response_type' => 'code',
                    'scope' => $platformConfig['scope'],
                    'state' => $state,
                ];
            
            case 'douyin':
                return [
                    'client_key' => $this->config['app_id'],
                    'redirect_uri' => $redirectUri,
                    'response_type' => 'code',
                    'scope' => $platformConfig['scope'],
                    'state' => $state,
                ];
            
            case 'alipay':
                return [
                    'app_id' => $this->config['app_id'],
                    'redirect_uri' => $redirectUri,
                    'scope' => $platformConfig['scope'],
                    'state' => $state,
                ];
            
            case 'wework':
                return [
                    'appid' => $this->config['app_id'],
                    'agentid' => $this->config['agent_id'] ?? '',
                    'redirect_uri' => $redirectUri,
                    'state' => $state,
                ];
            
            default:
                return [
                    'client_id' => $this->config['app_id'],
                    'redirect_uri' => $redirectUri,
                    'response_type' => 'code',
                    'state' => $state,
                ];
        }
    }

    /**
     * 通过授权码获取用户信息
     */
    public function getUserByCode($code, $redirectUri)
    {
        $tokenData = $this->getAccessToken($code, $redirectUri);
        
        if ($this->platform === 'dingtalk' && isset($tokenData['accessToken'])) {
            $tokenData['access_token'] = $tokenData['accessToken'];
        }
        
        if (empty($tokenData['access_token'])) {
            // 提供更详细的错误信息
            $errorMsg = '获取access_token失败';
            if (isset($tokenData['error'])) {
                $errorMsg .= ': ' . $tokenData['error'];
                if (isset($tokenData['error_description'])) {
                    $errorMsg .= ' - ' . $tokenData['error_description'];
                }
            } elseif (isset($tokenData['errcode'])) {
                $errorMsg .= ': ' . ($tokenData['errmsg'] ?? '未知错误') . ' (错误码: ' . $tokenData['errcode'] . ')';
            }
            throw new \Exception($errorMsg);
        }
        $userInfo = $this->getUserInfo($tokenData);
        
        return $this->normalizeUserInfo($userInfo, $tokenData);
    }

    /**
     * 获取Access Token
     */
    protected function getAccessToken($code, $redirectUri)
    {
        $platformConfig = self::$platformConfigs[$this->platform];
        
        switch ($this->platform) {
            case 'github':
                $params = [
                    'client_id' => $this->config['app_id'],
                    'client_secret' => $this->config['app_secret'],
                    'code' => $code,
                    'redirect_uri' => $redirectUri,
                ];
                $response = $this->httpPost($platformConfig['token_url'], $params, [
                    'Accept: application/json'
                ]);
                return json_decode($response, true);
            
            case 'google':
                $params = [
                    'client_id' => $this->config['app_id'],
                    'client_secret' => $this->config['app_secret'],
                    'code' => $code,
                    'redirect_uri' => $redirectUri,
                    'grant_type' => 'authorization_code',
                ];
                $response = $this->httpPost($platformConfig['token_url'], $params);
                return json_decode($response, true);
            
            case 'wx':
                $url = $platformConfig['token_url'] . '?' . http_build_query([
                    'appid' => $this->config['app_id'],
                    'secret' => $this->config['app_secret'],
                    'code' => $code,
                    'grant_type' => 'authorization_code',
                ]);
                $response = $this->httpGet($url);
                $result = json_decode($response, true);
                // 微信返回错误时会有errcode字段
                if (isset($result['errcode']) && $result['errcode'] != 0) {
                    throw new \Exception('微信登录失败: ' . ($result['errmsg'] ?? '未知错误') . ' (错误码: ' . $result['errcode'] . ')');
                }
                return $result;
            
            case 'qq':
                $url = $platformConfig['token_url'] . '?' . http_build_query([
                    'client_id' => $this->config['app_id'],
                    'client_secret' => $this->config['app_secret'],
                    'code' => $code,
                    'redirect_uri' => $redirectUri,
                    'grant_type' => 'authorization_code',
                    'fmt' => 'json',
                ]);
                $response = $this->httpGet($url);
                $data = json_decode($response, true);
                if (!empty($data['access_token'])) {
                    $openidUrl = $platformConfig['openid_url'] . '?access_token=' . $data['access_token'] . '&fmt=json';
                    $openidRes = $this->httpGet($openidUrl);
                    $openidData = json_decode($openidRes, true);
                    $data['openid'] = $openidData['openid'] ?? '';
                }
                return $data;
            
            case 'sina':
                $params = [
                    'client_id' => $this->config['app_id'],
                    'client_secret' => $this->config['app_secret'],
                    'code' => $code,
                    'redirect_uri' => $redirectUri,
                    'grant_type' => 'authorization_code',
                ];
                $response = $this->httpPost($platformConfig['token_url'], $params);
                return json_decode($response, true);
            
            case 'dingtalk':
                $params = [
                    'clientId' => $this->config['app_id'],
                    'clientSecret' => $this->config['app_secret'],
                    'code' => $code,
                    'grantType' => 'authorization_code',
                ];
                $response = $this->httpPostJson($platformConfig['token_url'], $params);
                $result = json_decode($response, true);
                if (isset($result['code']) && $result['code'] !== 0) {
                    throw new \Exception('钉钉登录失败: ' . ($result['message'] ?? '未知错误'));
                }
                return $result;
            
            case 'feishu':
                $appTokenUrl = 'https://open.feishu.cn/open-apis/auth/v3/app_access_token/internal';
                $appTokenRes = $this->httpPostJson($appTokenUrl, [
                    'app_id' => $this->config['app_id'],
                    'app_secret' => $this->config['app_secret'],
                ]);
                $appTokenData = json_decode($appTokenRes, true);
                if (empty($appTokenData['app_access_token'])) {
                    throw new \Exception('获取飞书app_access_token失败: ' . ($appTokenData['msg'] ?? '未知错误'));
                }
                $appToken = $appTokenData['app_access_token'];
                $params = [
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                ];
                $response = $this->httpPostJson($platformConfig['token_url'], $params, [
                    'Authorization: Bearer ' . $appToken,
                    'Content-Type: application/json; charset=utf-8'
                ]);
                $result = json_decode($response, true);
                if (isset($result['code']) && $result['code'] !== 0) {
                    throw new \Exception('获取飞书access_token失败: ' . ($result['msg'] ?? '未知错误'));
                }
                return $result['data'] ?? $result;
            
            case 'gitee':
                $params = [
                    'client_id' => $this->config['app_id'],
                    'client_secret' => $this->config['app_secret'],
                    'code' => $code,
                    'redirect_uri' => $redirectUri,
                    'grant_type' => 'authorization_code',
                ];
                $response = $this->httpPost($platformConfig['token_url'], $params);
                return json_decode($response, true);
            
            case 'douyin':
                $params = [
                    'client_key' => $this->config['app_id'],
                    'client_secret' => $this->config['app_secret'],
                    'code' => $code,
                    'grant_type' => 'authorization_code',
                ];
                $response = $this->httpPost($platformConfig['token_url'], $params);
                $result = json_decode($response, true);
                return $result['data'] ?? [];
            
            case 'alipay':
                return $this->getAlipayAccessToken($code);
            
            case 'wework':
                // 企业微信需要先获取企业access_token，再获取用户信息
                $tokenUrl = self::$platformConfigs['wework']['token_url'] . '?' . http_build_query([
                    'corpid' => $this->config['app_id'],
                    'corpsecret' => $this->config['app_secret'],
                ]);
                
                $response = $this->httpGet($tokenUrl);
                $result = json_decode($response, true);
                
                if (empty($result['access_token'])) {
                    throw new \Exception('企业微信获取access_token失败: ' . ($result['errmsg'] ?? '未知错误'));
                }
                // 返回企业access_token和code，后续getUserInfo会用code换取用户信息
                return [
                    'access_token' => $result['access_token'],
                    'code' => $code,
                ];
            
            default:
                throw new \Exception("不支持的平台: {$this->platform}");
        }
    }

    /**
     * 获取用户信息
     */
    protected function getUserInfo($tokenData)
    {
        $platformConfig = self::$platformConfigs[$this->platform];
        $accessToken = $tokenData['access_token'];
        
        switch ($this->platform) {
            case 'github':
                $response = $this->httpGet($platformConfig['user_url'], [
                    'Authorization: Bearer ' . $accessToken,
                    'User-Agent: MaxLogin-OAuth'
                ]);
                return json_decode($response, true);
            
            case 'google':
                $url = $platformConfig['user_url'] . '?access_token=' . $accessToken;
                $response = $this->httpGet($url);
                return json_decode($response, true);
            
            case 'wx':
                $url = $platformConfig['user_url'] . '?' . http_build_query([
                    'access_token' => $accessToken,
                    'openid' => $tokenData['openid'],
                    'lang' => 'zh_CN',
                ]);
                $response = $this->httpGet($url);
                return json_decode($response, true);
            
            case 'qq':
                $url = $platformConfig['user_url'] . '?' . http_build_query([
                    'access_token' => $accessToken,
                    'oauth_consumer_key' => $this->config['app_id'],
                    'openid' => $tokenData['openid'],
                ]);
                $response = $this->httpGet($url);
                $data = json_decode($response, true);
                $data['openid'] = $tokenData['openid'];
                return $data;
            
            case 'sina':
                $url = $platformConfig['user_url'] . '?' . http_build_query([
                    'access_token' => $accessToken,
                    'uid' => $tokenData['uid'],
                ]);
                $response = $this->httpGet($url);
                return json_decode($response, true);
            
            case 'dingtalk':
                $response = $this->httpGet($platformConfig['user_url'], [
                    'x-acs-dingtalk-access-token: ' . $accessToken
                ]);
                $result = json_decode($response, true);
                if (isset($result['code']) && $result['code'] !== 0) {
                    throw new \Exception('钉钉获取用户信息失败: ' . ($result['message'] ?? '未知错误'));
                }
                return $result;
            
            case 'feishu':
                $response = $this->httpGet($platformConfig['user_url'], [
                    'Authorization: Bearer ' . $accessToken
                ]);
                $result = json_decode($response, true);
                return $result['data'] ?? [];
            
            case 'gitee':
                $url = $platformConfig['user_url'] . '?access_token=' . $accessToken;
                $response = $this->httpGet($url);
                return json_decode($response, true);
            
            case 'douyin':
                $url = $platformConfig['user_url'] . '?' . http_build_query([
                    'access_token' => $accessToken,
                    'open_id' => $tokenData['open_id'],
                ]);
                $response = $this->httpGet($url);
                $result = json_decode($response, true);
                return $result['data'] ?? [];
            
            case 'alipay':
                return $tokenData['user_info'] ?? [];
            
            case 'wework':
                // 使用企业access_token和code获取用户信息
                $url = $platformConfig['user_url'] . '?' . http_build_query([
                    'access_token' => $accessToken,
                    'code' => $tokenData['code'],
                ]);
                $response = $this->httpGet($url);
                $result = json_decode($response, true);
                if (isset($result['errcode']) && $result['errcode'] !== 0) {
                    throw new \Exception('企业微信获取用户信息失败: ' . ($result['errmsg'] ?? '未知错误'));
                }
                return $result;
            
            default:
                throw new \Exception("不支持的平台: {$this->platform}");
        }
    }

    /**
     * 标准化用户信息
     */
    protected function normalizeUserInfo($userInfo, $tokenData)
    {
        $normalized = [
            'platform' => $this->platform,
            'open_id' => '',
            'union_id' => null,
            'nickname' => '',
            'avatar' => '',
            'gender' => 'unknown',
            'location' => '',
            'email' => null,
            'access_token' => $tokenData['access_token'] ?? '',
            'refresh_token' => $tokenData['refresh_token'] ?? null,
            'expires_in' => $tokenData['expires_in'] ?? null,
            'data' => $userInfo,
        ];
        
        switch ($this->platform) {
            case 'github':
                $normalized['open_id'] = (string)($userInfo['id'] ?? '');
                $normalized['nickname'] = $userInfo['login'] ?? $userInfo['name'] ?? '';
                $normalized['avatar'] = $userInfo['avatar_url'] ?? '';
                $normalized['email'] = $userInfo['email'] ?? null;
                break;
            
            case 'google':
                $normalized['open_id'] = $userInfo['id'] ?? '';
                $normalized['nickname'] = $userInfo['name'] ?? '';
                $normalized['avatar'] = $userInfo['picture'] ?? '';
                $normalized['email'] = $userInfo['email'] ?? null;
                break;
            
            case 'wx':
                $normalized['open_id'] = $userInfo['openid'] ?? $tokenData['openid'] ?? '';
                $normalized['union_id'] = $userInfo['unionid'] ?? null;
                $normalized['nickname'] = $userInfo['nickname'] ?? '';
                $normalized['avatar'] = $userInfo['headimgurl'] ?? '';
                $wxSex = (int)($userInfo['sex'] ?? 0);
                $normalized['gender'] = $wxSex === 1 ? 'male' : ($wxSex === 2 ? 'female' : 'unknown');
                $province = $userInfo['province'] ?? '';
                $city = $userInfo['city'] ?? '';
                $normalized['location'] = trim($province . ' ' . $city);
                break;
            
            case 'qq':
                $normalized['open_id'] = $userInfo['openid'] ?? $tokenData['openid'] ?? '';
                $normalized['nickname'] = $userInfo['nickname'] ?? '';
                $normalized['avatar'] = $userInfo['figureurl_qq_2'] ?? $userInfo['figureurl_qq_1'] ?? '';
                $qqGender = $userInfo['gender'] ?? '';
                $normalized['gender'] = $qqGender === '男' ? 'male' : ($qqGender === '女' ? 'female' : 'unknown');
                $normalized['location'] = $userInfo['province'] ?? '';
                break;
            
            case 'sina':
                $normalized['open_id'] = (string)($userInfo['id'] ?? $tokenData['uid'] ?? '');
                $normalized['nickname'] = $userInfo['screen_name'] ?? $userInfo['name'] ?? '';
                $normalized['avatar'] = $userInfo['avatar_large'] ?? $userInfo['profile_image_url'] ?? '';
                $wbGender = $userInfo['gender'] ?? 'n';
                $normalized['gender'] = $wbGender === 'm' ? 'male' : ($wbGender === 'f' ? 'female' : 'unknown');
                $normalized['location'] = $userInfo['location'] ?? '';
                break;
            
            case 'dingtalk':
                $normalized['open_id'] = $userInfo['openId'] ?? $userInfo['unionId'] ?? '';
                $normalized['union_id'] = $userInfo['unionId'] ?? null;
                $normalized['nickname'] = $userInfo['nick'] ?? '';
                $normalized['avatar'] = $userInfo['avatarUrl'] ?? '';
                $normalized['email'] = $userInfo['email'] ?? null;
                break;
            
            case 'feishu':
                $normalized['open_id'] = $userInfo['open_id'] ?? '';
                $normalized['union_id'] = $userInfo['union_id'] ?? null;
                $normalized['nickname'] = $userInfo['name'] ?? '';
                $normalized['avatar'] = $userInfo['avatar_url'] ?? '';
                $normalized['email'] = $userInfo['email'] ?? null;
                break;
            
            case 'gitee':
                $normalized['open_id'] = (string)($userInfo['id'] ?? '');
                $normalized['nickname'] = $userInfo['login'] ?? $userInfo['name'] ?? '';
                $normalized['avatar'] = $userInfo['avatar_url'] ?? '';
                $normalized['email'] = $userInfo['email'] ?? null;
                break;
            
            case 'douyin':
                $normalized['open_id'] = $userInfo['open_id'] ?? $tokenData['open_id'] ?? '';
                $normalized['union_id'] = $userInfo['union_id'] ?? null;
                $normalized['nickname'] = $userInfo['nickname'] ?? '';
                $normalized['avatar'] = $userInfo['avatar'] ?? '';
                break;
            
            case 'alipay':
                $normalized['open_id'] = $userInfo['user_id'] ?? $tokenData['user_id'] ?? '';
                $normalized['nickname'] = $userInfo['nick_name'] ?? '';
                $normalized['avatar'] = $userInfo['avatar'] ?? '';
                $alipayGender = $userInfo['gender'] ?? '';
                $normalized['gender'] = $alipayGender === 'M' ? 'male' : ($alipayGender === 'F' ? 'female' : 'unknown');
                $province = $userInfo['province'] ?? '';
                $city = $userInfo['city'] ?? '';
                $normalized['location'] = trim($province . ' ' . $city);
                break;
            
            case 'wework':
                $normalized['open_id'] = $userInfo['userid'] ?? $userInfo['OpenId'] ?? '';
                $normalized['union_id'] = $userInfo['open_userid'] ?? null;
                $normalized['nickname'] = $userInfo['userid'] ?? '';
                $normalized['avatar'] = '';
                $normalized['email'] = $userInfo['email'] ?? null;
                break;
        }
        
        return $normalized;
    }

    /**
     * 生成State参数
     */
    protected function generateState()
    {
        return md5(uniqid(mt_rand(), true));
    }

    /**
     * HTTP GET请求
     */
    protected function httpGet($url, $headers = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return $response;
    }

    /**
     * HTTP POST请求(表单)
     */
    protected function httpPost($url, $data, $headers = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return $response;
    }

    /**
     * HTTP POST请求(JSON)
     */
    protected function httpPostJson($url, $data, $headers = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return $response;
    }

    /**
     * 获取支持的平台列表
     */
    public static function getSupportedPlatforms()
    {
        return array_keys(self::$platformConfigs);
    }

    /**
     * 支付宝获取AccessToken和用户信息
     * 支付宝需要使用RSA签名
     */
    protected function getAlipayAccessToken($code)
    {
        $platformConfig = self::$platformConfigs['alipay'];
        
        $params = [
            'app_id' => $this->config['app_id'],
            'method' => 'alipay.system.oauth.token',
            'format' => 'JSON',
            'charset' => 'utf-8',
            'sign_type' => 'RSA2',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0',
            'grant_type' => 'authorization_code',
            'code' => $code,
        ];
        try {
            $params['sign'] = $this->generateAlipaySign($params);
        } catch (\Exception $e) {
            throw $e;
        }
        $response = $this->httpPost($platformConfig['gateway'], $params);
        $result = json_decode($response, true);
        
        // 支付宝可能返回不同的响应格式
        $tokenResponse = $result['alipay_system_oauth_token_response'] ?? null;
        
        // 检查是否有错误响应
        if (isset($result['error_response'])) {
            $errorMsg = $result['error_response']['sub_msg'] ?? $result['error_response']['msg'] ?? '获取token失败';
            throw new \Exception('支付宝登录失败: ' . $errorMsg);
        }
        
        if (!$tokenResponse) {
            throw new \Exception('支付宝登录失败: 响应格式异常');
        }
        
        // 检查 tokenResponse 内部是否有错误
        if (isset($tokenResponse['code']) && $tokenResponse['code'] != '10000') {
            $errorMsg = $tokenResponse['sub_msg'] ?? $tokenResponse['msg'] ?? '获取token失败';
            throw new \Exception('支付宝登录失败: ' . $errorMsg);
        }
        
        $accessToken = $tokenResponse['access_token'] ?? '';
        $userId = $tokenResponse['user_id'] ?? $tokenResponse['open_id'] ?? '';
        
        if (empty($accessToken) || empty($userId)) {
            throw new \Exception('支付宝登录失败: 获取access_token或user_id为空');
        }
        
        $userInfo = $this->getAlipayUserInfo($accessToken);
        
        return [
            'access_token' => $accessToken,
            'user_id' => $userId,
            'refresh_token' => $tokenResponse['refresh_token'] ?? null,
            'expires_in' => $tokenResponse['expires_in'] ?? null,
            'user_info' => $userInfo,
        ];
    }

    /**
     * 支付宝获取用户信息
     */
    protected function getAlipayUserInfo($accessToken)
    {
        $platformConfig = self::$platformConfigs['alipay'];
        
        $params = [
            'app_id' => $this->config['app_id'],
            'method' => 'alipay.user.info.share',
            'format' => 'JSON',
            'charset' => 'utf-8',
            'sign_type' => 'RSA2',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0',
            'auth_token' => $accessToken,
        ];
        
        $params['sign'] = $this->generateAlipaySign($params);
        
        $response = $this->httpPost($platformConfig['gateway'], $params);
        $result = json_decode($response, true);
        
        $userResponse = $result['alipay_user_info_share_response'] ?? null;
        if (!$userResponse || isset($result['error_response'])) {
            return [];
        }
        
        return $userResponse;
    }

    /**
     * 生成支付宝签名
     */
    protected function generateAlipaySign($params)
    {
        unset($params['sign']);
        ksort($params);
        $stringToBeSigned = '';
        foreach ($params as $k => $v) {
            if ($v !== '' && $v !== null && !is_array($v)) {
                $stringToBeSigned .= $k . '=' . $v . '&';
            }
        }
        $stringToBeSigned = rtrim($stringToBeSigned, '&');
        $privateKey = $this->formatPrivateKey($this->config['app_secret']);
        
        $res = openssl_get_privatekey($privateKey);
        if (!$res) {
            throw new \Exception('支付宝私钥格式错误，请检查私钥配置');
        }
        
        openssl_sign($stringToBeSigned, $sign, $res, OPENSSL_ALGO_SHA256);
        
        return base64_encode($sign);
    }
    
    /**
     * 格式化私钥
     */
    protected function formatPrivateKey($privateKey)
    {
        $privateKey = trim($privateKey);
        if (strpos($privateKey, '-----BEGIN') !== false) {
            return $privateKey;
        }
        $privateKey = str_replace(["\r", "\n", " "], '', $privateKey);
        $pkcs8Key = "-----BEGIN PRIVATE KEY-----\n" . 
                    wordwrap($privateKey, 64, "\n", true) . 
                    "\n-----END PRIVATE KEY-----";
        
        $res = @openssl_get_privatekey($pkcs8Key);
        if ($res) {
            return $pkcs8Key;
        }
        $pkcs1Key = "-----BEGIN RSA PRIVATE KEY-----\n" . 
                    wordwrap($privateKey, 64, "\n", true) . 
                    "\n-----END RSA PRIVATE KEY-----";
        
        return $pkcs1Key;
    }
}
