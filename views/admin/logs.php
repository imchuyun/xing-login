<?php $pageTitle = '授权日志';
ob_start(); ?>

<div class="card">
    <div class="card-body" style="padding: 0;">
        <?php if (empty($logs)): ?>
            <div style="text-align: center; padding: 4rem 2rem;">
                <div style="width: 4rem; height: 4rem; background-color: var(--bg-surface-hover); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                    <span class="iconify" data-icon="tabler:shield-check" style="font-size: 2rem; color: var(--text-muted);"></span>
                </div>
                <p style="color: var(--text-muted);">暂无授权记录</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>授权用户</th>
                            <th>平台</th>
                            <th>OpenID</th>
                            <th>所属用户/应用</th>
                            <th>IP</th>
                            <th>授权时间</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <?php
                            $platform = $log['type'] ?? $log['platform'] ?? '';
                            $platformName = get_platform_name($platform);
                            $iconPath = get_platform_icon($platform);
                            $gender = $log['gender'] ?? 0;
                            $genderIcon = $gender == 1 ? '♂' : ($gender == 2 ? '♀' : '');
                            $genderColor = $gender == 1 ? '#3b82f6' : ($gender == 2 ? '#ec4899' : '');
                            ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <?php if (!empty($log['avatar'])): ?>
                                            <img src="<?= e(ensure_https($log['avatar'])) ?>" alt="" style="width: 2.5rem; height: 2.5rem; border-radius: 50%; object-fit: cover;">
                                        <?php else: ?>
                                            <div style="width: 2.5rem; height: 2.5rem; border-radius: 50%; background-color: var(--bg-surface-hover); display: flex; align-items: center; justify-content: center;">
                                                <span class="iconify" data-icon="tabler:user" style="font-size: 1.25rem; color: var(--text-light);"></span>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div style="font-weight: 500; color: var(--text-main); display: flex; align-items: center; gap: 0.25rem;">
                                                <?= e($log['nickname'] ?: '未知用户') ?>
                                                <?php if ($genderIcon): ?>
                                                    <span style="color: <?= $genderColor ?>; font-size: 0.875rem;"><?= $genderIcon ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td style="white-space: nowrap;">
                                    <div style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.25rem 0.625rem 0.25rem 0.25rem; background-color: var(--bg-surface-hover); border-radius: 0.375rem; white-space: nowrap;">
                                        <img src="<?= e($iconPath) ?>" alt="<?= e($platformName) ?>" style="width: 1.5rem; height: 1.5rem; flex-shrink: 0;">
                                        <span style="font-size: 0.8125rem; font-weight: 500; color: var(--text-main); white-space: nowrap;"><?= e($platformName) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <code style="font-size: 0.75rem; background: var(--bg-surface-hover); padding: 0.25rem 0.5rem; border-radius: 4px; color: var(--text-muted);">
                                        <?= e(strlen($log['open_id']) > 20 ? substr($log['open_id'], 0, 20) . '...' : $log['open_id']) ?>
                                    </code>
                                </td>
                                <td>
                                    <div style="font-size: 0.875rem;">
                                        <div style="color: var(--text-main); font-weight: 500;"><?= e($log['username'] ?? '-') ?></div>
                                        <div style="color: var(--text-muted); font-size: 0.75rem;">
                                            <?= e($log['app_name'] ?? '-') ?>
                                            <?php if (!empty($log['application_id'])): ?>
                                                <span style="color: var(--text-muted);">(<?= e($log['application_id']) ?>)</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td style="color: var(--text-muted); font-size: 0.875rem;"><?= e($log['ip'] ?? '-') ?></td>
                                <td style="color: var(--text-muted); font-size: 0.875rem; white-space: nowrap;"><?= e($log['time'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.5rem; border-top: 1px solid var(--border-color);">
                    <span style="font-size: 0.875rem; color: var(--text-muted);">共 <?= number_format($total) ?> 条记录</span>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page - 1 ?>" class="btn btn-outline btn-sm">上一页</a>
                        <?php endif; ?>
                        <span style="font-size: 0.875rem; color: var(--text-muted);">第 <?= $page ?> / <?= $totalPages ?> 页</span>
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?= $page + 1 ?>" class="btn btn-outline btn-sm">下一页</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include ML_ROOT . '/views/layouts/admin.php'; ?>
