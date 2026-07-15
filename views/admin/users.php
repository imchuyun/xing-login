<?php $pageTitle = '用户管理';
ob_start(); ?>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3 class="card-title">用户列表</h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>用户名</th>
                        <th>邮箱</th>
                        <th>套餐状态</th>
                        <th>角色</th>
                        <th>状态</th>
                        <th>最后登录</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td style="color: var(--text-muted);">#<?= $u['id'] ?></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <div style="width: 2rem; height: 2rem; background-color: var(--bg-surface-hover); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--color-primary);">
                                        <span class="iconify" data-icon="tabler:user"></span>
                                    </div>
                                    <span style="font-weight: 500; color: var(--text-main);"><?= e($u['username']) ?></span>
                                    <?php
                                    $verifyStatus = $u['verify_status'] ?? null;
                                    $verifyType = $u['verify_type'] ?? 'personal';
                                    if ($verifyStatus === '1' || $verifyStatus === 1): ?>
                                        <span class="badge badge-success" style="font-size: 0.625rem; padding: 0.125rem 0.375rem; cursor: pointer;" 
                                              onclick="showVerifyInfo(<?= $u['id'] ?>, '<?= e($u['name'] ?? '') ?>', '<?= e(decrypt($u['id_card'] ?? '')) ?>', '<?= e($u['id_card_front'] ?? '') ?>', '<?= e($u['id_card_back'] ?? '') ?>', '<?= e($verifyType) ?>', '<?= e($u['company'] ?? '') ?>', '<?= e($u['unified_social_credit_code'] ?? '') ?>', '<?= e($u['license'] ?? '') ?>', true)"><?= $verifyType === 'enterprise' ? '企业认证' : '个人认证' ?></span>
                                    <?php elseif ($verifyStatus === '0' || $verifyStatus === 0 || $verifyStatus === '3' || $verifyStatus === 3): ?>
                                        <span class="badge badge-warning" style="font-size: 0.625rem; padding: 0.125rem 0.375rem; cursor: pointer;" 
                                              onclick="showVerifyInfo(<?= $u['id'] ?>, '<?= e($u['name'] ?? '') ?>', '<?= e(decrypt($u['id_card'] ?? '')) ?>', '<?= e($u['id_card_front'] ?? '') ?>', '<?= e($u['id_card_back'] ?? '') ?>', '<?= e($verifyType) ?>', '<?= e($u['company'] ?? '') ?>', '<?= e($u['unified_social_credit_code'] ?? '') ?>', '<?= e($u['license'] ?? '') ?>', false)">待审核</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary" style="font-size: 0.625rem; padding: 0.125rem 0.375rem;">未认证</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td style="color: var(--text-muted);"><?= e($u['email'] ?? $u['phone'] ?? '-') ?></td>
                            <td>
                                <?php if (!empty($u['package_name'])): ?>
                                    <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                                        <span class="badge badge-primary" style="font-size: 0.6875rem;"><?= e($u['package_name']) ?></span>
                                        <?php if (!empty($u['package_expire_time'])): ?>
                                            <?php 
                                            $expireTime = strtotime($u['package_expire_time']);
                                            $daysLeft = max(0, ceil(($expireTime - time()) / 86400));
                                            $isExpired = $expireTime <= time();
                                            ?>
                                            <span style="font-size: 0.6875rem; color: <?= $isExpired ? 'var(--color-error)' : ($daysLeft <= 7 ? 'var(--color-warning)' : 'var(--text-muted)') ?>;">
                                                <?= $isExpired ? '已过期' : "剩余{$daysLeft}天" ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="badge badge-secondary" style="font-size: 0.6875rem;">无套餐</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?= $u['role'] === 'admin' ? 'badge-primary' : 'badge-secondary' ?>">
                                    <?= $u['role'] === 'admin' ? '管理员' : '用户' ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?= $u['status'] === 'enable' ? 'badge-success' : 'badge-error' ?>">
                                    <?= $u['status'] === 'enable' ? '正常' : '禁用' ?>
                                </span>
                            </td>
                            <td style="color: var(--text-muted); font-size: 0.875rem;"><?= e($u['last_login_time'] ?: '-') ?></td>
                            <td>
                                <div style="display: flex; gap: 0.5rem; flex-shrink: 0; white-space: nowrap;">
                                    <button onclick="editUser(<?= $u['id'] ?>, '<?= e($u['username']) ?>', '<?= e($u['email'] ?? '') ?>', '<?= $u['role'] ?>', '<?= $u['status'] ?>')"
                                        class="btn btn-outline btn-sm" style="display: inline-flex; align-items: center; gap: 0.25rem; white-space: nowrap;">
                                        <span class="iconify" data-icon="tabler:edit"></span> 编辑
                                    </button>
                                    <button onclick="showPackageModal(<?= $u['id'] ?>, '<?= e($u['username']) ?>')"
                                        class="btn btn-outline btn-sm" style="display: inline-flex; align-items: center; gap: 0.25rem; border-color: var(--color-primary); color: var(--color-primary); white-space: nowrap;">
                                        <span class="iconify" data-icon="tabler:crown"></span> 套餐
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($totalPages > 1): ?>
        <div class="card-footer" style="display: flex; justify-content: center; gap: 0.5rem; padding: 1rem;">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>" class="btn btn-outline btn-sm">上一页</a>
            <?php endif; ?>
            <span style="display: flex; align-items: center; color: var(--text-muted); font-size: 0.875rem;">第 <?= $page ?> / <?= $totalPages ?> 页</span>
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>" class="btn btn-outline btn-sm">下一页</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- 编辑弹窗 -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">编辑用户: <span id="editUsername" style="color: var(--color-primary);"></span></h3>
            <button type="button" class="close-modal" onclick="hideEditModal()">&times;</button>
        </div>
        <form id="editForm">
            <div class="modal-body">
                <input type="hidden" name="_token" value="<?= e($csrf_token) ?>">
                <input type="hidden" name="id" id="editId">
                <div class="form-group">
                    <label class="form-label">邮箱</label>
                    <input type="email" name="email" id="editEmail" class="form-control" placeholder="用户邮箱">
                </div>
                <div class="form-group">
                    <label class="form-label">密码</label>
                    <input type="password" name="password" id="editPassword" class="form-control" placeholder="留空则不修改密码">
                    <small style="color: var(--text-muted); font-size: 0.75rem;">如不需要修改密码，请留空</small>
                </div>
                <div class="form-group">
                    <label class="form-label">角色</label>
                    <select name="role" id="editRole" class="form-control">
                        <option value="user">普通用户</option>
                        <option value="admin">管理员</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">状态</label>
                    <select name="status" id="editStatus" class="form-control">
                        <option value="enable">正常</option>
                        <option value="disable">禁用</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="hideEditModal()" class="btn btn-outline">取消</button>
                <button type="submit" class="btn btn-primary">保存</button>
            </div>
        </form>
    </div>
