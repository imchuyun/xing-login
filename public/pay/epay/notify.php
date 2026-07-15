<?php
/**
 * 易支付回调入口文件
 * 
 * 这个文件直接处理回调，绕过路由系统
 * 用于排查路由问题
 */
$logDir = dirname(dirname(dirname(__DIR__))) . '/storage/logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
}

$logFile = $logDir . '/epay_notify_direct.log';
$logContent = "\n" . str_repeat('=', 60) . "\n";
$logContent .= "时间: " . date('Y-m-d H:i:s') . "\n";
$logContent .= "IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "\n";
$logContent .= "Method: " . $_SERVER['REQUEST_METHOD'] . "\n";
$logContent .= "GET: " . json_encode($_GET, JSON_UNESCAPED_UNICODE) . "\n";
$logContent .= "POST: " . json_encode($_POST, JSON_UNESCAPED_UNICODE) . "\n";
$logContent .= str_repeat('=', 60) . "\n";
@file_put_contents($logFile, $logContent, FILE_APPEND);
define('ML_ROOT', dirname(dirname(dirname(__DIR__))));

try {
    require_once ML_ROOT . '/core/Autoloader.php';
    require_once ML_ROOT . '/core/helpers.php';
    $controller = new \App\Controllers\PayController();
    $controller->epayNotify();
} catch (Exception $e) {
    $errorLog = "错误: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
    @file_put_contents($logFile, $errorLog, FILE_APPEND);
    exit('FAIL: ' . $e->getMessage());
}
