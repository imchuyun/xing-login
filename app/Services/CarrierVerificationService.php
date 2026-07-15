<?php
namespace App\Services;

use App\Services\Carrier\CarrierProviderInterface;
use App\Services\Carrier\SlsjProvider;
use App\Services\Carrier\ShuxunProvider;
use App\Services\Carrier\ChuanglanProvider;

/**
 * 运营商三要素认证服务
 * 统一的认证服务入口，负责选择供应商并执行认证
 */
class CarrierVerificationService
{
    private $db;
    private ?CarrierProviderInterface $provider = null;
    
    public function __construct($db)
    {
        $this->db = $db;
        $this->initProvider();
    }
    
    /**
     * 初始化认证供应商
     */
    private function initProvider(): void
    {
        $config = $this->db->fetch(
            "SELECT * FROM {$this->db->getPrefix()}verification_config WHERE id = 1"
        );
        
        if (!$config || empty($config['carrier'])) {
            return;
        }
        
        $provider = $config['provider'] ?? 'slsj';
        
        switch ($provider) {
            case 'slsj':
                // 读取随联数聚专用字段
                $memberId = $config['slsj_member_id'] ?? '';
                $appKey = !empty($config['slsj_app_key']) ? decrypt($config['slsj_app_key']) : '';
                $apiUrl = $config['slsj_api_url'] ?? 'https://api.slsj.com';
                $this->provider = new SlsjProvider($memberId, $appKey, $apiUrl);
                break;
                
            case 'shuxun':
                // 读取数勋科技专用字段
                $appKey = !empty($config['shuxun_app_key']) ? decrypt($config['shuxun_app_key']) : '';
                $appSecret = !empty($config['shuxun_app_secret']) ? decrypt($config['shuxun_app_secret']) : '';
                $apiUrl = $config['shuxun_api_url'] ?? 'https://api.shuxuntech.com';
                $this->provider = new ShuxunProvider($appKey, $appSecret, $apiUrl);
                break;
                
            case 'chuanglan':
                // 读取创蓝云智专用字段
                $appId = $config['chuanglan_app_id'] ?? '';
                $appKey = !empty($config['chuanglan_app_key']) ? decrypt($config['chuanglan_app_key']) : '';
                $apiUrl = $config['chuanglan_api_url'] ?? 'https://api.253.com';
                $this->provider = new ChuanglanProvider($appId, $appKey, $apiUrl);
                break;
        }
    }
    
    /**
     * 检查认证服务是否启用
     */
    public function isEnabled(): bool
    {
        return $this->provider !== null;
    }
    
    /**
     * 执行三要素认证
     */
    public function verify(string $name, string $idCard, string $mobile): array
    {
        if (!$this->provider) {
            return [
                'success' => false,
                'matched' => false,
                'carrier_type' => '',
                'carrier_name' => '',
                'message' => '认证服务未配置',
                'raw_data' => []
            ];
        }
        $orderId = date('YmdHis') . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        
        return $this->provider->verify($name, $idCard, $mobile, $orderId);
    }
    
    /**
     * 获取当前供应商名称
     */
    public function getProviderName(): string
    {
        return $this->provider ? $this->provider->getProviderName() : '';
    }
}
