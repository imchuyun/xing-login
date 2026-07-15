<?php $pageTitle = '用户登录'; ?>
<?php include ML_ROOT . '/views/layouts/header.php'; ?>

<div style="min-height: calc(100vh - 300px); display: flex; align-items: center; justify-content: center; padding: 4rem 1rem; background-color: var(--bg-body);">
    <div class="card" style="width: 100%; max-width: 420px; border: none; box-shadow: var(--shadow-lg);">
        <div class="card-body" style="padding: 2.5rem;">
            <div class="text-center" style="margin-bottom: 2rem;">
                <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem;">登录您的账户</h1>
                <p style="color: var(--text-muted); font-size: 0.875rem;">欢迎回来，请登录以继续</p>
            </div>

            <form id="loginForm">
                <input type="hidden" name="_token" value="<?= e($csrf_token) ?>">

                <div id="errorAlert" class="alert alert-error" style="display: none; align-items: center; gap: 0.5rem;">
                    <span class="iconify" data-icon="tabler:alert-triangle-filled" style="font-size: 1.25rem;"></span>
                    <span id="errorMsg"></span>
                </div>

                <div class="form-group">
                    <label class="form-label">用户名 / 邮箱</label>
                    <div style="position: relative;">
                        <span class="iconify" data-icon="tabler:user" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-light); font-size: 1.125rem;"></span>
                        <input type="text" name="username" class="form-control" required placeholder="请输入用户名或邮箱" style="padding-left: 2.5rem;">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">密码</label>
                    <div style="position: relative;">
                        <span class="iconify" data-icon="tabler:lock" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-light); font-size: 1.125rem;"></span>
                        <input type="password" name="password" class="form-control" required placeholder="请输入密码" style="padding-left: 2.5rem;">
                    </div>
                </div>

                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; color: var(--text-muted); cursor: pointer;">
                        <input type="checkbox" name="remember" style="width: 1rem; height: 1rem; border-radius: 0.25rem; border: 1px solid var(--border-color); accent-color: var(--color-primary);">
                        <span>记住我</span>
                    </label>
                    <a href="/user/findpwd" style="font-size: 0.875rem; color: var(--color-primary);">忘记密码？</a>
                </div>

                <button type="submit" id="submitBtn" class="btn btn-primary btn-block" style="padding: 0.75rem; font-size: 1rem;">登 录</button>

                <div class="text-center" style="margin-top: 1.5rem;">
                    <span style="font-size: 0.875rem; color: var(--text-muted);">还没有账号？</span>
                    <a href="/user/reg" style="font-size: 0.875rem; font-weight: 500; color: var(--color-primary);">免费注册</a>
                </div>
            </form>

            <!-- Social Login -->
            <?php
            $platformConfig = [
                'qq' => ['label' => 'QQ'],
                'wx' => ['label' => '微信'],
                'alipay' => ['label' => '支付宝'],
                'sina' => ['label' => '微博'],
                'baidu' => ['label' => '百度'],
                'douyin' => ['label' => '抖音'],
                'huawei' => ['label' => '华为'],
                'google' => ['label' => 'Google'],
                'microsoft' => ['label' => 'Microsoft'],
                'wework' => ['label' => '企业微信'],
                'dingtalk' => ['label' => '钉钉'],
                'feishu' => ['label' => '飞书'],
                'gitee' => ['label' => 'Gitee'],
                'github' => ['label' => 'GitHub'],
                'xiaomi' => ['label' => '小米'],
                'bilibili' => ['label' => '哔哩哔哩'],
            ];
            $enabledKeys = array_column($enabledPlatforms ?? [], 'name');
            ?>
            <?php if (count($enabledKeys) > 0): ?>
                <div style="margin-top: 2rem; text-align: center;">
                    <div style="position: relative; margin-bottom: 1.5rem;">
                        <div style="position: absolute; left: 0; top: 50%; width: 100%; height: 1px; background: var(--border-color);"></div>
                        <span style="position: relative; background: var(--bg-surface); padding: 0 1rem; color: var(--text-light); font-size: 0.75rem;">其他登录方式</span>
                    </div>
                    <div style="display: flex; justify-content: center; flex-wrap: wrap; gap: 0.75rem;">
                        <?php foreach ($enabledKeys as $key): ?>
                            <?php if (isset($platformConfig[$key])): ?>
                                <?php $config = $platformConfig[$key]; ?>
                                <button type="button" onclick="socialLogin('<?= $key ?>')"
                                    style="width: 42px; height: 42px; min-width: 42px; border-radius: 50%; border: 1px solid var(--border-color); background: var(--bg-surface); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;"
                                    onmouseover="this.style.borderColor='var(--color-primary)'; this.style.backgroundColor='var(--bg-surface-hover)'; this.style.transform='translateY(-2px)'"
                                    onmouseout="this.style.borderColor='var(--border-color)'; this.style.backgroundColor='var(--bg-surface)'; this.style.transform='translateY(0)'"
                                    title="<?= $config['label'] ?>登录">
                                    <img src="/assets/icon/<?= $key ?>.svg" alt="<?= $config['label'] ?>" style="width: 1.5rem; height: 1.5rem;">
                                </button>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
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

    function socialLogin(provider) {
        window.location.href = '/oauth/' + provider;
    }

    document.getElementById('loginForm').addEventListener('submit', function(e) {
        e.preventDefault();
        hideError();

        const btn = document.getElementById('submitBtn');
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = '登录中...';

        const formData = new FormData(this);
        fetch('/user/login', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => {
                return res.text().then(text => {
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        throw new Error(text || '服务器返回了无效的响应');
                    }
                });
            })
            .then(data => {
                if (data.code === 0) {
                    btn.textContent = '登录成功';
                    toast('登录成功', 'success');
                    setTimeout(() => {
                        window.location.href = data.data.redirect || '/user/dashboard';
                    }, 500);
                } else {
                    showError(data.message || data.msg || '登录失败，请重试');
                    btn.disabled = false;
                    btn.textContent = originalText;
                }
            })
            .catch(err => {
                console.error('Login error:', err);
                let errorMsg = err.message || '请求失败';
                if (errorMsg.length > 200) {
                    errorMsg = '服务器错误，请检查服务器日志';
                }
                showError(errorMsg);
                btn.disabled = false;
                btn.textContent = originalText;
            });
    });
</script>

<?php include ML_ROOT . '/views/layouts/footer.php'; ?>