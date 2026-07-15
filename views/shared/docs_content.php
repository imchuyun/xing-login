<?php
/**
 * 共享的接入文档内容
 * 用于首页文档、用户后台文档、管理员后台文档
 */
$baseUrl = config('site.url') ?: ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']);
?>

<style>
.doc-nav-item:hover { background-color: var(--bg-surface-hover); }
.doc-nav-item.active { background-color: rgba(99, 102, 241, 0.1); color: var(--color-primary); font-weight: 500; }
.doc-table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
.doc-table th { padding: 0.75rem 1rem; text-align: left; border-bottom: 1px solid var(--border-color); font-weight: 600; background-color: var(--bg-surface-hover); white-space: nowrap; }
.doc-table td { padding: 0.75rem 1rem; border-bottom: 1px solid var(--border-color); }
.doc-table code { background-color: var(--bg-surface); padding: 0.125rem 0.375rem; border-radius: var(--radius-sm); font-size: 0.8125rem; }
.platform-item:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.08); background-color: var(--bg-main) !important; }

.doc-layout { display: grid; grid-template-columns: 220px 1fr; gap: 1.5rem; }
.doc-sidebar { position: sticky; top: <?= isset($isPublic) && $isPublic ? '5rem' : '1rem' ?>; align-self: start; }
.doc-content { display: flex; flex-direction: column; gap: 1.5rem; min-width: 0; }

.doc-mobile-nav-btn { display: none; }

.table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }

.doc-url-box { 
    word-break: break-all; 
    overflow-wrap: break-word;
}
.doc-url-box code {
    word-break: break-all;
    overflow-wrap: break-word;
    display: inline-block;
    max-width: 100%;
}
.doc-code-block {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}
.doc-code-block code {
    word-break: break-all;
    white-space: pre-wrap;
}

.doc-example-url {
    background-color: #1e1e1e;
    border-radius: var(--radius-md);
    padding: 0.875rem 1rem;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}
.doc-example-url code {
    color: #d4d4d4;
    font-size: 0.8125rem;
    word-break: break-all;
    overflow-wrap: break-word;
    white-space: pre-wrap;
    display: block;
}

.platform-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.75rem; }

.mobile-break { display: none; }

@media (max-width: 1024px) {
    .doc-layout { grid-template-columns: 200px 1fr; gap: 1rem; }
    .platform-grid { grid-template-columns: repeat(3, 1fr); }
}

@media (max-width: 768px) {
    .doc-layout { grid-template-columns: 1fr; }
    .doc-sidebar { 
        position: fixed; 
        top: 0; 
        left: -280px; 
        width: 280px; 
        height: 100vh; 
        z-index: 1000; 
        background: var(--bg-main); 
        transition: left 0.3s ease;
        padding: 1rem;
        overflow-y: auto;
    }
    .doc-sidebar.active { left: 0; box-shadow: 0 0 20px rgba(0,0,0,0.2); }
    .doc-sidebar .card { box-shadow: none; border: none; }
    .doc-mobile-nav-btn { 
        display: flex; 
        align-items: center; 
        gap: 0.5rem; 
        padding: 0.75rem 1rem; 
        background: var(--bg-surface); 
        border: 1px solid var(--border-color); 
        border-radius: var(--radius-md); 
        margin-bottom: 1rem;
        cursor: pointer;
        font-size: 0.875rem;
        color: var(--text-main);
    }
    .doc-mobile-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        z-index: 999;
    }
    .doc-mobile-overlay.active { display: block; }
    .platform-grid { grid-template-columns: repeat(2, 1fr); }
    .doc-table th, .doc-table td { padding: 0.5rem 0.75rem; font-size: 0.8125rem; }
    
    
    .mobile-break { display: block; }
    .doc-url-box code { 
        margin-top: 0.5rem; 
        font-size: 0.75rem;
    }
    
    .doc-info-grid { grid-template-columns: 1fr !important; }
}

@media (max-width: 480px) {
    .platform-grid { grid-template-columns: 1fr; }
    
    
    .doc-example-url code { font-size: 0.6875rem; }
    pre code { font-size: 0.75rem !important; }
}
</style>

<!-- 移动端导航遮罩 -->
<div class="doc-mobile-overlay" id="docOverlay" onclick="toggleDocNav()"></div>

<!-- 移动端导航按钮 -->
<button class="doc-mobile-nav-btn" onclick="toggleDocNav()">
    <span class="iconify" data-icon="tabler:menu-2" style="font-size: 1.25rem;"></span>
    <span>文档导航</span>
</button>

