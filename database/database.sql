SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL COMMENT '用户名',
  `email` varchar(100) DEFAULT NULL COMMENT '邮箱',
  `phone` varchar(20) DEFAULT NULL COMMENT '手机号',
  `password` varchar(255) DEFAULT NULL COMMENT '密码(加密)',
  `status` varchar(10) NOT NULL DEFAULT 'enable' COMMENT '状态(enable/disable)',
  `role` varchar(20) NOT NULL DEFAULT 'user' COMMENT '角色(user/admin)',
  `verification` varchar(20) DEFAULT NULL COMMENT '认证状态',
  `balance` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '账户余额',
  `last_login_time` datetime DEFAULT NULL COMMENT '最后登录时间',
  `last_login_ip` varchar(45) DEFAULT NULL COMMENT '最后登录IP',
  `time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `last_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_users_username` (`username`),
  UNIQUE KEY `idx_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户表';

CREATE TABLE IF NOT EXISTS `apps` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user` int(11) unsigned NOT NULL COMMENT '所属用户ID',
  `app_name` varchar(100) NOT NULL COMMENT '应用名称',
  `description` varchar(500) DEFAULT '' COMMENT '应用描述',
  `app_id` varchar(64) NOT NULL COMMENT '应用ID(appid参数)',
  `app_secret` varchar(64) NOT NULL COMMENT '应用密钥(appkey参数)',
  `app_icon` varchar(255) DEFAULT NULL COMMENT '应用图标',
  `domain` varchar(200) NOT NULL COMMENT '授权域名',
  `callback` varchar(500) NOT NULL COMMENT '回调地址',
  `platforms` varchar(200) DEFAULT 'qq,wx,alipay' COMMENT '启用的登录平台(逗号分隔)',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态(0禁用/1启用)',
  `daily_limit` int(11) NOT NULL DEFAULT '1000' COMMENT '每日调用限制',
  `today_calls` int(11) NOT NULL DEFAULT '0' COMMENT '今日调用次数',
  `total_calls` bigint(20) NOT NULL DEFAULT '0' COMMENT '总调用次数',
  `last_call_date` date DEFAULT NULL COMMENT '最后调用日期',
  `time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `last_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_apps_app_id` (`app_id`),
  KEY `idx_apps_user` (`user`),
  KEY `idx_apps_domain` (`domain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='应用表';

CREATE TABLE IF NOT EXISTS `platforms` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT '平台标识(qq/wx/alipay等)',
  `platform` varchar(50) NOT NULL COMMENT '平台显示名称',
  `app_id` varchar(100) NOT NULL COMMENT '第三方平台AppID',
  `app_secret` text NOT NULL COMMENT '第三方平台AppSecret(加密存储)',
  `auth_url` varchar(500) NOT NULL COMMENT '授权URL',
  `token_url` varchar(500) NOT NULL COMMENT 'Token获取URL',
  `user_info_url` varchar(500) NOT NULL COMMENT '用户信息URL',
  `scope` varchar(200) DEFAULT NULL COMMENT '授权范围',
  `icon` varchar(200) DEFAULT NULL COMMENT '平台图标',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态(0禁用/1启用)',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `last_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_platforms_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='第三方登录平台配置表';

CREATE TABLE IF NOT EXISTS `oauth_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(64) NOT NULL COMMENT '内部授权码',
  `app_id` varchar(32) NOT NULL COMMENT '应用ID',
  `user` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '应用所属用户ID',
  `type` varchar(20) NOT NULL COMMENT '登录类型(qq/wx等)',
  `platform` varchar(20) NOT NULL COMMENT '平台标识',
  `domain` varchar(255) DEFAULT NULL COMMENT '请求域名',
  `redirect` text COMMENT '回调地址',
  `state` varchar(255) DEFAULT NULL COMMENT '客户端state参数',
  `platform_code` varchar(255) DEFAULT NULL COMMENT '第三方平台返回的code',
  `open_id` varchar(128) DEFAULT NULL COMMENT '用户OpenID(social_uid)',
  `ip` varchar(45) DEFAULT NULL COMMENT '用户IP',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态(0等待/1完成)',
  `time` datetime NOT NULL COMMENT '创建时间',
  `last_time` datetime DEFAULT NULL COMMENT '完成时间',
  PRIMARY KEY (`id`),
  KEY `idx_oauth_logs_code` (`code`),
  KEY `idx_oauth_logs_app_id` (`app_id`),
  KEY `idx_oauth_logs_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='OAuth登录日志表';

CREATE TABLE IF NOT EXISTS `oauth_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `app_id` varchar(32) NOT NULL COMMENT '应用ID',
  `user` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '应用所属用户ID',
  `type` varchar(20) NOT NULL COMMENT '登录类型(qq/wx等)',
  `open_id` varchar(128) NOT NULL COMMENT '用户OpenID(social_uid)',
  `access_token` varchar(255) DEFAULT NULL COMMENT '访问令牌',
  `nickname` varchar(100) DEFAULT NULL COMMENT '昵称',
  `avatar` varchar(500) DEFAULT NULL COMMENT '头像URL(faceimg)',
  `gender` varchar(10) DEFAULT 'unknown' COMMENT '性别(unknown/male/female)',
  `location` varchar(100) DEFAULT NULL COMMENT '地区',
  `ip` varchar(45) DEFAULT NULL COMMENT '最后登录IP',
  `time` datetime NOT NULL COMMENT '创建时间',
  `last_time` datetime NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_oauth_users_unique` (`app_id`, `type`, `open_id`),
  KEY `idx_oauth_users_app_id` (`app_id`),
  KEY `idx_oauth_users_user` (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='OAuth用户信息表';

CREATE TABLE IF NOT EXISTS `user_oauth` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user` int(11) DEFAULT NULL COMMENT '绑定的用户ID',
  `platform` varchar(20) NOT NULL COMMENT '平台标识',
  `open_id` varchar(128) NOT NULL COMMENT '第三方OpenID',
  `union_id` varchar(128) DEFAULT NULL COMMENT 'UnionID',
  `nickname` varchar(100) DEFAULT NULL COMMENT '昵称',
  `avatar` varchar(500) DEFAULT NULL COMMENT '头像',
  `email` varchar(100) DEFAULT NULL COMMENT '邮箱',
  `data` text COMMENT '原始数据JSON',
  `time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `last_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_user_oauth_user` (`user`),
  KEY `idx_user_oauth_platform` (`platform`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户第三方绑定表';

CREATE TABLE IF NOT EXISTS `user_login_logs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user` int(11) unsigned NOT NULL COMMENT '用户ID',
  `type` varchar(20) NOT NULL DEFAULT 'password' COMMENT '登录方式',
  `ip` varchar(45) NOT NULL COMMENT '登录IP',
  `agent` varchar(500) DEFAULT NULL COMMENT '浏览器UA',
  `device` varchar(50) DEFAULT NULL COMMENT '设备类型',
  `browser` varchar(50) DEFAULT NULL COMMENT '浏览器',
  `os` varchar(50) DEFAULT NULL COMMENT '操作系统',
  `location` varchar(100) DEFAULT NULL COMMENT '登录地点',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '登录状态(1成功/0失败)',
  `time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '登录时间',
  PRIMARY KEY (`id`),
  KEY `idx_user_login_logs_user` (`user`),
  KEY `idx_user_login_logs_time` (`time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户登录日志表';

CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT '产品名称',
  `type` enum('package','quota','account') NOT NULL DEFAULT 'package' COMMENT '类型',
  `cycle` enum('monthly','quarterly','yearly','once') NOT NULL DEFAULT 'monthly' COMMENT '计费周期',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '价格',
  `original_price` decimal(10,2) DEFAULT NULL COMMENT '原价',
  `platforms` text COMMENT '支持的平台列表(JSON)',
  `daily_limit` int(11) DEFAULT NULL COMMENT '每日限制',
  `total_quota` int(11) DEFAULT NULL COMMENT '总配额',
  `account_limit` int(11) DEFAULT NULL COMMENT '账号数量限制',
  `duration` int(11) DEFAULT NULL COMMENT '有效期(天)',
  `features` text COMMENT '功能特性列表(JSON)',
  `description` text COMMENT '描述',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态(0禁用/1启用)',
  `recommend` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否推荐',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `last_time` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_products_type` (`type`),
  KEY `idx_products_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='产品/套餐表';

CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `no` varchar(32) NOT NULL COMMENT '订单号',
  `user` int(11) NOT NULL COMMENT '用户ID',
  `product_id` int(11) NOT NULL COMMENT '产品ID',
  `product_name` varchar(100) NOT NULL COMMENT '产品名称',
  `product_type` varchar(20) NOT NULL COMMENT '产品类型',
  `amount` decimal(10,2) NOT NULL COMMENT '金额',
  `method` varchar(20) DEFAULT NULL COMMENT '支付方式',
  `epay_no` varchar(64) DEFAULT NULL COMMENT '易支付交易号',
  `official_no` varchar(64) DEFAULT NULL COMMENT '官方交易号',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态(0待支付/1已支付/2已取消)',
  `paid_time` datetime DEFAULT NULL COMMENT '支付时间',
  `expire_time` datetime DEFAULT NULL COMMENT '过期时间',
  `snapshot` text COMMENT '产品快照(JSON)',
  `time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `last_time` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_orders_no` (`no`),
  KEY `idx_orders_user` (`user`),
  KEY `idx_orders_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='订单表';

CREATE TABLE IF NOT EXISTS `user_packages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) NOT NULL COMMENT '用户ID',
  `order` int(11) NOT NULL COMMENT '订单ID',
  `product` int(11) NOT NULL COMMENT '产品ID',
  `product_name` varchar(100) NOT NULL COMMENT '产品名称',
  `type` varchar(20) NOT NULL COMMENT '套餐类型(package/account/quota)',
  `platforms` text COMMENT '支持的平台(JSON)',
  `daily_limit` int(11) DEFAULT NULL COMMENT '每日限制',
  `total_quota` int(11) DEFAULT NULL COMMENT '总配额',
  `account_limit` int(11) DEFAULT NULL COMMENT '账号限制',
  `used_quota` int(11) DEFAULT '0' COMMENT '已使用配额',
  `start_time` datetime NOT NULL COMMENT '开始时间',
  `expire_time` datetime NOT NULL COMMENT '过期时间',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态(0无效/1有效)',
  `time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_user_packages_user` (`user`),
  KEY `idx_user_packages_expire_time` (`expire_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户套餐表';

CREATE TABLE IF NOT EXISTS `product_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user` int(11) NOT NULL COMMENT '用户ID',
  `product` int(11) NOT NULL COMMENT '产品ID',
  `action` varchar(20) NOT NULL COMMENT '操作类型',
  `old_product` int(11) DEFAULT NULL COMMENT '旧产品ID',
  `operator` int(11) DEFAULT NULL COMMENT '操作人ID',
  `reason` varchar(500) DEFAULT NULL COMMENT '操作原因',
  `time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_product_log_user` (`user`),
  KEY `idx_product_log_product` (`product`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='产品变更历史表';

CREATE TABLE IF NOT EXISTS `api_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user` int(11) NOT NULL COMMENT '用户ID',
  `app` varchar(64) NOT NULL COMMENT '应用ID',
  `platform` varchar(20) NOT NULL COMMENT '平台',
  `product_type` varchar(20) NOT NULL COMMENT '产品类型',
  `product` int(11) DEFAULT NULL COMMENT '产品ID',
  `ip` varchar(45) DEFAULT NULL COMMENT '请求IP',
  `time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_api_logs_user` (`user`),
  KEY `idx_api_logs_app` (`app`),
  KEY `idx_api_logs_product` (`product`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='API调用日志表';

CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) unsigned NOT NULL DEFAULT 1,

  `site_name` varchar(100) DEFAULT '刀客聚合登录' COMMENT '站点名称',
  `site_url` varchar(255) DEFAULT '' COMMENT '站点地址',
  `site_description` varchar(500) DEFAULT '企业级第三方登录聚合平台' COMMENT '站点描述',
  `site_keywords` varchar(255) DEFAULT '聚合登录,OAuth,第三方登录,刀客源码网,www.dkewl.com' COMMENT '站点关键词',
  `site_icp` varchar(100) DEFAULT '' COMMENT 'ICP备案号',
  `site_logo` varchar(255) DEFAULT '/assets/logo.png' COMMENT '站点Logo',
  `site_favicon` varchar(255) DEFAULT '/assets/favicon.ico' COMMENT '站点Favicon',
  `admin_email` varchar(100) DEFAULT '' COMMENT '管理员邮箱',
  `admin_path` varchar(50) DEFAULT 'admin' COMMENT '管理后台路径',
  `service_url` varchar(255) DEFAULT '' COMMENT '服务条款URL',
  `homepage_redirect` varchar(50) DEFAULT 'none' COMMENT '首页跳转设置',

  `enable_register` tinyint(1) DEFAULT 1 COMMENT '是否开放注册',
  `register_verify_method` varchar(20) DEFAULT 'none' COMMENT '注册验证方式(none/email/phone)',

  `api_version` varchar(10) DEFAULT 'v1' COMMENT 'API版本',
  `default_daily_limit` int(11) DEFAULT 1000 COMMENT '默认每日调用限制',

  `billing_free_enabled` tinyint(1) DEFAULT 1 COMMENT '是否允许免费用户调用API',
  `billing_free_daily_limit` int(11) DEFAULT 100 COMMENT '免费用户每日调用限制',
  `billing_free_platforms` text COMMENT '免费用户可用的登录平台(JSON)',
  `billing_rate_limit_default` int(11) DEFAULT 10 COMMENT '默认每秒调用限制',
  `billing_rate_limit_package` int(11) DEFAULT 50 COMMENT '套餐用户每秒调用限制',
  `billing_rate_limit_quota` int(11) DEFAULT 30 COMMENT '次数包用户每秒调用限制',

  `pay_epay_enabled` tinyint(1) DEFAULT 0 COMMENT '易支付开关',
  `pay_epay_api_url` varchar(255) DEFAULT '' COMMENT '易支付API地址',
  `pay_epay_pid` varchar(50) DEFAULT '' COMMENT '易支付商户ID',
  `pay_epay_key` varchar(100) DEFAULT '' COMMENT '易支付密钥',

  `pay_alipay_enabled` tinyint(1) DEFAULT 0 COMMENT '支付宝开关',
  `pay_alipay_channel` varchar(20) DEFAULT 'epay' COMMENT '支付渠道(official/epay)',
  `pay_alipay_app_id` varchar(50) DEFAULT '' COMMENT '支付宝AppID',
  `pay_alipay_private_key` text COMMENT '应用私钥',
  `pay_alipay_public_key` text COMMENT '支付宝公钥',

  `pay_wechat_enabled` tinyint(1) DEFAULT 0 COMMENT '微信支付开关',
  `pay_wechat_channel` varchar(20) DEFAULT 'epay' COMMENT '支付渠道(official/epay)',
  `pay_wechat_app_id` varchar(50) DEFAULT '' COMMENT '微信AppID',
  `pay_wechat_mch_id` varchar(50) DEFAULT '' COMMENT '商户号',
  `pay_wechat_api_key` varchar(100) DEFAULT '' COMMENT 'API密钥',

  `pay_qqpay_enabled` tinyint(1) DEFAULT 0 COMMENT 'QQ钱包开关',
  `pay_qqpay_channel` varchar(20) DEFAULT 'epay' COMMENT '支付渠道(official/epay)',
  `pay_qqpay_mch_id` varchar(50) DEFAULT '' COMMENT '商户号',
  `pay_qqpay_api_key` varchar(100) DEFAULT '' COMMENT 'API密钥',

  `sms_provider` varchar(20) DEFAULT '' COMMENT '短信服务商(aliyun/tencent)',

  `sms_aliyun_access_key_id` varchar(100) DEFAULT '' COMMENT '阿里云AccessKeyID',
  `sms_aliyun_access_key_secret` varchar(100) DEFAULT '' COMMENT '阿里云AccessKeySecret',
  `sms_aliyun_sign_name` varchar(50) DEFAULT '' COMMENT '短信签名',
  `sms_aliyun_template_code` varchar(50) DEFAULT '' COMMENT '模板代码',
  `sms_aliyun_template_content` varchar(255) DEFAULT '' COMMENT '模板内容',

  `sms_tencent_secret_id` varchar(100) DEFAULT '' COMMENT '腾讯云SecretID',
  `sms_tencent_secret_key` varchar(100) DEFAULT '' COMMENT '腾讯云SecretKey',
  `sms_tencent_sdk_app_id` varchar(50) DEFAULT '' COMMENT 'SDK AppID',
  `sms_tencent_sign_name` varchar(50) DEFAULT '' COMMENT '短信签名',
  `sms_tencent_template_id` varchar(50) DEFAULT '' COMMENT '模板ID',
  `sms_tencent_template_content` varchar(255) DEFAULT '' COMMENT '模板内容',

  `smtp_host` varchar(100) DEFAULT '' COMMENT 'SMTP服务器',
  `smtp_port` int(11) DEFAULT 465 COMMENT 'SMTP端口',
  `smtp_encryption` varchar(10) DEFAULT 'ssl' COMMENT '加密方式(ssl/tls)',
  `smtp_username` varchar(100) DEFAULT '' COMMENT 'SMTP用户名',
  `smtp_password` varchar(100) DEFAULT '' COMMENT 'SMTP密码',
  `smtp_from_name` varchar(50) DEFAULT 'MAXLOGIN' COMMENT '发件人名称',

  `security_ip_whitelist` text COMMENT 'IP白名单',
  `security_ip_blacklist` text COMMENT 'IP黑名单',
  `security_email_mode` varchar(20) DEFAULT 'blacklist' COMMENT '邮箱过滤模式(whitelist/blacklist)',
  `security_email_list` text COMMENT '邮箱过滤列表',
  `security_region_enabled` tinyint(1) DEFAULT 0 COMMENT '是否启用地区限制',
  `security_region_mode` varchar(20) DEFAULT 'whitelist' COMMENT '地区过滤模式(whitelist/blacklist)',
  `security_region_list` text COMMENT '地区列表',

  `time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `last_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',

  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统设置表';

INSERT INTO `settings` (`id`) VALUES (1);

CREATE TABLE IF NOT EXISTS `verification_config` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否启用认证',
  `provider` varchar(50) NOT NULL DEFAULT 'manual' COMMENT '认证提供商(manual/slsj/shuxun/chuanglan)',
  `personal_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否启用个人认证',
  `enterprise_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否启用企业认证',
  `require` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否强制认证',
  `carrier` tinyint(1) DEFAULT '0' COMMENT '是否启用运营商认证',
  `member_id` varchar(100) DEFAULT '' COMMENT '运营商会员ID(旧字段,兼容)',
  `app_key` varchar(500) DEFAULT '' COMMENT '运营商AppKey(旧字段,兼容)',
  `app_secret` varchar(500) DEFAULT '' COMMENT '运营商AppSecret(旧字段,兼容)',
  `api_url` varchar(500) DEFAULT 'https://api.253.com' COMMENT '运营商API地址(旧字段,兼容)',
  `slsj_member_id` varchar(100) DEFAULT '' COMMENT '随联数聚-用户编码',
  `slsj_app_key` varchar(500) DEFAULT '' COMMENT '随联数聚-AppKey(加密)',
  `slsj_api_url` varchar(500) DEFAULT 'https://api.slsj.com' COMMENT '随联数聚-API地址',
  `shuxun_app_key` varchar(500) DEFAULT '' COMMENT '数勋科技-AppKey(加密)',
  `shuxun_app_secret` varchar(500) DEFAULT '' COMMENT '数勋科技-AppSecret(加密)',
  `shuxun_api_url` varchar(500) DEFAULT 'https://api.shuxuntech.com' COMMENT '数勋科技-API地址',
  `chuanglan_app_id` varchar(100) DEFAULT '' COMMENT '创蓝云智-AppId',
  `chuanglan_app_key` varchar(500) DEFAULT '' COMMENT '创蓝云智-AppKey(加密)',
  `chuanglan_api_url` varchar(500) DEFAULT 'https://api.253.com' COMMENT '创蓝云智-API地址',
  `fee` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否启用认证收费',
  `fee_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '认证费用金额',
  `reward` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否启用认证奖励',
  `reward_product_id` int(11) DEFAULT NULL COMMENT '奖励产品ID',
  `reward_duration` int(11) DEFAULT NULL COMMENT '奖励有效期(天)',
  `time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `last_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='身份认证配置表';

CREATE TABLE IF NOT EXISTS `user_verifications` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user` int(11) unsigned NOT NULL COMMENT '用户ID',
  `type` varchar(20) NOT NULL COMMENT '认证类型(personal/enterprise)',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态(0待审核/1通过/2拒绝/3待支付)',
  `name` varchar(50) DEFAULT NULL COMMENT '真实姓名',
  `id_card` text COMMENT '身份证号(加密)',
  `id_card_front` varchar(500) DEFAULT NULL COMMENT '身份证正面图片',
  `id_card_back` varchar(500) DEFAULT NULL COMMENT '身份证背面图片',
  `company` varchar(200) DEFAULT NULL COMMENT '企业名称',
  `unified_social_credit_code` varchar(50) DEFAULT NULL COMMENT '统一社会信用代码',
  `license` varchar(500) DEFAULT NULL COMMENT '营业执照图片',
  `legal_person_name` varchar(50) DEFAULT NULL COMMENT '法人姓名',
  `legal_person_id_card` text COMMENT '法人身份证号(加密)',
  `verify_provider` varchar(50) DEFAULT NULL COMMENT '认证提供商',
  `verify_request_id` varchar(100) DEFAULT NULL COMMENT '认证请求ID',
  `verify_result` text COMMENT '认证结果(JSON)',
  `verify_mobile` varchar(20) DEFAULT NULL COMMENT '认证手机号',
  `carrier` varchar(10) DEFAULT NULL COMMENT '运营商类型',
  `reason` varchar(500) DEFAULT NULL COMMENT '拒绝原因',
  `verified_time` datetime DEFAULT NULL COMMENT '认证通过时间',
  `fee` decimal(10,2) DEFAULT NULL COMMENT '收取的认证费用',
  `reward` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否已发放奖励',
  `reward_package_id` int(11) DEFAULT NULL COMMENT '奖励套餐ID',
  `time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `last_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_user_verifications_user` (`user`),
  KEY `idx_user_verifications_type` (`type`),
  KEY `idx_user_verifications_status` (`status`),
  KEY `idx_user_verifications_fee` (`fee`),
  KEY `idx_user_verifications_reward` (`reward`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户认证记录表';

SET FOREIGN_KEY_CHECKS = 1;

INSERT INTO `platforms` (`name`, `platform`, `app_id`, `app_secret`, `auth_url`, `token_url`, `user_info_url`, `scope`, `icon`, `status`, `sort`) VALUES
('qq', 'QQ登录', '', '', 'https://graph.qq.com/oauth2.0/authorize', 'https://graph.qq.com/oauth2.0/token', 'https://graph.qq.com/user/get_user_info', 'get_user_info', '/assets/icon/qq.svg', 0, 1),
('wx', '微信登录', '', '', 'https://open.weixin.qq.com/connect/qrconnect', 'https://api.weixin.qq.com/sns/oauth2/access_token', 'https://api.weixin.qq.com/sns/userinfo', 'snsapi_login', '/assets/icon/wx.svg', 0, 2),
('alipay', '支付宝登录', '', '', 'https://openauth.alipay.com/oauth2/publicAppAuthorize.htm', 'https://openapi.alipay.com/gateway.do', 'https://openapi.alipay.com/gateway.do', 'auth_user', '/assets/icon/alipay.svg', 0, 3),
('sina', '微博登录', '', '', 'https://api.weibo.com/oauth2/authorize', 'https://api.weibo.com/oauth2/access_token', 'https://api.weibo.com/2/users/show.json', '', '/assets/icon/sina.svg', 0, 4),
('baidu', '百度登录', '', '', 'https://openapi.baidu.com/oauth/2.0/authorize', 'https://openapi.baidu.com/oauth/2.0/token', 'https://openapi.baidu.com/rest/2.0/passport/users/getInfo', 'basic', '/assets/icon/baidu.svg', 0, 5),
('douyin', '抖音登录', '', '', 'https://open.douyin.com/platform/oauth/connect', 'https://open.douyin.com/oauth/access_token', 'https://open.douyin.com/oauth/userinfo', 'user_info', '/assets/icon/douyin.svg', 0, 6),
('huawei', '华为登录', '', '', 'https://oauth-login.cloud.huawei.com/oauth2/v3/authorize', 'https://oauth-login.cloud.huawei.com/oauth2/v3/token', 'https://account.cloud.huawei.com/rest.php?nsp_svc=GOpen.User.getInfo', 'openid profile', '/assets/icon/huawei.svg', 0, 7),
('google', '谷歌登录', '', '', 'https://accounts.google.com/o/oauth2/v2/auth', 'https://oauth2.googleapis.com/token', 'https://www.googleapis.com/oauth2/v3/userinfo', 'openid profile email', '/assets/icon/google.svg', 0, 8),
('microsoft', '微软登录', '', '', 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize', 'https://login.microsoftonline.com/common/oauth2/v2.0/token', 'https://graph.microsoft.com/v1.0/me', 'openid profile email', '/assets/icon/microsoft.svg', 0, 9),
('wework', '企业微信登录', '', '', 'https://open.work.weixin.qq.com/wwopen/sso/qrConnect', 'https://qyapi.weixin.qq.com/cgi-bin/gettoken', 'https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo', '', '/assets/icon/wework.svg', 0, 10),
('dingtalk', '钉钉登录', '', '', 'https://login.dingtalk.com/oauth2/auth', 'https://api.dingtalk.com/v1.0/oauth2/userAccessToken', 'https://api.dingtalk.com/v1.0/contact/users/me', 'openid', '/assets/icon/dingtalk.svg', 0, 11),
('feishu', '飞书登录', '', '', 'https://open.feishu.cn/open-apis/authen/v1/authorize', 'https://open.feishu.cn/open-apis/authen/v1/oidc/access_token', 'https://open.feishu.cn/open-apis/authen/v1/user_info', '', '/assets/icon/feishu.svg', 0, 12),
('gitee', 'Gitee登录', '', '', 'https://gitee.com/oauth/authorize', 'https://gitee.com/oauth/token', 'https://gitee.com/api/v5/user', 'user_info', '/assets/icon/gitee.svg', 0, 13),
('github', 'GitHub登录', '', '', 'https://github.com/login/oauth/authorize', 'https://github.com/login/oauth/access_token', 'https://api.github.com/user', 'read:user', '/assets/icon/github.svg', 0, 14),
('xiaomi', '小米登录', '', '', 'https://account.xiaomi.com/oauth2/authorize', 'https://account.xiaomi.com/oauth2/token', 'https://open.account.xiaomi.com/user/profile', '', '/assets/icon/xiaomi.svg', 0, 15),
('bilibili', '哔哩哔哩登录', '', '', 'https://passport.bilibili.com/register/pc_oauth2.html', 'https://passport.bilibili.com/api/oauth2/accessToken', 'https://api.bilibili.com/x/account/myinfo', '', '/assets/icon/bilibili.svg', 0, 16)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);
