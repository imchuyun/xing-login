<?php
ob_start();
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
?>

<div style="display: grid; grid-template-columns: 240px 1fr; gap: 1.5rem;">
    <!-- 左侧导航 -->
    <div style="position: sticky; top: 1rem; align-self: start;">
        <div class="card">
            <div class="card-body" style="padding: 1rem;">
                <h3 style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 1rem;">支付配置指南</h3>
                <nav style="display: flex; flex-direction: column; gap: 0.25rem;" id="payDocNav">
                    <a href="javascript:;" data-target="epay" class="doc-nav-item active" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0.75rem; border-radius: var(--radius-md); color: var(--text-main); text-decoration: none; transition: background-color 0.2s; cursor: pointer;" onclick="handlePayDocNavClick('epay')">
                        <span class="iconify" data-icon="custom:epay" style="font-size: 1.25rem;"></span>
                        <span>易支付</span>
                    </a>
                    <a href="javascript:;" data-target="alipay" class="doc-nav-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0.75rem; border-radius: var(--radius-md); color: var(--text-main); text-decoration: none; transition: background-color 0.2s; cursor: pointer;" onclick="handlePayDocNavClick('alipay')">
                        <img src="/assets/icon/alipay.svg" alt="支付宝" style="width: 1.25rem; height: 1.25rem;">
                        <span>支付宝支付</span>
                    </a>
                    <a href="javascript:;" data-target="wechat" class="doc-nav-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0.75rem; border-radius: var(--radius-md); color: var(--text-main); text-decoration: none; transition: background-color 0.2s; cursor: pointer;" onclick="handlePayDocNavClick('wechat')">
                        <img src="/assets/icon/wx.svg" alt="微信" style="width: 1.25rem; height: 1.25rem;">
                        <span>微信支付</span>
                    </a>
                    <a href="javascript:;" data-target="qqpay" class="doc-nav-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0.75rem; border-radius: var(--radius-md); color: var(--text-main); text-decoration: none; transition: background-color 0.2s; cursor: pointer;" onclick="handlePayDocNavClick('qqpay')">
                        <img src="/assets/icon/qq.svg" alt="QQ" style="width: 1.25rem; height: 1.25rem;">
                        <span>QQ钱包</span>
                    </a>
                </nav>
            </div>
        </div>
    </div>

    <!-- 右侧内容 -->
    <div style="display: flex; flex-direction: column; gap: 2rem;">

        <!-- 易支付 -->
        <div id="epay" class="card">
            <div class="card-body">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                    <span class="iconify" data-icon="custom:epay" style="font-size: 3rem;"></span>
                    <div>
                        <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.25rem;">易支付配置</h2>
                        <p style="font-size: 0.875rem; color: var(--text-secondary);">聚合支付网关，支持支付宝/微信/QQ钱包</p>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div class="alert alert-secondary">
                        <h4 style="font-weight: 600; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                            <span class="iconify" data-icon="mdi:star"></span>
                            推荐使用
                        </h4>
                        <p style="font-size: 0.875rem;">易支付是最便捷的接入方式，只需配置一次即可同时支持支付宝、微信、QQ钱包等多种支付方式，无需企业资质。</p>
                    </div>

                    <div class="alert alert-info">
                        <h4 style="font-weight: 600; margin-bottom: 0.5rem;">接入步骤</h4>
                        <ol style="list-style-type: decimal; list-style-position: inside; margin: 0; padding-left: 0.5rem;">
                            <li>选择一个易支付服务商平台（如彩虹易支付、虎皮椒等）</li>
                            <li>注册账号并完成实名认证</li>
                            <li>进入商户后台获取商户ID和密钥</li>
                            <li>在本系统填入配置信息</li>
                        </ol>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                        <div style="background-color: var(--bg-surface-hover); padding: 1rem; border-radius: var(--radius-md);">
                            <h4 style="font-weight: 600; margin-bottom: 0.5rem;">API地址</h4>
                            <p style="font-size: 0.875rem; color: var(--text-secondary);">易支付网关地址</p>
                            <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">示例：https://pay.example.com</p>
                        </div>
                        <div style="background-color: var(--bg-surface-hover); padding: 1rem; border-radius: var(--radius-md);">
                            <h4 style="font-weight: 600; margin-bottom: 0.5rem;">商户ID (PID)</h4>
                            <p style="font-size: 0.875rem; color: var(--text-secondary);">商户后台首页可见</p>
                            <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">格式：纯数字</p>
                        </div>
                        <div style="background-color: var(--bg-surface-hover); padding: 1rem; border-radius: var(--radius-md);">
                            <h4 style="font-weight: 600; margin-bottom: 0.5rem;">商户密钥 (KEY)</h4>
                            <p style="font-size: 0.875rem; color: var(--text-secondary);">商户后台 → 商户资料</p>
                            <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">格式：32位字符串</p>
                        </div>
                    </div>

                    <div class="alert alert-warning">
                        <h4 style="font-weight: 600; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                            <span class="iconify" data-icon="tabler:alert-circle"></span>
                            回调地址配置
                        </h4>
                        <p style="font-size: 0.875rem; margin-bottom: 0.5rem;">在易支付商户后台配置以下回调地址：</p>
                        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                            <div>
                                <p style="font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 0.25rem;">异步通知地址：</p>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <code style="flex: 1; background-color: var(--bg-surface); padding: 0.5rem 0.75rem; border-radius: var(--radius-sm); font-family: monospace; cursor: pointer;"
                                        onclick="copyText('<?= $baseUrl ?>/pay/epay/notify')"
                                        title="点击复制"><?= $baseUrl ?>/pay/epay/notify</code>
                                </div>
                            </div>
                            <div>
                                <p style="font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 0.25rem;">同步跳转地址：</p>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <code style="flex: 1; background-color: var(--bg-surface); padding: 0.5rem 0.75rem; border-radius: var(--radius-sm); font-family: monospace; cursor: pointer;"
                                        onclick="copyText('<?= $baseUrl ?>/pay/epay/return')"
                                        title="点击复制"><?= $baseUrl ?>/pay/epay/return</code>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </div>

        <!-- 支付宝支付 -->
        <div id="alipay" class="card">
            <div class="card-body">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                    <img src="/assets/icon/alipay.svg" alt="支付宝" style="width: 3rem; height: 3rem;">
                    <div>
                        <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.25rem;">支付宝支付配置</h2>
                        <a href="https://open.alipay.com/" target="_blank" style="font-size: 0.875rem; color: var(--color-primary); display: flex; align-items: center; gap: 0.25rem;">
                            <span>访问支付宝开放平台</span>
                            <span class="iconify" data-icon="tabler:external-link"></span>
                        </a>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div class="alert alert-error">
                        <h4 style="font-weight: 600; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                            <span class="iconify" data-icon="tabler:info-circle"></span>
                            企业资质要求
                        </h4>
                        <p style="font-size: 0.875rem;">支付宝官方接口需要企业资质，个人用户建议使用易支付渠道。</p>
                    </div>

                    <div class="alert alert-info">
                        <h4 style="font-weight: 600; margin-bottom: 0.5rem;">申请步骤</h4>
                        <ol style="list-style-type: decimal; list-style-position: inside; margin: 0; padding-left: 0.5rem;">
                            <li>登录 <a href="https://open.alipay.com/" target="_blank" style="text-decoration: underline;">支付宝开放平台</a></li>
                            <li>进入「控制台」→「我的应用」→ 创建应用</li>
                            <li>选择「网页&移动应用」类型</li>
                            <li>在应用详情中添加「电脑网站支付」功能</li>
                            <li>配置密钥（推荐使用RSA2证书模式）</li>
                            <li>提交审核，等待上线</li>
                        </ol>
                    </div>

                    <div class="alert alert-secondary">
                        <h4 style="font-weight: 600; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                            <span class="iconify" data-icon="tabler:key"></span>
                            密钥配置说明
                        </h4>
                        <ol style="list-style-type: decimal; list-style-position: inside; margin: 0; padding-left: 0.5rem;">
                            <li>下载 <a href="https://opendocs.alipay.com/common/02kipk" target="_blank" style="text-decoration: underline;">支付宝密钥生成工具</a></li>
                            <li>选择「RSA2」算法，生成密钥对</li>
                            <li>将「应用公钥」上传到支付宝后台，获取「支付宝公钥」</li>
                            <li>将「应用私钥」和「支付宝公钥」填入本系统</li>
                        </ol>
                        <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem;">注意：填写时不需要包含 -----BEGIN/END----- 头尾标记</p>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div style="background-color: var(--bg-surface-hover); padding: 1rem; border-radius: var(--radius-md);">
                            <h4 style="font-weight: 600; margin-bottom: 0.5rem;">AppId</h4>
                            <p style="font-size: 0.875rem; color: var(--text-secondary);">应用详情页 → APPID</p>
                            <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">格式：16位数字</p>
                        </div>
                        <div style="background-color: var(--bg-surface-hover); padding: 1rem; border-radius: var(--radius-md);">
                            <h4 style="font-weight: 600; margin-bottom: 0.5rem;">应用私钥</h4>
                            <p style="font-size: 0.875rem; color: var(--text-secondary);">密钥工具生成的私钥内容</p>
                            <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">格式：RSA2私钥纯文本</p>
                        </div>
                        <div style="background-color: var(--bg-surface-hover); padding: 1rem; border-radius: var(--radius-md); grid-column: span 2;">
                            <h4 style="font-weight: 600; margin-bottom: 0.5rem;">支付宝公钥</h4>
                            <p style="font-size: 0.875rem; color: var(--text-secondary);">上传应用公钥后，平台返回的支付宝公钥</p>
                            <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">位置：开发设置 → 接口加签方式 → 查看支付宝公钥</p>
                        </div>
                    </div>

                    <div class="alert alert-success">
                        <h4 style="font-weight: 600; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                            <span class="iconify" data-icon="tabler:circle-check"></span>
                            需要开通的产品
                        </h4>
                        <ul style="list-style-type: disc; list-style-position: inside; margin: 0; padding-left: 0.5rem;">
                            <li><strong>电脑网站支付</strong> - 用于PC端收款</li>
                            <li><strong>手机网站支付</strong> - 用于移动端收款（可选）</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- 微信支付 -->
        <div id="wechat" class="card">
            <div class="card-body">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                    <img src="/assets/icon/wx.svg" alt="微信" style="width: 3rem; height: 3rem;">
                    <div>
                        <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.25rem;">微信支付配置</h2>
                        <a href="https://pay.weixin.qq.com/" target="_blank" style="font-size: 0.875rem; color: var(--color-primary); display: flex; align-items: center; gap: 0.25rem;">
                            <span>访问微信支付商户平台</span>
                            <span class="iconify" data-icon="tabler:external-link"></span>
                        </a>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div class="alert alert-error">
                        <h4 style="font-weight: 600; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                            <span class="iconify" data-icon="tabler:info-circle"></span>
                            企业资质要求
                        </h4>
                        <p style="font-size: 0.875rem;">微信支付官方接口需要企业资质并完成微信认证，个人用户建议使用易支付渠道。</p>
                    </div>

                    <div class="alert alert-warning">
                        <h4 style="font-weight: 600; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                            <span class="iconify" data-icon="tabler:alert-circle"></span>
                            关于 AppId 的说明
                        </h4>
                        <p style="font-size: 0.875rem; margin-bottom: 0.5rem;">微信支付 Native（扫码支付）<strong>必须关联 AppId</strong>，仅有商户号无法生成支付二维码。</p>
                        <p style="font-size: 0.875rem; margin-bottom: 0.5rem;">获取 AppId 的方式：</p>
                        <ul style="list-style-type: disc; list-style-position: inside; margin: 0; padding-left: 0.5rem;">
                            <li><strong>服务号</strong> - 需企业资质注册，在微信公众平台获取</li>
                            <li><strong>小程序</strong> - 个人可注册，但需认证（300元/年）后才能关联支付</li>
                            <li><strong>开放平台应用</strong> - 需企业资质，在微信开放平台创建</li>
                        </ul>
                        <p style="font-size: 0.75rem; color: var(--text-secondary); mt-2">💡 <strong>建议</strong>：没有公众号/小程序的用户，推荐使用「易支付」渠道，无需 AppId 即可收款</p>
                    </div>

                    <div class="alert alert-info">
                        <h4 style="font-weight: 600; margin-bottom: 0.5rem;">申请步骤</h4>
                        <ol style="list-style-type: decimal; list-style-position: inside; margin: 0; padding-left: 0.5rem;">
                            <li>登录 <a href="https://pay.weixin.qq.com/" target="_blank" style="text-decoration: underline;">微信支付商户平台</a></li>
                            <li>注册商户号并完成企业认证</li>
                            <li>进入「账户中心」→「API安全」设置API密钥</li>
                            <li>在「产品中心」→「AppID账号管理」关联公众号或小程序（<strong>必须</strong>）</li>
                            <li>开通「Native支付」产品</li>
                        </ol>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div style="background-color: var(--bg-surface-hover); padding: 1rem; border-radius: var(--radius-md);">
                            <h4 style="font-weight: 600; margin-bottom: 0.5rem;">AppId</h4>
                            <p style="font-size: 0.875rem; color: var(--text-secondary);">关联的公众号/小程序AppId</p>
                            <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">格式：wx开头的18位字符串</p>
                        </div>
                        <div style="background-color: var(--bg-surface-hover); padding: 1rem; border-radius: var(--radius-md);">
                            <h4 style="font-weight: 600; margin-bottom: 0.5rem;">商户号</h4>
                            <p style="font-size: 0.875rem; color: var(--text-secondary);">商户平台首页可见</p>
                            <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">格式：10位数字</p>
                        </div>
                        <div style="background-color: var(--bg-surface-hover); padding: 1rem; border-radius: var(--radius-md); grid-column: span 2;">
                            <h4 style="font-weight: 600; margin-bottom: 0.5rem;">API密钥</h4>
                            <p style="font-size: 0.875rem; color: var(--text-secondary);">账户中心 → API安全 → API密钥</p>
                            <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">格式：32位字符串（自行设置，设置后请妥善保管）</p>
                        </div>
                    </div>

                    <div class="alert alert-success">
                        <h4 style="font-weight: 600; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                            <span class="iconify" data-icon="tabler:circle-check"></span>
                            需要开通的产品
                        </h4>
                        <ul style="list-style-type: disc; list-style-position: inside; margin: 0; padding-left: 0.5rem;">
                            <li><strong>Native支付</strong> - 用于生成支付二维码（必须）</li>
                            <li><strong>H5支付</strong> - 用于移动端网页支付（可选）</li>
                        </ul>
                        <p style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 0.5rem;">产品中心 → 我的产品 → 开通对应产品</p>
                    </div>

                    <div class="alert alert-warning">
                        <h4 style="font-weight: 600; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                            <span class="iconify" data-icon="tabler:alert-circle"></span>
                            注意事项
                        </h4>
                        <ul style="list-style-type: disc; list-style-position: inside; margin: 0; padding-left: 0.5rem;">
                            <li>API密钥设置后仅显示一次，请妥善保管</li>
                            <li>需要在「开发配置」中设置支付回调域名</li>
                            <li>确保商户号状态正常且已通过微信认证</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- QQ钱包 -->
        <div id="qqpay" class="card">
            <div class="card-body">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                    <img src="/assets/icon/qq.svg" alt="QQ" style="width: 3rem; height: 3rem;">
                    <div>
                        <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.25rem;">QQ钱包配置</h2>
                        <a href="https://qpay.qq.com/" target="_blank" style="font-size: 0.875rem; color: var(--color-primary); display: flex; align-items: center; gap: 0.25rem;">
                            <span>访问QQ钱包商户平台</span>
                            <span class="iconify" data-icon="tabler:external-link"></span>
                        </a>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div class="alert alert-error">
                        <h4 style="font-weight: 600; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                            <span class="iconify" data-icon="tabler:info-circle"></span>
                            企业资质要求
                        </h4>
                        <p style="font-size: 0.875rem;">QQ钱包官方接口需要企业资质，个人用户建议使用易支付渠道。</p>
                    </div>

                    <div class="alert alert-info">
                        <h4 style="font-weight: 600; margin-bottom: 0.5rem;">申请步骤</h4>
                        <ol style="list-style-type: decimal; list-style-position: inside; margin: 0; padding-left: 0.5rem;">
                            <li>登录 <a href="https://qpay.qq.com/" target="_blank" style="text-decoration: underline;">QQ钱包商户平台</a></li>
                            <li>注册商户号并完成企业认证</li>
                            <li>进入「账户设置」→「API安全」设置API密钥</li>
                            <li>申请开通所需的支付产品</li>
                        </ol>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div style="background-color: var(--bg-surface-hover); padding: 1rem; border-radius: var(--radius-md);">
                            <h4 style="font-weight: 600; margin-bottom: 0.5rem;">商户号</h4>
                            <p style="font-size: 0.875rem; color: var(--text-secondary);">商户平台首页可见</p>
                            <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">格式：10位数字</p>
                        </div>
                        <div style="background-color: var(--bg-surface-hover); padding: 1rem; border-radius: var(--radius-md);">
                            <h4 style="font-weight: 600; margin-bottom: 0.5rem;">API密钥</h4>
                            <p style="font-size: 0.875rem; color: var(--text-secondary);">账户设置 → API安全</p>
                            <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">格式：32位字符串</p>
                        </div>
                    </div>

                    <div class="alert alert-success">
                        <h4 style="font-weight: 600; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                            <span class="iconify" data-icon="tabler:circle-check"></span>
                            需要开通的产品
                        </h4>
                        <ul style="list-style-type: disc; list-style-position: inside; margin: 0; padding-left: 0.5rem;">
                            <li><strong>付款码支付</strong> - 线下扫码支付</li>
                            <li><strong>原生支付</strong> - 生成二维码支付</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- 渠道选择建议 -->
        <div class="card">
            <div class="card-body">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                    <div style="width: 3rem; height: 3rem; background: linear-gradient(135deg, #fbbf24, #f59e0b); border-radius: 0.75rem; display: flex; align-items: center; justify-content: center;">
                        <span class="iconify" data-icon="tabler:bulb" style="color: white; font-size: 1.75rem;"></span>
                    </div>
                    <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.25rem;">渠道选择建议</h2>
                </div>

                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>场景</th>
                                <th>推荐渠道</th>
                                <th>说明</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>个人开发者</td>
                                <td><span class="badge badge-purple">易支付</span></td>
                                <td>无需企业资质，接入简单</td>
                            </tr>
                            <tr>
                                <td>小微企业</td>
                                <td><span class="badge badge-purple">易支付</span></td>
                                <td>成本低，维护简单</td>
                            </tr>
                            <tr>
                                <td>中大型企业</td>
                                <td><span class="badge badge-blue">官方接口</span></td>
                                <td>费率更低，资金更安全</td>
                            </tr>
                            <tr>
                                <td>高并发场景</td>
                                <td><span class="badge badge-blue">官方接口</span></td>
                                <td>稳定性更高，无并发限制</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
    .doc-nav-item:hover {
        background-color: var(--bg-surface-hover);
    }
    .doc-nav-item.active {
        background-color: rgba(99, 102, 241, 0.1);
        color: var(--color-primary);
        font-weight: 500;
    }
