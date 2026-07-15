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
    <link rel="stylesheet" href="/assets/css/custom.css">
    <?php if (isset($extraCss) && is_array($extraCss)): ?>
        <?php foreach ($extraCss as $css): ?>
            <link rel="stylesheet" href="<?= $css ?>">
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Icons (本地化) -->
    <script src="/assets/js/icons.js?v=<?= time() ?>"></script>
</head>

<body>
