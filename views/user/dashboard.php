<?php $pageTitle = '仪表盘';
ob_start(); 
$remainDays = 0;
$usagePercentage = 0;
$isExpiringSoon = false;
$isQuotaLow = false;
$isAccountLow = false;

if (!empty($currentPackage)) {
    $remainDays = max(0, (int)((strtotime($currentPackage['expire_time']) - time()) / 86400));
    $isExpiringSoon = $remainDays <= 7 && $remainDays > 0;
    
    if ($currentPackage['type'] === 'quota' && !empty($usageStats['package']['quota'])) {
        $usagePercentage = $usageStats['package']['quota']['percentage'] ?? 0;
        $isQuotaLow = $usagePercentage >= 90;
    } elseif ($currentPackage['type'] === 'account' && !empty($usageStats['package']['accounts'])) {
        $usagePercentage = $usageStats['package']['accounts']['percentage'] ?? 0;
        $isAccountLow = $usagePercentage >= 90;
    }
}
?>

<!-- 到期/额度提醒 -->
<?php if (!empty($currentPackage) && ($isExpiringSoon || $isQuotaLow || $isAccountLow)): ?>
<div class="card mb-4" style="border-left: 4px solid var(--color-warning); background: rgba(250, 140, 22, 0.05);">
    <div class="card-body" style="display: flex; align-items: center; gap: 1rem;">
        <div style="width: 2.5rem; height: 2.5rem; background: rgba(250, 140, 22, 0.15); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
            <span class="iconify" data-icon="tabler:alert-triangle" style="font-size: 1.25rem; color: var(--color-warning);"></span>
        </div>
        <div style="flex: 1;">
            <?php if ($isExpiringSoon): ?>
            <p style="margin: 0; font-weight: 500; color: var(--text-main);">套餐即将到期</p>
            <p style="margin: 0; font-size: 0.875rem; color: var(--text-muted);">您的套餐将在 <?= $remainDays ?> 天后到期，请及时续费以免影响使用。</p>
            <?php elseif ($isQuotaLow): ?>
            <p style="margin: 0; font-weight: 500; color: var(--text-main);">调用次数即将用完</p>
            <p style="margin: 0; font-size: 0.875rem; color: var(--text-muted);">您的调用次数已使用 <?= number_format($usagePercentage, 1) ?>%，剩余 <?= number_format($usageStats['package']['quota']['remaining'] ?? 0) ?> 次。</p>
            <?php elseif ($isAccountLow): ?>
            <p style="margin: 0; font-weight: 500; color: var(--text-main);">授权用户数即将达到上限</p>
            <p style="margin: 0; font-size: 0.875rem; color: var(--text-muted);">您的授权用户数已使用 <?= number_format($usagePercentage, 1) ?>%，剩余 <?= number_format($usageStats['package']['accounts']['remaining'] ?? 0) ?> 个名额。</p>
            <?php endif; ?>
        </div>
        <a href="/user/products" class="btn btn-warning btn-sm">立即续费</a>
    </div>
</div>
<?php endif; ?>

<!-- 统计卡片 -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <div class="card">
        <div class="card-body" style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.5rem;">我的应用</p>
                <p style="font-size: 1.5rem; font-weight: 700; color: var(--text-main); margin: 0;"><?= $appCount ?></p>
            </div>
            <div style="width: 3rem; height: 3rem; background-color: rgba(var(--primary-rgb), 0.1); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center;">
                <span class="iconify" data-icon="tabler:apps" style="font-size: 1.5rem; color: var(--color-primary);"></span>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body" style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.5rem;">今日调用</p>
                <p style="font-size: 1.5rem; font-weight: 700; color: var(--text-main); margin: 0;"><?= number_format($usageStats['calls']['today'] ?? $todayCalls) ?></p>
            </div>
            <div style="width: 3rem; height: 3rem; background-color: rgba(82, 196, 26, 0.1); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center;">
                <span class="iconify" data-icon="tabler:chart-line" style="font-size: 1.5rem; color: var(--color-success);"></span>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body" style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.5rem;">本月调用</p>
                <p style="font-size: 1.5rem; font-weight: 700; color: var(--text-main); margin: 0;"><?= number_format($usageStats['calls']['this_month'] ?? $totalCalls) ?></p>
            </div>
            <div style="width: 3rem; height: 3rem; background-color: rgba(19, 194, 194, 0.1); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center;">
                <span class="iconify" data-icon="tabler:history" style="font-size: 1.5rem; color: #13c2c2;"></span>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body" style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.5rem;">账户余额</p>
                <p style="font-size: 1.5rem; font-weight: 700; color: var(--text-main); margin: 0;">¥<?= number_format($user['balance'], 2) ?></p>
            </div>
            <div style="width: 3rem; height: 3rem; background-color: rgba(250, 140, 22, 0.1); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center;">
                <span class="iconify" data-icon="tabler:wallet" style="font-size: 1.5rem; color: var(--color-warning);"></span>
            </div>
        </div>
    </div>
