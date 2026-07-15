<?php 
$pageTitle = '身份认证配置';
// 判断是否为运营商认证模式（provider 为 slsj/shuxun/chuanglan 时都是运营商认证）
$isCarrierMode = in_array($config['provider'] ?? 'manual', ['slsj', 'shuxun', 'chuanglan']);
ob_start(); ?>

<div style="display: flex; flex-direction: column; gap: 1.5rem;">
    <!-- 基本设置 -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">身份认证设置</h3>
            <?php if ($pendingCount > 0): ?>
                <span class="badge badge-error"><?= $pendingCount ?> 条待审核</span>
            <?php endif; ?>
        </div>

        <div class="card-body">
            <form id="verificationForm">
                <input type="hidden" name="_token" value="<?= e($csrf_token) ?>">

                <!-- 功能开关 -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1rem; background-color: var(--bg-surface-hover); border-radius: var(--radius-md);">
                        <div>
                            <div style="font-weight: 500; color: var(--text-main); font-size: 0.875rem;">启用身份认证</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">开启后用户可进行实名认证</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="enabled" value="1" <?= $config['status'] ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1rem; background-color: var(--bg-surface-hover); border-radius: var(--radius-md);">
                        <div>
                            <div style="font-weight: 500; color: var(--text-main); font-size: 0.875rem;">强制认证</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">用户必须完成认证才能使用服务</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="require_verification" value="1" <?= $config['require'] ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>

                <!-- 认证类型 -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1rem; border: 1px solid var(--border-color); border-radius: var(--radius-md);">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <span class="iconify" data-icon="tabler:user" style="color: #3b82f6; font-size: 1.25rem;"></span>
                            <div>
                                <div style="font-weight: 500; color: var(--text-main); font-size: 0.875rem;">个人认证</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">身份证实名认证</div>
                            </div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="personal_enabled" value="1" <?= $config['personal_status'] ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1rem; border: 1px solid var(--border-color); border-radius: var(--radius-md);">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <span class="iconify" data-icon="tabler:building" style="color: #a855f7; font-size: 1.25rem;"></span>
                            <div>
                                <div style="font-weight: 500; color: var(--text-main); font-size: 0.875rem;">企业认证</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">营业执照认证</div>
                            </div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="enterprise_enabled" value="1" <?= $config['enterprise_status'] ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>

                <!-- 认证方式选择 -->
                <div class="form-group">
                    <label class="form-label">认证方式</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div id="manualCard" class="provider-option <?= !$isCarrierMode ? 'active' : '' ?>">
                            <input type="radio" name="provider" value="manual" <?= !$isCarrierMode ? 'checked' : '' ?> style="display: none;">
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div style="width: 2.5rem; height: 2.5rem; background-color: rgba(59, 130, 246, 0.1); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <span class="iconify" data-icon="tabler:user-shield" style="color: #3b82f6; font-size: 1.25rem;"></span>
                                </div>
                                <div style="min-width: 0;">
                                    <div style="font-weight: 600; color: var(--text-main);">管理审核</div>
                                    <div style="font-size: 0.75rem; color: var(--text-secondary);">管理员人工审核认证资料</div>
                                </div>
                            </div>
                            <div class="provider-indicator">
                                <span class="iconify" data-icon="tabler:check"></span>
                            </div>
                        </div>

                        <div id="carrierCard" class="provider-option <?= $isCarrierMode ? 'active' : '' ?>">
                            <input type="radio" name="provider" value="carrier" <?= $isCarrierMode ? 'checked' : '' ?> style="display: none;">
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div style="width: 2.5rem; height: 2.5rem; background-color: rgba(16, 185, 129, 0.1); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <span class="iconify" data-icon="tabler:phone" style="color: #10b981; font-size: 1.25rem;"></span>
                                </div>
                                <div style="min-width: 0;">
                                    <div style="font-weight: 600; color: var(--text-main);">运营商三要素认证</div>
                                    <div style="font-size: 0.75rem; color: var(--text-secondary);">姓名+身份证+手机号实时验证</div>
                                </div>
                            </div>
                            <div class="provider-indicator">
                                <span class="iconify" data-icon="tabler:check"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 管理审核说明 -->
                <div id="manualConfig" style="background-color: var(--bg-surface-hover); padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; <?= $isCarrierMode ? 'display: none;' : '' ?>">
                    <p style="font-size: 0.875rem; color: var(--text-secondary); margin: 0;">
                        <span class="iconify" data-icon="tabler:info-circle" style="vertical-align: middle; margin-right: 0.25rem;"></span>
                        用户提交认证资料后，管理员在下方认证记录中进行人工审核。
                    </p>
                </div>

                <!-- 运营商三要素认证配置（包含收费设置） -->
                <div id="carrierConfig" style="background-color: var(--bg-surface-hover); padding: 1.5rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; <?= !$isCarrierMode ? 'display: none;' : '' ?>">
                    <h4 style="font-weight: 500; color: #10b981; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                        <span class="iconify" data-icon="tabler:phone"></span>
                        运营商三要素认证配置
                    </h4>
                    
                    <!-- 第一行：供应商 + API地址 -->
                    <div class="carrier-form-row">
                        <div class="carrier-form-col">
                            <label class="form-label">认证供应商</label>
                            <select name="carrier_provider" id="carrierProviderSelect" class="form-control">
                                <option value="slsj" <?= ($config['provider'] ?? 'slsj') === 'slsj' ? 'selected' : '' ?>>随联数聚</option>
                                <option value="shuxun" <?= ($config['provider'] ?? '') === 'shuxun' ? 'selected' : '' ?>>数勋科技</option>
                                <option value="chuanglan" <?= ($config['provider'] ?? '') === 'chuanglan' ? 'selected' : '' ?>>创蓝云智</option>
                            </select>
                            <div class="form-hint">选择三要素认证供应商</div>
                        </div>
                        <div class="carrier-form-col">
                            <label class="form-label">API地址</label>
                            <?php
                            // 根据当前供应商显示对应的 API 地址
                            $currentProvider = $config['provider'] ?? 'slsj';
                            $apiUrlMap = [
                                'slsj' => $config['slsj_api_url'] ?? 'https://api.slsj.com',
                                'shuxun' => $config['shuxun_api_url'] ?? 'https://api.shuxuntech.com',
                                'chuanglan' => $config['chuanglan_api_url'] ?? 'https://api.253.com',
                            ];
                            $currentApiUrl = $apiUrlMap[$currentProvider] ?? 'https://api.slsj.com';
                            ?>
                            <input type="text" name="carrier_api_url" id="carrierApiUrl" value="<?= e($currentApiUrl) ?>"
                                class="form-control" style="background-color: var(--bg-surface); cursor: not-allowed;" readonly
                                data-slsj-url="<?= e($config['slsj_api_url'] ?? 'https://api.slsj.com') ?>"
                                data-shuxun-url="<?= e($config['shuxun_api_url'] ?? 'https://api.shuxuntech.com') ?>"
                                data-chuanglan-url="<?= e($config['chuanglan_api_url'] ?? 'https://api.253.com') ?>">
                            <div class="form-hint">
                                <span class="slsj-config" style="<?= in_array($config['provider'] ?? 'slsj', ['shuxun', 'chuanglan']) ? 'display: none;' : '' ?>">
                                    <a href="https://www.slsj.com/product/609" target="_blank" style="color: var(--color-primary); text-decoration: none;">
                                        <span class="iconify" data-icon="tabler:external-link" style="vertical-align: middle;"></span>
                                        前往购买API
                                    </a>
                                </span>
                                <span class="shuxun-config" style="<?= ($config['provider'] ?? 'slsj') !== 'shuxun' ? 'display: none;' : '' ?>">
                                    <a href="https://shuxuntech.com/productDetail?id=4" target="_blank" style="color: var(--color-primary); text-decoration: none;">
                                        <span class="iconify" data-icon="tabler:external-link" style="vertical-align: middle;"></span>
                                        前往购买API
                                    </a>
                                </span>
                                <span class="chuanglan-config" style="<?= ($config['provider'] ?? 'slsj') !== 'chuanglan' ? 'display: none;' : '' ?>">
                                    <a href="https://www.253.com/" target="_blank" style="color: var(--color-primary); text-decoration: none;">
                                        <span class="iconify" data-icon="tabler:external-link" style="vertical-align: middle;"></span>
                                        前往购买API
                                    </a>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- 第二行：随联数聚配置 -->
                    <div class="carrier-form-row slsj-config" style="<?= in_array($config['provider'] ?? 'slsj', ['shuxun', 'chuanglan']) ? 'display: none;' : '' ?>">
                        <div class="carrier-form-col">
                            <label class="form-label">用户编码 (memberId)</label>
                            <input type="text" name="slsj_member_id" value="<?= e($config['slsj_member_id'] ?? '') ?>"
                                class="form-control" placeholder="供应商分配的用户编码">
                            <div class="form-hint">&nbsp;</div>
                        </div>
                        <div class="carrier-form-col">
                            <label class="form-label">API密钥 (appKey)</label>
                            <input type="text" name="slsj_app_key" class="form-control" value="<?= e(!empty($config['slsj_app_key']) ? decrypt($config['slsj_app_key']) : '') ?>" placeholder="请输入API密钥">
                            <div class="form-hint">&nbsp;</div>
                        </div>
                    </div>
                    
                    <!-- 第二行：数勋科技配置 -->
                    <div class="carrier-form-row shuxun-config" style="<?= ($config['provider'] ?? 'slsj') !== 'shuxun' ? 'display: none;' : '' ?>">
                        <div class="carrier-form-col">
                            <label class="form-label">API密钥 (appKey)</label>
                            <input type="text" name="shuxun_app_key" class="form-control" value="<?= e(!empty($config['shuxun_app_key']) ? decrypt($config['shuxun_app_key']) : '') ?>" placeholder="请输入API密钥">
                            <div class="form-hint">&nbsp;</div>
                        </div>
                        <div class="carrier-form-col">
                            <label class="form-label">API密钥 (appSecret)</label>
                            <input type="text" name="shuxun_app_secret" class="form-control" value="<?= e(!empty($config['shuxun_app_secret']) ? decrypt($config['shuxun_app_secret']) : '') ?>" placeholder="请输入API密钥">
                            <div class="form-hint">&nbsp;</div>
                        </div>
                    </div>
                    
                    <!-- 第二行：创蓝云智配置 -->
                    <div class="carrier-form-row chuanglan-config" style="<?= ($config['provider'] ?? 'slsj') !== 'chuanglan' ? 'display: none;' : '' ?>">
                        <div class="carrier-form-col">
                            <label class="form-label">应用ID (appId)</label>
                            <input type="text" name="chuanglan_app_id" value="<?= e($config['chuanglan_app_id'] ?? '') ?>"
                                class="form-control" placeholder="创蓝云智分配的appId">
                            <div class="form-hint">&nbsp;</div>
                        </div>
                        <div class="carrier-form-col">
                            <label class="form-label">API密钥 (appKey)</label>
                            <input type="text" name="chuanglan_app_key" class="form-control" value="<?= e(!empty($config['chuanglan_app_key']) ? decrypt($config['chuanglan_app_key']) : '') ?>" placeholder="请输入API密钥">
                            <div class="form-hint">&nbsp;</div>
                        </div>
                    </div>

                    <!-- 第三行：认证收费设置 -->
                    <div class="carrier-form-row">
                        <div class="carrier-form-col">
                            <label class="form-label">认证收费</label>
                            <select name="fee_enabled" id="feeEnabledSelect" class="form-control">
                                <option value="0" <?= !($config['fee'] ?? 0) ? 'selected' : '' ?>>不收费</option>
                                <option value="1" <?= ($config['fee'] ?? 0) ? 'selected' : '' ?>>收费</option>
                            </select>
                            <div class="form-hint">是否向用户收取认证费用</div>
                        </div>
                        <div class="carrier-form-col">
                            <label class="form-label">认证费用 (元)</label>
                            <input type="number" name="fee_amount" id="feeAmountInput" step="0.01" min="0" 
                                   value="<?= e($config['fee_amount'] ?? '0.00') ?>" class="form-control"
                                   placeholder="请输入认证费用" <?= !($config['fee'] ?? 0) ? 'disabled' : '' ?>>
                            <div class="form-hint">认证失败将自动退款</div>
                        </div>
                    </div>

                    <!-- 说明文字 -->
                    <p class="slsj-config" style="font-size: 0.75rem; color: var(--text-muted); margin: 0; <?= in_array($config['provider'] ?? 'slsj', ['shuxun', 'chuanglan']) ? 'display: none;' : '' ?>">
                        <span class="iconify" data-icon="tabler:info-circle" style="vertical-align: middle;"></span>
                        需要在随联数聚平台申请账号并获取 memberId 和 appKey。三要素认证将验证用户的姓名、身份证号和手机号是否一致。
                    </p>
                    <p class="shuxun-config" style="font-size: 0.75rem; color: var(--text-muted); margin: 0; <?= ($config['provider'] ?? 'slsj') !== 'shuxun' ? 'display: none;' : '' ?>">
                        <span class="iconify" data-icon="tabler:info-circle" style="vertical-align: middle;"></span>
                        需要在数勋科技平台申请账号并获取 appKey 和 appSecret。三要素认证将验证用户的姓名、身份证号和手机号是否一致。
                    </p>
                    <p class="chuanglan-config" style="font-size: 0.75rem; color: var(--text-muted); margin: 0; <?= ($config['provider'] ?? 'slsj') !== 'chuanglan' ? 'display: none;' : '' ?>">
                        <span class="iconify" data-icon="tabler:info-circle" style="vertical-align: middle;"></span>
                        需要在创蓝云智平台申请账号并获取 appId 和 appKey。三要素认证将验证用户的姓名、身份证号和手机号是否一致。
                    </p>
                </div>

                <button type="submit" class="btn btn-primary">保存配置</button>
            </form>
        </div>
    </div>

    <!-- 认证奖励设置（单独卡片） -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title" style="display: flex; align-items: center; gap: 0.5rem;">
                <span class="iconify" data-icon="tabler:gift" style="color: #8b5cf6;"></span>
                认证奖励设置
            </h3>
            <label class="switch">
                <input type="checkbox" name="reward_enabled" id="rewardEnabledSwitch" value="1" form="verificationForm" <?= ($config['reward'] ?? 0) ? 'checked' : '' ?>>
                <span class="slider"></span>
            </label>
        </div>
        <div class="card-body" id="rewardConfigBody" style="<?= ($config['reward'] ?? 0) ? '' : 'display: none;' ?>">
            <div class="carrier-form-row">
                <div class="carrier-form-col">
                    <label class="form-label">奖励产品</label>
                    <select name="reward_product_id" class="form-control" form="verificationForm">
                        <option value="">请选择产品</option>
                        <?php foreach ($products as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= ($config['reward_product_id'] ?? '') == $p['id'] ? 'selected' : '' ?>>
                                <?= e($p['name']) ?> (<?= $p['type'] === 'package' ? '套餐' : '次数包' ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-hint">认证成功后赠送的产品</div>
                </div>
                <div class="carrier-form-col">
                    <label class="form-label">奖励有效期 (天)</label>
                    <input type="number" name="reward_duration" min="1" 
                           value="<?= e($config['reward_duration'] ?? '30') ?>" class="form-control"
                           placeholder="请输入有效期天数" form="verificationForm">
                    <div class="form-hint">奖励套餐的有效期天数</div>
                </div>
            </div>
            <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 1rem; margin-bottom: 0;">
                <span class="iconify" data-icon="tabler:info-circle" style="vertical-align: middle;"></span>
                启用后，用户认证成功将自动获得指定产品的使用权限。
            </p>
        </div>
        <div class="card-body" id="rewardDisabledBody" style="<?= ($config['reward'] ?? 0) ? 'display: none;' : '' ?>">
            <p style="font-size: 0.875rem; color: var(--text-muted); margin: 0;">
                <span class="iconify" data-icon="tabler:info-circle" style="vertical-align: middle; margin-right: 0.25rem;"></span>
                开启右上角开关后，可配置认证成功后赠送的产品和有效期。
            </p>
        </div>
    </div>

    <!-- 认证记录 -->
    <?php if (!empty($verifications)): ?>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">认证记录</h3>
            </div>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>用户</th>
                            <th>类型</th>
                            <th>姓名/企业</th>
                            <th>费用</th>
                            <th>奖励</th>
                            <th>状态</th>
                            <th>时间</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($verifications as $v): ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 500;"><?= e($v['username']) ?></div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);"><?= e($v['email'] ?? $v['phone'] ?? '-') ?></div>
                                </td>
                                <td>
                                    <?php if ($v['type'] === 'personal'): ?>
                                        <span class="badge badge-blue">个人</span>
                                    <?php else: ?>
                                        <span class="badge badge-purple">企业</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= e($v['type'] === 'personal' ? $v['name'] : $v['company']) ?></td>
                                <td>
                                    <?php if (isset($v['fee']) && $v['fee'] > 0): ?>
                                        <span style="color: #f59e0b; font-weight: 500;">¥<?= number_format((float)$v['fee'], 2) ?></span>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted);">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (isset($v['reward']) && $v['reward']): ?>
                                        <span class="badge badge-success" style="display: inline-flex; align-items: center; gap: 0.25rem;">
                                            <span class="iconify" data-icon="tabler:gift" style="font-size: 0.875rem;"></span>
                                            已发放
                                        </span>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted);">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $statusMap = [
                                        0 => ['待审核', 'badge-warning'],
                                        1 => ['已通过', 'badge-success'],
                                        2 => ['已拒绝', 'badge-error'],
                                        3 => ['待人工审核', 'badge-warning'],
                                    ];
                                    $status = $statusMap[$v['status']] ?? ['未知', 'badge-secondary'];
                                    ?>
                                    <span class="badge <?= $status[1] ?>"><?= $status[0] ?></span>
                                </td>
                                <td style="color: var(--text-muted);"><?= date('m-d H:i', strtotime($v['time'])) ?></td>
                                <td>
                                    <?php if (in_array($v['status'], [0, 3])): ?>
                                        <button onclick="reviewVerification(<?= $v['id'] ?>, 'approve')" class="btn btn-text btn-sm" style="color: var(--color-success);">通过</button>
                                        <button onclick="showRejectModal(<?= $v['id'] ?>)" class="btn btn-text btn-sm" style="color: var(--color-error);">拒绝</button>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted);">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- 拒绝原因弹窗 -->
