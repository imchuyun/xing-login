<?php
/**
 * 实时计费服务类
 * 负责在 API 调用时进行权限和配额检查
 * 
 * Requirements: 1.1, 2.1, 3.1, 4.1, 5.1, 6.1
 */

namespace App\Services;

use Core\Database;

class BillingService
{
    /**
     * @var Database
     */
    private $db;

    /**
     * 错误码定义
     */
    const ERROR_NO_PACKAGE = 201;
    const ERROR_PACKAGE_EXPIRED = 202;
    const ERROR_PLATFORM_NOT_SUPPORTED = 203;
    const ERROR_ACCOUNT_LIMIT_REACHED = 204;
    const ERROR_QUOTA_EXHAUSTED = 205;
    const ERROR_FREE_DAILY_LIMIT = 206;
    const ERROR_FREE_PLATFORM_NOT_SUPPORTED = 207;
    const ERROR_RATE_LIMITED = 429;

    /**
     * 构造函数
     * 
     * @param Database|null $db 数据库实例
     */
    public function __construct($db = null)
    {
        $this->db = $db ?? Database::getInstance();
    }

    /**
     * 获取用户当前有效套餐
     * 
     * 单套餐原则: 每个用户同一时间只能有一个有效套餐
     * 有效套餐定义: status=1 且 expire_time > 当前时间
     * 
     * @param int $userId 用户ID
     * @return array|null 套餐信息或null
     * 
     * Requirements: 1.1
     */
    public function getActivePackage(int $userId): ?array
    {
        $prefix = $this->db->getPrefix();
        $now = date('Y-m-d H:i:s');
        
        $sql = "SELECT * FROM {$prefix}user_packages 
                WHERE `user` = ? 
                AND status = 1 
                AND expire_time > ? 
                ORDER BY time DESC 
                LIMIT 1";
        
        $package = $this->db->fetch($sql, [$userId, $now]);
        
        if ($package) {
            if (!empty($package['platforms'])) {
                $package['platforms_array'] = json_decode($package['platforms'], true) ?: [];
            } else {
                $package['platforms_array'] = [];
            }
        }
        
        return $package ?: null;
    }

    /**
     * 检查套餐包类型的访问权限
     * 
     * 套餐包检查逻辑:
     * 1. 检查套餐是否在有效期内
     * 2. 检查套餐是否支持当前请求的登录平台
     * 
     * @param array $package 套餐信息
     * @param string $platform 请求的登录平台
     * @return array ['allowed' => bool, 'error' => string|null, 'error_code' => int|null]
     * 
     * Requirements: 2.1, 2.2, 2.3, 2.4, 2.5
     */
    public function checkPackageAccess(array $package, string $platform): array
    {
        $now = date('Y-m-d H:i:s');
        if ($package['expire_time'] <= $now) {
            return [
                'allowed' => false,
                'error' => '套餐已过期，请续费',
                'error_code' => self::ERROR_PACKAGE_EXPIRED
            ];
        }
        $supportedPlatforms = [];
        if (!empty($package['platforms'])) {
            $supportedPlatforms = json_decode($package['platforms'], true) ?: [];
        }
        if (empty($supportedPlatforms)) {
            return [
                'allowed' => true,
                'error' => null,
                'error_code' => null,
                'package_type' => 'package'
            ];
        }
        if (!in_array($platform, $supportedPlatforms)) {
            return [
                'allowed' => false,
                'error' => '当前套餐不支持该登录方式，请升级套餐',
                'error_code' => self::ERROR_PLATFORM_NOT_SUPPORTED
            ];
        }
        
        return [
            'allowed' => true,
            'error' => null,
            'error_code' => null,
            'package_type' => 'package'
        ];
    }

