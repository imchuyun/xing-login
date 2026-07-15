<?php $pageTitle = '系统设置';
ob_start(); ?>

<!-- 顶部提示 -->
<div class="alert alert-info" style="margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between;">
    <div style="display: flex; align-items: center; gap: 0.75rem;">
        <span class="iconify" data-icon="tabler:info-circle" style="font-size: 1.25rem;"></span>
        <span style="font-size: 0.875rem;">首次配置？查看详细的支付接入文档获取帮助</span>
    </div>
    <a href="<?= admin_url('settings/payment/docs') ?>" class="btn btn-primary btn-sm" style="display: flex; align-items: center; gap: 0.5rem; text-decoration: none;">
        <span class="iconify" data-icon="tabler:book"></span>
        <span>配置文档</span>
    </a>
</div>

<div style="display: flex; flex-direction: column; gap: 1.5rem;">
    <!-- 易支付配置 -->
    <div class="card">
        <div class="card-header">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <span class="iconify" data-icon="custom:epay" style="font-size: 2.5rem;"></span>
                <div>
                    <h3 class="card-title">易支付</h3>
                    <p style="font-size: 0.75rem; color: var(--text-secondary); margin: 0;">配置易支付网关，可用于支付宝、微信、QQ钱包</p>
                </div>
            </div>
            <label class="switch">
                <input type="checkbox" id="epayEnabled" <?= ($settings['pay_epay_enabled'] ?? '0') == '1' ? 'checked' : '' ?>>
                <span class="slider"></span>
            </label>
        </div>
        <div class="card-body">
            <form id="epayForm">
                <input type="hidden" name="_token" value="<?= e($csrf_token) ?>">
                <input type="hidden" name="settings[pay_epay_enabled]" id="epayEnabledValue" value="<?= ($settings['pay_epay_enabled'] ?? '0') ?>">

                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">API地址</label>
                        <input type="text" name="settings[pay_epay_api_url]" value="<?= e($settings['pay_epay_api_url'] ?? '') ?>"
                            class="form-control" placeholder="易支付API地址">
                    </div>
                    <div class="form-group">
                        <label class="form-label">商户ID (PID)</label>
                        <input type="text" name="settings[pay_epay_pid]" value="<?= e($settings['pay_epay_pid'] ?? '') ?>"
                            class="form-control" placeholder="商户ID">
                    </div>
                    <div class="form-group">
                        <label class="form-label">商户密钥 (KEY)</label>
                        <input type="password" name="settings[pay_epay_key]" value="<?= e($settings['pay_epay_key'] ?? '') ?>"
                            class="form-control" placeholder="商户密钥">
                    </div>
                </div>
                <div style="margin-top: 1rem;">
                    <button type="submit" class="btn btn-primary" style="background-color: #a855f7; border-color: #a855f7;">保存配置</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 支付宝 -->
    <div class="card">
        <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <img src="/assets/icon/alipay.svg" alt="支付宝" style="width: 2.5rem; height: 2.5rem;">
                <div>
                    <h3 class="card-title">支付宝支付</h3>
                    <p style="font-size: 0.75rem; color: var(--text-secondary); margin: 0;">支持官方接口或易支付通道</p>
                </div>
            </div>
            <label class="switch">
                <input type="checkbox" id="alipayEnabled" <?= ($settings['pay_alipay_enabled'] ?? '0') == '1' ? 'checked' : '' ?>>
                <span class="slider"></span>
            </label>
        </div>
        <div class="card-body">
            <!-- 支付渠道选择 - 圆点单选框 -->
            <div style="display: flex; gap: 1.5rem; padding: 0.75rem 0; margin-bottom: 1rem;">
                <label class="radio-option" style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="radio" name="alipay_channel_radio" value="epay" <?= ($settings['pay_alipay_channel'] ?? 'epay') == 'epay' ? 'checked' : '' ?> style="width: 1rem; height: 1rem; accent-color: var(--color-primary);">
                    <span style="font-size: 0.875rem;">易支付</span>
                </label>
                <label class="radio-option" style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="radio" name="alipay_channel_radio" value="official" <?= ($settings['pay_alipay_channel'] ?? 'epay') == 'official' ? 'checked' : '' ?> style="width: 1rem; height: 1rem; accent-color: var(--color-primary);">
                    <span style="font-size: 0.875rem;">官方接口</span>
                </label>
            </div>
            
            <form id="alipayForm">
                <input type="hidden" name="_token" value="<?= e($csrf_token) ?>">
                <input type="hidden" name="settings[pay_alipay_enabled]" id="alipayEnabledValue" value="<?= ($settings['pay_alipay_enabled'] ?? '0') ?>">
                <input type="hidden" name="settings[pay_alipay_channel]" id="alipayChannelValue" value="<?= ($settings['pay_alipay_channel'] ?? 'epay') ?>">

                <div id="alipayOfficialConfig" style="display: <?= ($settings['pay_alipay_channel'] ?? 'epay') == 'official' ? 'grid' : 'none' ?>; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">AppId</label>
                        <input type="text" name="settings[pay_alipay_app_id]" value="<?= e($settings['pay_alipay_app_id'] ?? '') ?>"
                            class="form-control" placeholder="支付宝应用AppId">
                    </div>
                    <div class="form-group">
                        <label class="form-label">应用私钥</label>
                        <input type="password" name="settings[pay_alipay_private_key]" value="<?= e($settings['pay_alipay_private_key'] ?? '') ?>"
                            class="form-control" placeholder="RSA2私钥">
                    </div>
                    <div class="form-group" style="grid-column: span 2;">
                        <label class="form-label">支付宝公钥</label>
                        <input type="password" name="settings[pay_alipay_public_key]" value="<?= e($settings['pay_alipay_public_key'] ?? '') ?>"
                            class="form-control" placeholder="支付宝公钥">
                    </div>
                    <div style="grid-column: span 2;">
                        <button type="submit" class="btn btn-primary" style="background-color: #3b82f6; border-color: #3b82f6;">保存配置</button>
                    </div>
                </div>
                <div id="alipayEpayConfig" style="display: <?= ($settings['pay_alipay_channel'] ?? 'epay') == 'epay' ? 'block' : 'none' ?>;">
                    <div class="alert alert-secondary">
                        <p style="font-size: 0.875rem; margin: 0;"><span class="iconify" data-icon="tabler:info-circle" style="vertical-align: middle; margin-right: 0.25rem;"></span>使用上方易支付配置，无需额外配置</p>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- 微信支付 -->
    <div class="card">
        <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <img src="/assets/icon/wx.svg" alt="微信" style="width: 2.5rem; height: 2.5rem;">
                <div>
                    <h3 class="card-title">微信支付</h3>
                    <p style="font-size: 0.75rem; color: var(--text-secondary); margin: 0;">支持官方接口或易支付通道</p>
                </div>
            </div>
            <label class="switch">
                <input type="checkbox" id="wechatEnabled" <?= ($settings['pay_wechat_enabled'] ?? '0') == '1' ? 'checked' : '' ?>>
                <span class="slider"></span>
            </label>
        </div>
        <div class="card-body">
            <!-- 支付渠道选择 - 圆点单选框 -->
            <div style="display: flex; gap: 1.5rem; padding: 0.75rem 0; margin-bottom: 1rem;">
                <label class="radio-option" style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="radio" name="wechat_channel_radio" value="epay" <?= ($settings['pay_wechat_channel'] ?? 'epay') == 'epay' ? 'checked' : '' ?> style="width: 1rem; height: 1rem; accent-color: var(--color-primary);">
                    <span style="font-size: 0.875rem;">易支付</span>
                </label>
                <label class="radio-option" style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="radio" name="wechat_channel_radio" value="official" <?= ($settings['pay_wechat_channel'] ?? 'epay') == 'official' ? 'checked' : '' ?> style="width: 1rem; height: 1rem; accent-color: var(--color-primary);">
                    <span style="font-size: 0.875rem;">官方接口</span>
                </label>
            </div>
            
            <form id="wechatForm">
                <input type="hidden" name="_token" value="<?= e($csrf_token) ?>">
                <input type="hidden" name="settings[pay_wechat_enabled]" id="wechatEnabledValue" value="<?= ($settings['pay_wechat_enabled'] ?? '0') ?>">
                <input type="hidden" name="settings[pay_wechat_channel]" id="wechatChannelValue" value="<?= ($settings['pay_wechat_channel'] ?? 'epay') ?>">

                <div id="wechatOfficialConfig" style="display: <?= ($settings['pay_wechat_channel'] ?? 'epay') == 'official' ? 'grid' : 'none' ?>; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">AppId</label>
                        <input type="text" name="settings[pay_wechat_app_id]" value="<?= e($settings['pay_wechat_app_id'] ?? '') ?>"
                            class="form-control" placeholder="微信AppId">
                    </div>
                    <div class="form-group">
                        <label class="form-label">商户号</label>
                        <input type="text" name="settings[pay_wechat_mch_id]" value="<?= e($settings['pay_wechat_mch_id'] ?? '') ?>"
                            class="form-control" placeholder="微信支付商户号">
                    </div>
                    <div class="form-group" style="grid-column: span 2;">
                        <label class="form-label">API密钥</label>
                        <input type="password" name="settings[pay_wechat_api_key]" value="<?= e($settings['pay_wechat_api_key'] ?? '') ?>"
                            class="form-control" placeholder="微信支付API密钥">
                    </div>
                    <div style="grid-column: span 2;">
                        <button type="submit" class="btn btn-primary" style="background-color: #22c55e; border-color: #22c55e;">保存配置</button>
                    </div>
                </div>
                <div id="wechatEpayConfig" style="display: <?= ($settings['pay_wechat_channel'] ?? 'epay') == 'epay' ? 'block' : 'none' ?>;">
                    <div class="alert alert-secondary">
                        <p style="font-size: 0.875rem; margin: 0;"><span class="iconify" data-icon="tabler:info-circle" style="vertical-align: middle; margin-right: 0.25rem;"></span>使用上方易支付配置，无需额外配置</p>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- QQ钱包 -->
    <div class="card">
        <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <img src="/assets/icon/qq.svg" alt="QQ" style="width: 2.5rem; height: 2.5rem;">
                <div>
                    <h3 class="card-title">QQ钱包</h3>
                    <p style="font-size: 0.75rem; color: var(--text-secondary); margin: 0;">支持官方接口或易支付通道</p>
                </div>
            </div>
            <label class="switch">
                <input type="checkbox" id="qqpayEnabled" <?= ($settings['pay_qqpay_enabled'] ?? '0') == '1' ? 'checked' : '' ?>>
                <span class="slider"></span>
            </label>
        </div>
        <div class="card-body">
            <!-- 支付渠道选择 - 圆点单选框 -->
            <div style="display: flex; gap: 1.5rem; padding: 0.75rem 0; margin-bottom: 1rem;">
                <label class="radio-option" style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="radio" name="qqpay_channel_radio" value="epay" <?= ($settings['pay_qqpay_channel'] ?? 'epay') == 'epay' ? 'checked' : '' ?> style="width: 1rem; height: 1rem; accent-color: var(--color-primary);">
                    <span style="font-size: 0.875rem;">易支付</span>
                </label>
                <label class="radio-option" style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="radio" name="qqpay_channel_radio" value="official" <?= ($settings['pay_qqpay_channel'] ?? 'epay') == 'official' ? 'checked' : '' ?> style="width: 1rem; height: 1rem; accent-color: var(--color-primary);">
                    <span style="font-size: 0.875rem;">官方接口</span>
                </label>
            </div>
            
            <form id="qqpayForm">
                <input type="hidden" name="_token" value="<?= e($csrf_token) ?>">
                <input type="hidden" name="settings[pay_qqpay_enabled]" id="qqpayEnabledValue" value="<?= ($settings['pay_qqpay_enabled'] ?? '0') ?>">
                <input type="hidden" name="settings[pay_qqpay_channel]" id="qqpayChannelValue" value="<?= ($settings['pay_qqpay_channel'] ?? 'epay') ?>">

                <div id="qqpayOfficialConfig" style="display: <?= ($settings['pay_qqpay_channel'] ?? 'epay') == 'official' ? 'grid' : 'none' ?>; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">商户号</label>
                        <input type="text" name="settings[pay_qqpay_mch_id]" value="<?= e($settings['pay_qqpay_mch_id'] ?? '') ?>"
                            class="form-control" placeholder="QQ钱包商户号">
                    </div>
                    <div class="form-group">
                        <label class="form-label">API密钥</label>
                        <input type="password" name="settings[pay_qqpay_api_key]" value="<?= e($settings['pay_qqpay_api_key'] ?? '') ?>"
                            class="form-control" placeholder="QQ钱包API密钥">
                    </div>
                    <div style="grid-column: span 2;">
                        <button type="submit" class="btn btn-primary" style="background-color: #06b6d4; border-color: #06b6d4;">保存配置</button>
                    </div>
                </div>
                <div id="qqpayEpayConfig" style="display: <?= ($settings['pay_qqpay_channel'] ?? 'epay') == 'epay' ? 'block' : 'none' ?>;">
                    <div class="alert alert-secondary">
                        <p style="font-size: 0.875rem; margin: 0;"><span class="iconify" data-icon="tabler:info-circle" style="vertical-align: middle; margin-right: 0.25rem;"></span>使用上方易支付配置，无需额外配置</p>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function() {
    var csrfToken = '<?= e($csrf_token) ?>';
    var updateUrl = '<?= admin_url("settings/payment/update") ?>';
    
    // 保存设置的通用函数
    function saveSetting(key, value, successMsg) {
        var formData = new FormData();
        formData.append('_token', csrfToken);
        formData.append('settings[' + key + ']', value);
        
        fetch(updateUrl, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.code === 0) {
                toast(successMsg, 'success');
            } else {
                toast(data.message || '保存失败', 'error');
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
            toast('请求失败，请重试', 'error');
        });
    }
    
    // 易支付开关
    document.getElementById('epayEnabled').addEventListener('change', function() {
        document.getElementById('epayEnabledValue').value = this.checked ? '1' : '0';
        saveSetting('pay_epay_enabled', this.checked ? '1' : '0', '易支付' + (this.checked ? '已开启' : '已关闭'));
    });
    
    // 支付宝开关
    document.getElementById('alipayEnabled').addEventListener('change', function() {
        document.getElementById('alipayEnabledValue').value = this.checked ? '1' : '0';
        saveSetting('pay_alipay_enabled', this.checked ? '1' : '0', '支付宝支付' + (this.checked ? '已开启' : '已关闭'));
    });
    
    // 支付宝渠道单选框
    document.querySelectorAll('input[name="alipay_channel_radio"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            var channel = this.value;
            document.getElementById('alipayChannelValue').value = channel;
            document.getElementById('alipayOfficialConfig').style.display = channel === 'official' ? 'grid' : 'none';
            document.getElementById('alipayEpayConfig').style.display = channel === 'epay' ? 'block' : 'none';
            saveSetting('pay_alipay_channel', channel, '支付宝已切换到' + (channel === 'official' ? '官方接口' : '易支付'));
        });
    });
    
    // 微信支付开关
    document.getElementById('wechatEnabled').addEventListener('change', function() {
        document.getElementById('wechatEnabledValue').value = this.checked ? '1' : '0';
        saveSetting('pay_wechat_enabled', this.checked ? '1' : '0', '微信支付' + (this.checked ? '已开启' : '已关闭'));
    });
    
    // 微信渠道单选框
    document.querySelectorAll('input[name="wechat_channel_radio"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            var channel = this.value;
            document.getElementById('wechatChannelValue').value = channel;
            document.getElementById('wechatOfficialConfig').style.display = channel === 'official' ? 'grid' : 'none';
            document.getElementById('wechatEpayConfig').style.display = channel === 'epay' ? 'block' : 'none';
            saveSetting('pay_wechat_channel', channel, '微信支付已切换到' + (channel === 'official' ? '官方接口' : '易支付'));
        });
    });
    
    // QQ钱包开关
    document.getElementById('qqpayEnabled').addEventListener('change', function() {
        document.getElementById('qqpayEnabledValue').value = this.checked ? '1' : '0';
        saveSetting('pay_qqpay_enabled', this.checked ? '1' : '0', 'QQ钱包' + (this.checked ? '已开启' : '已关闭'));
    });
    
    // QQ钱包渠道单选框
    document.querySelectorAll('input[name="qqpay_channel_radio"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            var channel = this.value;
            document.getElementById('qqpayChannelValue').value = channel;
            document.getElementById('qqpayOfficialConfig').style.display = channel === 'official' ? 'grid' : 'none';
            document.getElementById('qqpayEpayConfig').style.display = channel === 'epay' ? 'block' : 'none';
            saveSetting('pay_qqpay_channel', channel, 'QQ钱包已切换到' + (channel === 'official' ? '官方接口' : '易支付'));
        });
    });
    
    // 表单提交处理
    var payFormNames = {
        epayForm: '易支付',
        alipayForm: '支付宝',
        wechatForm: '微信支付',
        qqpayForm: 'QQ钱包'
    };
    
    ['epayForm', 'alipayForm', 'wechatForm', 'qqpayForm'].forEach(function(formId) {
        var form = document.getElementById(formId);
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                var moduleName = payFormNames[formId];
                var formData = new FormData(this);
                
                fetch(updateUrl, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    toast(data.code === 0 ? moduleName + '配置已保存' : data.message, data.code === 0 ? 'success' : 'error');
                })
                .catch(function(error) {
                    console.error('Error:', error);
                    toast('请求失败，请重试', 'error');
                });
            });
        }
    });
})();
</script>

<?php $settingsContent = ob_get_clean(); ?>
<?php ob_start(); ?>
<?php include ML_ROOT . '/views/layouts/admin_settings.php'; ?>
<?php $content = ob_get_clean(); ?>
<?php include ML_ROOT . '/views/layouts/admin.php'; ?>
