<?php
/**
 * 输入验证器
 * 负责验证安装向导的用户输入
 * 
 * Requirements: 4.3, 4.4, 5.4, 5.5
 */

namespace App\Services;

class InputValidator
{
    /**
     * 验证安装表单数据
     * 
     * @param array $data 表单数据
     * @return array{valid: bool, errors: array<string>}
     * 
     * Requirements: 4.3, 5.4, 5.5
     */
    public function validateInstallForm(array $data): array
    {
        $errors = [];
        if (empty($data['site']['url'] ?? '')) {
            $errors[] = '站点地址不能为空';
        }
        
        if (empty($data['site']['name'] ?? '')) {
            $errors[] = '站点名称不能为空';
        }
        if (empty($data['database']['host'] ?? '')) {
            $errors[] = '数据库主机不能为空';
        }
        
        if (empty($data['database']['name'] ?? '')) {
            $errors[] = '数据库名称不能为空';
        }
        
        if (empty($data['database']['user'] ?? '')) {
            $errors[] = '数据库用户名不能为空';
        }
        if (empty($data['admin']['username'] ?? '')) {
            $errors[] = '管理员用户名不能为空';
        }
        
        $adminEmail = $data['admin']['email'] ?? '';
        if (empty($adminEmail)) {
            $errors[] = '管理员邮箱不能为空';
        } elseif (!$this->validateEmail($adminEmail)) {
            $errors[] = '管理员邮箱格式不正确';
        }
        
        $adminPassword = $data['admin']['password'] ?? '';
        if (empty($adminPassword)) {
            $errors[] = '管理员密码不能为空';
        } elseif (!$this->validatePassword($adminPassword)) {
            $errors[] = '管理员密码至少需要6位';
        }
        $adminPath = $data['admin']['path'] ?? 'admin';
        if (!$this->validateAdminPath($adminPath)) {
            $errors[] = '后台路径格式不正确，必须以字母开头，仅支持字母、数字、下划线和横线';
        }
        
        // 特殊版本：跳过授权码验证
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * 验证后台路径
     * 
     * @param string $path 路径
     * @return bool
     */
    public function validateAdminPath(string $path): bool
    {
        if (empty($path) || mb_strlen($path) < 2 || mb_strlen($path) > 32) {
            return false;
        }
        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_-]*$/', $path)) {
            return false;
        }
        $reserved = ['user', 'api', 'oauth', 'auth', 'install', 'storage', 'assets', 'public', 'connect', 'return', 'pay', 'document'];
        if (in_array(strtolower($path), $reserved)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * 验证邮箱格式
     * 
     * @param string $email 邮箱地址
     * @return bool
     * 
     * Requirements: 5.4
     */
    public function validateEmail(string $email): bool
    {
        if (empty($email)) {
            return false;
        }
        
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * 验证密码强度
     * 
     * @param string $password 密码
     * @param int $minLength 最小长度
     * @return bool
     * 
     * Requirements: 5.5
     */
    public function validatePassword(string $password, int $minLength = 6): bool
    {
        if (empty($password)) {
            return false;
        }
        
        return mb_strlen($password) >= $minLength;
    }
    
    /**
     * 规范化URL（去除末尾斜杠）
     * 
     * @param string $url URL地址
     * @return string
     * 
     * Requirements: 4.4
     */
    public function normalizeUrl(string $url): string
    {
        if (empty($url)) {
            return '';
        }
        return rtrim($url, '/');
    }
}