<div id="rejectModal" class="modal">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h3 class="modal-title">拒绝认证</h3>
            <button type="button" class="close-modal" onclick="closeRejectModal()">&times;</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="rejectId">
            <textarea id="rejectReason" rows="3" class="form-control" placeholder="请输入拒绝原因"></textarea>
        </div>
        <div class="modal-footer">
            <button type="button" onclick="closeRejectModal()" class="btn btn-outline">取消</button>
            <button type="button" onclick="submitReject()" class="btn btn-primary" style="background-color: var(--color-error); border-color: var(--color-error);">确认拒绝</button>
        </div>
    </div>
</div>

<style>
    .provider-option {
        position: relative;
        padding: 0.875rem;
        border: 2px solid var(--border-color);
        border-radius: var(--radius-md);
        cursor: pointer;
        transition: all 0.2s;
    }
    .provider-option:hover { border-color: var(--border-color-hover); }
    .provider-option.active { border-color: var(--color-primary); background-color: var(--bg-surface-hover); }
    .provider-indicator {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        width: 1.25rem;
        height: 1.25rem;
        border-radius: 50%;
        border: 2px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.75rem;
        transition: all 0.2s;
    }
    .provider-option.active .provider-indicator { background-color: var(--color-primary); border-color: var(--color-primary); }
    
    
    .carrier-form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1rem;
        align-items: start;
    }
    .carrier-form-col {
        display: grid;
        grid-template-rows: auto 38px auto;
        gap: 0;
        min-width: 0;
    }
    .carrier-form-col .form-label {
        margin-bottom: 0.5rem;
        align-self: end;
    }
    .carrier-form-col .form-control {
        align-self: center;
    }
    .form-hint {
        font-size: 0.75rem;
        color: var(--text-muted);
        margin-top: 0.25rem;
        min-height: 1.125rem;
        align-self: start;
    }
    .carrier-form-col input.form-control,
    .carrier-form-col select.form-control {
        width: 100% !important;
        max-width: none !important;
        min-width: 0 !important;
        box-sizing: border-box !important;
        -webkit-appearance: none !important;
        -moz-appearance: none !important;
        appearance: none !important;
        height: 38px !important;
        padding: 0.5rem 0.75rem !important;
        font-size: 0.875rem !important;
        line-height: 1.5 !important;
    }
    .carrier-form-col select.form-control {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e") !important;
        background-position: right 0.5rem center !important;
        background-repeat: no-repeat !important;
        background-size: 1.5em 1.5em !important;
        padding-right: 2.5rem !important;
    }
