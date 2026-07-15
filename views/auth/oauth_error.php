<?php $pageTitle = '登录失败'; ?>
<?php include ML_ROOT . '/views/layouts/header.php'; ?>

<div style="min-height: calc(100vh - 300px); display: flex; align-items: center; justify-content: center; padding: 4rem 1rem; background-color: var(--bg-body);">
    <div class="card" style="width: 100%; max-width: 420px; border: none; box-shadow: var(--shadow-lg);">
        <div class="card-body" style="padding: 3rem 2rem; text-align: center;">
            <div style="width: 4rem; height: 4rem; background-color: var(--bg-surface-hover); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                <span class="iconify" data-icon="tabler:alert-triangle-filled" style="font-size: 2rem; color: var(--color-error);"></span>
            </div>

            <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--text-main); margin-bottom: 1rem;">登录失败</h1>

            <p style="color: var(--text-muted); margin-bottom: 2rem;"><?= e($message ?? '发生未知错误') ?></p>

            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <a href="/user/login" class="btn btn-primary btn-block" style="padding: 0.75rem;">返回登录</a>
                <a href="/" class="btn btn-outline btn-block" style="padding: 0.75rem;">返回首页</a>
            </div>
        </div>
    </div>
</div>

<?php include ML_ROOT . '/views/layouts/footer.php'; ?>