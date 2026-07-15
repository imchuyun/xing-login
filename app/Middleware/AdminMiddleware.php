<?php
namespace App\Middleware;

use Core\Database;

/**
 * 管理员权限中间件
 */
class AdminMiddleware
{
    /**
     * 处理管理员请求
     * 检查登录状态和强制更新状态
     */
    public function handle(): void
    {
        // 登录页面不需要检查
        $currentPath = $_SERVER['REQUEST_URI'] ?? '';
        if ($this->isLoginPage($currentPath)) {
            // 登录页面仍需检查登录状态（用于重定向已登录用户）
            return;
        }
        
        // 检查登录状态
        if (!isset($_SESSION['admin_id'])) {
            redirect(admin_url('login'));
        }

        $db = Database::getInstance();
        $user = $db->fetch(
            "SELECT role FROM {$db->getPrefix()}users WHERE id = ? AND role = 'admin'",
            [$_SESSION['admin_id']]
        );

        if (!$user) {
            unset($_SESSION['admin_id']);
            redirect(admin_url('login'));
        }
        
        // 检查强制更新状态
        $this->checkForceUpdate();
    }
    
    /**
     * 检查是否为登录页面
     * 登录和登出页面跳过强制更新检查
     * 
     * @param string $path 当前请求路径
     * @return bool 是否为登录页面
     */
    private function isLoginPage(string $path): bool
    {
        return strpos($path, '/admin/login') !== false 
            || strpos($path, '/admin/logout') !== false;
    }
    
    /**
     * 检查强制更新状态
     */
    private function checkForceUpdate(): void
    {
    }
}
