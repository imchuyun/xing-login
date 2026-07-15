<?php $pageTitle = '认证结果';
ob_start(); ?>

<div style="display: flex; justify-content: center; align-items: center; min-height: calc(100vh - 200px);">
    <div class="card" style="max-width: 500px; width: 100%;">
        <div class="card-body" style="text-align: center; padding: 3rem 2rem;">
            <?php if ($success): ?>
                <div style="width: 5rem; height: 5rem; background-color: rgba(82, 196, 26, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                    <span class="iconify" data-icon="tabler:circle-check-filled" style="font-size: 2.5rem; color: var(--color-success);"></span>
                </div>
                <h3 style="font-size: 1.25rem; font-weight: 600; color: var(--color-success); margin-bottom: 0.5rem;">认证成功</h3>
                <p style="color: var(--text-secondary); margin-bottom: 1rem;"><?= e($message) ?></p>
                
                <?php if (!empty($verification['verify_mobile'])): ?>
                    <?php 
                    $mobile = $verification['verify_mobile'];
                    $maskedMobile = substr($mobile, 0, 3) . '****' . substr($mobile, -4);
                    $carrierTypes = ['1' => '移动', '2' => '联通', '3' => '电信', '4' => '广电'];
                    $carrierName = $carrierTypes[$verification['carrier'] ?? ''] ?? '';
                    ?>
                    <div style="background: rgba(82, 196, 26, 0.08); border-radius: 8px; padding: 1rem; margin-bottom: 1.5rem;">
                        <p style="font-size: 0.875rem; color: var(--text-secondary); margin: 0;">
                            <span class="iconify" data-icon="tabler:phone-filled" style="vertical-align: middle; margin-right: 0.25rem;"></span>
                            绑定手机：<?= e($maskedMobile) ?>
                            <?php if ($carrierName): ?>
                                <span style="margin-left: 0.5rem; padding: 0.125rem 0.5rem; background: rgba(24, 144, 255, 0.1); color: #1890ff; border-radius: 4px; font-size: 0.75rem;"><?= e($carrierName) ?></span>
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div style="width: 5rem; height: 5rem; background-color: rgba(245, 34, 45, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                    <span class="iconify" data-icon="tabler:circle-x-filled" style="font-size: 2.5rem; color: var(--color-error);"></span>
                </div>
                <h3 style="font-size: 1.25rem; font-weight: 600; color: var(--color-error); margin-bottom: 0.5rem;">认证失败</h3>
                <p style="color: var(--text-secondary); margin-bottom: 1.5rem;"><?= e($message) ?></p>
            <?php endif; ?>
            
            <a href="/user/verification" class="btn btn-primary" style="padding: 0.75rem 2rem;">返回认证页面</a>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include ML_ROOT . '/views/layouts/user.php'; ?>