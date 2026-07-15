<?php $pageTitle = '个人资料';
ob_start(); ?>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1.5rem;">
    <!-- 基本信息 -->
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header">
            <h3 class="card-title">基本信息</h3>
        </div>
        <div class="card-body">
            <form id="profileForm">
                <input type="hidden" name="_token" value="<?= e($csrf_token) ?>">

                <div class="form-group">
                    <label class="form-label">用户名</label>
                    <input type="text" value="<?= e($admin['username']) ?>" disabled class="form-control" style="background-color: var(--bg-surface-hover); color: var(--text-muted);">
                </div>

                <div class="form-group">
                    <label class="form-label">邮箱</label>
                    <input type="email" name="email" value="<?= e($admin['email']) ?>" required class="form-control">
                </div>

                <div class="form-group">
                    <label class="form-label">角色</label>
                    <input type="text" value="系统管理员" disabled class="form-control" style="background-color: var(--bg-surface-hover); color: var(--text-muted);">
                </div>

                <div class="form-group">
                    <label class="form-label">注册时间</label>
                    <input type="text" value="<?= e($admin['time']) ?>" disabled class="form-control" style="background-color: var(--bg-surface-hover); color: var(--text-muted);">
                </div>

                <button type="submit" class="btn btn-primary btn-block">保存修改</button>
            </form>
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

<script>
    document.getElementById('profileForm').addEventListener('submit', function(e) {
        e.preventDefault();
        ajax('<?= admin_url('profile/update') ?>', Object.fromEntries(new FormData(this)), function(data) {
            toast(data.message, data.code === 0 ? 'success' : 'error');
        });
    });

    document.getElementById('passwordForm').addEventListener('submit', function(e) {
        e.preventDefault();
        ajax('<?= admin_url('profile/password') ?>', Object.fromEntries(new FormData(this)), function(data) {
            if (data.code === 0) {
                toast(data.message, 'success');
                e.target.reset();
            } else {
                toast(data.message, 'error');
            }
        });
    });
</script>

<?php $content = ob_get_clean(); ?>
<?php include ML_ROOT . '/views/layouts/admin.php'; ?>
