<?php
ob_start();
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
$domain = $_SERVER['HTTP_HOST'];
?>

<div style="display: grid; grid-template-columns: 240px 1fr; gap: 1.5rem;">
    <!-- 左侧导航 -->
    <div style="position: sticky; top: 1rem; align-self: start;">
        <div class="card">
            <div class="card-body" style="padding: 1rem;">
                <h3 style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 1rem;">平台配置指南</h3>
                <nav style="display: flex; flex-direction: column; gap: 0.25rem;" id="docNav">
                    <a href="javascript:;" data-target="qq" class="doc-nav-item active" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0.75rem; border-radius: var(--radius-md); color: var(--text-main); text-decoration: none; transition: background-color 0.2s; cursor: pointer;" onclick="handlePlatformDocNavClick('qq')">
                        <img src="/assets/icon/qq.svg" alt="QQ" style="width: 1.25rem; height: 1.25rem;">
                        <span>QQ登录</span>
                    </a>
                    <a href="javascript:;" data-target="wx" class="doc-nav-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0.75rem; border-radius: var(--radius-md); color: var(--text-main); text-decoration: none; transition: background-color 0.2s; cursor: pointer;" onclick="handlePlatformDocNavClick('wx')">
                        <img src="/assets/icon/wx.svg" alt="微信" style="width: 1.25rem; height: 1.25rem;">
                        <span>微信登录</span>
                    </a>
                    <a href="javascript:;" data-target="alipay" class="doc-nav-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0.75rem; border-radius: var(--radius-md); color: var(--text-main); text-decoration: none; transition: background-color 0.2s; cursor: pointer;" onclick="handlePlatformDocNavClick('alipay')">
                        <img src="/assets/icon/alipay.svg" alt="支付宝" style="width: 1.25rem; height: 1.25rem;">
                        <span>支付宝登录</span>
                    </a>
                    <a href="javascript:;" data-target="sina" class="doc-nav-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0.75rem; border-radius: var(--radius-md); color: var(--text-main); text-decoration: none; transition: background-color 0.2s; cursor: pointer;" onclick="handlePlatformDocNavClick('sina')">
                        <img src="/assets/icon/sina.svg" alt="微博" style="width: 1.25rem; height: 1.25rem;">
                        <span>微博登录</span>
                    </a>
                    <a href="javascript:;" data-target="baidu" class="doc-nav-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0.75rem; border-radius: var(--radius-md); color: var(--text-main); text-decoration: none; transition: background-color 0.2s; cursor: pointer;" onclick="handlePlatformDocNavClick('baidu')">
                        <img src="/assets/icon/baidu.svg" alt="百度" style="width: 1.25rem; height: 1.25rem;">
                        <span>百度登录</span>
                    </a>
                    <a href="javascript:;" data-target="douyin" class="doc-nav-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0.75rem; border-radius: var(--radius-md); color: var(--text-main); text-decoration: none; transition: background-color 0.2s; cursor: pointer;" onclick="handlePlatformDocNavClick('douyin')">
                        <img src="/assets/icon/douyin.svg" alt="抖音" style="width: 1.25rem; height: 1.25rem;">
                        <span>抖音登录</span>
                    </a>
                    <a href="javascript:;" data-target="huawei" class="doc-nav-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0.75rem; border-radius: var(--radius-md); color: var(--text-main); text-decoration: none; transition: background-color 0.2s; cursor: pointer;" onclick="handlePlatformDocNavClick('huawei')">
                        <img src="/assets/icon/huawei.svg" alt="华为" style="width: 1.25rem; height: 1.25rem;">
                        <span>华为登录</span>
                    </a>
                    <a href="javascript:;" data-target="google" class="doc-nav-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0.75rem; border-radius: var(--radius-md); color: var(--text-main); text-decoration: none; transition: background-color 0.2s; cursor: pointer;" onclick="handlePlatformDocNavClick('google')">
                        <img src="/assets/icon/google.svg" alt="谷歌" style="width: 1.25rem; height: 1.25rem;">
                        <span>谷歌登录</span>
                    </a>
                    <a href="javascript:;" data-target="microsoft" class="doc-nav-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0.75rem; border-radius: var(--radius-md); color: var(--text-main); text-decoration: none; transition: background-color 0.2s; cursor: pointer;" onclick="handlePlatformDocNavClick('microsoft')">
                        <img src="/assets/icon/microsoft.svg" alt="微软" style="width: 1.25rem; height: 1.25rem;">
                        <span>微软登录</span>
                    </a>
                    <a href="javascript:;" data-target="wework" class="doc-nav-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0.75rem; border-radius: var(--radius-md); color: var(--text-main); text-decoration: none; transition: background-color 0.2s; cursor: pointer;" onclick="handlePlatformDocNavClick('wework')">
                        <img src="/assets/icon/wework.svg" alt="企业微信" style="width: 1.25rem; height: 1.25rem;">
                        <span>企业微信登录</span>
                    </a>
                    <a href="javascript:;" data-target="dingtalk" class="doc-nav-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0.75rem; border-radius: var(--radius-md); color: var(--text-main); text-decoration: none; transition: background-color 0.2s; cursor: pointer;" onclick="handlePlatformDocNavClick('dingtalk')">
                        <img src="/assets/icon/dingtalk.svg" alt="钉钉" style="width: 1.25rem; height: 1.25rem;">
                        <span>钉钉登录</span>
                    </a>
                    <a href="javascript:;" data-target="feishu" class="doc-nav-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0.75rem; border-radius: var(--radius-md); color: var(--text-main); text-decoration: none; transition: background-color 0.2s; cursor: pointer;" onclick="handlePlatformDocNavClick('feishu')">
                        <img src="/assets/icon/feishu.svg" alt="飞书" style="width: 1.25rem; height: 1.25rem;">
                        <span>飞书登录</span>
                    </a>
                    <a href="javascript:;" data-target="gitee" class="doc-nav-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0.75rem; border-radius: var(--radius-md); color: var(--text-main); text-decoration: none; transition: background-color 0.2s; cursor: pointer;" onclick="handlePlatformDocNavClick('gitee')">
                        <img src="/assets/icon/gitee.svg" alt="Gitee" style="width: 1.25rem; height: 1.25rem;">
                        <span>Gitee登录</span>
                    </a>
                    <a href="javascript:;" data-target="github" class="doc-nav-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0.75rem; border-radius: var(--radius-md); color: var(--text-main); text-decoration: none; transition: background-color 0.2s; cursor: pointer;" onclick="handlePlatformDocNavClick('github')">
                        <img src="/assets/icon/github.svg" alt="GitHub" style="width: 1.25rem; height: 1.25rem;">
                        <span>GitHub登录</span>
                    </a>
                    <a href="javascript:;" data-target="xiaomi" class="doc-nav-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0.75rem; border-radius: var(--radius-md); color: var(--text-main); text-decoration: none; transition: background-color 0.2s; cursor: pointer;" onclick="handlePlatformDocNavClick('xiaomi')">
                        <img src="/assets/icon/xiaomi.svg" alt="小米" style="width: 1.25rem; height: 1.25rem;">
                        <span>小米登录</span>
                    </a>
                    <a href="javascript:;" data-target="bilibili" class="doc-nav-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0.75rem; border-radius: var(--radius-md); color: var(--text-main); text-decoration: none; transition: background-color 0.2s; cursor: pointer;" onclick="handlePlatformDocNavClick('bilibili')">
                        <img src="/assets/icon/bilibili.svg" alt="哔哩哔哩" style="width: 1.25rem; height: 1.25rem;">
                        <span>哔哩哔哩登录</span>
                    </a>
                </nav>
            </div>
        </div>
    </div>

    <!-- 右侧内容 -->
    <div style="display: flex; flex-direction: column; gap: 2rem;">

        <!-- QQ登录 -->
        <div id="qq" class="card">
            <div class="card-body">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                    <img src="/assets/icon/qq.svg" alt="QQ" style="width: 3rem; height: 3rem;">
                    <div>
                        <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.25rem;">QQ互联配置</h2>
                        <a href="https://connect.qq.com/" target="_blank" style="font-size: 0.875rem; color: var(--color-primary); display: flex; align-items: center; gap: 0.25rem;">访问QQ互联平台 <span class="iconify" data-icon="tabler:external-link"></span></a>
                    </div>
                </div>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div class="alert alert-info"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">申请步骤</h4><ol style="list-style-type: decimal; list-style-position: inside; margin: 0; padding-left: 0.5rem;"><li>登录QQ互联平台</li><li>完成开发者认证</li><li>创建网站应用</li><li>提交审核</li></ol></div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div style="background-color: var(--bg-surface-hover); padding: 1rem; border-radius: var(--radius-md);"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">APP ID</h4><p style="font-size: 0.875rem; color: var(--text-secondary);">应用管理 → APP ID</p></div>
                        <div style="background-color: var(--bg-surface-hover); padding: 1rem; border-radius: var(--radius-md);"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">APP Key</h4><p style="font-size: 0.875rem; color: var(--text-secondary);">应用管理 → APP Key</p></div>
                    </div>
                    <div class="alert alert-warning"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">回调地址</h4><code style="display: block; background-color: var(--bg-surface); padding: 0.5rem 0.75rem; border-radius: var(--radius-sm); cursor: pointer;" onclick="copyText('<?= $baseUrl ?>/oauth/qq/callback')"><?= $baseUrl ?>/oauth/qq/callback</code></div>
                </div>
            </div>
        </div>

        <!-- 微信登录 -->
        <div id="wx" class="card">
            <div class="card-body">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                    <img src="/assets/icon/wx.svg" alt="微信" style="width: 3rem; height: 3rem;">
                    <div>
                        <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.25rem;">微信开放平台配置</h2>
                        <a href="https://open.weixin.qq.com/" target="_blank" style="font-size: 0.875rem; color: var(--color-primary); display: flex; align-items: center; gap: 0.25rem;">访问微信开放平台 <span class="iconify" data-icon="tabler:external-link"></span></a>
                    </div>
                </div>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div class="alert alert-info"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">申请步骤</h4><ol style="list-style-type: decimal; list-style-position: inside; margin: 0; padding-left: 0.5rem;"><li>登录微信开放平台</li><li>完成企业资质认证（300元/年）</li><li>创建网站应用</li><li>提交审核</li></ol></div>
                    <div class="alert alert-error"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">重要说明</h4><p style="font-size: 0.875rem;">微信网站应用需要企业资质认证，个人开发者无法申请。</p></div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div style="background-color: var(--bg-surface-hover); padding: 1rem; border-radius: var(--radius-md);"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">AppID</h4><p style="font-size: 0.875rem; color: var(--text-secondary);">管理中心 → 网站应用 → AppID</p></div>
                        <div style="background-color: var(--bg-surface-hover); padding: 1rem; border-radius: var(--radius-md);"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">AppSecret</h4><p style="font-size: 0.875rem; color: var(--text-secondary);">管理中心 → 网站应用 → AppSecret</p></div>
                    </div>
                    <div class="alert alert-warning"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">回调地址</h4><code style="display: block; background-color: var(--bg-surface); padding: 0.5rem 0.75rem; border-radius: var(--radius-sm); cursor: pointer;" onclick="copyText('<?= $baseUrl ?>/oauth/wx/callback')"><?= $baseUrl ?>/oauth/wx/callback</code></div>
                </div>
            </div>
        </div>

        <!-- 支付宝登录 -->
        <div id="alipay" class="card">
            <div class="card-body">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                    <img src="/assets/icon/alipay.svg" alt="支付宝" style="width: 3rem; height: 3rem;">
                    <div>
                        <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.25rem;">支付宝开放平台配置</h2>
                        <a href="https://open.alipay.com/" target="_blank" style="font-size: 0.875rem; color: var(--color-primary); display: flex; align-items: center; gap: 0.25rem;">访问支付宝开放平台 <span class="iconify" data-icon="tabler:external-link"></span></a>
                    </div>
                </div>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div class="alert alert-info"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">申请步骤</h4><ol style="list-style-type: decimal; list-style-position: inside; margin: 0; padding-left: 0.5rem;"><li>登录支付宝开放平台</li><li>创建网页应用</li><li>添加"获取会员信息"能力</li><li>配置RSA2密钥</li><li>提交审核</li></ol></div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div style="background-color: var(--bg-surface-hover); padding: 1rem; border-radius: var(--radius-md);"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">APPID</h4><p style="font-size: 0.875rem; color: var(--text-secondary);">开发设置 → APPID</p></div>
                        <div style="background-color: var(--bg-surface-hover); padding: 1rem; border-radius: var(--radius-md);"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">应用私钥</h4><p style="font-size: 0.875rem; color: var(--text-secondary);">RSA2私钥（需配置公钥到支付宝）</p></div>
                    </div>
                    <div class="alert alert-warning"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">回调地址</h4><code style="display: block; background-color: var(--bg-surface); padding: 0.5rem 0.75rem; border-radius: var(--radius-sm); cursor: pointer;" onclick="copyText('<?= $baseUrl ?>/oauth/alipay/callback')"><?= $baseUrl ?>/oauth/alipay/callback</code></div>
                </div>
            </div>
        </div>

        <!-- 微博登录 -->
        <div id="sina" class="card">
            <div class="card-body">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                    <img src="/assets/icon/sina.svg" alt="微博" style="width: 3rem; height: 3rem;">
                    <div>
                        <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.25rem;">微博开放平台配置</h2>
                        <a href="https://open.weibo.com/" target="_blank" style="font-size: 0.875rem; color: var(--color-primary); display: flex; align-items: center; gap: 0.25rem;">访问微博开放平台 <span class="iconify" data-icon="tabler:external-link"></span></a>
                    </div>
                </div>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div class="alert alert-info"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">申请步骤</h4><ol style="list-style-type: decimal; list-style-position: inside; margin: 0; padding-left: 0.5rem;"><li>登录微博开放平台</li><li>微连接 → 网站接入</li><li>填写网站信息（需备案域名）</li><li>等待审核</li></ol></div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div style="background-color: var(--bg-surface-hover); padding: 1rem; border-radius: var(--radius-md);"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">App Key</h4><p style="font-size: 0.875rem; color: var(--text-secondary);">应用信息 → App Key</p></div>
                        <div style="background-color: var(--bg-surface-hover); padding: 1rem; border-radius: var(--radius-md);"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">App Secret</h4><p style="font-size: 0.875rem; color: var(--text-secondary);">应用信息 → App Secret</p></div>
                    </div>
                    <div class="alert alert-warning"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">回调地址</h4><code style="display: block; background-color: var(--bg-surface); padding: 0.5rem 0.75rem; border-radius: var(--radius-sm); cursor: pointer;" onclick="copyText('<?= $baseUrl ?>/oauth/sina/callback')"><?= $baseUrl ?>/oauth/sina/callback</code></div>
                </div>
            </div>
        </div>

        <!-- 百度登录 -->
        <div id="baidu" class="card">
            <div class="card-body">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                    <img src="/assets/icon/baidu.svg" alt="百度" style="width: 3rem; height: 3rem;">
                    <div>
                        <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.25rem;">百度开发者中心配置</h2>
                        <a href="https://developer.baidu.com/" target="_blank" style="font-size: 0.875rem; color: var(--color-primary); display: flex; align-items: center; gap: 0.25rem;">访问百度开发者中心 <span class="iconify" data-icon="tabler:external-link"></span></a>
                    </div>
                </div>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div class="alert alert-info"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">申请步骤</h4><ol style="list-style-type: decimal; list-style-position: inside; margin: 0; padding-left: 0.5rem;"><li>登录百度开发者中心</li><li>进入控制台 → 创建应用</li><li>选择网站类型</li><li>配置授权回调地址</li></ol></div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div style="background-color: var(--bg-surface-hover); padding: 1rem; border-radius: var(--radius-md);"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">API Key</h4><p style="font-size: 0.875rem; color: var(--text-secondary);">应用详情 → API Key</p></div>
                        <div style="background-color: var(--bg-surface-hover); padding: 1rem; border-radius: var(--radius-md);"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">Secret Key</h4><p style="font-size: 0.875rem; color: var(--text-secondary);">应用详情 → Secret Key</p></div>
                    </div>
                    <div class="alert alert-warning"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">回调地址</h4><code style="display: block; background-color: var(--bg-surface); padding: 0.5rem 0.75rem; border-radius: var(--radius-sm); cursor: pointer;" onclick="copyText('<?= $baseUrl ?>/oauth/baidu/callback')"><?= $baseUrl ?>/oauth/baidu/callback</code></div>
                </div>
            </div>
        </div>

        <!-- 抖音登录 -->
        <div id="douyin" class="card">
            <div class="card-body">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                    <img src="/assets/icon/douyin.svg" alt="抖音" style="width: 3rem; height: 3rem;">
                    <div>
                        <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.25rem;">抖音开放平台配置</h2>
                        <a href="https://open.douyin.com/" target="_blank" style="font-size: 0.875rem; color: var(--color-primary); display: flex; align-items: center; gap: 0.25rem;">访问抖音开放平台 <span class="iconify" data-icon="tabler:external-link"></span></a>
                    </div>
                </div>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div class="alert alert-info"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">申请步骤</h4><ol style="list-style-type: decimal; list-style-position: inside; margin: 0; padding-left: 0.5rem;"><li>登录抖音开放平台</li><li>创建网站应用</li><li>配置授权回调域</li><li>申请用户授权能力</li></ol></div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div style="background-color: var(--bg-surface-hover); padding: 1rem; border-radius: var(--radius-md);"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">Client Key</h4><p style="font-size: 0.875rem; color: var(--text-secondary);">应用详情 → Client Key</p></div>
                        <div style="background-color: var(--bg-surface-hover); padding: 1rem; border-radius: var(--radius-md);"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">Client Secret</h4><p style="font-size: 0.875rem; color: var(--text-secondary);">应用详情 → Client Secret</p></div>
                    </div>
                    <div class="alert alert-warning"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">回调地址</h4><code style="display: block; background-color: var(--bg-surface); padding: 0.5rem 0.75rem; border-radius: var(--radius-sm); cursor: pointer;" onclick="copyText('<?= $baseUrl ?>/oauth/douyin/callback')"><?= $baseUrl ?>/oauth/douyin/callback</code></div>
                </div>
            </div>
        </div>

        <!-- 华为登录 -->
        <div id="huawei" class="card">
            <div class="card-body">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                    <img src="/assets/icon/huawei.svg" alt="华为" style="width: 3rem; height: 3rem;">
                    <div>
                        <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.25rem;">华为开发者联盟配置</h2>
                        <a href="https://developer.huawei.com/" target="_blank" style="font-size: 0.875rem; color: var(--color-primary); display: flex; align-items: center; gap: 0.25rem;">访问华为开发者联盟 <span class="iconify" data-icon="tabler:external-link"></span></a>
                    </div>
                </div>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div class="alert alert-info"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">申请步骤</h4><ol style="list-style-type: decimal; list-style-position: inside; margin: 0; padding-left: 0.5rem;"><li>登录华为开发者联盟</li><li>完成开发者实名认证</li><li>AppGallery Connect → 创建项目</li><li>开通华为帐号服务</li><li>配置OAuth客户端</li></ol></div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div style="background-color: var(--bg-surface-hover); padding: 1rem; border-radius: var(--radius-md);"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">Client ID</h4><p style="font-size: 0.875rem; color: var(--text-secondary);">项目设置 → OAuth 2.0客户端ID</p></div>
                        <div style="background-color: var(--bg-surface-hover); padding: 1rem; border-radius: var(--radius-md);"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">Client Secret</h4><p style="font-size: 0.875rem; color: var(--text-secondary);">项目设置 → 客户端密钥</p></div>
                    </div>
                    <div class="alert alert-warning"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">回调地址</h4><code style="display: block; background-color: var(--bg-surface); padding: 0.5rem 0.75rem; border-radius: var(--radius-sm); cursor: pointer;" onclick="copyText('<?= $baseUrl ?>/oauth/huawei/callback')"><?= $baseUrl ?>/oauth/huawei/callback</code></div>
                </div>
            </div>
        </div>

        <!-- 谷歌登录 -->
        <div id="google" class="card">
            <div class="card-body">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                    <img src="/assets/icon/google.svg" alt="谷歌" style="width: 3rem; height: 3rem;">
                    <div>
                        <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.25rem;">Google OAuth配置</h2>
                        <a href="https://console.cloud.google.com/" target="_blank" style="font-size: 0.875rem; color: var(--color-primary); display: flex; align-items: center; gap: 0.25rem;">访问Google Cloud Console <span class="iconify" data-icon="tabler:external-link"></span></a>
                    </div>
                </div>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div class="alert alert-info"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">申请步骤</h4><ol style="list-style-type: decimal; list-style-position: inside; margin: 0; padding-left: 0.5rem;"><li>登录Google Cloud Console</li><li>创建项目</li><li>APIs & Services → Credentials</li><li>Create Credentials → OAuth client ID</li><li>配置OAuth同意屏幕</li></ol></div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div style="background-color: var(--bg-surface-hover); padding: 1rem; border-radius: var(--radius-md);"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">Client ID</h4><p style="font-size: 0.875rem; color: var(--text-secondary);">格式：xxxxx.apps.googleusercontent.com</p></div>
                        <div style="background-color: var(--bg-surface-hover); padding: 1rem; border-radius: var(--radius-md);"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">Client Secret</h4><p style="font-size: 0.875rem; color: var(--text-secondary);">OAuth客户端密钥</p></div>
                    </div>
                    <div class="alert alert-warning"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">回调地址</h4><code style="display: block; background-color: var(--bg-surface); padding: 0.5rem 0.75rem; border-radius: var(--radius-sm); cursor: pointer;" onclick="copyText('<?= $baseUrl ?>/oauth/google/callback')"><?= $baseUrl ?>/oauth/google/callback</code></div>
                </div>
            </div>
        </div>

        <!-- 微软登录 -->
        <div id="microsoft" class="card">
            <div class="card-body">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                    <img src="/assets/icon/microsoft.svg" alt="微软" style="width: 3rem; height: 3rem;">
                    <div>
                        <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.25rem;">微软Azure AD配置</h2>
                        <a href="https://portal.azure.com/" target="_blank" style="font-size: 0.875rem; color: var(--color-primary); display: flex; align-items: center; gap: 0.25rem;">访问Azure Portal <span class="iconify" data-icon="tabler:external-link"></span></a>
                    </div>
                </div>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div class="alert alert-info"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">申请步骤</h4><ol style="list-style-type: decimal; list-style-position: inside; margin: 0; padding-left: 0.5rem;"><li>登录Azure Portal</li><li>Azure Active Directory → 应用注册</li><li>新注册 → 创建应用</li><li>配置重定向URI</li><li>创建客户端密码</li></ol></div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div style="background-color: var(--bg-surface-hover); padding: 1rem; border-radius: var(--radius-md);"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">Client ID</h4><p style="font-size: 0.875rem; color: var(--text-secondary);">概述 → 应用程序(客户端)ID</p></div>
                        <div style="background-color: var(--bg-surface-hover); padding: 1rem; border-radius: var(--radius-md);"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">Client Secret</h4><p style="font-size: 0.875rem; color: var(--text-secondary);">证书和密码 → 客户端密码</p></div>
                    </div>
                    <div class="alert alert-warning"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">回调地址</h4><code style="display: block; background-color: var(--bg-surface); padding: 0.5rem 0.75rem; border-radius: var(--radius-sm); cursor: pointer;" onclick="copyText('<?= $baseUrl ?>/oauth/microsoft/callback')"><?= $baseUrl ?>/oauth/microsoft/callback</code></div>
                </div>
            </div>
        </div>

        <!-- 企业微信登录 -->
        <div id="wework" class="card">
            <div class="card-body">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                    <img src="/assets/icon/wework.svg" alt="企业微信" style="width: 3rem; height: 3rem;">
                    <div>
                        <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.25rem;">企业微信配置</h2>
                        <a href="https://work.weixin.qq.com/" target="_blank" style="font-size: 0.875rem; color: var(--color-primary); display: flex; align-items: center; gap: 0.25rem;">访问企业微信管理后台 <span class="iconify" data-icon="tabler:external-link"></span></a>
                    </div>
                </div>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div class="alert alert-info"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">申请步骤</h4><ol style="list-style-type: decimal; list-style-position: inside; margin: 0; padding-left: 0.5rem;"><li>登录企业微信管理后台</li><li>应用管理 → 自建应用 → 创建应用</li><li>设置应用可见范围</li><li>配置网页授权可信域名</li></ol></div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div style="background-color: var(--bg-surface-hover); padding: 1rem; border-radius: var(--radius-md);"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">CorpID</h4><p style="font-size: 0.875rem; color: var(--text-secondary);">我的企业 → 企业ID</p></div>
                        <div style="background-color: var(--bg-surface-hover); padding: 1rem; border-radius: var(--radius-md);"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">Secret</h4><p style="font-size: 0.875rem; color: var(--text-secondary);">应用管理 → 自建应用 → Secret</p></div>
                    </div>
                    <div class="alert alert-warning"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">回调地址</h4><code style="display: block; background-color: var(--bg-surface); padding: 0.5rem 0.75rem; border-radius: var(--radius-sm); cursor: pointer;" onclick="copyText('<?= $baseUrl ?>/oauth/wework/callback')"><?= $baseUrl ?>/oauth/wework/callback</code></div>
                </div>
            </div>
        </div>

        <!-- 钉钉登录 -->
        <div id="dingtalk" class="card">
            <div class="card-body">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                    <img src="/assets/icon/dingtalk.svg" alt="钉钉" style="width: 3rem; height: 3rem;">
                    <div>
                        <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.25rem;">钉钉开放平台配置</h2>
                        <a href="https://open.dingtalk.com/" target="_blank" style="font-size: 0.875rem; color: var(--color-primary); display: flex; align-items: center; gap: 0.25rem;">访问钉钉开放平台 <span class="iconify" data-icon="tabler:external-link"></span></a>
                    </div>
                </div>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div class="alert alert-info"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">申请步骤</h4><ol style="list-style-type: decimal; list-style-position: inside; margin: 0; padding-left: 0.5rem;"><li>登录钉钉开放平台</li><li>应用开发 → 企业内部应用</li><li>创建H5微应用</li><li>配置回调域名</li><li>申请权限</li></ol></div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div style="background-color: var(--bg-surface-hover); padding: 1rem; border-radius: var(--radius-md);"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">AppKey / Client ID</h4><p style="font-size: 0.875rem; color: var(--text-secondary);">应用信息 → 凭证与基础信息</p></div>
                        <div style="background-color: var(--bg-surface-hover); padding: 1rem; border-radius: var(--radius-md);"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">AppSecret / Client Secret</h4><p style="font-size: 0.875rem; color: var(--text-secondary);">应用信息 → 凭证与基础信息</p></div>
                    </div>
                    <div class="alert alert-warning"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">回调地址</h4><code style="display: block; background-color: var(--bg-surface); padding: 0.5rem 0.75rem; border-radius: var(--radius-sm); cursor: pointer;" onclick="copyText('<?= $baseUrl ?>/oauth/dingtalk/callback')"><?= $baseUrl ?>/oauth/dingtalk/callback</code></div>
                </div>
            </div>
        </div>

        <!-- 飞书登录 -->
        <div id="feishu" class="card">
            <div class="card-body">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                    <img src="/assets/icon/feishu.svg" alt="飞书" style="width: 3rem; height: 3rem;">
                    <div>
                        <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.25rem;">飞书开放平台配置</h2>
                        <a href="https://open.feishu.cn/" target="_blank" style="font-size: 0.875rem; color: var(--color-primary); display: flex; align-items: center; gap: 0.25rem;">访问飞书开放平台 <span class="iconify" data-icon="tabler:external-link"></span></a>
                    </div>
                </div>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div class="alert alert-info"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">申请步骤</h4><ol style="list-style-type: decimal; list-style-position: inside; margin: 0; padding-left: 0.5rem;"><li>登录飞书开放平台</li><li>开发者后台 → 创建企业自建应用</li><li>添加网页应用能力</li><li>安全设置 → 配置重定向URL</li><li>权限管理 → 申请权限并发布</li></ol></div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div style="background-color: var(--bg-surface-hover); padding: 1rem; border-radius: var(--radius-md);"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">App ID</h4><p style="font-size: 0.875rem; color: var(--text-secondary);">凭证与基础信息 → App ID</p></div>
                        <div style="background-color: var(--bg-surface-hover); padding: 1rem; border-radius: var(--radius-md);"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">App Secret</h4><p style="font-size: 0.875rem; color: var(--text-secondary);">凭证与基础信息 → App Secret</p></div>
                    </div>
                    <div class="alert alert-warning"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">回调地址</h4><code style="display: block; background-color: var(--bg-surface); padding: 0.5rem 0.75rem; border-radius: var(--radius-sm); cursor: pointer;" onclick="copyText('<?= $baseUrl ?>/oauth/feishu/callback')"><?= $baseUrl ?>/oauth/feishu/callback</code></div>
                </div>
            </div>
        </div>

        <!-- Gitee登录 -->
        <div id="gitee" class="card">
            <div class="card-body">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                    <img src="/assets/icon/gitee.svg" alt="Gitee" style="width: 3rem; height: 3rem;">
                    <div>
                        <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.25rem;">Gitee OAuth配置</h2>
                        <a href="https://gitee.com/oauth/applications" target="_blank" style="font-size: 0.875rem; color: var(--color-primary); display: flex; align-items: center; gap: 0.25rem;">访问Gitee第三方应用 <span class="iconify" data-icon="tabler:external-link"></span></a>
                    </div>
                </div>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div class="alert alert-info"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">申请步骤</h4><ol style="list-style-type: decimal; list-style-position: inside; margin: 0; padding-left: 0.5rem;"><li>登录Gitee</li><li>设置 → 第三方应用</li><li>创建应用</li><li>配置回调地址</li></ol></div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div style="background-color: var(--bg-surface-hover); padding: 1rem; border-radius: var(--radius-md);"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">Client ID</h4><p style="font-size: 0.875rem; color: var(--text-secondary);">应用详情页面</p></div>
                        <div style="background-color: var(--bg-surface-hover); padding: 1rem; border-radius: var(--radius-md);"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">Client Secret</h4><p style="font-size: 0.875rem; color: var(--text-secondary);">应用详情页面</p></div>
                    </div>
                    <div class="alert alert-warning"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">回调地址</h4><code style="display: block; background-color: var(--bg-surface); padding: 0.5rem 0.75rem; border-radius: var(--radius-sm); cursor: pointer;" onclick="copyText('<?= $baseUrl ?>/oauth/gitee/callback')"><?= $baseUrl ?>/oauth/gitee/callback</code></div>
                </div>
            </div>
        </div>

        <!-- GitHub登录 -->
        <div id="github" class="card">
            <div class="card-body">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                    <img src="/assets/icon/github.svg" alt="GitHub" style="width: 3rem; height: 3rem;">
                    <div>
                        <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.25rem;">GitHub OAuth配置</h2>
                        <a href="https://github.com/settings/developers" target="_blank" style="font-size: 0.875rem; color: var(--color-primary); display: flex; align-items: center; gap: 0.25rem;">访问GitHub开发者设置 <span class="iconify" data-icon="tabler:external-link"></span></a>
                    </div>
                </div>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div class="alert alert-info"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">申请步骤</h4><ol style="list-style-type: decimal; list-style-position: inside; margin: 0; padding-left: 0.5rem;"><li>登录GitHub</li><li>Settings → Developer settings</li><li>OAuth Apps → New OAuth App</li><li>填写应用信息和回调地址</li></ol></div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div style="background-color: var(--bg-surface-hover); padding: 1rem; border-radius: var(--radius-md);"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">Client ID</h4><p style="font-size: 0.875rem; color: var(--text-secondary);">OAuth App详情页面</p></div>
                        <div style="background-color: var(--bg-surface-hover); padding: 1rem; border-radius: var(--radius-md);"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">Client Secret</h4><p style="font-size: 0.875rem; color: var(--text-secondary);">点击Generate生成</p></div>
                    </div>
                    <div class="alert alert-warning"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">回调地址</h4><code style="display: block; background-color: var(--bg-surface); padding: 0.5rem 0.75rem; border-radius: var(--radius-sm); cursor: pointer;" onclick="copyText('<?= $baseUrl ?>/oauth/github/callback')"><?= $baseUrl ?>/oauth/github/callback</code></div>
                </div>
            </div>
        </div>

        <!-- 小米登录 -->
        <div id="xiaomi" class="card">
            <div class="card-body">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                    <img src="/assets/icon/xiaomi.svg" alt="小米" style="width: 3rem; height: 3rem;">
                    <div>
                        <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.25rem;">小米开放平台配置</h2>
                        <a href="https://dev.mi.com/" target="_blank" style="font-size: 0.875rem; color: var(--color-primary); display: flex; align-items: center; gap: 0.25rem;">访问小米开放平台 <span class="iconify" data-icon="tabler:external-link"></span></a>
                    </div>
                </div>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div class="alert alert-info"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">申请步骤</h4><ol style="list-style-type: decimal; list-style-position: inside; margin: 0; padding-left: 0.5rem;"><li>登录小米开放平台</li><li>完成开发者认证</li><li>管理中心 → 帐号服务 → 创建应用</li><li>选择网站应用类型</li><li>配置回调地址</li></ol></div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div style="background-color: var(--bg-surface-hover); padding: 1rem; border-radius: var(--radius-md);"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">App ID</h4><p style="font-size: 0.875rem; color: var(--text-secondary);">应用详情 → App ID</p></div>
                        <div style="background-color: var(--bg-surface-hover); padding: 1rem; border-radius: var(--radius-md);"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">App Secret</h4><p style="font-size: 0.875rem; color: var(--text-secondary);">应用详情 → App Secret</p></div>
                    </div>
                    <div class="alert alert-warning"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">回调地址</h4><code style="display: block; background-color: var(--bg-surface); padding: 0.5rem 0.75rem; border-radius: var(--radius-sm); cursor: pointer;" onclick="copyText('<?= $baseUrl ?>/oauth/xiaomi/callback')"><?= $baseUrl ?>/oauth/xiaomi/callback</code></div>
                </div>
            </div>
        </div>

        <!-- 哔哩哔哩登录 -->
        <div id="bilibili" class="card">
            <div class="card-body">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                    <img src="/assets/icon/bilibili.svg" alt="哔哩哔哩" style="width: 3rem; height: 3rem;">
                    <div>
                        <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.25rem;">哔哩哔哩开放平台配置</h2>
                        <a href="https://open.bilibili.com/" target="_blank" style="font-size: 0.875rem; color: var(--color-primary); display: flex; align-items: center; gap: 0.25rem;">访问哔哩哔哩开放平台 <span class="iconify" data-icon="tabler:external-link"></span></a>
                    </div>
                </div>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div class="alert alert-info"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">申请步骤</h4><ol style="list-style-type: decimal; list-style-position: inside; margin: 0; padding-left: 0.5rem;"><li>登录哔哩哔哩开放平台</li><li>完成开发者认证</li><li>开发者中心 → 创建应用</li><li>选择网站应用类型</li><li>申请用户授权登录能力</li></ol></div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div style="background-color: var(--bg-surface-hover); padding: 1rem; border-radius: var(--radius-md);"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">Client ID</h4><p style="font-size: 0.875rem; color: var(--text-secondary);">应用详情 → Client ID</p></div>
                        <div style="background-color: var(--bg-surface-hover); padding: 1rem; border-radius: var(--radius-md);"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">Client Secret</h4><p style="font-size: 0.875rem; color: var(--text-secondary);">应用详情 → Client Secret</p></div>
                    </div>
                    <div class="alert alert-warning"><h4 style="font-weight: 600; margin-bottom: 0.5rem;">回调地址</h4><code style="display: block; background-color: var(--bg-surface); padding: 0.5rem 0.75rem; border-radius: var(--radius-sm); cursor: pointer;" onclick="copyText('<?= $baseUrl ?>/oauth/bilibili/callback')"><?= $baseUrl ?>/oauth/bilibili/callback</code></div>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
