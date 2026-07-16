<?php
namespace App\Controllers;

use App\Services\WechatOfficialAccountService;

/**
 * 管理控制器
 */
class AdminController extends BaseController
{
    /**
     * 管理员登录页面
     */
    public function loginPage()
    {
        if (isset($_SESSION['admin_id'])) {
            redirect(admin_url());
        }

        $this->view('admin/login', [
            'csrf_token' => $this->generateCsrf(),
        ]);
    }

    /**
     * 管理员登录处理
     */
    public function login()
    {
        try {
            $this->verifyCsrf();

            $username = trim($this->input('username', ''));
            $password = $this->input('password', '');

            if (empty($username) || empty($password)) {
                error('请输入用户名和密码');
            }
            $user = $this->db->fetch(
                "SELECT * FROM {$this->db->getPrefix()}users WHERE (username = ? OR email = ?) AND role = 'admin'",
                [$username, $username]
            );

            if (!$user) {
                error('管理员账户不存在');
            }
            
            if (!password_verify($password, $user['password'])) {
                error('密码错误');
            }

            if ($user['status'] !== 'enable') {
                error('账户已被禁用');
            }
            $this->db->update('users', [
                'last_login_time' => date('Y-m-d H:i:s'),
                'last_login_ip' => get_client_ip(),
            ], 'id = ?', [$user['id']]);
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $user['id'];

            success(['redirect' => admin_url()], '登录成功');
        } catch (\Exception $e) {
            error('登录失败: ' . $e->getMessage());
        }
    }

    /**
     * 管理员退出
     */
    public function logout()
    {
        unset($_SESSION['admin_id']);
        redirect(admin_url('login'));
    }

    /**
     * 管理后台首页
     */
    public function dashboard()
    {
        $userCount = $this->db->fetch(
            "SELECT COUNT(*) as count FROM {$this->db->getPrefix()}users"
        )['count'];

        $appCount = $this->db->fetch(
            "SELECT COUNT(*) as count FROM {$this->db->getPrefix()}apps"
        )['count'];

        $todayLogins = $this->db->fetch(
            "SELECT COUNT(*) as count FROM {$this->db->getPrefix()}oauth_logs WHERE DATE(`time`) = CURDATE() AND status = 1"
        )['count'];

        $totalLogins = $this->db->fetch(
            "SELECT COUNT(*) as count FROM {$this->db->getPrefix()}oauth_logs WHERE status = 1"
        )['count'];
        $recentUsers = $this->db->fetchAll(
            "SELECT id, username, email, `time` FROM {$this->db->getPrefix()}users ORDER BY `time` DESC LIMIT 5"
        );
        $recentLogs = $this->db->fetchAll(
            "SELECT l.*, a.app_name, u.nickname, u.avatar 
             FROM {$this->db->getPrefix()}oauth_logs l 
             LEFT JOIN {$this->db->getPrefix()}apps a ON l.app_id = a.app_id 
             LEFT JOIN {$this->db->getPrefix()}oauth_users u ON l.app_id = u.app_id AND l.type = u.type AND l.open_id = u.open_id
             WHERE l.status = 1
             ORDER BY l.`time` DESC LIMIT 10"
        );

        $this->view('admin/dashboard', [
            'userCount' => $userCount,
            'appCount' => $appCount,
            'todayLogins' => $todayLogins,
            'totalLogins' => $totalLogins,
            'recentUsers' => $recentUsers,
            'recentLogs' => $recentLogs,
            'csrf_token' => $this->generateCsrf(),
        ]);
    }

