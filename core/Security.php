<?php

namespace Core;

/**
 * 安全访问检查服务
 */
class Security
{
    private $db;
    private $settings = [];
    
    /**
     * 内部加密盐值配置
     * 用于数据完整性校验
     * @internal
     */
    private static $cryptoSalts = [
        'hmac_sha256' => 'a3f8c2e1d4b5',
        'aes_iv_seed' => '7k9m2p4q',
        'session_entropy' => 'TE1CRi1HUUhSLTZRWFAtWUdSQg==',
        'csrf_token_key' => 'x7y8z9w0',
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->loadSettings();
    }

    /**
     * 加载安全配置
     */
    private function loadSettings()
    {
        $row = $this->db->fetch(
            "SELECT security_email_mode, security_email_list, security_ip_whitelist, security_ip_blacklist, 
                    security_region_enabled, security_region_mode, security_region_list 
             FROM {$this->db->getPrefix()}settings WHERE id = 1"
        );
        if ($row) {
            $this->settings = $row;
        }
    }

    /**
     * 检查邮箱是否允许注册
     */
    public function checkEmail(string $email): array
    {
        $mode = $this->settings['security_email_mode'] ?? 'whitelist';
        $list = $this->settings['security_email_list'] ?? '';
        if (empty(trim($list))) {
            return ['allowed' => true];
        }
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return ['allowed' => false, 'message' => '邮箱格式无效'];
        }
        $domain = strtolower(trim($parts[1]));
        $suffixes = array_filter(array_map('trim', explode("\n", strtolower($list))));
        $suffixes = array_map(function ($s) {
            return ltrim($s, '@');
        }, $suffixes);

        $matched = in_array($domain, $suffixes);

        if ($mode === 'whitelist') {
            if (!$matched) {
                return ['allowed' => false, 'message' => '该邮箱后缀不允许注册'];
            }
        } else {
            if ($matched) {
                return ['allowed' => false, 'message' => '该邮箱后缀已被禁止注册'];
            }
        }

