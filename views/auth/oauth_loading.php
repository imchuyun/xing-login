<?php $pageTitle = '正在跳转'; ?>
<?php include ML_ROOT . '/views/layouts/header.php'; ?>

<div style="min-height: calc(100vh - 300px); display: flex; align-items: center; justify-content: center; padding: 4rem 1rem; background-color: var(--bg-body);">
    <div class="card" style="width: 100%; max-width: 420px; border: none; box-shadow: var(--shadow-lg);">
        <div class="card-body" style="padding: 3rem 2rem; text-align: center;">
            <div style="display: inline-block; width: 48px; height: 48px; border: 3px solid var(--border-color); border-top-color: var(--color-primary); border-radius: 50%; animation: spin 1s linear infinite; margin-bottom: 1.5rem;"></div>

            <h1 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem;">正在跳转到授权页面</h1>
            <p style="color: var(--text-muted); font-size: 0.875rem;">请稍候，正在连接 <?= e(ucfirst($platform ?? '')) ?> 授权服务...</p>
        </div>
    </div>
</div>

<style>
    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }
</style>

<?php include ML_ROOT . '/views/layouts/footer.php'; ?>