<?php $pageTitle = '我的应用';
ob_start(); ?>

<!-- 创建应用按钮 -->
<div style="margin-bottom: 1.5rem;">
    <button onclick="showCreateModal()" class="btn btn-primary">
        <span class="iconify" data-icon="tabler:plus" style="margin-right: 0.5rem;"></span> 创建应用
    </button>
</div>

<!-- 应用列表 -->
<?php if (empty($apps)): ?>
    <div class="card">
        <div class="card-body" style="text-align: center; padding: 4rem 2rem;">
            <div style="color: var(--text-muted); margin-bottom: 1rem;">
                <span class="iconify" data-icon="tabler:apps" style="font-size: 4rem; opacity: 0.5;"></span>
            </div>
            <h5 style="color: var(--text-muted); font-size: 1.125rem; margin-bottom: 0.5rem;">暂无应用</h5>
            <p style="color: var(--text-light); font-size: 0.875rem;">点击上方"创建应用"按钮开始创建您的第一个应用</p>
        </div>
    </div>
<?php else: ?>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
        <?php foreach ($apps as $app): ?>
            <div class="card" style="margin-bottom: 0; height: 100%; display: flex; flex-direction: column;">
                <div class="card-body" style="flex: 1; display: flex; flex-direction: column;">
                    <div style="display: flex; align-items: flex-start; margin-bottom: 1rem;">
                        <?php 
                        $appIcon = $app['app_icon'] ?? '';
                        // 如果图标路径不以/开头，添加完整路径
                        if ($appIcon && strpos($appIcon, '/') !== 0) {
                            $appIcon = '/storage/uploads/apps/' . $appIcon;
                        }
                        ?>
                        <?php if ($appIcon): ?>
                            <img src="<?= e($appIcon) ?>" alt="" style="width: 3rem; height: 3rem; border-radius: 0.5rem; object-fit: cover;" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                            <div style="width: 3rem; height: 3rem; background-color: var(--bg-surface-hover); border-radius: 0.5rem; display: none; align-items: center; justify-content: center; color: var(--color-primary);">
                                <span class="iconify" data-icon="tabler:apps" style="font-size: 1.5rem;"></span>
                            </div>
                        <?php else: ?>
                            <div style="width: 3rem; height: 3rem; background-color: var(--bg-surface-hover); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; color: var(--color-primary);">
                                <span class="iconify" data-icon="tabler:apps" style="font-size: 1.5rem;"></span>
                            </div>
                        <?php endif; ?>
                        <div style="margin-left: 1rem; flex: 1; min-width: 0;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                                <h5 style="font-size: 1rem; font-weight: 600; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 140px;" title="<?= e($app['app_name']) ?>"><?= e($app['app_name']) ?></h5>
                                <span class="badge <?= $app['status'] ? 'badge-success' : 'badge-error' ?>">
                                    <?= $app['status'] ? '正常' : '禁用' ?>
                                </span>
                            </div>
                            <div style="color: var(--text-muted); font-size: 0.75rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= e($app['domain']) ?></div>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: space-between; color: var(--text-muted); font-size: 0.75rem; margin-bottom: 1rem;">
                        <span>今日调用: <?= $app['today_calls'] ?></span>
                        <span>总调用: <?= $app['total_calls'] ?></span>
                    </div>

                    <div style="display: flex; gap: 0.5rem; margin-top: auto;">
                        <button onclick="copyText('<?= e($app['app_id']) ?>')" class="btn btn-outline btn-sm" style="flex: 1;">
                            复制AppID
                        </button>
                        <a href="/user/app=<?= e($app['app_id']) ?>" class="btn btn-primary btn-sm" style="flex: 1;">
                            查看详情
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- 创建应用弹窗 -->
<div id="createModal" class="modal">
    <div class="modal-content" style="max-width: 800px;">
        <div class="modal-header">
            <h5 class="modal-title">创建应用</h5>
            <button type="button" class="close-modal" onclick="hideCreateModal()">&times;</button>
        </div>
        <form id="createForm" enctype="multipart/form-data">
            <div class="modal-body">
                <input type="hidden" name="_token" value="<?= e($csrf_token) ?>">

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                    <!-- 左列：基本信息 -->
                    <div>
                        <h6 style="color: var(--color-primary); display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem; font-size: 0.875rem; font-weight: 600;">
                            <span class="iconify" data-icon="tabler:info-circle"></span> 基本信息
                        </h6>

                        <div class="form-group">
                            <label class="form-label">应用图标</label>
                            <div style="display: flex; align-items: flex-start; gap: 1rem;">
                                <div id="iconPreview" style="width: 4rem; height: 4rem; background-color: var(--bg-surface-hover); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0;">
                                    <span class="iconify" data-icon="tabler:photo-plus" style="font-size: 1.5rem; color: var(--text-light);"></span>
                                </div>
                                <div style="padding-top: 0.25rem;">
                                    <input type="file" name="app_icon" id="appIconInput" accept="image/*" style="display: none;">
                                    <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('appIconInput').click()">选择图片</button>
                                    <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem;">支持 JPG、PNG，建议 200x200</p>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">应用名称 <span style="color: #ef4444;">*</span></label>
                            <input type="text" name="app_name" class="form-control" placeholder="请输入应用名称" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">授权域名 <span style="color: #ef4444;">*</span></label>
                            <input type="text" name="domain" class="form-control" placeholder="example.com" required>
                            <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">不需要带 http:// 或 https://</p>
                        </div>

                        <div class="form-group">
                            <label class="form-label">回调地址</label>
                            <input type="text" name="callback_url" class="form-control" placeholder="https://example.com/callback">
                            <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">留空则使用默认回调</p>
                        </div>
                    </div>

                    <!-- 右列：登录方式 -->
                    <div>
                        <h6 style="color: var(--color-primary); display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem; font-size: 0.875rem; font-weight: 600;">
                            <span class="iconify" data-icon="tabler:login"></span> 登录方式
                        </h6>

                        <div class="form-group">
                            <label class="form-label">选择支持的登录平台</label>
                            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                                <?php
                                // 从套餐信息获取允许的平台
                                $allowedPlatforms = $packageInfo['platforms'] ?? [];
                                // 遍历后台配置的平台，只显示启用的
                                foreach ($platforms as $p):
                                    // 只显示后台启用的平台
                                    if ($p['status'] != 1) continue;
                                    $platformKey = $p['name']; // name字段是平台代码如qq
                                    $platformName = get_platform_name($platformKey); // 使用映射获取显示名称
                                    // 用户套餐不允许则禁用
                                    $isAllowed = in_array($platformKey, $allowedPlatforms);
                                ?>
                                <label class="platform-select-item <?= !$isAllowed ? 'disabled' : '' ?>" onclick="toggleAppPlatform(this)" title="<?= !$isAllowed ? '当前套餐不支持此平台' : '' ?>">
                                    <input type="checkbox" name="platforms[]" value="<?= e($platformKey) ?>" style="display: none;" <?= !$isAllowed ? 'disabled' : '' ?>>
                                    <?= e($platformName) ?>
                                </label>
                                <?php endforeach; ?>
                            </div>
                            <?php if (empty($allowedPlatforms)): ?>
                            <p style="font-size: 0.75rem; color: #f59e0b; margin-top: 0.5rem;">
                                <span class="iconify" data-icon="tabler:alert-circle" style="vertical-align: middle;"></span>
                                当前套餐暂无可用登录方式，请先升级套餐
                            </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="hideCreateModal()">取消</button>
                <button type="submit" class="btn btn-primary">创建应用</button>
            </div>
        </form>
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
    function showCreateModal() {
        document.getElementById('createModal').classList.add('show');
        document.querySelectorAll('#createModal .platform-select-item').forEach(label => {
            const checkbox = label.querySelector('input[type="checkbox"]');
            if (checkbox && !checkbox.disabled) {
                if (checkbox.checked) {
                    label.classList.add('active');
                } else {
                    label.classList.remove('active');
                }
            }
        });
    }

    function hideCreateModal() {
        document.getElementById('createModal').classList.remove('show');
    }

    document.getElementById('createModal').addEventListener('click', function(e) {
        if (e.target === this) hideCreateModal();
    });

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

    document.getElementById('appIconInput').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('iconPreview').innerHTML = '<img src="' + e.target.result + '" style="width: 100%; height: 100%; object-fit: cover;">';
            };
            reader.readAsDataURL(file);
        }
    });

    document.getElementById('createForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('/user/apps/create', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.code === 0) {
                toast('应用创建成功', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                toast(data.message, 'error');
            }
        });
    });
</script>

<?php $content = ob_get_clean(); ?>
<?php include ML_ROOT . '/views/layouts/user.php'; ?>
