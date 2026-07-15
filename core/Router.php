<?php
namespace Core;

/**
 * 路由类
 */
class Router
{
    protected $routes = [];
    protected $currentGroup = '';
    protected $currentMiddleware = [];

    /**
     * 添加路由
     */
    public function add($method, $pattern, $handler, $middleware = [])
    {
        $pattern = $this->currentGroup . '/' . trim($pattern, '/');
        $pattern = '/' . trim($pattern, '/');
        
        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => $pattern,
            'handler' => $handler,
            'middleware' => array_merge($this->currentMiddleware, $middleware),
        ];
    }

    public function get($pattern, $handler, $middleware = [])
    {
        $this->add('GET', $pattern, $handler, $middleware);
    }

    public function post($pattern, $handler, $middleware = [])
    {
        $this->add('POST', $pattern, $handler, $middleware);
    }

    public function any($pattern, $handler, $middleware = [])
    {
        $this->add('GET', $pattern, $handler, $middleware);
        $this->add('POST', $pattern, $handler, $middleware);
    }

    /**
     * 路由分组
     */
    public function group($prefix, $callback, $middleware = [])
    {
        $previousGroup = $this->currentGroup;
        $previousMiddleware = $this->currentMiddleware;

        $this->currentGroup = $previousGroup . '/' . trim($prefix, '/');
        $this->currentMiddleware = array_merge($previousMiddleware, $middleware);

        $callback($this);

        $this->currentGroup = $previousGroup;
        $this->currentMiddleware = $previousMiddleware;
    }

    /**
     * 分发路由
     */
    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = '/' . trim($uri, '/');
        $this->handleCors();
        if ($method === 'OPTIONS') {
            http_response_code(204);
            exit;
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $params = $this->matchRoute($route['pattern'], $uri);
            if ($params !== false) {
                foreach ($route['middleware'] as $middleware) {
                    $this->runMiddleware($middleware);
                }
                return $this->runHandler($route['handler'], $params);
            }
        }
        error('路由不存在', 404, 404);
    }

    /**
     * 匹配路由
     */
    protected function matchRoute($pattern, $uri)
    {
        $regex = preg_replace('/\{(\w+)\}/', '([^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        if (preg_match($regex, $uri, $matches)) {
            array_shift($matches); // 移除完整匹配项
            return $matches;       // 返回索引数组
        }

        return false;
    }

    /**
     * 执行中间件
     */
    protected function runMiddleware($middleware)
    {
        $class = "\\App\\Middleware\\{$middleware}";
        if (class_exists($class)) {
            $instance = new $class();
            $instance->handle();
        }
    }

    /**
     * 执行处理器
     */
    protected function runHandler($handler, $params = [])
    {
        if (is_callable($handler)) {
            return call_user_func_array($handler, $params);
        }

        if (is_string($handler)) {
            list($controller, $method) = explode('@', $handler);
            $class = "\\App\\Controllers\\{$controller}";
            
            if (class_exists($class)) {
                $instance = new $class();
                return call_user_func_array([$instance, $method], $params);
            }
        }

        error('处理器不存在', 500, 500);
    }

    /**
     * 处理CORS
     */
    protected function handleCors()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Max-Age: 86400');
    }
}
