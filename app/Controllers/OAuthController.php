<?php

/**
 * OAuth第三方登录控制器
 */

namespace App\Controllers;

use App\Services\OAuthService;
use App\Services\WechatOfficialAccountService;

class OAuthController extends BaseController
{
    /**
     * 发起OAuth授权
     * GET /oauth/{platform}
     */
    public function authorize($platform)
    {
        $platformConfig = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}platforms WHERE name = ? AND status = 1",
            [$platform]
        );

        if (!$platformConfig) {
            $this->showError('该登录方式未启用');
            return;
        }

        if (empty($platformConfig['app_id']) || empty($platformConfig['app_secret'])) {
            $this->showError('该登录方式未配置');
            return;
        }

        if ($platform === 'wx' && $this->isWechatMpMode($platformConfig)) {
            $this->startWechatMpSiteLogin($platformConfig);
            return;
        }

        $settings = $this->getSettings();
        $baseUrl = rtrim($settings['site_url'] ?? '', '/');
        if (empty($baseUrl)) {
            $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
                . '://' . $_SERVER['HTTP_HOST'];
        }
        $redirectUri = $baseUrl . '/oauth/' . $platform . '/callback';

        try {
            $oauth = new OAuthService($platform, [
                'app_id' => $platformConfig['app_id'],
                'app_secret' => decrypt($platformConfig['app_secret']),
                'agent_id' => $platformConfig['scope'] ?? '', // 企业微信使用scope字段存储agentid
            ]);
            $state = md5(uniqid(mt_rand(), true));
            $_SESSION['oauth_state'] = $state;
            $_SESSION['oauth_platform'] = $platform;
            $isTest = isset($_GET['test']) && $_GET['test'] == '1';
            $_SESSION['oauth_test_mode'] = $isTest;
            $referer = $_GET['redirect'] ?? $_SERVER['HTTP_REFERER'] ?? '/';
            $_SESSION['oauth_redirect'] = $referer;

            $authUrl = $oauth->getAuthUrl($redirectUri, $state);

            header('Location: ' . $authUrl);
            exit;
        } catch (\Exception $e) {
            $this->showError('授权失败: ' . $e->getMessage());
        }
    }

    /**
     * 微信公众号订阅号站内登录状态
     */
    public function wechatMpStatus()
    {
        header('Content-Type: application/json; charset=UTF-8');

        $logId = (int)($_SESSION['wechat_mp_login_log_id'] ?? 0);
        if ($logId <= 0) {
            echo json_encode(['code' => -1, 'msg' => '登录会话已失效'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $log = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}oauth_logs WHERE id = ? AND app_id = ? LIMIT 1",
            [$logId, '__site__']
        );

        if (!$log) {
            echo json_encode(['code' => -1, 'msg' => '登录记录不存在'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (strtotime($log['time']) < time() - 600) {
            unset($_SESSION['wechat_mp_login_log_id']);
            echo json_encode(['code' => -1, 'msg' => '登录已过期，请重新扫码'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ((int)$log['status'] === 1 && !empty($log['open_id'])) {
            echo json_encode(['code' => 0, 'msg' => 'confirmed', 'redirect' => '/wechat/mp/finish'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        echo json_encode(['code' => 2, 'msg' => 'waiting'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * 完成微信公众号订阅号站内登录
     */
    public function wechatMpFinish()
    {
        $logId = (int)($_SESSION['wechat_mp_login_log_id'] ?? 0);
        if ($logId <= 0) {
            $this->showError('登录会话已失效，请重新登录');
            return;
        }

        $log = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}oauth_logs WHERE id = ? AND app_id = ? LIMIT 1",
            [$logId, '__site__']
        );

        if (!$log || (int)$log['status'] !== 1 || empty($log['open_id'])) {
            $this->showError('公众号登录尚未确认，请重新登录');
            return;
        }

        $platformConfig = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}platforms WHERE name = ? AND status = 1 LIMIT 1",
            ['wx']
        );

        if (!$platformConfig || !$this->isWechatMpMode($platformConfig)) {
            $this->showError('微信订阅号登录未启用');
            return;
        }

        $scopeConfig = WechatOfficialAccountService::parseScopeConfig($platformConfig['scope'] ?? '');
        $service = new WechatOfficialAccountService(
            $platformConfig['app_id'] ?? '',
            decrypt($platformConfig['app_secret'] ?? ''),
            $scopeConfig['mp_token'] ?? ''
        );
        $wechatUser = $service->getUserInfo($log['open_id']);
        $userInfo = [
            'platform' => 'wx',
            'open_id' => $log['open_id'],
            'union_id' => $wechatUser['unionid'] ?? '',
            'nickname' => $wechatUser['nickname'] ?? '微信用户',
            'avatar' => $wechatUser['headimgurl'] ?? '',
            'email' => '',
            'raw_data' => $wechatUser,
        ];

        unset($_SESSION['wechat_mp_login_log_id']);
        $result = $this->handleOAuthLogin($userInfo);
        if ($result['success']) {
            redirect($_SESSION['oauth_redirect'] ?? '/user/dashboard');
        }

        $this->showError($result['message'] ?? '登录失败');
    }

    /**
     * OAuth回调处理
     * GET /oauth/{platform}/callback
     */
    public function callback($platform)
    {
        $code = isset($_GET['code']) ? $_GET['code'] : (isset($_GET['auth_code']) ? $_GET['auth_code'] : '');
        $state = isset($_GET['state']) ? $_GET['state'] : '';
        $error = isset($_GET['error']) ? $_GET['error'] : (isset($_GET['error_description']) ? $_GET['error_description'] : '');
        if (!empty($error)) {
            $this->showError('授权失败: ' . $error);
            return;
        }
        $sessionState = isset($_SESSION['oauth_state']) ? $_SESSION['oauth_state'] : '';
        if (empty($state) || $state !== $sessionState) {
            $this->showError('授权验证失败，请重试');
            return;
        }

        if (empty($code)) {
            $this->showError('授权码无效');
            return;
        }
        $platformConfig = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}platforms WHERE name = ? AND status = 1",
            [$platform]
        );

        if (!$platformConfig) {
            $this->showError('该登录方式未启用');
            return;
        }
        $settings = $this->getSettings();
        $baseUrl = rtrim($settings['site_url'] ?? '', '/');
        if (empty($baseUrl)) {
            $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
                . '://' . $_SERVER['HTTP_HOST'];
        }
        $redirectUri = $baseUrl . '/oauth/' . $platform . '/callback';

        try {
            $decryptedSecret = decrypt($platformConfig['app_secret']);

            if (empty($decryptedSecret)) {
                $this->showError('平台配置错误，请联系管理员');
                return;
            }

            $oauth = new OAuthService($platform, [
                'app_id' => $platformConfig['app_id'],
                'app_secret' => $decryptedSecret,
                'agent_id' => $platformConfig['scope'] ?? '', // 企业微信使用scope字段存储agentid
            ]);
            $userInfo = $oauth->getUserByCode($code, $redirectUri);

            if (empty($userInfo['open_id'])) {
                $this->showError('获取用户信息失败');
                return;
            }
            $isTestMode = $_SESSION['oauth_test_mode'] ?? false;
            unset($_SESSION['oauth_test_mode']);

            if ($isTestMode) {
                unset($_SESSION['oauth_state'], $_SESSION['oauth_platform'], $_SESSION['oauth_redirect']);
                $this->showTestResult($platform, $userInfo);
                return;
            }
            $result = $this->handleOAuthLogin($userInfo);
            unset($_SESSION['oauth_state'], $_SESSION['oauth_platform']);
            $redirectUrl = $_SESSION['oauth_redirect'] ?? '/user/dashboard';
            unset($_SESSION['oauth_redirect']);

            if ($result['success']) {
                if (!empty($result['need_complete'])) {
                    redirect('/auth/complete-profile');
                } else {
                    redirect($redirectUrl);
                }
            } else {
                $this->showError($result['message']);
            }
        } catch (\Exception $e) {
            $this->showError('登录失败: ' . $e->getMessage());
        }
    }

    /**
     * 处理OAuth登录
     */
    /**
     * 处理OAuth登录
     */
    protected function handleOAuthLogin($userInfo)
    {
        $platform = $userInfo['platform'];
        $openId = $userInfo['open_id'];
        $binding = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}user_oauth WHERE platform = ? AND open_id = ?",
            [$platform, $openId]
        );

        if ($binding && !empty($binding['user'])) {
            $user = $this->db->fetch(
                "SELECT * FROM {$this->db->getPrefix()}users WHERE id = ? AND status = 'enable'",
                [$binding['user']]
            );

            if (!$user) {
                return ['success' => false, 'message' => '账号已被禁用或不存在'];
            }
            $this->db->update('user_oauth', [
                'nickname' => $userInfo['nickname'],
                'avatar' => $userInfo['avatar'],
                'data' => json_encode($userInfo['raw_data'], JSON_UNESCAPED_UNICODE),
            ], 'id = ?', [$binding['id']]);
            $this->loginUser($user, $platform);

            return ['success' => true, 'message' => '登录成功'];
        }
        if ($this->user) {
            if ($binding) {
                $this->db->update('user_oauth', [
                    'user' => $this->user['id'],
                    'nickname' => $userInfo['nickname'],
                    'avatar' => $userInfo['avatar'],
                    'data' => json_encode($userInfo['raw_data'], JSON_UNESCAPED_UNICODE),
                ], 'id = ?', [$binding['id']]);
            } else {
                $this->createBinding($this->user['id'], $userInfo);
            }
            return ['success' => true, 'message' => '绑定成功'];
        }
        if (!empty($userInfo['email'])) {
            $existingUser = $this->db->fetch(
                "SELECT * FROM {$this->db->getPrefix()}users WHERE email = ? AND status = 'enable'",
                [$userInfo['email']]
            );

            if ($existingUser) {
                if ($binding) {
                    $this->db->update('user_oauth', [
                        'user' => $existingUser['id'],
                        'nickname' => $userInfo['nickname'],
                        'avatar' => $userInfo['avatar'],
                        'data' => json_encode($userInfo['raw_data'], JSON_UNESCAPED_UNICODE),
                    ], 'id = ?', [$binding['id']]);
                } else {
                    $this->createBinding($existingUser['id'], $userInfo);
                }
                $this->loginUser($existingUser, $userInfo['platform']);
                return ['success' => true, 'message' => '登录成功'];
            }
        }
        if ($binding) {
            $this->db->update('user_oauth', [
                'nickname' => $userInfo['nickname'],
                'avatar' => $userInfo['avatar'],
                'data' => json_encode($userInfo['raw_data'], JSON_UNESCAPED_UNICODE),
            ], 'id = ?', [$binding['id']]);
            $_SESSION['oauth_binding_id'] = $binding['id'];
        } else {
            $bindingId = $this->createBinding(null, $userInfo);
            $_SESSION['oauth_binding_id'] = $bindingId;
        }
        redirect('/auth/bind');
    }

    /**
     * 创建绑定记录
     */
    protected function createBinding($userId, $userInfo)
    {
        $this->db->insert('user_oauth', [
            'user' => $userId,
            'platform' => $userInfo['platform'],
            'open_id' => $userInfo['open_id'],
            'union_id' => $userInfo['union_id'],
            'nickname' => $userInfo['nickname'],
            'avatar' => $userInfo['avatar'],
            'email' => $userInfo['email'],
            'data' => json_encode($userInfo['raw_data'], JSON_UNESCAPED_UNICODE),
        ]);

        return $this->db->lastInsertId();
    }

    /**
     * 创建待完善资料的用户 (已废弃，保留方法存根或直接删除)
     */
    protected function createPendingUser($userInfo)
    {
        return null;
    }

    /**
     * 登录用户
     */
    protected function loginUser($user, $platform = 'oauth')
    {
        if ($user['status'] !== 'enable') {
            throw new \Exception('账户已被禁用，无法登录');
        }
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $this->db->update('users', [
            'last_login_time' => date('Y-m-d H:i:s'),
            'last_login_ip' => get_client_ip(),
        ], 'id = ?', [$user['id']]);
        $this->recordLoginLog($user['id'], $platform);
    }

    /**
     * 显示错误页面
     */
    protected function showError($message)
    {
        $this->view('auth/oauth_error', [
            'message' => $message,
        ]);
    }

    /**
     * 显示测试结果页面
     */
    protected function showTestResult($platform, $userInfo)
    {
        $this->view('auth/oauth_test', [
            'platform' => $platform,
            'userInfo' => $userInfo,
        ]);
    }

    protected function isWechatMpMode($platformConfig)
    {
        if (($platformConfig['name'] ?? '') !== 'wx') {
            return false;
        }

        $scopeConfig = WechatOfficialAccountService::parseScopeConfig($platformConfig['scope'] ?? '');
        return ($scopeConfig['login_mode'] ?? '') === 'mp_subscribe';
    }

    protected function startWechatMpSiteLogin($platformConfig)
    {
        $referer = $_GET['redirect'] ?? $_SERVER['HTTP_REFERER'] ?? '/user/dashboard';
        $_SESSION['oauth_redirect'] = $referer;

        $code = strtoupper(md5(uniqid(mt_rand(), true)));
        $this->db->insert('oauth_logs', [
            'code' => $code,
            'app_id' => '__site__',
            'user' => 0,
            'type' => 'wx',
            'platform' => 'wx',
            'domain' => $_SERVER['HTTP_HOST'] ?? '',
            'redirect' => $referer,
            'state' => 'site_login',
            'status' => 0,
            'time' => date('Y-m-d H:i:s'),
        ]);

        $logId = $this->db->lastInsertId();
        $_SESSION['wechat_mp_login_log_id'] = $logId;

        $displayCode = substr($code, 0, 8);
        $scopeConfig = WechatOfficialAccountService::parseScopeConfig($platformConfig['scope'] ?? '');
        $service = new WechatOfficialAccountService(
            $platformConfig['app_id'] ?? '',
            decrypt($platformConfig['app_secret'] ?? ''),
            $scopeConfig['mp_token'] ?? ''
        );
        $qrUrl = $service->createLoginQrCode('MLLOGIN_' . $displayCode);

        $this->view('auth/wechat_mp_login', [
            'code' => $displayCode,
            'qrcode' => $qrUrl ?: '',
            'callbackUrl' => $this->getBaseUrl() . '/wechat/mp/callback',
            'statusUrl' => '/wechat/mp/status',
        ]);
    }

    protected function getBaseUrl()
    {
        $settings = $this->getSettings();
        $baseUrl = rtrim($settings['site_url'] ?? '', '/');
        if ($baseUrl !== '') {
            return $baseUrl;
        }

        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
            . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    }

    /**
     * 获取系统设置
     */
    protected function getSettings()
    {
        $row = $this->db->fetch("SELECT * FROM {$this->db->getPrefix()}settings WHERE id = 1");
        return $row ?: [];
    }
}
