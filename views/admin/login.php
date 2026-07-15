<?php $pageTitle = '管理员登录'; ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> - <?= e($siteSettings["site_name"] ?? "星聚合登录") ?></title>
    <link rel="icon" href="/assets/favicon.ico">
    <link rel="stylesheet" href="/assets/css/variables.css">
    <link rel="stylesheet" href="/assets/css/base.css">
    <link rel="stylesheet" href="/assets/css/components.css">
    <script src="/assets/js/icons.js"></script>
</head>
<body style="background-color: var(--bg-body); margin: 0; font-family: var(--font-family);">
    <div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem 1rem;">
        <div class="card" style="width: 100%; max-width: 420px; border: none; box-shadow: var(--shadow-lg);">
            <div class="card-body" style="padding: 2.5rem;">
                <!-- 头部 -->
                <div style="text-align: center; margin-bottom: 2rem;">
                    <div style="display: inline-flex; align-items: center; justify-content: center; width: 4rem; height: 4rem; background: linear-gradient(135deg, var(--color-primary), var(--color-primary-hover)); border-radius: var(--radius-xl); margin-bottom: 1rem; box-shadow: 0 10px 25px -5px rgba(var(--primary-rgb), 0.3);">
                        <span class="iconify" data-icon="tabler:shield-lock" style="font-size: 2rem; color: #fff;"></span>
                    </div>
                    <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem;">管理员登录</h1>
                    <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0;"><?= e($siteSettings["site_name"] ?? "星聚合登录") ?> 管理控制台</p>
                </div>

                <!-- 管理员标识 -->
                <div style="display: flex; justify-content: center; margin-bottom: 1.5rem;">
                    <div style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background-color: rgba(var(--primary-rgb), 0.1); color: var(--color-primary); border-radius: 9999px; font-size: 0.8125rem; font-weight: 500;">
                        <span class="iconify" data-icon="tabler:lock-check" style="font-size: 1rem;"></span>
                        安全管理通道
                    </div>
                </div>

                <!-- 登录表单 -->
                <form id="loginForm">
                    <input type="hidden" name="_token" value="<?= e($csrf_token) ?>">

                    <!-- 错误提示 -->
                    <div id="errorAlert" class="alert alert-error" style="display: none; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                        <span class="iconify" data-icon="tabler:alert-triangle-filled" style="font-size: 1.25rem;"></span>
                        <span id="errorMsg"></span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">管理员账号</label>
                        <div style="position: relative;">
                            <span class="iconify" data-icon="tabler:user-shield" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-light); font-size: 1.125rem;"></span>
                            <input type="text" name="username" class="form-control" required autofocus autocomplete="username" placeholder="请输入管理员账号" style="padding-left: 2.5rem;">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">管理员密码</label>
                        <div style="position: relative;">
                            <span class="iconify" data-icon="tabler:lock" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-light); font-size: 1.125rem;"></span>
                            <input type="password" name="password" id="passwordInput" class="form-control" required autocomplete="current-password" placeholder="请输入管理员密码" style="padding-left: 2.5rem; padding-right: 2.5rem;">
                            <button type="button" onclick="togglePassword()" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; padding: 0; cursor: pointer; color: var(--text-light);">
                                <span id="eyeIcon" class="iconify" data-icon="tabler:eye" style="font-size: 1.125rem;"></span>
                                <span id="eyeOffIcon" class="iconify" data-icon="tabler:eye-off" style="font-size: 1.125rem; display: none;"></span>
                            </button>
                        </div>
                    </div>

                    <button type="submit" id="submitBtn" class="btn btn-primary btn-block" style="padding: 0.75rem; font-size: 1rem; margin-top: 0.5rem;">
                        <span class="iconify" data-icon="tabler:login" style="font-size: 1.125rem; margin-right: 0.5rem;"></span>
                        登录管理后台
                    </button>
                </form>

                <!-- 底部链接 -->
                <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color); text-align: center;">
                    <a href="/" style="display: inline-flex; align-items: center; gap: 0.375rem; color: var(--color-primary); font-size: 0.875rem; text-decoration: none;">
                        <span class="iconify" data-icon="tabler:arrow-left" style="font-size: 1rem;"></span>
                        返回首页
                    </a>
                </div>

                <!-- 安全提示 -->
                <div class="alert alert-info" style="margin-top: 1.5rem; margin-bottom: 0; display: flex; align-items: flex-start; gap: 0.75rem;">
                    <span class="iconify" data-icon="tabler:info-circle" style="font-size: 1.25rem; flex-shrink: 0; margin-top: 0.125rem;"></span>
                    <div style="font-size: 0.8125rem;">
                        <p style="font-weight: 600; margin: 0 0 0.25rem 0;">安全提示</p>
                        <p style="color: var(--text-muted); margin: 0;">此页面仅限管理员访问，请确保在安全的网络环境下操作。</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        if (typeof renderIcons === 'function') renderIcons();
        function togglePassword() {
            const input = document.getElementById('passwordInput');
            const eyeIcon = document.getElementById('eyeIcon');
            const eyeOffIcon = document.getElementById('eyeOffIcon');
            
            if (input.type === 'password') {
                input.type = 'text';
                eyeIcon.style.display = 'none';
                eyeOffIcon.style.display = 'inline';
            } else {
                input.type = 'password';
                eyeIcon.style.display = 'inline';
                eyeOffIcon.style.display = 'none';
            }
        }
        function showError(message) {
            const alert = document.getElementById('errorAlert');
            const msg = document.getElementById('errorMsg');
            msg.textContent = message;
            alert.style.display = 'flex';
        }
        function hideError() {
            document.getElementById('errorAlert').style.display = 'none';
        }
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            hideError();
            
            const btn = document.getElementById('submitBtn');
            const originalHTML = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="iconify" data-icon="tabler:loader-2" style="font-size: 1.125rem; margin-right: 0.5rem; animation: spin 1s linear infinite;"></span>登录中...';
            if (typeof renderIcons === 'function') renderIcons();

            const formData = new FormData(this);
            const loginPath = window.location.pathname;
            
            fetch(loginPath, { 
                method: 'POST', 
                body: formData 
            })
            .then(res => res.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    throw new Error(text || '服务器返回了无效的响应');
                }
            }))
            .then(data => {
                if (data.code === 0) {
                    btn.innerHTML = '<span class="iconify" data-icon="tabler:check" style="font-size: 1.125rem; margin-right: 0.5rem;"></span>登录成功';
                    if (typeof renderIcons === 'function') renderIcons();
                    setTimeout(() => {
                        const adminPath = window.location.pathname.replace('/login', '');
                        window.location.href = data.data.redirect || adminPath || '/admin';
                    }, 500);
                } else {
                    showError(data.message || '登录失败，请检查账号密码');
                    btn.disabled = false;
                    btn.innerHTML = originalHTML;
                    if (typeof renderIcons === 'function') renderIcons();
                }
            })
            .catch(err => {
                let errorMsg = err.message || '请求失败';
                if (errorMsg.length > 200) {
                    errorMsg = '服务器错误，请检查服务器日志';
                }
                showError(errorMsg);
                btn.disabled = false;
                btn.innerHTML = originalHTML;
                if (typeof renderIcons === 'function') renderIcons();
            });
        });
    </script>

    <style>
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
</body>
</html>
