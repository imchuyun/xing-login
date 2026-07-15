<?php
namespace Core;

use App\Middleware\InstallCheckMiddleware;

/**
 * 应用核心类
 */
class Application
{
    protected $router;
    protected $db;
    
    /**
     * 安装检测中间件
     * @var InstallCheckMiddleware
     */
    protected InstallCheckMiddleware $installCheckMiddleware;
    
    /**
     * 启动时间戳
     */
    private static int $bootTime = 0;

    public function __construct()
    {
        self::$bootTime = time();
        $this->init();
    }

    /**
     * 初始化
     */
    protected function init()
    {
        date_default_timezone_set('Asia/Shanghai');
        $needsInstall = !file_exists(ML_ROOT . '/config/install.lock');
        $debug = $needsInstall ? true : config('debug');
        if ($debug) {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        } else {
            error_reporting(0);
            ini_set('display_errors', 0);
        }
        $this->startSession($needsInstall);
        if (!$needsInstall) {
            $this->db = Database::getInstance();
        }
        $this->router = new Router();
        $this->loadRoutes();
    }

    /**
     * 启动Session
     */
    protected function startSession(bool $installMode = false)
    {
        if ($installMode) {
            session_name('ML_INSTALL');
            session_start();
            return;
        }
        
        $config = config('session');
        ini_set('session.gc_maxlifetime', $config['lifetime']);
        session_name($config['name']);
        session_set_cookie_params(
            $config['lifetime'],
            $config['path'],
            $config['domain'],
            $config['secure'],
            $config['httponly']
        );
        
        session_start();
        if (isset($_COOKIE[$config['name']])) {
            setcookie(
                $config['name'],
                session_id(),
                time() + $config['lifetime'],
                $config['path'],
                $config['domain'],
                $config['secure'],
                $config['httponly']
            );
        }
    }

    /**
     * 加载路由
     */
    protected function loadRoutes()
    {
        $router = $this->router;
        require ML_ROOT . '/routes/web.php';
        require ML_ROOT . '/routes/api.php';
    }

    /**
     * 运行应用
     */
    public function run()
    {
        try {
            $this->installCheckMiddleware = new InstallCheckMiddleware();
            $this->installCheckMiddleware->handle();
            
            $this->router->dispatch();
        } catch (\Throwable $e) {
            $logFile = ML_ROOT . '/runtime/error.log';
            $logDir = dirname($logFile);
            if (!is_dir($logDir)) {
                @mkdir($logDir, 0755, true);
            }
            $logMsg = '[' . date('Y-m-d H:i:s') . '] ' . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n\n";
            @file_put_contents($logFile, $logMsg, FILE_APPEND);
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                      strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
            $isJson = strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false ||
                      strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;
            $isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
            
            if ($isAjax || $isJson || $isPost) {
                error($e->getMessage());
            } else {
                if (config('debug')) {
                    echo '<pre>' . $e->getMessage() . "\n" . $e->getTraceAsString() . '</pre>';
                } else {
                    error('系统错误', 500, 500);
                }
            }
        }
    }
    
    /**
     * 增强验证检查
     */
    protected function enhancedCheck(): void
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        
        // 后台路径验证已移除
    }

    /**
     * 获取路由器
     */
    public function getRouter()
    {
        return $this->router;
    }
}
