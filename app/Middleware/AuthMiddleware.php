<?php
namespace App\Middleware;

use Core\Database;

/**
 * 用户认证中间件
 */
class AuthMiddleware
{
    public function handle()
    {
        if (!isset($_SESSION['user_id'])) {
            if ($this->isAjax()) {
                error('请先登录', 401, 401);
            }
            redirect('/user/login');
        }

        $db = Database::getInstance();
        $user = $db->fetch(
            "SELECT username, password, verification FROM {$db->getPrefix()}users WHERE id = ?",
            [$_SESSION['user_id']]
        );
        if ($user && (strpos($user['username'], 'oauth_') === 0 || empty($user['password']))) {
            $currentPath = $_SERVER['REQUEST_URI'] ?? '';
            if (strpos($currentPath, '/auth/complete-profile') === false) {
                if ($this->isAjax()) {
                    error('请先完善账户信息', 403, 403);
                }
                redirect('/auth/complete-profile');
            }
            return; // 完善资料优先，不检查认证
        }
        $this->checkRequireVerification($db, $user);
    }

    /**
     * 检查强制认证
     */
    protected function checkRequireVerification($db, $user)
    {
        $currentPath = $_SERVER['REQUEST_URI'] ?? '';
        $excludePaths = [
            '/user/verification',
            '/user/logout',
            '/auth/logout',
            '/auth/send-verify-code',
            '/user/submit-personal-verification',
            '/user/submit-enterprise-verification',
        ];
        
        foreach ($excludePaths as $path) {
            if (strpos($currentPath, $path) === 0) {
                return;
            }
        }
        $config = $db->fetch(
            "SELECT status, `require` FROM {$db->getPrefix()}verification_config WHERE id = 1"
        );
        if (!$config || !$config['status'] || !$config['require']) {
            return;
        }
        $verificationStatus = $user['verification'] ?? 'none';
        if (in_array($verificationStatus, ['personal', 'enterprise'])) {
            return;
        }
        $pendingVerification = $db->fetch(
            "SELECT id, status FROM {$db->getPrefix()}user_verifications 
             WHERE `user` = ? AND status IN (0, 3) 
             ORDER BY `time` DESC LIMIT 1",
            [$_SESSION['user_id']]
        );
        if ($pendingVerification) {
            return;
        }
        if ($this->isAjax()) {
            error('请先完成身份认证', 403, 403);
        }
        
        redirect('/user/verification');
    }

    protected function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) 
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
