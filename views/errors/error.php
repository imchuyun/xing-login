<?php $pageTitle = 'Error'; ?>
<?php include ML_ROOT . '/views/layouts/header.php'; ?>

<div class="container" style="padding: 8rem 0; text-align: center;">
    <div style="margin-bottom: 2rem;">
        <span class="iconify" data-icon="tabler:alert-triangle" style="font-size: 8rem; color: var(--color-error); opacity: 0.5;"></span>
    </div>
    <h1 style="font-size: 3rem; margin-bottom: 1rem; color: var(--text-main);">Error <?= $code ?? '' ?></h1>
    <p style="font-size: 1.5rem; color: var(--text-muted); margin-bottom: 3rem;">
        <?= e($message ?? '发生了一个错误') ?>
    </p>
    <a href="/" class="btn btn-primary" style="padding: 0.8rem 2.5rem; font-size: 1.125rem;">
        返回首页
    </a>
</div>

<?php include ML_ROOT . '/views/layouts/footer.php'; ?>