<?php
namespace App\Controllers;

use App\Services\OAuthService;
use App\Services\BillingService;

/**
 * 彩虹聚合登录API控制器
 * 
 * 完全按照彩虹聚合登录接口规范实现
 * 
 * 接口入口: /connect.php
 * 回调入口: /return.php
 */
class ConnectController extends BaseController
{
    /**
     * @var BillingService
     */
    private $billingService;
    /**
     * API主入口 - connect.php
     */
    public function handle()
    {
        header('Content-Type: application/json; charset=UTF-8');
        header('Access-Control-Allow-Origin: *');
        
        $act = $this->input('act', '');
        if (empty($act)) {
            $this->jsonError(-1, 101, 'no act');
        }
        
        $appid = $this->input('appid', '');
        if (empty($appid)) {
            $this->jsonError(-1, 101, 'no appid');
        }
        
        $appkey = $this->input('appkey', '');
        if (empty($appkey)) {
            $this->jsonError(-1, 101, 'no appkey');
        }
        $app = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}apps WHERE app_id = ? LIMIT 1",
            [$appid]
        );
        
        if (!$app) {
            $this->jsonError(-1, 102, '应用appid不存在');
        }
        if ($app['status'] == 0) {
            $this->jsonError(-1, 102, '应用已关闭');
        }
        if ($app['app_secret'] !== $appkey) {
            $this->jsonError(-1, 103, 'appkey不正确');
        }
        $appOwner = $this->db->fetch(
            "SELECT status FROM {$this->db->getPrefix()}users WHERE id = ? LIMIT 1",
            [$app['user']]
        );
        if (!$appOwner || $appOwner['status'] !== 'enable') {
            $this->jsonError(-1, 102, '应用所属账户已被禁用');
        }
        
        $type = $this->input('type', 'qq');
        
        switch ($act) {
            case 'login':
                return $this->login($app, $type);
            case 'callback':
                return $this->callback($app);
            case 'query':
                return $this->query($app, $type);
            default:
                $this->jsonError(-1, 101, '无效的act参数');
        }
    }
    
    /**
     * 登录接口 - 获取第三方授权URL
     * 
     * 参数: appid, appkey, type, redirect_uri, state
     * 返回: {code: 0, url: "授权地址"}
     */
    protected function login($app, $type)
    {
        $redirectUri = $this->input('redirect_uri', '');
        if (empty($redirectUri)) {
            $this->jsonError(-1, 101, 'no redirect_uri');
        }
        
        $state = $this->input('state', '');
        $urlArr = parse_url($redirectUri);
        $callbackHost = $urlArr['host'] ?? '';
        
        if (!empty($app['domain'])) {
            $appDomain = $app['domain'];
            if ($callbackHost !== $appDomain && !str_ends_with($callbackHost, '.' . $appDomain)) {
                $this->jsonError(-1, 103, '回调域名未授权');
            }
        }
        $allowedPlatforms = explode(',', $app['platforms']);
        if (!in_array($type, $allowedPlatforms)) {
            $this->jsonError(-1, 104, '该应用未启用此登录方式');
        }
        $platformConfig = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}platforms WHERE name = ? AND status = 1 LIMIT 1",
            [$type]
        );
        
        if (!$platformConfig) {
            $this->jsonError(-1, 104, '当前登录方式未开启');
        }
        if (empty($platformConfig['app_id'])) {
            $this->jsonError(-1, 104, '当前登录方式未配置密钥');
        }
        $code = strtoupper(md5(uniqid(rand(), true)));
        $this->db->insert($this->db->getPrefix() . 'oauth_logs', [
            'code' => $code,
            'app_id' => $app['app_id'],
            'user' => $app['user'],
            'type' => $type,
            'platform' => $type,
            'domain' => $callbackHost,
            'redirect' => $redirectUri,  // 内部字段名为redirect
            'state' => $state,
            'status' => 0,
            'time' => date('Y-m-d H:i:s'),
        ]);
        
        $logId = $this->db->lastInsertId();
        $internalState = $this->encodeState($type, $logId);
        $authUrl = $this->buildAuthUrl($type, $platformConfig, $internalState);
        $result = [
            'code' => 0,
            'msg' => 'success',
            'type' => $type,
            'url' => $authUrl,
        ];
        if ($type == 'wx' || $type == 'alipay') {
            $result['qrcode'] = $authUrl . '&client=1';
        }
        
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * 回调接口 - 获取用户信息
     * 
     * 参数: appid, appkey, code
     * 返回: {code: 0, type, social_uid, access_token, nickname, faceimg, gender, location, ip}
     * 
     * 集成计费检查 (Requirements: 2.1, 3.1, 4.1, 5.1, 6.1, 10.3)
     */
    protected function callback($app)
    {
        $code = $this->input('code', '');
        if (empty($code)) {
            $this->jsonError(-1, 101, 'no code');
        }
        $log = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}oauth_logs WHERE app_id = ? AND code = ? ORDER BY id DESC LIMIT 1",
            [$app['app_id'], $code]
        );
        
        if (!$log) {
            $this->jsonError(-1, 102, '记录不存在');
        }
        if ($log['status'] == 1) {
            if (strtotime($log['last_time']) < time() - 60) {
                $this->jsonError(-1, 102, 'CODE已失效');
            }
            $account = $this->db->fetch(
                "SELECT * FROM {$this->db->getPrefix()}oauth_users WHERE app_id = ? AND type = ? AND open_id = ? LIMIT 1",
                [$app['app_id'], $log['type'], $log['open_id']]
            );
            
            if ($account) {
                $this->jsonSuccess([
                    'type' => $log['type'],
                    'social_uid' => $account['open_id'],
                    'access_token' => $account['access_token'],
                    'nickname' => $account['nickname'],
                    'faceimg' => $account['avatar'],
                    'gender' => $this->convertGenderForApi($account['gender']),
                    'location' => $account['location'] ?? '',
                    'ip' => $log['ip'],
                ]);
            }
        }
        if (empty($log['platform_code'])) {
            echo json_encode(['code' => 2, 'msg' => '等待授权中'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $platformConfig = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}platforms WHERE name = ? AND status = 1 LIMIT 1",
            [$log['platform']]
        );
        
        if (!$platformConfig) {
            $this->jsonError(-1, 104, '当前登录方式未开启');
        }
        try {
            $decryptedSecret = decrypt($platformConfig['app_secret']);
            
            $oauth = new OAuthService($log['platform'], [
                'app_id' => $platformConfig['app_id'],
                'app_secret' => $decryptedSecret,
                'agent_id' => $platformConfig['scope'] ?? '', // 企业微信使用scope字段存储agentid
            ]);
            
            $callbackUrl = config('site.url') . '/return.php';
            $userInfo = $oauth->getUserByCode($log['platform_code'], $callbackUrl);
            
        } catch (\Exception $e) {
            $this->jsonError(-1, 301, $e->getMessage());
        }
        $billingService = $this->getBillingService();
        $accessCheck = $billingService->checkAccess(
            (int)$app['user'],
            $app['app_id'],
            $log['type'],
            $userInfo['open_id'] ?? null
        );
        
        if (!$accessCheck['allowed']) {
            $this->jsonError(-1, $accessCheck['error_code'], $accessCheck['error']);
        }
        $existingUser = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}oauth_users WHERE app_id = ? AND type = ? AND open_id = ? LIMIT 1",
            [$app['app_id'], $log['type'], $userInfo['open_id']]
        );
        
        if ($existingUser) {
            $genderStr = $this->convertGenderToString($userInfo['gender'] ?? 0);
            
            $this->db->query(
                "UPDATE {$this->db->getPrefix()}oauth_users SET 
                    access_token = ?, nickname = ?, avatar = ?, gender = ?, location = ?, 
                    ip = ?, last_time = NOW() WHERE id = ?",
                [
                    $userInfo['access_token'] ?? '',
                    $userInfo['nickname'] ?? '',
                    $userInfo['avatar'] ?? '',
                    $genderStr,
                    $userInfo['location'] ?? '',
                    $log['ip'],
                    $existingUser['id']
                ]
            );
        } else {
            $genderStr = $this->convertGenderToString($userInfo['gender'] ?? 0);
            
            $this->db->insert($this->db->getPrefix() . 'oauth_users', [
                'app_id' => $app['app_id'],
                'user' => $app['user'],
                'type' => $log['type'],
                'open_id' => $userInfo['open_id'],
                'access_token' => $userInfo['access_token'] ?? '',
                'nickname' => $userInfo['nickname'] ?? '',
                'avatar' => $userInfo['avatar'] ?? '',
                'gender' => $genderStr,
                'location' => $userInfo['location'] ?? '',
                'ip' => $log['ip'],
                'time' => date('Y-m-d H:i:s'),
                'last_time' => date('Y-m-d H:i:s'),
            ]);
        }
        $this->db->query(
            "UPDATE {$this->db->getPrefix()}oauth_logs SET open_id = ?, status = 1, last_time = NOW() WHERE id = ?",
            [$userInfo['open_id'], $log['id']]
        );
        $this->updateCallCount($app);
        $billingService->recordCall(
            (int)$app['user'],
            $app['app_id'],
            $log['type'],
            $accessCheck['package_type'] ?? 'free',
            $accessCheck['package_id'] ?? null,
            $log['ip'] ?? $_SERVER['REMOTE_ADDR'] ?? null
        );
        $this->addRateLimitHeaders($accessCheck);
        $this->jsonSuccess([
            'type' => $log['type'],
            'social_uid' => $userInfo['open_id'],
            'access_token' => $userInfo['access_token'] ?? '',
            'nickname' => $userInfo['nickname'] ?? '',
            'faceimg' => $userInfo['avatar'] ?? '',
            'gender' => $this->convertGenderForApi($userInfo['gender'] ?? 0),
            'location' => $userInfo['location'] ?? '',
            'ip' => $log['ip'],
        ]);
    }
    
    /**
     * 查询接口 - 查询用户信息
     * 
     * 参数: appid, appkey, type, social_uid
     * 返回: {code: 0, ...用户信息}
     */
    protected function query($app, $type)
    {
        $socialUid = $this->input('social_uid', '');
        if (empty($socialUid)) {
            $this->jsonError(-1, 101, 'social_uid不能为空');
        }
        
        $account = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}oauth_users WHERE app_id = ? AND type = ? AND open_id = ? LIMIT 1",
            [$app['app_id'], $type, $socialUid]
        );
        
        if (!$account) {
            $this->jsonError(-1, 102, 'none');
        }
        
        $this->jsonSuccess([
            'type' => $account['type'],
            'social_uid' => $account['open_id'],
            'access_token' => $account['access_token'],
            'nickname' => $account['nickname'],
            'faceimg' => $account['avatar'],
            'gender' => $this->convertGenderForApi($account['gender']),
            'location' => $account['location'] ?? '',
            'ip' => $account['ip'],
        ]);
    }
    
    /**
     * 第三方平台回调入口 - return.php
     */
    public function return()
    {
        $code = $this->input('code', '') ?: $this->input('auth_code', '') ?: $this->input('authCode', '');
        if (empty($code)) {
            $error = $this->input('error', '');
            $errorDesc = $this->input('error_description', '');
            if ($error) {
                $this->showError("[{$error}] {$errorDesc}");
            }
            exit;
        }
        
        $state = $this->input('state', '');
        $stateData = $this->decodeState($state);
        if (!$stateData) {
            $this->showError('state无效');
        }
        
        $type = $stateData['type'];
        $logId = $stateData['log_id'];
        $log = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}oauth_logs WHERE id = ? LIMIT 1",
            [$logId]
        );
        
        if (!$log) {
            $this->showError('登录记录不存在');
        }
        if (strtotime($log['time']) < time() - 600) {
            $this->showError('登录已过期');
        }
        $this->db->query(
            "UPDATE {$this->db->getPrefix()}oauth_logs SET platform_code = ?, ip = ? WHERE id = ?",
            [$code, $_SERVER['REMOTE_ADDR'] ?? '', $logId]
        );
        $redirectUri = $log['redirect'];
        $separator = strpos($redirectUri, '?') !== false ? '&' : '?';
        $callbackUrl = $redirectUri . $separator . http_build_query([
            'type' => $type,
            'code' => $log['code'],
            'state' => $log['state'],
        ]);
        
        header('Location: ' . $callbackUrl);
        exit;
    }
    
    /**
     * 获取 BillingService 实例
     * 
     * @return BillingService
     */
    protected function getBillingService(): BillingService
    {
        if ($this->billingService === null) {
            $this->billingService = new BillingService($this->db);
        }
        return $this->billingService;
    }
    
    /**
     * 添加频率限制响应头
     * 
     * 在响应中添加 X-RateLimit-Remaining 等头信息
     * 
     * @param array $accessCheck 计费检查结果
     * @return void
     * 
     * Requirements: 10.3
     */
    protected function addRateLimitHeaders(array $accessCheck): void
    {
        $packageType = $accessCheck['package_type'] ?? 'free';
        switch ($packageType) {
            case 'quota':
                if (isset($accessCheck['remaining'])) {
                    header('X-RateLimit-Remaining: ' . $accessCheck['remaining']);
                    header('X-RateLimit-Limit: ' . ($accessCheck['total_quota'] ?? 0));
                }
                break;
                
            case 'account':
                if (isset($accessCheck['account_limit']) && isset($accessCheck['current_count'])) {
                    $remaining = max(0, $accessCheck['account_limit'] - $accessCheck['current_count']);
                    header('X-Account-Remaining: ' . $remaining);
                    header('X-Account-Limit: ' . $accessCheck['account_limit']);
                }
                break;
                
            case 'free':
                if (isset($accessCheck['remaining_today'])) {
                    header('X-RateLimit-Remaining: ' . $accessCheck['remaining_today']);
                    header('X-RateLimit-Limit: ' . ($accessCheck['daily_limit'] ?? 0));
                    $resetTime = strtotime('tomorrow');
                    header('X-RateLimit-Reset: ' . $resetTime);
                }
                break;
                
            case 'package':
            default:
                break;
        }
        header('X-Package-Type: ' . $packageType);
    }
    
    /**
     * 构建第三方授权URL
     */
    protected function buildAuthUrl($platform, $platformConfig, $state)
    {
        $redirectUri = urlencode(config('site.url') . '/return.php');
        $appId = $platformConfig['app_id'];

        switch ($platform) {
            case 'qq':
                return "https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id={$appId}&redirect_uri={$redirectUri}&state={$state}&scope=get_user_info";
            
            case 'wx':
                return "https://open.weixin.qq.com/connect/qrconnect?appid={$appId}&redirect_uri={$redirectUri}&response_type=code&scope=snsapi_login&state={$state}#wechat_redirect";
            
            case 'sina':
                return "https://api.weibo.com/oauth2/authorize?client_id={$appId}&redirect_uri={$redirectUri}&response_type=code&state={$state}";
            
            case 'alipay':
                return "https://openauth.alipay.com/oauth2/publicAppAuthorize.htm?app_id={$appId}&scope=auth_user&redirect_uri={$redirectUri}&state={$state}";
            
            case 'github':
                return "https://github.com/login/oauth/authorize?client_id={$appId}&redirect_uri={$redirectUri}&state={$state}&scope=user";
            
            case 'google':
                return "https://accounts.google.com/o/oauth2/v2/auth?client_id={$appId}&redirect_uri={$redirectUri}&response_type=code&scope=openid%20profile%20email&state={$state}";
            
            case 'gitee':
                return "https://gitee.com/oauth/authorize?client_id={$appId}&redirect_uri={$redirectUri}&response_type=code&state={$state}";
            
            case 'baidu':
                return "https://openapi.baidu.com/oauth/2.0/authorize?response_type=code&client_id={$appId}&redirect_uri={$redirectUri}&state={$state}&scope=basic";
            
            case 'douyin':
                return "https://open.douyin.com/platform/oauth/connect?client_key={$appId}&redirect_uri={$redirectUri}&response_type=code&scope=user_info&state={$state}";
            
            case 'microsoft':
                return "https://login.microsoftonline.com/common/oauth2/v2.0/authorize?client_id={$appId}&redirect_uri={$redirectUri}&response_type=code&scope=openid%20profile%20email%20User.Read&state={$state}";
            
            case 'xiaomi':
                return "https://account.xiaomi.com/oauth2/authorize?client_id={$appId}&redirect_uri={$redirectUri}&response_type=code&scope=profile&state={$state}";
            
            case 'dingtalk':
                return "https://login.dingtalk.com/oauth2/auth?client_id={$appId}&redirect_uri={$redirectUri}&response_type=code&scope=openid%20corpid&state={$state}&prompt=consent";
            
            case 'feishu':
                return "https://open.feishu.cn/open-apis/authen/v1/authorize?app_id={$appId}&redirect_uri={$redirectUri}&response_type=code&state={$state}";
            
            default:
                $this->jsonError(-1, 104, '未知登录方式(type)');
        }
    }
    
    /**
     * 加密state
     */
    protected function encodeState($type, $logId)
    {
        $data = $type . '||||' . $logId;
        return base64_encode(encrypt($data));
    }
    
    /**
     * 解密state
     */
    protected function decodeState($state)
    {
        try {
            $decoded = base64_decode($state);
            $decrypted = decrypt($decoded);
            $parts = explode('||||', $decrypted);
            
            if (count($parts) !== 2) {
                return null;
            }
            
            return [
                'type' => $parts[0],
                'log_id' => $parts[1],
            ];
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * 更新调用次数
     */
    protected function updateCallCount($app)
    {
        $today = date('Y-m-d');
        
        if ($app['last_call_date'] === $today) {
            $this->db->query(
                "UPDATE {$this->db->getPrefix()}apps SET today_calls = today_calls + 1, total_calls = total_calls + 1 WHERE id = ?",
                [$app['id']]
            );
        } else {
            $this->db->query(
                "UPDATE {$this->db->getPrefix()}apps SET today_calls = 1, total_calls = total_calls + 1, last_call_date = ? WHERE id = ?",
                [$today, $app['id']]
            );
        }
    }
    
    /**
     * 成功响应
     */
    protected function jsonSuccess($data)
    {
        echo json_encode(array_merge(['code' => 0, 'msg' => 'success'], $data), JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * 错误响应
     */
    protected function jsonError($code, $errcode, $msg)
    {
        echo json_encode(['code' => $code, 'errcode' => $errcode, 'msg' => $msg], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * 显示错误页面
     */
    protected function showError($message)
    {
        view('auth/oauth_error', ['error' => $message]);
        exit;
    }
    
    /**
     * 将gender数值转换为字符串
     * 0 -> unknown, 1 -> male, 2 -> female
     */
    protected function convertGenderToString($gender): string
    {
        switch ((int)$gender) {
            case 1:
                return 'male';
            case 2:
                return 'female';
            default:
                return 'unknown';
        }
    }
    
    /**
     * 将gender字符串转换为API响应格式
     * 保持与彩虹聚合登录API兼容
     */
    protected function convertGenderForApi($gender)
    {
        if (is_numeric($gender)) {
            return (int)$gender;
        }
        switch ($gender) {
            case 'male':
                return 1;
            case 'female':
                return 2;
            default:
                return 0;
        }
    }
}
