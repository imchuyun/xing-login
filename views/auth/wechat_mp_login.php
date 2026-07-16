<?php
$pageTitle = '微信公众号登录';
ob_start();
?>

<div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: var(--bg-page, #f5f7fb); padding: 24px;">
    <div style="width: 100%; max-width: 420px; background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 28px; text-align: center;">
        <img src="/assets/icon/wx.svg" alt="微信" style="width: 48px; height: 48px; margin-bottom: 16px;">
        <h1 style="font-size: 22px; margin: 0 0 8px; color: #111827;">微信公众号登录</h1>
        <p style="margin: 0 0 20px; color: #6b7280; line-height: 1.6;">请使用微信扫码，或关注公众号后发送验证码完成登录。</p>

        <?php if (!empty($qrcode)): ?>
            <div style="display: inline-flex; padding: 12px; border: 1px solid #e5e7eb; border-radius: 8px; margin-bottom: 18px;">
                <img src="<?= e($qrcode) ?>" alt="微信登录二维码" style="width: 220px; height: 220px; display: block;">
            </div>
        <?php endif; ?>

        <div style="border-top: 1px solid #eef2f7; padding-top: 18px;">
            <div style="font-size: 13px; color: #6b7280; margin-bottom: 8px;">验证码</div>
            <div style="font-size: 30px; font-weight: 700; letter-spacing: 4px; color: #111827; margin-bottom: 10px;"><?= e($code) ?></div>
            <div style="font-size: 14px; color: #4b5563; line-height: 1.7;">在公众号内发送：<strong>登录 <?= e($code) ?></strong></div>
        </div>

        <div style="margin-top: 18px; padding: 12px; background: #f9fafb; border-radius: 8px; color: #6b7280; font-size: 13px; line-height: 1.6; text-align: left;">
            公众号服务器地址：<br>
            <code style="word-break: break-all; color: #111827;"><?= e($callbackUrl) ?></code>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include ML_ROOT . '/views/layouts/main.php'; ?>
