<?php $pageTitle = $app['app_name'];
ob_start(); ?>

<div style="margin-bottom: 1.5rem;">
    <a href="/user/apps" style="display: inline-flex; align-items: center; color: var(--color-primary); text-decoration: none; font-size: 0.875rem;">
        <span class="iconify" data-icon="tabler:arrow-left" style="margin-right: 0.25rem;"></span>
        返回应用列表
    </a>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
    <!-- 左侧：接口配置、日志 -->
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">

        <!-- 接口配置信息 -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">接口配置 <span style="font-size: 0.75rem; font-weight: normal; color: var(--color-success);">(兼容彩虹聚合登录)</span></h3>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div>
                        <label class="form-label">APPID</label>
                        <div style="display: flex;">
                            <input type="text" readonly value="<?= e($app['app_id']) ?>" class="form-control" style="border-top-right-radius: 0; border-bottom-right-radius: 0; background-color: var(--bg-surface-hover); font-family: monospace;">
                            <button onclick="copyText('<?= e($app['app_id']) ?>')" class="btn btn-primary" style="border-top-left-radius: 0; border-bottom-left-radius: 0;">复制</button>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">APPKEY</label>
                        <div style="display: flex;">
                            <input type="text" readonly value="<?= e($app['app_secret']) ?>" class="form-control" style="border-top-right-radius: 0; border-bottom-right-radius: 0; background-color: var(--bg-surface-hover); font-family: monospace;">
                            <button onclick="copyText('<?= e($app['app_secret']) ?>')" class="btn btn-primary" style="border-top-left-radius: 0; border-bottom-left-radius: 0;">复制</button>
                        </div>
                    </div>
                </div>
                <div style="margin-bottom: 1rem;">
                    <label class="form-label">接口地址</label>
                    <div style="display: flex;">
                        <input type="text" readonly value="<?= url('/') ?>" class="form-control" style="border-top-right-radius: 0; border-bottom-right-radius: 0; background-color: var(--bg-surface-hover); font-family: monospace;">
                        <button onclick="copyText('<?= url('/') ?>')" class="btn btn-primary" style="border-top-left-radius: 0; border-bottom-left-radius: 0;">复制</button>
                    </div>
                </div>
                <div class="alert alert-success">
                    <p style="font-weight: 600; margin-bottom: 0.5rem;">第三方系统配置：</p>
                    <ul style="list-style: none; padding: 0; margin: 0; font-size: 0.875rem;">
                        <li style="margin-bottom: 0.25rem;">• 接口网址：<code style="background: rgba(255,255,255,0.5); padding: 0.1rem 0.3rem; border-radius: 0.25rem;"><?= url('/') ?></code></li>
                        <li style="margin-bottom: 0.25rem;">• APPID：<code style="background: rgba(255,255,255,0.5); padding: 0.1rem 0.3rem; border-radius: 0.25rem;"><?= e($app['app_id']) ?></code></li>
                        <li>• APPKEY：<code style="background: rgba(255,255,255,0.5); padding: 0.1rem 0.3rem; border-radius: 0.25rem;"><?= e($app['app_secret']) ?></code></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- 登录记录 -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">最近登录记录</h3>
            </div>
            <div class="card-body" style="padding: 0;">
                <?php if (empty($logs)): ?>
                    <div style="text-align: center; padding: 3rem 2rem;">
                        <div style="color: var(--text-muted); margin-bottom: 1rem;">
                            <span class="iconify" data-icon="tabler:history" style="font-size: 3rem; opacity: 0.5;"></span>
                        </div>
                        <p style="color: var(--text-muted); margin: 0;">暂无登录记录</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>用户</th>
                                    <th>平台</th>
                                    <th>OpenID</th>
                                    <th>最后登录</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log):
                                    $platform = $log['platform'] ?? '';
                                    $iconImg = get_platform_icon($platform);
                                    $platformName = get_platform_name($platform);
                                    $gender = $log['gender'] ?? 0;
                                ?>
                                    <tr>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                                <?php if ($log['avatar']): ?>
                                                    <img src="<?= e(ensure_https($log['avatar'])) ?>" alt="" style="width: 2.25rem; height: 2.25rem; border-radius: 50%; object-fit: cover;">
                                                <?php else: ?>
                                                    <div style="width: 2.25rem; height: 2.25rem; border-radius: 50%; background-color: var(--bg-surface-hover); display: flex; align-items: center; justify-content: center;">
                                                        <span class="iconify" data-icon="tabler:user" style="font-size: 1.125rem; color: var(--text-light);"></span>
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <div style="font-weight: 500; color: var(--text-main);"><?= e($log['nickname'] ?: '未知用户') ?></div>
                                                    <div style="font-size: 0.75rem; color: var(--text-muted);">
                                                        <?php
                                                        if ($gender == 1) echo '<span style="color: #3b82f6;">♂ 男</span>';
                                                        elseif ($gender == 2) echo '<span style="color: #ec4899;">♀ 女</span>';
                                                        else echo '未知';
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.25rem 0.625rem 0.25rem 0.25rem; background-color: var(--bg-surface-hover); border-radius: 0.375rem;">
                                                <img src="<?= $iconImg ?>" alt="<?= e($platformName) ?>" style="width: 1.5rem; height: 1.5rem; border-radius: 0.25rem;">
                                                <span style="font-size: 0.8125rem; font-weight: 500; color: var(--text-main);"><?= e($platformName) ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <code style="font-size: 0.75rem; background-color: var(--bg-surface-hover); padding: 0.25rem 0.5rem; border-radius: 0.25rem; cursor: pointer;" onclick="copyText('<?= e($log['open_id']) ?>')" title="点击复制">
                                                <?= e(substr($log['open_id'], 0, 16)) ?>...
                                            </code>
                                        </td>
                                        <td style="color: var(--text-muted); font-size: 0.875rem;"><?= e($log['last_time']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- 右侧设置 -->
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">应用设置</h3>
            </div>
            <div class="card-body">
                <form id="updateForm" enctype="multipart/form-data">
                    <input type="hidden" name="_token" value="<?= e($csrf_token) ?>">
                    <input type="hidden" name="id" value="<?= $app['id'] ?>">

                    <div class="form-group">
                        <label class="form-label">应用图标</label>
                        <div style="display: flex; align-items: flex-start; gap: 1rem;">
                            <div id="iconPreview" style="width: 4rem; height: 4rem; background-color: var(--bg-surface-hover); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0;">
                                <?php if (!empty($app['app_icon'])): ?>
                                    <img src="<?= e($app['app_icon']) ?>?v=<?= time() ?>" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                    <span class="iconify" data-icon="tabler:photo-plus" style="font-size: 1.5rem; color: var(--text-light);"></span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <input type="file" name="app_icon" id="appIconInput" accept="image/*" style="display: none;">
                                <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('appIconInput').click()">选择文件</button>
                                <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem;">支持 JPG、PNG</p>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">应用名称</label>
                        <input type="text" name="app_name" class="form-control" value="<?= e($app['app_name']) ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">授权域名</label>
                        <input type="text" name="domain" class="form-control" value="<?= e($app['domain']) ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">回调地址</label>
                        <input type="text" name="callback_url" class="form-control" value="<?= e($app['callback'] ?? '') ?>" placeholder="留空使用默认回调">
                        <?php if (empty($app['callback'])): ?>
                        <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">当前未设置，将使用默认回调地址</p>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label class="form-label">登录方式</label>
                        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                            <?php
                            // 从套餐信息获取允许的平台
                            $allowedPlatforms = $packageInfo['platforms'] ?? [];
                            // 获取应用已选择的平台（数据库存储为逗号分隔的字符串）
                            $appPlatforms = $app['platforms'] ?? '';
                            if (is_string($appPlatforms) && !empty($appPlatforms)) {
                                $appPlatforms = explode(',', $appPlatforms);
                            } else {
                                $appPlatforms = [];
                            }
                            // 遍历后台配置的平台，只显示启用的
                            foreach ($platforms as $p):
                                // 只显示后台启用的平台
                                if ($p['status'] != 1) continue;
                                $platformKey = $p['name']; // name字段是平台代码如qq
                                $platformName = get_platform_name($platformKey); // 使用映射获取显示名称
                                // 用户套餐不允许则禁用
                                $isAllowed = in_array($platformKey, $allowedPlatforms);
                                $isChecked = in_array($platformKey, $appPlatforms);
                            ?>
                            <label class="platform-select-item <?= !$isAllowed ? 'disabled' : '' ?> <?= $isChecked ? 'active' : '' ?>" onclick="toggleAppPlatform(this)" title="<?= !$isAllowed ? '当前套餐不支持此平台' : '' ?>">
                                <input type="checkbox" name="platforms[]" value="<?= e($platformKey) ?>" style="display: none;" <?= !$isAllowed ? 'disabled' : '' ?> <?= $isChecked ? 'checked' : '' ?>>
                                <?= e($platformName) ?>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">应用状态</label>
                        <select name="status" class="form-control">
                            <option value="1" <?= $app['status'] ? 'selected' : '' ?>>启用</option>
                            <option value="0" <?= !$app['status'] ? 'selected' : '' ?>>禁用</option>
                        </select>
                    </div>

                    <div style="display: flex; gap: 0.5rem;">
                        <button type="submit" class="btn btn-primary" style="flex: 1;">保存设置</button>
                        <button type="button" class="btn btn-outline" style="color: #ef4444; border-color: #ef4444;" onclick="deleteApp()">删除</button>
                    </div>
                </form>
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

    .platform-select-item:hover:not(.disabled) {
        border-color: var(--color-primary);
    }

    .platform-select-item.active {
        background-color: var(--color-primary);
        border-color: var(--color-primary);
        color: #fff;
    }

    .platform-select-item.disabled {
        background-color: var(--bg-surface-hover);
        color: var(--text-light);
        cursor: not-allowed;
        opacity: 0.65;
    }
</style>

<script>
    function toggleAppPlatform(label) {
        const checkbox = label.querySelector('input[type="checkbox"]');
        if (checkbox.disabled) return;
        checkbox.checked = !checkbox.checked;
        if (checkbox.checked) {
            label.classList.add('active');
        } else {
            label.classList.remove('active');
        }
    }

    document.getElementById('appIconInput').addEventListener('change', function() {
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('iconPreview').innerHTML = '<img src="' + e.target.result + '" style="width: 100%; height: 100%; object-fit: cover;">';
            };
            reader.readAsDataURL(this.files[0]);
        }
    });

    document.getElementById('updateForm').addEventListener('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);

        ajax('/user/apps/update', formData, function(res) {
            if (res.code === 0) {
                toast('保存成功', 'success');
            } else {
                toast(res.message, 'error');
            }
        });
    });

    function deleteApp() {
        showConfirm('确定要删除此应用吗？此操作不可恢复！', function() {
            ajax('/user/apps/delete', {
                _token: '<?= e($csrf_token) ?>',
                id: <?= $app['id'] ?>
            }, function(data) {
                if (data.code === 0) {
                    toast('删除成功', 'success');
                    setTimeout(() => location.href = '/user/apps', 1000);
                } else {
                    toast(data.message, 'error');
                }
            });
        });
    }
</script>

<?php $content = ob_get_clean(); ?>
<?php include ML_ROOT . '/views/layouts/user.php'; ?>
