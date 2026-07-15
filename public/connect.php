<?php
/**
 * 彩虹聚合登录API入口
 * 兼容 connect.php 调用方式
 */
$_SERVER['REQUEST_URI'] = '/connect' . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '');
require __DIR__ . '/index.php';
