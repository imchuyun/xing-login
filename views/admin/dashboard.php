<?php $pageTitle = '管理后台';
ob_start(); ?>

<!-- 统计卡片 -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <div class="card">
        <div class="card-body" style="display: flex; align-items: center; padding: 1.5rem;">
            <div style="width: 3rem; height: 3rem; background-color: rgba(var(--primary-rgb), 0.1); border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; margin-right: 1rem;">
                <span class="iconify" data-icon="tabler:user" style="font-size: 1.5rem; color: var(--color-primary);"></span>
            </div>
            <div>
                <div style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.25rem;">总用户</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--text-main); line-height: 1.2;"><?= number_format($userCount) ?></div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="display: flex; align-items: center; padding: 1.5rem;">
            <div style="width: 3rem; height: 3rem; background-color: rgba(82, 196, 26, 0.1); border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; margin-right: 1rem;">
                <span class="iconify" data-icon="tabler:apps" style="font-size: 1.5rem; color: var(--color-success);"></span>
            </div>
            <div>
                <div style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.25rem;">总应用</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--text-main); line-height: 1.2;"><?= number_format($appCount) ?></div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="display: flex; align-items: center; padding: 1.5rem;">
            <div style="width: 3rem; height: 3rem; background-color: rgba(23, 162, 184, 0.1); border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; margin-right: 1rem;">
                <span class="iconify" data-icon="tabler:login" style="font-size: 1.5rem; color: #17a2b8;"></span>
            </div>
            <div>
                <div style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.25rem;">今日登录</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--text-main); line-height: 1.2;"><?= number_format($todayLogins) ?></div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="display: flex; align-items: center; padding: 1.5rem;">
            <div style="width: 3rem; height: 3rem; background-color: rgba(111, 66, 193, 0.1); border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; margin-right: 1rem;">
                <span class="iconify" data-icon="tabler:history" style="font-size: 1.5rem; color: #6f42c1;"></span>
            </div>
            <div>
                <div style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.25rem;">总登录次数</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--text-main); line-height: 1.2;"><?= number_format($totalLogins) ?></div>
            </div>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1.5rem;">
    <!-- 最近用户 -->
    <div class="card" style="height: 100%;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 class="card-title">最近注册用户</h3>
            <a href="<?= admin_url('users') ?>" class="btn btn-outline btn-sm" style="display: flex; align-items: center; gap: 0.25rem;">
                查看全部 <span class="iconify" data-icon="tabler:chevron-right"></span>
            </a>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>用户名</th>
                            <th>邮箱</th>
                            <th>注册时间</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentUsers as $u): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <div style="width: 2rem; height: 2rem; background-color: var(--bg-surface-hover); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--color-primary);">
                                            <span class="iconify" data-icon="tabler:user"></span>
                                        </div>
                                        <span style="font-weight: 500; color: var(--text-main);"><?= e($u['username']) ?></span>
                                    </div>
                                </td>
                                <td style="color: var(--text-muted);"><?= e($u['email']) ?></td>
                                <td style="color: var(--text-muted); font-size: 0.875rem;"><?= e($u['time']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 最近登录 -->
    <div class="card" style="height: 100%;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 class="card-title">最近登录记录</h3>
            <a href="<?= admin_url('logs') ?>" class="btn btn-outline btn-sm" style="display: flex; align-items: center; gap: 0.25rem;">
                查看全部 <span class="iconify" data-icon="tabler:chevron-right"></span>
            </a>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>应用</th>
                            <th>平台</th>
                            <th>用户</th>
                            <th>时间</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentLogs as $log): ?>
                            <tr>
                                <td style="font-weight: 500; color: var(--text-main);"><?= e($log['app_name'] ?? '-') ?></td>
                                <td>
                                    <?php
                                    $platform = $log['type'] ?? $log['platform'] ?? '';
                                    $platformName = get_platform_name($platform);
                                    $iconPath = get_platform_icon($platform);
                                    ?>
                                    <div style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.25rem 0.625rem 0.25rem 0.25rem; background-color: var(--bg-surface-hover); border-radius: 0.375rem;">
                                        <img src="<?= e($iconPath) ?>" alt="<?= e($platformName) ?>" style="width: 1.5rem; height: 1.5rem;">
                                        <span style="font-size: 0.8125rem; font-weight: 500; color: var(--text-main);"><?= e($platformName) ?></span>
                                    </div>
                                </td>
                                <td><?= e($log['nickname'] ?: '-') ?></td>
                                <td style="color: var(--text-muted); font-size: 0.875rem;"><?= e($log['time'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include ML_ROOT . '/views/layouts/admin.php'; ?>