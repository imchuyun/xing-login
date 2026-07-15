<?php $pageTitle = '产品大全';
ob_start(); ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <div>
        <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.25rem;">产品大全</h2>
        <p style="color: var(--text-muted); font-size: 0.875rem;">管理套餐、调用次数包和账号授权包</p>
    </div>
    <button onclick="showProductModal()" class="btn btn-primary" style="display: flex; align-items: center; gap: 0.5rem;">
        <span class="iconify" data-icon="tabler:plus"></span>
        添加产品
    </button>
</div>

<!-- 产品类型切换 -->
<div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem;">
    <button onclick="filterProducts('all')" id="filterAll" class="btn btn-primary">全部</button>
    <button onclick="filterProducts('package')" id="filterPackage" class="btn btn-outline">套餐</button>
    <button onclick="filterProducts('quota')" id="filterQuota" class="btn btn-outline">调用次数包</button>
    <button onclick="filterProducts('account')" id="filterAccount" class="btn btn-outline">账号授权包</button>
</div>

<!-- 产品列表 -->
<div id="productList" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; align-items: stretch; padding-bottom: 1rem;">
    <?php 
    $totalPlatformCount = count($platforms);
    foreach ($products as $product): ?>
        <?php
        $platformData = []; // 存储平台的 name 和显示名称
        if ($product['platforms']) {
            $pList = json_decode($product['platforms'], true) ?: [];
            foreach ($pList as $pName) {
                $platformData[] = [
                    'name' => $pName,
                    'label' => get_platform_name($pName)
                ];
            }
        }
        $isAllPlatforms = count($platformData) === $totalPlatformCount && $totalPlatformCount > 0;
        $features = $product['features'] ? json_decode($product['features'], true) : [];
        $billingLabels = ['monthly' => '月付', 'quarterly' => '季付', 'yearly' => '年付', 'once' => '一次性'];
        ?>
        <div class="card product-card" data-type="<?= e($product['type']) ?>" style="display: flex; flex-direction: column; height: 100%;">
            <?php if ($product['recommend']): ?>
                <div style="background: linear-gradient(to right, var(--color-primary), #4f46e5); color: white; text-align: center; padding: 0.25rem; font-size: 0.75rem; font-weight: 600;">推荐</div>
            <?php endif; ?>

            <div class="card-body" style="flex: 1; display: flex; flex-direction: column;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                    <div>
                        <h3 style="font-size: 1.125rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem;"><?= e($product['name']) ?></h3>
                        <div style="display: flex; gap: 0.5rem;">
                            <span class="badge <?= $product['type'] === 'package' ? 'badge-info' : ($product['type'] === 'quota' ? 'badge-success' : 'badge-primary') ?>">
                                <?= $product['type'] === 'package' ? '套餐' : ($product['type'] === 'quota' ? '次数包' : '账号包') ?>
                            </span>
                            <span class="badge badge-secondary">
                                <?= $billingLabels[$product['cycle']] ?? '' ?>
                            </span>
                        </div>
                    </div>
                    <span class="badge <?= $product['status'] ? 'badge-success' : 'badge-error' ?>">
                        <?= $product['status'] ? '上架' : '下架' ?>
                    </span>
                </div>

                <div style="margin-bottom: 1rem;">
                    <span style="font-size: 1.5rem; font-weight: 700; color: var(--text-main);">¥<?= number_format($product['price'], 2) ?></span>
                    <?php if ($product['original_price']): ?>
                        <span style="color: var(--text-muted); text-decoration: line-through; margin-left: 0.5rem; font-size: 0.875rem;">¥<?= number_format($product['original_price'], 2) ?></span>
                    <?php endif; ?>
                </div>

                <div style="font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 1rem; flex: 1; min-height: 80px;">
                    <?php if ($product['type'] === 'package'): ?>
                        <?php if (!empty($platformData)): ?>
                            <p style="margin-bottom: 0.25rem; display: flex; align-items: center; flex-wrap: wrap; gap: 0.25rem;">
                                <span style="color: var(--text-muted);">可用平台:</span>
                                <?php if ($isAllPlatforms): ?>
                                    <span class="badge badge-success" style="font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                        <span class="iconify" data-icon="tabler:world" style="font-size: 0.875rem;"></span>
                                        全部平台
                                    </span>
                                <?php elseif (count($platformData) === 1): ?>
                                    <span style="display: inline-flex; align-items: center; gap: 0.25rem;">
                                        <img src="/assets/icon/<?= e($platformData[0]['name']) ?>.svg" alt="<?= e($platformData[0]['label']) ?>" style="width: 1rem; height: 1rem;">
                                        <?= e($platformData[0]['label']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="platform-tooltip" style="position: relative; cursor: pointer; display: inline-flex; align-items: center; gap: 0.25rem;">
                                        <img src="/assets/icon/<?= e($platformData[0]['name']) ?>.svg" alt="<?= e($platformData[0]['label']) ?>" style="width: 1rem; height: 1rem;">
                                        <span><?= e($platformData[0]['label']) ?></span>
                                        <span class="badge badge-secondary" style="font-size: 0.7rem; margin-left: 0.125rem;">+<?= count($platformData) - 1 ?></span>
                                        <span class="platform-tooltip-content" style="display: none; position: absolute; left: 0; top: 100%; background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 0.5rem; z-index: 10; box-shadow: 0 4px 12px rgba(0,0,0,0.15); margin-top: 0.25rem; min-width: 120px;">
                                            <?php foreach ($platformData as $pd): ?>
                                                <span style="display: inline-flex; align-items: center; gap: 0.25rem; margin: 0.125rem 0.25rem; font-size: 0.75rem; white-space: nowrap;">
                                                    <img src="/assets/icon/<?= e($pd['name']) ?>.svg" alt="<?= e($pd['label']) ?>" style="width: 0.875rem; height: 0.875rem;">
                                                    <?= e($pd['label']) ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </span>
                                    </span>
                                <?php endif; ?>
                            </p>
                        <?php endif; ?>
                        <?php if ($product['daily_limit']): ?>
                            <p style="margin-bottom: 0.25rem;"><span style="color: var(--text-muted);">每日限制:</span> <?= number_format($product['daily_limit']) ?> 次</p>
                        <?php endif; ?>
                        <p><span style="color: var(--text-muted);">有效期:</span> <?= $product['duration'] ?> 天</p>
                    <?php elseif ($product['type'] === 'quota'): ?>
                        <?php if ($product['total_quota']): ?>
                            <p style="margin-bottom: 0.25rem;"><span style="color: var(--text-muted);">调用次数:</span> <?= number_format($product['total_quota']) ?> 次</p>
                        <?php endif; ?>
                        <p><span style="color: var(--text-muted);">有效期:</span> <?= $product['duration'] ?> 天</p>
                    <?php else: ?>
                        <?php if ($product['account_limit']): ?>
                            <p style="margin-bottom: 0.25rem;"><span style="color: var(--text-muted);">授权账号:</span> <?= number_format($product['account_limit']) ?> 个</p>
                        <?php endif; ?>
                        <p><span style="color: var(--text-muted);">有效期:</span> <?= $product['duration'] ?> 天</p>
                    <?php endif; ?>

                    <?php if (!empty($features)): ?>
                        <ul style="margin-top: 0.75rem; list-style: none; padding: 0;">
                            <?php foreach (array_slice($features, 0, 2) as $feature): ?>
                                <li style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                                    <span class="iconify" data-icon="tabler:check" style="color: var(--color-primary); flex-shrink: 0;"></span>
                                    <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?= e($feature) ?></span>
                                </li>
                            <?php endforeach; ?>
                            <?php if (count($features) > 2): ?>
                                <li style="color: var(--text-muted); font-size: 0.75rem;">+<?= count($features) - 2 ?> 更多特性...</li>
                            <?php endif; ?>
                        </ul>
                    <?php endif; ?>
                </div>

                <div style="display: flex; gap: 0.5rem; padding-top: 1rem; border-top: 1px solid var(--border-color); margin-top: auto;">
                    <button onclick='editProduct(<?= json_encode($product) ?>)' class="btn btn-primary btn-block btn-sm">
                        编辑
                    </button>
                    <button onclick="deleteProduct(<?= $product['id'] ?>)" class="btn btn-outline btn-sm" title="删除" style="color: var(--color-error); border-color: var(--color-error);">
                        <span class="iconify" data-icon="tabler:trash"></span>
                    </button>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if (empty($products)): ?>
        <div style="grid-column: 1 / -1; text-align: center; padding: 4rem; background-color: var(--bg-surface); border-radius: var(--radius-lg); border: 1px solid var(--border-color);">
            <div style="width: 4rem; height: 4rem; background-color: var(--bg-surface-hover); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                <span class="iconify" data-icon="tabler:inbox" style="font-size: 2rem; color: var(--text-muted);"></span>
            </div>
            <p style="color: var(--text-muted);">暂无产品，点击上方按钮添加</p>
        </div>
    <?php endif; ?>
</div>

<!-- 产品编辑弹窗 -->
<div id="productModal" class="modal">
    <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">添加产品</h3>
                <button type="button" class="close-modal" onclick="hideProductModal()">&times;</button>
            </div>
            <form id="productForm">
                <input type="hidden" name="_token" value="<?= e($csrf_token) ?>">
                <input type="hidden" name="id" id="productId">

                <div class="modal-body" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                    <!-- 左列 -->
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <!-- 基本信息 -->
                        <div>
                            <h4 style="font-size: 0.875rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                                <span style="width: 0.375rem; height: 0.375rem; background-color: var(--color-primary); border-radius: 50%;"></span>
                                基本信息
                            </h4>
                            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                                <div class="form-group">
                                    <label class="form-label">产品名称 <span style="color: var(--color-error);">*</span></label>
                                    <input type="text" name="name" id="productName" required class="form-control" placeholder="输入产品名称">
                                </div>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                    <div class="form-group">
                                        <label class="form-label">产品类型</label>
                                        <select name="type" id="productType" onchange="toggleProductFields()" class="form-control">
                                            <option value="package">套餐</option>
                                            <option value="quota">次数包</option>
                                            <option value="account">账号授权包</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">计费周期</label>
                                        <select name="billing_cycle" id="billingCycle" class="form-control">
                                            <option value="monthly">月付</option>
                                            <option value="quarterly">季付</option>
                                            <option value="yearly">年付</option>
                                            <option value="once">一次性</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 价格 -->
                        <div>
                            <h4 style="font-size: 0.875rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                                <span style="width: 0.375rem; height: 0.375rem; background-color: var(--color-success); border-radius: 50%;"></span>
                                价格设置
                            </h4>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                                <div class="form-group">
                                    <label class="form-label">售价 <span style="color: var(--color-error);">*</span></label>
                                    <div style="position: relative;">
                                        <span style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);">¥</span>
                                        <input type="number" step="0.01" name="price" id="productPrice" required class="form-control" style="padding-left: 1.75rem;" placeholder="0.00">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">原价</label>
                                    <div style="position: relative;">
                                        <span style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);">¥</span>
                                        <input type="number" step="0.01" name="original_price" id="originalPrice" class="form-control" style="padding-left: 1.75rem;" placeholder="划线价">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 描述 -->
                        <div>
                            <h4 style="font-size: 0.875rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                                <span style="width: 0.375rem; height: 0.375rem; background-color: #8b5cf6; border-radius: 50%;"></span>
                                功能描述
                            </h4>
                            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                <div class="form-group">
                                    <label class="form-label">功能特性 <span style="color: var(--text-muted); font-size: 0.75rem;">(每行一个)</span></label>
                                    <textarea name="features" id="features" rows="3" class="form-control" placeholder="QQ登录&#10;微信登录"></textarea>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">产品描述</label>
                                    <textarea name="description" id="description" rows="2" class="form-control" placeholder="简要描述..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 右列 -->
                    <div style="display: flex; flex-direction: column; gap: 1rem; background-color: var(--bg-surface-hover); padding: 1rem; border-radius: var(--radius-lg);">
                        <!-- 平台配置 (套餐) -->
                        <div id="packageFields">
                            <h4 style="font-size: 0.875rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                                <span style="width: 0.375rem; height: 0.375rem; background-color: #06b6d4; border-radius: 50%;"></span>
                                平台配置
                            </h4>
                            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                <div class="form-group">
                                    <label class="form-label">可用登录平台</label>
                                    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                                        <?php foreach ($platforms as $platform): ?>
                                            <label class="platform-label" onclick="togglePlatform(this)" style="display: inline-flex; align-items: center; padding: 0.375rem 0.75rem; background-color: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md); cursor: pointer; font-size: 0.875rem; transition: all 0.2s;">
                                                <input type="checkbox" name="platforms[]" value="<?= e($platform['name']) ?>" class="platform-checkbox" style="display: none;">
                                                <span><?= e($platform['platform']) ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">每日调用限制</label>
                                    <input type="number" name="daily_limit" id="dailyLimit" class="form-control" placeholder="留空不限制">
                                </div>
                            </div>
                        </div>

                        <!-- 次数配置 (次数包) -->
                        <div id="quotaFields" style="display: none;">
                            <h4 style="font-size: 0.875rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                                <span style="width: 0.375rem; height: 0.375rem; background-color: #06b6d4; border-radius: 50%;"></span>
                                次数配置
                            </h4>
                            <div class="form-group">
                                <label class="form-label">总调用次数</label>
                                <input type="number" name="total_quota" id="totalQuota" class="form-control" placeholder="例如：1000">
                            </div>
                        </div>

                        <!-- 账号配置 (账号授权包) -->
                        <div id="accountFields" style="display: none;">
                            <h4 style="font-size: 0.875rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                                <span style="width: 0.375rem; height: 0.375rem; background-color: #8b5cf6; border-radius: 50%;"></span>
                                账号配置
                            </h4>
                            <div class="form-group">
                                <label class="form-label">授权账号数量</label>
                                <input type="number" name="account_limit" id="accountLimit" class="form-control" placeholder="例如：100">
                                <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">可绑定的第三方登录账号数量</p>
                            </div>
                        </div>

                        <!-- 状态设置 -->
                        <div>
                            <h4 style="font-size: 0.875rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                                <span style="width: 0.375rem; height: 0.375rem; background-color: #f59e0b; border-radius: 50%;"></span>
                                状态设置
                            </h4>
                            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.75rem;">
                                    <div class="form-group">
                                        <label class="form-label">有效期</label>
                                        <div style="position: relative;">
                                            <input type="number" name="duration" id="duration" value="30" class="form-control">
                                            <span style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.75rem;">天</span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">排序</label>
                                        <input type="number" name="sort" id="productSort" value="0" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">状态</label>
                                        <select name="status" id="productStatus" class="form-control">
                                            <option value="1">上架</option>
                                            <option value="0">下架</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- 推荐 -->
                                <label style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; background-color: rgba(245, 158, 11, 0.1); border-radius: var(--radius-md); cursor: pointer;">
                                    <input type="checkbox" name="is_recommended" id="isRecommended" value="1" style="width: 1.25rem; height: 1.25rem;">
                                    <div style="display: flex; align-items: center; gap: 0.375rem;">
                                        <span class="iconify" data-icon="tabler:star-filled" style="color: #f59e0b;"></span>
                                        <span style="font-size: 0.875rem; font-weight: 500; color: #b45309;">设为推荐产品</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="hideProductModal()" class="btn btn-outline">取消</button>
                    <button type="submit" class="btn btn-primary">保存</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // 平台提示框悬停显示
    document.addEventListener('mouseover', function(e) {
        const tooltip = e.target.closest('.platform-tooltip');
        if (tooltip) {
            const content = tooltip.querySelector('.platform-tooltip-content');
            if (content) content.style.display = 'block';
        }
    });
    document.addEventListener('mouseout', function(e) {
        const tooltip = e.target.closest('.platform-tooltip');
        if (tooltip) {
            const content = tooltip.querySelector('.platform-tooltip-content');
            if (content) content.style.display = 'none';
        }
    });

    function togglePlatform(label) {
        const checkbox = label.querySelector('input[type="checkbox"]');
        checkbox.checked = !checkbox.checked;
        updatePlatformStyle(label, checkbox.checked);
    }

    function updatePlatformStyle(label, checked) {
        if (checked) {
            label.style.backgroundColor = 'var(--color-primary)';
            label.style.borderColor = 'var(--color-primary)';
            label.style.color = '#fff';
        } else {
            label.style.backgroundColor = 'var(--bg-surface)';
            label.style.borderColor = 'var(--border-color)';
            label.style.color = 'var(--text-main)';
        }
    }

    function filterProducts(type) {
        document.querySelectorAll('.product-card').forEach(card => {
            card.style.display = (type === 'all' || card.dataset.type === type) ? 'flex' : 'none';
        });

        ['filterAll', 'filterPackage', 'filterQuota', 'filterAccount'].forEach(id => {
            const btn = document.getElementById(id);
            if ((type === 'all' && id === 'filterAll') ||
                (type === 'package' && id === 'filterPackage') ||
                (type === 'quota' && id === 'filterQuota') ||
                (type === 'account' && id === 'filterAccount')) {
                btn.className = 'btn btn-primary';
            } else {
                btn.className = 'btn btn-outline';
            }
        });
    }

    function toggleProductFields() {
        const type = document.getElementById('productType').value;
        document.getElementById('packageFields').style.display = type === 'package' ? 'block' : 'none';
        document.getElementById('quotaFields').style.display = type === 'quota' ? 'block' : 'none';
        document.getElementById('accountFields').style.display = type === 'account' ? 'block' : 'none';
    }

    function showProductModal() {
        document.getElementById('modalTitle').textContent = '添加产品';
        document.getElementById('productForm').reset();
        document.getElementById('productId').value = '';
        document.querySelectorAll('.platform-checkbox').forEach(cb => {
            cb.checked = false;
            updatePlatformStyle(cb.closest('.platform-label'), false);
        });
        toggleProductFields();
        document.getElementById('productModal').classList.add('show');
    }

    function hideProductModal() {
        document.getElementById('productModal').classList.remove('show');
    }
    document.getElementById('productModal').addEventListener('click', function(e) {
        if (e.target === this) hideProductModal();
    });

    function editProduct(product) {
        document.getElementById('modalTitle').textContent = '编辑产品';
        document.getElementById('productId').value = product.id;
        document.getElementById('productName').value = product.name;
        document.getElementById('productType').value = product.type;
        document.getElementById('billingCycle').value = product.cycle;
        document.getElementById('productPrice').value = product.price;
        document.getElementById('originalPrice').value = product.original_price || '';
        document.getElementById('dailyLimit').value = product.daily_limit || '';
        document.getElementById('totalQuota').value = product.total_quota || '';
        document.getElementById('accountLimit').value = product.account_limit || '';
        document.getElementById('duration').value = product.duration || 30;
        document.getElementById('productSort').value = product.sort || 0;
        document.getElementById('productStatus').value = product.status;
        document.getElementById('isRecommended').checked = product.recommend == 1;
        document.getElementById('description').value = product.description || '';
        const platforms = product.platforms ? JSON.parse(product.platforms) : [];
        document.querySelectorAll('.platform-checkbox').forEach(cb => {
            cb.checked = platforms.includes(cb.value);
            updatePlatformStyle(cb.closest('.platform-label'), cb.checked);
        });
        const features = product.features ? JSON.parse(product.features) : [];
        document.getElementById('features').value = features.join('\n');

        toggleProductFields();
        document.getElementById('productModal').classList.add('show');
    }

    function deleteProduct(id) {
        if (!confirm('确定要删除此产品吗？')) return;

        ajax('<?= admin_url('products/delete') ?>', {
            _token: '<?= e($csrf_token) ?>',
            id: id
        }, function(data) {
            if (data.code === 0) {
                toast('删除成功', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                toast(data.message, 'error');
            }
        });
    }

    document.getElementById('productForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        // 手动处理 platforms[] 数组，因为 Object.fromEntries 无法正确处理同名多值
        const data = {};
        const platforms = [];
        for (const [key, value] of formData.entries()) {
            if (key === 'platforms[]') {
                platforms.push(value);
            } else {
                data[key] = value;
            }
        }
        // 将平台数组添加到数据中
        if (platforms.length > 0) {
            data['platforms'] = platforms;
        }
        ajax('<?= admin_url('products/save') ?>', data, function(data) {
            if (data.code === 0) {
                toast(data.message, 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                toast(data.message, 'error');
            }
        });
    });
</script>

<?php $content = ob_get_clean(); ?>
<?php include ML_ROOT . '/views/layouts/admin.php'; ?>