<?php
/**
 * 安装检测中间件
 * 
 * 负责检测系统安装状态并进行相应的重定向处理：
 * - 未安装时：重定向到安装页面
 * - 已安装时：阻止访问安装路由
 * 
 * Requirements: 1.1, 1.2, 1.3
 */

namespace App\Middleware;

class InstallCheckMiddleware
{
    /**
     * 安装锁文件路径
     * @var string
     */
    protected string $lockFilePath;
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->lockFilePath = ML_ROOT . '/config/install.lock';
    }
    
    /**
     * 处理请求
     * 
     * 检测安装状态并执行相应的重定向逻辑：
     * - 未安装且不在安装页面：重定向到 /install
     * - 已安装且访问安装页面：重定向到首页并提示
     * 
     * @return void
     * 
     * Requirements: 1.1, 1.2, 1.3
     */
    public function handle(): void
    {
        $uri = $this->getCurrentUri();
        $isInstalled = $this->isInstalled();
        $isInstallRoute = $this->isInstallRoute($uri);
        $isStaticAsset = $this->isStaticAsset($uri);
        if ($isStaticAsset) {
            return;
        }
        if (!$isInstalled && !$isInstallRoute) {
            $this->redirectToInstall();
            return;
        }
        if ($isInstalled && $isInstallRoute) {
            $this->redirectToHomeWithMessage();
            return;
        }
    }
    
    /**
     * 检查系统是否已安装
     * 
     * @return bool
     * 
     * Requirements: 1.1, 1.2, 1.4
     */
    public function isInstalled(): bool
    {
        return file_exists($this->lockFilePath);
    }
    
    /**
     * 获取当前请求URI
     * 
     * @return string
     */
    protected function getCurrentUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        return parse_url($uri, PHP_URL_PATH) ?: '/';
    }
    
    /**
     * 检查是否为安装相关路由
     * 
     * @param string $uri 请求URI
     * @return bool
     */
    protected function isInstallRoute(string $uri): bool
    {
        return strpos($uri, '/install') === 0;
    }
    
    /**
     * 检查是否为静态资源请求
     * 
     * @param string $uri 请求URI
     * @return bool
     */
    protected function isStaticAsset(string $uri): bool
    {
        return strpos($uri, '/assets') === 0;
    }
    
    /**
     * 重定向到安装页面
     * 
     * @return void
     * 
     * Requirements: 1.1
     */
    protected function redirectToInstall(): void
    {
        header('Location: /install');
        exit;
    }
    
    /**
     * 重定向到首页并显示提示信息
     * 
     * @return void
     * 
     * Requirements: 1.3
     */
    protected function redirectToHomeWithMessage(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['flash_message'] = '系统已安装，无法再次访问安装页面';
            $_SESSION['flash_type'] = 'warning';
        }
        
        header('Location: /');
        exit;
    }
}
