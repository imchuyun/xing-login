<?php

namespace App\Controllers;

/**
 * 认证控制器
 */
class AuthController extends BaseController
{
    /**
     * 登录页面
     */
    public function loginPage()
    {
        if ($this->user) {
            redirect('/user/dashboard');
        }
        $enabledPlatforms = $this->db->fetchAll(
            "SELECT * FROM {$this->db->getPrefix()}platforms WHERE status = 1 ORDER BY id ASC"
        );
        $allPlatforms = $this->db->fetchAll(
            "SELECT * FROM {$this->db->getPrefix()}platforms ORDER BY id ASC"
        );

        $this->view('auth/login', [
            'csrf_token' => $this->generateCsrf(),
            'enabledPlatforms' => $enabledPlatforms,
            'allPlatforms' => $allPlatforms,
        ]);
    }

    /**
     * 登录处理
     */
    public function login()
    {
        $this->verifyCsrf();

        $username = trim($this->input('username', ''));
        $password = $this->input('password', '');

        if (empty($username) || empty($password)) {
            error('请输入用户名和密码');
        }
        $user = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}users WHERE (username = ? OR email = ? OR phone = ?)",
            [$username, $username, $username]
        );

        if (!$user || !password_verify($password, $user['password'])) {
            error('用户名或密码错误');
        }

        if ($user['status'] !== 'enable') {
            error('账户已被禁用');
        }
        $this->db->update('users', [
            'last_login_time' => date('Y-m-d H:i:s'),
            'last_login_ip' => get_client_ip(),
        ], 'id = ?', [$user['id']]);
        $this->recordLoginLog($user['id'], 'password');
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];

        success(['redirect' => '/user/dashboard'], '登录成功');
    }

    /**
     * 注册页面
     */
    public function registerPage()
    {
        if ($this->user) {
            redirect('/user/dashboard');
        }
        $settings = $this->db->fetch(
            "SELECT enable_register, register_verify_method FROM {$this->db->getPrefix()}settings WHERE id = 1"
        ) ?: [];
        
        $enableRegister = ($settings['enable_register'] ?? '1') == '1';
        $verifyMethod = $settings['register_verify_method'] ?? 'email';

        $this->view('auth/register', [
            'csrf_token' => $this->generateCsrf(),
            'verifyMethod' => $verifyMethod,
            'enableRegister' => $enableRegister,
        ]);
    }

    /**
     * 注册处理
     */
    public function register()
    {
        $this->verifyCsrf();
        $security = new \Core\Security();
        $securityCheck = $security->check();
        if (!$securityCheck['allowed']) {
            error($securityCheck['message']);
        }
        $settings = $this->db->fetch(
            "SELECT enable_register, register_verify_method FROM {$this->db->getPrefix()}settings WHERE id = 1"
        ) ?: [];
        if (($settings['enable_register'] ?? '1') != '1') {
            error('暂不开放注册');
        }
        $verifyMethod = $settings['register_verify_method'] ?? 'none';

        $username = trim($this->input('username', ''));
        $email = trim($this->input('email', ''));
        $phone = trim($this->input('phone', ''));
        $password = $this->input('password', '');
        $verifyCode = trim($this->input('verify_code', ''));
        if (empty($username) || strlen($username) < 3 || strlen($username) > 20) {
            error('用户名长度需在3-20个字符之间');
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            error('用户名只能包含字母、数字和下划线');
        }

        if (strlen($password) < 6) {
            error('密码长度至少6位');
        }
        if ($verifyMethod === 'phone') {
            if (empty($phone) || !preg_match('/^1[3-9]\d{9}$/', $phone)) {
                error('手机号格式不正确');
            }
            $cacheKey = "verify_code_register_{$phone}";
            $cached = $_SESSION[$cacheKey] ?? null;
            if (!$cached || $cached['code'] !== $verifyCode || $cached['expires'] < time()) {
                error('验证码错误或已过期');
            }
            unset($_SESSION[$cacheKey]);
            $exists = $this->db->fetch(
                "SELECT id FROM {$this->db->getPrefix()}users WHERE phone = ?",
                [$phone]
            );
            if ($exists) {
                error('手机号已被注册');
            }
            $exists = $this->db->fetch(
                "SELECT id FROM {$this->db->getPrefix()}users WHERE username = ?",
                [$username]
            );
            if ($exists) {
                error('用户名已存在');
            }
            $userId = $this->db->insert('users', [
                'username' => $username,
                'phone' => $phone,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'status' => 'enable',
                'role' => 'user',
            ]);
        } elseif ($verifyMethod === 'email') {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                error('邮箱格式不正确');
            }
            $emailCheck = $security->checkEmail($email);
            if (!$emailCheck['allowed']) {
                error($emailCheck['message']);
            }
            $cacheKey = "verify_code_register_{$email}";
            $cached = $_SESSION[$cacheKey] ?? null;
            if (!$cached || $cached['code'] !== $verifyCode || $cached['expires'] < time()) {
                error('验证码错误或已过期');
            }
            unset($_SESSION[$cacheKey]);
            $exists = $this->db->fetch(
                "SELECT id FROM {$this->db->getPrefix()}users WHERE username = ? OR email = ?",
                [$username, $email]
            );
            if ($exists) {
                error('用户名或邮箱已存在');
            }
            $userId = $this->db->insert('users', [
                'username' => $username,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'status' => 'enable',
                'role' => 'user',
            ]);
        } else {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                error('邮箱格式不正确');
            }
            $emailCheck = $security->checkEmail($email);
            if (!$emailCheck['allowed']) {
                error($emailCheck['message']);
            }
            $exists = $this->db->fetch(
                "SELECT id FROM {$this->db->getPrefix()}users WHERE username = ? OR email = ?",
                [$username, $email]
            );
            if ($exists) {
                error('用户名或邮箱已存在');
            }
            $userId = $this->db->insert('users', [
                'username' => $username,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'status' => 'enable',
                'role' => 'user',
            ]);
        }
        $_SESSION['user_id'] = $userId;

        success(['redirect' => '/user/dashboard'], '注册成功');
    }

    /**
     * 绑定账号页面
     */
    public function bindPage()
    {
        if (empty($_SESSION['oauth_binding_id'])) {
            redirect('/user/login');
        }
        $binding = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}user_oauth WHERE id = ?",
            [$_SESSION['oauth_binding_id']]
        );

        if (!$binding || !empty($binding['user'])) {
            unset($_SESSION['oauth_binding_id']);
            redirect('/user/login');
        }
        $settings = $this->db->fetch(
            "SELECT register_verify_method FROM {$this->db->getPrefix()}settings WHERE id = 1"
        ) ?: [];
        $verifyMethod = $settings['register_verify_method'] ?? 'email';

        $this->view('auth/bind', [
            'csrf_token' => $this->generateCsrf(),
            'binding' => $binding,
            'verifyMethod' => $verifyMethod,
        ]);
    }

    /**
     * 绑定账号处理
     */
    public function bindAccount()
    {
        $this->verifyCsrf();

        if (empty($_SESSION['oauth_binding_id'])) {
            error('会话已过期，请重新登录');
        }

        $bindingId = $_SESSION['oauth_binding_id'];
        $type = $this->input('type'); // 'new' or 'existing'

        if ($type === 'new') {
            $this->handleNewUserBind($bindingId);
        } elseif ($type === 'existing') {
            $this->handleExistingUserBind($bindingId);
        } else {
            error('无效的操作类型');
        }
    }

    /**
     * 处理新用户绑定
     */
    protected function handleNewUserBind($bindingId)
    {
        $settings = $this->db->fetch(
            "SELECT enable_register, register_verify_method FROM {$this->db->getPrefix()}settings WHERE id = 1"
        ) ?: [];
        if (($settings['enable_register'] ?? '1') != '1') {
            error('暂不开放注册');
        }

        $username = trim($this->input('username', ''));
        $email = trim($this->input('email', ''));
        $password = $this->input('password', '');
        $verifyCode = trim($this->input('verify_code', ''));
        if (empty($username) || strlen($username) < 3 || strlen($username) > 20) {
            error('用户名长度需在3-20个字符之间');
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            error('用户名只能包含字母、数字和下划线');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            error('邮箱格式不正确');
        }

        if (strlen($password) < 6) {
            error('密码长度至少6位');
        }
        $method = $settings['register_verify_method'] ?? 'email';

        if ($method === 'email') {
            $cacheKey = "verify_code_register_{$email}";
            $cached = $_SESSION[$cacheKey] ?? null;
            if (!$cached || $cached['code'] !== $verifyCode || $cached['expires'] < time()) {
                error('验证码错误或已过期');
            }
            unset($_SESSION[$cacheKey]);
        }
        $exists = $this->db->fetch(
            "SELECT id FROM {$this->db->getPrefix()}users WHERE username = ? OR email = ?",
            [$username, $email]
        );

        if ($exists) {
            error('用户名或邮箱已存在');
        }
        $userId = $this->db->insert('users', [
            'username' => $username,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'status' => 'enable',
            'role' => 'user',
        ]);
        $this->db->update('user_oauth', [
            'user' => $userId
        ], 'id = ?', [$bindingId]);
        $_SESSION['user_id'] = $userId;
        unset($_SESSION['oauth_binding_id']);

        success(['redirect' => '/user/dashboard'], '注册并绑定成功');
    }

    /**
     * 处理已有用户绑定
     */
    protected function handleExistingUserBind($bindingId)
    {
        $account = trim($this->input('account', ''));
        $password = $this->input('password', '');

        if (empty($account) || empty($password)) {
            error('请输入账号和密码');
        }
        $user = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}users WHERE (username = ? OR email = ?) AND role = 'user'",
            [$account, $account]
        );

        if (!$user || !password_verify($password, $user['password'])) {
            error('账号或密码错误');
        }

        if ($user['status'] !== 'enable') {
            error('账户已被禁用');
        }
        $binding = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}user_oauth WHERE id = ?",
            [$bindingId]
        );
        $existingBinding = $this->db->fetch(
            "SELECT id FROM {$this->db->getPrefix()}user_oauth WHERE user = ? AND platform = ?",
            [$user['id'], $binding['platform']]
        );

        if ($existingBinding) {
            error('该账号已绑定其他' . $binding['platform'] . '账号');
        }
        $this->db->update('user_oauth', [
            'user' => $user['id']
        ], 'id = ?', [$bindingId]);
        $_SESSION['user_id'] = $user['id'];
        $this->db->update('users', [
            'last_login_time' => date('Y-m-d H:i:s'),
            'last_login_ip' => get_client_ip(),
        ], 'id = ?', [$user['id']]);
        $this->recordLoginLog($user['id'], $binding['platform']);

        unset($_SESSION['oauth_binding_id']);

        success(['redirect' => '/user/dashboard'], '绑定成功');
    }

    /**
     * 发送验证码
     */
    public function sendVerifyCode()
    {
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $type = $input['type'] ?? '';
        $target = trim($input['target'] ?? '');
        $scene = $input['scene'] ?? 'register';

        if (empty($target)) {
            error('请输入目标地址');
        }
        $security = new \Core\Security();
        $securityCheck = $security->check();
        if (!$securityCheck['allowed']) {
            error($securityCheck['message']);
        }
        $cacheKey = "verify_code_{$type}_{$target}";
        if (isset($_SESSION[$cacheKey]) && $_SESSION[$cacheKey] > time() - 60) {
            error('发送太频繁，请稍后再试');
        }
        $code = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $config = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}settings WHERE id = 1"
        ) ?: [];

        $result = ['success' => false, 'message' => '发送失败'];

        if ($type === 'email') {
            if (!filter_var($target, FILTER_VALIDATE_EMAIL)) {
                error('邮箱格式不正确');
            }
            $emailCheck = $security->checkEmail($target);
            if (!$emailCheck['allowed']) {
                error($emailCheck['message']);
            }
            $siteName = $config['site_name'] ?? 'Max Login';

            $mailer = new \Core\Mailer([
                'host' => $config['smtp_host'] ?? '',
                'port' => $config['smtp_port'] ?? 465,
                'username' => $config['smtp_username'] ?? '',
                'password' => $config['smtp_password'] ?? '',
                'encryption' => $config['smtp_encryption'] ?? 'ssl',
                'from_name' => $config['smtp_from_name'] ?? 'MAXLOGIN',
                'site_name' => $siteName,
            ]);
            $result = $mailer->sendCode($target, $code, $scene);
        } elseif ($type === 'phone') {
            if (!preg_match('/^1[3-9]\d{9}$/', $target)) {
                error('手机号格式不正确');
            }

            $provider = $config['sms_provider'] ?? '';
            if (empty($provider)) {
                error('短信服务未配置');
            }

            $smsConfig = [];
            if ($provider === 'tencent') {
                $smsConfig = [
                    'secret_id' => $config['sms_tencent_secret_id'] ?? '',
                    'secret_key' => $config['sms_tencent_secret_key'] ?? '',
                    'sdk_app_id' => $config['sms_tencent_sdk_app_id'] ?? '',
                    'sign_name' => $config['sms_tencent_sign_name'] ?? '',
                    'template_id' => $config['sms_tencent_template_id'] ?? '',
                ];
            } elseif ($provider === 'aliyun') {
                $smsConfig = [
                    'access_key_id' => $config['sms_aliyun_access_key_id'] ?? '',
                    'access_key_secret' => $config['sms_aliyun_access_key_secret'] ?? '',
                    'sign_name' => $config['sms_aliyun_sign_name'] ?? '',
                    'template_code' => $config['sms_aliyun_template_code'] ?? '',
                ];
            }

            $sms = new \Core\Sms($provider, $smsConfig);
            $result = $sms->sendCode($target, $code, $scene);
        } else {
            error('未知的验证类型');
        }

        if ($result['success']) {
            $_SESSION["verify_code_{$scene}_{$target}"] = [
                'code' => $code,
                'expires' => time() + 300, // 5分钟有效
            ];
            $_SESSION[$cacheKey] = time();
            success(null, '验证码已发送');
        } else {
            error($result['message'] ?? '发送失败，请检查配置');
        }
    }

    /**
     * 退出登录
     */
    public function logout()
    {
        session_destroy();
        redirect('/user/login');
    }

    /**
     * 找回密码页面
     */
    public function findPwdPage()
    {
        $this->view('auth/findpwd', [
            'csrf_token' => $this->generateCsrf(),
        ]);
    }

    /**
     * 找回密码处理
     */
    public function findPwd()
    {
        $this->verifyCsrf();

        $email = trim($this->input('email', ''));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            error('邮箱格式不正确');
        }

        $user = $this->db->fetch(
            "SELECT id FROM {$this->db->getPrefix()}users WHERE email = ?",
            [$email]
        );
        success(null, '如果邮箱存在，重置链接已发送到您的邮箱');
    }

    /**
     * 完善资料页面
     */
    public function completeProfilePage()
    {
        if (empty($_SESSION['user_id'])) {
            redirect('/user/login');
        }
        $user = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}users WHERE id = ?",
            [$_SESSION['user_id']]
        );
        if ($user && strpos($user['username'], 'oauth_') !== 0 && !empty($user['password'])) {
            redirect('/user/dashboard');
        }
        $oauthInfo = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}user_oauth WHERE user = ? ORDER BY id DESC LIMIT 1",
            [$_SESSION['user_id']]
        );
        $settings = $this->db->fetch(
            "SELECT register_verify_method FROM {$this->db->getPrefix()}settings WHERE id = 1"
        ) ?: [];
        $verifyMethod = $settings['register_verify_method'] ?? 'none';

        $this->view('auth/complete_profile', [
            'csrf_token' => $this->generateCsrf(),
            'oauthInfo' => $oauthInfo,
            'verifyMethod' => $verifyMethod,
        ]);
    }

    /**
     * 完善资料处理
     */
    public function completeProfile()
    {
        $this->verifyCsrf();
        if (empty($_SESSION['user_id'])) {
            error('请先登录');
        }

        $userId = $_SESSION['user_id'];
        $user = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}users WHERE id = ?",
            [$userId]
        );

        if (!$user) {
            error('用户不存在');
        }
        if (strpos($user['username'], 'oauth_') !== 0 && !empty($user['password'])) {
            error('资料已完善');
        }

        $username = trim($this->input('username', ''));
        $password = $this->input('password', '');
        $email = trim($this->input('email', ''));
        $phone = trim($this->input('phone', ''));
        $verifyCode = trim($this->input('verify_code', ''));
        if (empty($username) || !preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
            error('用户名格式不正确（3-20个字符，字母数字下划线）');
        }
        $exists = $this->db->fetch(
            "SELECT id FROM {$this->db->getPrefix()}users WHERE username = ? AND id != ?",
            [$username, $userId]
        );
        if ($exists) {
            error('用户名已被使用');
        }
        if (empty($password) || strlen($password) < 6) {
            error('密码至少6位');
        }
        $settings = $this->db->fetch(
            "SELECT register_verify_method FROM {$this->db->getPrefix()}settings WHERE id = 1"
        ) ?: [];
        $verifyMethod = $settings['register_verify_method'] ?? 'none';

        $updateData = [
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
        ];

        if ($verifyMethod === 'phone') {
            if (empty($phone) || !preg_match('/^1[3-9]\d{9}$/', $phone)) {
                error('手机号格式不正确');
            }
            $cacheKey = "verify_code_complete_profile_{$phone}";
            $cached = $_SESSION[$cacheKey] ?? null;
            if (!$cached || $cached['code'] !== $verifyCode || $cached['expires'] < time()) {
                error('验证码错误或已过期');
            }
            unset($_SESSION[$cacheKey]);
            $exists = $this->db->fetch(
                "SELECT id FROM {$this->db->getPrefix()}users WHERE phone = ? AND id != ?",
                [$phone, $userId]
            );
            if ($exists) {
                error('手机号已被使用');
            }

            $updateData['phone'] = $phone;
        } elseif ($verifyMethod === 'email') {
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                error('邮箱格式不正确');
            }
            $cacheKey = "verify_code_complete_profile_{$email}";
            $cached = $_SESSION[$cacheKey] ?? null;
            if (!$cached || $cached['code'] !== $verifyCode || $cached['expires'] < time()) {
                error('验证码错误或已过期');
            }
            unset($_SESSION[$cacheKey]);
            $exists = $this->db->fetch(
                "SELECT id FROM {$this->db->getPrefix()}users WHERE email = ? AND id != ?",
                [$email, $userId]
            );
            if ($exists) {
                error('邮箱已被使用');
            }

            $updateData['email'] = $email;
        } else {
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                error('邮箱格式不正确');
            }
            $exists = $this->db->fetch(
                "SELECT id FROM {$this->db->getPrefix()}users WHERE email = ? AND id != ?",
                [$email, $userId]
            );
            if ($exists) {
                error('邮箱已被使用');
            }

            $updateData['email'] = $email;
        }
        $this->db->update('users', $updateData, 'id = ?', [$userId]);

        success(['redirect' => '/user/dashboard'], '资料完善成功');
    }
}
