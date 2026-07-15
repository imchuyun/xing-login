<?php
namespace App\Controllers;

use App\Services\BillingService;
use App\Services\CarrierVerificationService;

/**
 * 用户控制器
 */
class UserController extends BaseController
{
    /**
     * 仪表盘
     */
    public function dashboard()
    {
        $appCount = $this->db->fetch(
            "SELECT COUNT(*) as count FROM {$this->db->getPrefix()}apps WHERE `user` = ?",
            [$this->user['id']]
        )['count'];
        $todayCalls = $this->db->fetch(
            "SELECT SUM(today_calls) as total FROM {$this->db->getPrefix()}apps WHERE `user` = ?",
            [$this->user['id']]
        )['total'] ?? 0;
        $totalCalls = $this->db->fetch(
            "SELECT SUM(total_calls) as total FROM {$this->db->getPrefix()}apps WHERE `user` = ?",
            [$this->user['id']]
        )['total'] ?? 0;
        $recentLogs = $this->db->fetchAll(
            "SELECT * FROM {$this->db->getPrefix()}user_login_logs 
             WHERE `user` = ? 
             ORDER BY `time` DESC LIMIT 10",
            [$this->user['id']]
        );
        $currentPackage = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}user_packages 
             WHERE `user` = ? AND status = 1 AND expire_time > NOW()
             ORDER BY expire_time DESC LIMIT 1",
            [$this->user['id']]
        );
        $billingService = new BillingService($this->db);
        $usageStats = $billingService->getUsageStats($this->user['id']);

        $this->view('user/dashboard', [
            'appCount' => $appCount,
            'todayCalls' => $todayCalls,
            'totalCalls' => $totalCalls,
            'recentLogs' => $recentLogs,
            'currentPackage' => $currentPackage,
            'usageStats' => $usageStats,
            'csrf_token' => $this->generateCsrf(),
        ]);
    }

    /**
     * 应用列表
     */
    public function apps()
    {
        $apps = $this->db->fetchAll(
            "SELECT * FROM {$this->db->getPrefix()}apps WHERE `user` = ? ORDER BY `time` DESC",
            [$this->user['id']]
        );
        $platforms = $this->db->fetchAll(
            "SELECT name, platform, status FROM {$this->db->getPrefix()}platforms ORDER BY sort ASC, id ASC"
        );

        // 获取用户套餐信息
        $billingService = new \App\Services\BillingService();
        $packageInfo = $billingService->getSidebarPackageInfo($this->user['id']);

        $this->view('user/apps', [
            'apps' => $apps,
            'platforms' => $platforms,
            'packageInfo' => $packageInfo,
            'csrf_token' => $this->generateCsrf(),
        ]);
    }

    /**
     * 创建应用
     */
    public function createApp()
    {
        $this->verifyCsrf();

        $appName = trim($this->input('app_name', ''));
        $description = trim($this->input('description', ''));
        $domain = trim($this->input('domain', ''));
        $callbackUrl = trim($this->input('callback_url', ''));
        $platforms = $this->input('platforms', []);

        if (empty($appName)) {
            error('请输入应用名称');
        }

        if (empty($domain)) {
            error('请输入授权域名');
        }
        $domain = preg_replace('#^https?://#', '', $domain);
        $domain = rtrim($domain, '/');

        if (empty($callbackUrl)) {
            error('请输入回调地址');
        }

        if (!filter_var($callbackUrl, FILTER_VALIDATE_URL)) {
            error('回调地址格式不正确');
        }
        $callbackHost = parse_url($callbackUrl, PHP_URL_HOST);
        if ($callbackHost !== $domain && !str_ends_with($callbackHost, '.' . $domain)) {
            error('回调地址必须与授权域名匹配');
        }

        if (!is_array($platforms) || empty($platforms)) {
            $platforms = ['qq', 'wx', 'alipay'];
        }
        $appCount = $this->db->fetch(
            "SELECT COUNT(*) as count FROM {$this->db->getPrefix()}apps WHERE `user` = ?",
            [$this->user['id']]
        )['count'];

        if ($appCount >= 10) {
            error('每个用户最多创建10个应用');
        }
        $appId = random_string(16);
        $appSecret = random_string(32);
        $appIcon = null;
        if (isset($_FILES['app_icon']) && $_FILES['app_icon']['error'] === UPLOAD_ERR_OK) {
            $appIcon = $this->uploadAppIcon($_FILES['app_icon'], $appId);
        }
        $this->db->insert('apps', [
            'user' => $this->user['id'],
            'app_name' => $appName,
            'description' => $description,
            'app_id' => $appId,
            'app_secret' => $appSecret,
            'domain' => $domain,
            'callback' => $callbackUrl,  // 内部字段名为callback
            'platforms' => implode(',', $platforms),
            'app_icon' => $appIcon,
            'status' => 1,
        ]);

        success([
            'app_id' => $appId,
            'app_secret' => $appSecret,
        ], '应用创建成功');
    }

    /**
     * 上传应用图标
     */
    protected function uploadAppIcon($file, $appId = null)
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes)) {
            error('只支持 JPG、PNG、GIF、WEBP 格式的图片');
        }
        if ($file['size'] > 2 * 1024 * 1024) {
            error('图片大小不能超过2MB');
        }
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $ext = 'png';
        }
        $fileName = ($appId ? $appId : random_string(32)) . '.' . $ext;
        // 上传到public目录下，确保Web可访问
        $uploadDir = ML_ROOT . '/public/storage/uploads/apps';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $filePath = $uploadDir . '/' . $fileName;
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            error('图片上传失败');
        }
        return '/storage/uploads/apps/' . $fileName;
    }

    /**
     * 更新应用
     */
    public function updateApp()
    {
        $this->verifyCsrf();

        $id = (int) $this->input('id', 0);
        $appName = trim($this->input('app_name', ''));
        $domain = trim($this->input('domain', ''));
        $callbackUrl = trim($this->input('callback_url', '')) ?: trim($this->input('callback', ''));
        $platforms = $this->input('platforms', []);
        $status = (int) $this->input('status', 1);
        $app = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}apps WHERE id = ? AND `user` = ?",
            [$id, $this->user['id']]
        );

        if (!$app) {
            error('应用不存在');
        }

        $domain = preg_replace('#^https?://#', '', $domain);
        $domain = rtrim($domain, '/');

        $updateData = [
            'app_name' => $appName,
            'domain' => $domain,
            'callback' => $callbackUrl,
            'platforms' => implode(',', $platforms),
            'status' => $status,
        ];
        if (isset($_FILES['app_icon']) && $_FILES['app_icon']['error'] === UPLOAD_ERR_OK) {
            $updateData['app_icon'] = $this->uploadAppIcon($_FILES['app_icon'], $app['app_id']);
        }

        $this->db->update('apps', $updateData, 'id = ?', [$id]);

        success(null, '应用更新成功');
    }

    /**
     * 删除应用
     */
    public function deleteApp()
    {
        $this->verifyCsrf();

        $id = (int) $this->input('id', 0);
        $app = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}apps WHERE id = ? AND `user` = ?",
            [$id, $this->user['id']]
        );

        if (!$app) {
            error('应用不存在');
        }
        if (!empty($app['app_icon'])) {
            $iconPath = ML_ROOT . '/public' . $app['app_icon'];
            if (file_exists($iconPath)) {
                @unlink($iconPath);
            }
        }

        $this->db->delete('apps', 'id = ?', [$id]);

        success(null, '应用删除成功');
    }

    /**
     * 应用详情
     */
    public function appDetail($appId)
    {
        $app = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}apps WHERE app_id = ? AND `user` = ?",
            [$appId, $this->user['id']]
        );

        if (!$app) {
            error('应用不存在', 404, 404);
        }
        $platforms = $this->db->fetchAll(
            "SELECT name, platform, status FROM {$this->db->getPrefix()}platforms ORDER BY id ASC"
        );
        $logs = $this->db->fetchAll(
            "SELECT type as platform, open_id, nickname, avatar, gender, `time`, last_time 
             FROM {$this->db->getPrefix()}oauth_users 
             WHERE app_id = ? 
             ORDER BY last_time DESC 
             LIMIT 50",
            [$app['app_id']]
        );

        // 获取用户套餐信息
        $billingService = new \App\Services\BillingService();
        $packageInfo = $billingService->getSidebarPackageInfo($this->user['id']);

        $this->view('user/app_detail', [
            'app' => $app,
            'platforms' => $platforms,
            'packageInfo' => $packageInfo,
            'logs' => $logs,
            'csrf_token' => $this->generateCsrf(),
        ]);
    }

    /**
     * 用户管理 - 查看通过应用登录的用户
     */
    public function members()
    {
        $page = max(1, (int) $this->input('page', 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;
        $platform = $this->input('platform', '');
        $appId = $this->input('app_id', '');
        $search = trim($this->input('search', ''));
        $userApps = $this->db->fetchAll(
            "SELECT app_id, app_name FROM {$this->db->getPrefix()}apps WHERE `user` = ?",
            [$this->user['id']]
        );

        if (empty($userApps)) {
            $this->view('user/members', [
                'members' => [],
                'userApps' => [],
                'platforms' => [],
                'total' => 0,
                'page' => 1,
                'totalPages' => 0,
                'filter' => ['platform' => '', 'app_id' => '', 'search' => ''],
                'csrf_token' => $this->generateCsrf(),
            ]);
            return;
        }

        $appIds = array_column($userApps, 'app_id');
        $placeholders = implode(',', array_fill(0, count($appIds), '?'));
        $where = "u.app_id IN ({$placeholders})";
        $params = $appIds;

        if ($platform) {
            $where .= " AND u.type = ?";
            $params[] = $platform;
        }

        if ($appId) {
            $where .= " AND u.app_id = ?";
            $params[] = $appId;
        }

        if ($search) {
            $where .= " AND (u.open_id LIKE ? OR u.nickname LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        $total = $this->db->fetch(
            "SELECT COUNT(*) as count FROM {$this->db->getPrefix()}oauth_users u WHERE {$where}",
            $params
        )['count'];
        $members = $this->db->fetchAll(
            "SELECT u.*, a.app_name,
                    (SELECT COUNT(*) FROM {$this->db->getPrefix()}oauth_logs l 
                     WHERE l.app_id = u.app_id AND l.type = u.type AND l.open_id = u.open_id AND l.status = 1) as login_count
             FROM {$this->db->getPrefix()}oauth_users u
             LEFT JOIN {$this->db->getPrefix()}apps a ON u.app_id = a.app_id
             WHERE {$where}
             ORDER BY u.last_time DESC
             LIMIT {$limit} OFFSET {$offset}",
            $params
        );
        $platforms = $this->db->fetchAll(
            "SELECT name, platform FROM {$this->db->getPrefix()}platforms WHERE status = 1"
        );

        $this->view('user/members', [
            'members' => $members,
            'userApps' => $userApps,
            'platforms' => $platforms,
            'total' => $total,
            'page' => $page,
            'totalPages' => ceil($total / $limit),
            'filter' => ['platform' => $platform, 'app_id' => $appId, 'search' => $search],
            'csrf_token' => $this->generateCsrf(),
        ]);
    }

    /**
     * 个人资料
     */
    public function profile()
    {
        // 获取系统开启的平台
        $platforms = $this->db->fetchAll(
            "SELECT name, platform, icon FROM {$this->db->getPrefix()}platforms WHERE status = 1 ORDER BY sort ASC"
        );
        
        // 获取用户已绑定的社交账号
        $userBindings = $this->db->fetchAll(
            "SELECT * FROM {$this->db->getPrefix()}user_oauth WHERE user = ?",
            [$this->user['id']]
        );
        
        // 转换为以平台名为key的数组
        $bindingMap = [];
        foreach ($userBindings as $binding) {
            $bindingMap[$binding['platform']] = $binding;
        }
        
        $this->view('user/profile', [
            'csrf_token' => $this->generateCsrf(),
            'platforms' => $platforms,
            'bindingMap' => $bindingMap,
        ]);
    }

    /**
     * 更新资料
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
            [$email, $this->user['id']]
        );

        if ($exists) {
            error('邮箱已被使用');
        }

        $this->db->update('users', [
            'email' => $email,
        ], 'id = ?', [$this->user['id']]);

        success(null, '资料更新成功');
    }

    /**
     * 修改密码
     */
    public function changePassword()
    {
        $this->verifyCsrf();

        $oldPassword = $this->input('old_password', '');
        $newPassword = $this->input('new_password', '');
        $confirmPassword = $this->input('confirm_password', '');

        if (!password_verify($oldPassword, $this->user['password'])) {
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
        ], 'id = ?', [$this->user['id']]);

        success(null, '密码修改成功');
    }

    /**
     * 绑定邮箱/手机
     */
    public function bindContact()
    {
        $this->verifyCsrf();

        $type = $this->input('type', '');
        $target = trim($this->input('target', ''));
        $verifyCode = trim($this->input('verify_code', ''));

        if (!in_array($type, ['email', 'phone'])) {
            error('无效的绑定类型');
        }

        if (empty($target)) {
            error('请输入' . ($type === 'email' ? '邮箱地址' : '手机号码'));
        }

        if (empty($verifyCode)) {
            error('请输入验证码');
        }
        if ($type === 'email') {
            if (!filter_var($target, FILTER_VALIDATE_EMAIL)) {
                error('邮箱格式不正确');
            }
        } else {
            if (!preg_match('/^1[3-9]\d{9}$/', $target)) {
                error('手机号格式不正确');
            }
        }
        $cacheKey = "verify_code_bind_{$target}";
        $cached = $_SESSION[$cacheKey] ?? null;
        if (!$cached || $cached['code'] !== $verifyCode || $cached['expires'] < time()) {
            error('验证码错误或已过期');
        }
        unset($_SESSION[$cacheKey]);
        $exists = $this->db->fetch(
            "SELECT id FROM {$this->db->getPrefix()}users WHERE {$type} = ? AND id != ?",
            [$target, $this->user['id']]
        );
        if ($exists) {
            error(($type === 'email' ? '该邮箱' : '该手机号') . '已被其他用户绑定');
        }
        $this->db->update('users', [
            $type => $target
        ], 'id = ?', [$this->user['id']]);

        success(null, '绑定成功');
    }

    /**
     * 解绑社交账号
     */
    public function unbindOAuth()
    {
        $this->verifyCsrf();

        $platform = $this->input('platform', '');
        
        if (empty($platform)) {
            error('参数错误');
        }
        
        // 检查是否有绑定
        $binding = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}user_oauth WHERE user = ? AND platform = ?",
            [$this->user['id'], $platform]
        );
        
        if (!$binding) {
            error('未绑定该平台账号');
        }
        
        // 检查用户是否有其他登录方式（邮箱、手机或其他社交账号）
        $hasEmail = !empty($this->user['email']);
        $hasPhone = !empty($this->user['phone']);
        $hasPassword = !empty($this->user['password']);
        
        $otherBindings = $this->db->fetch(
            "SELECT COUNT(*) as cnt FROM {$this->db->getPrefix()}user_oauth WHERE user = ? AND platform != ?",
            [$this->user['id'], $platform]
        );
        $hasOtherOAuth = ($otherBindings['cnt'] ?? 0) > 0;
        
        // 如果没有其他登录方式，不允许解绑
        if (!$hasEmail && !$hasPhone && !$hasPassword && !$hasOtherOAuth) {
            error('请先绑定邮箱或手机号后再解绑');
        }
        
        // 执行解绑（将user字段设为null，保留记录）
        $this->db->update('user_oauth', [
            'user' => null
        ], 'id = ?', [$binding['id']]);
        
        success(null, '解绑成功');
    }

    /**
     * API文档
     */
    public function docs()
    {
        $this->view('user/docs', [
            'csrf_token' => $this->generateCsrf(),
        ]);
    }

    /**
     * 产品列表
     */
    public function products()
    {
        $products = $this->db->fetchAll(
            "SELECT * FROM {$this->db->getPrefix()}products WHERE status = 1 ORDER BY sort ASC, id ASC"
        );
        $activePackages = $this->db->fetchAll(
            "SELECT * FROM {$this->db->getPrefix()}user_packages 
             WHERE `user` = ? AND status = 1 AND expire_time > NOW()
             ORDER BY expire_time DESC",
            [$this->user['id']]
        );
        $settings = $this->db->fetch(
            "SELECT pay_epay_enabled, pay_alipay_enabled, pay_wechat_enabled, pay_qqpay_enabled 
             FROM {$this->db->getPrefix()}settings WHERE id = 1"
        ) ?: [];
        
        $payMethods = [];
        if (($settings['pay_alipay_enabled'] ?? '0') == '1') $payMethods[] = 'alipay';
        if (($settings['pay_wechat_enabled'] ?? '0') == '1') $payMethods[] = 'wechat';
        if (($settings['pay_qqpay_enabled'] ?? '0') == '1') $payMethods[] = 'qqpay';

        // 获取所有启用的平台
        $platforms = $this->db->fetchAll(
            "SELECT name, platform FROM {$this->db->getPrefix()}platforms WHERE status = 1 ORDER BY sort ASC"
        );

        $this->view('user/products', [
            'products' => $products,
            'activePackages' => $activePackages,
            'payMethods' => $payMethods,
            'platforms' => $platforms,
            'csrf_token' => $this->generateCsrf(),
        ]);
    }

    /**
     * 创建订单
     */
    public function createOrder()
    {
        $this->verifyCsrf();

        $productId = (int) $this->input('product_id', 0);
        $payMethod = $this->input('pay_method', '');

        if ($productId <= 0) {
            error('请选择产品');
        }

        if (!in_array($payMethod, ['alipay', 'wechat', 'qqpay'])) {
            error('请选择支付方式');
        }
        $settings = $this->getSettings();
        
        $enabledKey = "pay_{$payMethod}_enabled";
        if (($settings[$enabledKey] ?? '0') != '1') {
            error('该支付方式暂未开放');
        }
        $product = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}products WHERE id = ? AND status = 1",
            [$productId]
        );
        if (!$product) {
            error('产品不存在或已下架');
        }
        $orderNo = date('YmdHis') . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        $this->db->insert('orders', [
            'no' => $orderNo,
            'user' => $this->user['id'],
            'product_id' => $product['id'],
            'product_name' => $product['name'],
            'product_type' => $product['type'],
            'amount' => $product['price'],
            'method' => $payMethod,
            'status' => 0,
            'expire_time' => date('Y-m-d H:i:s', strtotime('+30 minutes')),
            'snapshot' => json_encode($product),
        ]);
        
        // 获取支付渠道设置
        $channelKey = 'pay_' . $payMethod . '_channel';
        $channel = $settings[$channelKey] ?? 'epay';
        
        // 构建订单数据用于支付
        $order = [
            'no' => $orderNo,
            'product_name' => $product['name'],
            'amount' => $product['price'],
        ];
        
        // 根据渠道选择支付方式
        if ($channel === 'official') {
            $payUrl = $this->processOfficialPayment($order, $payMethod, $settings);
        } else {
            $payUrl = $this->processEpayPayment($order, $payMethod, $settings);
        }

        success(['order_no' => $orderNo, 'pay_url' => $payUrl], '订单创建成功');
    }

    /**
     * 购买产品（createOrder的别名）
     */
    public function buyProduct()
    {
        return $this->createOrder();
    }

    /**
     * 支付页面
     */
    public function payOrder($orderNo)
    {
        $order = $this->db->fetch(
            "SELECT o.*, p.name as product_name FROM {$this->db->getPrefix()}orders o 
             LEFT JOIN {$this->db->getPrefix()}products p ON o.product_id = p.id
             WHERE o.no = ? AND o.`user` = ?",
            [$orderNo, $this->user['id']]
        );

        if (!$order) {
            redirect('/user/orders');
            return;
        }
        if ($order['status'] == 1) {
            redirect('/user/orders?msg=订单已支付');
            return;
        }
        $settings = $this->getSettings();
        $payMethods = [];
        if (($settings['pay_alipay_enabled'] ?? '0') == '1') {
            $payMethods['alipay'] = [
                'name' => '支付宝',
                'channel' => $settings['pay_alipay_channel'] ?? 'epay',
                'icon' => 'alipay',
                'color' => 'from-blue-400 to-indigo-600',
            ];
        }
        if (($settings['pay_wechat_enabled'] ?? '0') == '1') {
            $payMethods['wechat'] = [
                'name' => '微信支付',
                'channel' => $settings['pay_wechat_channel'] ?? 'epay',
                'icon' => 'wx',
                'color' => 'from-green-400 to-emerald-600',
            ];
        }
        if (($settings['pay_qqpay_enabled'] ?? '0') == '1') {
            $payMethods['qqpay'] = [
                'name' => 'QQ钱包',
                'channel' => $settings['pay_qqpay_channel'] ?? 'epay',
                'icon' => 'qq',
                'color' => 'from-cyan-400 to-blue-500',
            ];
        }

        $this->view('user/pay', [
            'order' => $order,
            'payMethods' => $payMethods,
            'settings' => $settings,
            'csrf_token' => $this->generateCsrf(),
        ]);
    }

    /**
     * 发起支付
     */
    public function doPay()
    {
        try {
            $this->verifyCsrf();

            $orderNo = $this->input('order_no', '');
            $payType = $this->input('pay_type', 'alipay');

            if (empty($orderNo)) {
                error('订单号不能为空');
                return;
            }

            $order = $this->db->fetch(
                "SELECT o.*, p.name as product_name FROM {$this->db->getPrefix()}orders o 
                 LEFT JOIN {$this->db->getPrefix()}products p ON o.product_id = p.id
                 WHERE o.no = ? AND o.`user` = ? AND o.status = 0",
                [$orderNo, $this->user['id']]
            );

            if (!$order) {
                error('订单不存在或已支付');
                return;
            }

            $settings = $this->getSettings();
            
            // 检查该支付方式是否启用
            $enabledKey = 'pay_' . $payType . '_enabled';
            if (empty($settings[$enabledKey]) || $settings[$enabledKey] != '1') {
                error('该支付方式未启用');
                return;
            }
            
            // 获取支付渠道设置（官方接口或易支付）
            $channelKey = 'pay_' . $payType . '_channel';
            $channel = $settings[$channelKey] ?? 'epay';
            
            $this->db->update('orders', ['method' => $payType], 'id = ?', [$order['id']]);
            
            // 根据渠道选择支付方式
            if ($channel === 'official') {
                // 官方接口支付 - 返回二维码数据用于页面内显示
                $payResult = $this->processOfficialPayment($order, $payType, $settings);
                success($payResult, '获取支付信息成功');
            } else {
                // 易支付 - 返回跳转URL
                $payUrl = $this->processEpayPayment($order, $payType, $settings);
                success(['url' => $payUrl, 'type' => 'redirect'], '正在跳转支付...');
            }

        } catch (\Exception $e) {
            error('支付发起失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 处理易支付
     */
    private function processEpayPayment(array $order, string $payType, array $settings): string
    {
        if (empty($settings['pay_epay_enabled']) || $settings['pay_epay_enabled'] != '1') {
            error('易支付未启用，请先在后台配置');
        }

        if (empty($settings['pay_epay_pid']) || empty($settings['pay_epay_key'])) {
            error('易支付商户信息未配置');
        }

        $baseUrl = rtrim($settings['site_url'] ?? '', '/');
        if (empty($baseUrl)) {
            $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') 
                     . '://' . $_SERVER['HTTP_HOST'];
        }
        
        $epayConfig = [
            'api_url'    => $settings['pay_epay_api_url'] ?? '',
            'pid'        => $settings['pay_epay_pid'],
            'key'        => $settings['pay_epay_key'],
            'notify_url' => $baseUrl . '/pay/epay/notify',
            'return_url' => $baseUrl . '/pay/epay/return',
        ];
        
        $payTypeMap = ['alipay' => 'alipay', 'wechat' => 'wxpay', 'qqpay' => 'qqpay'];
        $type = $payTypeMap[$payType] ?? 'alipay';
        
        $params = [
            'pid'          => $epayConfig['pid'],
            'type'         => $type,
            'out_trade_no' => $order['no'],
            'notify_url'   => $epayConfig['notify_url'],
            'return_url'   => $epayConfig['return_url'],
            'name'         => $order['product_name'] ?? '订单支付',
            'money'        => sprintf('%.2f', $order['amount']),
        ];
        
        ksort($params);
        $signStr = '';
        foreach ($params as $k => $v) {
            if ($v !== '' && $v !== null) {
                $signStr .= $k . '=' . $v . '&';
            }
        }
        $signStr = rtrim($signStr, '&');
        $params['sign'] = md5($signStr . $epayConfig['key']);
        $params['sign_type'] = 'MD5';
        
        return rtrim($epayConfig['api_url'], '/') . '/submit.php?' . http_build_query($params);
    }
    
    /**
     * 处理官方接口支付
     */
    private function processOfficialPayment(array $order, string $payType, array $settings): array
    {
        $baseUrl = rtrim($settings['site_url'] ?? '', '/');
        if (empty($baseUrl)) {
            $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') 
                     . '://' . $_SERVER['HTTP_HOST'];
        }
        
        switch ($payType) {
            case 'wechat':
                return $this->processWechatOfficialPayment($order, $settings, $baseUrl);
            case 'alipay':
                return $this->processAlipayOfficialPayment($order, $settings, $baseUrl);
            case 'qqpay':
                return $this->processQqpayOfficialPayment($order, $settings, $baseUrl);
            default:
                error("不支持的支付方式: {$payType}");
                return [];
        }
    }
    
    /**
     * 处理微信官方支付
     */
    private function processWechatOfficialPayment(array $order, array $settings, string $baseUrl): array
    {
        // 检查微信官方配置
        if (empty($settings['pay_wechat_app_id']) || empty($settings['pay_wechat_mch_id']) || empty($settings['pay_wechat_api_key'])) {
            error('微信支付官方接口未配置完整，请在后台设置AppId、商户号和API密钥');
        }
        
        $wechatPay = new \App\Services\WechatPayService([
            'app_id' => $settings['pay_wechat_app_id'],
            'mch_id' => $settings['pay_wechat_mch_id'],
            'api_key' => $settings['pay_wechat_api_key'],
            'notify_url' => $baseUrl . '/pay/wechat/notify',
        ]);
        
        // Native支付（扫码支付）- 返回二维码数据
        $result = $wechatPay->createNativeOrder(
            $order['no'],
            $order['product_name'] ?? '订单支付',
            (float)$order['amount']
        );
        
        if (!$result['success']) {
            error($result['message']);
        }
        
        return [
            'type' => 'qrcode',
            'pay_type' => 'wechat',
            'code_url' => $result['code_url'],
            'order_no' => $order['no'],
            'amount' => $order['amount'],
            'product_name' => $order['product_name'] ?? '订单支付'
        ];
    }
    
    /**
     * 处理支付宝官方支付
     */
    private function processAlipayOfficialPayment(array $order, array $settings, string $baseUrl): array
    {
        // 检查支付宝官方配置
        if (empty($settings['pay_alipay_app_id']) || empty($settings['pay_alipay_private_key']) || empty($settings['pay_alipay_public_key'])) {
            error('支付宝官方接口未配置完整，请在后台设置AppId、应用私钥和支付宝公钥');
        }
        
        $alipay = new \App\Services\AlipayService([
            'app_id' => $settings['pay_alipay_app_id'],
            'private_key' => $settings['pay_alipay_private_key'],
            'alipay_public_key' => $settings['pay_alipay_public_key'],
            'notify_url' => $baseUrl . '/pay/alipay/notify',
            'return_url' => $baseUrl . '/pay/alipay/return?order_no=' . $order['no'],
        ]);
        
        // 当面付（扫码支付）- 返回二维码数据
        $result = $alipay->createQrPayOrder(
            $order['no'],
            $order['product_name'] ?? '订单支付',
            (float)$order['amount']
        );
        
        if (!$result['success']) {
            error($result['message']);
        }
        
        return [
            'type' => 'qrcode',
            'pay_type' => 'alipay',
            'code_url' => $result['qr_code'],
            'order_no' => $order['no'],
            'amount' => $order['amount'],
            'product_name' => $order['product_name'] ?? '订单支付'
        ];
    }
    
    /**
     * 处理QQ钱包官方支付
     */
    private function processQqpayOfficialPayment(array $order, array $settings, string $baseUrl): array
    {
        // 检查QQ钱包官方配置
        if (empty($settings['pay_qqpay_mch_id']) || empty($settings['pay_qqpay_api_key'])) {
            error('QQ钱包官方接口未配置完整，请在后台设置商户号和API密钥');
        }
        
        $qqpay = new \App\Services\QqpayService([
            'mch_id' => $settings['pay_qqpay_mch_id'],
            'api_key' => $settings['pay_qqpay_api_key'],
            'notify_url' => $baseUrl . '/pay/qqpay/notify',
        ]);
        
        // Native支付（扫码支付）- 返回二维码数据
        $result = $qqpay->createNativeOrder(
            $order['no'],
            $order['product_name'] ?? '订单支付',
            (float)$order['amount']
        );
        
        if (!$result['success']) {
            error($result['message']);
        }
        
        return [
            'type' => 'qrcode',
            'pay_type' => 'qqpay',
            'code_url' => $result['code_url'],
            'order_no' => $order['no'],
            'amount' => $order['amount'],
            'product_name' => $order['product_name'] ?? '订单支付'
        ];
    }
    
    /**
     * 我的订单
     */
    public function orders()
    {
        $page = max(1, (int) $this->input('page', 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $total = $this->db->fetch(
            "SELECT COUNT(*) as count FROM {$this->db->getPrefix()}orders WHERE `user` = ?",
            [$this->user['id']]
        )['count'];

        $orders = $this->db->fetchAll(
            "SELECT * FROM {$this->db->getPrefix()}orders 
             WHERE `user` = ? ORDER BY `time` DESC LIMIT {$limit} OFFSET {$offset}",
            [$this->user['id']]
        );
        $activePackages = $this->db->fetchAll(
            "SELECT * FROM {$this->db->getPrefix()}user_packages 
             WHERE `user` = ? AND status = 1 AND expire_time > NOW()
             ORDER BY expire_time DESC",
            [$this->user['id']]
        );

        $this->view('user/orders', [
            'orders' => $orders,
            'activePackages' => $activePackages,
            'total' => $total,
            'page' => $page,
            'totalPages' => ceil($total / $limit),
            'csrf_token' => $this->generateCsrf(),
        ]);
    }

    /**
     * 取消订单
     */
    public function cancelOrder()
    {
        $this->verifyCsrf();

        $orderNo = $this->input('order_no', '');

        $order = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}orders WHERE no = ? AND `user` = ? AND status = 0",
            [$orderNo, $this->user['id']]
        );

        if (!$order) {
            error('订单不存在或无法取消');
        }

        $this->db->update('orders', ['status' => 2], 'id = ?', [$order['id']]);

        success(null, '订单已取消');
    }

    /**
     * 身份认证页面
     */
    public function verification()
    {
        $config = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}verification_config WHERE id = 1"
        );
        if (!$config || !$config['status']) {
            $this->view('user/verification_disabled', [
                'csrf_token' => $this->generateCsrf(),
            ]);
            return;
        }
        $verification = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}user_verifications WHERE `user` = ? ORDER BY `time` DESC LIMIT 1",
            [$this->user['id']]
        );
        $settings = $this->getSettings();
        $rewardProduct = null;
        if (!empty($config['reward']) && !empty($config['reward_product_id'])) {
            $rewardProduct = $this->db->fetch(
                "SELECT id, name, type, description FROM {$this->db->getPrefix()}products WHERE id = ? AND status = 1",
                [$config['reward_product_id']]
            );
        }
        
        // 获取可用支付方式
        $payMethods = [];
        if (($settings['pay_alipay_enabled'] ?? '0') == '1') $payMethods[] = 'alipay';
        if (($settings['pay_wechat_enabled'] ?? '0') == '1') $payMethods[] = 'wechat';
        if (($settings['pay_qqpay_enabled'] ?? '0') == '1') $payMethods[] = 'qqpay';
        
        $GLOBALS['verificationConfig'] = $config;
        $GLOBALS['settings'] = $settings;

        $this->view('user/verification', [
            'config' => $config,
            'verification' => $verification,
            'rewardProduct' => $rewardProduct,
            'payMethods' => $payMethods,
            'csrf_token' => $this->generateCsrf(),
        ]);
    }

    /**
     * 提交个人认证
     * 
     * Requirements: 5.1, 5.3, 5.4, 5.5, 6.1
     */
    public function submitPersonalVerification()
    {
        try {
            $this->verifyCsrf();
            $config = $this->db->fetch(
                "SELECT * FROM {$this->db->getPrefix()}verification_config WHERE id = 1"
            );

            if (!$config || !$config['status'] || !$config['personal_status']) {
                error('个人认证功能未启用');
            }
            $existing = $this->db->fetch(
                "SELECT * FROM {$this->db->getPrefix()}user_verifications WHERE `user` = ? AND status IN (0, 1, 3)",
                [$this->user['id']]
            );

            if ($existing) {
                if ($existing['status'] == 1) {
                    error('您已完成认证');
                } else {
                    error('您有待审核的认证申请');
                }
            }

            $realName = trim($this->input('real_name', ''));
            $idCardNumber = trim($this->input('id_card_number', ''));
            $mobile = trim($this->input('mobile', ''));
            $needPay = $this->input('need_pay', '') === '1';
            $payMethod = $this->input('pay_method', '');

            if (empty($realName) || empty($idCardNumber)) {
                error('请填写完整的认证信息');
            }
            if (!preg_match('/^\d{17}[\dXx]$/', $idCardNumber)) {
                error('身份证号格式不正确');
            }
            $idCardFront = $this->handleUpload('id_card_front');
            $idCardBack = $this->handleUpload('id_card_back');
            
            // 如果需要收费，创建订单并返回支付链接
            $feeAmount = (!empty($config['fee']) && $config['fee_amount'] > 0) ? (float)$config['fee_amount'] : 0;
            if ($feeAmount > 0 && $needPay) {
                if (!in_array($payMethod, ['alipay', 'wechat', 'qqpay'])) {
                    error('请选择支付方式');
                }
                
                $settings = $this->getSettings();
                $enabledKey = "pay_{$payMethod}_enabled";
                if (($settings[$enabledKey] ?? '0') != '1') {
                    error('该支付方式暂未开放');
                }
                
                // 先保存认证信息到session，支付成功后再处理
                $verificationData = [
                    'type' => 'personal',
                    'real_name' => $realName,
                    'id_card_number' => $idCardNumber,
                    'mobile' => $mobile,
                    'id_card_front' => $idCardFront,
                    'id_card_back' => $idCardBack,
                ];
                $_SESSION['pending_verification'] = $verificationData;
                
                // 创建认证订单
                $orderNo = 'VF' . date('YmdHis') . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
                $this->db->insert('orders', [
                    'no' => $orderNo,
                    'user' => $this->user['id'],
                    'product_id' => 0,
                    'product_name' => '身份认证费用',
                    'product_type' => 'verification',
                    'amount' => $feeAmount,
                    'method' => $payMethod,
                    'status' => 0,
                    'expire_time' => date('Y-m-d H:i:s', strtotime('+30 minutes')),
                    'snapshot' => json_encode($verificationData),
                ]);
                
                // 获取支付链接
                $order = [
                    'no' => $orderNo,
                    'product_name' => '身份认证费用',
                    'amount' => $feeAmount,
                ];
                
                $channelKey = 'pay_' . $payMethod . '_channel';
                $channel = $settings[$channelKey] ?? 'epay';
                
                if ($channel === 'official') {
                    $payUrl = $this->processOfficialPayment($order, $payMethod, $settings);
                } else {
                    $payUrl = $this->processEpayPayment($order, $payMethod, $settings);
                }
                
                success(['pay_url' => $payUrl, 'order_no' => $orderNo], '请完成支付');
                return;
            }
            
            // 无需支付，直接处理认证
            $this->processPersonalVerification($config, $realName, $idCardNumber, $mobile, $idCardFront, $idCardBack, 0);
            
        } catch (\Exception $e) {
            error('提交失败：' . $e->getMessage());
        }
    }
    
    /**
     * 处理个人认证逻辑
     */
    private function processPersonalVerification($config, $realName, $idCardNumber, $mobile, $idCardFront, $idCardBack, $feeCharged)
    {
        $carrierService = new CarrierVerificationService($this->db);
        if ($carrierService->isEnabled()) {
            if (empty($mobile)) {
                error('请输入手机号');
            }
            if (!preg_match('/^1\d{10}$/', $mobile)) {
                error('手机号格式不正确');
            }
            $verifyResult = $carrierService->verify($realName, $idCardNumber, $mobile);

            if (!$verifyResult['success']) {
                error($verifyResult['message'] ?? '认证服务暂不可用，请稍后重试');
            }

            if ($verifyResult['matched']) {
                $this->db->insert('user_verifications', [
                    'user' => $this->user['id'],
                    'type' => 'personal',
                    'status' => 1, // 已通过
                    'name' => $realName,
                    'id_card' => encrypt($idCardNumber),
                    'id_card_front' => $idCardFront,
                    'id_card_back' => $idCardBack,
                    'verify_mobile' => $mobile,
                    'carrier' => $verifyResult['carrier_type'] ?? '',
                    'verify_provider' => 'carrier_' . $carrierService->getProviderName(),
                    'verify_result' => json_encode($verifyResult['raw_data'] ?? []),
                    'verified_time' => date('Y-m-d H:i:s'),
                    'fee' => $feeCharged,
                    'reward' => 0,
                ]);

                $verificationId = $this->db->lastInsertId();
                $this->db->update('users', [
                    'verification' => 'personal',
                    'phone' => $mobile,
                ], 'id = ?', [$this->user['id']]);
                $rewardMessage = '';
                if (!empty($config['reward']) && !empty($config['reward_product_id'])) {
                    $rewardResult = $this->grantVerificationReward($verificationId, $config);
                    if ($rewardResult['success']) {
                        $rewardMessage = '，已获得' . $rewardResult['product_name'] . ' ' . $rewardResult['duration'] . '天使用权';
                    }
                }

                success(null, '认证通过' . $rewardMessage);
            } else {
                $this->db->insert('user_verifications', [
                    'user' => $this->user['id'],
                    'type' => 'personal',
                    'status' => 2, // 已拒绝
                    'name' => $realName,
                    'id_card' => encrypt($idCardNumber),
                    'id_card_front' => $idCardFront,
                    'id_card_back' => $idCardBack,
                    'verify_mobile' => $mobile,
                    'carrier' => $verifyResult['carrier_type'] ?? '',
                    'verify_provider' => 'carrier_' . $carrierService->getProviderName(),
                    'verify_result' => json_encode($verifyResult['raw_data'] ?? []),
                    'reason' => $verifyResult['message'] ?? '三要素认证不一致',
                    'fee' => $feeCharged,
                    'reward' => 0,
                ]);

                error($verifyResult['message'] ?? '三要素认证不一致，请检查您的姓名、身份证号和手机号是否匹配');
            }
            return;
        }
        $this->db->insert('user_verifications', [
            'user' => $this->user['id'],
            'type' => 'personal',
            'status' => 3, // 默认待人工审核
            'name' => $realName,
            'id_card' => encrypt($idCardNumber),
            'id_card_front' => $idCardFront,
            'id_card_back' => $idCardBack,
            'fee' => $feeCharged,
            'reward' => 0,
        ]);

        success(null, '认证信息已提交，请等待审核');
    }

    /**
     * 取消认证
     */
    public function cancelVerification()
    {
        $this->verifyCsrf();
        $verification = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}user_verifications WHERE `user` = ? AND status IN (0, 3) ORDER BY `time` DESC LIMIT 1",
            [$this->user['id']]
        );

        if (!$verification) {
            error('没有可取消的认证记录');
        }
        $this->db->delete('user_verifications', 'id = ?', [$verification['id']]);

        success(null, '认证已取消');
    }

    /**
     * 提交企业认证
     * 
     * Requirements: 5.1, 5.3, 5.4, 5.5, 6.1
     */
    public function submitEnterpriseVerification()
    {
        try {
            $this->verifyCsrf();
            $config = $this->db->fetch(
                "SELECT * FROM {$this->db->getPrefix()}verification_config WHERE id = 1"
            );

            if (!$config || !$config['status'] || !$config['enterprise_status']) {
                error('企业认证功能未启用');
            }
            $existing = $this->db->fetch(
                "SELECT * FROM {$this->db->getPrefix()}user_verifications WHERE `user` = ? AND status IN (0, 1, 3)",
                [$this->user['id']]
            );

            if ($existing) {
                if ($existing['status'] == 1) {
                    error('您已完成认证');
                } else {
                    error('您有待审核的认证申请');
                }
            }

            $companyName = trim($this->input('company_name', ''));
            $creditCode = trim($this->input('unified_social_credit_code', ''));
            $legalPersonName = trim($this->input('legal_person_name', ''));
            $legalPersonIdCard = trim($this->input('legal_person_id_card', ''));
            $legalPersonMobile = trim($this->input('legal_person_mobile', ''));
            $needPay = $this->input('need_pay', '') === '1';
            $payMethod = $this->input('pay_method', '');

            if (empty($companyName) || empty($creditCode) || empty($legalPersonName) || empty($legalPersonIdCard)) {
                error('请填写完整的企业认证信息');
            }
            if (!preg_match('/^[0-9A-Z]{18}$/', $creditCode)) {
                error('统一社会信用代码格式不正确');
            }
            if (!preg_match('/^\d{17}[\dXx]$/', $legalPersonIdCard)) {
                error('法人身份证号格式不正确');
            }
            $businessLicense = $this->handleUpload('business_license');
            
            // 如果需要收费，创建订单并返回支付链接
            $feeAmount = (!empty($config['fee']) && $config['fee_amount'] > 0) ? (float)$config['fee_amount'] : 0;
            if ($feeAmount > 0 && $needPay) {
                if (!in_array($payMethod, ['alipay', 'wechat', 'qqpay'])) {
                    error('请选择支付方式');
                }
                
                $settings = $this->getSettings();
                $enabledKey = "pay_{$payMethod}_enabled";
                if (($settings[$enabledKey] ?? '0') != '1') {
                    error('该支付方式暂未开放');
                }
                
                // 先保存认证信息到session，支付成功后再处理
                $verificationData = [
                    'type' => 'enterprise',
                    'company_name' => $companyName,
                    'credit_code' => $creditCode,
                    'legal_person_name' => $legalPersonName,
                    'legal_person_id_card' => $legalPersonIdCard,
                    'legal_person_mobile' => $legalPersonMobile,
                    'business_license' => $businessLicense,
                ];
                $_SESSION['pending_verification'] = $verificationData;
                
                // 创建认证订单
                $orderNo = 'VF' . date('YmdHis') . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
                $this->db->insert('orders', [
                    'no' => $orderNo,
                    'user' => $this->user['id'],
                    'product_id' => 0,
                    'product_name' => '企业认证费用',
                    'product_type' => 'verification',
                    'amount' => $feeAmount,
                    'method' => $payMethod,
                    'status' => 0,
                    'expire_time' => date('Y-m-d H:i:s', strtotime('+30 minutes')),
                    'snapshot' => json_encode($verificationData),
                ]);
                
                // 获取支付链接
                $order = [
                    'no' => $orderNo,
                    'product_name' => '企业认证费用',
                    'amount' => $feeAmount,
                ];
                
                $channelKey = 'pay_' . $payMethod . '_channel';
                $channel = $settings[$channelKey] ?? 'epay';
                
                if ($channel === 'official') {
                    $payUrl = $this->processOfficialPayment($order, $payMethod, $settings);
                } else {
                    $payUrl = $this->processEpayPayment($order, $payMethod, $settings);
                }
                
                success(['pay_url' => $payUrl, 'order_no' => $orderNo], '请完成支付');
                return;
            }
            
            // 无需支付，直接处理认证
            $this->processEnterpriseVerification($config, $companyName, $creditCode, $legalPersonName, $legalPersonIdCard, $legalPersonMobile, $businessLicense, 0);
            
        } catch (\Exception $e) {
            error('提交失败：' . $e->getMessage());
        }
    }
    
    /**
     * 处理企业认证逻辑
     */
    private function processEnterpriseVerification($config, $companyName, $creditCode, $legalPersonName, $legalPersonIdCard, $legalPersonMobile, $businessLicense, $feeCharged)
    {
        $carrierService = new CarrierVerificationService($this->db);
        if ($carrierService->isEnabled()) {
            if (empty($legalPersonMobile)) {
                error('请输入法人手机号');
            }
            if (!preg_match('/^1\d{10}$/', $legalPersonMobile)) {
                error('法人手机号格式不正确');
            }
            $verifyResult = $carrierService->verify($legalPersonName, $legalPersonIdCard, $legalPersonMobile);

            if (!$verifyResult['success']) {
                error($verifyResult['message'] ?? '认证服务暂不可用，请稍后重试');
            }

            if ($verifyResult['matched']) {
                $this->db->insert('user_verifications', [
                    'user' => $this->user['id'],
                    'type' => 'enterprise',
                    'status' => 1, // 已通过
                    'company' => $companyName,
                    'unified_social_credit_code' => $creditCode,
                    'license' => $businessLicense,
                    'legal_person_name' => $legalPersonName,
                    'legal_person_id_card' => encrypt($legalPersonIdCard),
                    'verify_mobile' => $legalPersonMobile,
                    'carrier' => $verifyResult['carrier_type'] ?? '',
                    'verify_provider' => 'carrier_' . $carrierService->getProviderName(),
                    'verify_result' => json_encode($verifyResult['raw_data'] ?? []),
                    'verified_time' => date('Y-m-d H:i:s'),
                    'fee' => $feeCharged,
                    'reward' => 0,
                ]);

                $verificationId = $this->db->lastInsertId();
                $this->db->update('users', [
                    'verification' => 'enterprise',
                    'phone' => $legalPersonMobile,
                ], 'id = ?', [$this->user['id']]);
                $rewardMessage = '';
                if (!empty($config['reward']) && !empty($config['reward_product_id'])) {
                    $rewardResult = $this->grantVerificationReward($verificationId, $config);
                    if ($rewardResult['success']) {
                        $rewardMessage = '，已获得' . $rewardResult['product_name'] . ' ' . $rewardResult['duration'] . '天使用权';
                    }
                }

                success(null, '企业认证通过' . $rewardMessage);
            } else {
                $this->db->insert('user_verifications', [
                    'user' => $this->user['id'],
                    'type' => 'enterprise',
                    'status' => 2, // 已拒绝
                    'company' => $companyName,
                    'unified_social_credit_code' => $creditCode,
                    'license' => $businessLicense,
                    'legal_person_name' => $legalPersonName,
                    'legal_person_id_card' => encrypt($legalPersonIdCard),
                    'verify_mobile' => $legalPersonMobile,
                    'carrier' => $verifyResult['carrier_type'] ?? '',
                    'verify_provider' => 'carrier_' . $carrierService->getProviderName(),
                    'verify_result' => json_encode($verifyResult['raw_data'] ?? []),
                    'reason' => $verifyResult['message'] ?? '法人三要素认证不一致',
                    'fee' => $feeCharged,
                    'reward' => 0,
                ]);

                error($verifyResult['message'] ?? '法人三要素认证不一致，请检查法人姓名、身份证号和手机号是否匹配');
            }
            return;
        }
        $this->db->insert('user_verifications', [
            'user' => $this->user['id'],
            'type' => 'enterprise',
            'status' => 3, // 默认待人工审核
            'company' => $companyName,
            'unified_social_credit_code' => $creditCode,
            'license' => $businessLicense,
            'legal_person_name' => $legalPersonName,
            'legal_person_id_card' => !empty($legalPersonIdCard) ? encrypt($legalPersonIdCard) : null,
            'fee' => $feeCharged,
            'reward' => 0,
        ]);

        success(null, '企业认证信息已提交，请等待审核');
    }

    /**
     * 处理文件上传
     */
    private function handleUpload($fieldName)
    {
        if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $file = $_FILES[$fieldName];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedTypes)) {
            throw new \Exception('只支持 JPG、PNG、GIF、WebP 格式的图片');
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            throw new \Exception('图片大小不能超过5MB');
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        if (empty($ext)) {
            $extMap = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
            $ext = $extMap[$mimeType] ?? 'jpg';
        }
        
        $filename = md5($this->user['id'] . time() . rand(1000, 9999)) . '.' . $ext;
        $uploadDir = ML_ROOT . '/storage/uploads/verification/' . $this->user['id'];
        
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                throw new \Exception('创建上传目录失败');
            }
        }

        $targetPath = $uploadDir . '/' . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new \Exception('文件上传失败');
        }

        return '/storage/uploads/verification/' . $this->user['id'] . '/' . $filename;
    }

    /**
     * 获取系统设置
     */
    private function getSettings(): array
    {
        $row = $this->db->fetch("SELECT * FROM {$this->db->getPrefix()}settings WHERE id = 1");
        return $row ?: [];
    }

    /**
     * 检查用户余额是否充足
     * 
     * @param int $userId 用户ID
     * @param float $amount 需要的金额
     * @return array ['sufficient' => bool, 'balance' => float, 'required' => float]
     * 
     * Requirements: 5.1, 5.2
     */
    public function checkBalance(int $userId, float $amount): array
    {
        $user = $this->db->fetch(
            "SELECT balance FROM {$this->db->getPrefix()}users WHERE id = ?",
            [$userId]
        );

        $balance = (float)($user['balance'] ?? 0);

        return [
            'sufficient' => $balance >= $amount,
            'balance' => $balance,
            'required' => $amount
        ];
    }

    /**
     * 扣除用户余额
     * 
     * @param int $userId 用户ID
     * @param float $amount 扣除金额
     * @param string $reason 扣款原因
     * @return array ['success' => bool, 'message' => string, 'new_balance' => float|null]
     * 
     * Requirements: 5.1, 5.2, 5.3
     */
    public function deductBalance(int $userId, float $amount, string $reason = ''): array
    {
        if ($amount <= 0) {
            return [
                'success' => false,
                'message' => '扣款金额必须大于0',
                'new_balance' => null
            ];
        }
        $balanceCheck = $this->checkBalance($userId, $amount);
        if (!$balanceCheck['sufficient']) {
            return [
                'success' => false,
                'message' => '余额不足，请先充值。当前余额：¥' . number_format($balanceCheck['balance'], 2) . '，需要：¥' . number_format($amount, 2),
                'new_balance' => null
            ];
        }
        $result = $this->db->query(
            "UPDATE {$this->db->getPrefix()}users SET balance = balance - ? WHERE id = ? AND balance >= ?",
            [$amount, $userId, $amount]
        );
        if ($result->rowCount() === 0) {
            return [
                'success' => false,
                'message' => '扣款失败，余额不足或用户不存在',
                'new_balance' => null
            ];
        }
        $user = $this->db->fetch(
            "SELECT balance FROM {$this->db->getPrefix()}users WHERE id = ?",
            [$userId]
        );
        $newBalance = (float)($user['balance'] ?? 0);

        return [
            'success' => true,
            'message' => '扣款成功',
            'new_balance' => $newBalance,
            'deducted_amount' => $amount,
            'reason' => $reason
        ];
    }

    /**
     * 退还用户余额
     * 
     * @param int $userId 用户ID
     * @param float $amount 退还金额
     * @param string $reason 退款原因
     * @return array ['success' => bool, 'message' => string, 'new_balance' => float|null]
     * 
     * Requirements: 5.5
     */
    public function refundBalance(int $userId, float $amount, string $reason = ''): array
    {
        if ($amount <= 0) {
            return [
                'success' => false,
                'message' => '退款金额必须大于0',
                'new_balance' => null
            ];
        }
        $user = $this->db->fetch(
            "SELECT id, balance FROM {$this->db->getPrefix()}users WHERE id = ?",
            [$userId]
        );

        if (!$user) {
            return [
                'success' => false,
                'message' => '用户不存在',
                'new_balance' => null
            ];
        }
        $this->db->query(
            "UPDATE {$this->db->getPrefix()}users SET balance = balance + ? WHERE id = ?",
            [$amount, $userId]
        );
        $user = $this->db->fetch(
            "SELECT balance FROM {$this->db->getPrefix()}users WHERE id = ?",
            [$userId]
        );
        $newBalance = (float)($user['balance'] ?? 0);

        return [
            'success' => true,
            'message' => '退款成功',
            'new_balance' => $newBalance,
            'refunded_amount' => $amount,
            'reason' => $reason
        ];
    }

    /**
     * 发放认证奖励
     * 
     * 认证成功后，根据配置发放套餐奖励
     * 
     * @param int $verificationId 认证记录ID
     * @param array $config 认证配置（包含 reward, reward_product_id, reward_duration）
     * @return array ['success' => bool, 'message' => string, 'package_id' => int|null]
     * 
     * Requirements: 6.1, 6.2, 6.3, 6.4
     */
    public function grantVerificationReward(int $verificationId, array $config): array
    {
        if (empty($config['reward']) || empty($config['reward_product_id'])) {
            return [
                'success' => false,
                'message' => '认证奖励未启用或未配置奖励产品',
                'package_id' => null
            ];
        }
        $product = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}products WHERE id = ? AND status = 1",
            [$config['reward_product_id']]
        );

        if (!$product) {
            return [
                'success' => false,
                'message' => '奖励产品不存在或已下架',
                'package_id' => null
            ];
        }
        $billingService = new BillingService($this->db);
        $productInfo = [
            'id' => $product['id'],
            'name' => $product['name'],
            'type' => $product['type'],
            'platforms' => $product['platforms'],
            'duration' => (int)($config['reward_duration'] ?? 30), // 使用配置的奖励有效期
            'total_quota' => $product['total_quota'],
            'account_limit' => $product['account_limit'],
        ];
        $packageId = $billingService->activatePackage(
            $this->user['id'],
            0, // order_id = 0 表示非订单来源
            $productInfo,
            null, // operator_id
            '身份认证奖励'
        );
        $billingService->recordPackageHistory(
            $this->user['id'],
            $packageId,
            'verification_reward',
            null, // old_package_id
            null, // operator_id
            '身份认证成功奖励'
        );
        $this->db->update('user_verifications', [
            'reward' => 1,
            'reward_package_id' => $packageId,
        ], 'id = ?', [$verificationId]);

        return [
            'success' => true,
            'message' => '认证奖励发放成功',
            'package_id' => $packageId,
            'product_name' => $product['name'],
            'duration' => $productInfo['duration']
        ];
    }
}
