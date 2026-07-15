<?php $pageTitle = '授权测试结果'; ?>
<?php include ML_ROOT . '/views/layouts/header.php'; ?>

<div style="min-height: calc(100vh - 300px); display: flex; align-items: center; justify-content: center; padding: 4rem 1rem; background-color: var(--bg-body);">
    <div class="card" style="width: 100%; max-width: 480px; border: none; box-shadow: var(--shadow-lg);">
        <div class="card-body" style="padding: 2rem;">
            <div style="text-align: center; margin-bottom: 1.5rem;">
                <div style="width: 64px; height: 64px; background: linear-gradient(135deg, #22c55e, #16a34a); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                    <span class="iconify" data-icon="tabler:check" style="font-size: 2rem; color: white;"></span>
                </div>
                <h1 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem;">授权测试成功</h1>
                <p style="color: var(--text-muted); font-size: 0.875rem;"><?= e(ucfirst($platform ?? '')) ?> 平台配置正确</p>
            </div>

            <div style="background-color: var(--bg-surface-hover); border-radius: var(--radius-md); padding: 1rem; margin-bottom: 1.5rem;">
                <h3 style="font-size: 0.875rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.75rem;">获取到的用户信息</h3>
                <div style="display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.875rem;">
                    <?php if (!empty($userInfo['openid'])): ?>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-muted);">OpenID:</span>
                        <span style="color: var(--text-main); word-break: break-all; text-align: right; max-width: 60%;"><?= e($userInfo['openid']) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($userInfo['unionid'])): ?>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-muted);">UnionID:</span>
                        <span style="color: var(--text-main); word-break: break-all; text-align: right; max-width: 60%;"><?= e($userInfo['unionid']) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($userInfo['nickname'])): ?>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-muted);">昵称:</span>
                        <span style="color: var(--text-main);"><?= e($userInfo['nickname']) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($userInfo['avatar'])): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: var(--text-muted);">头像:</span>
                        <img src="<?= e($userInfo['avatar']) ?>" alt="头像" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div style="text-align: center;">
                <button onclick="window.close()" class="btn btn-primary" style="min-width: 120px;">关闭窗口</button>
            </div>
        </div>
    </div>
</div>

<?php include ML_ROOT . '/views/layouts/footer.php'; ?>
