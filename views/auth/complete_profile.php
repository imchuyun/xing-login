<?php $pageTitle = '完善资料'; ?>
<?php include ML_ROOT . '/views/layouts/header.php'; ?>

<div style="min-height: calc(100vh - 300px); display: flex; align-items: center; justify-content: center; padding: 4rem 1rem; background-color: var(--bg-body);">
    <div class="card" style="width: 100%; max-width: 480px; border: none; box-shadow: var(--shadow-lg);">
        <div class="card-body" style="padding: 2.5rem;">
            <div class="text-center" style="margin-bottom: 2rem;">
                <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem;">完善账户信息</h1>
                <p style="color: var(--text-muted); font-size: 0.875rem;">第三方登录成功，请完善以下信息</p>

                <?php if (!empty($oauthInfo)): ?>
                    <div style="display: flex; align-items: center; justify-content: center; gap: 0.5rem; margin-top: 1rem;">
                        <?php if (!empty($oauthInfo['avatar'])): ?>
                            <img src="<?= e(ensure_https($oauthInfo['avatar'])) ?>" style="width: 2rem; height: 2rem; border-radius: 50%; border: 1px solid var(--border-color);">
                        <?php endif; ?>
                        <span style="font-size: 0.875rem; color: var(--text-muted);">
                            <?= e($oauthInfo['nickname'] ?? '') ?> (<?= e($oauthInfo['platform'] ?? '') ?>)
                        </span>
                    </div>
                <?php endif; ?>
            </div>

            <form id="profileForm">
                <input type="hidden" name="_token" value="<?= e($csrf_token) ?>">

                <div id="errorAlert" class="alert alert-error" style="display: none; align-items: center; gap: 0.5rem;">
                    <span class="iconify" data-icon="tabler:alert-triangle-filled" style="font-size: 1.25rem;"></span>
                    <span id="errorMsg"></span>
                </div>

                <div class="form-group">
                    <label class="form-label">用户名 <span style="color: var(--color-error);">*</span></label>
                    <div style="position: relative;">
                        <span class="iconify" data-icon="tabler:user" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-light); font-size: 1.125rem;"></span>
                        <input type="text" name="username" class="form-control" required placeholder="3-20个字符，字母数字下划线" style="padding-left: 2.5rem;">
                    </div>
                </div>

                <?php if ($verifyMethod === 'phone'): ?>
                    <div class="form-group">
                        <label class="form-label">手机号码 <span style="color: var(--color-error);">*</span></label>
                        <div style="display: flex; gap: 0.5rem;">
                            <div style="position: relative; flex: 1;">
                                <span class="iconify" data-icon="tabler:device-mobile" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-light); font-size: 1.125rem;"></span>
                                <input type="tel" name="phone" id="phoneInput" class="form-control" required placeholder="请输入手机号码" style="padding-left: 2.5rem;">
                            </div>
                            <button type="button" id="sendCodeBtn" onclick="sendVerifyCode('phone')" class="btn btn-outline" style="white-space: nowrap;">获取验证码</button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">验证码 <span style="color: var(--color-error);">*</span></label>
                        <div style="position: relative;">
                            <span class="iconify" data-icon="tabler:shield-lock" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-light); font-size: 1.125rem;"></span>
                            <input type="text" name="verify_code" class="form-control" required maxlength="6" placeholder="请输入验证码" style="padding-left: 2.5rem;">
                        </div>
                    </div>
                <?php elseif ($verifyMethod === 'email'): ?>
                    <div class="form-group">
                        <label class="form-label">邮箱 <span style="color: var(--color-error);">*</span></label>
                        <div style="display: flex; gap: 0.5rem;">
                            <div style="position: relative; flex: 1;">
                                <span class="iconify" data-icon="tabler:mail" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-light); font-size: 1.125rem;"></span>
                                <input type="email" name="email" id="emailInput" class="form-control" required placeholder="请输入邮箱地址" style="padding-left: 2.5rem;">
                            </div>
                            <button type="button" id="sendCodeBtn" onclick="sendVerifyCode('email')" class="btn btn-outline" style="white-space: nowrap;">获取验证码</button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">验证码 <span style="color: var(--color-error);">*</span></label>
                        <div style="position: relative;">
                            <span class="iconify" data-icon="tabler:shield-lock" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-light); font-size: 1.125rem;"></span>
                            <input type="text" name="verify_code" class="form-control" required maxlength="6" placeholder="请输入验证码" style="padding-left: 2.5rem;">
                        </div>
                    </div>
                <?php else: ?>
                    <div class="form-group">
                        <label class="form-label">邮箱 <span style="color: var(--color-error);">*</span></label>
                        <div style="position: relative;">
                            <span class="iconify" data-icon="tabler:mail" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-light); font-size: 1.125rem;"></span>
                            <input type="email" name="email" id="emailInput" class="form-control" required placeholder="请输入邮箱地址" style="padding-left: 2.5rem;">
                        </div>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label class="form-label">设置密码 <span style="color: var(--color-error);">*</span></label>
                    <div style="position: relative;">
                        <span class="iconify" data-icon="tabler:lock" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-light); font-size: 1.125rem;"></span>
                        <input type="password" name="password" id="passwordInput" class="form-control" required placeholder="至少6位密码" style="padding-left: 2.5rem;">
                    </div>
                    <div style="display: flex; gap: 4px; margin-top: 8px; height: 4px;">
                        <div id="strength1" style="flex: 1; background: var(--bg-surface-hover); border-radius: 2px; transition: all 0.3s;"></div>
                        <div id="strength2" style="flex: 1; background: var(--bg-surface-hover); border-radius: 2px; transition: all 0.3s;"></div>
                        <div id="strength3" style="flex: 1; background: var(--bg-surface-hover); border-radius: 2px; transition: all 0.3s;"></div>
                        <div id="strength4" style="flex: 1; background: var(--bg-surface-hover); border-radius: 2px; transition: all 0.3s;"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">确认密码 <span style="color: var(--color-error);">*</span></label>
                    <div style="position: relative;">
                        <span class="iconify" data-icon="tabler:lock" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-light); font-size: 1.125rem;"></span>
                        <input type="password" name="password_confirm" class="form-control" required placeholder="再次输入密码" style="padding-left: 2.5rem;">
                    </div>
                </div>

                <button type="submit" id="submitBtn" class="btn btn-primary btn-block" style="padding: 0.75rem; font-size: 1rem;">完成注册</button>
            </form>
        </div>
    </div>