    /**
     * 统计用户所有应用的 oauth_users 数量
     * 
     * 账号数量包需要统计该用户所有应用下的授权用户总数
     * 
     * @param int $userId 用户ID
     * @return int 用户数量
     * 
     * Requirements: 3.1
     */
    public function countOAuthUsers(int $userId): int
    {
        $prefix = $this->db->getPrefix();
        $appsSql = "SELECT app_id FROM {$prefix}apps WHERE `user` = ?";
        $apps = $this->db->fetchAll($appsSql, [$userId]);
        
        if (empty($apps)) {
            return 0;
        }
        $appIds = array_column($apps, 'app_id');
        $placeholders = implode(',', array_fill(0, count($appIds), '?'));
        $countSql = "SELECT COUNT(*) as count FROM {$prefix}oauth_users WHERE app_id IN ({$placeholders})";
        $result = $this->db->fetch($countSql, $appIds);
        
        return (int)($result['count'] ?? 0);
    }

    /**
     * 检查是否为新的 OAuth 用户
     * 
     * 判断该用户是否已经在系统中存在（同一应用、同一平台、同一 open_id）
     * 
     * @param string $appId 应用ID
     * @param string $platform 平台
     * @param string $openId OpenID
     * @return bool 是否为新用户
     * 
     * Requirements: 3.4
     */
    public function isNewOAuthUser(string $appId, string $platform, string $openId): bool
    {
        $prefix = $this->db->getPrefix();
        
        $sql = "SELECT COUNT(*) as count FROM {$prefix}oauth_users 
                WHERE app_id = ? AND type = ? AND open_id = ?";
        $result = $this->db->fetch($sql, [$appId, $platform, $openId]);
        
        return (int)($result['count'] ?? 0) === 0;
    }

    /**
     * 检查账号数量包类型的访问权限
     * 
     * 账号数量包检查逻辑:
     * 1. 如果是已存在的用户，直接允许
     * 2. 如果是新用户，检查当前用户数是否已达上限
     * 
     * @param array $package 套餐信息
     * @param int $userId 用户ID
     * @param string $appId 应用ID
     * @param string $platform 平台
     * @param string|null $openId OpenID
     * @return array ['allowed' => bool, 'error' => string|null, 'error_code' => int|null]
     * 
     * Requirements: 3.1, 3.2, 3.3, 3.4
     */
    public function checkAccountAccess(array $package, int $userId, string $appId, string $platform, ?string $openId = null): array
    {
        if ($openId === null) {
            return [
                'allowed' => true,
                'error' => null,
                'error_code' => null,
                'package_type' => 'account'
            ];
        }
        if (!$this->isNewOAuthUser($appId, $platform, $openId)) {
            return [
                'allowed' => true,
                'error' => null,
                'error_code' => null,
                'package_type' => 'account',
                'is_new_user' => false
            ];
        }
        $accountLimit = (int)($package['account_limit'] ?? 0);
        if ($accountLimit <= 0) {
            return [
                'allowed' => true,
                'error' => null,
                'error_code' => null,
                'package_type' => 'account',
                'is_new_user' => true
            ];
        }
        $currentCount = $this->countOAuthUsers($userId);
        if ($currentCount >= $accountLimit) {
            return [
                'allowed' => false,
                'error' => '授权用户数量已达上限，请升级套餐',
                'error_code' => self::ERROR_ACCOUNT_LIMIT_REACHED,
                'current_count' => $currentCount,
                'account_limit' => $accountLimit
            ];
        }
        
        return [
            'allowed' => true,
            'error' => null,
            'error_code' => null,
            'package_type' => 'account',
            'is_new_user' => true,
            'current_count' => $currentCount,
            'account_limit' => $accountLimit
        ];
    }

    /**
     * 统计有效期内的调用次数
     * 
     * 调用次数包需要统计有效期内的 API 调用总次数
     * - 如果有 expire_time，只统计 start_time 到 expire_time 之间的调用
     * - 如果没有 expire_time（永久），统计 start_time 之后的所有调用
     * 
     * @param int $userId 用户ID
     * @param array $package 套餐信息
     * @return int 调用次数
     * 
     * Requirements: 4.1, 4.2, 4.3
     */
    public function countCallsInPeriod(int $userId, array $package): int
    {
        $prefix = $this->db->getPrefix();
        $packageId = $package['id'] ?? null;
        $startTime = $package['start_time'] ?? null;
        $expireTime = $package['expire_time'] ?? null;
        if (empty($startTime)) {
            return 0;
        }
        $params = [$userId, $startTime];
        
        if (!empty($expireTime)) {
            $sql = "SELECT COUNT(*) as count FROM {$prefix}api_logs 
                    WHERE `user` = ? AND `time` >= ? AND `time` <= ?";
            $params[] = $expireTime;
        } else {
            $sql = "SELECT COUNT(*) as count FROM {$prefix}api_logs 
                    WHERE `user` = ? AND `time` >= ?";
        }
        
        $result = $this->db->fetch($sql, $params);
        
        return (int)($result['count'] ?? 0);
    }