</div>

<!-- 套餐管理弹窗 -->
<div id="packageModal" class="modal">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3 class="modal-title">套餐管理: <span id="packageUsername" style="color: var(--color-primary);"></span></h3>
            <button type="button" class="close-modal" onclick="hidePackageModal()">&times;</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="packageUserId" value="">
            <div id="currentPackageInfo" style="margin-bottom: 1.5rem;">
                <h4 style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.75rem; color: var(--text-main);">当前套餐</h4>
                <div id="packageDetails" style="padding: 1rem; background: var(--bg-surface-hover); border-radius: var(--radius-md);">
                    <p style="color: var(--text-muted); margin: 0;">加载中...</p>
                </div>
            </div>
            <div id="packageHistorySection" style="margin-bottom: 1.5rem; display: none;">
                <h4 style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.75rem; color: var(--text-main);">套餐变更历史</h4>
                <div id="packageHistory" style="max-height: 150px; overflow-y: auto;">
                </div>
            </div>
            <div style="border-top: 1px solid var(--border-color); padding-top: 1.5rem;">
                <h4 style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.75rem; color: var(--text-main);">开通/更换套餐</h4>
                <form id="grantPackageForm">
                    <input type="hidden" name="_token" value="<?= e($csrf_token) ?>">
                    <input type="hidden" name="user_id" id="grantUserId">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">选择产品</label>
                            <select name="product_id" id="grantProductId" class="form-control" required onchange="onProductChange(this)">
                                <option value="">请选择产品</option>
                                <option value="0" data-type="none" data-duration="0">无套餐（取消用户套餐）</option>
                                <?php foreach ($products as $product): ?>
                                    <option value="<?= $product['id'] ?>" data-type="<?= e($product['type']) ?>" data-duration="<?= $product['duration'] ?>">
                                        <?= e($product['name']) ?> (<?= $product['type'] === 'package' ? '套餐包' : ($product['type'] === 'account' ? '账号包' : '次数包') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">有效期 (天)</label>
                            <input type="number" name="duration" id="grantDuration" class="form-control" value="30" min="1" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">操作原因</label>
                        <input type="text" name="reason" id="grantReason" class="form-control" placeholder="例如：客户补偿、活动赠送等">
                    </div>
                    <div style="margin-top: 1rem; padding: 0.75rem 1rem; background: rgba(250, 173, 20, 0.1); border: 1px solid rgba(250, 173, 20, 0.3); border-radius: var(--radius-md);">
                        <div style="display: flex; align-items: flex-start; gap: 0.5rem; color: #d48806;">
                            <span class="iconify" data-icon="tabler:alert-circle" style="flex-shrink: 0; margin-top: 0.125rem;"></span>
                            <span style="font-size: 0.8125rem;">开通新套餐将替换用户当前的有效套餐，此操作会记录到套餐变更历史中。</span>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" onclick="hidePackageModal()" class="btn btn-outline">取消</button>
            <button type="button" onclick="submitGrantPackage()" class="btn btn-primary">确认开通</button>
        </div>
    </div>
</div>

<!-- 认证信息弹窗 -->
<div id="verifyModal" class="modal">
    <div class="modal-content" style="max-width: 700px;">
        <div class="modal-header">
            <h3 class="modal-title" id="verifyModalTitle">认证信息审核</h3>
            <button type="button" class="close-modal" onclick="hideVerifyModal()">&times;</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="verifyUserId" value="">
            <input type="hidden" id="verifyReadOnly" value="false">
            
            <!-- 个人认证信息 -->
            <div id="personalVerifySection">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div>
                        <label class="form-label">真实姓名</label>
                        <p id="verifyRealName" style="font-weight: 500; color: var(--text-main);"></p>
                    </div>
                    <div>
                        <label class="form-label">身份证号</label>
                        <p id="verifyIdCard" style="font-weight: 500; color: var(--text-main);"></p>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 1.5rem;">
                    <div>
                        <label class="form-label">身份证正面（人像面）</label>
                        <div id="verifyIdFront" style="border: 1px solid var(--border-color); border-radius: var(--radius-md); overflow: hidden; cursor: pointer;">
                        </div>
                    </div>
                    <div>
                        <label class="form-label">身份证反面（国徽面）</label>
                        <div id="verifyIdBack" style="border: 1px solid var(--border-color); border-radius: var(--radius-md); overflow: hidden; cursor: pointer;">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 企业认证信息 -->
            <div id="enterpriseVerifySection" style="display: none;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div>
                        <label class="form-label">企业名称</label>
                        <p id="verifyCompanyName" style="font-weight: 500; color: var(--text-main);"></p>
                    </div>
                    <div>
                        <label class="form-label">统一社会信用代码</label>
                        <p id="verifyCreditCode" style="font-weight: 500; color: var(--text-main);"></p>
                    </div>
                </div>
                <div style="margin-top: 1.5rem;">
                    <label class="form-label">营业执照</label>
                    <div id="verifyLicense" style="border: 1px solid var(--border-color); border-radius: var(--radius-md); overflow: hidden; cursor: pointer; max-width: 400px;">
                    </div>
                </div>
            </div>
            
            <!-- 审核意见（仅审核模式显示） -->
            <div id="verifyReasonSection" class="form-group" style="margin-top: 1.5rem;">
                <label class="form-label">审核意见（拒绝时必填）</label>
                <input type="text" id="verifyReason" class="form-control" placeholder="请输入审核意见">
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" onclick="hideVerifyModal()" class="btn btn-outline" id="verifyCancelBtn">取消</button>
            <button type="button" onclick="rejectVerification()" class="btn btn-outline" style="border-color: var(--color-error); color: var(--color-error);" id="verifyRejectBtn">拒绝</button>
            <button type="button" onclick="approveVerification()" class="btn btn-primary" id="verifyApproveBtn">通过</button>
        </div>
    </div>
</div>

<script>
(function() {
var adminPath = '<?= get_admin_path() ?>';

window.editUser = function(id, username, email, role, status) {
    document.getElementById('editId').value = id;
    document.getElementById('editUsername').textContent = username;
    document.getElementById('editEmail').value = email || '';
    document.getElementById('editPassword').value = '';
    document.getElementById('editRole').value = role;
    document.getElementById('editStatus').value = status;
    document.getElementById('editModal').classList.add('show');
}

window.hideEditModal = function() {
    document.getElementById('editModal').classList.remove('show');
}

document.getElementById('editForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('/' + adminPath + '/users/update', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.code === 0) {
            alert(data.msg || '更新成功');
            location.reload();
        } else {
            alert(data.msg || '更新失败');
        }
    })
    .catch(err => {
        alert('请求失败');
    });
});
window.showPackageModal = function(userId, username) {
    document.getElementById('packageUserId').value = userId;
    document.getElementById('grantUserId').value = userId;
    document.getElementById('packageUsername').textContent = username;
    document.getElementById('packageDetails').innerHTML = '<p style="color: var(--text-muted); margin: 0;">加载中...</p>';
    document.getElementById('packageHistorySection').style.display = 'none';
    document.getElementById('grantProductId').value = '';
    document.getElementById('grantDuration').value = 30;
    document.getElementById('grantReason').value = '';
    document.getElementById('packageModal').classList.add('show');
    loadUserPackage(userId);
}

