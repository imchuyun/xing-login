<?php
/**
 * 配置文件生成器
 * 负责生成系统配置文件
 * 
 * Requirements: 7.1, 7.2, 7.3
 */

namespace App\Services;

class ConfigGenerator
{
    /**
     * 系统版本号
     */
    private const VERSION = '1.0.0';
    
    /**
     * 生成配置文件内容
     * 
     * @param array $config 配置数据
     * @return string 配置文件内容
     * 
     * Requirements: 7.1
     */
    public function generate(array $config): string
    {
        $encryptKey = $this->generateSecretKey(16);
        $jwtSecret = $this->generateSecretKey(16);
        $siteUrl = rtrim($config['site']['url'] ?? '', '/');
        $licenseCode = $config['license']['code'] ?? '';
        
        $content = <<<PHP
<?php
/**
 * Max Login 系统配置文件
 */
defined('ML_ROOT') or die('Access Denied');

return [
    'site' => [
        'name' => '{$this->escapeString($config['site']['name'] ?? '星聚合登录')}',
        'url' => '{$this->escapeString($siteUrl)}',
        'description' => '第三方聚合登录系统',
        'version' => '{$this->escapeString(self::VERSION)}',
    ],
    'database' => [
        'host' => '{$this->escapeString($config['database']['host'] ?? 'localhost')}',
        'port' => {$this->escapeInt($config['database']['port'] ?? 3306)},
        'name' => '{$this->escapeString($config['database']['name'] ?? '')}',
        'user' => '{$this->escapeString($config['database']['user'] ?? '')}',
        'pass' => '{$this->escapeString($config['database']['pass'] ?? '')}',
        'charset' => 'utf8mb4',
        'prefix' => '',
    ],
    'session' => [
        'name' => 'ML_SESSION',
        'lifetime' => 86400 * 30,
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => true,
    ],
    'api' => [
        'version' => 'v1',
        'rate_limit' => 100,
        'token_expire' => 7200,
    ],
    'security' => [
        'encrypt_key' => '{$encryptKey}',
        'jwt_secret' => '{$jwtSecret}',
        'password_cost' => 10,
    ],
    'license' => [
        'license_key' => '{$this->escapeString($licenseCode)}',
    ],
    'debug' => false,
];

PHP;
        
        return $content;
    }
    
    /**
     * 保存配置文件
     * 
     * @param string $content 配置内容
     * @param string $path 保存路径
     * @return bool
     * 
     * Requirements: 7.1
     */
    public function save(string $content, string $path): bool
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                return false;
            }
        }
        $result = file_put_contents($path, $content);
        
        return $result !== false;
    }
    
    /**
     * 生成随机密钥
     * 使用 random_bytes 确保密码学安全的随机性
     * 
     * @param int $length 密钥长度（字节）
     * @return string 十六进制密钥
     * 
     * Requirements: 7.2
     */
    public function generateSecretKey(int $length = 16): string
    {
        if ($length < 1) {
            $length = 16;
        }
        
        return bin2hex(random_bytes($length));
    }
    
    /**
     * 转义字符串用于PHP配置文件
     * 
     * @param string $value 原始值
     * @return string 转义后的值
     */
    private function escapeString(string $value): string
    {
        return addcslashes($value, "'\\");
    }
    
    /**
     * 确保值为整数
     * 
     * @param mixed $value 原始值
     * @return int 整数值
     */
    private function escapeInt($value): int
    {
        return (int) $value;
    }
}