    /**
     * 检查调用次数包类型的访问权限
     * 
     * 调用次数包检查逻辑:
     * 1. 统计有效期内的调用次数
     * 2. 检查是否已达到总配额上限
     * 
     * @param array $package 套餐信息
     * @param int $userId 用户ID
     * @return array ['allowed' => bool, 'error' => string|null, 'error_code' => int|null]
     * 
     * Requirements: 4.1, 4.2, 4.3, 4.4, 4.5
     */
    public function checkQuotaAccess(array $package, int $userId): array
    {
        $totalQuota = (int)($package['total_quota'] ?? 0);
        if ($totalQuota <= 0) {
            return [
                'allowed' => true,
                'error' => null,
                'error_code' => null,
                'package_type' => 'quota'
            ];
        }
        $usedCount = $this->countCallsInPeriod($userId, $package);
        if ($usedCount >= $totalQuota) {
            return [
                'allowed' => false,
                'error' => '调用次数已用完，请购买新套餐',
                'error_code' => self::ERROR_QUOTA_EXHAUSTED,
                'used_count' => $usedCount,
                'total_quota' => $totalQuota
            ];
        }
        
        return [
            'allowed' => true,
            'error' => null,
            'error_code' => null,
            'package_type' => 'quota',
            'used_count' => $usedCount,
            'total_quota' => $totalQuota,
            'remaining' => $totalQuota - $usedCount
        ];
    }

    /**
     * 获取免费用户配置
     * 
     * 从系统设置中获取免费用户的配置信息
     * 
     * @return array 免费用户配置
     * 
     * Requirements: 5.1, 5.4, 5.5
     */
    public function getFreeUserConfig(): array
    {
        $prefix = $this->db->getPrefix();
        $settings = $this->db->fetch(
            "SELECT billing_free_enabled, billing_free_daily_limit, billing_free_platforms 
             FROM {$prefix}settings WHERE id = 1"
        ) ?: [];
        return [
            'enabled' => (int)($settings['billing_free_enabled'] ?? 1) === 1,
            'daily_limit' => (int)($settings['billing_free_daily_limit'] ?? 100),
            'platforms' => json_decode($settings['billing_free_platforms'] ?? '["qq","wx"]', true) ?: ['qq', 'wx']
        ];
    }

    /**
     * 统计免费用户今日调用次数
     * 
     * @param int $userId 用户ID
     * @return int 今日调用次数
     */
    private function countTodayCalls(int $userId): int
    {
        $prefix = $this->db->getPrefix();
        $today = date('Y-m-d');
        
        $sql = "SELECT COUNT(*) as count FROM {$prefix}api_logs 
                WHERE `user` = ? AND `time` >= ? AND `time` < ?";
        $result = $this->db->fetch($sql, [
            $userId,
            $today . ' 00:00:00',
            $today . ' 23:59:59'
        ]);
        
        return (int)($result['count'] ?? 0);
    }

    /**
     * 检查免费用户的访问权限
     * 
     * 免费用户检查逻辑:
     * 1. 检查是否允许免费用户调用
     * 2. 检查请求的平台是否在免费用户可用列表中
     * 3. 永久授权无限制，不再检查每日调用次数
     * 
     * @param int $userId 用户ID
     * @param string $platform 请求的登录平台
     * @return array ['allowed' => bool, 'error' => string|null, 'error_code' => int|null]
     * 
     * Requirements: 5.1, 5.2, 5.3, 5.4, 5.5
     */
    public function checkFreeUserAccess(int $userId, string $platform): array
    {
        $config = $this->getFreeUserConfig();
        if (!$config['enabled']) {
            return [
                'allowed' => false,
                'error' => '免费授权未启用',
                'error_code' => self::ERROR_NO_PACKAGE
            ];
        }
        if (!in_array($platform, $config['platforms'])) {
            return [
                'allowed' => false,
                'error' => '免费用户不支持该登录方式',
                'error_code' => self::ERROR_FREE_PLATFORM_NOT_SUPPORTED
            ];
        }
        
        // 永久授权无限制，不再检查每日调用次数
        return [
            'allowed' => true,
            'error' => null,
            'error_code' => null,
            'package_type' => 'free',
            'today_calls' => 0,
            'daily_limit' => -1,
            'remaining_today' => -1
        ];
    }

