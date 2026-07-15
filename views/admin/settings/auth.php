<?php $pageTitle = '系统设置';
ob_start(); ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">注册登录</h3>
    </div>
    <div class="card-body">
        <form id="authForm" style="max-width: 600px;">
            <input type="hidden" name="_token" value="<?= e($csrf_token) ?>">

            <div class="form-group">
                <label class="form-label">开放注册</label>
                <select name="settings[enable_register]" class="form-control">
                    <option value="1" <?= ($settings['enable_register'] ?? '1') == '1' ? 'selected' : '' ?>>开放</option>
                    <option value="0" <?= ($settings['enable_register'] ?? '1') == '0' ? 'selected' : '' ?>>关闭</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">注册验证方式</label>
                <select name="settings[register_verify_method]" class="form-control">
                    <option value="none" <?= ($settings['register_verify_method'] ?? 'none') == 'none' ? 'selected' : '' ?>>无需验证（仅填邮箱）</option>
                    <option value="email" <?= ($settings['register_verify_method'] ?? 'none') == 'email' ? 'selected' : '' ?>>邮箱验证码</option>
                    <option value="phone" <?= ($settings['register_verify_method'] ?? 'none') == 'phone' ? 'selected' : '' ?>>手机验证码</option>
                </select>
                <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">选择验证方式后，用户注册需要输入对应的验证码</p>
            </div>

            <div style="margin-top: 1.5rem;">
                <button type="submit" class="btn btn-primary">
                    保存设置
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    (function() {
        var form = document.getElementById('authForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                ajax('<?= admin_url('settings/auth/update') ?>', new FormData(this), function(data) {
                    toast(data.code === 0 ? '注册登录设置已更新' : data.message, data.code === 0 ? 'success' : 'error');
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
