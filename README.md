# 聚合登录系统

一个基于 PHP 的第三方聚合登录系统，提供 QQ、微信、支付宝、微博、GitHub、Gitee、Google、Microsoft、钉钉、飞书、抖音、企业微信等登录方式的统一接入，并兼容 `connect.php` / `return.php` 形式的聚合登录接口。

## 功能特性

- 多平台 OAuth 登录聚合
- 用户中心、应用管理、调用统计
- 管理后台平台凭证配置
- 套餐、配额、计费限制
- 支付配置与订单管理
- 用户实名认证资料提交与审核
- 微信公众号订阅号验证码登录
- 兼容彩虹聚合登录接口风格

## 环境要求

- PHP 7.4+
- MySQL 5.6+
- Nginx 或 Apache
- PHP 扩展：PDO MySQL、cURL、OpenSSL、Fileinfo、mbstring

建议把站点运行目录指向 `public`，不要把项目根目录直接暴露到 Web。

## 安装

1. 上传代码到服务器。
2. 将 Web 根目录设置为项目的 `public` 目录。
3. 确保以下目录可写：

```text
config
storage
public/storage
runtime
```

4. 访问：

```text
https://你的域名/install
```

5. 按页面提示填写数据库、站点信息和管理员账号。
6. 安装完成后进入后台配置第三方平台凭证。

注意：当前安装器会在安装完成后删除 `database` 目录，这是安装包逻辑。如果你在源码开发目录中测试安装，请先备份 `database/database.sql`。

## Nginx 伪静态

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass 127.0.0.1:9000;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
}
```

## Apache

项目已提供 `public/.htaccess`。Apache 需要启用 `mod_rewrite`。

## 聚合登录接口

### 发起登录

```http
GET /connect.php?act=login&appid=应用ID&appkey=应用密钥&type=wx&redirect_uri=https%3A%2F%2Fexample.com%2Fcallback&state=STATE
```

成功返回：

```json
{
  "code": 0,
  "msg": "success",
  "type": "wx",
  "url": "授权地址",
  "qrcode": "二维码地址"
}
```

### 获取用户信息

```http
GET /connect.php?act=callback&appid=应用ID&appkey=应用密钥&code=内部授权码
```

授权未完成时返回：

```json
{
  "code": 2,
  "msg": "等待授权中"
}
```

授权完成后返回：

```json
{
  "code": 0,
  "msg": "success",
  "type": "wx",
  "social_uid": "openid",
  "access_token": "",
  "nickname": "昵称",
  "faceimg": "头像",
  "gender": 0,
  "location": "",
  "ip": "用户IP"
}
```

## 微信订阅号登录

后台进入“平台配置”，在“微信登录”中选择：

```text
订阅号验证码登录
```

然后填写公众号的：

- AppID
- AppSecret
- 公众号服务器 Token

公众号后台服务器地址填写：

```text
https://你的域名/wechat/mp/callback
```

订阅号模式下，`type=wx` 会优先尝试生成微信公众号临时参数二维码。用户扫码后，公众号回调会自动确认登录。

如果公众号权限不支持临时二维码接口，系统会退回到验证码方案：用户关注公众号后发送：

```text
登录 验证码
```

网站端继续通过原来的 `act=callback` 轮询登录结果。

## 后台入口

安装时可以自定义后台路径。默认后台地址：

```text
/admin/login
```

建议安装后修改默认后台路径，并开启 HTTPS。

## 开源/生产安全检查

开源或正式部署前建议处理以下事项：

- 不要提交真实的 `config/config.php`、`config/install.lock`、数据库备份、日志文件、支付密钥、OAuth 密钥。
- `public/pay/epay/callback_test.php`、`public/pay/epay/test_notify.php` 是测试脚本，生产环境建议删除或限制访问。
- `app/Services/UpdateService.php` 包含远程更新、写文件、删文件、执行 SQL、请求 URL 的逻辑，当前还存在未定义的 `$validator` 依赖。开源版建议删除自动更新功能或默认禁用相关后台入口。
- `InstallController` 当前跳过商业授权校验，适合开源版；如果保留授权逻辑，请清理旧的授权服务器地址和内部 AppID。
- `core/helpers.php` 中存在 `license.license_key` 的内部回退逻辑，开源版建议删除，避免不透明授权逻辑。
- OAuth HTTP 请求中部分代码关闭了 SSL 证书校验，生产环境建议改为开启证书校验。
- CSRF Token 当前是普通字符串比较，建议改为 `hash_equals`。
- 安装完成后确认 `/install` 无法再次访问，并确保 `config/install.lock` 存在。
- 不要将项目根目录作为 Web 根目录，只暴露 `public`。
- 建议在 Web 服务器层禁止访问 `.git`、`config`、`storage`、`database`、`app`、`core`、`routes`、`views`。

## 目录结构

```text
app/        控制器、中间件、服务类
config/     配置文件
core/       框架核心
database/   数据库初始化 SQL
public/     Web 入口和静态资源
routes/     路由定义
storage/    缓存和上传目录
views/      页面模板
```

## 许可证

请根据你的开源计划补充许可证文件，例如 MIT、Apache-2.0 或 GPL-3.0。
