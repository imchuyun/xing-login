<?php $pageTitle = '系统设置';
ob_start(); ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">网站信息</h3>
    </div>
    <div class="card-body">
        <form id="settingsForm" style="max-width: 600px;">
            <input type="hidden" name="_token" value="<?= e($csrf_token) ?>">

            <div class="form-group">
                <label class="form-label">站点名称</label>
                <input type="text" name="settings[site_name]" value="<?= e($settings['site_name'] ?? '') ?>"
                    class="form-control">
            </div>

            <div class="form-group">
                <label class="form-label">站点地址</label>
                <input type="url" name="settings[site_url]" value="<?= e($settings['site_url'] ?? '') ?>"
                    class="form-control">
            </div>

            <div class="form-group">
                <label class="form-label">站点描述</label>
                <textarea name="settings[site_description]" rows="2"
                    class="form-control"><?= e($settings['site_description'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">网站关键词</label>
                <input type="text" name="settings[site_keywords]" value="<?= e($settings['site_keywords'] ?? '') ?>"
                    placeholder="多个关键词用逗号分隔"
                    class="form-control">
            </div>

            <div class="form-group">
                <label class="form-label">管理员邮箱</label>
                <input type="email" name="settings[admin_email]" value="<?= e($settings['admin_email'] ?? '') ?>"
                    class="form-control">
            </div>

            <div class="form-group">
                <label class="form-label">ICP备案号</label>
                <input type="text" name="settings[site_icp]" value="<?= e($settings['site_icp'] ?? '') ?>"
                    placeholder="例如：京ICP备12345678号"
                    class="form-control">
                <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">网站ICP备案号，将显示在页面底部</p>
            </div>

            <div class="form-group">
                <label class="form-label">首页跳转</label>
                <select name="settings[homepage_redirect]" class="form-control">
                    <option value="none" <?= (empty($settings['homepage_redirect']) || $settings['homepage_redirect'] === 'none') ? 'selected' : '' ?>>不跳转（显示首页）</option>
                    <option value="login" <?= ($settings['homepage_redirect'] ?? '') === 'login' ? 'selected' : '' ?>>跳转到登录页面</option>
                </select>
                <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">设置访问网站首页时的跳转行为</p>
            </div>

            <div class="form-group">
                <label class="form-label">站点Logo</label>
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <div id="logoPreview" style="width: 3rem; height: 3rem; border-radius: 0.5rem; background: var(--bg-surface-hover); display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0;">
                        <img src="/assets/logo.png?v=<?= time() ?>" alt="Logo" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                    </div>
                    <span style="color: var(--text-muted); font-size: 0.875rem;">/assets/logo.png</span>
                    <input type="file" id="logoFileInput" accept="image/jpeg,image/png,image/gif,image/webp" style="display: none;">
                    <button type="button" class="btn btn-primary" onclick="document.getElementById('logoFileInput').click()">上传</button>
                </div>
                <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">支持 JPG、PNG、GIF、WEBP 格式，最大 2MB</p>
            </div>

            <div class="form-group">
                <label class="form-label">站点Favicon</label>
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <div id="faviconPreview" style="width: 3rem; height: 3rem; border-radius: 0.5rem; background: var(--bg-surface-hover); display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0;">
                        <img src="/assets/favicon.ico?v=<?= time() ?>" alt="Favicon" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                    </div>
                    <span style="color: var(--text-muted); font-size: 0.875rem;">/assets/favicon.ico</span>
                    <input type="file" id="faviconFileInput" accept="image/x-icon,image/vnd.microsoft.icon,image/png,image/gif,.ico" style="display: none;">
                    <button type="button" class="btn btn-primary" onclick="document.getElementById('faviconFileInput').click()">上传</button>
                </div>
                <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">支持 ICO、PNG、GIF 格式，最大 2MB</p>
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
        var form = document.getElementById('settingsForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                ajax('<?= admin_url('settings/site/update') ?>', new FormData(this), function(data) {
                    toast(data.code === 0 ? '网站信息已更新' : data.message, data.code === 0 ? 'success' : 'error');
                });
            });
        }
        var logoFileInput = document.getElementById('logoFileInput');
        var logoPreview = document.getElementById('logoPreview');
        
        if (logoFileInput) {
            logoFileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    uploadImage(this.files[0], 'logo', function(url) {
                        logoPreview.innerHTML = '<img src="' + url + '" alt="Logo" style="max-width: 100%; max-height: 100%; object-fit: contain;">';
                    });
                }
            });
        }
        var faviconFileInput = document.getElementById('faviconFileInput');
        var faviconPreview = document.getElementById('faviconPreview');
        
        if (faviconFileInput) {
            faviconFileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    uploadImage(this.files[0], 'favicon', function(url) {
                        faviconPreview.innerHTML = '<img src="' + url + '" alt="Favicon" style="max-width: 100%; max-height: 100%; object-fit: contain;">';
                    });
                }
            });
        }
        function uploadImage(file, type, callback) {
            var formData = new FormData();
            formData.append('file', file);
            formData.append('type', type);
            formData.append('_token', '<?= e($csrf_token) ?>');

            var xhr = new XMLHttpRequest();
            xhr.open('POST', '/admin/settings/upload-image', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response.code === 0 && response.data && response.data.url) {
                            callback(response.data.url);
                            toast('上传成功', 'success');
                        } else {
                            toast(response.message || '上传失败', 'error');
                        }
                    } catch (e) {
                        toast('上传失败', 'error');
                    }
                } else {
                    toast('上传失败', 'error');
                }
            };
            xhr.onerror = function() {
                toast('上传失败', 'error');
            };
            xhr.send(formData);
        }
    })();
</script>

<?php $settingsContent = ob_get_clean(); ?>
<?php ob_start(); ?>
<?php include ML_ROOT . '/views/layouts/admin_settings.php'; ?>
<?php $content = ob_get_clean(); ?>
<?php include ML_ROOT . '/views/layouts/admin.php'; ?>