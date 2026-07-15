<?php $pageTitle = '绑定账号'; ?>
<?php include ML_ROOT . '/views/layouts/header.php'; ?>

<div style="min-height: calc(100vh - 300px); display: flex; align-items: center; justify-content: center; padding: 4rem 1rem; background-color: var(--bg-body);">
    <div class="card" style="width: 100%; max-width: 480px; border: none; box-shadow: var(--shadow-lg);">
        <div class="card-body" style="padding: 2.5rem;">
            <div class="text-center" style="margin-bottom: 2rem;">
                <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem;">绑定账号</h1>
                <div style="color: var(--text-muted); font-size: 0.875rem;">
                    您正在使用 <strong style="color: var(--text-main);"><?= e(get_platform_name($binding['platform'])) ?></strong> 登录
                    <div style="display: flex; align-items: center; justify-content: center; gap: 0.5rem; margin-top: 0.5rem;">
                        <img src="<?= e(ensure_https($binding['avatar'])) ?>" alt="Avatar" style="width: 2rem; height: 2rem; border-radius: 50%; border: 1px solid var(--border-color);">
                        <span><?= e($binding['nickname']) ?></span>
                    </div>
                </div>
            </div>

            <div style="display: flex; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color);">
                <button type="button" class="btn-tab active" onclick="switchTab('new')" id="tab-new" style="flex: 1; padding: 1rem; border: none; background: none; border-bottom: 2px solid var(--color-primary); color: var(--color-primary); font-weight: 600; cursor: pointer; transition: all 0.2s;">注册新账号</button>
                <button type="button" class="btn-tab" onclick="switchTab('existing')" id="tab-existing" style="flex: 1; padding: 1rem; border: none; background: none; border-bottom: 2px solid transparent; color: var(--text-muted); cursor: pointer; transition: all 0.2s;">绑定已有账号</button>
            </div>

            <div id="errorAlert" class="alert alert-error" style="display: none; align-items: center; gap: 0.5rem;">
                <span class="iconify" data-icon="tabler:alert-triangle-filled" style="font-size: 1.25rem;"></span>
                <span id="errorMsg"></span>
            </div>

            <!-- 注册新账号表单 -->
            <form id="form-new" onsubmit="submitBind(event, 'new')">
                <input type="hidden" name="_token" value="<?= e($csrf_token) ?>">
                <input type="hidden" name="type" value="new">

                <div class="form-group">
                    <label class="form-label">用户名</label>
                    <div style="position: relative;">
                        <span class="iconify" data-icon="tabler:user" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-light); font-size: 1.125rem;"></span>
                        <input type="text" name="username" class="form-control" required placeholder="3-20个字符，字母数字下划线" style="padding-left: 2.5rem;">
                    </div>
                </div>

                <?php if (($verifyMethod ?? 'none') === 'email'): ?>
                    <div class="form-group">
                        <label class="form-label">邮箱</label>
                        <div style="display: flex; gap: 0.5rem;">
                            <div style="position: relative; flex: 1;">
                                <span class="iconify" data-icon="tabler:mail" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-light); font-size: 1.125rem;"></span>
                                <input type="email" name="email" id="emailInput" class="form-control" required placeholder="请输入邮箱地址" style="padding-left: 2.5rem;" value="<?= e($binding['email'] ?? '') ?>">
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
                            <input type="email" name="email" id="emailInput" class="form-control" required placeholder="请输入邮箱地址" style="padding-left: 2.5rem;" value="<?= e($binding['email'] ?? '') ?>">
                        </div>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label class="form-label">设置密码</label>
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

                <button type="submit" id="submitBtnNew" class="btn btn-primary btn-block" style="padding: 0.75rem; font-size: 1rem;">立即注册并绑定</button>
            </form>

            <!-- 绑定已有账号表单 -->
            <form id="form-existing" style="display: none;" onsubmit="submitBind(event, 'existing')">
                <input type="hidden" name="_token" value="<?= e($csrf_token) ?>">
                <input type="hidden" name="type" value="existing">

                <div class="form-group">
                    <label class="form-label">用户名 / 邮箱</label>
                    <div style="position: relative;">
                        <span class="iconify" data-icon="tabler:user" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-light); font-size: 1.125rem;"></span>
                        <input type="text" name="account" class="form-control" required placeholder="请输入用户名或邮箱" style="padding-left: 2.5rem;">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">密码</label>
                    <div style="position: relative;">
                        <span class="iconify" data-icon="tabler:lock" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-light); font-size: 1.125rem;"></span>
                        <input type="password" name="password" class="form-control" required placeholder="请输入密码" style="padding-left: 2.5rem;">
                    </div>
                </div>

                <button type="submit" id="submitBtnExisting" class="btn btn-primary btn-block" style="padding: 0.75rem; font-size: 1rem;">立即绑定</button>
            </form>

            <div class="text-center" style="margin-top: 1.5rem;">
                <a href="/user/login" style="font-size: 0.875rem; color: var(--text-muted); display: inline-flex; align-items: center; gap: 0.25rem;">
                    <span class="iconify" data-icon="tabler:arrow-left"></span> 返回登录
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    function switchTab(type) {
        hideError();
        document.querySelectorAll('.btn-tab').forEach(btn => {
            btn.classList.remove('active');
            btn.style.borderBottomColor = 'transparent';
            btn.style.color = 'var(--text-muted)';
            btn.style.fontWeight = 'normal';
        });
        const activeBtn = document.getElementById('tab-' + type);
        activeBtn.classList.add('active');
        activeBtn.style.borderBottomColor = 'var(--color-primary)';
        activeBtn.style.color = 'var(--color-primary)';
        activeBtn.style.fontWeight = '600';

        document.getElementById('form-new').style.display = type === 'new' ? 'block' : 'none';
        document.getElementById('form-existing').style.display = type === 'existing' ? 'block' : 'none';
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

    function sendVerifyCode(type) {
        let countdownTimer = null;
        const btn = document.getElementById('sendCodeBtn');
        const target = document.getElementById('emailInput')?.value?.trim();

        if (!target) {
            showError('请输入邮箱地址');
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
                    scene: 'register'
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

    function submitBind(e, type) {
        e.preventDefault();
        hideError();

        const form = e.target;
        const btn = type === 'new' ? document.getElementById('submitBtnNew') : document.getElementById('submitBtnExisting');
        const originalText = btn.textContent;

        btn.disabled = true;
        btn.textContent = '处理中...';

        const formData = new FormData(form);
        fetch('/auth/bind', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.code === 0) {
                    btn.textContent = '绑定成功';
                    toast('绑定成功', 'success');
                    setTimeout(() => {
                        window.location.href = data.data.redirect || '/user/dashboard';
                    }, 500);
                } else {
                    showError(data.message || '绑定失败，请重试');
                    btn.disabled = false;
                    btn.textContent = originalText;
                }
            })
            .catch(err => {
                showError('网络请求失败，请检查网络连接');
                btn.disabled = false;
                btn.textContent = originalText;
            });
    }
    document.getElementById('passwordInput')?.addEventListener('input', function(e) {
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
            if (bar) bar.style.background = i <= strength ? colors[strength] : 'var(--bg-surface-hover)';
        }
    });
</script>

<?php include ML_ROOT . '/views/layouts/footer.php'; ?>