</style>

<script>
// 获取滚动容器
function getPayDocScrollContainer() {
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

// 支付文档导航点击处理
window.handlePayDocNavClick = function(targetId) {
    var target = document.getElementById(targetId);
    if (!target) return;
    
    // 更新激活状态
    var navItems = document.querySelectorAll('#payDocNav .doc-nav-item');
    navItems.forEach(function(item) {
        item.classList.remove('active');
        if (item.getAttribute('data-target') === targetId) {
            item.classList.add('active');
        }
    });
    
    // 计算目标位置并滚动
    var rect = target.getBoundingClientRect();
    var offset = 100;
    var container = getPayDocScrollContainer();
    
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
        var docNav = document.getElementById('payDocNav');
        if (!docNav) return;
        
        var navItems = docNav.querySelectorAll('.doc-nav-item');
        var sections = document.querySelectorAll('.card[id]');
        if (!sections.length) return;
        
        var activeId = null;
        var viewportTop = 160;
        
        sections.forEach(function(section) {
            var rect = section.getBoundingClientRect();
            if (rect.top <= viewportTop) {
                activeId = section.id;
            }
        });
        
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
    
    var mainContent = document.getElementById('main-content');
    var pageBody = document.querySelector('.page-body');
    var adminContent = document.querySelector('.admin-content');
    
    [window, mainContent, pageBody, adminContent].forEach(function(el) {
        if (el) {
            el.addEventListener('scroll', updateActiveNav, { passive: true });
        }
    });
    
    updateActiveNav();
    setTimeout(updateActiveNav, 500);
})();
</script>

<?php $content = ob_get_clean(); ?>
<?php include ML_ROOT . '/views/layouts/admin.php'; ?>