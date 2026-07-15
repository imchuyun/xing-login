<?php $pageTitle = '身份认证';
ob_start(); ?>

<div style="display: flex; flex-direction: column; gap: 1.5rem;">
    <!-- 强制认证提示 -->
    <?php if ($config['require'] && (!$verification || $verification['status'] != 1)): ?>
        <div class="alert alert-warning" style="display: flex; align-items: center; gap: 1rem; padding: 1rem 1.5rem; background: linear-gradient(135deg, rgba(250, 173, 20, 0.1) 0%, rgba(250, 140, 22, 0.1) 100%); border: 1px solid rgba(250, 173, 20, 0.3); border-radius: var(--radius-lg);">
            <div style="width: 3rem; height: 3rem; background-color: rgba(250, 173, 20, 0.15); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <span class="iconify" data-icon="tabler:shield-check" style="font-size: 1.5rem; color: #fa8c16;"></span>
            </div>
            <div style="flex: 1;">
                <h3 style="font-size: 1rem; font-weight: 600; color: #d46b08; margin: 0 0 0.25rem 0;">需要完成实名认证</h3>
                <p style="color: #ad6800; margin: 0; font-size: 0.875rem;">根据平台规定，您需要完成实名认证后才能使用全部功能。请选择下方的认证方式完成认证。</p>
            </div>
        </div>
    <?php endif; ?>

    <!-- 认证状态展示 -->
    <?php if ($verification): ?>
        <?php if ($verification['status'] == 1): ?>
            <!-- 已通过 -->
            <div style="display: flex; justify-content: center; align-items: center; min-height: calc(100vh - 200px);">
                <div class="card" style="max-width: 500px; width: 100%;">
                    <div class="card-body" style="text-align: center; padding: 3rem 2rem;">
                        <div style="width: 5rem; height: 5rem; background-color: rgba(82, 196, 26, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                            <span class="iconify" data-icon="tabler:circle-check-filled" style="font-size: 2.5rem; color: var(--color-success);"></span>
                        </div>
                        <h3 style="font-size: 1.25rem; font-weight: 600; color: var(--color-success); margin-bottom: 0.75rem;">
                            <?= $verification['type'] === 'personal' ? '个人认证已通过' : '企业认证已通过' ?>
                        </h3>
                        <p style="font-size: 1.5rem; font-weight: 600; color: var(--text-main); margin-bottom: 1rem;">
                            <?= $verification['type'] === 'personal' ? e($verification['name']) : e($verification['company']) ?>
                        </p>
                        <p style="font-size: 0.875rem; color: var(--text-muted);">认证时间：<?= $verification['verified_time'] ?></p>
                    </div>
                </div>
            </div>

        <?php elseif (in_array($verification['status'], [0, 3])): ?>
            <!-- 审核中 -->
            <div style="display: flex; justify-content: center; align-items: center; min-height: calc(100vh - 200px);">
                <div class="card" style="max-width: 500px; width: 100%;">
                    <div class="card-body" style="text-align: center; padding: 3rem 2rem;">
                        <div style="width: 5rem; height: 5rem; background-color: rgba(250, 140, 22, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                            <span class="iconify" data-icon="tabler:loader-2" style="font-size: 2.5rem; color: var(--color-warning); animation: spin 1s linear infinite;"></span>
                        </div>
                        <h3 style="font-size: 1.25rem; font-weight: 600; color: var(--color-warning); margin-bottom: 0.5rem;">认证审核中</h3>
                        <p style="color: var(--text-secondary); margin-bottom: 0.5rem;">
                            <?php if ($verification['type'] === 'personal'): ?>
                                个人认证 · <?= e($verification['name']) ?>
                            <?php else: ?>
                                企业认证 · <?= e($verification['company']) ?>
                            <?php endif; ?>
                        </p>
                        <p style="font-size: 0.875rem; color: var(--text-muted);">提交时间：<?= $verification['time'] ?></p>
                        <p style="font-size: 0.875rem; color: var(--text-muted);">预计1-3个工作日完成审核</p>
                    </div>
                </div>
            </div>
        <?php elseif ($verification['status'] == 2): ?>
            <!-- 被拒绝 -->
            <div class="alert alert-error" style="display: flex; align-items: flex-start; gap: 1rem; padding: 1rem 1.5rem; background: linear-gradient(135deg, rgba(245, 34, 45, 0.08) 0%, rgba(207, 19, 34, 0.08) 100%); border: 1px solid rgba(245, 34, 45, 0.2); border-radius: var(--radius-lg);">
                <div style="width: 2.5rem; height: 2.5rem; background-color: rgba(245, 34, 45, 0.12); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 0.125rem;">
                    <span class="iconify" data-icon="tabler:circle-x-filled" style="font-size: 1.25rem; color: #cf1322;"></span>
                </div>
                <div style="flex: 1; min-width: 0;">
                    <h3 style="font-size: 1rem; font-weight: 600; color: #a8071a; margin: 0 0 0.5rem 0;">认证未通过</h3>
                    <p style="color: #cf1322; margin: 0 0 0.5rem 0; font-size: 0.875rem; word-break: break-word; line-height: 1.5;">
                        <strong>拒绝原因：</strong><?= e($verification['reason'] ?: '资料不符合要求') ?>
                    </p>
                    <p style="font-size: 0.8125rem; color: #f5222d; opacity: 0.85; margin: 0;">您可以修改信息后重新提交认证申请</p>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- 认证表单（未认证或被拒绝时显示） -->
    <?php if (!$verification || $verification['status'] == 2): ?>
        <!-- 收费提示信息 -->
        <?php if (!empty($config['fee']) && $config['fee_amount'] > 0): ?>
        <div class="alert alert-info" style="display: flex; align-items: center; gap: 1rem; padding: 1rem 1.5rem; background: linear-gradient(135deg, rgba(24, 144, 255, 0.08) 0%, rgba(9, 109, 217, 0.08) 100%); border: 1px solid rgba(24, 144, 255, 0.2); border-radius: var(--radius-lg);">
            <div style="width: 2.5rem; height: 2.5rem; background-color: rgba(24, 144, 255, 0.12); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <span class="iconify" data-icon="tabler:currency-yuan" style="font-size: 1.25rem; color: #1890ff;"></span>
            </div>
            <p style="color: #1890ff; margin: 0; font-size: 0.875rem; line-height: 1.5;">
                您将支付认证费用 <strong style="font-size: 1.125rem;">¥<?= number_format((float)$config['fee_amount'], 2) ?></strong> 元，该费用将由验证商收取。
            </p>
        </div>
        <?php endif; ?>

        <?php if (!empty($config['reward']) && !empty($rewardProduct)): ?>
        <div class="alert alert-success" style="display: flex; align-items: flex-start; gap: 1rem; padding: 1rem 1.5rem; background: linear-gradient(135deg, rgba(82, 196, 26, 0.08) 0%, rgba(56, 158, 13, 0.08) 100%); border: 1px solid rgba(82, 196, 26, 0.2); border-radius: var(--radius-lg);">
            <div style="width: 2.5rem; height: 2.5rem; background-color: rgba(82, 196, 26, 0.12); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 0.125rem;">
                <span class="iconify" data-icon="tabler:gift" style="font-size: 1.25rem; color: #52c41a;"></span>
            </div>
            <div style="flex: 1; min-width: 0;">
                <h3 style="font-size: 1rem; font-weight: 600; color: #389e0d; margin: 0 0 0.5rem 0;">认证奖励</h3>
                <p style="color: #52c41a; margin: 0 0 0.5rem 0; font-size: 0.875rem; line-height: 1.5;">
                    认证成功后将获得 <strong><?= e($rewardProduct['name']) ?></strong> <strong><?= (int)$config['reward_duration'] ?>天</strong> 使用权
                </p>
                <p style="font-size: 0.8125rem; color: #73d13d; opacity: 0.85; margin: 0;">完成实名认证即可领取奖励套餐</p>
            </div>
        </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(380px, 1fr)); gap: 1.5rem; align-items: stretch;">
            <!-- 个人认证 -->
            <?php if ($config['personal_status']): ?>
                <div class="card" style="display: flex; flex-direction: column;">
                    <div class="card-body" style="flex: 1; display: flex; flex-direction: column;">
                        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.5rem;">
                            <div style="width: 2.5rem; height: 2.5rem; background: linear-gradient(135deg, #1890ff 0%, #096dd9 100%); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <span class="iconify" data-icon="tabler:user" style="font-size: 1.25rem; color: #fff;"></span>
                            </div>
                            <div>
                                <h3 style="font-size: 1rem; font-weight: 600; color: var(--text-main); margin: 0;">个人认证</h3>
                                <p style="font-size: 0.75rem; color: var(--text-muted); margin: 0;">适用于个人开发者</p>
                            </div>
                        </div>

                        <form id="personalForm" enctype="multipart/form-data" style="flex: 1; display: flex; flex-direction: column;">
                            <input type="hidden" name="_token" value="<?= e($csrf_token) ?>">

                            <div class="form-group">
                                <label class="form-label">真实姓名 <span style="color: var(--color-error);">*</span></label>
                                <input type="text" name="real_name" required class="form-control" placeholder="请输入身份证上的姓名">
                            </div>

                            <div class="form-group">
                                <label class="form-label">身份证号 <span style="color: var(--color-error);">*</span></label>
                                <input type="text" name="id_card_number" required maxlength="18" class="form-control" placeholder="请输入18位身份证号">
                            </div>

                            <div class="form-group">
                                <label class="form-label">手机号 <span style="color: var(--color-error);">*</span></label>
                                <input type="tel" name="mobile" required maxlength="11" pattern="^1[3-9]\d{9}$" class="form-control" placeholder="请输入11位手机号">
                                <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">用于运营商三要素验证，认证通过后将绑定到您的账户</p>
                            </div>

                            <div class="form-group">
                                <label class="form-label">身份证人像面照片 <span style="color: var(--text-muted); font-size: 0.75rem;">（选填）</span></label>
                                <div class="upload-box" onclick="document.getElementById('id_card_front').click()">
                                    <input type="file" name="id_card_front" id="id_card_front" accept="image/*" style="display: none;" onchange="previewImage(this, 'frontPreview')">
                                    <img id="frontPreview" class="upload-preview">
                                    <div id="frontPlaceholder">
                                        <span class="iconify" data-icon="tabler:photo-plus" style="font-size: 2rem; color: var(--text-light);"></span>
                                        <p style="font-size: 0.875rem; color: var(--text-muted); margin: 0.5rem 0 0 0;">点击上传身份证人像面</p>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">身份证国徽面照片 <span style="color: var(--text-muted); font-size: 0.75rem;">（选填）</span></label>
                                <div class="upload-box" onclick="document.getElementById('id_card_back').click()">
                                    <input type="file" name="id_card_back" id="id_card_back" accept="image/*" style="display: none;" onchange="previewImage(this, 'backPreview')">
                                    <img id="backPreview" class="upload-preview">
                                    <div id="backPlaceholder">
                                        <span class="iconify" data-icon="tabler:photo-plus" style="font-size: 2rem; color: var(--text-light);"></span>
                                        <p style="font-size: 0.875rem; color: var(--text-muted); margin: 0.5rem 0 0 0;">点击上传身份证国徽面</p>
                                    </div>
                                </div>
                            </div>

                            <div style="margin-top: auto; padding-top: 1rem;">
                                <button type="submit" class="btn btn-primary btn-block" style="padding: 0.75rem;">提交个人认证</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <!-- 企业认证 -->
            <?php if ($config['enterprise_status']): ?>
                <div class="card" style="display: flex; flex-direction: column;">
                    <div class="card-body" style="flex: 1; display: flex; flex-direction: column;">
                        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.5rem;">
                            <div style="width: 2.5rem; height: 2.5rem; background: linear-gradient(135deg, #722ed1 0%, #531dab 100%); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <span class="iconify" data-icon="tabler:building" style="font-size: 1.25rem; color: #fff;"></span>
                            </div>
                            <div>
                                <h3 style="font-size: 1rem; font-weight: 600; color: var(--text-main); margin: 0;">企业认证</h3>
                                <p style="font-size: 0.75rem; color: var(--text-muted); margin: 0;">适用于企业/组织</p>
                            </div>
                        </div>

                        <form id="enterpriseForm" enctype="multipart/form-data" style="flex: 1; display: flex; flex-direction: column;">
                            <input type="hidden" name="_token" value="<?= e($csrf_token) ?>">

                            <div class="form-group">
                                <label class="form-label">企业名称 <span style="color: var(--color-error);">*</span></label>
                                <input type="text" name="company_name" required class="form-control" placeholder="请输入营业执照上的企业名称">
                            </div>

                            <div class="form-group">
                                <label class="form-label">统一社会信用代码 <span style="color: var(--color-error);">*</span></label>
                                <input type="text" name="unified_social_credit_code" required maxlength="18" class="form-control" placeholder="请输入18位统一社会信用代码">
                            </div>

                            <div class="form-group">
                                <label class="form-label">法人姓名 <span style="color: var(--color-error);">*</span></label>
                                <input type="text" name="legal_person_name" required class="form-control" placeholder="请输入法定代表人姓名">
                            </div>

                            <div class="form-group">
                                <label class="form-label">法人身份证号 <span style="color: var(--color-error);">*</span></label>
                                <input type="text" name="legal_person_id_card" required maxlength="18" class="form-control" placeholder="请输入法人18位身份证号">
                            </div>

                            <div class="form-group">
                                <label class="form-label">法人手机号 <span style="color: var(--color-error);">*</span></label>
                                <input type="tel" name="legal_person_mobile" required maxlength="11" pattern="^1[3-9]\d{9}$" class="form-control" placeholder="请输入法人11位手机号">
                                <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">用于运营商三要素验证，认证通过后将绑定到您的账户</p>
                            </div>

                            <div class="form-group">
                                <label class="form-label">营业执照照片 <span style="color: var(--text-muted); font-size: 0.75rem;">（选填）</span></label>
                                <div class="upload-box" onclick="document.getElementById('business_license').click()">
                                    <input type="file" name="business_license" id="business_license" accept="image/*" style="display: none;" onchange="previewImage(this, 'licensePreview')">
                                    <img id="licensePreview" class="upload-preview">
                                    <div id="licensePlaceholder">
                                        <span class="iconify" data-icon="tabler:photo-plus" style="font-size: 2rem; color: var(--text-light);"></span>
                                        <p style="font-size: 0.875rem; color: var(--text-muted); margin: 0.5rem 0 0 0;">点击上传营业执照</p>
                                    </div>
                                </div>
                            </div>

                            <div style="margin-top: auto; padding-top: 1rem;">
                                <button type="submit" class="btn btn-primary btn-block" style="padding: 0.75rem; background: linear-gradient(135deg, #722ed1 0%, #531dab 100%); border: none;">提交企业认证</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- 认证须知 -->
        <div class="card">
            <div class="card-body">
                <h3 class="card-title" style="margin-bottom: 1rem;">认证须知</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; color: var(--text-secondary); font-size: 0.875rem;">
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <div style="display: flex; align-items: flex-start; gap: 0.5rem;">
                            <span class="iconify" data-icon="tabler:info-circle-filled" style="color: var(--color-primary); flex-shrink: 0; margin-top: 0.125rem;"></span>
                            <span>认证信息仅用于身份核验，我们将严格保护您的隐私</span>
                        </div>
                        <div style="display: flex; align-items: flex-start; gap: 0.5rem;">
                            <span class="iconify" data-icon="tabler:info-circle-filled" style="color: var(--color-primary); flex-shrink: 0; margin-top: 0.125rem;"></span>
                            <span>手机号用于运营商三要素验证，请确保与身份证信息一致</span>
                        </div>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <div style="display: flex; align-items: flex-start; gap: 0.5rem;">
                            <span class="iconify" data-icon="tabler:info-circle-filled" style="color: var(--color-primary); flex-shrink: 0; margin-top: 0.125rem;"></span>
                            <span>认证通过后，手机号将绑定到您的账户</span>
                        </div>
                        <div style="display: flex; align-items: flex-start; gap: 0.5rem;">
                            <span class="iconify" data-icon="tabler:info-circle-filled" style="color: var(--color-primary); flex-shrink: 0; margin-top: 0.125rem;"></span>
                            <span>如有疑问请联系客服</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- 认证支付弹窗 -->
