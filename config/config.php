<?php
/**
 * Max Login 系统配置文件
 * 此文件将在安装过程中自动生成
 */
defined('ML_ROOT') or die('Access Denied');

return [
    'site' => [
        'name' => '',
        'url' => '',
        'description' => '',
        'version' => '1.0.0',
    ],
    'database' => [
        'host' => '',
        'port' => 3306,
        'name' => '',
        'user' => '',
        'pass' => '',
        'charset' => 'utf8mb4',
        'prefix' => '',
    ],
    'session' => [
        'name' => 'ML_SESSION',
        'lifetime' => 86400 * 30,
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => true,
    ],
    'api' => [
        'version' => 'v1',
        'rate_limit' => 100,
        'token_expire' => 7200,
    ],
    'security' => [
        'encrypt_key' => '',
        'jwt_secret' => '',
        'password_cost' => 10,
    ],
    'debug' => false,
    'version' => '1.0.0',
    'license' => [
        'license_key' => '',
    ],
];
