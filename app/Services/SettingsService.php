<?php
/**
 * 系统设置服务
 * 将键值对方式改为固定字段方式
 */

namespace App\Services;

use Core\Database;

class SettingsService
{
    private static ?self $instance = null;
    private Database $db;
    private ?array $cache = null;
    
    private function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 获取所有设置（兼容旧的键值对方式）
     */
    public function getAll(): array
    {
        if ($this->cache !== null) {
            return $this->cache;
        }
        
        $row = $this->db->fetch("SELECT * FROM {$this->db->getPrefix()}settings WHERE id = 1");
        
        if (!$row) {
            $this->initDefault();
            $row = $this->db->fetch("SELECT * FROM {$this->db->getPrefix()}settings WHERE id = 1");
        }
        unset($row['id'], $row['time'], $row['last_time']);
        
        $this->cache = $row;
        return $this->cache;
    }
    
    /**
     * 获取单个设置值
     */
    public function get(string $key, $default = null)
    {
        $settings = $this->getAll();
        return $settings[$key] ?? $default;
    }
    
    /**
     * 更新设置
     */
    public function update(array $data): bool
    {
        $allowedFields = $this->getAllowedFields();
        $updateData = [];
        
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $updateData[$key] = $value;
            }
        }
        
        if (empty($updateData)) {
            return false;
        }
        
        $this->db->update('settings', $updateData, 'id = 1');
        $this->cache = null; // 清除缓存
        
        return true;
    }
    
    /**
     * 设置单个值
     */
    public function set(string $key, $value): bool
    {
        return $this->update([$key => $value]);
    }
    
    /**
     * 获取站点基本设置（用于视图）
     */
    public function getSiteSettings(): array
    {
        $all = $this->getAll();
        return [
            'site_name' => $all['site_name'] ?? '星聚合登录',
            'site_url' => $all['site_url'] ?? '',
            'site_description' => $all['site_description'] ?? '',
            'site_keywords' => $all['site_keywords'] ?? '',
            'site_icp' => $all['site_icp'] ?? '',
        ];
    }
    
    /**
     * 获取允许的字段列表
     */
    private function getAllowedFields(): array
    {
        return [
            'site_name', 'site_url', 'site_description', 'site_keywords', 'site_icp',
            'site_logo', 'site_favicon',
            'admin_email', 'service_url', 'homepage_redirect',
            'enable_register', 'register_verify_method',
            'api_version', 'default_daily_limit',
            'billing_free_enabled', 'billing_free_daily_limit', 'billing_free_platforms',
            'billing_rate_limit_default', 'billing_rate_limit_package', 'billing_rate_limit_quota',
            'pay_epay_enabled', 'pay_epay_api_url', 'pay_epay_pid', 'pay_epay_key',
            'pay_alipay_enabled', 'pay_alipay_channel', 'pay_alipay_app_id',
            'pay_alipay_private_key', 'pay_alipay_public_key',
            'pay_wechat_enabled', 'pay_wechat_channel', 'pay_wechat_app_id',
            'pay_wechat_mch_id', 'pay_wechat_api_key',
            'pay_qqpay_enabled', 'pay_qqpay_channel', 'pay_qqpay_mch_id', 'pay_qqpay_api_key',
            'sms_provider',
            'sms_aliyun_access_key_id', 'sms_aliyun_access_key_secret',
            'sms_aliyun_sign_name', 'sms_aliyun_template_code', 'sms_aliyun_template_content',
            'sms_tencent_secret_id', 'sms_tencent_secret_key', 'sms_tencent_sdk_app_id',
            'sms_tencent_sign_name', 'sms_tencent_template_id', 'sms_tencent_template_content',
            'smtp_host', 'smtp_port', 'smtp_encryption', 'smtp_username', 'smtp_password', 'smtp_from_name',
            'security_ip_whitelist', 'security_ip_blacklist',
            'security_email_mode', 'security_email_list',
            'security_region_enabled', 'security_region_mode', 'security_region_list',
        ];
    }
    
    /**
     * 初始化默认设置
     */
    private function initDefault(): void
    {
        $this->db->query("INSERT INTO {$this->db->getPrefix()}settings (id) VALUES (1) ON DUPLICATE KEY UPDATE id = 1");
    }
    
    /**
     * 清除缓存
     */
    public function clearCache(): void
    {
        $this->cache = null;
    }
}