    /**
     * 统一的访问权限检查方法
     * 
     * 整合所有检查逻辑，根据用户套餐类型调用对应的检查方法
     * 
     * @param int $userId 用户ID
     * @param string $appId 应用ID
     * @param string $platform 请求的登录平台
     * @param string|null $openId 第三方用户OpenID (用于判断是否新用户)
     * @return array ['allowed' => bool, 'error' => string|null, 'error_code' => int|null, 'package_type' => string]
     * 
     * Requirements: 2.1, 3.1, 4.1, 5.1
     */
    public function checkAccess(int $userId, string $appId, string $platform, ?string $openId = null): array
    {
        $package = $this->getActivePackage($userId);
        if ($package === null) {
            return $this->checkFreeUserAccess($userId, $platform);
        }
        $packageType = $package['type'] ?? 'package';
        
        switch ($packageType) {
            case 'package':
                $result = $this->checkPackageAccess($package, $platform);
                break;
                
            case 'account':
                $result = $this->checkAccountAccess($package, $userId, $appId, $platform, $openId);
                break;
                
            case 'quota':
                $result = $this->checkQuotaAccess($package, $userId);
                break;
                
            default:
                $result = $this->checkPackageAccess($package, $platform);
                break;
        }
        if ($result['allowed']) {
            $result['package_id'] = $package['id'];
            $result['package_name'] = $package['product_name'] ?? '';
        }
        
        return $result;
    }

    /**
     * 记录 API 调用
     * 
     * 在每次成功的 API 调用后记录调用信息到 api_logs 表
     * 
     * @param int $userId 用户ID
     * @param string $appId 应用ID
     * @param string $platform 登录平台
     * @param string $packageType 使用的套餐类型 (package/account/quota/free)
     * @param int|null $packageId 套餐ID (可选)
     * @param string|null $ip 请求IP (可选)
     * @return int 插入的记录ID
     * 
     * Requirements: 6.1
     */
    public function recordCall(int $userId, string $appId, string $platform, string $packageType, ?int $packageId = null, ?string $ip = null): int
    {
        $prefix = $this->db->getPrefix();
        
        $data = [
            'user' => $userId,
            'app' => $appId,
            'platform' => $platform,
            'product_type' => $packageType,
            'product' => $packageId,
            'ip' => $ip,
            'time' => date('Y-m-d H:i:s')
        ];
        
        $columns = implode(', ', array_map(fn($col) => "`{$col}`", array_keys($data)));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$prefix}api_logs ({$columns}) VALUES ({$placeholders})";
        $this->db->query($sql, array_values($data));
        
