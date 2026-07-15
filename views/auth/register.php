<?php $pageTitle = '用户注册'; ?>
<?php include ML_ROOT . '/views/layouts/header.php'; ?>

<div style="min-height: calc(100vh - 300px); display: flex; align-items: center; justify-content: center; padding: 4rem 1rem; background-color: var(--bg-body);">
    <div class="card" style="width: 100%; max-width: 460px; border: none; box-shadow: var(--shadow-lg);">
        <div class="card-body" style="padding: 2.5rem;">
            <?php if (empty($enableRegister)): ?>
                <!-- 不开放注册时的提示 -->
                <div class="text-center" style="padding: 2rem 0;">
                    <div style="width: 80px; height: 80px; margin: 0 auto 1.5rem; background-color: rgba(239, 68, 68, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <span class="iconify" data-icon="tabler:user-off" style="font-size: 2.5rem; color: var(--color-error);"></span>
                    </div>
                    <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.75rem;">暂不开放注册</h1>
                    <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 2rem;">当前系统暂未开放新用户注册，如有需要请联系管理员</p>
                    <a href="/user/login" class="btn btn-primary" style="padding: 0.75rem 2rem;">返回登录</a>
                </div>
            <?php else: ?>
                <!-- 正常注册表单 -->
                <div class="text-center" style="margin-bottom: 2rem;">
                    <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem;">创建账户</h1>
                    <p style="color: var(--text-muted); font-size: 0.875rem;">填写信息，开启聚合登录之旅</p>
                </div>

                <form id="regForm">
                    <input type="hidden" name="_token" value="<?= e($csrf_token) ?>">

                    <div id="errorAlert" class="alert alert-error" style="display: none; align-items: center; gap: 0.5rem;">
                        <span class="iconify" data-icon="tabler:alert-triangle-filled" style="font-size: 1.25rem;"></span>
                        <span id="errorMsg"></span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">用户名</label>
                        <div style="position: relative;">
                            <span class="iconify" data-icon="tabler:user" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-light); font-size: 1.125rem;"></span>
                            <input type="text" name="username" class="form-control" required placeholder="3-20个字符，字母数字下划线" style="padding-left: 2.5rem;">
                        </div>
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
                        <div class="form-group">
                            <label class="form-label">验证码</label>
                            <div style="position: relative;">
                                <span class="iconify" data-icon="tabler:shield-lock" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-light); font-size: 1.125rem;"></span>
                                <input type="text" name="verify_code" class="form-control" required maxlength="6" placeholder="请输入验证码" style="padding-left: 2.5rem;">
                            </div>
                        </div>
                    <?php elseif (($verifyMethod ?? 'none') === 'email'): ?>
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
                        <div class="form-group">
                            <label class="form-label">验证码</label>
                            <div style="position: relative;">
                                <span class="iconify" data-icon="tabler:shield-lock" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-light); font-size: 1.125rem;"></span>
                                <input type="text" name="verify_code" class="form-control" required maxlength="6" placeholder="请输入验证码" style="padding-left: 2.5rem;">
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="form-group">
                            <label class="form-label">邮箱</label>
                            <div style="position: relative;">
                                <span class="iconify" data-icon="tabler:mail" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-light); font-size: 1.125rem;"></span>
                                <input type="email" name="email" id="emailInput" class="form-control" required placeholder="请输入邮箱地址" style="padding-left: 2.5rem;">
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label class="form-label">密码</label>
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

                    <div class="mb-4">
                        <label style="display: flex; align-items: flex-start; gap: 0.5rem; font-size: 0.875rem; color: var(--text-muted); cursor: pointer;">
                            <input type="checkbox" name="agree" required style="margin-top: 0.25rem; width: 1rem; height: 1rem; border-radius: 0.25rem; border: 1px solid var(--border-color); accent-color: var(--color-primary);">
                            <span>我已阅读并同意 <a href="#" style="color: var(--color-primary);">服务协议</a> 和 <a href="#" style="color: var(--color-primary);">隐私政策</a></span>
                        </label>
                    </div>

                    <button type="submit" id="submitBtn" class="btn btn-primary btn-block" style="padding: 0.75rem; font-size: 1rem;">立即注册</button>

                    <div class="text-center" style="margin-top: 1.5rem;">
                        <span style="font-size: 0.875rem; color: var(--text-muted);">已有账号？</span>
                        <a href="/user/login" style="font-size: 0.875rem; font-weight: 500; color: var(--color-primary);">立即登录</a>
                    </div>
                </form>

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
                            if (!target) { showError('请输入邮箱地址'); return; }
                        } else {
                            target = document.getElementById('phoneInput')?.value;
                            if (!target || !/^1[3-9]\d{9}$/.test(target)) { showError('请输入有效的手机号码'); return; }
                        }

                        hideError();
                        btn.disabled = true;
                        btn.textContent = '发送中...';

                        fetch('/api/send-verify-code', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ type: type, target: target, scene: 'register' })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.code === 0) {
                                let countdown = 60;
                                btn.textContent = countdown + 's';
                                countdownTimer = setInterval(() => {
                                    countdown--;
                                    if (countdown <= 0) { clearInterval(countdownTimer); btn.disabled = false; btn.textContent = '获取验证码'; }
                                    else { btn.textContent = countdown + 's'; }
                                }, 1000);
                                toast('验证码已发送', 'success');
                            } else {
                                showError(data.message || '发送失败，请重试');
                                btn.disabled = false;
                                btn.textContent = '获取验证码';
                            }
                        })
                        .catch(err => { showError('网络请求失败'); btn.disabled = false; btn.textContent = '获取验证码'; });
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
                            document.getElementById('strength' + i).style.background = i <= strength ? colors[strength] : 'var(--bg-surface-hover)';
                        }
                    });

                    document.getElementById('regForm').addEventListener('submit', function(e) {
                        e.preventDefault();
                        hideError();
                        const btn = document.getElementById('submitBtn');
                        const originalText = btn.textContent;
                        btn.disabled = true;
                        btn.textContent = '注册中...';

                        fetch('/user/reg', { method: 'POST', body: new FormData(this) })
                        .then(res => res.json())
                        .then(data => {
                            if (data.code === 0) {
                                btn.textContent = '注册成功';
                                toast('注册成功', 'success');
                                setTimeout(() => { window.location.href = data.data.redirect || '/user/dashboard'; }, 500);
                            } else {
                                showError(data.message || '注册失败，请重试');
                                btn.disabled = false;
                                btn.textContent = originalText;
                            }
                        })
                        .catch(err => { showError('网络请求失败，请检查网络连接'); btn.disabled = false; btn.textContent = originalText; });
                    });
                </script>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include ML_ROOT . '/views/layouts/footer.php'; ?>