</div>

<!-- 当前套餐 -->
<?php if (!empty($currentPackage)): ?>
<?php 
$packageType = $currentPackage['type'] ?? 'package';
$typeLabels = ['package' => '订阅套餐', 'quota' => '次数包', 'account' => '账号包'];
$typeIcons = ['package' => 'tabler:crown', 'quota' => 'tabler:stack-2', 'account' => 'tabler:star-filled'];
$typeGradients = [
    'package' => 'linear-gradient(135deg, #667eea, #764ba2)',
    'quota' => 'linear-gradient(135deg, #11998e, #38ef7d)',
    'account' => 'linear-gradient(135deg, #f093fb, #f5576c)'
];
?>
<div class="card mb-4">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3 class="card-title">当前套餐</h3>
        <a href="/user/products" style="font-size: 0.875rem; color: var(--color-primary);">查看更多套餐</a>
    </div>
    <div class="card-body">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem;">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <div style="width: 3rem; height: 3rem; background: <?= $typeGradients[$packageType] ?? $typeGradients['package'] ?>; border-radius: 0.75rem; display: flex; align-items: center; justify-content: center;">
                    <span class="iconify" data-icon="<?= $typeIcons[$packageType] ?? $typeIcons['package'] ?>" style="font-size: 1.5rem; color: white;"></span>
                </div>
                <div>
                    <p style="font-size: 1.125rem; font-weight: 600; color: var(--text-main); margin: 0;"><?= e($currentPackage['product_name']) ?></p>
                    <span class="badge <?= $packageType === 'package' ? 'badge-primary' : ($packageType === 'account' ? 'badge-info' : 'badge-success') ?>" style="margin-top: 0.25rem;">
                        <?= $typeLabels[$packageType] ?? '套餐' ?>
                    </span>
                </div>
            </div>
            <div style="text-align: right;">
                <?php if ($remainDays > 7): ?>
                <span class="badge badge-success" style="font-size: 0.875rem; padding: 0.5rem 1rem;">正常</span>
                <?php elseif ($remainDays > 0): ?>
                <span class="badge badge-warning" style="font-size: 0.875rem; padding: 0.5rem 1rem;">即将到期</span>
                <?php else: ?>
                <span class="badge badge-danger" style="font-size: 0.875rem; padding: 0.5rem 1rem;">已过期</span>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- 使用进度条 (次数包和账号包) -->
        <?php if ($packageType === 'quota' && !empty($usageStats['package']['quota'])): ?>
        <?php 
        $quotaUsed = $usageStats['package']['quota']['used'] ?? 0;
        $quotaTotal = $usageStats['package']['quota']['total'] ?? 0;
        $quotaRemaining = $usageStats['package']['quota']['remaining'] ?? 0;
        $quotaPercentage = $usageStats['package']['quota']['percentage'] ?? 0;
        $progressColor = $quotaPercentage >= 90 ? 'var(--color-danger)' : ($quotaPercentage >= 70 ? 'var(--color-warning)' : 'var(--color-success)');
        ?>
        <div style="background: var(--bg-surface-hover); border-radius: var(--radius-md); padding: 1rem; margin-bottom: 1rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                <span style="font-size: 0.875rem; color: var(--text-muted);">调用次数使用情况</span>
                <span style="font-size: 0.875rem; font-weight: 600; color: var(--text-main);"><?= number_format($quotaUsed) ?> / <?= number_format($quotaTotal) ?></span>
            </div>
            <div style="height: 8px; background: var(--bg-surface); border-radius: 4px; overflow: hidden;">
                <div style="height: 100%; width: <?= min(100, $quotaPercentage) ?>%; background: <?= $progressColor ?>; border-radius: 4px; transition: width 0.3s ease;"></div>
            </div>
            <div style="display: flex; justify-content: space-between; margin-top: 0.5rem;">
                <span style="font-size: 0.75rem; color: var(--text-muted);">剩余 <?= number_format($quotaRemaining) ?> 次</span>
                <span style="font-size: 0.75rem; color: <?= $progressColor ?>;"><?= number_format($quotaPercentage, 1) ?>%</span>
            </div>
        </div>
        <?php elseif ($packageType === 'account' && !empty($usageStats['package']['accounts'])): ?>
        <?php 
        $accountUsed = $usageStats['package']['accounts']['used'] ?? 0;
        $accountTotal = $usageStats['package']['accounts']['total'] ?? 0;
        $accountRemaining = $usageStats['package']['accounts']['remaining'] ?? 0;
        $accountPercentage = $usageStats['package']['accounts']['percentage'] ?? 0;
        $progressColor = $accountPercentage >= 90 ? 'var(--color-danger)' : ($accountPercentage >= 70 ? 'var(--color-warning)' : 'var(--color-success)');
        ?>
        <div style="background: var(--bg-surface-hover); border-radius: var(--radius-md); padding: 1rem; margin-bottom: 1rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                <span style="font-size: 0.875rem; color: var(--text-muted);">授权用户数使用情况</span>
                <span style="font-size: 0.875rem; font-weight: 600; color: var(--text-main);"><?= number_format($accountUsed) ?> / <?= number_format($accountTotal) ?></span>
            </div>
            <div style="height: 8px; background: var(--bg-surface); border-radius: 4px; overflow: hidden;">
                <div style="height: 100%; width: <?= min(100, $accountPercentage) ?>%; background: <?= $progressColor ?>; border-radius: 4px; transition: width 0.3s ease;"></div>
            </div>
            <div style="display: flex; justify-content: space-between; margin-top: 0.5rem;">
                <span style="font-size: 0.75rem; color: var(--text-muted);">剩余 <?= number_format($accountRemaining) ?> 个名额</span>
                <span style="font-size: 0.75rem; color: <?= $progressColor ?>;"><?= number_format($accountPercentage, 1) ?>%</span>
            </div>
        </div>
        <?php endif; ?>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 1rem;">
            <?php if ($packageType === 'package'): ?>
            <!-- 套餐包: 显示支持的平台 -->
            <?php 
            $platforms = [];
            if (!empty($currentPackage['platforms'])) {
                $platforms = json_decode($currentPackage['platforms'], true) ?: [];
            }
            // 使用 get_platform_name() helper 函数获取平台名称
            ?>
            <div style="background: var(--bg-surface-hover); border-radius: var(--radius-md); padding: 1rem; text-align: center;">
                <p style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">支持平台</p>
                <p style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin: 0;"><?= count($platforms) ?></p>
                <p style="font-size: 0.75rem; color: var(--text-muted); margin: 0;">个</p>
            </div>
            <div style="background: var(--bg-surface-hover); border-radius: var(--radius-md); padding: 1rem; text-align: center;">
                <p style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">本周调用</p>
                <p style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin: 0;"><?= number_format($usageStats['calls']['this_week'] ?? 0) ?></p>
                <p style="font-size: 0.75rem; color: var(--text-muted); margin: 0;">次</p>
            </div>
            <?php elseif ($packageType === 'quota'): ?>
            <!-- 次数包: 显示剩余次数 -->
            <div style="background: var(--bg-surface-hover); border-radius: var(--radius-md); padding: 1rem; text-align: center;">
                <p style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">剩余次数</p>
                <p style="font-size: 1.5rem; font-weight: 700; color: var(--text-main); margin: 0;"><?= number_format($usageStats['package']['quota']['remaining'] ?? 0) ?></p>
                <p style="font-size: 0.75rem; color: var(--text-muted); margin: 0;">次</p>
            </div>
            <div style="background: var(--bg-surface-hover); border-radius: var(--radius-md); padding: 1rem; text-align: center;">
                <p style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">已使用</p>
                <p style="font-size: 1.5rem; font-weight: 700; color: var(--text-main); margin: 0;"><?= number_format($usageStats['package']['quota']['used'] ?? 0) ?></p>
                <p style="font-size: 0.75rem; color: var(--text-muted); margin: 0;">次</p>
            </div>
            <?php elseif ($packageType === 'account'): ?>
            <!-- 账号包: 显示剩余名额 -->
            <div style="background: var(--bg-surface-hover); border-radius: var(--radius-md); padding: 1rem; text-align: center;">
                <p style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">剩余名额</p>
                <p style="font-size: 1.5rem; font-weight: 700; color: var(--text-main); margin: 0;"><?= number_format($usageStats['package']['accounts']['remaining'] ?? 0) ?></p>
                <p style="font-size: 0.75rem; color: var(--text-muted); margin: 0;">个</p>
            </div>
            <div style="background: var(--bg-surface-hover); border-radius: var(--radius-md); padding: 1rem; text-align: center;">
                <p style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">已授权</p>
                <p style="font-size: 1.5rem; font-weight: 700; color: var(--text-main); margin: 0;"><?= number_format($usageStats['package']['accounts']['used'] ?? 0) ?></p>
                <p style="font-size: 0.75rem; color: var(--text-muted); margin: 0;">个</p>
            </div>
            <?php endif; ?>
            <div style="background: var(--bg-surface-hover); border-radius: var(--radius-md); padding: 1rem; text-align: center;">
                <p style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">剩余天数</p>
                <p style="font-size: 1.5rem; font-weight: 700; color: <?= $remainDays <= 7 ? 'var(--color-warning)' : 'var(--color-primary)' ?>; margin: 0;"><?= $remainDays ?></p>
                <p style="font-size: 0.75rem; color: var(--text-muted); margin: 0;">天</p>
            </div>
            <div style="background: var(--bg-surface-hover); border-radius: var(--radius-md); padding: 1rem; text-align: center;">
                <p style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">到期时间</p>
                <p style="font-size: 1rem; font-weight: 600; color: var(--text-main); margin: 0;"><?= date('Y-m-d', strtotime($currentPackage['expire_time'])) ?></p>
            </div>
        </div>
        
        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border-color); font-size: 0.75rem; color: var(--text-muted); display: flex; align-items: center; gap: 0.25rem;">
            <span class="iconify" data-icon="tabler:clock"></span>
            购买时间：<?= date('Y-m-d H:i', strtotime($currentPackage['time'])) ?>
        </div>
    </div>
