<?php $pageTitle = '系统设置';
ob_start(); ?>

<div style="display: flex; flex-direction: column; gap: 1.5rem;">
    <!-- 邮箱限制 -->
    <div class="card">
        <div class="card-header">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 class="card-title">邮箱限制</h3>
                    <p style="font-size: 0.875rem; color: var(--text-secondary); margin-top: 0.25rem;">限制允许注册的邮箱后缀，留空则不限制</p>
                </div>
            </div>
        </div>
        <div class="card-body">
            <form id="emailForm" style="max-width: 600px;">
                <input type="hidden" name="_token" value="<?= e($csrf_token) ?>">

                <div class="form-group">
                    <label class="form-label">限制模式</label>
                    <div style="display: flex; gap: 1.5rem;">
                        <label style="display: flex; align-items: center; cursor: pointer;">
                            <input type="radio" name="settings[security_email_mode]" value="whitelist"
                                <?= ($settings['security_email_mode'] ?? 'whitelist') === 'whitelist' ? 'checked' : '' ?>
                                style="margin-right: 0.5rem;">
                            <span style="font-size: 0.875rem;">白名单模式（只允许指定后缀）</span>
                        </label>
                        <label style="display: flex; align-items: center; cursor: pointer;">
                            <input type="radio" name="settings[security_email_mode]" value="blacklist"
                                <?= ($settings['security_email_mode'] ?? '') === 'blacklist' ? 'checked' : '' ?>
                                style="margin-right: 0.5rem;">
                            <span style="font-size: 0.875rem;">黑名单模式（禁止指定后缀）</span>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">邮箱后缀列表</label>
                    <textarea name="settings[security_email_list]" rows="4"
                        class="form-control" style="font-family: monospace;"
                        placeholder="每行一个邮箱后缀，如：&#10;qq.com&#10;163.com&#10;gmail.com"><?= e($settings['security_email_list'] ?? '') ?></textarea>
                    <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">每行一个邮箱后缀（不含@符号），留空则不限制</p>
                </div>

                <div style="margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary">
                        保存邮箱配置
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 访问限制 -->
    <div class="card">
        <div class="card-header">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 class="card-title">访问限制</h3>
                    <p style="font-size: 0.875rem; color: var(--text-secondary); margin-top: 0.25rem;">通过地区和IP限制访问，保护系统安全</p>
                </div>
                <label class="switch">
                    <input type="checkbox" id="regionToggle" <?= ($settings['security_region_enabled'] ?? '0') == '1' ? 'checked' : '' ?>>
                    <span class="slider"></span>
                </label>
            </div>
        </div>
        <div class="card-body">
            <form id="accessForm">
                <input type="hidden" name="_token" value="<?= e($csrf_token) ?>">
                <input type="hidden" name="settings[security_region_enabled]" id="regionEnabledInput" value="<?= ($settings['security_region_enabled'] ?? '0') ?>">

                <!-- 地区限制 -->
                <div style="background-color: var(--bg-surface-hover); padding: 1.5rem; border-radius: var(--radius-md); margin-bottom: 1.5rem;">
                    <h4 style="font-weight: 600; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                        <span class="iconify" data-icon="tabler:map-pin" style="color: var(--color-primary);"></span>
                        地区限制
                    </h4>

                    <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                        <div style="display: flex; gap: 0.75rem;">
                            <label class="btn btn-outline btn-sm" style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                <input type="radio" name="settings[security_region_mode]" value="whitelist"
                                    <?= ($settings['security_region_mode'] ?? 'whitelist') === 'whitelist' ? 'checked' : '' ?>>
                                <span>白名单</span>
                            </label>
                            <label class="btn btn-outline btn-sm" style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                <input type="radio" name="settings[security_region_mode]" value="blacklist"
                                    <?= ($settings['security_region_mode'] ?? '') === 'blacklist' ? 'checked' : '' ?>>
                                <span>黑名单</span>
                            </label>
                        </div>
                        <button type="button" id="openRegionModal" class="btn btn-primary btn-sm" style="display: flex; align-items: center; gap: 0.5rem;">
                            <span class="iconify" data-icon="tabler:plus"></span>
                            选择地区
                        </button>
                        <span id="selectedCount" style="font-size: 0.875rem; color: var(--text-secondary);">已选 <strong style="color: var(--color-primary);">0</strong> 个地区</span>
                    </div>

                    <?php
                    $regionOptions = [
                        '中国大陆' => ['北京', '天津', '上海', '重庆', '河北', '山西', '辽宁', '吉林', '黑龙江', '江苏', '浙江', '安徽', '福建', '江西', '山东', '河南', '湖北', '湖南', '广东', '海南', '四川', '贵州', '云南', '陕西', '甘肃', '青海', '内蒙古', '广西', '西藏', '宁夏', '新疆'],
                        '港澳台' => ['香港', '澳门', '台湾'],
                        '亚洲' => ['日本', '韩国', '新加坡', '马来西亚', '泰国', '越南', '印度', '印度尼西亚', '菲律宾'],
                        '欧洲' => ['英国', '法国', '德国', '意大利', '西班牙', '荷兰', '俄罗斯', '瑞士', '瑞典'],
                        '北美洲' => ['美国', '加拿大', '墨西哥'],
                        '大洋洲' => ['澳大利亚', '新西兰'],
                        '其他' => ['南美洲', '非洲', '中东'],
                    ];
                    $selectedRegions = array_filter(array_map('trim', explode("\n", $settings['security_region_list'] ?? '')));
                    ?>

                    <!-- 已选地区展示 -->
                    <div id="selectedRegionsDisplay" style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                        <?php foreach ($selectedRegions as $region): ?>
                            <span class="badge badge-primary" style="display: flex; align-items: center; gap: 0.25rem;" data-region="<?= $region ?>">
                                <?= $region ?>
                                <button type="button" class="remove-region" style="background: none; border: none; color: currentColor; cursor: pointer; padding: 0; opacity: 0.7;" data-region="<?= $region ?>">&times;</button>
                            </span>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="settings[security_region_list]" id="regionListInput" value="<?= e($settings['security_region_list'] ?? '') ?>">
                </div>

                <!-- IP限制 -->
                <div style="background-color: var(--bg-surface-hover); padding: 1.5rem; border-radius: var(--radius-md);">
                    <h4 style="font-weight: 600; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                        <span class="iconify" data-icon="tabler:shield" style="color: var(--text-main);"></span>
                        IP限制
                    </h4>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                        <div>
                            <label class="form-label" style="display: flex; align-items: center; gap: 0.25rem;">
                                <span style="color: var(--color-error);">●</span> IP黑名单
                            </label>
                            <textarea name="settings[security_ip_blacklist]" rows="4"
                                class="form-control" style="font-family: monospace;"
                                placeholder="每行一个IP，如：&#10;192.168.1.100&#10;10.0.0.0/8"><?= e($settings['security_ip_blacklist'] ?? '') ?></textarea>
                            <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">禁止这些IP访问</p>
                        </div>
                        <div>
                            <label class="form-label" style="display: flex; align-items: center; gap: 0.25rem;">
                                <span style="color: var(--color-success);">●</span> IP白名单（优先级更高）
                            </label>
                            <textarea name="settings[security_ip_whitelist]" rows="4"
                                class="form-control" style="font-family: monospace;"
                                placeholder="每行一个IP，白名单IP跳过所有检查"><?= e($settings['security_ip_whitelist'] ?? '') ?></textarea>
                            <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">这些IP跳过所有安全检查</p>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary">
                        保存访问限制配置
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 地区选择弹窗 -->
<div id="regionModal" class="modal">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3 class="modal-title">选择地区</h3>
            <button type="button" class="close-modal" id="closeRegionModal">&times;</button>
        </div>
        <div class="modal-body">
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                <?php foreach ($regionOptions as $group => $regions): ?>
                    <div style="background-color: var(--bg-surface-hover); padding: 0.75rem; border-radius: var(--radius-sm);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; padding-bottom: 0.25rem; border-bottom: 1px solid var(--border-color);">
                            <span style="font-size: 0.75rem; font-weight: 600; color: var(--text-main);"><?= $group ?></span>
                            <button type="button" class="select-all-btn" style="font-size: 0.75rem; color: var(--color-primary); background: none; border: none; cursor: pointer;" data-group="<?= $group ?>">全选</button>
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 0.25rem; max-height: 150px; overflow-y: auto;">
                            <?php foreach ($regions as $region): ?>
                                <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.75rem; cursor: pointer;">
                                    <input type="checkbox" value="<?= $region ?>"
                                        data-group="<?= $group ?>"
                                        <?= in_array($region, $selectedRegions) ? 'checked' : '' ?>
                                        class="region-checkbox">
                                    <span><?= $region ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" id="clearAllRegions" class="btn btn-outline btn-sm" style="color: var(--color-error); border-color: var(--color-error);">清空</button>
            <div style="flex: 1;"></div>
            <button type="button" id="cancelRegionModal" class="btn btn-outline">取消</button>
            <button type="button" id="confirmRegions" class="btn btn-primary">确认</button>
        </div>
    </div>