</div>

<script>
    function showError(message) {
        const alert = document.getElementById('errorAlert');
        const msg = document.getElementById('errorMsg');
        msg.textContent = message;
        alert.style.display = 'flex';
    }

    function hideError() {
        document.getElementById('errorAlert').style.display = 'none';
    }

    function sendVerifyCode(type) {
        let countdownTimer = null;
        const btn = document.getElementById('sendCodeBtn');
        const target = type === 'phone' ?
            document.getElementById('phoneInput').value :
            document.getElementById('emailInput').value;

        if (!target) {
            showError(type === 'phone' ? '请输入手机号' : '请输入邮箱');
            return;
        }

        hideError();
        btn.disabled = true;
        btn.textContent = '发送中...';

        fetch('/api/send-verify-code', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    type: type,
                    target: target,
                    scene: 'complete_profile'
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.code === 0) {
                    let countdown = 60;
                    btn.textContent = countdown + 's';
                    countdownTimer = setInterval(() => {
                        countdown--;
                        if (countdown <= 0) {
                            clearInterval(countdownTimer);
                            btn.disabled = false;
                            btn.textContent = '获取验证码';
                        } else {
                            btn.textContent = countdown + 's';
                        }
                    }, 1000);
                    toast('验证码已发送', 'success');
                } else {
                    showError(data.message || '发送失败，请重试');
                    btn.disabled = false;
                    btn.textContent = '获取验证码';
                }
            })
            .catch(err => {
                showError('网络请求失败');
                btn.disabled = false;
                btn.textContent = '获取验证码';
            });
    }
    document.getElementById('passwordInput').addEventListener('input', function(e) {
        const password = e.target.value;
        let strength = 0;
        if (password.length >= 6) strength++;
        if (password.length >= 8) strength++;
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^a-zA-Z0-9]/.test(password)) strength++;

        strength = Math.min(strength, 4);
        const colors = ['var(--bg-surface-hover)', 'var(--color-error)', 'var(--color-warning)', 'var(--color-success)', 'var(--color-primary)'];

        for (let i = 1; i <= 4; i++) {
            const bar = document.getElementById('strength' + i);
            bar.style.background = i <= strength ? colors[strength] : 'var(--bg-surface-hover)';
        }
    });

    document.getElementById('profileForm').addEventListener('submit', function(e) {
        e.preventDefault();
        hideError();

        const password = document.querySelector('[name="password"]').value;
        const confirm = document.querySelector('[name="password_confirm"]').value;

        if (password !== confirm) {
            showError('两次密码输入不一致');
            return;
        }

        const btn = document.getElementById('submitBtn');
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = '处理中...';

        const formData = new FormData(this);
        fetch('/auth/complete-profile', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.code === 0) {
                    toast('完善成功', 'success');
                    setTimeout(() => {
                        window.location.href = data.data?.redirect || '/user/dashboard';
                    }, 500);
                } else {
                    showError(data.message || '提交失败，请重试');
                    btn.disabled = false;
                    btn.textContent = originalText;
                }
            })
            .catch(err => {
                showError('网络请求失败，请检查网络连接');
                btn.disabled = false;
                btn.textContent = originalText;
            });
    });
</script>

<?php include ML_ROOT . '/views/layouts/footer.php'; ?>