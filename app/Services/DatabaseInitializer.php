<?php
/**
 * 数据库初始化器
 * 负责数据库连接测试和表结构初始化
 * 
 * Requirements: 3.2, 3.3, 5.6, 6.1, 6.4
 */

namespace App\Services;

use PDO;
use PDOException;
use Exception;

class DatabaseInitializer
{
    /**
     * SQL文件路径
     */
    private const SQL_FILE_PATH = 'database/database.sql';
    
    /**
     * 测试数据库连接
     * 如果数据库不存在，尝试自动创建
     * 
     * @param array $config 数据库配置
     * @return array{success: bool, message: string}
     * 
     * Requirements: 3.2, 3.3
     */
    public function testConnection(array $config): array
    {
        $host = $config['host'] ?? 'localhost';
        $port = $config['port'] ?? 3306;
        $name = $config['name'] ?? '';
        $user = $config['user'] ?? '';
        $pass = $config['pass'] ?? '';
        if (empty($name)) {
            return ['success' => false, 'message' => '数据库名称不能为空'];
        }
        
        if (empty($user)) {
            return ['success' => false, 'message' => '数据库用户名不能为空'];
        }
        
        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            
            return ['success' => true, 'message' => '数据库连接成功'];
            
        } catch (PDOException $e) {
            if ($this->isDatabaseNotExistsError($e)) {
                return $this->createDatabase($config);
            }
            return [
                'success' => false, 
                'message' => $this->formatConnectionError($e)
            ];
        }
    }

    /**
     * 检查是否是数据库不存在的错误
     * 
     * @param PDOException $e
     * @return bool
     */
    private function isDatabaseNotExistsError(PDOException $e): bool
    {
        return $e->getCode() == 1049 || strpos($e->getMessage(), 'Unknown database') !== false;
    }
    
    /**
     * 创建数据库
     * 
     * @param array $config 数据库配置
     * @return array{success: bool, message: string}
     * 
     * Requirements: 3.3
     */
    private function createDatabase(array $config): array
    {
        $host = $config['host'] ?? 'localhost';
        $port = $config['port'] ?? 3306;
        $name = $config['name'] ?? '';
        $user = $config['user'] ?? '';
        $pass = $config['pass'] ?? '';
        
        try {
            $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
            $safeName = $this->escapeIdentifier($name);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS {$safeName} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            return ['success' => true, 'message' => '数据库连接成功（已自动创建数据库）'];
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => '数据库创建失败，请检查权限: ' . $this->sanitizeErrorMessage($e->getMessage())
            ];
        }
    }
    
    /**
     * 格式化连接错误信息
     * 
     * @param PDOException $e
     * @return string
     * 
     * Requirements: 10.2
     */
    private function formatConnectionError(PDOException $e): string
    {
        $code = $e->getCode();
        $message = $e->getMessage();
        if ($code == 2002 || strpos($message, 'Connection refused') !== false) {
            return '无法连接到数据库服务器，请检查主机地址和端口是否正确';
        }
        
        if ($code == 1045 || strpos($message, 'Access denied') !== false) {
            return '数据库用户名或密码错误，请检查后重试';
        }
        
        if ($code == 2005 || strpos($message, 'Unknown MySQL server host') !== false) {
            return '无法解析数据库主机地址，请检查主机名是否正确';
        }
        
        if (strpos($message, 'timed out') !== false || strpos($message, 'timeout') !== false) {
            return '数据库连接超时，请检查网络连接或数据库服务器状态';
        }
        
        if ($code == 1044) {
            return '数据库权限不足，请确保用户具有相应权限';
        }
        return '数据库连接失败: ' . $this->sanitizeErrorMessage($message);
    }
    
    /**
     * 清理错误信息，移除敏感信息
     * 
     * @param string $message
     * @return string
     * 
     * Requirements: 10.4
     */
    private function sanitizeErrorMessage(string $message): string
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
     * 初始化数据库
     * 执行SQL文件创建所有表结构
     * 
     * @param PDO $pdo 数据库连接
     * @return void
     * @throws Exception 初始化失败时抛出异常
     * 
     * Requirements: 6.1
     */
    public function initialize(PDO $pdo): void
    {
        $sqlFile = $this->getBasePath() . '/' . self::SQL_FILE_PATH;
        
        if (!file_exists($sqlFile)) {
            // 尝试使用 ML_ROOT 常量
            if (defined('ML_ROOT')) {
                $sqlFile = ML_ROOT . '/' . self::SQL_FILE_PATH;
            }
        }
        
        if (!file_exists($sqlFile)) {
            throw new Exception('数据库初始化文件不存在: ' . self::SQL_FILE_PATH);
        }
        
        $sql = file_get_contents($sqlFile);
        
        if ($sql === false) {
            throw new Exception('无法读取数据库初始化文件');
        }
        $statements = $this->splitSqlStatements($sql);
        
        if (empty($statements)) {
            throw new Exception('数据库初始化文件为空或格式错误');
        }
        
        try {
            $this->dropAllTables($pdo);
            
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement)) {
                    $pdo->exec($statement);
                }
            }
        } catch (PDOException $e) {
            throw new Exception('数据库初始化失败: ' . $this->sanitizeErrorMessage($e->getMessage()));
        }
    }
    
    /**
     * 删除数据库中所有表
     * 
     * @param PDO $pdo 数据库连接
     * @return void
     */
    private function dropAllTables(PDO $pdo): void
    {
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        $stmt = $pdo->query('SHOW TABLES');
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        foreach ($tables as $table) {
            $safeTable = $this->escapeIdentifier($table);
            $pdo->exec("DROP TABLE IF EXISTS {$safeTable}");
        }
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    }
    
    /**
     * 创建管理员账户
     * 
     * @param PDO $pdo 数据库连接
     * @param array $adminData 管理员信息
     * @return void
     * @throws Exception 创建失败时抛出异常
     * 
     * Requirements: 5.6
     */
    public function createAdmin(PDO $pdo, array $adminData): void
    {
        $username = $adminData['username'] ?? 'admin';
        $email = $adminData['email'] ?? '';
        $password = $adminData['password'] ?? '';
        
        if (empty($password)) {
            throw new Exception('管理员密码不能为空');
        }
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO users (username, email, password, role, status, `time`, last_time)
                VALUES (:username, :email, :password, 'admin', 'enable', NOW(), NOW())
            ");
            
            $stmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':password' => $hashedPassword,
            ]);
            
        } catch (PDOException $e) {
            throw new Exception('创建管理员账户失败: ' . $this->sanitizeErrorMessage($e->getMessage()));
        }
    }

    /**
     * 插入默认系统配置
     * 
     * @param PDO $pdo 数据库连接
     * @param array $siteConfig 站点配置
     * @return void
     * @throws Exception 插入失败时抛出异常
     * 
     * Requirements: 6.4
     */
    public function insertDefaultSettings(PDO $pdo, array $siteConfig): void
    {
        $siteName = $siteConfig['name'] ?? '星聚合登录';
        $siteUrl = rtrim($siteConfig['url'] ?? '', '/');
        $adminPath = $siteConfig['admin_path'] ?? 'admin';
        $adminEmail = $siteConfig['admin_email'] ?? '';
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO settings (id, site_name, site_url, site_description, admin_path, admin_email, enable_register)
                VALUES (1, :site_name, :site_url, :site_description, :admin_path, :admin_email, 1)
                ON DUPLICATE KEY UPDATE 
                    site_name = VALUES(site_name),
                    site_url = VALUES(site_url),
                    admin_path = VALUES(admin_path),
                    admin_email = VALUES(admin_email)
            ");
            
            $stmt->execute([
                ':site_name' => $siteName,
                ':site_url' => $siteUrl,
                ':site_description' => '第三方聚合登录系统',
                ':admin_path' => $adminPath,
                ':admin_email' => $adminEmail,
            ]);
            
        } catch (PDOException $e) {
            throw new Exception('插入默认配置失败: ' . $this->sanitizeErrorMessage($e->getMessage()));
        }
    }
    
    /**
     * 插入默认认证配置
     * 
     * @param PDO $pdo 数据库连接
     * @return void
     * @throws Exception 插入失败时抛出异常
     */
    public function insertDefaultVerificationConfig(PDO $pdo): void
    {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO verification_config (id, status, provider, personal_status, enterprise_status, `require`)
                VALUES (1, 0, 'manual', 1, 1, 0)
                ON DUPLICATE KEY UPDATE id = id
            ");
            $stmt->execute();
            
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') === false) {
                throw new Exception('插入默认认证配置失败: ' . $this->sanitizeErrorMessage($e->getMessage()));
            }
        }
    }

    /**
     * 分割SQL语句
     * 将SQL文件内容分割为独立的语句
     * 
     * @param string $sql SQL内容
     * @return array<string>
     */
    public function splitSqlStatements(string $sql): array
    {
        $sql = $this->removeComments($sql);
        
        $statements = [];
        $currentStatement = '';
        $inString = false;
        $stringChar = '';
        $length = strlen($sql);
        
        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];
            $prevChar = $i > 0 ? $sql[$i - 1] : '';
            if (($char === '"' || $char === "'") && $prevChar !== '\\') {
                if (!$inString) {
                    $inString = true;
                    $stringChar = $char;
                } elseif ($char === $stringChar) {
                    $inString = false;
                }
            }
            if ($char === ';' && !$inString) {
                $statement = trim($currentStatement);
                if (!empty($statement)) {
                    $statements[] = $statement;
                }
                $currentStatement = '';
            } else {
                $currentStatement .= $char;
            }
        }
        $statement = trim($currentStatement);
        if (!empty($statement)) {
            $statements[] = $statement;
        }
        
        return $statements;
    }
    
    /**
     * 移除SQL注释
     * 
     * @param string $sql
     * @return string
     */
    private function removeComments(string $sql): string
    {
        $sql = preg_replace('/--[^\r\n]*/', '', $sql);
        $sql = preg_replace('/#[^\r\n]*/', '', $sql);
        $sql = preg_replace('/\/\*[\s\S]*?\*\//', '', $sql);
        
        return $sql;
    }
    
    /**
     * 生成API密钥
     * 
     * @return string
     */
    private function generateApiKey(): string
    {
        return bin2hex(random_bytes(16));
    }
    
    /**
     * 转义数据库标识符
     * 
     * @param string $identifier
     * @return string
     */
    private function escapeIdentifier(string $identifier): string
    {
        $safe = preg_replace('/[^a-zA-Z0-9_]/', '', $identifier);
        return '`' . $safe . '`';
    }
    
    /**
     * 获取项目根目录路径
     * 
     * @return string
     */
    protected function getBasePath(): string
    {
        return dirname(dirname(__DIR__));
    }
    
    /**
     * 创建PDO连接
     * 
     * @param array $config 数据库配置
     * @return PDO
     * @throws Exception 连接失败时抛出异常
     */
    public function createConnection(array $config): PDO
    {
        $host = $config['host'] ?? 'localhost';
        $port = $config['port'] ?? 3306;
        $name = $config['name'] ?? '';
        $user = $config['user'] ?? '';
        $pass = $config['pass'] ?? '';
        
        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
            return new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            throw new Exception('数据库连接失败: ' . $this->formatConnectionError($e));
        }
    }
}
