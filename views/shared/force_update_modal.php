<?php
/**
 * 强制更新弹窗组件
 * 当 $GLOBALS['_FORCE_UPDATE'] 为 true 时显示全屏遮罩弹窗
 * 阻止管理员进行任何操作，直到完成系统更新
 * 
 * Requirements: 1.1-1.5, 2.1-2.5, 5.1-5.3
 */
if (!empty($GLOBALS['_FORCE_UPDATE'])): 
    $forceUpdateInfo = $GLOBALS['_FORCE_UPDATE_INFO'] ?? [];
    $currentVersion = $forceUpdateInfo['current_version'] ?? '-';
    $latestVersion = $forceUpdateInfo['latest_version'] ?? '-';
    $versionTitle = $forceUpdateInfo['version_title'] ?? '';
    $versionDescription = $forceUpdateInfo['version_description'] ?? '';
    $downloadCode = $forceUpdateInfo['download_code'] ?? '';
?>
<div id="force-update-overlay" style="
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(4px);
">
    <!-- 隐藏字段存储 download_code -->
    <input type="hidden" id="force-update-download-code" value="<?= e($downloadCode) ?>">
    
    <div class="card" style="max-width: 480px; width: 90%; margin: 1rem; animation: scaleIn 0.3s ease-out;">
        <div class="card-header" style="background: var(--color-warning); border-bottom: none;">
            <h3 class="card-title" style="color: #fff; display: flex; align-items: center; gap: 0.5rem; margin: 0;">
                <span class="iconify" data-icon="tabler:alert-triangle" style="font-size: 1.25rem;"></span>
                出于某些原因，我们强制要求您更新版本
            </h3>
        </div>
        <div class="card-body" style="text-align: center; padding: 2rem;">
            <!-- 版本标题和说明 -->
            <?php if (!empty($versionTitle) || !empty($versionDescription)): ?>
            <div style="background: var(--bg-surface-hover); border-radius: var(--radius-md); padding: 1rem; margin-bottom: 1.5rem; text-align: left;">
                <?php if (!empty($versionTitle)): ?>
                <div style="font-weight: 600; color: var(--text-main); margin-bottom: 0.5rem; font-size: 1rem;">
                    <?= e($versionTitle) ?>
                </div>
                <?php endif; ?>
                <?php if (!empty($versionDescription)): ?>
                <div style="color: var(--text-muted); font-size: 0.875rem; line-height: 1.6;">
                    <?= nl2br(e($versionDescription)) ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <!-- 版本信息 -->
            <div id="force-update-version-info" style="background: var(--bg-surface-hover); border-radius: var(--radius-md); padding: 1rem; margin-bottom: 1.5rem;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                    <span style="color: var(--text-muted); font-size: 0.875rem;">当前版本</span>
                    <span id="force-update-current-version" style="font-weight: 600; color: var(--text-main);"><?= e($currentVersion) ?></span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted); font-size: 0.875rem;">最新版本</span>
                    <span id="force-update-latest-version" style="font-weight: 600; color: var(--color-success);"><?= e($latestVersion) ?></span>
                </div>
            </div>
            
            <!-- 更新状态显示区域 -->
            <div id="force-update-status" style="display: none; background: var(--bg-surface-hover); border-radius: var(--radius-md); padding: 1rem; margin-bottom: 1.5rem; text-align: left;">
                <div id="force-update-status-text" style="color: var(--text-main); font-size: 0.875rem; display: flex; align-items: center; gap: 0.5rem;">
                    <span class="iconify" data-icon="tabler:loader-2" style="animation: spin 1s linear infinite;"></span>
                    <span>正在更新...</span>
                </div>
            </div>
            
            <p style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 1.5rem;">
                更新期间，普通用户可正常使用系统功能。
            </p>
            
            <!-- 操作按钮 -->
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <button type="button" class="btn btn-outline" onclick="refreshLicenseStatus()" id="refresh-license-btn">
                    <span class="iconify" data-icon="tabler:refresh" style="margin-right: 0.375rem;"></span>
                    刷新状态
                </button>
                <button type="button" class="btn btn-primary" onclick="performForceUpdate()" id="force-update-btn">
                    <span class="iconify" data-icon="tabler:download" style="margin-right: 0.375rem;"></span>
                    立即更新
                </button>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes scaleIn {
    from {
        transform: scale(0.9);
        opacity: 0;
    }
    to {
        transform: scale(1);
        opacity: 1;
    }
}