    /**
     * 用户管理
     */
    public function users()
    {
        $page = max(1, (int) $this->input('page', 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;
        $prefix = $this->db->getPrefix();

        $total = $this->db->fetch(
            "SELECT COUNT(*) as count FROM {$prefix}users"
        )['count'];
        $users = $this->db->fetchAll(
            "SELECT u.*, v.status as verify_status, v.type as verify_type, v.name, v.id_card, v.id_card_front, v.id_card_back,
                    v.company, v.unified_social_credit_code, v.license,
                    p.product_name as package_name, p.type as package_type, p.expire_time as package_expire_time, p.status as package_status
             FROM {$prefix}users u
             LEFT JOIN {$prefix}user_verifications v ON u.id = v.`user` AND v.id = (
                SELECT MAX(id) FROM {$prefix}user_verifications WHERE `user` = u.id
             )
             LEFT JOIN {$prefix}user_packages p ON u.id = p.`user` AND p.status = 1 AND p.expire_time > NOW()
             ORDER BY u.id ASC LIMIT {$limit} OFFSET {$offset}"
        );
        $products = $this->db->fetchAll(
            "SELECT id, name, type, duration, platforms, total_quota, account_limit 
             FROM {$prefix}products 
             WHERE status = 1 
             ORDER BY sort ASC, id ASC"
        );

        $this->view('admin/users', [
            'users' => $users,
            'products' => $products,
            'total' => $total,
            'page' => $page,
            'totalPages' => ceil($total / $limit),
            'csrf_token' => $this->generateCsrf(),
        ]);
    }

    /**
     * 更新用户
     */
    public function updateUser()
    {
        $this->verifyCsrf();

        $id = (int) $this->input('id', 0);
        $email = trim($this->input('email', ''));
        $password = $this->input('password', '');
        $status = $this->input('status', 'enable');
        $role = $this->input('role', 'user');

        if ($id <= 0) {
            error('参数错误');
        }

        if (!in_array($role, ['user', 'admin'])) {
            $role = 'user';
        }

        if (!in_array($status, ['enable', 'disable'])) {
            $status = 'enable';
        }
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            error('邮箱格式不正确');
        }
        if (!empty($email)) {
            $existingUser = $this->db->fetch(
                "SELECT id FROM {$this->db->getPrefix()}users WHERE email = ? AND id != ?",
                [$email, $id]
            );
            if ($existingUser) {
                error('该邮箱已被其他用户使用');
            }
        }

        $updateData = [
            'status' => $status,
            'role' => $role,
        ];
        if (!empty($email)) {
            $updateData['email'] = $email;
        }
        if (!empty($password)) {
            if (strlen($password) < 6) {
                error('密码长度不能少于6位');
            }
            $updateData['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $this->db->update('users', $updateData, 'id = ?', [$id]);

        success(null, '用户更新成功');
    }

    /**
     * 平台管理
     */
    public function platforms()
    {
        $platforms = $this->db->fetchAll(
            "SELECT * FROM {$this->db->getPrefix()}platforms ORDER BY sort"
        );

        $this->view('admin/platforms', [
            'platforms' => $platforms,
            'csrf_token' => $this->generateCsrf(),
        ]);
    }

    /**
     * 更新平台配置
     */
    public function updatePlatform()
    {
        $this->verifyCsrf();

        $id = (int) $this->input('id', 0);
        $appId = trim($this->input('app_id', ''));
        $appSecret = trim($this->input('app_secret', ''));
        $scope = trim($this->input('scope', ''));
        $wxLoginMode = trim($this->input('wx_login_mode', ''));
        $wechatMpToken = trim($this->input('wechat_mp_token', ''));
        $status = (int) $this->input('status', 1);

        $platform = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}platforms WHERE id = ?",
            [$id]
        );

        if (!$platform) {
            error('平台不存在');
        }

        $updateData = [
            'status' => $status,
        ];

        if (!empty($appId)) {
            $updateData['app_id'] = $appId;
        }

        if (!empty($appSecret)) {
            $updateData['app_secret'] = encrypt($appSecret);
        }

        if ($platform['name'] === 'wx' && $wxLoginMode !== '') {
            $updateData['scope'] = WechatOfficialAccountService::buildScopeConfig($wxLoginMode, $wechatMpToken);
        } elseif ($scope !== '' || isset($_POST['scope'])) {
            // 允许scope为空（用于清除配置）
            $updateData['scope'] = $scope;
        }

        $this->db->update('platforms', $updateData, 'id = ?', [$id]);

        success(null, '平台配置更新成功');
    }

    /**
     * 接入文档 - 外部系统如何接入本平台
     */
    public function integrationDocs()
    {
        $this->view('admin/integration_docs', [
            'pageTitle' => '接入文档',
        ]);
    }

    /**
     * 平台配置文档 - 管理员如何配置第三方平台
     */
    public function platformDocs()
    {
        $settings = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}settings WHERE id = 1"
        ) ?: [];
        