window.hidePackageModal = function() {
    document.getElementById('packageModal').classList.remove('show');
}

window.loadUserPackage = function(userId) {
    fetch('/' + adminPath + '/users/package?user_id=' + userId)
    .then(res => res.json())
    .then(data => {
        if (data.code === 0) {
            renderPackageDetails(data.data.package, data.data.stats);
        } else {
            document.getElementById('packageDetails').innerHTML = '<p style="color: var(--color-error); margin: 0;">' + (data.msg || '加载失败') + '</p>';
        }
    })
    .catch(err => {
        document.getElementById('packageDetails').innerHTML = '<p style="color: var(--color-error); margin: 0;">加载失败</p>';
    });
}

window.renderPackageDetails = function(pkg, stats) {
    const container = document.getElementById('packageDetails');
    
    if (!pkg) {
        container.innerHTML = '<p style="color: var(--text-muted); margin: 0;">该用户暂无有效套餐</p>';
        return;
    }
    
    // 处理免费套餐
    if (pkg.is_free || pkg.type === 'free') {
        const platformsArray = pkg.platforms_array || [];
        const platformNames = platformsArray.map(p => {
            // 处理别名
            if (p === 'wechat') p = 'wx';
            if (p === 'weibo') p = 'sina';
            const nameMap = {
                'qq': 'QQ', 'wx': '微信', 'alipay': '支付宝', 'sina': '微博',
                'baidu': '百度', 'douyin': '抖音', 'huawei': '华为', 'google': 'Google',
                'microsoft': '微软', 'wework': '企业微信', 'dingtalk': '钉钉', 'feishu': '飞书',
                'gitee': 'Gitee', 'github': 'GitHub', 'xiaomi': '小米', 'bilibili': 'B站'
            };
            return nameMap[p] || p;
        }).join('、');
        
        container.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <div style="font-weight: 600; color: var(--text-main);">${pkg.name || '免费套餐'}</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">免费用户</div>
                </div>
                <span class="badge badge-info">免费</span>
            </div>
            <div style="margin-top: 0.75rem; font-size: 0.8125rem;">
                <div style="margin-bottom: 0.5rem;">
                    <span style="color: var(--text-muted);">每日调用限制:</span>
                    <span style="color: var(--text-main); margin-left: 0.25rem;">${pkg.daily_limit || 100} 次/天</span>
                </div>
                <div>
                    <span style="color: var(--text-muted);">可用平台:</span>
                    <span style="color: var(--text-main); margin-left: 0.25rem;">${platformNames || '无'}</span>
                </div>
            </div>
        `;
        return;
    }
    
    const typeLabels = {
        'package': '套餐包',
        'account': '账号数量包',
        'quota': '调用次数包'
    };
    
    const expireDate = new Date(pkg.expire_time);
    const now = new Date();
    const daysLeft = Math.max(0, Math.ceil((expireDate - now) / (1000 * 60 * 60 * 24)));
    const isExpired = expireDate <= now;
    
    let usageHtml = '';
    if (pkg.type === 'account' && stats) {
        const used = stats.oauth_users || 0;
        const limit = pkg.account_limit || 0;
        const percent = limit > 0 ? Math.min(100, (used / limit) * 100) : 0;
        usageHtml = `
            <div style="margin-top: 0.75rem;">
                <div style="display: flex; justify-content: space-between; font-size: 0.75rem; margin-bottom: 0.25rem;">
                    <span>授权用户数</span>
                    <span>${used} / ${limit}</span>
                </div>
                <div style="height: 6px; background: var(--bg-surface); border-radius: 3px; overflow: hidden;">
                    <div style="height: 100%; width: ${percent}%; background: ${percent >= 90 ? 'var(--color-error)' : 'var(--color-primary)'}; transition: width 0.3s;"></div>
                </div>
            </div>
        `;
    } else if (pkg.type === 'quota' && stats) {
        const used = stats.calls_in_period || 0;
        const limit = pkg.total_quota || 0;
        const percent = limit > 0 ? Math.min(100, (used / limit) * 100) : 0;
        usageHtml = `
            <div style="margin-top: 0.75rem;">
                <div style="display: flex; justify-content: space-between; font-size: 0.75rem; margin-bottom: 0.25rem;">
                    <span>调用次数</span>
                    <span>${used} / ${limit}</span>
                </div>
                <div style="height: 6px; background: var(--bg-surface); border-radius: 3px; overflow: hidden;">
                    <div style="height: 100%; width: ${percent}%; background: ${percent >= 90 ? 'var(--color-error)' : 'var(--color-primary)'}; transition: width 0.3s;"></div>
                </div>
            </div>
        `;
    }
    
    container.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <div style="font-weight: 600; color: var(--text-main);">${pkg.product_name || '未知套餐'}</div>
                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">${typeLabels[pkg.type] || pkg.type}</div>
            </div>
            <span class="badge ${isExpired ? 'badge-error' : 'badge-success'}">${isExpired ? '已过期' : '有效'}</span>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-top: 0.75rem; font-size: 0.8125rem;">
            <div>
                <span style="color: var(--text-muted);">开始时间:</span>
                <span style="color: var(--text-main); margin-left: 0.25rem;">${pkg.start_time || '-'}</span>
            </div>
            <div>
                <span style="color: var(--text-muted);">到期时间:</span>
                <span style="color: ${isExpired ? 'var(--color-error)' : (daysLeft <= 7 ? 'var(--color-warning)' : 'var(--text-main)')}; margin-left: 0.25rem;">
                    ${pkg.expire_time || '-'} ${!isExpired ? '(剩余' + daysLeft + '天)' : ''}
                </span>
            </div>
        </div>
        ${usageHtml}
    `;
}
document.getElementById('grantProductId').addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    if (selected && selected.dataset.duration) {
        document.getElementById('grantDuration').value = selected.dataset.duration;
    }
});
window.onProductChange = function(select) {
    const selected = select.options[select.selectedIndex];
    const durationInput = document.getElementById('grantDuration');
    
    if (selected.value === '0') {
        durationInput.value = '';
        durationInput.disabled = true;
        durationInput.required = false;
    } else if (selected.value) {
        durationInput.disabled = false;
        durationInput.required = true;
        if (selected.dataset.duration) {
            durationInput.value = selected.dataset.duration;
        }
    } else {
        durationInput.disabled = false;
        durationInput.required = true;
        durationInput.value = '30';
    }
}

