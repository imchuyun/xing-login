<?php
$pageTitle = '联系方式';
$isPublic = true;
ob_start();
?>

<div class="container" style="padding: 3rem 1rem; max-width: 900px; margin: 0 auto;">
    <div style="text-align: center; margin-bottom: 3rem;">
        <h1 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 1rem;">联系我们</h1>
        <p class="text-muted" style="font-size: 1.125rem;">如有任何问题，欢迎随时与我们取得联系</p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <?php if (!empty($siteSettings['admin_email'])): ?>
        <div style="background: var(--bg-surface); border-radius: var(--radius-lg); padding: 2rem; border: 1px solid var(--border-color); text-align: center;">
            <span class="iconify" data-icon="tabler:mail" style="font-size: 3rem; color: var(--color-primary); margin-bottom: 1rem;"></span>
            <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 0.5rem;">电子邮箱</h3>
            <a href="mailto:<?= e($siteSettings['admin_email']) ?>" style="color: var(--color-primary); text-decoration: none;">
                <?= e($siteSettings['admin_email']) ?>
            </a>
        </div>
        <?php endif; ?>

        <?php if (!empty($siteSettings['contact_qq'])): ?>
        <div style="background: var(--bg-surface); border-radius: var(--radius-lg); padding: 2rem; border: 1px solid var(--border-color); text-align: center;">
            <span class="iconify" data-icon="tabler:brand-qq" style="font-size: 3rem; color: var(--color-primary); margin-bottom: 1rem;"></span>
            <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 0.5rem;">QQ客服</h3>
            <a href="https://wpa.qq.com/msgrd?v=3&uin=<?= e($siteSettings['contact_qq']) ?>&site=qq&menu=yes" target="_blank" style="color: var(--color-primary); text-decoration: none;">
                <?= e($siteSettings['contact_qq']) ?>
            </a>
        </div>
        <?php endif; ?>

        <?php if (!empty($siteSettings['contact_wechat'])): ?>
        <div style="background: var(--bg-surface); border-radius: var(--radius-lg); padding: 2rem; border: 1px solid var(--border-color); text-align: center;">
            <span class="iconify" data-icon="tabler:brand-wechat" style="font-size: 3rem; color: var(--color-primary); margin-bottom: 1rem;"></span>
            <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 0.5rem;">微信客服</h3>
            <span style="color: var(--text-secondary);"><?= e($siteSettings['contact_wechat']) ?></span>
        </div>
        <?php endif; ?>

        <?php if (!empty($siteSettings['contact_qq_group'])): ?>
        <div style="background: var(--bg-surface); border-radius: var(--radius-lg); padding: 2rem; border: 1px solid var(--border-color); text-align: center;">
            <span class="iconify" data-icon="tabler:users-group" style="font-size: 3rem; color: var(--color-primary); margin-bottom: 1rem;"></span>
            <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 0.5rem;">QQ交流群</h3>
            <span style="color: var(--text-secondary);"><?= e($siteSettings['contact_qq_group']) ?></span>
        </div>
        <?php endif; ?>
    </div>

    <div style="background: var(--bg-surface); border-radius: var(--radius-lg); padding: 2rem; border: 1px solid var(--border-color);">
        <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
            <span class="iconify" data-icon="tabler:clock" style="color: var(--color-primary);"></span>
            服务时间
        </h2>
        <p style="line-height: 1.8; color: var(--text-secondary);">
            工作日：9:00 - 18:00<br>
            节假日：在线留言，我们会尽快回复
        </p>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include ML_ROOT . '/views/layouts/main.php'; ?>