</div>

<script>
    (function() {
        var regionToggle = document.getElementById('regionToggle');
        if (regionToggle) {
            regionToggle.addEventListener('change', function() {
                document.getElementById('regionEnabledInput').value = this.checked ? '1' : '0';
            });
        }
        var modal = document.getElementById('regionModal');

        function openModal() {
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            modal.classList.remove('show');
            document.body.style.overflow = '';
        }

        var openRegionModalBtn = document.getElementById('openRegionModal');
        var closeRegionModalBtn = document.getElementById('closeRegionModal');
        var cancelRegionModalBtn = document.getElementById('cancelRegionModal');
        var confirmRegionsBtn = document.getElementById('confirmRegions');

        if (openRegionModalBtn) openRegionModalBtn.addEventListener('click', openModal);
        if (closeRegionModalBtn) closeRegionModalBtn.addEventListener('click', closeModal);
        if (cancelRegionModalBtn) cancelRegionModalBtn.addEventListener('click', closeModal);
        if (confirmRegionsBtn) {
            confirmRegionsBtn.addEventListener('click', function() {
                updateSelectedDisplay();
                closeModal();
            });
        }
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeModal();
                }
            });
        }
        function updateSelectedDisplay() {
            var checked = document.querySelectorAll('.region-checkbox:checked');
            var values = Array.from(checked).map(function(cb) { return cb.value; });
            var regionListInput = document.getElementById('regionListInput');
            if (regionListInput) regionListInput.value = values.join('\n');
            var selectedCount = document.getElementById('selectedCount');
            if (selectedCount) {
                selectedCount.innerHTML = '已选 <strong style="color: var(--color-primary);">' + values.length + '</strong> 个地区';
            }
            var display = document.getElementById('selectedRegionsDisplay');
            if (display) {
                display.innerHTML = values.map(function(v) {
                    return '<span class="badge badge-primary" style="display: flex; align-items: center; gap: 0.25rem;" data-region="' + v + '">' +
                        v +
                        '<button type="button" class="remove-region" style="background: none; border: none; color: currentColor; cursor: pointer; padding: 0; opacity: 0.7;" data-region="' + v + '">&times;</button>' +
                        '</span>';
                }).join('');
                bindRemoveButtons();
            }
        }
        function bindRemoveButtons() {
            document.querySelectorAll('.remove-region').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var region = this.dataset.region;
                    var checkbox = document.querySelector('.region-checkbox[value="' + region + '"]');
                    if (checkbox) checkbox.checked = false;
                    updateSelectedDisplay();
                });
            });
        }
        bindRemoveButtons();
        document.querySelectorAll('.select-all-btn').forEach(function(btn) {
            btn.onclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                var group = this.dataset.group;
                var checkboxes = document.querySelectorAll('.region-checkbox[data-group="' + group + '"]');
                var allChecked = Array.from(checkboxes).every(function(cb) {
                    return cb.checked;
                });
                checkboxes.forEach(function(cb) {
                    cb.checked = !allChecked;
                });
                this.textContent = allChecked ? '全选' : '取消';
            };
        });
        var clearAllRegionsBtn = document.getElementById('clearAllRegions');
        if (clearAllRegionsBtn) {
            clearAllRegionsBtn.onclick = function(e) {
                e.preventDefault();
                document.querySelectorAll('.region-checkbox').forEach(function(cb) {
                    cb.checked = false;
                });
                document.querySelectorAll('.select-all-btn').forEach(function(btn) {
                    btn.textContent = '全选';
                });
            };
        }
        updateSelectedDisplay();
        var securityFormNames = {
            emailForm: '邮箱限制',
            accessForm: '访问限制'
        };
        ['emailForm', 'accessForm'].forEach(function(formId) {
            var form = document.getElementById(formId);
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    var moduleName = securityFormNames[formId];
                    ajax('<?= admin_url('settings/security/update') ?>', new FormData(this), function(data) {
                        toast(data.code === 0 ? moduleName + '已更新' : data.message, data.code === 0 ? 'success' : 'error');
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