window.submitGrantPackage = function() {
    const userId = document.getElementById('grantUserId').value;
    const productId = document.getElementById('grantProductId').value;
    const duration = document.getElementById('grantDuration').value;
    const reason = document.getElementById('grantReason').value;
    
    if (!productId) {
        alert('请选择产品');
        return;
    }
    if (productId === '0') {
        if (!confirm('确定要取消该用户的套餐吗？')) {
            return;
        }
        
        const formData = new FormData();
        formData.append('_token', '<?= e($csrf_token) ?>');
        formData.append('user_id', userId);
        formData.append('product_id', '0');
        formData.append('duration', '0');
        formData.append('reason', reason || '管理员取消套餐');
        
        fetch('/' + adminPath + '/users/grant-package', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.code === 0) {
                alert(data.msg || '套餐已取消');
                location.reload();
            } else {
                alert(data.msg || '操作失败');
            }
        })
        .catch(err => {
            alert('请求失败');
        });
        return;
    }
    
    if (!duration || duration < 1) {
        alert('请输入有效期');
        return;
    }
    
    if (!confirm('确定要为该用户开通此套餐吗？这将替换用户当前的有效套餐。')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('_token', '<?= e($csrf_token) ?>');
    formData.append('user_id', userId);
    formData.append('product_id', productId);
    formData.append('duration', duration);
    formData.append('reason', reason);
    
    fetch('/' + adminPath + '/users/grant-package', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.code === 0) {
            alert(data.msg || '套餐开通成功');
            location.reload();
        } else {
            alert(data.msg || '操作失败');
        }
    })
    .catch(err => {
        alert('请求失败');
    });
}
window.showVerifyInfo = function(userId, realName, idCard, idFront, idBack, verifyType, companyName, creditCode, license, isReadOnly) {
    document.getElementById('verifyUserId').value = userId;
    document.getElementById('verifyReadOnly').value = isReadOnly ? 'true' : 'false';
    document.getElementById('verifyReason').value = '';
    const title = document.getElementById('verifyModalTitle');
    if (isReadOnly) {
        title.textContent = '认证信息查看';
    } else {
        title.textContent = '认证信息审核';
    }
    document.getElementById('verifyReasonSection').style.display = isReadOnly ? 'none' : 'block';
    document.getElementById('verifyRejectBtn').style.display = isReadOnly ? 'none' : 'inline-flex';
    document.getElementById('verifyApproveBtn').style.display = isReadOnly ? 'none' : 'inline-flex';
    document.getElementById('verifyCancelBtn').textContent = isReadOnly ? '关闭' : '取消';
    const personalSection = document.getElementById('personalVerifySection');
    const enterpriseSection = document.getElementById('enterpriseVerifySection');
    
    if (verifyType === 'enterprise') {
        personalSection.style.display = 'none';
        enterpriseSection.style.display = 'block';
        
        document.getElementById('verifyCompanyName').textContent = companyName || '-';
        document.getElementById('verifyCreditCode').textContent = creditCode || '-';
        
        const licenseContainer = document.getElementById('verifyLicense');
        if (license) {
            licenseContainer.innerHTML = `<img src="${license}" style="width: 100%; display: block;" onclick="window.open('${license}', '_blank')">`;
        } else {
            licenseContainer.innerHTML = '<p style="padding: 2rem; text-align: center; color: var(--text-muted);">未上传</p>';
        }
    } else {
        personalSection.style.display = 'block';
        enterpriseSection.style.display = 'none';
        
        document.getElementById('verifyRealName').textContent = realName || '-';
        document.getElementById('verifyIdCard').textContent = idCard || '-';
        
        const frontContainer = document.getElementById('verifyIdFront');
        const backContainer = document.getElementById('verifyIdBack');
        
        if (idFront) {
            frontContainer.innerHTML = `<img src="${idFront}" style="width: 100%; display: block;" onclick="window.open('${idFront}', '_blank')">`;
        } else {
            frontContainer.innerHTML = '<p style="padding: 2rem; text-align: center; color: var(--text-muted);">未上传</p>';
        }
        
        if (idBack) {
            backContainer.innerHTML = `<img src="${idBack}" style="width: 100%; display: block;" onclick="window.open('${idBack}', '_blank')">`;
        } else {
            backContainer.innerHTML = '<p style="padding: 2rem; text-align: center; color: var(--text-muted);">未上传</p>';
        }
    }
    
    document.getElementById('verifyModal').classList.add('show');
}

