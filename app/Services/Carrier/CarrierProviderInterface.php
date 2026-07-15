<?php
namespace App\Services\Carrier;

/**
 * 运营商认证供应商接口
 * 定义认证供应商的统一接口，便于后续扩展
 */
interface CarrierProviderInterface
{
    /**
     * 执行三要素认证
     * @param string $name 姓名
     * @param string $idCard 身份证号
     * @param string $mobile 手机号
     * @param string $orderId 订单号
     * @return array ['success' => bool, 'matched' => bool, 'carrier_type' => string, 'message' => string, 'raw_data' => array]
     */
    public function verify(string $name, string $idCard, string $mobile, string $orderId): array;
    
    /**
     * 获取供应商标识
     * @return string
     */
    public function getProviderName(): string;
}
