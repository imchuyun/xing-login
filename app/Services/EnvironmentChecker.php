<?php
/**
 * 环境检测器
 * 负责检测服务器环境是否满足运行要求
 * 
 * Requirements: 2.1, 2.2, 2.3
 */

namespace App\Services;

class EnvironmentChecker
{
    /**
     * 最低PHP版本要求
     */
    private const MIN_PHP_VERSION = '7.4';
    
    /**
     * 必需的PHP扩展列表
     */
    private const REQUIRED_EXTENSIONS = [
        'PDO',
        'pdo_mysql',
        'curl',
        'openssl',
        'json',
        'mbstring'
    ];
    
    /**
     * 需要写入权限的目录列表
     */
    private const WRITABLE_DIRECTORIES = [
        'config',
        'storage/uploads'
    ];
    
    /**
     * 执行所有环境检测
     * 
     * @return array<array{name: string, required: string, current: string, passed: bool}>
     * 
     * Requirements: 2.1, 2.2, 2.3
     */
    public function checkAll(): array
    {
        $results = [];
        $results[] = $this->checkPhpVersion();
        foreach (self::REQUIRED_EXTENSIONS as $extension) {
            $results[] = $this->checkExtension($extension);
        }
        foreach (self::WRITABLE_DIRECTORIES as $directory) {
            $results[] = $this->checkDirectoryWritable($directory);
        }
        
        return $results;
    }
    
    /**
     * 检测PHP版本
     * 
     * @return array{name: string, required: string, current: string, passed: bool}
     * 
     * Requirements: 2.1
     */
    public function checkPhpVersion(): array
    {
        $currentVersion = PHP_VERSION;
        $passed = version_compare($currentVersion, self::MIN_PHP_VERSION, '>=');
        
        return [
            'name' => 'PHP版本',
            'required' => '>= ' . self::MIN_PHP_VERSION,
            'current' => $currentVersion,
            'passed' => $passed
        ];
    }
    
    /**
     * 检测PHP扩展
     * 
     * @param string $extension 扩展名
     * @return array{name: string, required: string, current: string, passed: bool}
     * 
     * Requirements: 2.2
     */
    public function checkExtension(string $extension): array
    {
        $loaded = extension_loaded(strtolower($extension));
        
        return [
            'name' => $extension . '扩展',
            'required' => '已安装',
            'current' => $loaded ? '已安装' : '未安装',
            'passed' => $loaded
        ];
    }
    
    /**
     * 检测目录写入权限
     * 
     * @param string $path 目录路径（相对于项目根目录）
     * @return array{name: string, required: string, current: string, passed: bool}
     * 
     * Requirements: 2.3
     */
    public function checkDirectoryWritable(string $path): array
    {
        $fullPath = $this->getBasePath() . '/' . $path;
        $writable = $this->testWritable($fullPath);
        
        return [
            'name' => $path . '目录',
            'required' => '可写',
            'current' => $writable ? '可写' : '不可写',
            'passed' => $writable
        ];
    }
    
    /**
     * 测试目录是否可写（通过实际写入测试）
     * 
     * @param string $path 目录路径
     * @return bool
     */
    protected function testWritable(string $path): bool
    {
        if (!is_dir($path)) {
            if (!@mkdir($path, 0755, true)) {
                return false;
            }
        }
        $testFile = $path . '/.write_test_' . uniqid();
        $result = @file_put_contents($testFile, 'test');
        
        if ($result !== false) {
            @unlink($testFile);
            return true;
        }
        
        return false;
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
     * 检查是否所有检测项都通过
     * 
     * @return bool
     */
    public function allPassed(): bool
    {
        $results = $this->checkAll();
        
        foreach ($results as $result) {
            if (!$result['passed']) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * 获取未通过的检测项
     * 
     * @return array<array{name: string, required: string, current: string, passed: bool}>
     */
    public function getFailedChecks(): array
    {
        $results = $this->checkAll();
        
        return array_filter($results, function ($result) {
            return !$result['passed'];
        });
    }
}
