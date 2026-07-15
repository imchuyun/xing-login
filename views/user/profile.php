<?php $pageTitle = '个人资料';
ob_start(); ?>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1.5rem;">
    <!-- 基本信息 -->
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header">
            <h3 class="card-title">基本信息</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">用户名</label>
                <input type="text" value="<?= e($user['username']) ?>" disabled class="form-control" style="background-color: var(--bg-surface-hover); color: var(--text-muted);">
            </div>

            <div class="form-group">
                <label class="form-label">邮箱</label>
                <div style="display: flex; gap: 0.5rem;">
                    <input type="text" value="<?= e($user['email'] ?? '未绑定') ?>" disabled class="form-control" style="flex: 1; background-color: var(--bg-surface-hover); color: <?= empty($user['email']) ? 'var(--text-muted)' : 'var(--text-main)' ?>;">
                    <button type="button" onclick="openBindModal('email')" class="btn <?= empty($user['email']) ? 'btn-primary' : 'btn-outline' ?>" style="white-space: nowrap;">
                        <?= empty($user['email']) ? '绑定' : '换绑' ?>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">手机号</label>
                <div style="display: flex; gap: 0.5rem;">
                    <input type="text" value="<?= e($user['phone'] ?? '未绑定') ?>" disabled class="form-control" style="flex: 1; background-color: var(--bg-surface-hover); color: <?= empty($user['phone']) ? 'var(--text-muted)' : 'var(--text-main)' ?>;">
                    <button type="button" onclick="openBindModal('phone')" class="btn <?= empty($user['phone']) ? 'btn-primary' : 'btn-outline' ?>" style="white-space: nowrap;">
                        <?= empty($user['phone']) ? '绑定' : '换绑' ?>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">注册时间</label>
                <input type="text" value="<?= e($user['time']) ?>" disabled class="form-control" style="background-color: var(--bg-surface-hover); color: var(--text-muted);">
            </div>
        </div>
    </div>

    <!-- 修改密码 -->
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header">
            <h3 class="card-title">修改密码</h3>
        </div>
        <div class="card-body">
            <form id="passwordForm">
                <input type="hidden" name="_token" value="<?= e($csrf_token) ?>">

                <div class="form-group">
                    <label class="form-label">原密码</label>
                    <input type="password" name="old_password" required class="form-control">
                </div>

                <div class="form-group">
                    <label class="form-label">新密码</label>
                    <input type="password" name="new_password" required class="form-control">
                </div>

                <div class="form-group">
                    <label class="form-label">确认新密码</label>
                    <input type="password" name="confirm_password" required class="form-control">
                </div>

                <button type="submit" class="btn btn-primary btn-block">修改密码</button>
            </form>
        </div>
    </div>
</div>

<!-- 账号绑定 -->
<?php if (!empty($platforms)): ?>
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">账号绑定</h3>
    </div>
    <div class="card-body">
        <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 1rem;">绑定第三方账号后，可使用该账号快速登录</p>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1rem;">
            <?php foreach ($platforms as $platform): 
                $binding = $bindingMap[$platform['name']] ?? null;
                $isBound = !empty($binding);
            ?>
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 1rem; background: var(--bg-surface-hover); border-radius: var(--radius-md); border: 1px solid <?= $isBound ? 'var(--color-success)' : 'var(--border-color)' ?>;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <img src="/assets/icon/<?= e($platform['name']) ?>.svg" alt="<?= e($platform['platform']) ?>" style="width: 2rem; height: 2rem;">
                    <div>
                        <div style="font-weight: 500; color: var(--text-main); font-size: 0.9375rem;"><?= e(get_platform_name($platform['name'])) ?></div>
                        <?php if ($isBound): ?>
                            <div style="font-size: 0.75rem; color: var(--color-success); display: flex; align-items: center; gap: 0.25rem;">
                                <span class="iconify" data-icon="tabler:check" style="font-size: 0.875rem;"></span>
                                已绑定
                                <?php if (!empty($binding['nickname'])): ?>
                                    <span style="color: var(--text-muted);">· <?= e($binding['nickname']) ?></span>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">未绑定</div>
                        <?php endif; ?>
                    </div>
                </div>
                <div>
                    <?php if ($isBound): ?>
                        <button type="button" onclick="unbindOAuth('<?= e($platform['name']) ?>', '<?= e(get_platform_name($platform['name'])) ?>')" class="btn btn-sm btn-outline" style="color: var(--color-error); border-color: var(--color-error);">
                            解绑
                        </button>
                    <?php else: ?>
                        <a href="/oauth/<?= e($platform['name']) ?>?redirect=/user/profile" class="btn btn-sm btn-primary">
                            绑定
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- 绑定弹窗 -->
<div id="bindModal" class="modal">
    <div class="modal-content" style="max-width: 420px;">
        <div class="modal-header">
            <h3 class="modal-title" id="bindModalTitle">绑定</h3>
            <button type="button" class="close-modal" onclick="closeBindModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="bindForm">
                <input type="hidden" name="_token" value="<?= e($csrf_token) ?>">
                <input type="hidden" name="type" id="bindType">
                
                <div class="form-group">
                    <label class="form-label" id="bindTargetLabel">目标</label>
                    <div style="display: flex; gap: 0.5rem;">
                        <input type="text" name="target" id="bindTarget" class="form-control" style="flex: 1;" required>
                        <button type="button" id="sendBindCodeBtn" onclick="sendBindCode()" class="btn btn-outline" style="white-space: nowrap;">获取验证码</button>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">验证码</label>
                    <input type="text" name="verify_code" id="bindVerifyCode" class="form-control" maxlength="6" required placeholder="请输入验证码">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" onclick="closeBindModal()" class="btn btn-outline">取消</button>
            <button type="button" onclick="submitBind()" class="btn btn-primary">确认绑定</button>
        </div>
    </div>
