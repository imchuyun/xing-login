<?php
/**
 * 系统安装控制器
 * 
 * 负责处理安装相关的所有HTTP请求
 * 使用服务类实现环境检测、数据库初始化、配置生成和输入验证
 * 
 * Requirements: 1.1, 1.2, 1.3, 2.4, 2.5, 3.4, 3.5, 8.1, 8.3, 8.4
 */

namespace App\Controllers;

use App\Services\EnvironmentChecker;
use App\Services\DatabaseInitializer;
use App\Services\ConfigGenerator;
use App\Services\InputValidator;

class InstallController
{
    /**
     * 环境检测器
     * @var EnvironmentChecker
     */
    protected EnvironmentChecker $environmentChecker;
    
    /**
     * 数据库初始化器
     * @var DatabaseInitializer
     */
    protected DatabaseInitializer $databaseInitializer;
    
    /**
     * 配置文件生成器
     * @var ConfigGenerator
     */
    protected ConfigGenerator $configGenerator;
    
    /**
     * 输入验证器
     * @var InputValidator
     */
    protected InputValidator $inputValidator;
    
    /**
     * 构造函数 - 注入服务类
     */
    public function __construct()
    {
        $this->environmentChecker = new EnvironmentChecker();
        $this->databaseInitializer = new DatabaseInitializer();
        $this->configGenerator = new ConfigGenerator();
        $this->inputValidator = new InputValidator();
    }

    /**
     * 检查系统是否已安装
     * 
     * @return bool
     * 
     * Requirements: 1.1, 1.2
     */
    public static function isInstalled(): bool
    {
        return file_exists(ML_ROOT . '/config/install.lock');
    }

    /**
     * 显示安装向导页面
     * 检测环境并渲染安装界面
     * 
     * Requirements: 1.3, 2.4, 2.5
     */
    public function index(): void
    {
        if (self::isInstalled()) {
            \redirect('/');
        }
        $requirements = $this->environmentChecker->checkAll();
        $allPassed = $this->environmentChecker->allPassed();
        
        \view('install/index', [
            'pageTitle' => '系统安装',
            'requirements' => $requirements,
            'allPassed' => $allPassed
        ]);
    }

    /**
     * 测试数据库连接
     * 
     * @return void 返回JSON响应
     * 
     * Requirements: 3.4, 3.5
     */
    public function testDb(): void
    {
        if (self::isInstalled()) {
            \error('系统已安装');
        }
        $config = [
            'host' => $_POST['db_host'] ?? 'localhost',
            'port' => (int)($_POST['db_port'] ?? 3306),
            'name' => $_POST['db_name'] ?? '',
            'user' => $_POST['db_user'] ?? '',
            'pass' => $_POST['db_pass'] ?? '',
        ];
        $result = $this->databaseInitializer->testConnection($config);

        if ($result['success']) {
            \success(null, $result['message']);
        } else {
            \error($result['message']);
        }
    }

    /**
     * 获取公钥证书
     */
    public function fetchPublicKey(): void
    {
        if (self::isInstalled()) {
            \error('系统已安装');
        }
        
        $publicKeyPath = ML_ROOT . '/app/Services/publickey.pem';
        $result = $this->downloadPublicKey($publicKeyPath);
        
        if ($result['success']) {
            \success(null, '公钥获取成功');
        } else {
            \error($result['message']);
        }
    }
    
    /**
     * 获取应用ID（供前端使用）
     */
    public static function getAppId(): string
    {
        return 'ThzwH9RXDxAG0pndBH';
    }

    /**
     * 验证授权码绑定
     * 特殊版本：跳过授权码验证
     */
    public function verifyLicense(): void
    {
        if (self::isInstalled()) {
            \error('系统已安装');
        }
        
        // 特殊版本：直接返回成功，不验证授权码
        \success([
            'license_key' => 'SPECIAL-VERSION-KEY',
            'expires_at' => null,
            'remaining_days' => null
        ], '授权验证成功');
    }
    