        return (int)$this->db->lastInsertId();
    }

    /**
     * 获取调用记录列表
     * 
     * 支持分页和多种筛选条件
     * 
     * @param int $userId 用户ID
     * @param array $filters 筛选条件 ['app_id' => string, 'platform' => string, 'start_date' => string, 'end_date' => string]
     * @param int $page 页码 (从1开始)
     * @param int $pageSize 每页数量
     * @return array ['data' => array, 'total' => int, 'page' => int, 'page_size' => int, 'total_pages' => int]
     * 
     * Requirements: 6.2, 6.3
     */
    public function getCallLogs(int $userId, array $filters = [], int $page = 1, int $pageSize = 20): array
    {
        $prefix = $this->db->getPrefix();
        $where = ["`user` = ?"];
        $params = [$userId];
        if (!empty($filters['app_id'])) {
            $where[] = "`app` = ?";
            $params[] = $filters['app_id'];
        }
        if (!empty($filters['platform'])) {
            $where[] = "platform = ?";
            $params[] = $filters['platform'];
        }
        if (!empty($filters['start_date'])) {
            $where[] = "`time` >= ?";
            $params[] = $filters['start_date'] . ' 00:00:00';
        }
        
        if (!empty($filters['end_date'])) {
            $where[] = "`time` <= ?";
            $params[] = $filters['end_date'] . ' 23:59:59';
        }
        
        $whereClause = implode(' AND ', $where);
        $countSql = "SELECT COUNT(*) as count FROM {$prefix}api_logs WHERE {$whereClause}";
        $countResult = $this->db->fetch($countSql, $params);
        $total = (int)($countResult['count'] ?? 0);
        $page = max(1, $page);
        $pageSize = max(1, min(100, $pageSize)); // Limit page size to 100
        $totalPages = $total > 0 ? (int)ceil($total / $pageSize) : 0;
        $offset = ($page - 1) * $pageSize;
        $dataSql = "SELECT * FROM {$prefix}api_logs 
                    WHERE {$whereClause} 
                    ORDER BY `time` DESC 
                    LIMIT {$pageSize} OFFSET {$offset}";
        $data = $this->db->fetchAll($dataSql, $params);
        
        return [
            'data' => $data ?: [],
            'total' => $total,
            'page' => $page,
            'page_size' => $pageSize,
            'total_pages' => $totalPages
        ];
    }

    /**
     * 激活新套餐（替换旧套餐）
     * 
     * 单套餐原则: 购买新套餐时自动将旧套餐设为无效
     * 
     * @param int $userId 用户ID
     * @param int $orderId 订单ID
     * @param array $product 产品信息 ['id', 'name', 'type', 'platforms', 'duration', 'total_quota', 'account_limit']
     * @param int|null $operatorId 操作人ID (管理员操作时)
     * @param string|null $reason 操作原因
     * @return int 新套餐ID
     * 
     * Requirements: 1.2, 1.3
     */
    public function activatePackage(int $userId, int $orderId, array $product, ?int $operatorId = null, ?string $reason = null): int
    {
        $prefix = $this->db->getPrefix();
        $now = date('Y-m-d H:i:s');
        $oldPackage = $this->getActivePackage($userId);
        if ($oldPackage !== null) {
            $this->db->update(
                'user_packages',
                ['status' => 0],
                'id = ?',
                [$oldPackage['id']]
            );
        }
        $startTime = $now;
        $duration = (int)($product['duration'] ?? 30); // Default 30 days
        $expireTime = date('Y-m-d H:i:s', strtotime("+{$duration} days"));
        $newPackageData = [
            'user' => $userId,
            'order' => $orderId,
            'product' => $product['id'] ?? 0,
            'product_name' => $product['name'] ?? '',
            'type' => $product['type'] ?? 'package',
            'platforms' => $product['platforms'] ?? null,
            'total_quota' => $product['total_quota'] ?? null,
            'account_limit' => $product['account_limit'] ?? null,
            'start_time' => $startTime,
            'expire_time' => $expireTime,
            'status' => 1,
            'time' => $now
        ];
        
        $newPackageId = $this->db->insert('user_packages', $newPackageData);
        $action = $oldPackage !== null ? 'replaced' : 'activated';
        $this->recordPackageHistory(
            $userId,
            (int)$newPackageId,
            $action,
            $oldPackage !== null ? (int)$oldPackage['id'] : null,
            $operatorId,
            $reason
        );
        
        return (int)$newPackageId;
    }

    /**
     * 记录套餐变更历史
     * 
     * @param int $userId 用户ID
     * @param int $packageId 套餐ID
     * @param string $action 操作类型 (activated/replaced/expired/admin_modified/verification_reward)
     * @param int|null $oldPackageId 被替换的旧套餐ID
     * @param int|null $operatorId 操作人ID
     * @param string|null $reason 操作原因
     * @return int 历史记录ID
     * 
     * Requirements: 1.3, 6.5, 9.3
     */
    public function recordPackageHistory(int $userId, int $packageId, string $action, ?int $oldPackageId = null, ?int $operatorId = null, ?string $reason = null): int
    {
        $data = [
            'user' => $userId,
            'product' => $packageId,
            'action' => $action,
            'old_product' => $oldPackageId,
            'operator' => $operatorId,
            'reason' => $reason,
            'time' => date('Y-m-d H:i:s')
        ];
        
        return (int)$this->db->insert('product_log', $data);
    }

    /**
     * 获取用户使用统计
     * 
     * 返回当前套餐使用情况、剩余额度等统计信息
     * 
     * @param int $userId 用户ID
     * @return array 使用统计信息
     * 
     * Requirements: 7.1, 7.2, 7.3, 7.4, 7.5
     */
    public function getUsageStats(int $userId): array
    {
        $prefix = $this->db->getPrefix();
        $now = new \DateTime();
        $package = $this->getActivePackage($userId);
        $today = $now->format('Y-m-d');
        $weekStart = (clone $now)->modify('monday this week')->format('Y-m-d');
        $monthStart = $now->format('Y-m-01');
        $todayCalls = $this->getCallCountForPeriod($userId, $today . ' 00:00:00', $today . ' 23:59:59');
        $weekCalls = $this->getCallCountForPeriod($userId, $weekStart . ' 00:00:00', $now->format('Y-m-d H:i:s'));
        $monthCalls = $this->getCallCountForPeriod($userId, $monthStart . ' 00:00:00', $now->format('Y-m-d H:i:s'));
        $callsByApp = $this->getCallCountByApp($userId);
        $callsByPlatform = $this->getCallCountByPlatform($userId);
        $stats = [
            'calls' => [
                'today' => $todayCalls,
                'this_week' => $weekCalls,
                'this_month' => $monthCalls
            ],
            'by_app' => $callsByApp,
            'by_platform' => $callsByPlatform,
            'package' => null
        ];
        if ($package !== null) {
            $packageStats = [
                'id' => $package['id'],
                'name' => $package['product_name'] ?? '',
                'type' => $package['type'],
                'start_time' => $package['start_time'],
                'expire_time' => $package['expire_time'],
                'days_remaining' => max(0, (int)((strtotime($package['expire_time']) - time()) / 86400))
            ];
            
            switch ($package['type']) {
                case 'quota':
                    $usedQuota = $this->countCallsInPeriod($userId, $package);
                    $totalQuota = (int)($package['total_quota'] ?? 0);
                    $packageStats['quota'] = [
                        'used' => $usedQuota,
                        'total' => $totalQuota,
                        'remaining' => max(0, $totalQuota - $usedQuota),
                        'percentage' => $totalQuota > 0 ? round(($usedQuota / $totalQuota) * 100, 2) : 0
                    ];
                    break;
                    
                case 'account':
                    $usedAccounts = $this->countOAuthUsers($userId);
                    $totalAccounts = (int)($package['account_limit'] ?? 0);
                    $packageStats['accounts'] = [
                        'used' => $usedAccounts,
                        'total' => $totalAccounts,
                        'remaining' => max(0, $totalAccounts - $usedAccounts),
                        'percentage' => $totalAccounts > 0 ? round(($usedAccounts / $totalAccounts) * 100, 2) : 0
                    ];
                    break;
                    
                case 'package':
                    $platforms = [];
                    if (!empty($package['platforms'])) {
                        $platforms = json_decode($package['platforms'], true) ?: [];
                    }
                    $packageStats['platforms'] = $platforms;
                    break;
            }
            
            $stats['package'] = $packageStats;
        }
        
        return $stats;
    }

    /**
     * 获取指定时间段内的调用次数
     * 
     * @param int $userId 用户ID
     * @param string $startTime 开始时间
     * @param string $endTime 结束时间
     * @return int 调用次数
     */
    private function getCallCountForPeriod(int $userId, string $startTime, string $endTime): int
    {
        $prefix = $this->db->getPrefix();
        $sql = "SELECT COUNT(*) as count FROM {$prefix}api_logs 
                WHERE `user` = ? AND `time` >= ? AND `time` <= ?";
        $result = $this->db->fetch($sql, [$userId, $startTime, $endTime]);
        return (int)($result['count'] ?? 0);
    }

    /**
     * 获取按应用分组的调用次数
     * 
     * @param int $userId 用户ID
     * @return array 按应用分组的调用次数
     */
    private function getCallCountByApp(int $userId): array
    {
        $prefix = $this->db->getPrefix();
        $sql = "SELECT `app`, COUNT(*) as count FROM {$prefix}api_logs 
                WHERE `user` = ? GROUP BY `app` ORDER BY count DESC";
        $results = $this->db->fetchAll($sql, [$userId]);
        
        $byApp = [];
        foreach ($results as $row) {
            $byApp[$row['app']] = (int)$row['count'];
        }
        return $byApp;
    }

    /**
     * 获取按平台分组的调用次数
     * 
     * @param int $userId 用户ID
     * @return array 按平台分组的调用次数
     */
    private function getCallCountByPlatform(int $userId): array
    {
        $prefix = $this->db->getPrefix();
        $sql = "SELECT platform, COUNT(*) as count FROM {$prefix}api_logs 
                WHERE `user` = ? GROUP BY platform ORDER BY count DESC";
        $results = $this->db->fetchAll($sql, [$userId]);
        
        $byPlatform = [];
        foreach ($results as $row) {
            $byPlatform[$row['platform']] = (int)$row['count'];
        }
        return $byPlatform;
    }

    /**
     * 获取侧边栏套餐显示信息
     * 
     * 返回用于侧边栏显示的套餐信息，包括套餐类型、使用量、百分比等
     * 支持付费套餐和免费用户两种场景
     * 
     * @param int $userId 用户ID
     * @return array 套餐显示信息
     * 
     * Requirements: 2.1, 2.2, 2.3, 3.1, 3.2
     */
    public function getSidebarPackageInfo(int $userId): array
    {
        $package = $this->getActivePackage($userId);
        $freeConfig = $this->getFreeUserConfig();
        
        if ($package === null) {
            $todayCalls = $this->countTodayCalls($userId);
            
            return [
                'type' => 'free',
                'name' => '永久授权',
                'used' => null,
                'total' => null,
                'percentage' => null,
                'platforms' => $freeConfig['platforms'],
                'platforms_count' => count($freeConfig['platforms']),
                'expire_time' => null,
                'days_remaining' => null,
                'is_expiring_soon' => false,
                'unit' => '无限制',
                'has_package' => false,
                'free_enabled' => $freeConfig['enabled']
            ];
        }
        $expireTimestamp = strtotime($package['expire_time']);
        $daysRemaining = max(0, (int)(($expireTimestamp - time()) / 86400));
        $isExpiringSoon = ($expireTimestamp - time()) <= 7 * 86400;
        $platforms = json_decode($package['platforms'] ?? '[]', true) ?: [];
        
        $info = [
            'type' => $package['type'],
            'name' => $package['product_name'] ?? '套餐',
            'expire_time' => $package['expire_time'],
            'days_remaining' => $daysRemaining,
            'is_expiring_soon' => $isExpiringSoon,
            'platforms' => $platforms,
            'platforms_count' => count($platforms),
            'has_package' => true,
            'free_enabled' => $freeConfig['enabled']
        ];
        
        switch ($package['type']) {
            case 'quota':
                $used = $this->countCallsInPeriod($userId, $package);
                $total = (int)($package['total_quota'] ?? 0);
                $info['used'] = $used;
                $info['total'] = $total;
                $info['percentage'] = $total > 0 ? round(($used / $total) * 100, 1) : 0;
                $info['unit'] = '次';
                break;
                
            case 'account':
                $used = $this->countOAuthUsers($userId);
                $total = (int)($package['account_limit'] ?? 0);
                $info['used'] = $used;
                $info['total'] = $total;
                $info['percentage'] = $total > 0 ? round(($used / $total) * 100, 1) : 0;
                $info['unit'] = '人';
                break;
                
            case 'package':
            default:
                $info['used'] = null;
                $info['total'] = null;
                $info['percentage'] = null;
                $info['unit'] = null;
                break;
        }
        
        return $info;
    }
}