window.hideVerifyModal = function() {
    document.getElementById('verifyModal').classList.remove('show');
}

window.approveVerification = function() {
    const userId = document.getElementById('verifyUserId').value;
    const reason = document.getElementById('verifyReason').value;
    
    if (!confirm('确定通过该用户的认证申请吗？')) {
        return;
    }
    
    submitVerificationReview(userId, 'approve', reason);
}

window.rejectVerification = function() {
    const userId = document.getElementById('verifyUserId').value;
    const reason = document.getElementById('verifyReason').value;
    
    if (!reason) {
        alert('请填写拒绝原因');
        return;
    }
    
    if (!confirm('确定拒绝该用户的认证申请吗？')) {
        return;
    }
    
    submitVerificationReview(userId, 'reject', reason);
}

window.submitVerificationReview = function(userId, action, reason) {
    const formData = new FormData();
    formData.append('_token', '<?= e($csrf_token) ?>');
    formData.append('user_id', userId);
    formData.append('action', action);
    formData.append('reason', reason);
    
    fetch('/' + adminPath + '/settings/verification/review', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.code === 0) {
            alert(data.msg || '操作成功');
            location.reload();
        } else {
            alert(data.msg || '操作失败');
        }
    })
    .catch(err => {
        alert('请求失败');
    });
}
document.querySelectorAll('.modal').forEach(function(modal) {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('show');
        }
    });
});
})();
</script>

<?php $content = ob_get_clean(); ?>

<?php include ML_ROOT . '/views/layouts/admin.php'; ?>
