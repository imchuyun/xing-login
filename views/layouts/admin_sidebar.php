<?php $adminPath = get_admin_path(); ?>
<aside class="admin-sidebar">
    <div class="sidebar-brand">
        <a href="<?= admin_url() ?>">
            <img src="<?= e($siteSettings['site_logo'] ?? '/assets/logo.png') ?>" alt="Logo" style="width: 1.5rem; height: 1.5rem; object-fit: contain;">
            <span><?= e($siteSettings['site_name'] ?? '星聚合登录') ?></span>
        </a>
    </div>
    <div class="sidebar-nav">
        <a href="<?= admin_url() ?>" class="sidebar-item <?= $_SERVER['REQUEST_URI'] === admin_url() || $_SERVER['REQUEST_URI'] === admin_url() . '/' ? 'active' : '' ?>">
            <span class="iconify" data-icon="tabler:layout-dashboard" style="font-size: 1.25rem;"></span>
            <span>仪表盘</span>
        </a>
        <a href="<?= admin_url('users') ?>" class="sidebar-item <?= strpos($_SERVER['REQUEST_URI'], admin_url('users')) !== false ? 'active' : '' ?>">
            <span class="iconify" data-icon="tabler:user-cog" style="font-size: 1.25rem;"></span>
            <span>用户管理</span>
        </a>
        <a href="<?= admin_url('logs') ?>" class="sidebar-item <?= strpos($_SERVER['REQUEST_URI'], admin_url('logs')) !== false ? 'active' : '' ?>">
            <span class="iconify" data-icon="tabler:shield-check" style="font-size: 1.25rem;"></span>
            <span>授权日志</span>
        </a>
        <a href="<?= admin_url('products') ?>" class="sidebar-item <?= strpos($_SERVER['REQUEST_URI'], admin_url('products')) !== false ? 'active' : '' ?>">
            <span class="iconify" data-icon="tabler:shopping-bag" style="font-size: 1.25rem;"></span>
            <span>商品管理</span>
        </a>
        <a href="<?= admin_url('orders') ?>" class="sidebar-item <?= strpos($_SERVER['REQUEST_URI'], admin_url('orders')) !== false ? 'active' : '' ?>">
            <span class="iconify" data-icon="tabler:file-text" style="font-size: 1.25rem;"></span>
            <span>订单管理</span>
        </a>
        <a href="<?= admin_url('platforms') ?>" class="sidebar-item <?= strpos($_SERVER['REQUEST_URI'], admin_url('platforms')) !== false && strpos($_SERVER['REQUEST_URI'], admin_url('platforms/docs')) === false ? 'active' : '' ?>">
            <span class="iconify" data-icon="tabler:world" style="font-size: 1.25rem;"></span>
            <span>平台配置</span>
        </a>

        <div class="sidebar-header">系统</div>

        <a href="<?= admin_url('settings/site') ?>" class="sidebar-item <?= strpos($_SERVER['REQUEST_URI'], admin_url('settings')) !== false ? 'active' : '' ?>">
            <span class="iconify" data-icon="tabler:settings" style="font-size: 1.25rem;"></span>
            <span>系统设置</span>
        </a>
        <a href="<?= admin_url('integration/docs') ?>" class="sidebar-item <?= strpos($_SERVER['REQUEST_URI'], admin_url('integration/docs')) !== false ? 'active' : '' ?>">
            <span class="iconify" data-icon="tabler:book" style="font-size: 1.25rem;"></span>
            <span>接入文档</span>
        </a>
        <?php if (preg_match('/^[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/i', config('license.license_key', ''))): ?>
        <a href="<?= admin_url('support') ?>" class="sidebar-item <?= strpos($_SERVER['REQUEST_URI'], admin_url('support')) !== false ? 'active' : '' ?>">
            <span class="iconify" data-icon="tabler:headset" style="font-size: 1.25rem;"></span>
            <span>技术支持</span>
        </a>
        <?php endif; ?>
    </div>

    <!-- 底部用户信息和操作 -->
    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="user-avatar admin">
                <span class="iconify" data-icon="tabler:user-shield" style="font-size: 1.25rem;"></span>
            </div>
            <div class="user-info">
                <div class="user-name"><?= e($admin['username'] ?? '管理员') ?></div>
                <div class="user-role">系统管理员</div>
            </div>
        </div>
        <div class="sidebar-actions">
            <a href="/" class="sidebar-action-btn" title="返回首页">
                <span class="iconify" data-icon="tabler:home" style="font-size: 1.125rem;"></span>
            </a>
            <a href="<?= admin_url('profile') ?>" class="sidebar-action-btn" title="个人资料">
                <span class="iconify" data-icon="tabler:user-cog" style="font-size: 1.125rem;"></span>
            </a>
            <a href="<?= admin_url('logout') ?>" class="sidebar-action-btn logout" title="退出登录">
                <span class="iconify" data-icon="tabler:logout" style="font-size: 1.125rem;"></span>
            </a>
        </div>
    </div>
</aside>
