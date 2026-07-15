<?php $pageTitle = '系统设置';
ob_start(); ?>

<div style="display: flex; flex-direction: column; gap: 1.5rem;">
    <!-- 邮件配置 -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">邮件配置</h3>
            <button type="button" onclick="testEmail()" class="btn btn-outline btn-sm">测试发送</button>
        </div>
        <div class="card-body">
            <form id="smtpForm">
                <input type="hidden" name="_token" value="<?= e($csrf_token) ?>">

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">SMTP服务器</label>
                        <input type="text" name="settings[smtp_host]" value="<?= e($settings['smtp_host'] ?? '') ?>"
                            class="form-control" placeholder="smtp.example.com">
                    </div>
                    <div class="form-group">
                        <label class="form-label">端口</label>
                        <input type="number" name="settings[smtp_port]" value="<?= e($settings['smtp_port'] ?? '465') ?>"
                            class="form-control" placeholder="465">
                    </div>
                    <div class="form-group">
                        <label class="form-label">用户名/邮箱</label>
                        <input type="text" name="settings[smtp_username]" value="<?= e($settings['smtp_username'] ?? '') ?>"
                            class="form-control" placeholder="user@example.com">
                    </div>
                    <div class="form-group">
                        <label class="form-label">密码/授权码</label>
                        <input type="password" name="settings[smtp_password]" value="<?= e($settings['smtp_password'] ?? '') ?>"
                            class="form-control" placeholder="密码或授权码">
                    </div>
                    <div class="form-group">
                        <label class="form-label">加密方式</label>
                        <select name="settings[smtp_encryption]" class="form-control">
                            <option value="ssl" <?= ($settings['smtp_encryption'] ?? 'ssl') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                            <option value="tls" <?= ($settings['smtp_encryption'] ?? '') === 'tls' ? 'selected' : '' ?>>TLS</option>
                            <option value="" <?= ($settings['smtp_encryption'] ?? '') === '' ? 'selected' : '' ?>>无</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">发件人名称</label>
                        <input type="text" name="settings[smtp_from_name]" value="<?= e($settings['smtp_from_name'] ?? 'MAXLOGIN') ?>"
                            class="form-control" placeholder="MAXLOGIN">
                    </div>
                </div>

                <div style="margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary">
                        保存邮件配置
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 短信配置 -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">短信配置</h3>
            <button type="button" onclick="testSms()" class="btn btn-outline btn-sm">测试发送</button>
        </div>
        <div class="card-body">
            <form id="smsForm">
                <input type="hidden" name="_token" value="<?= e($csrf_token) ?>">

                <div class="form-group">
                    <label class="form-label">短信服务商</label>
                    <select name="settings[sms_provider]" id="smsProvider" onchange="toggleSmsConfig()" class="form-control">
                        <option value="">不启用短信</option>
                        <option value="tencent" <?= ($settings['sms_provider'] ?? '') === 'tencent' ? 'selected' : '' ?>>腾讯云短信</option>
                        <option value="aliyun" <?= ($settings['sms_provider'] ?? '') === 'aliyun' ? 'selected' : '' ?>>阿里云短信</option>
                    </select>
                </div>

                <!-- 腾讯云配置 -->
                <div id="tencentConfig" style="margin-top: 1rem; <?= ($settings['sms_provider'] ?? '') !== 'tencent' ? 'display: none;' : '' ?>">
                    <div style="background-color: var(--bg-surface-hover); padding: 1.5rem; border-radius: var(--radius-md);">
                        <p style="font-size: 0.875rem; color: var(--color-primary); margin-bottom: 1rem; font-weight: 500;">腾讯云短信配置</p>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label class="form-label">SecretId</label>
                                <input type="text" name="settings[sms_tencent_secret_id]" value="<?= e($settings['sms_tencent_secret_id'] ?? '') ?>"
                                    class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="form-label">SecretKey</label>
                                <input type="password" name="settings[sms_tencent_secret_key]" value="<?= e($settings['sms_tencent_secret_key'] ?? '') ?>"
                                    class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="form-label">SdkAppId</label>
                                <input type="text" name="settings[sms_tencent_sdk_app_id]" value="<?= e($settings['sms_tencent_sdk_app_id'] ?? '') ?>"
                                    class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="form-label">短信签名</label>
                                <input type="text" name="settings[sms_tencent_sign_name]" value="<?= e($settings['sms_tencent_sign_name'] ?? '') ?>"
                                    class="form-control">
                            </div>
                            <div class="form-group" style="grid-column: 1 / -1;">
                                <label class="form-label">模板ID</label>
                                <input type="text" name="settings[sms_tencent_template_id]" value="<?= e($settings['sms_tencent_template_id'] ?? '') ?>"
                                    class="form-control">
                                <p style="font-size: 0.75rem; color: var(--color-primary); margin-top: 0.5rem; padding: 0.5rem; background: rgba(var(--color-primary-rgb), 0.1); border-radius: var(--radius-sm);">模板：您的验证码为{1}，有效期5分钟，请勿泄露给他人。</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 阿里云配置 -->
                <div id="aliyunConfig" style="margin-top: 1rem; <?= ($settings['sms_provider'] ?? '') !== 'aliyun' ? 'display: none;' : '' ?>">
                    <div style="background-color: var(--bg-surface-hover); padding: 1.5rem; border-radius: var(--radius-md);">
                        <p style="font-size: 0.875rem; color: #f97316; margin-bottom: 1rem; font-weight: 500;">阿里云短信配置</p>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label class="form-label">AccessKeyId</label>
                                <input type="text" name="settings[sms_aliyun_access_key_id]" value="<?= e($settings['sms_aliyun_access_key_id'] ?? '') ?>"
                                    class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="form-label">AccessKeySecret</label>
                                <input type="password" name="settings[sms_aliyun_access_key_secret]" value="<?= e($settings['sms_aliyun_access_key_secret'] ?? '') ?>"
                                    class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="form-label">短信签名</label>
                                <input type="text" name="settings[sms_aliyun_sign_name]" value="<?= e($settings['sms_aliyun_sign_name'] ?? '') ?>"
                                    class="form-control">
                            </div>
                            <div class="form-group" style="grid-column: 1 / -1;">
                                <label class="form-label">模板Code</label>
                                <input type="text" name="settings[sms_aliyun_template_code]" value="<?= e($settings['sms_aliyun_template_code'] ?? '') ?>"
                                    class="form-control">
                                <p style="font-size: 0.75rem; color: #f97316; margin-top: 0.5rem; padding: 0.5rem; background: rgba(249, 115, 22, 0.1); border-radius: var(--radius-sm);">模板：您的验证码为${code}，有效期5分钟，请勿泄露给他人。</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary">
                        保存短信配置
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 测试弹窗 -->
<div id="testModal" class="modal">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h3 class="modal-title" id="testTitle">测试发送</h3>
            <button type="button" class="close-modal" onclick="hideTestModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="testForm">
                <input type="hidden" name="_token" value="<?= e($csrf_token) ?>">
                <input type="hidden" name="type" id="testType">
                <div class="form-group">
                    <label class="form-label" id="testLabel">目标</label>
                    <input type="text" name="target" id="testTarget" class="form-control">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" onclick="hideTestModal()" class="btn btn-outline">取消</button>
            <button type="button" onclick="document.getElementById('testForm').dispatchEvent(new Event('submit'))" class="btn btn-primary">发送测试</button>
        </div>
    </div>
