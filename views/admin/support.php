<?php $pageTitle = '技术支持';
ob_start(); ?>

<!-- 页面标题区域 -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body" style="display: flex; align-items: center; padding: 1.5rem;">
        <div style="width: 3.5rem; height: 3.5rem; background: linear-gradient(135deg, var(--color-primary), #6366f1); border-radius: 1rem; display: flex; align-items: center; justify-content: center; margin-right: 1.25rem;">
            <span class="iconify" data-icon="tabler:headset" style="font-size: 1.75rem; color: white;"></span>
        </div>
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 600; color: var(--text-main); margin: 0 0 0.25rem 0;">技术支持</h2>
            <p style="font-size: 0.875rem; color: var(--text-muted); margin: 0;">如需帮助，请通过以下方式联系我们的技术支持团队</p>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
    <!-- 联系方式区域 -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">联系方式</h3>
        </div>
        <div class="card-body">
            <!-- QQ -->
            <div style="display: flex; align-items: center; padding: 1rem; background-color: var(--bg-surface-hover); border-radius: 0.5rem; margin-bottom: 1rem;">
                <img src="/assets/icon/qq.svg" alt="QQ" style="width: 2rem; height: 2rem; margin-right: 1rem;">
                <div style="flex: 1;">
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.125rem;">QQ</div>
                    <div style="font-size: 0.9375rem; font-weight: 500; color: var(--text-main);">
                        <?= !empty($supportInfo['qq']) ? e($supportInfo['qq']) : '暂未配置' ?>
                    </div>
                </div>
            </div>

            <!-- 微信 -->
            <div style="display: flex; align-items: center; padding: 1rem; background-color: var(--bg-surface-hover); border-radius: 0.5rem; margin-bottom: 1rem;">
                <img src="/assets/icon/wx.svg" alt="微信" style="width: 2rem; height: 2rem; margin-right: 1rem;">
                <div style="flex: 1;">
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.125rem;">微信</div>
                    <div style="font-size: 0.9375rem; font-weight: 500; color: var(--text-main);">
                        <?= !empty($supportInfo['wechat']) ? e($supportInfo['wechat']) : '暂未配置' ?>
                    </div>
                </div>
            </div>

            <!-- 网址 -->
            <div style="display: flex; align-items: center; padding: 1rem; background-color: var(--bg-surface-hover); border-radius: 0.5rem; margin-bottom: 1rem;">
                <span class="iconify" data-icon="custom:email" style="font-size: 2rem; margin-right: 1rem;"></span>
                <div style="flex: 1;">
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.125rem;">网址</div>
                    <div style="font-size: 0.9375rem; font-weight: 500; color: var(--text-main);">
                        https://www.dkewl.com
                    </div>
                </div>
            </div>

            <!-- QQ群 -->
            <div style="display: flex; align-items: center; padding: 1rem; background-color: var(--bg-surface-hover); border-radius: 0.5rem;">
                <span class="iconify" data-icon="custom:group" style="font-size: 2rem; margin-right: 1rem;"></span>
                <div style="flex: 1;">
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.125rem;">QQ群</div>
                    <div style="font-size: 0.9375rem; font-weight: 500; color: var(--text-main);">
                        <?php if (!empty($supportInfo['qq_group'])): ?>
                            <a href="<?= e($supportInfo['qq_group']) ?>" target="_blank" rel="noopener noreferrer" style="color: var(--color-primary); text-decoration: none;"><?= e($supportInfo['qq_group']) ?></a>
                        <?php else: ?>
                            暂未配置
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 在线更新区域 -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">在线更新</h3>
        </div>
        <div class="card-body">
            <!-- 当前版本信息 -->
            <div style="display: flex; align-items: center; padding: 1rem; background-color: var(--bg-surface-hover); border-radius: 0.5rem; margin-bottom: 1rem;">
                <div style="width: 2.5rem; height: 2.5rem; background: linear-gradient(135deg, #a78bfa, #8b5cf6); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; margin-right: 1rem;">
                    <span class="iconify" data-icon="tabler:info-circle" style="font-size: 1.25rem; color: white;"></span>
                </div>
                <div style="flex: 1;">
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.125rem;">当前版本</div>
                    <div id="current-version" style="font-size: 0.9375rem; font-weight: 500; color: var(--text-main);">
                        <?= !empty($updateInfo['current_version']) ? e($updateInfo['current_version']) : '1.0.0' ?>
                    </div>
                </div>
            </div>

            <!-- 最新版本信息 -->
            <div style="display: flex; align-items: center; padding: 1rem; background-color: var(--bg-surface-hover); border-radius: 0.5rem; margin-bottom: 1rem;">
                <div style="width: 2.5rem; height: 2.5rem; background: linear-gradient(135deg, #4ade80, #16a34a); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; margin-right: 1rem;">
                    <span class="iconify" data-icon="tabler:rocket" style="font-size: 1.25rem; color: white;"></span>
                </div>
                <div style="flex: 1;">
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.125rem;">最新版本</div>
                    <div id="latest-version" style="font-size: 0.9375rem; font-weight: 500; color: var(--text-main);">
                        <?= !empty($updateInfo['latest_version']) ? e($updateInfo['latest_version']) : '检查中...' ?>
                    </div>
                </div>
            </div>

            <!-- 更新状态 -->
            <div style="display: flex; align-items: center; padding: 1rem; background-color: var(--bg-surface-hover); border-radius: 0.5rem; margin-bottom: 1.5rem;">
                <div style="width: 2.5rem; height: 2.5rem; background: linear-gradient(135deg, #38bdf8, #0ea5e9); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; margin-right: 1rem;">
                    <span class="iconify" data-icon="tabler:circle-check" style="font-size: 1.25rem; color: white;"></span>
                </div>
                <div style="flex: 1;">
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.125rem;">更新状态</div>
                    <div id="update-status" style="font-size: 0.9375rem; font-weight: 500; color: <?= !empty($updateInfo['has_update']) ? 'var(--color-warning)' : 'var(--color-success)' ?>;">
                        <?= !empty($updateInfo['status']) ? e($updateInfo['status']) : '已是最新版本' ?>
                    </div>
                </div>
            </div>

            <!-- 操作按钮 -->
            <div style="display: flex; gap: 0.75rem;">
                <button type="button" id="check-update-btn" class="btn btn-secondary" style="flex: 1;" onclick="checkUpdate()">
                    <span class="iconify" data-icon="tabler:refresh" style="font-size: 1rem; margin-right: 0.375rem;"></span>
                    检查更新
                </button>
                <?php if (!empty($updateInfo['has_update'])): ?>
                <button type="button" id="update-btn" class="btn btn-primary" style="flex: 1;" onclick="goToUpdate()">
                    <span class="iconify" data-icon="tabler:download" style="font-size: 1rem; margin-right: 0.375rem;"></span>
                    立即更新
                </button>
                <?php else: ?>
                <button type="button" id="update-btn" class="btn btn-secondary" style="flex: 1;" disabled>
                    <span class="iconify" data-icon="tabler:download" style="font-size: 1rem; margin-right: 0.375rem;"></span>
                    暂无最新版本
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- 隐藏字段存储下载地址和CSRF令牌 -->
<input type="hidden" id="download-url" value="<?= e($updateInfo['download_url'] ?? 'https://www.xingqingchuang.com/download') ?>">
<input type="hidden" id="csrf-token" value="<?= e($csrf_token ?? '') ?>">