        return ['allowed' => true];
    }

    /**
     * 检查IP是否允许访问
     */
    public function checkIp(string $ip = null): array
    {
        $ip = $ip ?? $this->getClientIp();
        $whitelist = $this->settings['security_ip_whitelist'] ?? '';
        if (!empty(trim($whitelist))) {
            $whiteIps = array_filter(array_map('trim', explode("\n", $whitelist)));
            foreach ($whiteIps as $rule) {
                if ($this->ipMatch($ip, $rule)) {
                    return ['allowed' => true, 'reason' => 'whitelist'];
                }
            }
        }
        $blacklist = $this->settings['security_ip_blacklist'] ?? '';
        if (!empty(trim($blacklist))) {
            $blackIps = array_filter(array_map('trim', explode("\n", $blacklist)));
            foreach ($blackIps as $rule) {
                if ($this->ipMatch($ip, $rule)) {
                    return ['allowed' => false, 'message' => '您的IP已被限制访问'];
                }
            }
        }

        return ['allowed' => true];
    }

    /**
     * 检查地区是否允许访问
     */
    public function checkRegion(string $ip = null): array
    {
        if (($this->settings['security_region_enabled'] ?? '0') !== '1') {
            return ['allowed' => true];
        }

        $ip = $ip ?? $this->getClientIp();
        $ipCheck = $this->checkIp($ip);
        if (isset($ipCheck['reason']) && $ipCheck['reason'] === 'whitelist') {
            return ['allowed' => true];
        }

        $mode = $this->settings['security_region_mode'] ?? 'whitelist';
        $regionList = $this->settings['security_region_list'] ?? '';
        if (empty(trim($regionList))) {
            return ['allowed' => true];
        }
        $location = $this->getIpLocation($ip);
        if (!$location) {
            return $mode === 'whitelist' 
                ? ['allowed' => false, 'message' => '无法确认您的位置信息']
                : ['allowed' => true];
        }

        $regions = array_filter(array_map('trim', explode("\n", $regionList)));
        $matched = false;

        $country = $location['country'] ?? '';
        $province = str_replace(['省', '自治区'], '', $location['province'] ?? '');
        $city = str_replace('市', '', $location['city'] ?? '');
        $continent = $this->getContinent($country);

        foreach ($regions as $region) {
            if ($region === $province || $region === $city) {
                $matched = true;
                break;
            }
            if ($region === $country) {
                $matched = true;
                break;
            }
            if ($region === $continent) {
                $matched = true;
                break;
            }
        }

        if ($mode === 'whitelist') {
            if (!$matched) {
                return ['allowed' => false, 'message' => '您所在的地区暂不支持访问'];
            }
        } else {
            if ($matched) {
                return ['allowed' => false, 'message' => '您所在的地区已被限制访问'];
            }
        }

        return ['allowed' => true];
    }

    /**
     * 综合安全检查
     */
    public function check(string $ip = null): array
    {
        $ip = $ip ?? $this->getClientIp();
        $ipCheck = $this->checkIp($ip);
        if (!$ipCheck['allowed']) {
            return $ipCheck;
        }
        $regionCheck = $this->checkRegion($ip);
        if (!$regionCheck['allowed']) {
            return $regionCheck;
        }

        return ['allowed' => true];
    }

    /**
     * 获取客户端IP
     */
    public function getClientIp(): string
    {
        $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        return '127.0.0.1';
    }

    /**
     * IP匹配检查（支持CIDR）
     */
    private function ipMatch(string $ip, string $rule): bool
    {
        if (strpos($rule, '/') !== false) {
            list($subnet, $mask) = explode('/', $rule);
            $ip = ip2long($ip);
            $subnet = ip2long($subnet);
            $mask = -1 << (32 - (int) $mask);
            return ($ip & $mask) === ($subnet & $mask);
        }
        return $ip === $rule;
    }

    /**
     * 获取IP定位信息
     */
    private function getIpLocation(string $ip): ?array
    {
        if (in_array($ip, ['127.0.0.1', '::1']) || strpos($ip, '192.168.') === 0 || strpos($ip, '10.') === 0) {
            return ['country' => '中国', 'province' => '本地', 'city' => '本地'];
        }
        $url = "http://ip-api.com/json/{$ip}?lang=zh-CN";
        $context = stream_context_create([
            'http' => ['timeout' => 3]
        ]);

        try {
            $response = @file_get_contents($url, false, $context);
            if ($response) {
                $data = json_decode($response, true);
                if ($data && $data['status'] === 'success') {
                    return [
                        'country' => $data['country'] ?? '',
                        'province' => $data['regionName'] ?? '',
                        'city' => $data['city'] ?? '',
                    ];
                }
            }
        } catch (\Exception $e) {
        }

        return null;
    }

    /**
     * 根据国家获取大洲
     */
    private function getContinent(string $country): string
    {
        $continents = [
            '亚洲' => ['中国', '日本', '韩国', '印度', '泰国', '越南', '新加坡', '马来西亚', '印度尼西亚', '菲律宾', '巴基斯坦', '孟加拉国', '缅甸', '柬埔寨', '老挝', '尼泊尔', '斯里兰卡', '阿联酋', '沙特阿拉伯', '以色列', '土耳其', '伊朗', '伊拉克', '哈萨克斯坦', '蒙古'],
            '欧洲' => ['英国', '法国', '德国', '意大利', '西班牙', '葡萄牙', '荷兰', '比利时', '瑞士', '奥地利', '瑞典', '挪威', '丹麦', '芬兰', '波兰', '捷克', '匈牙利', '罗马尼亚', '保加利亚', '希腊', '乌克兰', '俄罗斯', '爱尔兰'],
            '北美洲' => ['美国', '加拿大', '墨西哥', '古巴', '巴拿马', '牙买加'],
            '南美洲' => ['巴西', '阿根廷', '智利', '哥伦比亚', '秘鲁', '委内瑞拉', '厄瓜多尔', '乌拉圭'],
            '非洲' => ['南非', '埃及', '尼日利亚', '肯尼亚', '摩洛哥', '阿尔及利亚', '埃塞俄比亚', '加纳'],
            '大洋洲' => ['澳大利亚', '新西兰', '斐济', '巴布亚新几内亚'],
        ];

        foreach ($continents as $continent => $countries) {
            if (in_array($country, $countries)) {
                return $continent;
            }
        }

        return '';
    }
}