</div>

<script>
    (function() {
        function toggleSmsConfig() {
            const provider = document.getElementById('smsProvider').value;
            document.getElementById('tencentConfig').style.display = provider === 'tencent' ? 'block' : 'none';
            document.getElementById('aliyunConfig').style.display = provider === 'aliyun' ? 'block' : 'none';
        }

        function testEmail() {
            document.getElementById('testTitle').textContent = '测试邮件发送';
            document.getElementById('testLabel').textContent = '收件邮箱';
            document.getElementById('testTarget').placeholder = 'test@example.com';
            document.getElementById('testTarget').value = '';
            document.getElementById('testType').value = 'email';
            document.getElementById('testModal').classList.add('show');
        }

        function testSms() {
            document.getElementById('testTitle').textContent = '测试短信发送';
            document.getElementById('testLabel').textContent = '手机号码';
            document.getElementById('testTarget').placeholder = '13800138000';
            document.getElementById('testTarget').value = '';
            document.getElementById('testType').value = 'sms';
            document.getElementById('testModal').classList.add('show');
        }

        function hideTestModal() {
            document.getElementById('testModal').classList.remove('show');
        }
        window.toggleSmsConfig = toggleSmsConfig;
        window.testEmail = testEmail;
        window.testSms = testSms;
        window.hideTestModal = hideTestModal;
        var formNames = {
            smtpForm: '邮件配置',
            smsForm: '短信配置'
        };
        ['smtpForm', 'smsForm'].forEach(function(formId) {
            var form = document.getElementById(formId);
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    var moduleName = formNames[formId];
                    ajax('<?= admin_url('settings/notify/update') ?>', new FormData(this), function(data) {
                        toast(data.code === 0 ? moduleName + '已更新' : data.message, data.code === 0 ? 'success' : 'error');
                    });
                });
            }
        });
        var testForm = document.getElementById('testForm');
        if (testForm) {
            testForm.addEventListener('submit', function(e) {
                e.preventDefault();
                ajax('<?= admin_url('settings/notify/test') ?>', new FormData(this), function(data) {
                    if (data.code === 0) {
                        toast('发送成功，验证码: ' + data.data.code, 'success');
                        hideTestModal();
                    } else {
                        toast(data.message, 'error');
                    }
                });
            });
        }
    })();
</script>

<?php $settingsContent = ob_get_clean(); ?>
<?php ob_start(); ?>
<?php include ML_ROOT . '/views/layouts/admin_settings.php'; ?>
<?php $content = ob_get_clean(); ?>
<?php include ML_ROOT . '/views/layouts/admin.php'; ?>