.doc-nav-item:hover { background-color: var(--bg-surface-hover); }
.doc-nav-item.active { background-color: rgba(99, 102, 241, 0.1); color: var(--color-primary); font-weight: 500; }
</style>

<script>
// 获取实际的滚动容器
function getPlatformDocScrollContainer() {
    var containers = [
        document.getElementById('main-content'),
        document.querySelector('.page-body'),
        document.querySelector('.admin-content')
    ];
    
    for (var i = 0; i < containers.length; i++) {
        var el = containers[i];
        if (el && el.scrollHeight > el.clientHeight) {
            return el;
        }
    }
    return null;
}

// 平台文档导航点击处理
window.handlePlatformDocNavClick = function(targetId) {
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
    var container = getPlatformDocScrollContainer();
    
    if (container) {
        var targetPos = container.scrollTop + rect.top - offset;
        container.scrollTo({ top: targetPos, behavior: 'smooth' });
    } else {
        var targetPos = window.pageYOffset + rect.top - offset;
        window.scrollTo({ top: targetPos, behavior: 'smooth' });
    }
};

// 滚动监听 - 更新导航状态
(function() {
    function updateActiveNav() {
        var docNav = document.getElementById('docNav');
        if (!docNav) return;
        
        var navItems = docNav.querySelectorAll('.doc-nav-item');
        var sections = document.querySelectorAll('.card[id]');
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
    var adminContent = document.querySelector('.admin-content');
    
    // 绑定所有可能的滚动容器
    [window, mainContent, pageBody, adminContent].forEach(function(el) {
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

<?php $content = ob_get_clean(); ?>
<?php include ML_ROOT . '/views/layouts/admin.php'; ?>
