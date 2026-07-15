<?php
$pageTitle = '公司介绍';
$isPublic = true;
ob_start();
?>

<div class="container" style="padding: 3rem 1rem; max-width: 900px; margin: 0 auto;">
    <div style="text-align: center; margin-bottom: 3rem;">
        <h1 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 1rem;">关于 <?= e($siteSettings['site_name'] ?? 'Max Login') ?></h1>
        <p class="text-muted" style="font-size: 1.125rem;">企业级聚合登录认证平台</p>
    </div>

    <div style="background: var(--bg-surface); border-radius: var(--radius-lg); padding: 2rem; margin-bottom: 2rem; border: 1px solid var(--border-color);">
        <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
            <span class="iconify" data-icon="tabler:building" style="color: var(--color-primary);"></span>
            公司简介
        </h2>
        <p style="line-height: 1.8; color: var(--text-secondary);">
            <?= e($siteSettings['site_name'] ?? 'Max Login') ?> 是一家专注于身份认证与安全登录解决方案的技术服务商。我们致力于为企业提供安全、稳定、高效的聚合登录服务，帮助企业快速构建统一身份认证体系，提升用户体验，降低开发成本。
        </p>
    </div>

    <div style="background: var(--bg-surface); border-radius: var(--radius-lg); padding: 2rem; margin-bottom: 2rem; border: 1px solid var(--border-color);">
        <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
            <span class="iconify" data-icon="tabler:target" style="color: var(--color-primary);"></span>
            我们的使命
        </h2>
        <p style="line-height: 1.8; color: var(--text-secondary);">
            让每一个企业都能轻松实现安全、便捷的用户身份认证。通过技术创新和优质服务，为客户创造价值，推动行业发展。
        </p>
    </div>

    <div style="background: var(--bg-surface); border-radius: var(--radius-lg); padding: 2rem; margin-bottom: 2rem; border: 1px solid var(--border-color);">
        <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
            <span class="iconify" data-icon="tabler:star" style="color: var(--color-primary);"></span>
            核心优势
        </h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
            <div style="text-align: center; padding: 1rem;">
                <span class="iconify" data-icon="tabler:shield-check" style="font-size: 2.5rem; color: var(--color-primary); margin-bottom: 0.5rem;"></span>
                <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 0.5rem;">安全可靠</h3>
                <p class="text-muted text-sm">多重安全防护，保障数据安全</p>
            </div>
            <div style="text-align: center; padding: 1rem;">
                <span class="iconify" data-icon="tabler:rocket" style="font-size: 2.5rem; color: var(--color-primary); margin-bottom: 0.5rem;"></span>
                <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 0.5rem;">快速接入</h3>
                <p class="text-muted text-sm">简单配置，快速上线</p>
            </div>
            <div style="text-align: center; padding: 1rem;">
                <span class="iconify" data-icon="tabler:headset" style="font-size: 2.5rem; color: var(--color-primary); margin-bottom: 0.5rem;"></span>
                <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 0.5rem;">专业服务</h3>
                <p class="text-muted text-sm">7x24小时技术支持</p>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include ML_ROOT . '/views/layouts/main.php'; ?>
