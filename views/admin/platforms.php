<?php
$pageTitle = '平台配置';
ob_start();

$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
$platformConfigs = [
    'qq' => [
        'doc_url' => 'https://connect.qq.com/',
        'id_label' => 'APP ID',
        'id_placeholder' => 'QQ互联平台的应用ID',
        'secret_label' => 'APP Key',
        'secret_placeholder' => 'QQ互联平台的应用密钥',
        'tips' => '前往QQ互联 → 应用管理 获取APP ID和APP Key',
    ],
    'wx' => [
        'doc_url' => 'https://open.weixin.qq.com/',
        'id_label' => 'AppID',
        'id_placeholder' => '微信开放平台或公众号的AppID',
        'secret_label' => 'AppSecret',
        'secret_placeholder' => '微信开放平台应用密钥或公众号AppSecret',
        'tips' => '开放平台扫码登录使用微信开放平台网站应用；订阅号登录复用这里填写的公众号AppID和AppSecret，公众号后台服务器地址填写下方提示的回调地址。',
    ],
    'alipay' => [
        'doc_url' => 'https://open.alipay.com/',
        'id_label' => 'APPID',
        'id_placeholder' => '支付宝开放平台的应用APPID',
        'secret_label' => '应用私钥',
        'secret_placeholder' => 'RSA2私钥（需在支付宝配置公钥）',
        'tips' => '前往支付宝开放平台 → 开发者中心 → 我的应用，注意配置RSA2密钥',
    ],
    'sina' => [
        'doc_url' => 'https://open.weibo.com/',
        'id_label' => 'App Key',
        'id_placeholder' => '微博开放平台的App Key',
        'secret_label' => 'App Secret',
        'secret_placeholder' => '微博开放平台的App Secret',
        'tips' => '前往微博开放平台 → 我的应用 获取App Key',
    ],
    'baidu' => [
        'doc_url' => 'https://developer.baidu.com/',
        'id_label' => 'API Key',
        'id_placeholder' => '百度开发者中心的API Key',
        'secret_label' => 'Secret Key',
        'secret_placeholder' => '百度开发者中心的Secret Key',
        'tips' => '前往百度开发者中心 → 控制台 → 创建应用',
    ],
    'douyin' => [
        'doc_url' => 'https://open.douyin.com/',
        'id_label' => 'Client Key',
        'id_placeholder' => '抖音开放平台的Client Key',
        'secret_label' => 'Client Secret',
        'secret_placeholder' => '抖音开放平台的Client Secret',
        'tips' => '前往抖音开放平台 → 开发者后台 → 创建应用',
    ],
    'huawei' => [
        'doc_url' => 'https://developer.huawei.com/',
        'id_label' => 'Client ID',
        'id_placeholder' => '华为AppGallery Connect的OAuth客户端ID',
        'secret_label' => 'Client Secret',
        'secret_placeholder' => '华为AppGallery Connect的客户端密钥',
        'tips' => '前往华为开发者联盟 → AppGallery Connect → 我的项目 → 开通华为帐号服务',
    ],
    'google' => [
        'doc_url' => 'https://console.cloud.google.com/apis/credentials',
        'id_label' => 'Client ID',
        'id_placeholder' => '格式：xxxxx.apps.googleusercontent.com',
        'secret_label' => 'Client Secret',
        'secret_placeholder' => 'OAuth 2.0 客户端密钥',
        'tips' => '前往 Google Cloud Console → APIs & Services → Credentials 创建OAuth 2.0客户端',
    ],
    'microsoft' => [
        'doc_url' => 'https://portal.azure.com/',
        'id_label' => 'Client ID',
        'id_placeholder' => 'Azure应用程序(客户端)ID',
        'secret_label' => 'Client Secret',
        'secret_placeholder' => 'Azure客户端密码',
        'tips' => '前往 Azure Portal → Azure Active Directory → 应用注册 创建应用',
    ],
    'wework' => [
        'doc_url' => 'https://work.weixin.qq.com/',
        'id_label' => 'CorpID',
        'id_placeholder' => '企业微信的企业ID',
        'secret_label' => 'Secret',
        'secret_placeholder' => '企业微信应用的Secret',
        'tips' => '前往企业微信管理后台 → 应用管理 → 自建应用 获取凭证，AgentID填写在下方Scope字段中',
        'scope_label' => 'AgentID',
        'scope_placeholder' => '企业微信自建应用的AgentID',
        'show_scope' => true,
    ],
    'dingtalk' => [
        'doc_url' => 'https://open.dingtalk.com/',
        'id_label' => 'AppKey / Client ID',
        'id_placeholder' => '钉钉开放平台的AppKey或Client ID',
        'secret_label' => 'AppSecret / Client Secret',
        'secret_placeholder' => '钉钉开放平台的AppSecret或Client Secret',
        'tips' => '前往钉钉开放平台 → 应用开发 → 企业内部应用 创建H5微应用',
    ],
    'gitee' => [
        'doc_url' => 'https://gitee.com/oauth/applications',
        'id_label' => 'Client ID',
        'id_placeholder' => 'Gitee第三方应用的Client ID',
        'secret_label' => 'Client Secret',
        'secret_placeholder' => 'Gitee第三方应用的Client Secret',
        'tips' => '前往 Gitee → 设置 → 第三方应用 创建应用',
    ],
    'github' => [
        'doc_url' => 'https://github.com/settings/developers',
        'id_label' => 'Client ID',
        'id_placeholder' => '在GitHub OAuth Apps中获取',
        'secret_label' => 'Client Secret',
        'secret_placeholder' => '创建OAuth App时生成的密钥',
        'tips' => '前往 GitHub Settings → Developer settings → OAuth Apps 创建应用',
    ],
    'feishu' => [
        'doc_url' => 'https://open.feishu.cn/',
        'id_label' => 'App ID',
        'id_placeholder' => '飞书开放平台的App ID',
        'secret_label' => 'App Secret',
        'secret_placeholder' => '飞书开放平台的App Secret',
        'tips' => '前往飞书开放平台 → 开发者后台 → 创建企业自建应用',
    ],
    'bilibili' => [
        'doc_url' => 'https://open.bilibili.com/',
        'id_label' => 'Client ID',
        'id_placeholder' => '哔哩哔哩开放平台的Client ID',
        'secret_label' => 'Client Secret',
        'secret_placeholder' => '哔哩哔哩开放平台的Client Secret',
        'tips' => '前往哔哩哔哩开放平台 → 开发者中心 → 创建应用',
    ],
    'xiaomi' => [
        'doc_url' => 'https://dev.mi.com/',
        'id_label' => 'App ID',
        'id_placeholder' => '小米开放平台的App ID',
        'secret_label' => 'App Secret',
        'secret_placeholder' => '小米开放平台的App Secret',
        'tips' => '前往小米开放平台 → 管理中心 → 帐号服务 → 创建网站应用',
    ],
];
$defaultConfig = [
    'doc_url' => '',
    'id_label' => 'App ID',
    'id_placeholder' => '请输入第三方平台的App ID',
    'secret_label' => 'App Secret',
    'secret_placeholder' => '请输入第三方平台的密钥',
    'tips' => '',
];
?>

