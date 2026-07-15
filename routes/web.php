<?php

/**
 * Web路由
 */

use Core\Router;

/** @var Router $router */
$router->get('/install', 'InstallController@index');
$router->post('/install/test-db', 'InstallController@testDb');
$router->post('/install/fetch-key', 'InstallController@fetchPublicKey');
$router->post('/install/verify-license', 'InstallController@verifyLicense');
$router->post('/install/do', 'InstallController@install');
$router->get('/', 'HomeController@index');
$router->get('/document', 'HomeController@docs');
$router->get('/about', 'HomeController@about');
$router->get('/contact', 'HomeController@contact');
$router->any('/connect.php', 'ConnectController@handle');
$router->any('/connect', 'ConnectController@handle');
$router->get('/return.php', 'ConnectController@return');
$router->get('/return', 'ConnectController@return');
$router->any('/storage/uploads/{user}/{file}', function ($user, $file) {
    $filePath = ML_ROOT . '/storage/uploads/' . $user . '/' . $file;
    $basePath = realpath(ML_ROOT . '/storage/uploads');
    $realPath = realpath($filePath);

    if (!$realPath || !file_exists($realPath) || strpos($realPath, $basePath) !== 0) {
        http_response_code(404);
        exit('Not Found');
    }
    $mimeTypes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp'
    ];
    $ext = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));
    header('Content-Type: ' . ($mimeTypes[$ext] ?? 'application/octet-stream'));
    header('Cache-Control: public, max-age=31536000');
    readfile($realPath);
    exit;
});
$router->any('/storage/uploads/verification/{userId}/{file}', function ($userId, $file) {
    $filePath = ML_ROOT . '/storage/uploads/verification/' . $userId . '/' . $file;
    $basePath = realpath(ML_ROOT . '/storage/uploads');
    $realPath = realpath($filePath);

    if (!$realPath || !file_exists($realPath) || strpos($realPath, $basePath) !== 0) {
        http_response_code(404);
        exit('Not Found');
    }
    $mimeTypes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp'
    ];
    $ext = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));
    header('Content-Type: ' . ($mimeTypes[$ext] ?? 'application/octet-stream'));
    header('Cache-Control: public, max-age=31536000');
    readfile($realPath);
    exit;
});
$router->any('/pay/epay/notify', 'PayController@epayNotify');
$router->get('/pay/epay/return', 'PayController@epayReturn');
$router->get('/pay/wechat/qrcode', 'PayController@wechatQrcode');
$router->any('/pay/wechat/notify', 'PayController@wechatNotify');
$router->get('/pay/wechat/return', 'PayController@wechatReturn');
$router->get('/pay/alipay/redirect', 'PayController@alipayRedirect');
$router->any('/pay/alipay/notify', 'PayController@alipayNotify');
$router->get('/pay/alipay/return', 'PayController@alipayReturn');
$router->get('/pay/qqpay/qrcode', 'PayController@qqpayQrcode');
$router->any('/pay/qqpay/notify', 'PayController@qqpayNotify');
$router->get('/pay/qqpay/return', 'PayController@qqpayReturn');
$router->get('/pay/check-status', 'PayController@checkOrderStatus');
$router->get('/oauth/{platform}', 'OAuthController@authorize');
$router->get('/oauth/{platform}/callback', 'OAuthController@callback');
$router->post('/api/send-verify-code', 'AuthController@sendVerifyCode');
$router->get('/user/login', 'AuthController@loginPage');
$router->post('/user/login', 'AuthController@login');
$router->get('/user/reg', 'AuthController@registerPage');
$router->post('/user/reg', 'AuthController@register');
$router->get('/user/logout', 'AuthController@logout');
$router->get('/user/findpwd', 'AuthController@findPwdPage');
$router->post('/user/findpwd', 'AuthController@findPwd');
$router->get('/auth/bind', 'AuthController@bindPage');
$router->post('/auth/bind', 'AuthController@bindAccount');
$router->get('/auth/complete-profile', 'AuthController@completeProfilePage');
$router->post('/auth/complete-profile', 'AuthController@completeProfile');
$router->group('/user', function ($r) {
    $r->get('/dashboard', 'UserController@dashboard');
    $r->get('/apps', 'UserController@apps');
    $r->post('/apps/create', 'UserController@createApp');
    $r->post('/apps/update', 'UserController@updateApp');
    $r->post('/apps/delete', 'UserController@deleteApp');
    $r->get('/app={appId}', 'UserController@appDetail');
    $r->get('/members', 'UserController@members');
    $r->get('/profile', 'UserController@profile');
    $r->post('/profile/update', 'UserController@updateProfile');
    $r->post('/profile/password', 'UserController@changePassword');
    $r->post('/profile/bind', 'UserController@bindContact');
    $r->post('/profile/unbind-oauth', 'UserController@unbindOAuth');
    $r->get('/products', 'UserController@products');
    $r->post('/products/buy', 'UserController@buyProduct');
    $r->get('/order/pay/{order_no}', 'UserController@payOrder');
    $r->post('/order/pay', 'UserController@doPay');
    $r->get('/orders', 'UserController@orders');
    $r->post('/order/cancel', 'UserController@cancelOrder');
    $r->get('/docs', 'UserController@docs');
    $r->get('/verification', 'UserController@verification');
    $r->post('/verification/personal', 'UserController@submitPersonalVerification');
    $r->post('/verification/enterprise', 'UserController@submitEnterpriseVerification');
    $r->get('/verification/callback', 'UserController@verificationCallback');
    $r->get('/verification/alipay-callback', 'UserController@alipayAuthCallback');
    $r->post('/verification/check-status', 'UserController@checkVerificationStatus');
    $r->post('/verification/cancel', 'UserController@cancelVerification');
}, ['AuthMiddleware']);
$adminPath = 'admin';
if (file_exists(ML_ROOT . '/config/install.lock')) {
    try {
        $db = \Core\Database::getInstance();
        $stmt = $db->query("SELECT * FROM settings WHERE id = 1 LIMIT 1");
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($result && isset($result['admin_path']) && !empty($result['admin_path'])) {
            $adminPath = $result['admin_path'];
        }
    } catch (\Exception $e) {
    }
}