</div>

<script>
    var currentBindType = '';

    function openBindModal(type) {
        currentBindType = type;
        document.getElementById('bindType').value = type;
        
        if (type === 'email') {
            document.getElementById('bindModalTitle').textContent = '<?= empty($user['email']) ? '绑定邮箱' : '换绑邮箱' ?>';
            document.getElementById('bindTargetLabel').textContent = '新邮箱地址';
            document.getElementById('bindTarget').placeholder = '请输入邮箱地址';
            document.getElementById('bindTarget').type = 'email';
        } else {
            document.getElementById('bindModalTitle').textContent = '<?= empty($user['phone']) ? '绑定手机' : '换绑手机' ?>';
            document.getElementById('bindTargetLabel').textContent = '新手机号码';
            document.getElementById('bindTarget').placeholder = '请输入手机号码';
            document.getElementById('bindTarget').type = 'tel';
        }
        
        document.getElementById('bindTarget').value = '';
        document.getElementById('bindVerifyCode').value = '';
        document.getElementById('sendBindCodeBtn').disabled = false;
        document.getElementById('sendBindCodeBtn').textContent = '获取验证码';
        document.getElementById('bindModal').classList.add('show');
    }

    function closeBindModal() {
        document.getElementById('bindModal').classList.remove('show');
    }

    function sendBindCode() {
        var type = currentBindType;
        var target = document.getElementById('bindTarget').value.trim();
        var btn = document.getElementById('sendBindCodeBtn');

        if (type === 'email') {
            if (!target || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(target)) {
                toast('请输入有效的邮箱地址', 'error');
                return;
            }
        } else {
            if (!target || !/^1[3-9]\d{9}$/.test(target)) {
                toast('请输入有效的手机号码', 'error');
                return;
            }
        }

        btn.disabled = true;
        btn.textContent = '发送中...';

        fetch('/api/send-verify-code', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ type: type, target: target, scene: 'bind' })
        })
        .then(res => res.json())
        .then(data => {
            if (data.code === 0) {
                var countdown = 60;
                btn.textContent = countdown + 's';
                var timer = setInterval(function() {
                    countdown--;
                    if (countdown <= 0) {
                        clearInterval(timer);
                        btn.disabled = false;
                        btn.textContent = '获取验证码';
                    } else {
                        btn.textContent = countdown + 's';
                    }
                }, 1000);
                toast('验证码已发送', 'success');
            } else {
                toast(data.message || '发送失败', 'error');
                btn.disabled = false;
                btn.textContent = '获取验证码';
            }
        })
        .catch(function() {
            toast('网络请求失败', 'error');
            btn.disabled = false;
            btn.textContent = '获取验证码';
        });
    }

    function submitBind() {
        var formData = new FormData(document.getElementById('bindForm'));
        ajax('/user/profile/bind', Object.fromEntries(formData), function(data) {
            if (data.code === 0) {
                toast(data.message, 'success');
                setTimeout(function() { location.reload(); }, 1000);
            } else {
                toast(data.message, 'error');
            }
        });
    }

    document.getElementById('passwordForm').addEventListener('submit', function(e) {
        e.preventDefault();
        ajax('/user/profile/password', Object.fromEntries(new FormData(this)), function(data) {
            if (data.code === 0) {
                toast(data.message, 'success');
                e.target.reset();
            } else {
                toast(data.message, 'error');
            }
        });
    });

    // 解绑社交账号
    function unbindOAuth(platform, platformName) {
        if (!confirm('确定要解绑' + platformName + '账号吗？解绑后将无法使用该账号登录。')) {
            return;
        }
        
        ajax('/user/profile/unbind-oauth', {
            _token: '<?= e($csrf_token) ?>',
            platform: platform
        }, function(data) {
            if (data.code === 0) {
                toast(data.message, 'success');
                setTimeout(function() { location.reload(); }, 1000);
            } else {
                toast(data.message, 'error');
            }
        });
    }
</script>

<?php $content = ob_get_clean(); ?>
<?php include ML_ROOT . '/views/layouts/user.php'; ?>