<div class="alert alert-info mb-6" style="display: flex; align-items: center; justify-content: space-between;">
    <div style="display: flex; gap: 0.75rem;">
        <span class="iconify" data-icon="tabler:info-circle" style="font-size: 1.25rem; margin-top: 0.125rem;"></span>
        <div>
            <strong>重要：</strong> 在此配置企业级的第三方登录凭证。配置后，所有注册用户创建的应用都将使用这些凭证进行OAuth认证。
        </div>
    </div>
    <a href="<?= admin_url('platforms/docs') ?>" class="btn btn-sm btn-primary" style="display: flex; align-items: center; gap: 0.5rem; white-space: nowrap; flex-shrink: 0;">
        <span class="iconify" data-icon="tabler:book"></span>
        <span>平台申请指南</span>
    </a>
</div>

<div style="display: grid; gap: 1.5rem;">
    <?php foreach ($platforms as $platform):
        $config = $platformConfigs[$platform['name']] ?? $defaultConfig;
        $wxScopeConfig = $platform['name'] === 'wx'
            ? \App\Services\WechatOfficialAccountService::parseScopeConfig($platform['scope'] ?? '')
            : [];
    ?>
        <div style="padding: 1.5rem; border-bottom: 1px solid var(--border-color);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <img src="/assets/icon/<?= e($platform['name']) ?>.svg" alt="<?= e($platform['platform']) ?>" style="width: 3rem; height: 3rem;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <h3 style="font-size: 1.125rem; font-weight: 600; color: var(--text-main); margin: 0;"><?= e($platform['platform']) ?></h3>
                            <?php if (!empty($config['doc_url'])): ?>
                                <a href="<?= $config['doc_url'] ?>" target="_blank" style="font-size: 0.875rem; color: var(--color-primary); display: flex; align-items: center; gap: 0.25rem;">
                                    <span class="iconify" data-icon="tabler:external-link"></span>
                                    开放平台
                                </a>
                            <?php endif; ?>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.25rem;">
                            <span style="font-size: 0.75rem; color: var(--text-muted);">回调地址:</span>
                            <code style="font-size: 0.75rem; background-color: var(--bg-surface-hover); padding: 0.125rem 0.375rem; border-radius: 0.25rem; cursor: pointer;"
                                onclick="copyText('<?= $baseUrl ?>/oauth/<?= e($platform['name']) ?>/callback')"
                                title="点击复制"><?= $baseUrl ?>/oauth/<?= e($platform['name']) ?>/callback</code>
                        </div>
                        <?php if ($platform['name'] === 'wx'): ?>
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.25rem;">
                            <span style="font-size: 0.75rem; color: var(--text-muted);">公众号服务器:</span>
                            <code style="font-size: 0.75rem; background-color: var(--bg-surface-hover); padding: 0.125rem 0.375rem; border-radius: 0.25rem; cursor: pointer;"
                                onclick="copyText('<?= $baseUrl ?>/wechat/mp/callback')"
                                title="点击复制"><?= $baseUrl ?>/wechat/mp/callback</code>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <label class="switch">
                    <input type="checkbox" class="platform-toggle" data-id="<?= $platform['id'] ?>" data-name="<?= e($platform['platform']) ?>" <?= $platform['status'] ? 'checked' : '' ?>>
                    <span class="slider round"></span>
                </label>
            </div>

            <?php if (!empty($config['tips'])): ?>
                <div class="alert alert-warning mb-4" style="padding: 0.75rem 1rem; font-size: 0.875rem;">
                    <div style="display: flex; gap: 0.5rem;">
                        <span class="iconify" data-icon="tabler:bulb" style="margin-top: 0.125rem;"></span>
                        <?= $config['tips'] ?>
                    </div>
                </div>
            <?php endif; ?>

            <form class="platformForm" data-id="<?= $platform['id'] ?>" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                <input type="hidden" name="_token" value="<?= e($csrf_token) ?>">
                <input type="hidden" name="id" value="<?= $platform['id'] ?>">

                <div class="form-group">
                    <label class="form-label"><?= $config['id_label'] ?></label>
                    <input type="text" name="app_id" value="<?= e($platform['app_id']) ?>"
                        class="form-control"
                        placeholder="<?= $config['id_placeholder'] ?>">
                </div>

                <?php if ($platform['name'] === 'wx'): ?>
                <div class="form-group">
                    <label class="form-label">微信登录方式</label>
                    <select name="wx_login_mode" class="form-control">
                        <option value="open_platform" <?= (($wxScopeConfig['login_mode'] ?? 'open_platform') === 'open_platform') ? 'selected' : '' ?>>开放平台扫码登录</option>
                        <option value="mp_subscribe" <?= (($wxScopeConfig['login_mode'] ?? '') === 'mp_subscribe') ? 'selected' : '' ?>>订阅号验证码登录</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">公众号服务器 Token</label>
                    <input type="text" name="wechat_mp_token" value="<?= e($wxScopeConfig['mp_token'] ?? '') ?>"
                        class="form-control"
                        placeholder="与公众号后台服务器配置 Token 保持一致">
                </div>
                <?php endif; ?>

                <?php if (!empty($config['show_scope'])): ?>
                <div class="form-group">
                    <label class="form-label"><?= $config['scope_label'] ?? 'Scope' ?></label>
                    <input type="text" name="scope" value="<?= e($platform['scope'] ?? '') ?>"
                        class="form-control"
                        placeholder="<?= $config['scope_placeholder'] ?? '授权范围' ?>">
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label class="form-label"><?= $config['secret_label'] ?></label>
                    <div style="position: relative;">
                        <input type="password" name="app_secret" id="secret_<?= $platform['id'] ?>"
                            value="<?= !empty($platform['app_secret']) ? '••••••••••••••••' : '' ?>"
                            class="form-control"
                            placeholder="<?= $config['secret_placeholder'] ?><?= !empty($platform['app_secret']) ? '（已配置，留空则不修改）' : '' ?>"
                            onfocus="if(this.value==='••••••••••••••••')this.value=''">
                        <?php if (!empty($platform['app_secret'])): ?>
                            <span style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); font-size: 0.75rem; color: var(--color-success); background-color: rgba(var(--success-rgb), 0.1); padding: 0.125rem 0.375rem; border-radius: 0.25rem;">已配置</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div style="grid-column: 1 / -1; display: flex; justify-content: space-between; align-items: center; margin-top: 0.5rem;">
                    <button type="button" data-platform="<?= e($platform['name']) ?>" onclick="testPlatform(this)"
                        class="btn btn-outline btn-sm" style="display: flex; align-items: center; gap: 0.5rem;">
                        <span class="iconify" data-icon="tabler:flask"></span>
                        测试回调
                    </button>
                    <button type="submit" class="btn btn-primary">
                        保存配置
                    </button>
                </div>
            </form>
        </div>
    <?php endforeach; ?>
