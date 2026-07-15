<!DOCTYPE html>
<html lang="zh-CN" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? ($siteSettings['site_name'] ?? '星聚合登录')) ?> - <?= e($siteSettings['site_name'] ?? '星聚合登录') ?></title>
    <link rel="icon" href="<?= e($siteSettings['site_favicon'] ?? '/assets/favicon.ico') ?>" type="image/x-icon">

    <!-- CSS -->
    <link rel="stylesheet" href="/assets/css/variables.css">
    <link rel="stylesheet" href="/assets/css/base.css">
    <link rel="stylesheet" href="/assets/css/components.css">
    <link rel="stylesheet" href="/assets/css/custom.css"> <!-- For page specific overrides -->
    <?php if (isset($extraCss) && is_array($extraCss)): ?>
        <?php foreach ($extraCss as $css): ?>
            <link rel="stylesheet" href="<?= $css ?>">
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Icons (本地化) -->
    <script src="/assets/js/icons.js?v=<?= time() ?>"></script>
</head>

<body>
    <!-- Navigation -->
    <nav style="background-color: var(--bg-surface); border-bottom: 1px solid var(--border-color); position: sticky; top: 0; z-index: 100;">
        <div class="container" style="height: 64px; display: flex; align-items: center; justify-content: space-between;">
            <!-- Logo -->
            <a href="/" style="display: flex; align-items: center; gap: 0.5rem; font-weight: 700; font-size: 1.25rem; color: var(--text-main);">
                <img src="<?= e($siteSettings['site_logo'] ?? '/assets/logo.png') ?>" alt="Logo" style="width: 1.75rem; height: 1.75rem; object-fit: contain;">
                <span><?= e($siteSettings['site_name'] ?? '星聚合登录') ?></span>
            </a>

            <!-- Desktop Menu -->
            <div class="hidden" style="display: none;">
                <!-- Mobile Menu Toggle (Implemented in JS) -->
            </div>

            <div style="display: flex; align-items: center; gap: 2rem;" class="desktop-menu">
                <div style="display: flex; gap: 1.5rem;">
                    <a href="/document" class="text-sm" style="color: var(--text-muted);">接入文档</a>
                </div>

                <div style="display: flex; align-items: center; gap: 1rem;">
                    <?php if (isset($user) && $user): ?>
                        <a href="/user/dashboard" class="btn btn-outline">控制台</a>
                        <?php if ($user['role'] === 'admin'): ?>
                            <a href="<?= admin_url() ?>" class="btn btn-outline">管理后台</a>
                        <?php endif; ?>
                        <a href="/user/logout" class="btn btn-outline" style="border: none; color: var(--text-muted);">退出</a>
                    <?php else: ?>
                        <a href="/user/login" class="text-sm" style="color: var(--text-main); font-weight: 500;">登录</a>
                        <a href="/user/reg" class="btn btn-primary">免费注册</a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Mobile Menu Button -->
            <button id="menu-toggle" class="btn btn-outline" style="display: none; padding: 0.5rem;">
                <span class="iconify" data-icon="tabler:menu-2" style="font-size: 1.5rem;"></span>
            </button>
        </div>

        <!-- Mobile Menu (Hidden by default) -->
        <div id="mobile-menu" class="hidden" style="border-top: 1px solid var(--border-color); padding: 1rem; background-color: var(--bg-surface);">
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <a href="/document" style="color: var(--text-main);">接入文档</a>
                <hr style="border: 0; border-top: 1px solid var(--border-color);"><?php if (isset($user) && $user): ?>
                    <a href="/user/dashboard" style="color: var(--text-main);">控制台</a>
                    <a href="/user/logout" style="color: var(--text-muted);">退出</a>
                <?php else: ?>
                    <a href="/user/login" style="color: var(--text-main);">登录</a>
                    <a href="/user/reg" class="btn btn-primary text-center">免费注册</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <style>
        @media (max-width: 768px) {
            .desktop-menu {
                display: none !important;
            }
                display: inline-flex !important;
            }
        }
    </style>

    <!-- Main Content -->
    <main style="flex: 1;">