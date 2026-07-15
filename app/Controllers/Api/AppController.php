<?php
namespace App\Controllers\Api;

use App\Controllers\BaseController;

/**
 * 应用API控制器
 */
class AppController extends BaseController
{
    protected $apiUserId;

    public function __construct()
    {
        parent::__construct();
        $this->authenticate();
    }

    /**
     * API认证
     */
    protected function authenticate()
    {
        $apiKey = $this->getApiKey();
        $apiSecret = $this->input('api_secret', '');

        if (empty($apiKey)) {
            error('缺少API密钥', 401, 401);
        }

        $user = $this->db->fetch(
            "SELECT id, status FROM {$this->db->getPrefix()}users WHERE api_key = ?",
            [$apiKey]
        );

        if (!$user) {
            error('无效的API密钥', 401, 401);
        }

        if ($user['status'] !== 'enable') {
            error('账户已被禁用', 403, 403);
        }

        $this->apiUserId = $user['id'];
    }

    protected function getApiKey()
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (preg_match('/Bearer\s+(.+)/i', $header, $matches)) {
            return $matches[1];
        }
        return $this->input('api_key', '');
    }

    /**
     * 创建应用
     */
    public function create()
    {
        $appName = trim($this->input('app_name', ''));
        $domain = trim($this->input('domain', ''));
        $callbackUrl = trim($this->input('callback_url', ''));

        if (empty($appName) || empty($domain) || empty($callbackUrl)) {
            error('参数不完整');
        }

        $domain = preg_replace('#^https?://#', '', $domain);
        $domain = rtrim($domain, '/');
        $count = $this->db->fetch(
            "SELECT COUNT(*) as count FROM {$this->db->getPrefix()}apps WHERE `user` = ?",
            [$this->apiUserId]
        )['count'];

        if ($count >= 10) {
            error('应用数量已达上限');
        }

        $appId = 'app_' . random_string(16);
        $appSecret = 'secret_' . random_string(32);

        $id = $this->db->insert('apps', [
            'user' => $this->apiUserId,
            'app_name' => $appName,
            'app_id' => $appId,
            'app_secret' => $appSecret,
            'domain' => $domain,
            'callback' => $callbackUrl,
            'status' => 1,
        ]);

        success([
            'id' => $id,
            'app_id' => $appId,
            'app_secret' => $appSecret,
        ], '创建成功');
    }

    /**
     * 更新应用
     */
    public function update()
    {
        $appId = $this->input('app_id', '');
        
        $app = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}apps WHERE app_id = ? AND `user` = ?",
            [$appId, $this->apiUserId]
        );

        if (!$app) {
            error('应用不存在');
        }

        $updateData = [];
        
        if ($this->input('app_name')) {
            $updateData['app_name'] = trim($this->input('app_name'));
        }
        if ($this->input('domain')) {
            $domain = trim($this->input('domain'));
            $domain = preg_replace('#^https?://#', '', $domain);
            $updateData['domain'] = rtrim($domain, '/');
        }
        if ($this->input('callback_url')) {
            $updateData['callback'] = trim($this->input('callback_url'));
        }
        if ($this->input('status') !== null) {
            $updateData['status'] = (int) $this->input('status');
        }

        if (!empty($updateData)) {
            $this->db->update('apps', $updateData, 'app_id = ?', [$appId]);
        }

        success(null, '更新成功');
    }

    /**
     * 删除应用
     */
    public function delete()
    {
        $appId = $this->input('app_id', '');
        
        $app = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}apps WHERE app_id = ? AND `user` = ?",
            [$appId, $this->apiUserId]
        );

        if (!$app) {
            error('应用不存在');
        }

        $this->db->delete('apps', 'app_id = ?', [$appId]);

        success(null, '删除成功');
    }

    /**
     * 应用列表
     */
    public function list()
    {
        $apps = $this->db->fetchAll(
            "SELECT app_id, app_name, domain, callback, status, daily_limit, today_calls, total_calls, `time` 
             FROM {$this->db->getPrefix()}apps WHERE `user` = ? ORDER BY `time` DESC",
            [$this->apiUserId]
        );

        success($apps);
    }

    /**
     * 应用详情
     */
    public function info()
    {
        $appId = $this->input('app_id', '');
        
        $app = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}apps WHERE app_id = ? AND `user` = ?",
            [$appId, $this->apiUserId]
        );

        if (!$app) {
            error('应用不存在');
        }

        unset($app['id'], $app['user']);

        success($app);
    }

    /**
     * 登录日志
     */
    public function logs()
    {
        $appId = $this->input('app_id', '');
        $page = max(1, (int) $this->input('page', 1));
        $limit = min(100, max(1, (int) $this->input('limit', 20)));
        $offset = ($page - 1) * $limit;
        $app = $this->db->fetch(
            "SELECT id FROM {$this->db->getPrefix()}apps WHERE app_id = ? AND `user` = ?",
            [$appId, $this->apiUserId]
        );

        if (!$app) {
            error('应用不存在');
        }

        $total = $this->db->fetch(
            "SELECT COUNT(*) as count FROM {$this->db->getPrefix()}oauth_logs WHERE app_id = ? AND status = 1",
            [$appId]
        )['count'];

        $logs = $this->db->fetchAll(
            "SELECT l.type as platform, l.open_id, ou.nickname, ou.avatar, ou.gender, l.ip, l.`time` 
             FROM {$this->db->getPrefix()}oauth_logs l 
             LEFT JOIN {$this->db->getPrefix()}oauth_users ou ON l.app_id = ou.app_id AND l.type = ou.type AND l.open_id = ou.open_id
             WHERE l.app_id = ? AND l.status = 1
             ORDER BY l.`time` DESC LIMIT {$limit} OFFSET {$offset}",
            [$appId]
        );

        success([
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'list' => $logs,
        ]);
    }
}
