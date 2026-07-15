<?php
/**
 * Max Login 入口文件
 */
define('ML_ROOT', dirname(__DIR__));
require ML_ROOT . '/core/Autoloader.php';
$app = new \Core\Application();
$app->run();
