<?php

namespace App\Controllers;

use App\Services\EpayService;
use App\Services\BillingService;
use App\Services\WechatPayService;
use App\Services\AlipayService;
use App\Services\QqpayService;

class PayController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Core\Database::getInstance();
    }

    /**
     * 易支付异步回调
     */
    public function epayNotify()
    {
        $params = !empty($_GET) ? $_GET : $_POST;
        error_log("[Epay Notify] Received params: " . json_encode($params, JSON_UNESCAPED_UNICODE));
        $settings = $this->getSettings();
        
        if (empty($settings['pay_epay_enabled']) || $settings['pay_epay_enabled'] != '1') {
            exit('FAIL: Epay not enabled');
        }

        $epay = new EpayService([
            'api_url' => $settings['pay_epay_api_url'] ?? '',
            'pid'     => $settings['pay_epay_pid'] ?? '',
            'key'     => $settings['pay_epay_key'] ?? '',
        ]);
        if (!$epay->verifySign($params)) {
            error_log("[Epay Notify] Sign verification failed!");
            error_log("[Epay Notify] Received sign: " . ($params['sign'] ?? 'empty'));
            $debugParams = $params;
            unset($debugParams['sign'], $debugParams['sign_type']);
            ksort($debugParams);
            $queryParts = [];
            foreach ($debugParams as $key => $value) {
                if ($value !== '' && $value !== null) {
                    $queryParts[] = $key . '=' . $value;
                }
            }
            $queryString = implode('&', $queryParts);
            error_log("[Epay Notify] Query string for sign: " . $queryString);
            error_log("[Epay Notify] Calculated sign: " . md5($queryString . ($settings['pay_epay_key'] ?? '')));
            
            exit('FAIL: Sign error');
        }
        
        error_log("[Epay Notify] Sign verification passed!");
        $orderNo = $params['out_trade_no'] ?? '';
        $tradeNo = $params['trade_no'] ?? '';
        $tradeStatus = $params['trade_status'] ?? '';
        $money = $params['money'] ?? 0;

        if (empty($orderNo)) {
            exit('FAIL: Order not found');
        }
        if ($tradeStatus !== 'TRADE_SUCCESS') {
            exit('success');
        }
        $order = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}orders WHERE no = ?",
            [$orderNo]
        );

        if (!$order) {
            exit('FAIL: Order not exists');
        }
        if ($order['status'] == 1) {
            exit('success');
        }
        if (sprintf('%.2f', $order['amount']) !== sprintf('%.2f', $money)) {
            exit('FAIL: Amount error');
        }
        $this->db->update('orders', [
            'status'       => 1,
            'paid_time'    => date('Y-m-d H:i:s'),
            'epay_no'      => $tradeNo,
        ], 'id = ?', [$order['id']]);
        $this->processOrderSuccess($order);

        exit('success');
    }

    /**
     * 易支付同步回调
     */
    public function epayReturn()
    {
        $params = $_GET;
        $settings = $this->getSettings();
        
        $epay = new EpayService([
            'api_url' => $settings['pay_epay_api_url'] ?? '',
            'pid'     => $settings['pay_epay_pid'] ?? '',
            'key'     => $settings['pay_epay_key'] ?? '',
        ]);
        if (!$epay->verifySign($params)) {
            redirect('/user/orders?msg=签名验证失败');
            return;
        }

        $orderNo = $params['out_trade_no'] ?? '';
        $tradeStatus = $params['trade_status'] ?? '';
        
        // 检查是否是认证订单
        if (strpos($orderNo, 'VF') === 0) {
            if ($tradeStatus === 'TRADE_SUCCESS') {
                redirect('/user/verification?msg=支付成功，正在处理认证');
            } else {
                redirect('/user/verification?msg=支付处理中');
            }
            return;
        }

        if ($tradeStatus === 'TRADE_SUCCESS') {
            redirect('/user/orders?msg=支付成功');
        } else {
            redirect('/user/orders?msg=支付处理中');
        }
    }
    
    /**
     * 微信支付二维码页面
     */
    public function wechatQrcode()
    {
        $orderNo = $_GET['order_no'] ?? '';
        $codeUrl = $_GET['code_url'] ?? '';
        
        if (empty($orderNo) || empty($codeUrl)) {
            redirect('/user/orders?msg=参数错误');
            return;
        }
        
        $order = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}orders WHERE no = ?",
            [$orderNo]
        );
        
        if (!$order) {
            redirect('/user/orders?msg=订单不存在');
            return;
        }
        
        if ($order['status'] == 1) {
            redirect('/user/orders?msg=订单已支付');
            return;
        }
        
        // 渲染二维码页面
        include ML_ROOT . '/views/pay/wechat_qrcode.php';
    }
    
    /**
     * 微信支付异步回调
     */
    public function wechatNotify()
    {
        $xml = file_get_contents('php://input');
        error_log("[Wechat Notify] Received: " . $xml);
        
        $settings = $this->getSettings();
        
        if (empty($settings['pay_wechat_app_id']) || empty($settings['pay_wechat_mch_id']) || empty($settings['pay_wechat_api_key'])) {
            echo '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[Config error]]></return_msg></xml>';
            return;
        }
        
        $wechatPay = new WechatPayService([
            'app_id' => $settings['pay_wechat_app_id'],
            'mch_id' => $settings['pay_wechat_mch_id'],
            'api_key' => $settings['pay_wechat_api_key'],
            'notify_url' => '',
        ]);
        
        // 解析XML
        libxml_disable_entity_loader(true);
        $data = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($data === false) {
            echo $wechatPay->notifyResponse(false, 'XML parse error');
            return;
        }
        $params = json_decode(json_encode($data), true);
        
        // 验证签名
        if (!$wechatPay->verifyNotify($params)) {
            error_log("[Wechat Notify] Sign verification failed!");
            echo $wechatPay->notifyResponse(false, 'Sign error');
            return;
        }
        
        if ($params['return_code'] !== 'SUCCESS' || $params['result_code'] !== 'SUCCESS') {
            echo $wechatPay->notifyResponse(true);
            return;
        }
        
        $orderNo = $params['out_trade_no'] ?? '';
        $transactionId = $params['transaction_id'] ?? '';
        $totalFee = $params['total_fee'] ?? 0;
        
        if (empty($orderNo)) {
            echo $wechatPay->notifyResponse(false, 'Order not found');
            return;
        }
        
        $order = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}orders WHERE no = ?",
            [$orderNo]
        );
        
        if (!$order) {
            echo $wechatPay->notifyResponse(false, 'Order not exists');
            return;
        }
        
        if ($order['status'] == 1) {
            echo $wechatPay->notifyResponse(true);
            return;
        }
        
        // 验证金额（微信返回的是分）
        $orderAmountFen = (int)($order['amount'] * 100);
        if ($orderAmountFen !== (int)$totalFee) {
            error_log("[Wechat Notify] Amount mismatch: order={$orderAmountFen}, paid={$totalFee}");
            echo $wechatPay->notifyResponse(false, 'Amount error');
            return;
        }
        
        // 更新订单状态
        $this->db->update('orders', [
            'status' => 1,
            'paid_time' => date('Y-m-d H:i:s'),
            'epay_no' => $transactionId,
        ], 'id = ?', [$order['id']]);
        
        $this->processOrderSuccess($order);
        
        error_log("[Wechat Notify] Order {$orderNo} paid successfully");
        echo $wechatPay->notifyResponse(true);
    }
    
    /**
     * 微信支付同步回调
     */
    public function wechatReturn()
    {
        $orderNo = $_GET['order_no'] ?? '';
        
        if (empty($orderNo)) {
            redirect('/user/orders?msg=参数错误');
            return;
        }
        
        // 检查是否是认证订单
        if (strpos($orderNo, 'VF') === 0) {
            redirect('/user/verification?msg=支付处理中，请稍候');
            return;
        }
        
        redirect('/user/orders?msg=支付处理中，请稍候');
    }
    
    /**
     * 检查订单支付状态（用于轮询）
     */
    public function checkOrderStatus()
    {
        $orderNo = $_GET['order_no'] ?? '';
        
        if (empty($orderNo)) {
            header('Content-Type: application/json');
            echo json_encode(['code' => 1, 'message' => '参数错误']);
            return;
        }
        
        $order = $this->db->fetch(
            "SELECT status FROM {$this->db->getPrefix()}orders WHERE no = ?",
            [$orderNo]
        );
        
        if (!$order) {
            header('Content-Type: application/json');
            echo json_encode(['code' => 1, 'message' => '订单不存在']);
            return;
        }
        
        header('Content-Type: application/json');
        echo json_encode([
            'code' => 0,
            'data' => [
                'paid' => $order['status'] == 1,
            ]
        ]);
    }
    
    /**
     * 支付宝跳转页面（输出自动提交表单）
     */
    public function alipayRedirect()
    {
        $file = $_GET['file'] ?? '';
        
        if (empty($file) || !preg_match('/^alipay_[\w]+\.html$/', $file)) {
            redirect('/user/orders?msg=参数错误');
            return;
        }
        
        $filePath = ML_ROOT . '/storage/cache/' . $file;
        
        if (!file_exists($filePath)) {
            redirect('/user/orders?msg=支付链接已过期');
            return;
        }
        
        $html = file_get_contents($filePath);
        @unlink($filePath); // 读取后删除临时文件
        
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
    }
    
    /**
     * 支付宝异步回调
     */
    public function alipayNotify()
    {
        $params = $_POST;
        error_log("[Alipay Notify] Received params: " . json_encode($params, JSON_UNESCAPED_UNICODE));
        
        $settings = $this->getSettings();
        
        if (empty($settings['pay_alipay_app_id']) || empty($settings['pay_alipay_public_key'])) {
            exit('FAIL: Config error');
        }
        
        $alipay = new AlipayService([
            'app_id' => $settings['pay_alipay_app_id'],
            'private_key' => $settings['pay_alipay_private_key'] ?? '',
            'alipay_public_key' => $settings['pay_alipay_public_key'],
            'notify_url' => '',
            'return_url' => '',
        ]);
        
        if (!$alipay->verifyNotify($params)) {
            error_log("[Alipay Notify] Sign verification failed!");
            exit('FAIL: Sign error');
        }
        
        $tradeStatus = $params['trade_status'] ?? '';
        if (!in_array($tradeStatus, ['TRADE_SUCCESS', 'TRADE_FINISHED'])) {
            exit('success');
        }
        
        $orderNo = $params['out_trade_no'] ?? '';
        $tradeNo = $params['trade_no'] ?? '';
        $totalAmount = $params['total_amount'] ?? 0;
        
        if (empty($orderNo)) {
            exit('FAIL: Order not found');
        }
        
        $order = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}orders WHERE no = ?",
            [$orderNo]
        );
        
        if (!$order) {
            exit('FAIL: Order not exists');
        }
        
        if ($order['status'] == 1) {
            exit('success');
        }
        
        if (sprintf('%.2f', $order['amount']) !== sprintf('%.2f', $totalAmount)) {
            error_log("[Alipay Notify] Amount mismatch: order={$order['amount']}, paid={$totalAmount}");
            exit('FAIL: Amount error');
        }
        
        $this->db->update('orders', [
            'status' => 1,
            'paid_time' => date('Y-m-d H:i:s'),
            'epay_no' => $tradeNo,
        ], 'id = ?', [$order['id']]);
        
        $this->processOrderSuccess($order);
        
        error_log("[Alipay Notify] Order {$orderNo} paid successfully");
        exit('success');
    }
    
    /**
     * 支付宝同步回调
     */
    public function alipayReturn()
    {
        $params = $_GET;
        $orderNo = $params['order_no'] ?? '';
        
        // 检查是否是认证订单
        if (strpos($orderNo, 'VF') === 0) {
            redirect('/user/verification?msg=支付处理中，请稍候');
            return;
        }
        
        redirect('/user/orders?msg=支付处理中，请稍候');
    }
    
    /**
     * QQ钱包二维码页面
     */
    public function qqpayQrcode()
    {
        $orderNo = $_GET['order_no'] ?? '';
        $codeUrl = $_GET['code_url'] ?? '';
        
        if (empty($orderNo) || empty($codeUrl)) {
            redirect('/user/orders?msg=参数错误');
            return;
        }
        
        $order = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}orders WHERE no = ?",
            [$orderNo]
        );
        
        if (!$order) {
            redirect('/user/orders?msg=订单不存在');
            return;
        }
        
        if ($order['status'] == 1) {
            redirect('/user/orders?msg=订单已支付');
            return;
        }
        
        include ML_ROOT . '/views/pay/qqpay_qrcode.php';
    }
    
    /**
     * QQ钱包异步回调
     */
    public function qqpayNotify()
    {
        $xml = file_get_contents('php://input');
        error_log("[QQpay Notify] Received: " . $xml);
        
        $settings = $this->getSettings();
        
        if (empty($settings['pay_qqpay_mch_id']) || empty($settings['pay_qqpay_api_key'])) {
            echo '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[Config error]]></return_msg></xml>';
            return;
        }
        
        $qqpay = new QqpayService([
            'mch_id' => $settings['pay_qqpay_mch_id'],
            'api_key' => $settings['pay_qqpay_api_key'],
            'notify_url' => '',
        ]);
        
        // 解析XML
        libxml_disable_entity_loader(true);
        $data = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($data === false) {
            echo $qqpay->notifyResponse(false, 'XML parse error');
            return;
        }
        $params = json_decode(json_encode($data), true);
        
        if (!$qqpay->verifyNotify($params)) {
            error_log("[QQpay Notify] Sign verification failed!");
            echo $qqpay->notifyResponse(false, 'Sign error');
            return;
        }
        
        if ($params['return_code'] !== 'SUCCESS' || $params['result_code'] !== 'SUCCESS') {
            echo $qqpay->notifyResponse(true);
            return;
        }
        
        $orderNo = $params['out_trade_no'] ?? '';
        $transactionId = $params['transaction_id'] ?? '';
        $totalFee = $params['total_fee'] ?? 0;
        
        if (empty($orderNo)) {
            echo $qqpay->notifyResponse(false, 'Order not found');
            return;
        }
        
        $order = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}orders WHERE no = ?",
            [$orderNo]
        );
        
        if (!$order) {
            echo $qqpay->notifyResponse(false, 'Order not exists');
            return;
        }
        
        if ($order['status'] == 1) {
            echo $qqpay->notifyResponse(true);
            return;
        }
        
        $orderAmountFen = (int)($order['amount'] * 100);
        if ($orderAmountFen !== (int)$totalFee) {
            error_log("[QQpay Notify] Amount mismatch: order={$orderAmountFen}, paid={$totalFee}");
            echo $qqpay->notifyResponse(false, 'Amount error');
            return;
        }
        
        $this->db->update('orders', [
            'status' => 1,
            'paid_time' => date('Y-m-d H:i:s'),
            'epay_no' => $transactionId,
        ], 'id = ?', [$order['id']]);
        
        $this->processOrderSuccess($order);
        
        error_log("[QQpay Notify] Order {$orderNo} paid successfully");
        echo $qqpay->notifyResponse(true);
    }
    
    /**
     * QQ钱包同步回调
     */
    public function qqpayReturn()
    {
        $orderNo = $_GET['order_no'] ?? '';
        
        if (empty($orderNo)) {
            redirect('/user/orders?msg=参数错误');
            return;
        }
        
        // 检查是否是认证订单
        if (strpos($orderNo, 'VF') === 0) {
            redirect('/user/verification?msg=支付处理中，请稍候');
            return;
        }
        
        redirect('/user/orders?msg=支付处理中，请稍候');
    }

    /**
     * 获取系统设置
     */
    private function getSettings(): array
    {
        $row = $this->db->fetch("SELECT * FROM {$this->db->getPrefix()}settings WHERE id = 1");
        return $row ?: [];
    }

    /**
     * 处理订单支付成功后的业务逻辑
     * 
     * 根据产品类型激活用户套餐，确保单套餐规则生效
     * Requirements: 1.2
     */
    private function processOrderSuccess(array $order): void
    {
        // 处理认证订单
        if ($order['product_type'] === 'verification') {
            $this->processVerificationOrder($order);
            return;
        }
        
        $product = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}products WHERE id = ?",
            [$order['product_id']]
        );

        if (!$product) {
            error_log("Order {$order['no']} paid but product not found: {$order['product_id']}");
            return;
        }
        $billingService = new BillingService($this->db);
        
        try {
            $packageId = $billingService->activatePackage(
                $order['user'],
                $order['id'],
                [
                    'id' => $product['id'],
                    'name' => $product['name'],
                    'type' => $product['type'] ?? 'package',
                    'platforms' => $product['platforms'] ?? null,
                    'duration' => $product['duration'] ?? 30,
                    'total_quota' => $product['total_quota'] ?? null,
                    'account_limit' => $product['account_limit'] ?? null
                ]
            );
            
            error_log("Order {$order['no']} paid successfully, package activated: {$packageId}, product: {$product['name']}");
        } catch (\Exception $e) {
            error_log("Order {$order['no']} paid but failed to activate package: " . $e->getMessage());
        }
    }
    
    /**
     * 处理认证订单支付成功
     */
    private function processVerificationOrder(array $order): void
    {
        $snapshot = json_decode($order['snapshot'], true);
        if (!$snapshot) {
            error_log("Verification order {$order['no']} paid but snapshot is empty");
            return;
        }
        
        $userId = $order['user'];
        $feeAmount = (float)$order['amount'];
        
        // 获取认证配置
        $config = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}verification_config WHERE id = 1"
        );
        
        if (!$config) {
            error_log("Verification order {$order['no']} paid but config not found");
            return;
        }
        
        // 检查是否已有认证记录
        $existing = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}user_verifications WHERE `user` = ? AND status IN (0, 1, 3)",
            [$userId]
        );
        
        if ($existing) {
            error_log("Verification order {$order['no']} paid but user already has pending/approved verification");
            return;
        }
        
        $carrierService = new \App\Services\CarrierVerificationService($this->db);
        
        if ($snapshot['type'] === 'personal') {
            $this->processPersonalVerificationAfterPay($userId, $config, $snapshot, $feeAmount, $carrierService);
        } else {
            $this->processEnterpriseVerificationAfterPay($userId, $config, $snapshot, $feeAmount, $carrierService);
        }
        
        error_log("Verification order {$order['no']} processed successfully");
    }
    
    /**
     * 支付成功后处理个人认证
     */
    private function processPersonalVerificationAfterPay($userId, $config, $snapshot, $feeAmount, $carrierService): void
    {
        $realName = $snapshot['real_name'];
        $idCardNumber = $snapshot['id_card_number'];
        $mobile = $snapshot['mobile'] ?? '';
        $idCardFront = $snapshot['id_card_front'] ?? null;
        $idCardBack = $snapshot['id_card_back'] ?? null;
        
        if ($carrierService->isEnabled() && !empty($mobile)) {
            $verifyResult = $carrierService->verify($realName, $idCardNumber, $mobile);
            
            if (!$verifyResult['success']) {
                // 认证服务失败，记录待人工审核
                $this->db->insert('user_verifications', [
                    'user' => $userId,
                    'type' => 'personal',
                    'status' => 3,
                    'name' => $realName,
                    'id_card' => encrypt($idCardNumber),
                    'id_card_front' => $idCardFront,
                    'id_card_back' => $idCardBack,
                    'verify_mobile' => $mobile,
                    'fee' => $feeAmount,
                    'reward' => 0,
                    'reason' => '认证服务暂不可用：' . ($verifyResult['message'] ?? ''),
                ]);
                return;
            }
            
            if ($verifyResult['matched']) {
                $this->db->insert('user_verifications', [
                    'user' => $userId,
                    'type' => 'personal',
                    'status' => 1,
                    'name' => $realName,
                    'id_card' => encrypt($idCardNumber),
                    'id_card_front' => $idCardFront,
                    'id_card_back' => $idCardBack,
                    'verify_mobile' => $mobile,
                    'carrier' => $verifyResult['carrier_type'] ?? '',
                    'verify_provider' => 'carrier_' . $carrierService->getProviderName(),
                    'verify_result' => json_encode($verifyResult['raw_data'] ?? []),
                    'verified_time' => date('Y-m-d H:i:s'),
                    'fee' => $feeAmount,
                    'reward' => 0,
                ]);
                
                $verificationId = $this->db->lastInsertId();
                $this->db->update('users', [
                    'verification' => 'personal',
                    'phone' => $mobile,
                ], 'id = ?', [$userId]);
                
                // 发放奖励
                if (!empty($config['reward']) && !empty($config['reward_product_id'])) {
                    $this->grantVerificationReward($userId, $verificationId, $config);
                }
            } else {
                $this->db->insert('user_verifications', [
                    'user' => $userId,
                    'type' => 'personal',
                    'status' => 2,
                    'name' => $realName,
                    'id_card' => encrypt($idCardNumber),
                    'id_card_front' => $idCardFront,
                    'id_card_back' => $idCardBack,
                    'verify_mobile' => $mobile,
                    'carrier' => $verifyResult['carrier_type'] ?? '',
                    'verify_provider' => 'carrier_' . $carrierService->getProviderName(),
                    'verify_result' => json_encode($verifyResult['raw_data'] ?? []),
                    'reason' => $verifyResult['message'] ?? '三要素认证不一致',
                    'fee' => $feeAmount,
                    'reward' => 0,
                ]);
            }
        } else {
            // 无运营商认证，待人工审核
            $this->db->insert('user_verifications', [
                'user' => $userId,
                'type' => 'personal',
                'status' => 3,
                'name' => $realName,
                'id_card' => encrypt($idCardNumber),
                'id_card_front' => $idCardFront,
                'id_card_back' => $idCardBack,
                'fee' => $feeAmount,
                'reward' => 0,
            ]);
        }
    }
    
    /**
     * 支付成功后处理企业认证
     */
    private function processEnterpriseVerificationAfterPay($userId, $config, $snapshot, $feeAmount, $carrierService): void
    {
        $companyName = $snapshot['company_name'];
        $creditCode = $snapshot['credit_code'];
        $legalPersonName = $snapshot['legal_person_name'];
        $legalPersonIdCard = $snapshot['legal_person_id_card'];
        $legalPersonMobile = $snapshot['legal_person_mobile'] ?? '';
        $businessLicense = $snapshot['business_license'] ?? null;
        
        if ($carrierService->isEnabled() && !empty($legalPersonMobile)) {
            $verifyResult = $carrierService->verify($legalPersonName, $legalPersonIdCard, $legalPersonMobile);
            
            if (!$verifyResult['success']) {
                $this->db->insert('user_verifications', [
                    'user' => $userId,
                    'type' => 'enterprise',
                    'status' => 3,
                    'company' => $companyName,
                    'unified_social_credit_code' => $creditCode,
                    'license' => $businessLicense,
                    'legal_person_name' => $legalPersonName,
                    'legal_person_id_card' => encrypt($legalPersonIdCard),
                    'verify_mobile' => $legalPersonMobile,
                    'fee' => $feeAmount,
                    'reward' => 0,
                    'reason' => '认证服务暂不可用：' . ($verifyResult['message'] ?? ''),
                ]);
                return;
            }
            
            if ($verifyResult['matched']) {
                $this->db->insert('user_verifications', [
                    'user' => $userId,
                    'type' => 'enterprise',
                    'status' => 1,
                    'company' => $companyName,
                    'unified_social_credit_code' => $creditCode,
                    'license' => $businessLicense,
                    'legal_person_name' => $legalPersonName,
                    'legal_person_id_card' => encrypt($legalPersonIdCard),
                    'verify_mobile' => $legalPersonMobile,
                    'carrier' => $verifyResult['carrier_type'] ?? '',
                    'verify_provider' => 'carrier_' . $carrierService->getProviderName(),
                    'verify_result' => json_encode($verifyResult['raw_data'] ?? []),
                    'verified_time' => date('Y-m-d H:i:s'),
                    'fee' => $feeAmount,
                    'reward' => 0,
                ]);
                
                $verificationId = $this->db->lastInsertId();
                $this->db->update('users', [
                    'verification' => 'enterprise',
                    'phone' => $legalPersonMobile,
                ], 'id = ?', [$userId]);
                
                if (!empty($config['reward']) && !empty($config['reward_product_id'])) {
                    $this->grantVerificationReward($userId, $verificationId, $config);
                }
            } else {
                $this->db->insert('user_verifications', [
                    'user' => $userId,
                    'type' => 'enterprise',
                    'status' => 2,
                    'company' => $companyName,
                    'unified_social_credit_code' => $creditCode,
                    'license' => $businessLicense,
                    'legal_person_name' => $legalPersonName,
                    'legal_person_id_card' => encrypt($legalPersonIdCard),
                    'verify_mobile' => $legalPersonMobile,
                    'carrier' => $verifyResult['carrier_type'] ?? '',
                    'verify_provider' => 'carrier_' . $carrierService->getProviderName(),
                    'verify_result' => json_encode($verifyResult['raw_data'] ?? []),
                    'reason' => $verifyResult['message'] ?? '法人三要素认证不一致',
                    'fee' => $feeAmount,
                    'reward' => 0,
                ]);
            }
        } else {
            $this->db->insert('user_verifications', [
                'user' => $userId,
                'type' => 'enterprise',
                'status' => 3,
                'company' => $companyName,
                'unified_social_credit_code' => $creditCode,
                'license' => $businessLicense,
                'legal_person_name' => $legalPersonName,
                'legal_person_id_card' => !empty($legalPersonIdCard) ? encrypt($legalPersonIdCard) : null,
                'fee' => $feeAmount,
                'reward' => 0,
            ]);
        }
    }
    
    /**
     * 发放认证奖励
     */
    private function grantVerificationReward($userId, $verificationId, $config): void
    {
        $product = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}products WHERE id = ? AND status = 1",
            [$config['reward_product_id']]
        );
        
        if (!$product) {
            return;
        }
        
        $billingService = new BillingService($this->db);
        $productInfo = [
            'id' => $product['id'],
            'name' => $product['name'],
            'type' => $product['type'],
            'platforms' => $product['platforms'],
            'duration' => (int)($config['reward_duration'] ?? 30),
            'total_quota' => $product['total_quota'],
            'account_limit' => $product['account_limit'],
        ];
        
        $packageId = $billingService->activatePackage(
            $userId,
            0,
            $productInfo,
            null,
            '身份认证奖励'
        );
        
        $billingService->recordPackageHistory(
            $userId,
            $packageId,
            'verification_reward',
            null,
            null,
            '身份认证成功奖励'
        );
        
        $this->db->update('user_verifications', [
            'reward' => 1,
            'reward_package_id' => $packageId,
        ], 'id = ?', [$verificationId]);
    }
}