<?php $content = ob_get_clean(); ?>
<?php include ML_ROOT . '/views/layouts/admin.php'; ?>

<script>
// 使用 IIFE 避免变量冲突
(function() {
    // 获取当前管理路径（从URL中提取）
    var _adminPath = window.location.pathname.split('/')[1] || 'admin';

    // 将 checkUpdate 挂载到 window 上，避免 onclick 找不到
    window.checkUpdate = function() {
        var btn = document.getElementById('check-update-btn');
        var originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="iconify" data-icon="tabler:loader-2" style="font-size: 1rem; animation: spin 1s linear infinite;"></span> 检查中...';
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '/' + _adminPath + '/check-update', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                btn.disabled = false;
                btn.innerHTML = originalText;
                
                if (xhr.status === 200) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response.code === 0 && response.data) {
                            updateVersionInfo(response.data);
                            toast('检查完成', 'success');
                        } else {
                            toast(response.message || response.msg || '检查更新失败', 'error');
                        }
                    } catch (e) {
                        toast('解析响应失败', 'error');
                    }
                } else {
                    toast('网络请求失败 (HTTP ' + xhr.status + ')', 'error');
                }
            }
        };
        
        xhr.onerror = function() {
            btn.disabled = false;
            btn.innerHTML = originalText;
            toast('网络连接失败，请检查网络', 'error');
        };
        
        var csrfToken = document.getElementById('csrf-token').value;
        xhr.send('_token=' + encodeURIComponent(csrfToken));
    };

    window.updateVersionInfo = function(data) {
        document.getElementById('current-version').textContent = data.current_version || '1.0.0';
        document.getElementById('latest-version').textContent = data.latest_version || '检查失败';
        var statusEl = document.getElementById('update-status');
        statusEl.textContent = data.status || '已是最新版本';
        statusEl.style.color = data.has_update ? 'var(--color-warning)' : 'var(--color-success)';
        document.getElementById('download-url').value = data.download_url || 'https://www.xingqingchuang.com/download';
        var updateBtn = document.getElementById('update-btn');
        if (data.has_update) {
            updateBtn.disabled = false;
            updateBtn.className = 'btn btn-primary';
            updateBtn.innerHTML = '<span class="iconify" data-icon="tabler:download" style="font-size: 1rem; margin-right: 0.375rem;"></span> 立即更新';
            updateBtn.onclick = goToUpdate;
        } else {
            updateBtn.disabled = true;
            updateBtn.className = 'btn btn-secondary';
            updateBtn.innerHTML = '<span class="iconify" data-icon="tabler:download" style="font-size: 1rem; margin-right: 0.375rem;"></span> 暂无最新版本';
            updateBtn.onclick = null;
        }
    };

    window.goToUpdate = function() {
        var downloadUrl = document.getElementById('download-url').value;
        if (downloadUrl) {
            window.open(downloadUrl, '_blank');
        } else {
            window.open('https://www.xingqingchuang.com/download', '_blank');
        }
    };
})();
</script>

<style>
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
