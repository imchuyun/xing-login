<?php
/**
 * 调试日志服务
 * 用于追踪OAuth登录流程
 */

namespace App\Services;

class DebugLogger
{
    private static $logFile = null;
    
    /**
     * 初始化日志文件路径
     */
    private static function init()
    {
        if (self::$logFile === null) {
            $logDir = dirname(__DIR__, 2) . '/storage/logs';
            if (!is_dir($logDir)) {
                @mkdir($logDir, 0755, true);
            }
            self::$logFile = $logDir . '/oauth_debug_' . date('Y-m-d') . '.log';
        }
    }
    
    /**
     * 记录日志
     */
    public static function log($tag, $message, $data = null)
    {
        self::init();
        
        $time = date('Y-m-d H:i:s');
        $content = "[{$time}] [{$tag}] {$message}";
        
        if ($data !== null) {
            if (is_array($data) || is_object($data)) {
                $content .= "\n" . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            } else {
                $content .= " => " . $data;
            }
        }
        
        $content .= "\n" . str_repeat('-', 80) . "\n";
        
        @file_put_contents(self::$logFile, $content, FILE_APPEND);
    }
    
    /**
     * 记录OAuth授权发起
     */
    public static function logAuthStart($platform, $redirectUri, $state)
    {
        self::log('AUTH_START', "发起OAuth授权", [
            'platform' => $platform,
            'redirect_uri' => $redirectUri,
            'state' => $state,
            'session_id' => session_id(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'client_ip' => self::getClientIp(),
        ]);
    }
    
    /**
     * 记录OAuth回调
     */
    public static function logCallback($platform, $params)
    {
        self::log('CALLBACK', "收到OAuth回调", [
            'platform' => $platform,
            'GET_params' => $_GET,
            'session_id' => session_id(),
            'session_oauth_state' => $_SESSION['oauth_state'] ?? 'NOT_SET',
            'session_oauth_platform' => $_SESSION['oauth_platform'] ?? 'NOT_SET',
        ]);
    }
    
    /**
     * 记录获取Token
     */
    public static function logTokenRequest($platform, $code, $response)
    {
        self::log('TOKEN', "获取AccessToken", [
            'platform' => $platform,
            'code' => substr($code, 0, 10) . '...',
            'response' => is_string($response) ? $response : json_encode($response),
        ]);
    }
    
    /**
     * 记录用户信息
     */
    public static function logUserInfo($platform, $userInfo)
    {
        self::log('USER_INFO', "获取用户信息", [
            'platform' => $platform,
            'user_info' => $userInfo,
        ]);
    }
    
    /**
     * 记录登录结果
     */
    public static function logLoginResult($platform, $success, $message, $userId = null)
    {
        self::log('LOGIN_RESULT', $success ? "登录成功" : "登录失败", [
            'platform' => $platform,
            'success' => $success,
            'message' => $message,
            'user_id' => $userId,
        ]);
    }
    
    /**
     * 记录错误
     */
    public static function logError($tag, $message, $exception = null)
    {
        $data = ['error' => $message];
        if ($exception instanceof \Exception) {
            $data['exception'] = [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ];
        }
        self::log($tag . '_ERROR', $message, $data);
    }
    
    /**
     * 获取客户端IP
     */
    private static function getClientIp()
    {
        $keys = ['HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        foreach ($keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = explode(',', $_SERVER[$key])[0];
                return trim($ip);
            }
        }
        return 'unknown';
    }
}