</style>

<script>
(function() {
    var manualCard = document.getElementById('manualCard');
    var carrierCard = document.getElementById('carrierCard');
    var manualConfig = document.getElementById('manualConfig');
    var carrierConfig = document.getElementById('carrierConfig');
    var carrierProviderSelect = document.getElementById('carrierProviderSelect');
    var carrierApiUrl = document.getElementById('carrierApiUrl');
    var feeEnabledSelect = document.getElementById('feeEnabledSelect');
    var feeAmountInput = document.getElementById('feeAmountInput');
    var rewardEnabledSwitch = document.getElementById('rewardEnabledSwitch');
    var rewardConfigBody = document.getElementById('rewardConfigBody');
    var rewardDisabledBody = document.getElementById('rewardDisabledBody');

    function selectManual() {
        document.querySelector('input[name="provider"][value="manual"]').checked = true;
        manualConfig.style.display = 'block';
        carrierConfig.style.display = 'none';
        manualCard.classList.add('active');
        carrierCard.classList.remove('active');
    }

    function selectCarrier() {
        document.querySelector('input[name="provider"][value="carrier"]').checked = true;
        carrierConfig.style.display = 'block';
        manualConfig.style.display = 'none';
        carrierCard.classList.add('active');
        manualCard.classList.remove('active');
    }
    function updateProviderConfig() {
        var provider = carrierProviderSelect.value;
        var slsjConfigs = document.querySelectorAll('.slsj-config');
        var shuxunConfigs = document.querySelectorAll('.shuxun-config');
        var chuanglanConfigs = document.querySelectorAll('.chuanglan-config');
        
        // 隐藏所有供应商配置
        slsjConfigs.forEach(function(el) { el.style.display = 'none'; });
        shuxunConfigs.forEach(function(el) { el.style.display = 'none'; });
        chuanglanConfigs.forEach(function(el) { el.style.display = 'none'; });
        
        if (provider === 'shuxun') {
            shuxunConfigs.forEach(function(el) { el.style.display = ''; });
            carrierApiUrl.value = carrierApiUrl.dataset.shuxunUrl || 'https://api.shuxuntech.com';
        } else if (provider === 'chuanglan') {
            chuanglanConfigs.forEach(function(el) { el.style.display = ''; });
            carrierApiUrl.value = carrierApiUrl.dataset.chuanglanUrl || 'https://api.253.com';
        } else {
            slsjConfigs.forEach(function(el) { el.style.display = ''; });
            carrierApiUrl.value = carrierApiUrl.dataset.slsjUrl || 'https://api.slsj.com';
        }
    }
    function toggleFeeAmount() {
        if (feeEnabledSelect.value === '1') {
            feeAmountInput.disabled = false;
        } else {
            feeAmountInput.disabled = true;
        }
    }
    function toggleRewardConfig() {
        if (rewardEnabledSwitch.checked) {
            rewardConfigBody.style.display = 'block';
            rewardDisabledBody.style.display = 'none';
        } else {
            rewardConfigBody.style.display = 'none';
            rewardDisabledBody.style.display = 'block';
        }
    }

    if (manualCard) manualCard.onclick = selectManual;
    if (carrierCard) carrierCard.onclick = selectCarrier;
    if (carrierProviderSelect) carrierProviderSelect.onchange = updateProviderConfig;
    if (feeEnabledSelect) feeEnabledSelect.onchange = toggleFeeAmount;
    if (rewardEnabledSwitch) rewardEnabledSwitch.onchange = toggleRewardConfig;

    var verificationForm = document.getElementById('verificationForm');
    if (verificationForm) {
        verificationForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var formData = Object.fromEntries(new FormData(this));
            formData.carrier_api_url = carrierApiUrl.value;
            formData.reward_enabled = rewardEnabledSwitch.checked ? '1' : '0';
            ajax('<?= admin_url('settings/verification/update') ?>', formData, function(data) {
                toast(data.code === 0 ? '配置已保存' : data.message, data.code === 0 ? 'success' : 'error');
            });
        });
    }

    window.reviewVerification = function(id, action) {
        if (!confirm('确定要通过此认证吗？')) return;
        ajax('<?= admin_url('settings/verification/review') ?>', { _token: '<?= e($csrf_token) ?>', id: id, action: action }, function(data) {
            toast(data.message, data.code === 0 ? 'success' : 'error');
            if (data.code === 0) location.reload();
        });
    };

    window.showRejectModal = function(id) {
        document.getElementById('rejectId').value = id;
        document.getElementById('rejectReason').value = '';
        document.getElementById('rejectModal').classList.add('show');
    };

    window.closeRejectModal = function() {
        document.getElementById('rejectModal').classList.remove('show');
    };

    window.submitReject = function() {
        var id = document.getElementById('rejectId').value;
        var reason = document.getElementById('rejectReason').value;
        if (!reason.trim()) { toast('请填写拒绝原因', 'error'); return; }
        ajax('<?= admin_url('settings/verification/review') ?>', { _token: '<?= e($csrf_token) ?>', id: id, action: 'reject', reason: reason }, function(data) {
            toast(data.message, data.code === 0 ? 'success' : 'error');
            if (data.code === 0) location.reload();
        });
    };
})();
</script>

<?php $settingsContent = ob_get_clean(); ?>
<?php ob_start(); ?>
<?php include ML_ROOT . '/views/layouts/admin_settings.php'; ?>
<?php $content = ob_get_clean(); ?>
<?php include ML_ROOT . '/views/layouts/admin.php'; ?>