$router->get('/' . $adminPath . '/login', 'AdminController@loginPage');
$router->post('/' . $adminPath . '/login', 'AdminController@login');
$router->get('/' . $adminPath . '/logout', 'AdminController@logout');
$router->group('/' . $adminPath, function ($r) {
    $r->get('/', 'AdminController@dashboard');
    $r->get('/users', 'AdminController@users');
    $r->post('/users/update', 'AdminController@updateUser');
    $r->get('/platforms', 'AdminController@platforms');
    $r->get('/platforms/docs', 'AdminController@platformDocs');
    $r->get('/integration/docs', 'AdminController@integrationDocs');
    $r->post('/platforms/update', 'AdminController@updatePlatform');
    $r->get('/logs', 'AdminController@logs');
    $r->get('/products', 'AdminController@products');
    $r->post('/products/save', 'AdminController@saveProduct');
    $r->post('/products/delete', 'AdminController@deleteProduct');
    $r->get('/settings', 'AdminController@settingsPage');
    $r->get('/settings/site', 'AdminController@siteSettings');
    $r->post('/settings/site/update', 'AdminController@updateSiteSettings');
    $r->post('/settings/upload-image', 'AdminController@uploadImage');
    $r->get('/settings/auth', 'AdminController@authSettings');
    $r->post('/settings/auth/update', 'AdminController@updateAuthSettings');
    $r->get('/settings/notify', 'AdminController@notify');
    $r->post('/settings/notify/update', 'AdminController@updateNotify');
    $r->post('/settings/notify/test', 'AdminController@testNotify');
    $r->get('/settings/payment', 'AdminController@payment');
    $r->get('/settings/payment/docs', 'AdminController@paymentDocs');
    $r->post('/settings/payment/update', 'AdminController@updatePayment');
    $r->get('/settings/verification', 'AdminController@verification');
    $r->post('/settings/verification/update', 'AdminController@updateVerification');
    $r->post('/settings/verification/review', 'AdminController@reviewVerification');
    $r->get('/settings/security', 'AdminController@security');
    $r->post('/settings/security/update', 'AdminController@updateSecurity');
    $r->get('/settings/billing', 'AdminController@billingSettings');
    $r->post('/settings/billing/update', 'AdminController@updateBillingSettings');
    $r->get('/billing/stats', 'AdminController@billingStats');
    $r->post('/users/grant-package', 'AdminController@grantPackage');
    $r->get('/users/package', 'AdminController@getUserPackage');
    $r->get('/orders', 'AdminController@orders');
    $r->get('/profile', 'AdminController@profile');
    $r->post('/profile/update', 'AdminController@updateProfile');
    $r->post('/profile/password', 'AdminController@changePassword');
    $r->get('/support', 'AdminController@support');
    $r->post('/check-update', 'AdminController@checkUpdate');
    $r->post('/refresh-license', 'AdminController@refreshLicense');
    $r->post('/perform-update', 'AdminController@performUpdate');
}, ['AdminMiddleware']);