<div class="doc-layout">
    <!-- 左侧导航 -->
    <div class="doc-sidebar" id="docSidebar">
        <div class="card">
            <div class="card-body" style="padding: 1rem;">
                <h3 style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 1rem;">接入文档</h3>
                <nav style="display: flex; flex-direction: column; gap: 0.25rem;" id="docNav">
                    <a href="javascript:;" data-target="overview" class="doc-nav-item active" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0.75rem; border-radius: var(--radius-md); color: var(--text-main); text-decoration: none; cursor: pointer;" onclick="handleDocNavClick('overview')">
                        <span class="iconify" data-icon="tabler:home" style="color: var(--color-primary); font-size: 1.125rem;"></span>
                        <span>概述</span>
                    </a>
                    <a href="javascript:;" data-target="quickstart" class="doc-nav-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0.75rem; border-radius: var(--radius-md); color: var(--text-main); text-decoration: none; cursor: pointer;" onclick="handleDocNavClick('quickstart')">
                        <span class="iconify" data-icon="tabler:rocket" style="color: #10b981; font-size: 1.125rem;"></span>
                        <span>快速开始</span>
                    </a>
                    <a href="javascript:;" data-target="api-login" class="doc-nav-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0.75rem; border-radius: var(--radius-md); color: var(--text-main); text-decoration: none; cursor: pointer;" onclick="handleDocNavClick('api-login')">
                        <span class="iconify" data-icon="tabler:login" style="color: #3b82f6; font-size: 1.125rem;"></span>
                        <span>登录接口</span>
                    </a>
                    <a href="javascript:;" data-target="api-callback" class="doc-nav-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0.75rem; border-radius: var(--radius-md); color: var(--text-main); text-decoration: none; cursor: pointer;" onclick="handleDocNavClick('api-callback')">
                        <span class="iconify" data-icon="tabler:user-check" style="color: #8b5cf6; font-size: 1.125rem;"></span>
                        <span>回调接口</span>
                    </a>
                    <a href="javascript:;" data-target="api-query" class="doc-nav-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0.75rem; border-radius: var(--radius-md); color: var(--text-main); text-decoration: none; cursor: pointer;" onclick="handleDocNavClick('api-query')">
                        <span class="iconify" data-icon="tabler:search" style="color: #f59e0b; font-size: 1.125rem;"></span>
                        <span>查询接口</span>
                    </a>
                    <a href="javascript:;" data-target="platforms" class="doc-nav-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0.75rem; border-radius: var(--radius-md); color: var(--text-main); text-decoration: none; cursor: pointer;" onclick="handleDocNavClick('platforms')">
                        <span class="iconify" data-icon="tabler:apps" style="color: #ec4899; font-size: 1.125rem;"></span>
                        <span>支持平台</span>
                    </a>
                    <a href="javascript:;" data-target="errors" class="doc-nav-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0.75rem; border-radius: var(--radius-md); color: var(--text-main); text-decoration: none; cursor: pointer;" onclick="handleDocNavClick('errors')">
                        <span class="iconify" data-icon="tabler:alert-triangle" style="color: #ef4444; font-size: 1.125rem;"></span>
                        <span>错误码</span>
                    </a>
                    <a href="javascript:;" data-target="sdk" class="doc-nav-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0.75rem; border-radius: var(--radius-md); color: var(--text-main); text-decoration: none; cursor: pointer;" onclick="handleDocNavClick('sdk')">
                        <span class="iconify" data-icon="tabler:code" style="color: #06b6d4; font-size: 1.125rem;"></span>
                        <span>SDK下载</span>
                    </a>
                </nav>
            </div>
        </div>
    </div>

    <!-- 右侧内容 -->
    <div class="doc-content">

        <!-- 概述 -->
        <div id="overview" class="card">
            <div class="card-body">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                    <div style="width: 2.75rem; height: 2.75rem; background: linear-gradient(135deg, #6366f1, #8b5cf6); border-radius: 0.625rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <span class="iconify" data-icon="tabler:home" style="color: white; font-size: 1.5rem;"></span>
                    </div>
                    <div style="min-width: 0;">
                        <h2 style="font-size: 1.125rem; font-weight: 700; color: var(--text-main); margin: 0; line-height: 1.3;">接口概述</h2>
                        <p style="font-size: 0.8125rem; color: var(--text-secondary); margin: 0; line-height: 1.3;">聚合登录API接入说明</p>
                    </div>
                </div>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div class="alert alert-info doc-url-box" style="margin: 0;">
                        <span style="font-weight: 600;">接口地址：</span><br class="mobile-break">
                        <code style="background-color: var(--bg-surface); padding: 0.25rem 0.5rem; border-radius: var(--radius-sm); cursor: pointer;" onclick="copyText('<?= $baseUrl ?>/connect.php')"><?= $baseUrl ?>/connect.php</code>
                    </div>
                    <div class="doc-info-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div style="background-color: var(--bg-surface-hover); padding: 0.875rem 1rem; border-radius: var(--radius-md);">
                            <span style="font-weight: 600;">请求方式：</span><span style="color: var(--text-secondary); margin-left: 0.5rem;">GET / POST</span>
                        </div>
                        <div style="background-color: var(--bg-surface-hover); padding: 0.875rem 1rem; border-radius: var(--radius-md);">
                            <span style="font-weight: 600;">返回格式：</span><span style="color: var(--text-secondary); margin-left: 0.5rem;">JSON</span>
                        </div>
                    </div>
                    <div class="alert alert-warning" style="margin: 0;">
                        <div style="font-weight: 600; margin-bottom: 0.5rem;">接入流程</div>
                        <ol style="list-style-type: decimal; list-style-position: inside; margin: 0; padding-left: 0.25rem; font-size: 0.875rem; line-height: 1.75;">
                            <li><?php if (isset($isPublic) && $isPublic): ?><a href="/user/reg" style="color: var(--color-primary);">注册账号</a>并创建应用<?php else: ?>在<a href="/user/apps" style="color: var(--color-primary);">我的应用</a>创建应用<?php endif; ?>，获取 <code>appid</code> 和 <code>appkey</code></li>
                            <li>调用登录接口获取授权URL，跳转用户授权</li>
                            <li>用户授权后回调到您的网站，携带 <code>code</code> 参数</li>
                            <li>调用回调接口，使用 <code>code</code> 换取用户信息</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- 快速开始 -->
        <div id="quickstart" class="card">
            <div class="card-body">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                    <div style="width: 2.75rem; height: 2.75rem; background: linear-gradient(135deg, #10b981, #059669); border-radius: 0.625rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <span class="iconify" data-icon="tabler:rocket" style="color: white; font-size: 1.5rem;"></span>
                    </div>
                    <div style="min-width: 0;">
                        <h2 style="font-size: 1.125rem; font-weight: 700; color: var(--text-main); margin: 0; line-height: 1.3;">快速开始</h2>
                        <p style="font-size: 0.8125rem; color: var(--text-secondary); margin: 0; line-height: 1.3;">5分钟完成接入</p>
                    </div>
                </div>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <p style="font-size: 0.875rem; font-weight: 600; color: var(--text-main); margin: 0;">PHP 示例代码</p>
                    <div style="background-color: #1e1e1e; border-radius: var(--radius-md); overflow: hidden;">
                        <div style="background-color: #2d2d2d; padding: 0.5rem 1rem; display: flex; align-items: center; gap: 0.5rem;">
                            <span class="iconify" data-icon="tabler:file-code" style="color: #9ca3af;"></span>
                            <span style="color: #9ca3af; font-size: 0.75rem;">login.php - 发起登录</span>
                        </div>
                        <pre style="margin: 0; padding: 1rem; overflow-x: auto; font-size: 0.8125rem; line-height: 1.6;"><code style="color: #d4d4d4;"><?= htmlspecialchars('<?php
$apiurl = \'' . $baseUrl . '/connect.php\';
$appid  = \'您的APPID\';
$appkey = \'您的APPKEY\';
$type   = \'qq\';
$redirect_uri = \'https://您的网站/callback.php\';

$params = [\'act\' => \'login\', \'appid\' => $appid, \'appkey\' => $appkey, \'type\' => $type, \'redirect_uri\' => $redirect_uri];
$result = json_decode(file_get_contents($apiurl . \'?\' . http_build_query($params)), true);

if ($result[\'code\'] == 0) {
    header(\'Location: \' . $result[\'url\']);
    exit;
}') ?></code></pre>
                    </div>
                    <div style="background-color: #1e1e1e; border-radius: var(--radius-md); overflow: hidden;">
                        <div style="background-color: #2d2d2d; padding: 0.5rem 1rem; display: flex; align-items: center; gap: 0.5rem;">
                            <span class="iconify" data-icon="tabler:file-code" style="color: #9ca3af;"></span>
                            <span style="color: #9ca3af; font-size: 0.75rem;">callback.php - 处理回调</span>
                        </div>
                        <pre style="margin: 0; padding: 1rem; overflow-x: auto; font-size: 0.8125rem; line-height: 1.6;"><code style="color: #d4d4d4;"><?= htmlspecialchars('<?php
$code = $_GET[\'code\'] ?? \'\';
$params = [\'act\' => \'callback\', \'appid\' => $appid, \'appkey\' => $appkey, \'code\' => $code];
$result = json_decode(file_get_contents($apiurl . \'?\' . http_build_query($params)), true);

if ($result[\'code\'] == 0) {
    $social_uid = $result[\'social_uid\']; // 用户唯一标识
    $nickname   = $result[\'nickname\'];   // 昵称
    $faceimg    = $result[\'faceimg\'];    // 头像
    $gender     = $result[\'gender\'];     // 性别
}') ?></code></pre>
                    </div>
                </div>
            </div>
        </div>

        <!-- 登录接口 -->
        <div id="api-login" class="card">
            <div class="card-body">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                    <div style="width: 2.75rem; height: 2.75rem; background: linear-gradient(135deg, #3b82f6, #2563eb); border-radius: 0.625rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <span class="iconify" data-icon="tabler:login" style="color: white; font-size: 1.5rem;"></span>
                    </div>
                    <div style="min-width: 0;">
                        <h2 style="font-size: 1.125rem; font-weight: 700; color: var(--text-main); margin: 0; line-height: 1.3;">登录接口</h2>
                        <p style="font-size: 0.8125rem; color: var(--text-secondary); margin: 0; line-height: 1.3;">act=login</p>
                    </div>
                </div>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <h4 style="font-weight: 600; font-size: 0.9375rem; margin: 0;">请求参数</h4>
                    <div class="table-responsive">
                    <table class="doc-table">
                        <thead><tr><th>参数名</th><th>必填</th><th>类型</th><th>说明</th></tr></thead>
                        <tbody>
                            <tr><td><code>act</code></td><td>是</td><td>string</td><td>接口动作，固定值 <code>login</code></td></tr>
                            <tr><td><code>appid</code></td><td>是</td><td>string</td><td>应用ID，在"我的应用"中获取，如：<code>f99abb60a4f4439d</code></td></tr>
                            <tr><td><code>appkey</code></td><td>是</td><td>string</td><td>应用密钥，在"我的应用"中获取，请妥善保管不要泄露</td></tr>
                            <tr><td><code>type</code></td><td>是</td><td>string</td><td>登录平台类型，可选值：<code>qq</code>、<code>wx</code>、<code>alipay</code>、<code>sina</code>、<code>baidu</code>、<code>github</code>、<code>gitee</code>、<code>google</code>、<code>dingtalk</code>、<code>feishu</code> 等</td></tr>
                            <tr><td><code>redirect_uri</code></td><td>是</td><td>string</td><td>授权成功后的回调地址，必须与应用配置的授权域名匹配，需进行URL编码。示例：<code>https://example.com/callback.php</code></td></tr>
                            <tr><td><code>state</code></td><td>否</td><td>string</td><td>自定义状态参数，用于防止CSRF攻击或传递业务数据，回调时会原样返回。建议传入随机字符串并在回调时验证</td></tr>
                        </tbody>
                    </table>
                    </div>
                    <h4 style="font-weight: 600; font-size: 0.9375rem; margin: 0.5rem 0 0;">返回参数</h4>
                    <div class="table-responsive">
                    <table class="doc-table">
                        <thead><tr><th>参数名</th><th>类型</th><th>说明</th></tr></thead>
                        <tbody>
                            <tr><td><code>code</code></td><td>int</td><td>状态码，<code>0</code>=成功，其他值表示失败</td></tr>
                            <tr><td><code>msg</code></td><td>string</td><td>返回信息，成功时为 <code>success</code>，失败时为错误描述</td></tr>
                            <tr><td><code>type</code></td><td>string</td><td>登录类型，与请求参数一致</td></tr>
                            <tr><td><code>url</code></td><td>string</td><td>第三方授权页面地址，需要将用户重定向（302跳转）到此地址进行授权</td></tr>
                        </tbody>
                    </table>
                    </div>
                    <h4 style="font-weight: 600; font-size: 0.9375rem; margin: 0.5rem 0 0;">请求示例</h4>
                    <div class="doc-example-url">
                        <code><?= $baseUrl ?>/connect.php?act=login&appid=您的APPID&appkey=您的APPKEY&type=qq&redirect_uri=https://您的网站/callback.php&state=随机字符串</code>
                    </div>
                    <h4 style="font-weight: 600; font-size: 0.9375rem; margin: 0.5rem 0 0;">成功返回示例</h4>
                    <div style="background-color: #1e1e1e; border-radius: var(--radius-md); padding: 0.875rem 1rem;">
                        <pre style="margin: 0; color: #d4d4d4; font-size: 0.8125rem;">{
    "code": 0,
    "msg": "success",
    "type": "qq",
    "url": "https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id=xxx&redirect_uri=xxx&state=xxx"
}</pre>
                    </div>
                    <h4 style="font-weight: 600; font-size: 0.9375rem; margin: 0.5rem 0 0;">失败返回示例</h4>
                    <div style="background-color: #1e1e1e; border-radius: var(--radius-md); padding: 0.875rem 1rem;">
                        <pre style="margin: 0; color: #d4d4d4; font-size: 0.8125rem;">{
    "code": -1,
    "msg": "应用不存在或已禁用",
    "errcode": 102
}</pre>
                    </div>
                </div>
            </div>
        </div>

        <!-- 回调接口 -->
        <div id="api-callback" class="card">
            <div class="card-body">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                    <div style="width: 2.75rem; height: 2.75rem; background: linear-gradient(135deg, #8b5cf6, #7c3aed); border-radius: 0.625rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <span class="iconify" data-icon="tabler:user-check" style="color: white; font-size: 1.5rem;"></span>
                    </div>
                    <div style="min-width: 0;">
                        <h2 style="font-size: 1.125rem; font-weight: 700; color: var(--text-main); margin: 0; line-height: 1.3;">回调接口</h2>
                        <p style="font-size: 0.8125rem; color: var(--text-secondary); margin: 0; line-height: 1.3;">act=callback</p>
                    </div>
                </div>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div class="alert alert-info" style="margin: 0;">
                        <strong>说明：</strong>用户在第三方平台授权成功后，会携带 <code>code</code> 参数跳转到您设置的回调地址。您需要在回调页面调用此接口，用 <code>code</code> 换取用户信息。
                    </div>
                    <h4 style="font-weight: 600; font-size: 0.9375rem; margin: 0;">请求参数</h4>
                    <div class="table-responsive">
                    <table class="doc-table">
                        <thead><tr><th>参数名</th><th>必填</th><th>类型</th><th>说明</th></tr></thead>
                        <tbody>
                            <tr><td><code>act</code></td><td>是</td><td>string</td><td>接口动作，固定值 <code>callback</code></td></tr>
                            <tr><td><code>appid</code></td><td>是</td><td>string</td><td>应用ID，与登录接口使用的一致</td></tr>
                            <tr><td><code>appkey</code></td><td>是</td><td>string</td><td>应用密钥，与登录接口使用的一致</td></tr>
                            <tr><td><code>code</code></td><td>是</td><td>string</td><td>授权码，从回调URL的 <code>code</code> 参数获取。注意：每个code只能使用一次，有效期约5分钟</td></tr>
                        </tbody>
                    </table>
                    </div>
                    <h4 style="font-weight: 600; font-size: 0.9375rem; margin: 0.5rem 0 0;">返回参数</h4>
                    <div class="table-responsive">
                    <table class="doc-table">
                        <thead><tr><th>参数名</th><th>类型</th><th>说明</th></tr></thead>
                        <tbody>
                            <tr><td><code>code</code></td><td>int</td><td>状态码：<code>0</code>=成功，<code>2</code>=等待用户授权中（轮询场景），其他=失败</td></tr>
                            <tr><td><code>msg</code></td><td>string</td><td>返回信息</td></tr>
                            <tr><td><code>type</code></td><td>string</td><td>登录平台类型，如 <code>qq</code>、<code>wx</code> 等</td></tr>
                            <tr><td><code>social_uid</code></td><td>string</td><td>用户在该平台的唯一标识，可用于关联本地用户账号。同一用户在同一平台的 social_uid 始终相同</td></tr>
                            <tr><td><code>access_token</code></td><td>string</td><td>访问令牌，部分平台可用于调用其他API</td></tr>
                            <tr><td><code>nickname</code></td><td>string</td><td>用户昵称/用户名</td></tr>
                            <tr><td><code>faceimg</code></td><td>string</td><td>用户头像URL地址</td></tr>
                            <tr><td><code>gender</code></td><td>int</td><td>用户性别：<code>0</code>=未知，<code>1</code>=男，<code>2</code>=女</td></tr>
                            <tr><td><code>location</code></td><td>string</td><td>用户所在地区，如"广东 深圳"（部分平台可能为空）</td></tr>
                            <tr><td><code>ip</code></td><td>string</td><td>用户授权时的IP地址</td></tr>
                        </tbody>
                    </table>
                    </div>
                    <h4 style="font-weight: 600; font-size: 0.9375rem; margin: 0.5rem 0 0;">请求示例</h4>
                    <div class="doc-example-url">
                        <code><?= $baseUrl ?>/connect.php?act=callback&appid=您的APPID&appkey=您的APPKEY&code=从回调获取的code</code>
                    </div>
                    <h4 style="font-weight: 600; font-size: 0.9375rem; margin: 0.5rem 0 0;">成功返回示例</h4>
                    <div style="background-color: #1e1e1e; border-radius: var(--radius-md); padding: 0.875rem 1rem;">
                        <pre style="margin: 0; color: #d4d4d4; font-size: 0.8125rem;">{
    "code": 0,
    "msg": "success",
    "type": "qq",
    "social_uid": "ABCD1234567890",
    "access_token": "E8F7D6C5B4A3...",
    "nickname": "用户昵称",
    "faceimg": "https://thirdqq.qlogo.cn/g?b=oidb&k=xxx",
    "gender": 1,
    "location": "广东 深圳",
    "ip": "113.89.xxx.xxx"
}</pre>
                    </div>
                    <div class="alert alert-warning" style="margin: 0;">
                        <strong>重要提示：</strong>
                        <ul style="margin: 0.5rem 0 0; padding-left: 1.25rem;">
                            <li><code>social_uid</code> 是用户的唯一标识，建议存储到数据库用于用户关联</li>
                            <li>每个 <code>code</code> 只能使用一次，重复使用会返回错误</li>
                            <li>用户头像URL可能会过期，建议下载保存到自己的服务器</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- 查询接口 -->
        <div id="api-query" class="card">
            <div class="card-body">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                    <div style="width: 2.75rem; height: 2.75rem; background: linear-gradient(135deg, #f59e0b, #d97706); border-radius: 0.625rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <span class="iconify" data-icon="tabler:search" style="color: white; font-size: 1.5rem;"></span>
                    </div>
                    <div style="min-width: 0;">
                        <h2 style="font-size: 1.125rem; font-weight: 700; color: var(--text-main); margin: 0; line-height: 1.3;">查询接口</h2>
                        <p style="font-size: 0.8125rem; color: var(--text-secondary); margin: 0; line-height: 1.3;">act=query</p>
                    </div>
                </div>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div class="alert alert-info" style="margin: 0;">
                        <strong>说明：</strong>根据 <code>social_uid</code> 查询已授权用户的缓存信息。适用于需要再次获取用户信息但不想让用户重新授权的场景。
                    </div>
                    <h4 style="font-weight: 600; font-size: 0.9375rem; margin: 0;">请求参数</h4>
                    <div class="table-responsive">
                    <table class="doc-table">
                        <thead><tr><th>参数名</th><th>必填</th><th>类型</th><th>说明</th></tr></thead>
                        <tbody>
                            <tr><td><code>act</code></td><td>是</td><td>string</td><td>接口动作，固定值 <code>query</code></td></tr>
                            <tr><td><code>appid</code></td><td>是</td><td>string</td><td>应用ID</td></tr>
                            <tr><td><code>appkey</code></td><td>是</td><td>string</td><td>应用密钥</td></tr>
                            <tr><td><code>type</code></td><td>是</td><td>string</td><td>登录平台类型，如 <code>qq</code>、<code>wx</code> 等</td></tr>
                            <tr><td><code>social_uid</code></td><td>是</td><td>string</td><td>用户唯一标识，从回调接口获取</td></tr>
                        </tbody>
                    </table>
                    </div>
                    <h4 style="font-weight: 600; font-size: 0.9375rem; margin: 0.5rem 0 0;">返回参数</h4>
                    <div class="table-responsive">
                    <table class="doc-table">
                        <thead><tr><th>参数名</th><th>类型</th><th>说明</th></tr></thead>
                        <tbody>
                            <tr><td><code>code</code></td><td>int</td><td>状态码，<code>0</code>=成功</td></tr>
                            <tr><td><code>msg</code></td><td>string</td><td>返回信息</td></tr>
                            <tr><td><code>type</code></td><td>string</td><td>登录平台类型</td></tr>
                            <tr><td><code>social_uid</code></td><td>string</td><td>用户唯一标识</td></tr>
                            <tr><td><code>access_token</code></td><td>string</td><td>访问令牌（可能已过期）</td></tr>
                            <tr><td><code>nickname</code></td><td>string</td><td>用户昵称</td></tr>
                            <tr><td><code>faceimg</code></td><td>string</td><td>用户头像URL</td></tr>
                            <tr><td><code>gender</code></td><td>int</td><td>用户性别：<code>0</code>=未知，<code>1</code>=男，<code>2</code>=女</td></tr>
                            <tr><td><code>location</code></td><td>string</td><td>用户所在地区</td></tr>
                            <tr><td><code>ip</code></td><td>string</td><td>用户最后授权时的IP地址</td></tr>
                        </tbody>
                    </table>
                    </div>
                    <h4 style="font-weight: 600; font-size: 0.9375rem; margin: 0.5rem 0 0;">请求示例</h4>
                    <div class="doc-example-url">
                        <code><?= $baseUrl ?>/connect.php?act=query&appid=您的APPID&appkey=您的APPKEY&type=qq&social_uid=用户的social_uid</code>
                    </div>
                    <h4 style="font-weight: 600; font-size: 0.9375rem; margin: 0.5rem 0 0;">成功返回示例</h4>
                    <div style="background-color: #1e1e1e; border-radius: var(--radius-md); padding: 0.875rem 1rem;">
                        <pre style="margin: 0; color: #d4d4d4; font-size: 0.8125rem;">{
    "code": 0,
    "msg": "success",
    "type": "qq",
    "social_uid": "ABCD1234567890",
    "access_token": "E8F7D6C5B4A3...",
    "nickname": "用户昵称",
    "faceimg": "https://thirdqq.qlogo.cn/g?b=oidb&k=xxx",
    "gender": 1,
    "location": "广东 深圳",
    "ip": "113.89.xxx.xxx"
}</pre>
                    </div>
                </div>
            </div>
        </div>

        <!-- 支持平台 -->
        <div id="platforms" class="card">
            <div class="card-body">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                    <div style="width: 2.75rem; height: 2.75rem; background: linear-gradient(135deg, #ec4899, #db2777); border-radius: 0.625rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <span class="iconify" data-icon="tabler:apps" style="color: white; font-size: 1.5rem;"></span>
                    </div>
                    <div style="min-width: 0;">
                        <h2 style="font-size: 1.125rem; font-weight: 700; color: var(--text-main); margin: 0; line-height: 1.3;">支持平台</h2>
                        <p style="font-size: 0.8125rem; color: var(--text-secondary); margin: 0; line-height: 1.3;">type 参数可选值</p>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 0.75rem; padding: 1rem; background-color: var(--bg-surface); border-radius: var(--radius-lg); border: 1px solid var(--border-color);">
                    <div class="platform-item" style="display: flex; align-items: center; gap: 0.625rem; padding: 0.625rem 0.75rem; background-color: var(--bg-surface-hover); border-radius: var(--radius-md); transition: all 0.2s ease; cursor: default;">
                        <img src="/assets/icon/qq.svg" alt="QQ" style="width: 1.25rem; height: 1.25rem;">
                        <div><div style="font-weight: 600; font-size: 0.875rem;">QQ登录</div><code style="font-size: 0.75rem; color: var(--text-muted);">type=qq</code></div>
                    </div>
                    <div class="platform-item" style="display: flex; align-items: center; gap: 0.625rem; padding: 0.625rem 0.75rem; background-color: var(--bg-surface-hover); border-radius: var(--radius-md); transition: all 0.2s ease; cursor: default;">
                        <img src="/assets/icon/wx.svg" alt="微信" style="width: 1.25rem; height: 1.25rem;">
                        <div><div style="font-weight: 600; font-size: 0.875rem;">微信登录</div><code style="font-size: 0.75rem; color: var(--text-muted);">type=wx</code></div>
                    </div>
                    <div class="platform-item" style="display: flex; align-items: center; gap: 0.625rem; padding: 0.625rem 0.75rem; background-color: var(--bg-surface-hover); border-radius: var(--radius-md); transition: all 0.2s ease; cursor: default;">
                        <img src="/assets/icon/alipay.svg" alt="支付宝" style="width: 1.25rem; height: 1.25rem;">
                        <div><div style="font-weight: 600; font-size: 0.875rem;">支付宝</div><code style="font-size: 0.75rem; color: var(--text-muted);">type=alipay</code></div>
                    </div>
                    <div class="platform-item" style="display: flex; align-items: center; gap: 0.625rem; padding: 0.625rem 0.75rem; background-color: var(--bg-surface-hover); border-radius: var(--radius-md); transition: all 0.2s ease; cursor: default;">
                        <img src="/assets/icon/sina.svg" alt="微博" style="width: 1.25rem; height: 1.25rem;">
                        <div><div style="font-weight: 600; font-size: 0.875rem;">微博登录</div><code style="font-size: 0.75rem; color: var(--text-muted);">type=sina</code></div>
                    </div>
                    <div class="platform-item" style="display: flex; align-items: center; gap: 0.625rem; padding: 0.625rem 0.75rem; background-color: var(--bg-surface-hover); border-radius: var(--radius-md); transition: all 0.2s ease; cursor: default;">
                        <img src="/assets/icon/baidu.svg" alt="百度" style="width: 1.25rem; height: 1.25rem;">
                        <div><div style="font-weight: 600; font-size: 0.875rem;">百度登录</div><code style="font-size: 0.75rem; color: var(--text-muted);">type=baidu</code></div>
                    </div>
                    <div class="platform-item" style="display: flex; align-items: center; gap: 0.625rem; padding: 0.625rem 0.75rem; background-color: var(--bg-surface-hover); border-radius: var(--radius-md); transition: all 0.2s ease; cursor: default;">
                        <img src="/assets/icon/douyin.svg" alt="抖音" style="width: 1.25rem; height: 1.25rem;">
                        <div><div style="font-weight: 600; font-size: 0.875rem;">抖音登录</div><code style="font-size: 0.75rem; color: var(--text-muted);">type=douyin</code></div>
                    </div>
                    <div class="platform-item" style="display: flex; align-items: center; gap: 0.625rem; padding: 0.625rem 0.75rem; background-color: var(--bg-surface-hover); border-radius: var(--radius-md); transition: all 0.2s ease; cursor: default;">
                        <img src="/assets/icon/huawei.svg" alt="华为" style="width: 1.25rem; height: 1.25rem;">
                        <div><div style="font-weight: 600; font-size: 0.875rem;">华为登录</div><code style="font-size: 0.75rem; color: var(--text-muted);">type=huawei</code></div>
                    </div>
                    <div class="platform-item" style="display: flex; align-items: center; gap: 0.625rem; padding: 0.625rem 0.75rem; background-color: var(--bg-surface-hover); border-radius: var(--radius-md); transition: all 0.2s ease; cursor: default;">
                        <img src="/assets/icon/google.svg" alt="谷歌" style="width: 1.25rem; height: 1.25rem;">
                        <div><div style="font-weight: 600; font-size: 0.875rem;">谷歌登录</div><code style="font-size: 0.75rem; color: var(--text-muted);">type=google</code></div>
                    </div>
                    <div class="platform-item" style="display: flex; align-items: center; gap: 0.625rem; padding: 0.625rem 0.75rem; background-color: var(--bg-surface-hover); border-radius: var(--radius-md); transition: all 0.2s ease; cursor: default;">
                        <img src="/assets/icon/microsoft.svg" alt="微软" style="width: 1.25rem; height: 1.25rem;">
                        <div><div style="font-weight: 600; font-size: 0.875rem;">微软登录</div><code style="font-size: 0.75rem; color: var(--text-muted);">type=microsoft</code></div>
                    </div>
                    <div class="platform-item" style="display: flex; align-items: center; gap: 0.625rem; padding: 0.625rem 0.75rem; background-color: var(--bg-surface-hover); border-radius: var(--radius-md); transition: all 0.2s ease; cursor: default;">
                        <img src="/assets/icon/wework.svg" alt="企业微信" style="width: 1.25rem; height: 1.25rem;">
                        <div><div style="font-weight: 600; font-size: 0.875rem;">企业微信</div><code style="font-size: 0.75rem; color: var(--text-muted);">type=wework</code></div>
                    </div>
                    <div class="platform-item" style="display: flex; align-items: center; gap: 0.625rem; padding: 0.625rem 0.75rem; background-color: var(--bg-surface-hover); border-radius: var(--radius-md); transition: all 0.2s ease; cursor: default;">
                        <img src="/assets/icon/dingtalk.svg" alt="钉钉" style="width: 1.25rem; height: 1.25rem;">
                        <div><div style="font-weight: 600; font-size: 0.875rem;">钉钉登录</div><code style="font-size: 0.75rem; color: var(--text-muted);">type=dingtalk</code></div>
                    </div>
                    <div class="platform-item" style="display: flex; align-items: center; gap: 0.625rem; padding: 0.625rem 0.75rem; background-color: var(--bg-surface-hover); border-radius: var(--radius-md); transition: all 0.2s ease; cursor: default;">
                        <img src="/assets/icon/feishu.svg" alt="飞书" style="width: 1.25rem; height: 1.25rem;">
                        <div><div style="font-weight: 600; font-size: 0.875rem;">飞书登录</div><code style="font-size: 0.75rem; color: var(--text-muted);">type=feishu</code></div>
                    </div>
                    <div class="platform-item" style="display: flex; align-items: center; gap: 0.625rem; padding: 0.625rem 0.75rem; background-color: var(--bg-surface-hover); border-radius: var(--radius-md); transition: all 0.2s ease; cursor: default;">
                        <img src="/assets/icon/gitee.svg" alt="Gitee" style="width: 1.25rem; height: 1.25rem;">
                        <div><div style="font-weight: 600; font-size: 0.875rem;">Gitee</div><code style="font-size: 0.75rem; color: var(--text-muted);">type=gitee</code></div>
                    </div>
                    <div class="platform-item" style="display: flex; align-items: center; gap: 0.625rem; padding: 0.625rem 0.75rem; background-color: var(--bg-surface-hover); border-radius: var(--radius-md); transition: all 0.2s ease; cursor: default;">
                        <img src="/assets/icon/github.svg" alt="GitHub" style="width: 1.25rem; height: 1.25rem;">
                        <div><div style="font-weight: 600; font-size: 0.875rem;">GitHub</div><code style="font-size: 0.75rem; color: var(--text-muted);">type=github</code></div>
                    </div>
                    <div class="platform-item" style="display: flex; align-items: center; gap: 0.625rem; padding: 0.625rem 0.75rem; background-color: var(--bg-surface-hover); border-radius: var(--radius-md); transition: all 0.2s ease; cursor: default;">
                        <img src="/assets/icon/xiaomi.svg" alt="小米" style="width: 1.25rem; height: 1.25rem;">
                        <div><div style="font-weight: 600; font-size: 0.875rem;">小米登录</div><code style="font-size: 0.75rem; color: var(--text-muted);">type=xiaomi</code></div>
                    </div>
                    <div class="platform-item" style="display: flex; align-items: center; gap: 0.625rem; padding: 0.625rem 0.75rem; background-color: var(--bg-surface-hover); border-radius: var(--radius-md); transition: all 0.2s ease; cursor: default;">
                        <img src="/assets/icon/bilibili.svg" alt="哔哩哔哩" style="width: 1.25rem; height: 1.25rem;">
                        <div><div style="font-weight: 600; font-size: 0.875rem;">哔哩哔哩</div><code style="font-size: 0.75rem; color: var(--text-muted);">type=bilibili</code></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 错误码 -->
        <div id="errors" class="card">
            <div class="card-body">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                    <div style="width: 2.75rem; height: 2.75rem; background: linear-gradient(135deg, #ef4444, #dc2626); border-radius: 0.625rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <span class="iconify" data-icon="tabler:alert-triangle" style="color: white; font-size: 1.5rem;"></span>
                    </div>
                    <div style="min-width: 0;">
                        <h2 style="font-size: 1.125rem; font-weight: 700; color: var(--text-main); margin: 0; line-height: 1.3;">错误码说明</h2>
                        <p style="font-size: 0.8125rem; color: var(--text-secondary); margin: 0; line-height: 1.3;">接口返回的错误码含义</p>
                    </div>
                </div>
                <div class="table-responsive">
                <table class="doc-table">
                    <thead><tr><th>code</th><th>errcode</th><th>说明</th><th>解决方案</th></tr></thead>
                    <tbody>
                        <tr><td><code>0</code></td><td>-</td><td>请求成功</td><td>-</td></tr>
                        <tr><td><code>2</code></td><td>-</td><td>等待用户授权中</td><td>继续轮询等待，或提示用户完成授权</td></tr>
                        <tr><td><code>-1</code></td><td>101</td><td>参数缺失或无效</td><td>检查必填参数是否完整，参数值是否正确</td></tr>
                        <tr><td><code>-1</code></td><td>102</td><td>应用不存在或已禁用</td><td>检查 appid 是否正确，应用是否已被禁用</td></tr>
                        <tr><td><code>-1</code></td><td>103</td><td>appkey错误或回调域名未授权</td><td>检查 appkey 是否正确，回调地址域名是否与应用配置的授权域名匹配</td></tr>
                        <tr><td><code>-1</code></td><td>104</td><td>登录方式未开启或未配置</td><td>检查该登录平台是否已在应用中启用，平台是否已配置</td></tr>
                        <tr><td><code>-1</code></td><td>105</td><td>授权码无效或已过期</td><td>code 只能使用一次且有效期约5分钟，请重新发起授权</td></tr>
                        <tr><td><code>-1</code></td><td>106</td><td>用户不存在</td><td>查询接口使用的 social_uid 不存在</td></tr>
                        <tr><td><code>-1</code></td><td>201</td><td>调用次数已达上限</td><td>当日调用次数已用完，请升级套餐或等待次日重置</td></tr>
                        <tr><td><code>-1</code></td><td>301</td><td>第三方平台接口错误</td><td>第三方平台返回错误，请稍后重试或联系客服</td></tr>
                    </tbody>
                </table>
                </div>
            </div>
        </div>

        <!-- SDK下载 -->
        <div id="sdk" class="card">
            <div class="card-body">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                    <div style="width: 2.75rem; height: 2.75rem; background: linear-gradient(135deg, #06b6d4, #0891b2); border-radius: 0.625rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <span class="iconify" data-icon="tabler:code" style="color: white; font-size: 1.5rem;"></span>
                    </div>
                    <div style="min-width: 0;">
                        <h2 style="font-size: 1.125rem; font-weight: 700; color: var(--text-main); margin: 0; line-height: 1.3;">SDK下载</h2>
                        <p style="font-size: 0.8125rem; color: var(--text-secondary); margin: 0; line-height: 1.3;">快速集成SDK</p>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 1rem;">
                    <div style="border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1rem;">
                        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                            <span class="iconify" data-icon="logos:php" style="font-size: 1.75rem;"></span>
                            <div>
                                <div style="font-weight: 600; font-size: 0.9375rem;">PHP SDK</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">支持 PHP 7.0+</div>
                            </div>
                        </div>
                        <a href="/assets/sdk/oauth-sdk-php.zip" class="btn btn-outline btn-sm" style="width: 100%;" download>
                            <span class="iconify" data-icon="tabler:download"></span> 下载 SDK
                        </a>
                    </div>
                    <div style="border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1rem;">
                        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                            <span class="iconify" data-icon="logos:javascript" style="font-size: 1.75rem;"></span>
                            <div>
                                <div style="font-weight: 600; font-size: 0.9375rem;">JavaScript SDK</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">支持 Node.js / 浏览器</div>
                            </div>
                        </div>
                        <a href="/assets/sdk/oauth-sdk-js.zip" class="btn btn-outline btn-sm" style="width: 100%;" download>
                            <span class="iconify" data-icon="tabler:download"></span> 下载 SDK
                        </a>
                    </div>
                    <div style="border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1rem;">
                        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                            <span class="iconify" data-icon="logos:python" style="font-size: 1.75rem;"></span>
                            <div>
                                <div style="font-weight: 600; font-size: 0.9375rem;">Python SDK</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">支持 Python 3.6+</div>
                            </div>
                        </div>
                        <a href="/assets/sdk/oauth-sdk-python.zip" class="btn btn-outline btn-sm" style="width: 100%;" download>
                            <span class="iconify" data-icon="tabler:download"></span> 下载 SDK
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <?php if (isset($isPublic) && $isPublic): ?>
        <!-- CTA -->
        <div class="card" style="background: linear-gradient(135deg, var(--color-primary), #8b5cf6); color: white;">
            <div class="card-body" style="text-align: center; padding: 2rem;">
                <h3 style="font-size: 1.125rem; font-weight: 700; margin: 0 0 0.5rem;">准备好开始接入了吗？</h3>
                <p style="opacity: 0.9; margin: 0 0 1.25rem; font-size: 0.875rem;">免费注册，立即获取 APPID 和 APPKEY</p>
                <a href="/user/reg" class="btn" style="background: white; color: var(--color-primary); font-weight: 600;">
                    免费注册 <span class="iconify" data-icon="tabler:arrow-right"></span>
                </a>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<script>
// 所有函数挂载到 window，确保 AJAX 加载后可用
window.toggleDocNav = function() {
    var sidebar = document.getElementById('docSidebar');
    var overlay = document.getElementById('docOverlay');
    if (sidebar) sidebar.classList.toggle('active');
    if (overlay) overlay.classList.toggle('active');
    document.body.style.overflow = (sidebar && sidebar.classList.contains('active')) ? 'hidden' : '';
};

window.closeDocNav = function() {
    if (window.innerWidth <= 768) {
        var sidebar = document.getElementById('docSidebar');
        var overlay = document.getElementById('docOverlay');
        if (sidebar) sidebar.classList.remove('active');
        if (overlay) overlay.classList.remove('active');
        document.body.style.overflow = '';
    }
};

// 获取实际的滚动容器
function getDocScrollContainer() {
    // 按优先级检查可能的滚动容器
    var containers = [
        document.getElementById('main-content'),
        document.querySelector('.page-body'),
        document.querySelector('.user-content'),
        document.querySelector('.admin-content')
    ];
    
    for (var i = 0; i < containers.length; i++) {
        var el = containers[i];
        if (el && el.scrollHeight > el.clientHeight) {
            return el;
        }
    }
    return null; // 使用 window
}

// 文档导航点击处理
window.handleDocNavClick = function(targetId) {
    var target = document.getElementById(targetId);
    if (!target) return;
    
    // 更新激活状态
    var navItems = document.querySelectorAll('#docNav .doc-nav-item');
    navItems.forEach(function(item) {
        item.classList.remove('active');
        if (item.getAttribute('data-target') === targetId) {
            item.classList.add('active');
        }
    });
    
    // 计算目标位置并滚动
    var rect = target.getBoundingClientRect();
    var offset = 100; // 顶部偏移
    var container = getDocScrollContainer();
    
    if (container) {
        // 使用找到的滚动容器
        var targetPos = container.scrollTop + rect.top - offset;
        container.scrollTo({ top: targetPos, behavior: 'smooth' });
    } else {
        // 使用 window 作为滚动容器
        var targetPos = window.pageYOffset + rect.top - offset;
        window.scrollTo({ top: targetPos, behavior: 'smooth' });
    }
    
    closeDocNav();
};

// 滚动监听 - 更新导航状态
(function() {
    function updateActiveNav() {
        var docNav = document.getElementById('docNav');
        if (!docNav) return;
        
        var navItems = docNav.querySelectorAll('.doc-nav-item');
        var sections = document.querySelectorAll('.doc-content > .card[id]');
        if (!sections.length) return;
        
        var activeId = null;
        var viewportTop = 160; // 检测点：距离视口顶部 160px
        
        sections.forEach(function(section) {
            var rect = section.getBoundingClientRect();
            // 如果 section 顶部在检测点之上，则认为它是当前活动的
            if (rect.top <= viewportTop) {
                activeId = section.id;
            }
        });
        
        // 如果没有找到，默认第一个
        if (!activeId && sections.length > 0) {
            activeId = sections[0].id;
        }
        
        if (activeId) {
            navItems.forEach(function(item) {
                if (item.getAttribute('data-target') === activeId) {
                    item.classList.add('active');
                } else {
                    item.classList.remove('active');
                }
            });
        }
    }
    
    // 绑定到多个可能的滚动容器
    var mainContent = document.getElementById('main-content');
    var pageBody = document.querySelector('.page-body');
    var userContent = document.querySelector('.user-content');
    var adminContent = document.querySelector('.admin-content');
    
    // 绑定所有可能的滚动容器
    [window, mainContent, pageBody, userContent, adminContent].forEach(function(el) {
        if (el) {
            el.addEventListener('scroll', updateActiveNav, { passive: true });
        }
    });
    
    // 初始化
    updateActiveNav();
    
    // 延迟再执行一次，确保 DOM 完全加载
    setTimeout(updateActiveNav, 500);
})();
</script>
