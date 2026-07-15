<?php
namespace App\Controllers;

use Core\Database;

/**
 * 基础控制器
 */
class BaseController
{
    protected $db;
    protected $user = null;
    protected $admin = null;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->loadUser();
        $this->loadAdmin();
    }

    /**
     * 加载当前用户
     */
    protected function loadUser()
    {
        if (isset($_SESSION['user_id'])) {
            $this->user = $this->db->fetch(
                "SELECT * FROM {$this->db->getPrefix()}users WHERE id = ?",
                [$_SESSION['user_id']]
            );
        }
    }

    /**
     * 加载当前管理员
     */
    protected function loadAdmin()
    {
        if (isset($_SESSION['admin_id'])) {
            $this->admin = $this->db->fetch(
                "SELECT * FROM {$this->db->getPrefix()}users WHERE id = ? AND role = 'admin'",
                [$_SESSION['admin_id']]
            );
        }
    }

    /**
     * 渲染视图
     */
    protected function view($template, $data = [])
    {
        $siteSettings = $this->getSiteSettings();
        $data['siteSettings'] = $siteSettings;
        $isPartialRequest = !empty($_SERVER['HTTP_X_PARTIAL']);
        $GLOBALS['_PARTIAL_REQUEST'] = $isPartialRequest;
        
        extract($data);
        $user = $this->user;
        $admin = $this->admin;
        
        $templateFile = ML_ROOT . '/views/' . $template . '.php';
        if (!file_exists($templateFile)) {
            error('模板不存在: ' . $template, 500, 500);
        }

        include $templateFile;
    }

    /**
     * 获取网站设置
     */
    protected function getSiteSettings()
    {
        static $settings = null;
        if ($settings === null) {
            try {
                $row = $this->db->fetch(
                    "SELECT * FROM {$this->db->getPrefix()}settings WHERE id = 1"
                );
                $settings = $row ?: [];
            } catch (\Exception $e) {
                $settings = [];
            }
            $defaults = [
                'site_name' => '星聚合登录',
                'site_url' => '',
                'site_description' => '',
                'site_keywords' => '',
                'site_icp' => '',
                'site_logo' => '/assets/logo.png',
                'site_favicon' => '/assets/favicon.ico',
            ];
            
            foreach ($defaults as $key => $value) {
                if (!isset($settings[$key]) || $settings[$key] === null || $settings[$key] === '') {
                    $settings[$key] = $value;
                }
            }
        }
        return $settings;
    }

    /**
     * 获取输入
     */
    protected function input($key, $default = null)
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    /**
     * 验证CSRF
     */
    protected function verifyCsrf()
    {
        $token = $this->input('_token');
        if (!$token || $token !== ($_SESSION['csrf_token'] ?? '')) {
            error('CSRF验证失败', 403, 403);
        }
    }

    /**
     * 生成CSRF Token
     */
    protected function generateCsrf()
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = random_string(32);
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * 记录用户登录日志
     */
    protected function recordLoginLog($userId, $loginType = 'password')
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $device = 'pc';
        $browser = 'Unknown';
        $os = 'Unknown';
        if (preg_match('/Mobile|Android|iPhone|iPad/i', $userAgent)) {
            $device = preg_match('/iPad|Tablet/i', $userAgent) ? 'tablet' : 'mobile';
        }
        if (preg_match('/Chrome\/[\d.]+/i', $userAgent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/Firefox\/[\d.]+/i', $userAgent)) {
            $browser = 'Firefox';
        } elseif (preg_match('/Safari\/[\d.]+/i', $userAgent) && !preg_match('/Chrome/i', $userAgent)) {
            $browser = 'Safari';
        } elseif (preg_match('/Edge\/[\d.]+/i', $userAgent)) {
            $browser = 'Edge';
        } elseif (preg_match('/MSIE|Trident/i', $userAgent)) {
            $browser = 'IE';
        }
        if (preg_match('/Windows NT/i', $userAgent)) {
            $os = 'Windows';
        } elseif (preg_match('/Mac OS X/i', $userAgent)) {
            $os = 'macOS';
        } elseif (preg_match('/Linux/i', $userAgent)) {
            $os = 'Linux';
        } elseif (preg_match('/Android/i', $userAgent)) {
            $os = 'Android';
        } elseif (preg_match('/iPhone|iPad/i', $userAgent)) {
            $os = 'iOS';
        }

        try {
            $this->db->insert('user_login_logs', [
                'user' => $userId,
                'type' => $loginType,
                'ip' => get_client_ip(),
                'agent' => substr($userAgent, 0, 500),
                'device' => $device,
                'browser' => $browser,
                'os' => $os,
                'status' => 1,
            ]);
        } catch (\Exception $e) {
        }
    }
}