<?php if (!empty($config['fee']) && $config['fee_amount'] > 0 && !empty($payMethods)): ?>
<div id="verifyPayModal" class="modal">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h3 class="modal-title">支付认证费用</h3>
            <button onclick="hidePayModal()" class="close-modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="verifyPayForm">
                <input type="hidden" name="_token" value="<?= e($csrf_token) ?>">

                <div style="background: var(--bg-surface-hover); border-radius: var(--radius-md); padding: 1rem; margin-bottom: 1rem;">
                    <p style="color: var(--text-muted); font-size: 0.875rem;">认证费用</p>
                    <p style="font-size: 1.5rem; font-weight: 700; color: var(--color-primary); margin-top: 0.5rem;">¥<?= number_format((float)$config['fee_amount'], 2) ?></p>
                    <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem;">该费用将由验证商收取</p>
                </div>

                <div class="form-group">
                    <label class="form-label">选择支付方式</label>
                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <?php if (in_array('alipay', $payMethods)): ?>
                        <label class="pay-method-option">
                            <input type="radio" name="pay_method" value="alipay">
                            <img src="/assets/icon/alipay.svg" alt="支付宝" style="width: 1.5rem; height: 1.5rem;">
                            <span>支付宝</span>
                        </label>
                        <?php endif; ?>

                        <?php if (in_array('wechat', $payMethods)): ?>
                        <label class="pay-method-option">
                            <input type="radio" name="pay_method" value="wechat">
                            <img src="/assets/icon/wx.svg" alt="微信" style="width: 1.5rem; height: 1.5rem;">
                            <span>微信支付</span>
                        </label>
                        <?php endif; ?>

                        <?php if (in_array('qqpay', $payMethods)): ?>
                        <label class="pay-method-option">
                            <input type="radio" name="pay_method" value="qqpay">
                            <img src="/assets/icon/qq.svg" alt="QQ" style="width: 1.5rem; height: 1.5rem;">
                            <span>QQ钱包</span>
                        </label>
                        <?php endif; ?>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block" style="margin-top: 1rem;">确认支付</button>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
    .upload-box {
        border: 2px dashed var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
        background-color: var(--bg-surface-hover);
        position: relative;
        overflow: hidden;
        min-height: 120px;
    }

    .upload-box:hover {
        border-color: var(--color-primary);
        background-color: rgba(var(--primary-rgb), 0.05);
    }

    .upload-box.has-image {
        padding: 0;
        border-style: solid;
        border-color: var(--color-primary);
    }

    .upload-preview {
        display: none;
        width: 100%;
        height: 100%;
        object-fit: cover;
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
    }

    .upload-box.has-image {
        min-height: 150px;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    .pay-method-option {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        cursor: pointer;
        transition: all 0.2s;
    }

    .pay-method-option:hover {
        background: var(--bg-surface-hover);
    }

    .pay-method-option input {
        display: none;
    }

    .pay-method-option.selected {
        border-color: var(--color-primary);
        background: rgba(var(--primary-rgb), 0.08);
    }
</style>

<script>
    // 认证费用配置
    const verificationFee = <?= (!empty($config['fee']) && $config['fee_amount'] > 0) ? number_format((float)$config['fee_amount'], 2, '.', '') : '0' ?>;
    const payMethods = <?= json_encode($payMethods ?? []) ?>;
    
    // 临时存储表单数据
    let pendingFormData = null;
    let pendingFormType = null;
    let pendingSubmitBtn = null;

    function previewImage(input, previewId) {
        const preview = document.getElementById(previewId);
        const placeholder = preview.nextElementSibling;
        const uploadBox = preview.closest('.upload-box');

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                if (placeholder) placeholder.style.display = 'none';
                if (uploadBox) uploadBox.classList.add('has-image');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
    
    function validateMobile(mobile) {
        return /^1[3-9]\d{9}$/.test(mobile);
    }
    
    // 显示支付弹窗
    function showPayModal() {
        document.getElementById('verifyPayModal').classList.add('show');
    }
    
    // 隐藏支付弹窗
    function hidePayModal() {
        document.getElementById('verifyPayModal').classList.remove('show');
        // 恢复按钮状态
        if (pendingSubmitBtn) {
            pendingSubmitBtn.disabled = false;
            pendingSubmitBtn.innerHTML = pendingFormType === 'personal' ? '提交个人认证' : '提交企业认证';
        }
        pendingFormData = null;
        pendingFormType = null;
        pendingSubmitBtn = null;
    }
    
    // 直接提交认证（无需支付或支付成功后）
    function submitVerification(formData, formType, submitBtn) {
        const url = formType === 'personal' ? '/user/verification/personal' : '/user/verification/enterprise';
        const btnText = formType === 'personal' ? '提交个人认证' : '提交企业认证';
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="iconify" data-icon="tabler:loader-2" style="animation: spin 1s linear infinite;"></span> 提交中...';

        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => {
            if (!res.ok) throw new Error('服务器响应错误');
            return res.json();
        })
        .then(data => {
            if (data.code === 0) {
                toast(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                toast(data.message, 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = btnText;
            }
        })
        .catch(err => {
            console.error('提交错误:', err);
            toast('网络错误，请稍后重试', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = btnText;
        });
    }
    
    // 处理表单提交
    function handleFormSubmit(form, formType) {
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        const mobile = formType === 'personal' ? formData.get('mobile') : formData.get('legal_person_mobile');
        
        if (!validateMobile(mobile)) {
            toast(formType === 'personal' ? '请输入正确的11位手机号' : '请输入正确的法人11位手机号', 'error');
            return;
        }
        
        // 如果需要收费且有支付方式，显示支付弹窗
        if (verificationFee > 0 && payMethods.length > 0) {
            pendingFormData = formData;
            pendingFormType = formType;
            pendingSubmitBtn = submitBtn;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="iconify" data-icon="tabler:loader-2" style="animation: spin 1s linear infinite;"></span> 处理中...';
            showPayModal();
        } else {
            // 无需支付，直接提交
            submitVerification(formData, formType, submitBtn);
        }
    }
    
    document.getElementById('personalForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        handleFormSubmit(this, 'personal');
    });
    
    document.getElementById('enterpriseForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        handleFormSubmit(this, 'enterprise');
    });
    
    // 支付方式选择
    document.querySelectorAll('.pay-method-option').forEach(option => {
        option.addEventListener('click', function() {
            document.querySelectorAll('.pay-method-option').forEach(o => o.classList.remove('selected'));
            this.classList.add('selected');
            this.querySelector('input').checked = true;
        });
    });
    
    // 支付表单提交
    document.getElementById('verifyPayForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const payMethod = document.querySelector('#verifyPayForm input[name="pay_method"]:checked');
        if (!payMethod) {
            toast('请选择支付方式', 'error');
            return;
        }
        
        if (!pendingFormData || !pendingFormType) {
            toast('认证信息丢失，请重新提交', 'error');
            hidePayModal();
            return;
        }
        
        const payBtn = this.querySelector('button[type="submit"]');
        payBtn.disabled = true;
        payBtn.innerHTML = '<span class="iconify" data-icon="tabler:loader-2" style="animation: spin 1s linear infinite; margin-right: 0.5rem;"></span>正在处理...';
        
        // 添加支付方式到表单数据
        pendingFormData.append('pay_method', payMethod.value);
        pendingFormData.append('need_pay', '1');
        
        const url = pendingFormType === 'personal' ? '/user/verification/personal' : '/user/verification/enterprise';
        
        fetch(url, {
            method: 'POST',
            body: pendingFormData,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.code === 0) {
                // 如果返回支付链接，跳转支付
                if (data.data && data.data.pay_url) {
                    toast('正在跳转支付...', 'success');
                    window.location.href = data.data.pay_url;
                } else {
                    // 无需支付或已支付，直接成功
                    toast(data.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                }
            } else {
                toast(data.message, 'error');
                payBtn.disabled = false;
                payBtn.innerHTML = '确认支付';
            }
        })
        .catch(err => {
            console.error('支付错误:', err);
            toast('网络错误，请稍后重试', 'error');
            payBtn.disabled = false;
            payBtn.innerHTML = '确认支付';
        });
    });
    
    function cancelVerification() {
        if (!confirm('确定要取消当前认证吗？取消后可以重新提交。')) {
            return;
        }
        
        fetch('/user/verification/cancel', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: '_token=<?= e($csrf_token) ?>'
        })
        .then(res => res.json())
        .then(data => {
            toast(data.message, data.code === 0 ? 'success' : 'error');
            if (data.code === 0) {
                setTimeout(() => location.reload(), 1500);
            }
        })
        .catch(err => {
            console.error('取消错误:', err);
            toast('网络错误', 'error');
        });
    }
</script>

<?php $content = ob_get_clean(); ?>
<?php include ML_ROOT . '/views/layouts/user.php'; ?>