    /**
     * 向授权服务器验证授权码
     * 根据开发者API文档 v2.0.0 实现
     */
    protected function verifyLicenseWithServer(string $licenseCode, array $uploadParams, string $domain): array
    {
        $publicKeyPath = ML_ROOT . '/app/Services/publickey.pem';
        $appId = self::getAppId();
        
        if (!file_exists($publicKeyPath)) {
            $fetchResult = $this->downloadPublicKey($publicKeyPath);
            if (!$fetchResult['success']) {
                return [
                    'valid' => false,
                    'message' => $fetchResult['message']
                ];
            }
        }
        
        $publicKeyContent = file_get_contents($publicKeyPath);
        $publicKey = openssl_pkey_get_public($publicKeyContent);
        
        if ($publicKey === false) {
            $fetchResult = $this->downloadPublicKey($publicKeyPath);
            if (!$fetchResult['success']) {
                return [
                    'valid' => false,
                    'message' => '公钥加载失败'
                ];
            }
            $publicKeyContent = file_get_contents($publicKeyPath);
            $publicKey = openssl_pkey_get_public($publicKeyContent);
            if ($publicKey === false) {
                return [
                    'valid' => false,
                    'message' => '公钥格式无效: ' . openssl_error_string()
                ];
            }
        }
        
        // 构建载荷（根据新API文档）
        $payload = [
            'license_key' => $licenseCode,
            'verify_type' => 'domain',
            'verify_value' => $domain,
            'timestamp' => time(),
            'current_version' => '1.0.0',
            'upload_params' => $uploadParams
        ];
        
        $jsonPayload = json_encode($payload, JSON_UNESCAPED_UNICODE);
        
        // RSA 2048位密钥，PKCS#1 v1.5 填充，最大加密长度 245 字节
        $maxLength = 245;
        $dataLength = strlen($jsonPayload);
        
        if ($dataLength <= $maxLength) {
            $encrypted = '';
            $result = openssl_public_encrypt($jsonPayload, $encrypted, $publicKey, OPENSSL_PKCS1_PADDING);
            
            if ($result === false) {
                return [
                    'valid' => false,
                    'message' => '加密失败: ' . openssl_error_string()
                ];
            }
            $encryptedPayload = base64_encode($encrypted);
        } else {
            $encryptedChunks = [];
            $offset = 0;
            
            while ($offset < $dataLength) {
                $chunk = substr($jsonPayload, $offset, $maxLength);
                $encrypted = '';
                $result = openssl_public_encrypt($chunk, $encrypted, $publicKey, OPENSSL_PKCS1_PADDING);
                
                if ($result === false) {
                    return [
                        'valid' => false,
                        'message' => '分段加密失败: ' . openssl_error_string()
                    ];
                }
                $encryptedChunks[] = base64_encode($encrypted);
                $offset += $maxLength;
            }
            $encryptedPayload = implode('|', $encryptedChunks);
        }
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://software.xingqingchuang.com/api/v1/license/verify-encrypted',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode([
                'app_id' => $appId,
                'encrypted_payload' => $encryptedPayload
            ], JSON_UNESCAPED_SLASHES),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json'
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($response === false || !empty($curlError)) {
            return [
                'valid' => false,
                'message' => '网络请求失败: ' . $curlError
            ];
        }
        
        if ($httpCode !== 200) {
            return [
                'valid' => false,
                'message' => '服务器响应错误: HTTP ' . $httpCode
            ];
        }
        
        $result = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'valid' => false,
                'message' => '响应解析失败'
            ];
        }
        
        // 根据新API文档标准化响应
        $normalized = [
            'valid' => $result['valid'] ?? false,
            'code' => $result['code'] ?? -1,
            'message' => $result['message'] ?? '未知错误',
            'request_id' => $result['request_id'] ?? null
        ];
        
        // 处理 features 字段（顶层）
        if (isset($result['features']) && is_array($result['features'])) {
            $normalized['features'] = $result['features'];
        }
        
        // 处理 data 字段
        if (isset($result['data']) && is_array($result['data'])) {
            $data = $result['data'];
            $normalized['channel'] = $data['channel'] ?? null;
            $normalized['activated_at'] = $data['activated_at'] ?? null;
            $normalized['expires_at'] = $data['expires_at'] ?? null;
            $normalized['remaining_days'] = $data['remaining_days'] ?? null;
            $normalized['latest_version'] = $data['latest_version'] ?? null;
        }
        
        return $normalized;
    }
    
    /**
     * 从服务器获取公钥
     * 使用新的API端点 /api/v1/license/public-key
     */
    protected function downloadPublicKey(string $savePath): array
    {
        $appId = self::getAppId();
        $url = 'https://software.xingqingchuang.com/api/v1/app/public-key?app_id=' . $appId;
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER => ['Accept: application/json']
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($response === false || $httpCode !== 200) {
            return ['success' => false, 'message' => '公钥获取失败'];
        }
        
        $result = json_decode($response, true);
        // 根据新API文档，公钥在 public_key 字段
        $publicKeyContent = $result['public_key'] ?? null;
        
        if (empty($publicKeyContent)) {
            return ['success' => false, 'message' => '响应中无公钥数据'];
        }
        
        $dir = dirname($savePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        file_put_contents($savePath, $publicKeyContent);
        
        return ['success' => true];
    }

    /**
     * 执行安装
     * 
     * @return void 返回JSON响应
     * 
     * Requirements: 8.1, 8.3, 8.4
     */
    public function install(): void
    {
        if (self::isInstalled()) {
            \error('系统已安装');
        }
        $formData = $this->collectFormData();
        $validation = $this->inputValidator->validateInstallForm($formData);
        
        if (!$validation['valid']) {
            \error(implode('；', $validation['errors']));
        }
        $formData['site']['url'] = $this->inputValidator->normalizeUrl($formData['site']['url']);

        try {
            $dbResult = $this->databaseInitializer->testConnection($formData['database']);
            if (!$dbResult['success']) {
                \error($dbResult['message']);
            }
            $pdo = $this->databaseInitializer->createConnection($formData['database']);
            $this->databaseInitializer->initialize($pdo);
            $this->databaseInitializer->createAdmin($pdo, $formData['admin']);
            $this->databaseInitializer->insertDefaultSettings($pdo, [
                'name' => $formData['site']['name'],
                'url' => $formData['site']['url'],
                'admin_path' => $formData['admin']['path'],
                'admin_email' => $formData['admin']['email'],
            ]);
            $configContent = $this->configGenerator->generate($formData);
            $configPath = ML_ROOT . '/config/config.php';
            
            if (!$this->configGenerator->save($configContent, $configPath)) {
                \error('配置文件保存失败，请检查目录权限');
            }
            $this->createInstallLock();
            $this->deleteDirectory(ML_ROOT . '/database');
            $adminPath = $formData['admin']['path'] ?? 'admin';
            \success(['redirect' => '/' . $adminPath . '/login'], '安装成功！');

        } catch (\PDOException $e) {
            \error($this->formatPdoError($e));
        } catch (\Exception $e) {
            \error($this->formatInstallError($e));
        }
    }
    
    /**
     * 收集表单数据
     * 
     * @return array 结构化的表单数据
     */
    protected function collectFormData(): array
    {
        return [
            'site' => [
                'name' => $_POST['site_name'] ?? '星聚合登录',
                'url' => $_POST['site_url'] ?? '',
            ],
            'database' => [
                'host' => $_POST['db_host'] ?? 'localhost',
                'port' => (int)($_POST['db_port'] ?? 3306),
                'name' => $_POST['db_name'] ?? '',
                'user' => $_POST['db_user'] ?? '',
                'pass' => $_POST['db_pass'] ?? '',
            ],
            'admin' => [
                'username' => $_POST['admin_user'] ?? 'admin',
                'email' => $_POST['admin_email'] ?? '',
                'password' => $_POST['admin_pass'] ?? '',
                'path' => $_POST['admin_path'] ?? 'admin',
            ],
            'license' => [
                'code' => '',
            ],
        ];
    }
    
    /**
     * 创建安装锁定文件
     * 
     * @return void
     * 
     * Requirements: 8.1, 8.4
     */
    protected function createInstallLock(): void
    {
        $lockContent = "installed at " . date('Y-m-d H:i:s') . "\n";
        $lockContent .= "Version: 1.0.0\n";
        file_put_contents(ML_ROOT . '/config/install.lock', $lockContent);
    }

    /**
     * 递归删除目录
     * 
     * @param string $dir 目录路径
     * @return bool
     * 
     * Requirements: 8.3
     */
    protected function deleteDirectory(string $dir): bool
    {
        if (!is_dir($dir)) {
            return false;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->deleteDirectory($path) : @unlink($path);
        }
        
        return @rmdir($dir);
    }
    
    /**
     * 清理错误信息，移除敏感信息
     * 
     * @param string $message 原始错误信息
     * @return string 清理后的错误信息
     * 
     * Requirements: 10.4
     */
    protected function sanitizeErrorMessage(string $message): string
    {
        $message = preg_replace('/password[\'"]?\s*[=:]\s*[\'"]?[^\s\'"]+/i', 'password=***', $message);
        $message = preg_replace('/\/[a-zA-Z0-9_\-\.\/]+/', '[路径已隐藏]', $message);
        $message = preg_replace('/[A-Z]:\\\\[^\s]+/', '[路径已隐藏]', $message);
        $message = preg_replace('/\b(?!localhost)(\d{1,3}\.){3}\d{1,3}\b/', '[IP已隐藏]', $message);
        $message = preg_replace('/SQLSTATE\[[^\]]+\]\s*\[[^\]]*\]\s*/', '', $message);
        $message = preg_replace('/^PDOException:\s*/i', '', $message);
        
        return trim($message);
    }
    
    /**
     * 格式化安装错误信息，提供用户友好的提示
     * 
     * @param \Exception $e 异常对象
     * @return string 用户友好的错误信息
     * 
     * Requirements: 10.1, 10.2, 10.3
     */
    protected function formatInstallError(\Exception $e): string
    {
        $message = $e->getMessage();
        $code = $e->getCode();
        if ($e instanceof \PDOException) {
            return $this->formatPdoError($e);
        }
        if (strpos($message, 'Permission denied') !== false || 
            strpos($message, 'permission') !== false) {
            return '文件写入权限不足，请检查目录权限设置';
        }
        if (strpos($message, 'No such file') !== false || 
            strpos($message, 'not found') !== false ||
            strpos($message, '不存在') !== false) {
            return '所需文件不存在，请确保安装包完整';
        }
        return $this->sanitizeErrorMessage($message);
    }
    
    /**
     * 格式化PDO数据库错误信息
     * 
     * @param \PDOException $e PDO异常
     * @return string 用户友好的错误信息
     * 
     * Requirements: 10.2
     */
    protected function formatPdoError(\PDOException $e): string
    {
        $code = $e->getCode();
        $message = $e->getMessage();
        if ($code == 2002 || strpos($message, 'Connection refused') !== false) {
            return '无法连接到数据库服务器，请检查数据库主机地址和端口是否正确';
        }
        if ($code == 1045 || strpos($message, 'Access denied') !== false) {
            return '数据库用户名或密码错误，请检查后重试';
        }
        if ($code == 2005 || strpos($message, 'Unknown MySQL server host') !== false) {
            return '无法解析数据库主机地址，请检查主机名是否正确';
        }
        if ($code == 1049 || strpos($message, 'Unknown database') !== false) {
            return '指定的数据库不存在，系统将尝试自动创建';
        }
        if ($code == 1050 || strpos($message, 'already exists') !== false) {
            return '数据库表已存在，可能系统已经安装过。如需重新安装，请先清空数据库';
        }
        if ($code == 1044 || strpos($message, 'Access denied') !== false) {
            return '数据库权限不足，请确保用户具有创建表的权限';
        }
        if (strpos($message, 'timed out') !== false || strpos($message, 'timeout') !== false) {
            return '数据库连接超时，请检查网络连接或数据库服务器状态';
        }
        return '数据库操作失败: ' . $this->sanitizeErrorMessage($message);
    }
}
