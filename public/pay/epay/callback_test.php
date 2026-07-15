<?php
/**
 * 最简单的回调测试 - 完全独立，不依赖任何框架
 * 用于确认易支付回调是否能到达服务器
 */
$logDir = dirname(dirname(dirname(__DIR__))) . '/storage/logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
}
$logFile = $logDir . '/callback_raw.log';
$data = [
    'time' => date('Y-m-d H:i:s'),
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
    'uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
    'query' => $_SERVER['QUERY_STRING'] ?? '',
    'get' => $_GET,
    'post' => $_POST,
    'raw_input' => file_get_contents('php://input'),
    'headers' => getallheaders() ?: [],
];

$logContent = "\n" . str_repeat('=', 80) . "\n";
$logContent .= json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
$logContent .= "\n" . str_repeat('=', 80) . "\n";

@file_put_contents($logFile, $logContent, FILE_APPEND);
echo 'success';
