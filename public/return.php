<?php
/**
 * 第三方平台回调入口
 * 兼容 return.php 调用方式
 */
$_SERVER['REQUEST_URI'] = '/return' . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '');
require __DIR__ . '/index.php';