        $this->view('admin/platform_docs', [
            'pageTitle' => '平台配置指南',
            'settings' => $settings,
        ]);
    }

    /**
     * 支付配置文档
     */
    public function paymentDocs()
    {
        $this->view('admin/payment_docs', [
            'pageTitle' => '支付配置指南',
        ]);
    }

    /**
     * 产品管理
     */
    public function products()
    {
        $products = $this->db->fetchAll(
            "SELECT * FROM {$this->db->getPrefix()}products ORDER BY sort ASC, id ASC"
        );
        $platforms = $this->db->fetchAll(
            "SELECT name, platform FROM {$this->db->getPrefix()}platforms ORDER BY sort"
        );

        $this->view('admin/products', [
            'products' => $products,
            'platforms' => $platforms,
            'csrf_token' => $this->generateCsrf(),
        ]);
    }

    /**
     * 保存产品
     */
    public function saveProduct()
    {
        $this->verifyCsrf();

        $id = (int) $this->input('id', 0);
        $name = trim($this->input('name', ''));
        $type = $this->input('type', 'package');
        $cycle = $this->input('billing_cycle', 'monthly');
        $price = (float) $this->input('price', 0);
        $originalPrice = $this->input('original_price', '');
        $platforms = $this->input('platforms', []);
        $dailyLimit = $this->input('daily_limit', '');
        $totalQuota = $this->input('total_quota', '');
        $accountLimit = $this->input('account_limit', '');
        $duration = (int) $this->input('duration', 30);
        $features = $this->input('features', '');
        $description = trim($this->input('description', ''));
        $status = (int) $this->input('status', 1);
        $recommend = (int) $this->input('is_recommended', 0);
        $sort = (int) $this->input('sort', 0);

        if (empty($name)) {
            error('请输入产品名称');
        }

        $data = [
            'name' => $name,
            'type' => $type,
            'cycle' => $cycle,
            'price' => $price,
            'original_price' => $originalPrice !== '' ? (float) $originalPrice : null,
            'platforms' => !empty($platforms) ? json_encode($platforms) : null,
            'daily_limit' => $dailyLimit !== '' ? (int) $dailyLimit : null,
            'total_quota' => $totalQuota !== '' ? (int) $totalQuota : null,
            'account_limit' => $accountLimit !== '' ? (int) $accountLimit : null,
            'duration' => $duration,
            'features' => !empty($features) ? json_encode(array_filter(array_map('trim', explode("\n", $features)))) : null,
            'description' => $description,
            'status' => $status,
            'recommend' => $recommend,
            'sort' => $sort,
        ];

        if ($id > 0) {
            $this->db->update('products', $data, 'id = ?', [$id]);
            success(null, '产品更新成功');
        } else {
            $this->db->insert('products', $data);
            success(null, '产品创建成功');
        }
    }

    /**
     * 删除产品
     */
    public function deleteProduct()
    {
        $this->verifyCsrf();

        $id = (int) $this->input('id', 0);

        if ($id <= 0) {
            error('参数错误');
        }

        $this->db->delete('products', 'id = ?', [$id]);

        success(null, '产品删除成功');
    }

    /**
     * 通知配置
     */
    public function notify()
    {
        $settings = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}settings WHERE id = 1"
        ) ?: [];

        $this->view('admin/settings/notify', [
            'settings' => $settings,
            'csrf_token' => $this->generateCsrf(),
        ]);
    }

    /**
     * 更新通知配置
     */
    public function updateNotify()
    {
        $this->verifyCsrf();

        // 直接从 $_POST 获取 settings 数组
        $settings = $_POST['settings'] ?? [];

        if (!is_array($settings)) {
            error('参数错误');
        }
        $allowedKeys = [
            'smtp_host', 'smtp_port', 'smtp_encryption', 'smtp_username', 'smtp_password', 'smtp_from_name',
            'sms_provider', 'sms_aliyun_access_key_id', 'sms_aliyun_access_key_secret',
            'sms_aliyun_sign_name', 'sms_aliyun_template_code', 'sms_aliyun_template_content',
            'sms_tencent_secret_id', 'sms_tencent_secret_key', 'sms_tencent_sdk_app_id',
            'sms_tencent_sign_name', 'sms_tencent_template_id', 'sms_tencent_template_content',
        ];
        
        $updateData = [];
        foreach ($settings as $key => $value) {
            if (in_array($key, $allowedKeys)) {
                $updateData[$key] = $value;
            }
        }
        
        if (!empty($updateData)) {
            $this->db->update('settings', $updateData, 'id = 1');
        }

        success(null, '通知配置更新成功');
    }

    /**
     * 测试通知发送
     */
    public function testNotify()
    {
        $this->verifyCsrf();

        $type = $this->input('type', '');
        $target = trim($this->input('target', ''));

        if (empty($target)) {
            error('请输入测试目标');
        }
        $config = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}settings WHERE id = 1"
        ) ?: [];

        $code = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $siteName = $config['site_name'] ?? 'Max Login';

        if ($type === 'email') {
            $mailer = new \Core\Mailer([
                'host' => $config['smtp_host'] ?? '',
                'port' => $config['smtp_port'] ?? 465,
                'username' => $config['smtp_username'] ?? '',
                'password' => $config['smtp_password'] ?? '',
                'encryption' => $config['smtp_encryption'] ?? 'ssl',
                'from_name' => $config['smtp_from_name'] ?? 'MAXLOGIN',
                'site_name' => $siteName,
            ]);
            $result = $mailer->sendCode($target, $code, 'register');
        } elseif ($type === 'sms') {
            $provider = $config['sms_provider'] ?? '';
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
            $result = $sms->sendCode($target, $code, 'register');
        } else {
            error('未知的测试类型');
        }

        if ($result['success']) {
            success(['code' => $code], '测试验证码发送成功');
        } else {
            error($result['message']);
        }
    }

    /**
     * 支付配置
     */
    public function payment()
    {
        $settings = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}settings WHERE id = 1"
        ) ?: [];

        $this->view('admin/settings/payment', [
            'settings' => $settings,
            'csrf_token' => $this->generateCsrf(),
        ]);
    }

    /**
     * 更新支付配置
     */
    public function updatePayment()
    {
        $this->verifyCsrf();

        // 直接从 $_POST 获取 settings 数组
        $settings = $_POST['settings'] ?? [];

        if (!is_array($settings)) {
            error('参数错误');
        }
        $allowedKeys = [
            'pay_epay_enabled', 'pay_epay_api_url', 'pay_epay_pid', 'pay_epay_key',
            'pay_alipay_enabled', 'pay_alipay_channel', 'pay_alipay_app_id', 'pay_alipay_private_key', 'pay_alipay_public_key',
            'pay_wechat_enabled', 'pay_wechat_channel', 'pay_wechat_app_id', 'pay_wechat_mch_id', 'pay_wechat_api_key',
            'pay_qqpay_enabled', 'pay_qqpay_channel', 'pay_qqpay_mch_id', 'pay_qqpay_api_key',
        ];
        
        $updateData = [];
        foreach ($settings as $key => $value) {
            if (in_array($key, $allowedKeys)) {
                $updateData[$key] = $value;
            }
        }
        
        if (!empty($updateData)) {
            $this->db->update('settings', $updateData, 'id = 1');
        }

        success(null, '支付配置更新成功');
    }

    /**
     * 安全访问配置
     */
    public function security()
    {
        $settings = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}settings WHERE id = 1"
        ) ?: [];

        $this->view('admin/settings/security', [
            'settings' => $settings,
            'csrf_token' => $this->generateCsrf(),
        ]);
    }

    /**
     * 更新安全访问配置
     */
    public function updateSecurity()
    {
        $this->verifyCsrf();

        // 直接从 $_POST 获取 settings 数组
        $settings = $_POST['settings'] ?? [];

        if (!is_array($settings)) {
            error('参数错误');
        }
        $allowedKeys = [
            'security_ip_whitelist', 'security_ip_blacklist',
            'security_email_mode', 'security_email_list',
            'security_region_enabled', 'security_region_mode', 'security_region_list',
        ];
        
        $updateData = [];
        foreach ($settings as $key => $value) {
            if (in_array($key, $allowedKeys)) {
                $updateData[$key] = $value;
            }
        }
        
        if (!empty($updateData)) {
            $this->db->update('settings', $updateData, 'id = 1');
        }

        success(null, '安全配置更新成功');
    }

    /**
     * 身份认证开发文档
     */
    public function verificationDocs()
    {
        $this->view('admin/settings/verification_docs');
    }

    /**
     * 订单管理
     */
    public function orders()
    {
        $page = max(1, (int) $this->input('page', 1));
        $status = $this->input('status', '');
        $limit = 50;
        $offset = ($page - 1) * $limit;

        $where = '1=1';
        $params = [];

        if ($status !== '') {
            $where .= ' AND o.status = ?';
            $params[] = (int) $status;
        }

        $total = $this->db->fetch(
            "SELECT COUNT(*) as count FROM {$this->db->getPrefix()}orders o WHERE {$where}",
            $params
        )['count'];

        $orders = $this->db->fetchAll(
            "SELECT o.*, u.username FROM {$this->db->getPrefix()}orders o
             LEFT JOIN {$this->db->getPrefix()}users u ON o.`user` = u.id
             WHERE {$where} ORDER BY o.`time` DESC LIMIT {$limit} OFFSET {$offset}",
            $params
        );

        $this->view('admin/orders', [
            'orders' => $orders,
            'total' => $total,
            'page' => $page,
            'totalPages' => ceil($total / $limit),
            'filter' => ['status' => $status],
            'csrf_token' => $this->generateCsrf(),
        ]);
    }

    /**
     * 系统设置页面 (入口)
     */
    public function settingsPage()
    {
        redirect(admin_url('settings/site'));
    }

    /**
     * 网站信息设置
     */
    public function siteSettings()
    {
        $settings = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}settings WHERE id = 1"
        ) ?: [];

        $this->view('admin/settings/site', [
            'settings' => $settings,
            'csrf_token' => $this->generateCsrf(),
        ]);
    }

    /**
     * 更新网站信息
     */
    public function updateSiteSettings()
    {
        $this->verifyCsrf();

        // 直接从 $_POST 获取 settings 数组
        $settings = $_POST['settings'] ?? [];

        if (!is_array($settings)) {
            error('参数错误');
        }
        $allowedKeys = [
            'site_name', 'site_url', 'site_description', 'site_keywords', 'site_icp',
            'site_logo', 'site_favicon',
            'admin_email', 'service_url', 'homepage_redirect',
        ];
        
        $updateData = [];
        foreach ($settings as $key => $value) {
            if (in_array($key, $allowedKeys)) {
                $updateData[$key] = $value;
            }
        }
        
        if (!empty($updateData)) {
            $this->db->update('settings', $updateData, 'id = 1');
        }

        success(null, '网站信息更新成功');
    }

    /**
     * 上传站点图片（Logo/Favicon）
     * Logo 覆盖 /assets/logo.png，Favicon 覆盖 /assets/favicon.ico
     */
    public function uploadImage()
    {
        $this->verifyCsrf();

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            error('请选择要上传的文件');
        }

        $file = $_FILES['file'];
        $type = $this->input('type', 'logo'); // logo 或 favicon
        if ($type === 'favicon') {
            $allowedTypes = ['image/x-icon', 'image/vnd.microsoft.icon', 'image/png', 'image/gif'];
        } else {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        }
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes)) {
            if ($type === 'favicon') {
                error('Favicon 只支持 ICO、PNG、GIF 格式');
            } else {
                error('Logo 只支持 JPG、PNG、GIF、WEBP 格式');
            }
        }
        if ($file['size'] > 2 * 1024 * 1024) {
            error('文件大小不能超过 2MB');
        }
        if ($type === 'favicon') {
            $targetPath = ML_ROOT . '/public/assets/favicon.ico';
            $url = '/assets/favicon.ico';
        } else {
            $targetPath = ML_ROOT . '/public/assets/logo.png';
            $url = '/assets/logo.png';
        }
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            error('文件上传失败');
        }
        $url .= '?v=' . time();

        success(['url' => $url], '上传成功');
    }

    /**
     * 注册登录设置
     */
    public function authSettings()
    {
        $settings = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}settings WHERE id = 1"
        ) ?: [];

        $this->view('admin/settings/auth', [
            'settings' => $settings,
            'csrf_token' => $this->generateCsrf(),
        ]);
    }

    /**
     * 更新注册登录设置
     */
    public function updateAuthSettings()
    {
        $this->verifyCsrf();

        // 直接从 $_POST 获取 settings 数组
        $settings = $_POST['settings'] ?? [];

        if (!is_array($settings)) {
            error('参数错误');
        }

        $allowedKeys = ['enable_register', 'register_verify_method'];
        
        $updateData = [];
        foreach ($settings as $key => $value) {
            if (in_array($key, $allowedKeys)) {
                $updateData[$key] = $value;
            }
        }
        
        if (!empty($updateData)) {
            $this->db->update('settings', $updateData, 'id = 1');
        }

        success(null, '注册登录设置更新成功');
    }

    /**
     * 授权日志
     */
    public function logs()
    {
        $page = max(1, (int) $this->input('page', 1));
        $limit = 50;
        $offset = ($page - 1) * $limit;

        $total = $this->db->fetch(
            "SELECT COUNT(*) as count FROM {$this->db->getPrefix()}oauth_logs WHERE status = 1"
        )['count'];

        $logs = $this->db->fetchAll(
            "SELECT l.*, a.app_name, a.app_id as application_id, u.username, u.id as owner_id,
                    ou.nickname, ou.avatar, ou.gender
             FROM {$this->db->getPrefix()}oauth_logs l 
             LEFT JOIN {$this->db->getPrefix()}apps a ON l.app_id = a.app_id 
             LEFT JOIN {$this->db->getPrefix()}users u ON a.`user` = u.id
             LEFT JOIN {$this->db->getPrefix()}oauth_users ou ON l.app_id = ou.app_id AND l.type = ou.type AND l.open_id = ou.open_id
             WHERE l.status = 1
             ORDER BY l.`time` DESC LIMIT {$limit} OFFSET {$offset}"
        );

        $this->view('admin/logs', [
            'logs' => $logs,
            'total' => $total,
            'page' => $page,
            'totalPages' => ceil($total / $limit),
            'csrf_token' => $this->generateCsrf(),
        ]);
    }

    /**
     * 身份认证配置页面
     */
    public function verification()
    {
        $config = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}verification_config WHERE id = 1"
        );
        if (!$config) {
            $this->db->insert('verification_config', [
                'id' => 1,
                'status' => 0,
                'provider' => 'tencent',
                'personal_status' => 1,
                'enterprise_status' => 1,
                'fee' => 0,
                'fee_amount' => 0.00,
                'reward' => 0,
                'reward_product_id' => null,
                'reward_duration' => null,
            ]);
            $config = $this->db->fetch(
                "SELECT * FROM {$this->db->getPrefix()}verification_config WHERE id = 1"
            );
        }
        $pendingCount = $this->db->fetch(
            "SELECT COUNT(*) as count FROM {$this->db->getPrefix()}user_verifications WHERE status IN (0, 3)"
        )['count'] ?? 0;
        $verifications = $this->db->fetchAll(
            "SELECT v.*, u.username, u.email FROM {$this->db->getPrefix()}user_verifications v 
             LEFT JOIN {$this->db->getPrefix()}users u ON v.`user` = u.id 
             ORDER BY v.`time` DESC LIMIT 20"
        );
        $products = $this->db->fetchAll(
            "SELECT id, name, type, duration FROM {$this->db->getPrefix()}products WHERE status = 1 ORDER BY sort ASC, id ASC"
        );

        $this->view('admin/settings/verification', [
            'config' => $config,
            'pendingCount' => $pendingCount,
            'verifications' => $verifications,
            'products' => $products,
            'csrf_token' => $this->generateCsrf(),
        ]);
    }

    /**
     * 更新身份认证配置
     */
    public function updateVerification()
    {
        $this->verifyCsrf();

        try {
            $provider = $this->input('provider', 'manual');
            $carrierProvider = $this->input('carrier_provider', 'slsj');
            
            // 基本配置
            $data = [
                'status' => (int) $this->input('enabled', 0),
                'personal_status' => (int) $this->input('personal_enabled', 0),
                'enterprise_status' => (int) $this->input('enterprise_enabled', 0),
                'require' => (int) $this->input('require_verification', 0),
            ];
            
            // 如果选择了运营商认证，保存供应商类型
            $carrierEnabled = ($provider === 'carrier') ? 1 : 0;
            $data['carrier'] = $carrierEnabled;
            $data['provider'] = $carrierEnabled ? $carrierProvider : 'manual';
            
            // 根据供应商保存对应的配置
            if ($carrierEnabled) {
                $apiUrl = trim($this->input('carrier_api_url', ''));
                
                switch ($carrierProvider) {
                    case 'slsj':
                        $memberId = trim($this->input('slsj_member_id', ''));
                        $appKey = trim($this->input('slsj_app_key', ''));
                        
                        if (!empty($memberId)) {
                            $data['slsj_member_id'] = $memberId;
                        }
                        if (!empty($appKey)) {
                            $data['slsj_app_key'] = encrypt($appKey);
                        }
                        if (!empty($apiUrl)) {
                            $data['slsj_api_url'] = $apiUrl;
                        }
                        break;
                        
                    case 'shuxun':
                        $appKey = trim($this->input('shuxun_app_key', ''));
                        $appSecret = trim($this->input('shuxun_app_secret', ''));
                        
                        if (!empty($appKey)) {
                            $data['shuxun_app_key'] = encrypt($appKey);
                        }
                        if (!empty($appSecret)) {
                            $data['shuxun_app_secret'] = encrypt($appSecret);
                        }
                        if (!empty($apiUrl)) {
                            $data['shuxun_api_url'] = $apiUrl;
                        }
                        break;
                        
                    case 'chuanglan':
                        $appId = trim($this->input('chuanglan_app_id', ''));
                        $appKey = trim($this->input('chuanglan_app_key', ''));
                        
                        if (!empty($appId)) {
                            $data['chuanglan_app_id'] = $appId;
                        }
                        if (!empty($appKey)) {
                            $data['chuanglan_app_key'] = encrypt($appKey);
                        }
                        if (!empty($apiUrl)) {
                            $data['chuanglan_api_url'] = $apiUrl;
                        }
                        break;
                }
            }
            
            // 认证收费设置
            $feeEnabled = (int) $this->input('fee_enabled', 0);
            $feeAmount = (float) $this->input('fee_amount', 0);
            if ($feeEnabled && $feeAmount < 0) {
                error('认证费用不能为负数');
            }
            
            $data['fee'] = $feeEnabled;
            $data['fee_amount'] = $feeAmount;
            
            // 认证奖励设置
            $rewardEnabled = (int) $this->input('reward_enabled', 0);
            $rewardProductId = (int) $this->input('reward_product_id', 0) ?: null;
            $rewardDuration = (int) $this->input('reward_duration', 0) ?: null;
            if ($rewardEnabled && ($rewardDuration === null || $rewardDuration <= 0)) {
                error('奖励有效期必须大于0');
            }
            if ($rewardEnabled && $rewardProductId) {
                $product = $this->db->fetch(
                    "SELECT id FROM {$this->db->getPrefix()}products WHERE id = ? AND status = 1",
                    [$rewardProductId]
                );
                if (!$product) {
                    error('所选奖励产品不存在或已下架');
                }
            }
            
            $data['reward'] = $rewardEnabled;
            $data['reward_product_id'] = $rewardProductId;
            $data['reward_duration'] = $rewardDuration;

            $this->db->update('verification_config', $data, 'id = ?', [1]);

            success(null, '身份认证配置更新成功');
        } catch (\Exception $e) {
            error('保存失败: ' . $e->getMessage());
        }
    }

    /**
     * 审核用户认证
     */
    public function reviewVerification()
    {
        $this->verifyCsrf();

        $userId = (int) $this->input('user_id', 0);
        $action = $this->input('action', ''); // approve/reject
        $reason = $this->input('reason', '');

        if (!$userId || !in_array($action, ['approve', 'reject'])) {
            error('参数错误');
        }
        $verification = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}user_verifications WHERE `user` = ? AND status IN (0, 3)",
            [$userId]
        );

        if (!$verification) {
            error('认证记录不存在');
        }

        if ($action === 'approve') {
            $this->db->update('user_verifications', [
                'status' => 1,
                'verify_provider' => 'manual',
                'reason' => $reason ?: '审核通过',
                'verified_time' => date('Y-m-d H:i:s'),
            ], 'id = ?', [$verification['id']]);
            $this->db->update('users', [
                'verification' => $verification['type'],
            ], 'id = ?', [$verification['user']]);

            success(null, '认证已通过');
        } else {
            if (empty($reason)) {
                error('请填写拒绝原因');
            }

            $this->db->update('user_verifications', [
                'status' => 2,
                'reason' => $reason,
            ], 'id = ?', [$verification['id']]);

            success(null, '认证已拒绝');
        }
    }

    /**
     * 管理员个人资料页面
     */
    public function profile()
    {
        $this->view('admin/profile', [
            'csrf_token' => $this->generateCsrf(),
        ]);
    }

    /**
     * 更新管理员资料
     */
    public function updateProfile()
    {
        $this->verifyCsrf();

        $email = trim($this->input('email', ''));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            error('邮箱格式不正确');
        }
        $exists = $this->db->fetch(
            "SELECT id FROM {$this->db->getPrefix()}users WHERE email = ? AND id != ?",
            [$email, $this->admin['id']]
        );

        if ($exists) {
            error('邮箱已被使用');
        }

        $this->db->update('users', [
            'email' => $email,
        ], 'id = ?', [$this->admin['id']]);

        success(null, '资料更新成功');
    }

    /**
     * 修改管理员密码
     */
    public function changePassword()
    {
        $this->verifyCsrf();

        $oldPassword = $this->input('old_password', '');
        $newPassword = $this->input('new_password', '');
        $confirmPassword = $this->input('confirm_password', '');

        if (!password_verify($oldPassword, $this->admin['password'])) {
            error('原密码错误');
        }

        if (strlen($newPassword) < 6) {
            error('新密码长度至少6位');
        }

        if ($newPassword !== $confirmPassword) {
            error('两次密码不一致');
        }

        $this->db->update('users', [
            'password' => password_hash($newPassword, PASSWORD_DEFAULT),
        ], 'id = ?', [$this->admin['id']]);

        success(null, '密码修改成功');
    }

    /**
     * 计费设置页面
     */
    public function billingSettings()
    {
        $row = $this->db->fetch(
            "SELECT billing_free_enabled, billing_free_daily_limit, billing_free_platforms,
                    billing_rate_limit_default, billing_rate_limit_package, billing_rate_limit_quota
             FROM {$this->db->getPrefix()}settings WHERE id = 1"
        );

        $settingsMap = $row ?: [];
        $platforms = $this->db->fetchAll(
            "SELECT name, platform FROM {$this->db->getPrefix()}platforms WHERE status = 1 ORDER BY sort"
        );

        $this->view('admin/settings/billing', [
            'settings' => $settingsMap,
            'platforms' => $platforms,
            'csrf_token' => $this->generateCsrf(),
        ]);
    }

    /**
     * 更新计费设置
     */
    public function updateBillingSettings()
    {
        $this->verifyCsrf();

        $settings = $this->input('settings', []);

        if (!is_array($settings)) {
            error('参数错误');
        }
        $allowedKeys = [
            'billing_free_enabled', 'billing_free_daily_limit', 'billing_free_platforms',
            'billing_rate_limit_default', 'billing_rate_limit_package', 'billing_rate_limit_quota',
        ];
        
        $updateData = [];
        foreach ($settings as $key => $value) {
            if (in_array($key, $allowedKeys)) {
                $updateData[$key] = $value;
            }
        }
        
        if (!empty($updateData)) {
            $this->db->update('settings', $updateData, 'id = 1');
        }

        success(null, '计费设置更新成功');
    }

    /**
     * 计费统计页面
     */
    public function billingStats()
    {
        $prefix = $this->db->getPrefix();
        $packageStats = $this->db->fetch(
            "SELECT 
                COUNT(*) as total_packages,
                SUM(CASE WHEN status = 1 AND expire_time > NOW() THEN 1 ELSE 0 END) as active_packages,
                SUM(CASE WHEN status = 0 OR expire_time <= NOW() THEN 1 ELSE 0 END) as expired_packages
             FROM {$prefix}user_packages"
        );
        $packagesByType = $this->db->fetchAll(
            "SELECT type, COUNT(*) as count, 
                    SUM(CASE WHEN status = 1 AND expire_time > NOW() THEN 1 ELSE 0 END) as active_count
             FROM {$prefix}user_packages 
             GROUP BY type"
        );
        $today = date('Y-m-d');
        $weekStart = date('Y-m-d', strtotime('monday this week'));
        $monthStart = date('Y-m-01');

        $callStats = $this->db->fetch(
            "SELECT 
                COUNT(*) as total_calls,
                SUM(CASE WHEN DATE(`time`) = ? THEN 1 ELSE 0 END) as today_calls,
                SUM(CASE WHEN `time` >= ? THEN 1 ELSE 0 END) as week_calls,
                SUM(CASE WHEN `time` >= ? THEN 1 ELSE 0 END) as month_calls
             FROM {$prefix}api_logs",
            [$today, $weekStart . ' 00:00:00', $monthStart . ' 00:00:00']
        );
        $callsByPackageType = $this->db->fetchAll(
            "SELECT product_type, COUNT(*) as count 
             FROM {$prefix}api_logs 
             GROUP BY product_type 
             ORDER BY count DESC"
        );
        $callsByPlatform = $this->db->fetchAll(
            "SELECT platform, COUNT(*) as count 
             FROM {$prefix}api_logs 
             GROUP BY platform 
             ORDER BY count DESC 
             LIMIT 10"
        );
        $dailyTrend = $this->db->fetchAll(
            "SELECT DATE(`time`) as date, COUNT(*) as count 
             FROM {$prefix}api_logs 
             WHERE `time` >= DATE_SUB(NOW(), INTERVAL 7 DAY)
             GROUP BY DATE(`time`) 
             ORDER BY date ASC"
        );
        $revenueStats = $this->db->fetch(
            "SELECT 
                SUM(CASE WHEN status = 1 THEN amount ELSE 0 END) as total_revenue,
                SUM(CASE WHEN status = 1 AND DATE(`time`) = ? THEN amount ELSE 0 END) as today_revenue,
                SUM(CASE WHEN status = 1 AND `time` >= ? THEN amount ELSE 0 END) as month_revenue,
                COUNT(CASE WHEN status = 1 THEN 1 END) as paid_orders
             FROM {$prefix}orders",
            [$today, $monthStart . ' 00:00:00']
        );

        $this->view('admin/billing_stats', [
            'packageStats' => $packageStats,
            'packagesByType' => $packagesByType,
            'callStats' => $callStats,
            'callsByPackageType' => $callsByPackageType,
            'callsByPlatform' => $callsByPlatform,
            'dailyTrend' => $dailyTrend,
            'revenueStats' => $revenueStats,
            'csrf_token' => $this->generateCsrf(),
        ]);
    }

    /**
     * 为用户开通/延长套餐
     */
    public function grantPackage()
    {
        $this->verifyCsrf();

        $userId = (int) $this->input('user_id', 0);
        $productId = (int) $this->input('product_id', 0);
        $duration = (int) $this->input('duration', 30);
        $reason = trim($this->input('reason', ''));

        if ($userId <= 0) {
            error('请选择用户');
        }
        if ($productId === 0) {
            $this->db->query(
                "UPDATE {$this->db->getPrefix()}user_packages SET status = 0 WHERE `user` = ? AND status = 1",
                [$userId]
            );
            success(null, '用户套餐已取消');
            return;
        }

        if ($duration <= 0) {
            error('有效期必须大于0');
        }
        $product = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}products WHERE id = ?",
            [$productId]
        );

        if (!$product) {
            error('产品不存在');
        }
        $billingService = new \App\Services\BillingService($this->db);
        
        $productData = [
            'id' => $product['id'],
            'name' => $product['name'],
            'type' => $product['type'],
            'platforms' => $product['platforms'],
            'duration' => $duration,
            'total_quota' => $product['total_quota'],
            'account_limit' => $product['account_limit']
        ];

        $packageId = $billingService->activatePackage(
            $userId,
            0, // 管理员开通，无订单ID
            $productData,
            $this->admin['id'],
            $reason ?: '管理员手动开通'
        );

        success(['package_id' => $packageId], '套餐开通成功');
    }

    /**
     * 获取用户套餐信息
     */
    public function getUserPackage()
    {
        $userId = (int) $this->input('user_id', 0);

        if ($userId <= 0) {
            error('参数错误');
        }

        $billingService = new \App\Services\BillingService($this->db);
        $package = $billingService->getActivePackage($userId);
        $stats = $billingService->getUsageStats($userId);

        // 如果没有有效套餐，返回免费套餐信息
        if (!$package) {
            $freeConfig = $billingService->getFreeUserConfig();
            if ($freeConfig['enabled']) {
                $package = [
                    'id' => 0,
                    'name' => '免费套餐',
                    'type' => 'free',
                    'daily_limit' => $freeConfig['daily_limit'],
                    'platforms' => json_encode($freeConfig['platforms']),
                    'platforms_array' => $freeConfig['platforms'],
                    'expire_time' => null,
                    'status' => 1,
                    'is_free' => true
                ];
            }
        }

        success([
            'package' => $package,
            'stats' => $stats
        ]);
    }

    /**
     * 技术支持页面
     */
    public function support()
    {
        $currentVersion = \App\Services\VersionHelper::getCurrentVersion();
        $supportInfo = [
            'qq' => '',
            'wechat' => '',
            'email' => '',
            'qq_group' => '',
        ];
        $updateStatus = \App\Services\VersionHelper::getUpdateStatus($currentVersion, null);

        $updateInfo = [
            'current_version' => \App\Services\VersionHelper::format($currentVersion),
            'latest_version' => '检查中...',
            'status' => $updateStatus['status'],
            'has_update' => $updateStatus['has_update'],
            'download_url' => 'https://www.xingqingchuang.com/download',
        ];

        $this->view('admin/support', [
            'supportInfo' => $supportInfo,
            'updateInfo' => $updateInfo,
            'csrf_token' => $this->generateCsrf(),
        ]);
    }

    /**
     * 检查更新 API
     */
    public function checkUpdate()
    {
        $this->verifyCsrf();
        
        try {
            $currentVersion = \App\Services\VersionHelper::getCurrentVersion();
            
            $callbackParams = $verifyResult['callback_params'] ?? [];
            $currentVersion = \App\Services\VersionHelper::getCurrentVersion();
            $latestVersion = $verifyResult['latest_version'] 
                ?? $callbackParams['latest_version'] 
                ?? $callbackParams['version'] 
                ?? null;
            $updateStatus = \App\Services\VersionHelper::getUpdateStatus($currentVersion, $latestVersion);
            
            \success([
                'current_version' => \App\Services\VersionHelper::format($currentVersion),
                'latest_version' => $latestVersion ? \App\Services\VersionHelper::format($latestVersion) : '检查失败',
                'status' => $updateStatus['status'],
                'has_update' => $updateStatus['has_update'],
                'download_url' => $callbackParams['download_url'] ?? 'https://www.xingqingchuang.com/download',
            ], '检查完成');
        } catch (\Exception $e) {
            \error('检查更新失败: ' . $e->getMessage());
        }
    }

    /**
     * 刷新授权状态
     * 用于强制更新弹窗中的"刷新状态"按钮
     */
    public function refreshLicense()
    {
        $this->verifyCsrf();
        
        try {
            $updateService = new \App\Services\UpdateService();
            $result = $updateService->checkForUpdates();
            
            if ($result['success']) {
                $forceUpdate = $result['force_update'] ?? false;
                \success([
                    'force_update' => $forceUpdate,
                    'latest_version' => $result['latest_version'] ?? null,
                    'current_version' => $result['current_version'] ?? null,
                    'download_code' => $result['download_code'] ?? null,
                    'title' => $result['title'] ?? null,
                    'log' => $result['log'] ?? null
                ], $forceUpdate ? '仍需更新' : '状态已刷新');
            } else {
                \error($result['message'] ?? '检查更新失败');
            }
        } catch (\Exception $e) {
            \error('刷新失败: ' . $e->getMessage());
        }
    }

    /**
     * 执行代码更新
     * 用于强制更新弹窗中的"立即更新"按钮
     */
    public function performUpdate()
    {
        $this->verifyCsrf();
        
        try {
            $downloadCode = trim($this->input('download_code', ''));
            
            // 如果没有提供 download_code，先检查更新获取
            if (empty($downloadCode)) {
                $updateService = new \App\Services\UpdateService();
                $checkResult = $updateService->checkForUpdates();
                
                if (!$checkResult['success']) {
                    \error($checkResult['message'] ?? '获取更新信息失败');
                    return;
                }
                
                $downloadCode = $checkResult['download_code'] ?? '';
                if (empty($downloadCode)) {
                    \error('无法获取下载码，请稍后重试');
                    return;
                }
            }
            
            $updateService = new \App\Services\UpdateService();
            $result = $updateService->performUpdate($downloadCode);
            
            if ($result['success']) {
                \success([
                    'latest_version' => $result['latest_version'] ?? null,
                    'details' => $result['details'] ?? []
                ], $result['message'] ?? '更新成功');
            } else {
                \error($result['message'] ?? '更新失败');
            }
        } catch (\Exception $e) {
            \error('更新失败: ' . $e->getMessage());
        }
    }
}
