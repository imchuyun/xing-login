<?php
namespace App\Services;

/**
 * 版本号工具类
 * 提供版本号解析、验证、比较等功能
 */
class VersionHelper
{
    /**
     * 默认版本号
     */
    private const DEFAULT_VERSION = '1.0.0';

    /**
     * 解析版本号为数组
     * @param string $version 版本号（如 v1.2.3、1.2.3、v1.0、1.0）
     * @return array [major, minor, patch]
     */
    public static function parse(string $version): array
    {
        $version = ltrim($version, 'vV');
        $parts = explode('.', $version);
        
        return [
            (int)($parts[0] ?? 0),  // major
            (int)($parts[1] ?? 0),  // minor
            (int)($parts[2] ?? 0),  // patch
        ];
    }

    /**
     * 验证版本号格式
     * @param string $version 版本号
     * @return bool
     */
    public static function isValid(string $version): bool
    {
        return (bool)preg_match('/^v?\d+\.\d+(\.\d+)?$/', $version);
    }

    /**
     * 比较两个版本号
     * @param string $version1 版本号1
     * @param string $version2 版本号2
     * @return int -1: v1 < v2, 0: v1 = v2, 1: v1 > v2
     */
    public static function compare(string $version1, string $version2): int
    {
        $v1 = self::parse($version1);
        $v2 = self::parse($version2);
        if ($v1[0] !== $v2[0]) {
            return $v1[0] > $v2[0] ? 1 : -1;
        }
        if ($v1[1] !== $v2[1]) {
            return $v1[1] > $v2[1] ? 1 : -1;
        }
        if ($v1[2] !== $v2[2]) {
            return $v1[2] > $v2[2] ? 1 : -1;
        }
        
        return 0;
    }

    /**
     * 获取当前系统版本
     * @return string
     */
    public static function getCurrentVersion(): string
    {
        $config = require ML_ROOT . '/config/config.php';
        return $config['version'] ?? self::DEFAULT_VERSION;
    }

    /**
     * 获取更新状态
     * @param string $currentVersion 当前版本
     * @param string|null $latestVersion 最新版本
     * @return array ['status' => string, 'has_update' => bool]
     */
    public static function getUpdateStatus(string $currentVersion, ?string $latestVersion): array
    {
        if (empty($latestVersion) || !self::isValid($latestVersion)) {
            return [
                'status' => '检查中...',
                'has_update' => false,
            ];
        }
        $comparison = self::compare($latestVersion, $currentVersion);
        
        if ($comparison > 0) {
            return [
                'status' => '有新版本可用',
                'has_update' => true,
            ];
        }
        return [
            'status' => '已是最新版本',
            'has_update' => false,
        ];
    }
    
    /**
     * 格式化版本号（统一为三位数格式）
     * @param string|null $version 版本号
     * @return string
     */
    public static function format(?string $version): string
    {
        if (empty($version)) {
            return self::DEFAULT_VERSION;
        }
        // 移除 v 前缀
        $version = ltrim($version, 'vV');
        // 确保是三位数格式
        $parts = explode('.', $version);
        return sprintf('%d.%d.%d', 
            (int)($parts[0] ?? 0),
            (int)($parts[1] ?? 0),
            (int)($parts[2] ?? 0)
        );
    }
}