</div>

<style>
    
    .switch {
        position: relative;
        display: inline-block;
        width: 44px;
        height: 24px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: var(--bg-surface-hover);
        border: 1px solid var(--border-color);
        transition: .4s;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 2px;
        bottom: 2px;
        background-color: white;
        transition: .4s;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    input:checked+.slider {
        background-color: var(--color-primary);
        border-color: var(--color-primary);
    }

    input:focus+.slider {
        box-shadow: 0 0 1px var(--color-primary);
    }

    input:checked+.slider:before {
        transform: translateX(20px);
    }

    .slider.round {
        border-radius: 24px;
    }

    .slider.round:before {
        border-radius: 50%;
    }
</style>

<script>
    window.testPlatform = function(btn) {
        var platform = btn.dataset.platform;
        var card = btn.closest('.card');
        var form = card.querySelector('.platformForm');
        var toggle = card.querySelector('.platform-toggle');

        if (!toggle || !toggle.checked) {
            toast('请先启用该平台', 'warning');
            return;
        }

        var appId = form.querySelector('input[name="app_id"]').value;
        if (!appId || appId.trim() === '') {
            toast('请先配置并保存App ID', 'warning');
            return;
        }

        var testUrl = '/oauth/' + platform + '?test=1';
        var width = 600;
        var height = 700;
        var left = (screen.width - width) / 2;
        var top = (screen.height - height) / 2;

        var testWindow = window.open(
            testUrl,
            'oauth_test_' + platform,
            'width=' + width + ',height=' + height + ',left=' + left + ',top=' + top + ',scrollbars=yes,resizable=yes'
        );

        if (testWindow) {
            toast('已打开测试窗口，请在新窗口中完成授权', 'info');
        } else {
            toast('无法打开测试窗口，请检查浏览器是否阻止了弹窗', 'error');
        }
    };
    
    // 使用 IIFE 避免重复绑定事件
    (function() {
        document.querySelectorAll('.platform-toggle').forEach(toggle => {
            toggle.addEventListener('change', function() {
                const id = this.dataset.id;
                const name = this.dataset.name;
                const status = this.checked ? 1 : 0;
                const statusText = this.checked ? '已开启' : '已关闭';
                const toastType = this.checked ? 'success' : 'error';
                ajax('<?= admin_url('platforms/update') ?>', {
                    _token: '<?= e($csrf_token) ?>',
                    id: id,
                    status: status
                }, function(data) {
                    if (data.code === 0) {
                        toast(name + '登录' + statusText, toastType);
                    } else {
                        toast(data.message, 'error');
                    }
                });
            });
        });
        
        document.querySelectorAll('.platformForm').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const id = formData.get('id');
                const toggle = document.querySelector(`.platform-toggle[data-id="${id}"]`);
                formData.set('status', toggle.checked ? '1' : '0');

                ajax('<?= admin_url('platforms/update') ?>', Object.fromEntries(formData), function(data) {
                    toast(data.message, data.code === 0 ? 'success' : 'error');
                });
            });
        });
    })();
</script>

<?php $content = ob_get_clean(); ?>
<?php include ML_ROOT . '/views/layouts/admin.php'; ?>
