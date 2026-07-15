<aside class="user-sidebar">
    <div class="sidebar-brand">
        <a href="/user/dashboard">
            <img src="<?= e($siteSettings['site_logo'] ?? '/assets/logo.png') ?>" alt="Logo" style="width: 1.5rem; height: 1.5rem; object-fit: contain;">
            <span><?= e($siteSettings['site_name'] ?? '星聚合登录') ?></span>
        </a>
    </div>
    <div class="sidebar-nav">
        <a href="/user/dashboard" class="sidebar-item <?= strpos($_SERVER['REQUEST_URI'], '/dashboard') !== false ? 'active' : '' ?>">
            <span class="iconify" data-icon="tabler:layout-dashboard" style="font-size: 1.25rem;"></span>
            <span>仪表盘</span>
        </a>
        <a href="/user/apps" class="sidebar-item <?= strpos($_SERVER['REQUEST_URI'], '/apps') !== false ? 'active' : '' ?>">
            <span class="iconify" data-icon="tabler:apps" style="font-size: 1.25rem;"></span>
            <span>我的应用</span>
        </a>
        <a href="/user/members" class="sidebar-item <?= strpos($_SERVER['REQUEST_URI'], '/members') !== false ? 'active' : '' ?>">
            <span class="iconify" data-icon="tabler:users" style="font-size: 1.25rem;"></span>
            <span>用户管理</span>
        </a>
        <a href="/user/verification" class="sidebar-item <?= strpos($_SERVER['REQUEST_URI'], '/verification') !== false ? 'active' : '' ?>">
            <span class="iconify" data-icon="tabler:id" style="font-size: 1.25rem;"></span>
            <span>身份认证</span>
        </a>
        <a href="/user/products" class="sidebar-item <?= strpos($_SERVER['REQUEST_URI'], '/products') !== false ? 'active' : '' ?>">
            <span class="iconify" data-icon="tabler:shopping-bag" style="font-size: 1.25rem;"></span>
            <span>产品订购</span>
        </a>

        <div class="sidebar-header">帮助</div>

        <a href="/user/docs" class="sidebar-item <?= strpos($_SERVER['REQUEST_URI'], '/docs') !== false ? 'active' : '' ?>">
            <span class="iconify" data-icon="tabler:book" style="font-size: 1.25rem;"></span>
            <span>接入文档</span>
        </a>
    </div>

    <!-- 套餐信息卡片 -->
    <?php
    $billingService = new \App\Services\BillingService();
    $packageInfo = $billingService->getSidebarPackageInfo($user['id']);
    ?>
    <a href="/user/products" class="sidebar-package-card <?= $packageInfo['is_expiring_soon'] ? 'expiring' : '' ?> <?= ($packageInfo['type'] === 'free' && !$packageInfo['free_enabled']) ? 'disabled' : '' ?>" title="点击查看详情或<?= $packageInfo['has_package'] ? '更改' : '升级' ?>套餐">
        <div class="package-header">
            <span class="package-badge <?= $packageInfo['type'] ?>">
                <?php
                $typeLabels = [
                    'free' => '永久授权',
                    'package' => '套餐包',
                    'account' => '账号包',
                    'quota' => '次数包'
                ];
                echo $typeLabels[$packageInfo['type']] ?? '套餐';
                ?>
            </span>
            <?php if ($packageInfo['type'] === 'free'): ?>
                <?php if ($packageInfo['free_enabled']): ?>
                <span class="status-badge active">无限制</span>
                <?php else: ?>
                <span class="status-badge inactive">禁用状态</span>
                <?php endif; ?>
            <?php elseif ($packageInfo['is_expiring_soon']): ?>
            <span class="expiring-badge">即将到期</span>
            <?php endif; ?>
        </div>
        
        <?php if ($packageInfo['percentage'] !== null): ?>
        <div class="package-progress">
            <div class="progress-bar">
                <div class="progress-fill <?= $packageInfo['percentage'] >= 95 ? 'danger' : ($packageInfo['percentage'] >= 80 ? 'warning' : '') ?>" 
                     style="width: <?= min(100, $packageInfo['percentage']) ?>%"></div>
            </div>
            <div class="progress-text">
                <?= number_format($packageInfo['used']) ?>/<?= number_format($packageInfo['total']) ?>
                <?= $packageInfo['unit'] ?? '' ?>
            </div>
        </div>
        <?php elseif ($packageInfo['type'] === 'free'): ?>
        <div class="package-progress">
            <div class="progress-text" style="text-align: center; padding: 0.5rem 0;">
                <strong><?= $packageInfo['unit'] ?? '无限制' ?></strong>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($packageInfo['platforms'])): ?>
        <?php
        $allPlatformLabels = [];
        foreach ($packageInfo['platforms'] as $p) {
            $allPlatformLabels[] = get_platform_name($p);
        }
        $fullPlatformsText = implode('、', $allPlatformLabels);
        $displayPlatforms = array_slice($packageInfo['platforms'], 0, 4);
        $hasMore = count($packageInfo['platforms']) > 4;
        ?>
        <div class="package-platforms-icons" title="支持：<?= $fullPlatformsText ?>">
            <?php foreach ($displayPlatforms as $p): ?>
            <?php 
            $platformName = get_platform_name($p);
            $platformIcon = get_platform_icon($p);
            ?>
            <span class="platform-icon-badge" title="<?= e($platformName) ?>">
                <img src="<?= $platformIcon ?>" alt="<?= e($platformName) ?>" style="width: 1rem; height: 1rem;">
            </span>
            <?php endforeach; ?>
            <?php if ($hasMore): ?>
            <span class="platform-more">+<?= count($packageInfo['platforms']) - 4 ?></span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <?php if ($packageInfo['days_remaining'] !== null): ?>
        <div class="package-expire">
            剩余 <?= $packageInfo['days_remaining'] ?> 天
        </div>
        <?php elseif ($packageInfo['type'] === 'free'): ?>
        <div class="package-expire" style="text-align: center; color: var(--success-color); font-weight: 500;">
            ✓ 永久授权已激活
        </div>
        <?php else: ?>
        <div class="package-expire upgrade-hint">
            <span class="iconify" data-icon="tabler:refresh" style="font-size: 0.75rem;"></span>
            更改套餐
        </div>
        <?php endif; ?>
    </a>

    <!-- 底部用户信息和操作 -->
    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="user-avatar">
                <span class="iconify" data-icon="tabler:user-filled" style="font-size: 1.25rem;"></span>
            </div>
            <div class="user-info">
                <div class="user-name"><?= e($user['username'] ?? '用户') ?></div>
                <div class="user-email"><?= e($user['email'] ?? '') ?></div>
            </div>
        </div>
        <div class="sidebar-actions">
            <a href="/" class="sidebar-action-btn" title="返回首页">
                <span class="iconify" data-icon="tabler:home" style="font-size: 1.125rem;"></span>
            </a>
            <a href="/user/profile" class="sidebar-action-btn" title="个人资料">
                <span class="iconify" data-icon="tabler:user-cog" style="font-size: 1.125rem;"></span>
            </a>
            <a href="/user/logout" class="sidebar-action-btn logout" title="退出登录">
                <span class="iconify" data-icon="tabler:logout" style="font-size: 1.125rem;"></span>
            </a>
        </div>
    </div>
</aside>