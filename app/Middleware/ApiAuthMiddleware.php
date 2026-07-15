<?php
namespace App\Middleware;

use Core\Database;

/**
 * API认证中间件
 */
class ApiAuthMiddleware
{
    public function handle()
    {
        $apiKey = $this->getApiKey();
        
        if (!$apiKey) {
            error('缺少API密钥', 401, 401);
        }

        $db = Database::getInstance();
        $user = $db->fetch(
            "SELECT id, status FROM {$db->getPrefix()}users WHERE api_key = ?",
            [$apiKey]
        );

        if (!$user) {
            error('无效的API密钥', 401, 401);
        }

        if ($user['status'] !== 'enable') {
            error('账户已被禁用', 403, 403);
        }
        $_REQUEST['_api_user_id'] = $user['id'];
    }

    protected function getApiKey()
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (preg_match('/Bearer\s+(.+)/i', $header, $matches)) {
            return $matches[1];
        }
        return $_GET['api_key'] ?? $_POST['api_key'] ?? null;
    }
}
