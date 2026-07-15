<?php
/**
 * 回调URL可访问性测试
 * 访问这个页面如果能看到 "OK"，说明路径可访问
 */
header('Content-Type: text/plain');
echo "OK - " . date('Y-m-d H:i:s');
$logDir = dirname(dirname(dirname(__DIR__))) . '/storage/logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
}
$logFile = $logDir . '/notify_access.log';
$log = date('Y-m-d H:i:s') . " - IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . " - test_notify accessed\n";
@file_put_contents($logFile, $log, FILE_APPEND);