#force-update-overlay .btn {
    min-width: 120px;
}

#force-update-overlay .btn .iconify {
    font-size: 1.125rem;
}

/* 按钮加载状态 */
#force-update-overlay .btn.loading {
    pointer-events: none;
    opacity: 0.7;
}

#force-update-overlay .btn.loading .iconify {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* 更新状态样式 */
#force-update-status.success {
    background: rgba(var(--color-success-rgb, 34, 197, 94), 0.1);
    border: 1px solid var(--color-success);
}

#force-update-status.error {
    background: rgba(var(--color-danger-rgb, 239, 68, 68), 0.1);
    border: 1px solid var(--color-danger);
}

#force-update-status.success #force-update-status-text {
    color: var(--color-success);
}

#force-update-status.error #force-update-status-text {
    color: var(--color-danger);
}
</style>

<script>
// 全局变量存储 download_code
var forceUpdateDownloadCode = document.getElementById('force-update-download-code').value || '';

/**
 * 刷新授权状态
 * Requirements: 1.1, 1.2, 1.3, 1.4, 1.5
 */
function refreshLicenseStatus() {
    var btn = document.getElementById('refresh-license-btn');
    if (btn.classList.contains('loading')) return;
    
    btn.classList.add('loading');
    hideUpdateStatus();
    
    fetch('<?= admin_url('refresh-license') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: '_token=<?= e($csrf_token ?? '') ?>'
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        btn.classList.remove('loading');
        
        if (data.code === 0) {
            // 存储返回的 download_code (Requirement 1.4)
            if (data.data && data.data.download_code) {
                forceUpdateDownloadCode = data.data.download_code;
                document.getElementById('force-update-download-code').value = forceUpdateDownloadCode;
            }
            
            // 检查是否仍需强制更新
            if (data.data && data.data.force_update === false) {
                // 不再需要强制更新，刷新页面 (Requirement 1.3)
                window.location.reload();
            } else {
                // 仍需强制更新，更新版本信息显示 (Requirement 1.2)
                if (data.data) {
                    if (data.data.current_version) {
                        document.getElementById('force-update-current-version').textContent = data.data.current_version;
                    }
                    if (data.data.latest_version) {
                        document.getElementById('force-update-latest-version').textContent = data.data.latest_version;
                    }
                }
                
                if (typeof toast === 'function') {
                    toast(data.message || '状态已刷新', 'info');
                }
            }
        } else {
            // 显示错误信息 (Requirement 1.5)
            showUpdateStatus(data.message || '刷新失败，请稍后重试', 'error');
        }
    })
    .catch(function(error) {
        btn.classList.remove('loading');
        console.error('License refresh error:', error);
        // 显示网络错误 (Requirement 1.5)
        showUpdateStatus('网络错误，请检查网络连接', 'error');
    });
}

/**
 * 执行强制更新
 * Requirements: 2.1, 2.2, 2.3, 2.4, 2.5
 */
function performForceUpdate() {
    var updateBtn = document.getElementById('force-update-btn');
    var refreshBtn = document.getElementById('refresh-license-btn');
    
    if (updateBtn.classList.contains('loading')) return;
    
    // 检查是否有 download_code，没有则先调用刷新获取 (Requirement 2.2)
    if (!forceUpdateDownloadCode) {
        // 先获取 download_code
        fetchDownloadCodeAndUpdate();
        return;
    }
    
    // 执行更新
    executeUpdate();
}