</div>
<?php else: ?>
<!-- 无套餐提示 -->
<div class="card mb-4" style="border: 2px dashed var(--border-color);">
    <div class="card-body" style="text-align: center; padding: 3rem 2rem;">
        <div style="display: flex; justify-content: center; margin-bottom: 1rem;">
            <span class="iconify" data-icon="tabler:gift" style="font-size: 3rem; color: var(--text-light);"></span>
        </div>
        <p style="font-size: 1.125rem; font-weight: 500; color: var(--text-main); margin-bottom: 0.5rem;">您还没有购买套餐</p>
        <p style="color: var(--text-muted); margin-bottom: 1.5rem;">购买套餐后可享受更多登录平台和更高调用额度</p>
        <a href="/user/products" class="btn btn-primary">立即购买</a>
    </div>
</div>
<?php endif; ?>

<!-- 最近登录记录 -->
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3 class="card-title">最近登录记录</h3>
        <a href="/user/logs" style="font-size: 0.875rem; color: var(--color-primary);">查看全部</a>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($recentLogs)): ?>
            <div style="text-align: center; padding: 3rem;">
                <span class="iconify" data-icon="tabler:file-text" style="font-size: 3rem; color: var(--text-light); margin-bottom: 1rem;"></span>
                <p style="color: var(--text-muted);">暂无登录记录</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>登录方式</th>
                            <th>设备/浏览器</th>
                            <th>IP</th>
                            <th>时间</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentLogs as $log): ?>
                            <tr>
                                <td>
                                    <?php
                                    $loginType = $log['type'] ?? 'password';
                                    $badgeClass = get_platform_badge_class($loginType);
                                    $iconImg = get_platform_icon($loginType);
                                    $typeName = ($loginType === 'password') ? '密码登录' : get_platform_name($loginType) . '登录';
                                    ?>
                                    <span class="badge <?= $badgeClass ?>" style="display: inline-flex; align-items: center; gap: 0.25rem;">
                                        <?php if ($iconImg): ?>
                                        <img src="<?= $iconImg ?>" alt="" style="width: 1rem; height: 1rem;">
                                        <?php else: ?>
                                        <span class="iconify" data-icon="tabler:lock"></span>
                                        <?php endif; ?>
                                        <?= e($typeName) ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <?php
                                        $deviceIcon = 'tabler:device-mobile';
                                        if (($log['device'] ?? '') === 'mobile') {
                                            $deviceIcon = 'tabler:device-mobile';
                                        } elseif (($log['device'] ?? '') === 'tablet') {
                                            $deviceIcon = 'tabler:device-mobile';
                                        }
                                        ?>
                                        <span class="iconify" data-icon="<?= $deviceIcon ?>" style="color: var(--text-muted);"></span>
                                        <span><?= e(($log['os'] ?? '-') . ' / ' . ($log['browser'] ?? '-')) ?></span>
                                    </div>
                                </td>
                                <td style="color: var(--text-muted);"><?= e($log['ip'] ?? '-') ?></td>
                                <td style="color: var(--text-muted);"><?= e($log['time'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
</script>

<?php $content = ob_get_clean(); ?>
<?php include ML_ROOT . '/views/layouts/user.php'; ?>