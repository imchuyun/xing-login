<?php $pageTitle = '找回密码'; ?>
<?php include ML_ROOT . '/views/layouts/header.php'; ?>

<div style="min-height: calc(100vh - 300px); display: flex; align-items: center; justify-content: center; padding: 4rem 1rem; background-color: var(--bg-body);">
    <div class="card" style="width: 100%; max-width: 420px; border: none; box-shadow: var(--shadow-lg);">
        <div class="card-body" style="padding: 2.5rem;">
            <div class="text-center" style="margin-bottom: 2rem;">
                <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem;">找回密码</h1>
                <p style="color: var(--text-muted); font-size: 0.875rem;">请输入您的邮箱或手机号重置密码</p>
            </div>

            <form id="findPwdForm">
                <input type="hidden" name="_token" value="<?= e($csrf_token) ?>">

                <div id="errorAlert" class="alert alert-error" style="display: none; align-items: center; gap: 0.5rem;">
                    <span class="iconify" data-icon="tabler:alert-triangle-filled" style="font-size: 1.25rem;"></span>
                    <span id="errorMsg"></span>
                </div>

                <?php if (($verifyMethod ?? 'none') === 'phone'): ?>
                    <div class="form-group">
                        <label class="form-label">手机号码</label>
                        <div style="display: flex; gap: 0.5rem;">
                            <div style="position: relative; flex: 1;">
                                <span class="iconify" data-icon="tabler:device-mobile" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-light); font-size: 1.125rem;"></span>
                                <input type="tel" name="phone" id="phoneInput" class="form-control" required placeholder="请输入手机号码" style="padding-left: 2.5rem;">
                            </div>
                            <button type="button" id="sendCodeBtn" onclick="sendVerifyCode('phone')" class="btn btn-outline" style="white-space: nowrap;">获取验证码</button>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="form-group">
                        <label class="form-label">邮箱</label>
                        <div style="display: flex; gap: 0.5rem;">
                            <div style="position: relative; flex: 1;">
                                <span class="iconify" data-icon="tabler:mail" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-light); font-size: 1.125rem;"></span>
                                <input type="email" name="email" id="emailInput" class="form-control" required placeholder="请输入邮箱地址" style="padding-left: 2.5rem;">
                            </div>
                            <button type="button" id="sendCodeBtn" onclick="sendVerifyCode('email')" class="btn btn-outline" style="white-space: nowrap;">获取验证码</button>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label class="form-label">验证码</label>
                    <div style="position: relative;">
                        <span class="iconify" data-icon="tabler:shield-lock" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-light); font-size: 1.125rem;"></span>
                        <input type="text" name="verify_code" class="form-control" required maxlength="6" placeholder="请输入验证码" style="padding-left: 2.5rem;">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">新密码</label>
                    <div style="position: relative;">
                        <span class="iconify" data-icon="tabler:lock" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-light); font-size: 1.125rem;"></span>
                        <input type="password" name="password" class="form-control" required placeholder="请输入新密码" style="padding-left: 2.5rem;">
                    </div>
                </div>

                <button type="submit" id="submitBtn" class="btn btn-primary btn-block" style="padding: 0.75rem; font-size: 1rem;">重置密码</button>

                <div class="text-center" style="margin-top: 1.5rem;">
                    <a href="/user/login" style="font-size: 0.875rem; color: var(--text-muted); display: inline-flex; align-items: center; gap: 0.25rem;">
                        <span class="iconify" data-icon="tabler:arrow-left"></span> 返回登录
                    </a>
                </div>
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
        let target = '';

        if (type === 'email') {
            target = document.getElementById('emailInput')?.value?.trim();
            if (!target) {
                showError('请输入邮箱地址');
                return;
            }
        } else {
            target = document.getElementById('phoneInput')?.value;
            if (!target || !/^1[3-9]\d{9}$/.test(target)) {
                showError('请输入有效的手机号码');
                return;
            }
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
                    scene: 'findpwd'
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

    document.getElementById('findPwdForm').addEventListener('submit', function(e) {
        e.preventDefault();
        hideError();

        const btn = document.getElementById('submitBtn');
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = '提交中...';

        const formData = new FormData(this);
        fetch('/user/findpwd', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.code === 0) {
                    btn.textContent = '重置成功';
                    toast('密码重置成功', 'success');
                    setTimeout(() => {
                        window.location.href = '/user/login';
                    }, 1000);
                } else {
                    showError(data.message || '重置失败，请重试');
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