/**
 * 获取 download_code 后执行更新
 */
function fetchDownloadCodeAndUpdate() {
    var updateBtn = document.getElementById('force-update-btn');
    var refreshBtn = document.getElementById('refresh-license-btn');
    
    // 禁用按钮 (Requirement 5.1)
    updateBtn.classList.add('loading');
    refreshBtn.disabled = true;
    
    showUpdateStatus('正在获取更新信息...', 'loading');
    
    fetch('<?= admin_url('refresh-license') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: '_token=<?= e($csrf_token ?? '') ?>'
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        if (data.code === 0 && data.data && data.data.download_code) {
            forceUpdateDownloadCode = data.data.download_code;
            document.getElementById('force-update-download-code').value = forceUpdateDownloadCode;
            // 继续执行更新
            executeUpdate();
        } else {
            updateBtn.classList.remove('loading');
            refreshBtn.disabled = false;
            showUpdateStatus(data.message || '无法获取下载码，请稍后重试', 'error');
        }
    })
    .catch(function(error) {
        updateBtn.classList.remove('loading');
        refreshBtn.disabled = false;
        console.error('Fetch download code error:', error);
        showUpdateStatus('网络错误，请检查网络连接', 'error');
    });
}

/**
 * 执行更新操作
 */
function executeUpdate() {
    var updateBtn = document.getElementById('force-update-btn');
    var refreshBtn = document.getElementById('refresh-license-btn');
    
    // 禁用按钮并显示"正在更新..."状态 (Requirement 5.1, 5.2)
    updateBtn.classList.add('loading');
    refreshBtn.disabled = true;
    
    showUpdateStatus('正在更新，请稍候...', 'loading');
    
    // 调用 perform-update API (Requirement 2.1)
    fetch('<?= admin_url('perform-update') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: '_token=<?= e($csrf_token ?? '') ?>&download_code=' + encodeURIComponent(forceUpdateDownloadCode)
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        if (data.code === 0) {
            // 更新成功 (Requirement 2.4, 5.3)
            showUpdateStatus(data.message || '更新成功！', 'success');
            
            // 成功后刷新页面
            setTimeout(function() {
                window.location.reload();
            }, 1500);
        } else {
            // 更新失败，显示详细错误信息 (Requirement 2.5)
            updateBtn.classList.remove('loading');
            refreshBtn.disabled = false;
            showUpdateStatus(data.message || '更新失败，请稍后重试', 'error');
        }
    })
    .catch(function(error) {
        updateBtn.classList.remove('loading');
        refreshBtn.disabled = false;
        console.error('Update error:', error);
        showUpdateStatus('网络错误，请检查网络连接', 'error');
    });
}

/**
 * 显示更新状态
 * @param {string} message 状态消息
 * @param {string} type 状态类型: loading, success, error
 */
function showUpdateStatus(message, type) {
    var statusDiv = document.getElementById('force-update-status');
    var statusText = document.getElementById('force-update-status-text');
    
    // 移除所有状态类
    statusDiv.classList.remove('success', 'error');
    
    // 设置图标
    var icon = 'tabler:loader-2';
    var iconStyle = 'animation: spin 1s linear infinite;';
    
    if (type === 'success') {
        icon = 'tabler:check';
        iconStyle = '';
        statusDiv.classList.add('success');
    } else if (type === 'error') {
        icon = 'tabler:alert-circle';
        iconStyle = '';
        statusDiv.classList.add('error');
    }
    
    statusText.innerHTML = '<span class="iconify" data-icon="' + icon + '" style="' + iconStyle + '"></span><span>' + message + '</span>';
    statusDiv.style.display = 'block';
}

/**
 * 隐藏更新状态
 */
function hideUpdateStatus() {
    var statusDiv = document.getElementById('force-update-status');
    statusDiv.style.display = 'none';
    statusDiv.classList.remove('success', 'error');
}
</script>
<?php endif; ?>
