<?php $pageTitle = '身份认证'; ob_start(); ?>

<style>
.verification-disabled-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: calc(100vh - 200px);
}
</style>

<div class="verification-disabled-wrapper">
    <div class="card" style="max-width: 500px; width: 100%; text-align: center;">
        <div class="card-body" style="padding: 3rem 2rem;">
            <div style="width: 80px; height: 80px; background: var(--bg-surface-hover); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                <span class="iconify" data-icon="tabler:shield-check" style="font-size: 2.5rem; color: var(--text-muted);"></span>
            </div>
            <h2 style="font-size: 1.5rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.75rem;">身份认证功能暂未开放</h2>
            <p style="color: var(--text-muted); margin-bottom: 2rem;">管理员尚未启用身份认证功能，请稍后再试</p>
            <a href="/user/dashboard" class="btn btn-primary" style="padding: 0.75rem 2rem;">
                <span class="iconify" data-icon="tabler:arrow-left" style="margin-right: 0.5rem;"></span>
                返回控制台
            </a>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include ML_ROOT . '/views/layouts/user.php'; ?>
