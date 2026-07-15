<?php $pageTitle = '系统设置';
ob_start(); ?>

<div style="display: flex; flex-direction: column; gap: 1.5rem;">
    <!-- 免费用户策略 -->
    <div class="card">
        <div class="card-header">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 2.5rem; height: 2.5rem; background: linear-gradient(135deg, #10b981, #059669); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; box-shadow: var(--shadow-sm);">
                    <span class="iconify" data-icon="tabler:gift" style="color: white; font-size: 1.25rem;"></span>
                </div>
                <div>
                    <h3 class="card-title">免费用户策略</h3>
                    <p style="font-size: 0.75rem; color: var(--text-secondary); margin: 0;">配置无套餐用户的访问权限和限制</p>
                </div>
            </div>
            <label class="switch">
                <input type="checkbox" id="freeEnabled" <?= ($settings['billing_free_enabled'] ?? '1') == '1' ? 'checked' : '' ?>>
                <span class="slider"></span>
            </label>
        </div>
        <div class="card-body">
            <form id="freeUserForm">
                <input type="hidden" name="_token" value="<?= e($csrf_token) ?>">
                <input type="hidden" name="settings[billing_free_enabled]" id="freeEnabledValue" value="<?= ($settings['billing_free_enabled'] ?? '1') ?>">

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">每日调用限制</label>
                        <input type="number" name="settings[billing_free_daily_limit]" 
                            value="<?= e($settings['billing_free_daily_limit'] ?? '100') ?>"
                            class="form-control" placeholder="每日最大调用次数" min="0">
                        <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">免费用户每天可调用API的最大次数，0表示不限制</p>
                    </div>
                    <div class="form-group">
                        <label class="form-label">可用登录平台</label>
                        <?php $freePlatforms = json_decode($settings['billing_free_platforms'] ?? '["qq","wx"]', true) ?: []; ?>
                        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; padding: 0.5rem 0;">
                            <?php foreach ($platforms as $p):
                                $isChecked = in_array($p['name'], $freePlatforms);
                            ?>
                                <label class="platform-select-item <?= $isChecked ? 'active' : '' ?>" onclick="toggleFreePlatform(this)">
                                    <input type="checkbox" name="free_platforms[]" value="<?= e($p['name']) ?>" 
                                        style="display: none;"
                                        <?= $isChecked ? 'checked' : '' ?>
                                        class="free-platform-checkbox">
                                    <span><?= e($p['platform']) ?></span>
                                </label>
                            <?php endforeach; ?>
                            <?php if (empty($platforms)): ?>
                                <p style="color: var(--text-muted); font-size: 0.875rem;">暂无已启用的登录平台，请先在平台管理中启用</p>
                            <?php endif; ?>
                        </div>
                        <input type="hidden" name="settings[billing_free_platforms]" id="freePlatformsValue" 
                            value="<?= e($settings['billing_free_platforms'] ?? '["qq","wx"]') ?>">
                        <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">点击切换启用/禁用状态，仅显示后台已启用的平台</p>
                    </div>
                </div>
                <div style="margin-top: 1rem;">
                    <button type="submit" class="btn btn-primary" style="background-color: #10b981; border-color: #10b981;">保存配置</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 频率限制配置 -->
    <div class="card">
        <div class="card-header">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 2.5rem; height: 2.5rem; background: linear-gradient(135deg, #f59e0b, #d97706); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; box-shadow: var(--shadow-sm);">
                    <span class="iconify" data-icon="tabler:gauge" style="color: white; font-size: 1.25rem;"></span>
                </div>
                <div>
                    <h3 class="card-title">频率限制</h3>
                    <p style="font-size: 0.75rem; color: var(--text-secondary); margin: 0;">配置不同套餐类型的API调用频率限制</p>
                </div>
            </div>
        </div>
        <div class="card-body">
            <form id="rateLimitForm">
                <input type="hidden" name="_token" value="<?= e($csrf_token) ?>">

                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">默认限制 (次/秒)</label>
                        <input type="number" name="settings[billing_rate_limit_default]" 
                            value="<?= e($settings['billing_rate_limit_default'] ?? '10') ?>"
                            class="form-control" placeholder="默认每秒调用限制" min="1">
                        <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">免费用户的频率限制</p>
                    </div>
                    <div class="form-group">
                        <label class="form-label">套餐包限制 (次/秒)</label>
                        <input type="number" name="settings[billing_rate_limit_package]" 
                            value="<?= e($settings['billing_rate_limit_package'] ?? '50') ?>"
                            class="form-control" placeholder="套餐包每秒调用限制" min="1">
                        <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">套餐包用户的频率限制</p>
                    </div>
                    <div class="form-group">
                        <label class="form-label">次数包限制 (次/秒)</label>
                        <input type="number" name="settings[billing_rate_limit_quota]" 
                            value="<?= e($settings['billing_rate_limit_quota'] ?? '30') ?>"
                            class="form-control" placeholder="次数包每秒调用限制" min="1">
                        <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">调用次数包用户的频率限制</p>
                    </div>
                </div>
                <div style="margin-top: 1rem;">
                    <button type="submit" class="btn btn-primary" style="background-color: #f59e0b; border-color: #f59e0b;">保存配置</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 计费说明 -->
    <div class="card">
        <div class="card-header">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 2.5rem; height: 2.5rem; background: linear-gradient(135deg, #6366f1, #4f46e5); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; box-shadow: var(--shadow-sm);">
                    <span class="iconify" data-icon="tabler:info-circle" style="color: white; font-size: 1.25rem;"></span>
                </div>
                <div>
                    <h3 class="card-title">计费说明</h3>
                    <p style="font-size: 0.75rem; color: var(--text-secondary); margin: 0;">系统支持的套餐类型和计费规则</p>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem;">
                <div style="padding: 1rem; background: var(--bg-surface-hover); border-radius: var(--radius-md);">
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;">
                        <span class="iconify" data-icon="tabler:crown" style="color: #a855f7; font-size: 1.25rem;"></span>
                        <h4 style="margin: 0; font-size: 0.9375rem; font-weight: 600;">套餐包</h4>
                    </div>
                    <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.8125rem; color: var(--text-secondary); line-height: 1.75;">
                        <li>按周期计费（包月/包季/包年）</li>
                        <li>限制可用的登录平台</li>
                        <li>有效期内不限调用次数</li>
                    </ul>
                </div>
                <div style="padding: 1rem; background: var(--bg-surface-hover); border-radius: var(--radius-md);">
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;">
                        <span class="iconify" data-icon="tabler:users" style="color: #3b82f6; font-size: 1.25rem;"></span>
                        <h4 style="margin: 0; font-size: 0.9375rem; font-weight: 600;">账号数量包</h4>
                    </div>
                    <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.8125rem; color: var(--text-secondary); line-height: 1.75;">
                        <li>限制授权用户总数量</li>
                        <li>已存在用户不计入新增</li>
                        <li>适合用户数量可控的场景</li>
                    </ul>
                </div>
                <div style="padding: 1rem; background: var(--bg-surface-hover); border-radius: var(--radius-md);">
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;">
                        <span class="iconify" data-icon="tabler:clock" style="color: #10b981; font-size: 1.25rem;"></span>
                        <h4 style="margin: 0; font-size: 0.9375rem; font-weight: 600;">调用次数包</h4>
                    </div>
                    <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.8125rem; color: var(--text-secondary); line-height: 1.75;">
                        <li>限制有效期内的调用总次数</li>
                        <li>支持永久有效（无过期时间）</li>
                        <li>适合按量付费的场景</li>
                    </ul>
                </div>
            </div>
            <div class="alert alert-info" style="margin-top: 1.5rem;">
                <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
                    <span class="iconify" data-icon="tabler:bulb" style="font-size: 1.25rem; flex-shrink: 0;"></span>
                    <div style="font-size: 0.8125rem;">
                        <strong>单套餐原则：</strong>每个用户同一时间只能拥有一个有效套餐。购买新套餐会自动替换原有套餐，系统会记录套餐变更历史。
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>

.platform-select-item {
    display: inline-flex;
    align-items: center;
    padding: 0.375rem 0.75rem;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: all 0.2s;
    user-select: none;
    font-size: 0.875rem;
    color: var(--text-main);
}

.platform-select-item:hover {
    border-color: var(--color-primary);
}

.platform-select-item.active {
    background-color: var(--color-primary);
    border-color: var(--color-primary);
    color: #fff;
}
</style>

<script>
function toggleFreePlatform(label) {
    var checkbox = label.querySelector('input[type="checkbox"]');
    checkbox.checked = !checkbox.checked;
    if (checkbox.checked) {
        label.classList.add('active');
    } else {
        label.classList.remove('active');
    }
    updateFreePlatforms();
}
function updateFreePlatforms() {
    var checkboxes = document.querySelectorAll('.free-platform-checkbox:checked');
    var platforms = Array.from(checkboxes).map(function(cb) { return cb.value; });
    document.getElementById('freePlatformsValue').value = JSON.stringify(platforms);
}

(function() {
    var freeEnabled = document.getElementById('freeEnabled');
    if (freeEnabled) {
        freeEnabled.addEventListener('change', function() {
            document.getElementById('freeEnabledValue').value = this.checked ? '1' : '0';
            toast('免费用户访问' + (this.checked ? '已开启' : '已关闭'), this.checked ? 'success' : 'warning');
        });
    }
    var freeUserForm = document.getElementById('freeUserForm');
    if (freeUserForm) {
        freeUserForm.addEventListener('submit', function(e) {
            e.preventDefault();
            updateFreePlatforms();
            ajax('<?= admin_url('settings/billing/update') ?>', new FormData(this), function(data) {
                toast(data.code === 0 ? '免费用户策略已更新' : data.message, data.code === 0 ? 'success' : 'error');
            });
        });
    }
    var rateLimitForm = document.getElementById('rateLimitForm');
    if (rateLimitForm) {
        rateLimitForm.addEventListener('submit', function(e) {
            e.preventDefault();
            ajax('<?= admin_url('settings/billing/update') ?>', new FormData(this), function(data) {
                toast(data.code === 0 ? '频率限制配置已更新' : data.message, data.code === 0 ? 'success' : 'error');
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