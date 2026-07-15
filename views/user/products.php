<?php $pageTitle = '产品订购'; ob_start(); ?>

<!-- 引入QRCode库 -->
<script src="/assets/js/qrcode.min.js"></script>

<!-- 产品类型切换 -->
<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
        <button onclick="filterProducts('all')" id="filterAll" class="btn btn-primary btn-sm">全部</button>
        <button onclick="filterProducts('package')" id="filterPackage" class="btn btn-secondary btn-sm">套餐</button>
        <button onclick="filterProducts('account')" id="filterAccount" class="btn btn-secondary btn-sm">账号数量包</button>
        <button onclick="filterProducts('quota')" id="filterQuota" class="btn btn-secondary btn-sm">调用次数包</button>
    </div>
    <a href="/user/orders" style="font-size: 0.875rem; color: var(--color-primary);">我的订单 →</a>
</div>

<!-- 产品列表 -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;" id="productList">
    <?php 
    $totalPlatformCount = count($platforms ?? []);
    foreach ($products as $product): 
        $features = $product['features'] ? json_decode($product['features'], true) : [];
        $billingLabels = ['monthly' => '/月', 'quarterly' => '/季', 'yearly' => '/年', 'once' => ''];
        // 解析平台数据
        $platformData = [];
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
    ?>
    <div class="card product-card <?= $product['recommend'] ? 'recommended' : '' ?>" data-type="<?= e($product['type']) ?>" style="position: relative; overflow: hidden;">
        <!-- 推荐标签 - 放在图标左边 -->
        <?php if ($product['recommend']): ?>
        <div class="product-recommend-tag">推荐</div>
        <?php endif; ?>
        
        <!-- 产品类型图标 - 右上角 -->
        <div class="product-type-icon <?php 
            if ($product['type'] === 'package') echo 'type-package';
            elseif ($product['type'] === 'account') echo 'type-account';
            else echo 'type-quota';
        ?>">
            <span class="iconify" data-icon="<?php 
                if ($product['type'] === 'package') echo 'tabler:crown';
                elseif ($product['type'] === 'account') echo 'tabler:user-plus';
                else echo 'tabler:bolt';
            ?>"></span>
        </div>
        
        <div class="card-body product-card-body">
            <div style="margin-bottom: 1rem;">
                <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem;"><?= e($product['name']) ?></h3>
                <span class="badge <?php 
                    if ($product['type'] === 'package') echo 'badge-primary';
                    elseif ($product['type'] === 'account') echo 'badge-warning';
                    else echo 'badge-success';
                ?>">
                    <?php 
                    if ($product['type'] === 'package') echo '套餐';
                    elseif ($product['type'] === 'account') echo '账号数量包';
                    else echo '次数包';
                    ?>
                </span>
            </div>

            <div style="margin-bottom: 1rem;">
                <span style="font-size: 2rem; font-weight: 700; color: var(--text-main);">¥<?= number_format($product['price'], 2) ?></span>
                <span style="color: var(--text-muted);"><?= $billingLabels[$product['cycle']] ?? '' ?></span>
                <?php if ($product['original_price']): ?>
                <span style="color: var(--text-light); text-decoration: line-through; margin-left: 0.5rem;">¥<?= number_format($product['original_price'], 2) ?></span>
                <?php endif; ?>
            </div>

            <?php if ($product['description']): ?>
            <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 1rem;"><?= e($product['description']) ?></p>
            <?php endif; ?>

            <!-- 支持平台显示 - 放在说明上方 -->
            <?php if ($product['type'] === 'package'): ?>
                <?php if ($isAllPlatforms || empty($platformData) || count($platformData) >= $totalPlatformCount): ?>
                <p style="margin-bottom: 0.5rem; display: flex; align-items: center; flex-wrap: wrap; gap: 0.25rem; font-size: 0.875rem;">
                    <span style="color: var(--text-muted);">可用平台:</span>
                    <span class="badge badge-success" style="font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                        <span class="iconify" data-icon="tabler:world" style="font-size: 0.875rem;"></span>
                        全部平台
                    </span>
                </p>
                <?php elseif (count($platformData) === 1): ?>
                <p style="margin-bottom: 0.5rem; display: flex; align-items: center; flex-wrap: wrap; gap: 0.25rem; font-size: 0.875rem;">
                    <span style="color: var(--text-muted);">可用平台:</span>
                    <span style="display: inline-flex; align-items: center; gap: 0.25rem;">
                        <img src="/assets/icon/<?= e($platformData[0]['name']) ?>.svg" alt="<?= e($platformData[0]['label']) ?>" style="width: 1rem; height: 1rem;">
                        <?= e($platformData[0]['label']) ?>
                    </span>
                </p>
                <?php else: ?>
                <p style="margin-bottom: 0.5rem; display: flex; align-items: center; flex-wrap: wrap; gap: 0.25rem; font-size: 0.875rem;">
                    <span style="color: var(--text-muted);">可用平台:</span>
                    <span class="platform-tooltip" style="position: relative; cursor: pointer; display: inline-flex; align-items: center; gap: 0.25rem;">
                        <img src="/assets/icon/<?= e($platformData[0]['name']) ?>.svg" alt="<?= e($platformData[0]['label']) ?>" style="width: 1rem; height: 1rem;">
                        <span><?= e($platformData[0]['label']) ?></span>
                        <span class="badge badge-secondary" style="font-size: 0.7rem; margin-left: 0.125rem;">+<?= count($platformData) - 1 ?></span>
                        <span class="platform-tooltip-content">
                            <?php foreach ($platformData as $pd): ?>
                                <span class="platform-tooltip-item">
                                    <img src="/assets/icon/<?= e($pd['name']) ?>.svg" alt="<?= e($pd['label']) ?>" style="width: 0.875rem; height: 0.875rem;">
                                    <?= e($pd['label']) ?>
                                </span>
                            <?php endforeach; ?>
                        </span>
                    </span>
                </p>
                <?php endif; ?>
            <?php else: ?>
            <!-- 账号包、次数包显示全部平台 -->
            <p style="margin-bottom: 0.5rem; display: flex; align-items: center; flex-wrap: wrap; gap: 0.25rem; font-size: 0.875rem;">
                <span style="color: var(--text-muted);">可用平台:</span>
                <span class="badge badge-success" style="font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                    <span class="iconify" data-icon="tabler:world" style="font-size: 0.875rem;"></span>
                    全部平台
                </span>
            </p>
            <?php endif; ?>

            <?php if (!empty($features)): ?>
            <ul style="list-style: none; padding: 0; margin-bottom: 1rem;">
                <?php foreach ($features as $feature): ?>
                <li style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 0.5rem;">
                    <span class="iconify" data-icon="tabler:check" style="color: var(--color-success);"></span>
                    <?= e($feature) ?>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php else: ?>
            <div style="margin-bottom: 1rem; font-size: 0.875rem; color: var(--text-secondary);">
                <?php if ($product['type'] === 'package' && $product['daily_limit']): ?>
                <p style="margin-bottom: 0.25rem;">每日调用限制: <?= number_format($product['daily_limit']) ?> 次</p>
                <?php endif; ?>
                <?php if ($product['type'] === 'quota' && $product['total_quota']): ?>
                <p style="margin-bottom: 0.25rem;">总调用次数: <?= number_format($product['total_quota']) ?> 次</p>
                <?php endif; ?>
                <?php if ($product['type'] === 'account' && $product['account_limit']): ?>
                <p style="margin-bottom: 0.25rem;">授权账号数量: <?= number_format($product['account_limit']) ?> 个</p>
                <?php endif; ?>
                <?php if ($product['duration']): ?>
                <p>有效期: <?= $product['duration'] ?> 天</p>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <button onclick="showPayModal(<?= $product['id'] ?>, '<?= e($product['name']) ?>', <?= $product['price'] ?>)" 
                class="btn <?= $product['recommend'] ? 'btn-primary' : 'btn-secondary' ?> btn-block">
                立即购买
            </button>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if (empty($products)): ?>
    <div class="card" style="grid-column: 1 / -1;">
        <div class="card-body" style="text-align: center; padding: 3rem;">
            <span class="iconify" data-icon="tabler:shopping-bag" style="font-size: 3rem; color: var(--text-light); margin-bottom: 1rem;"></span>
            <p style="color: var(--text-muted);">暂无可购买的产品</p>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- 支付弹窗 -->
<div id="payModal" class="modal">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h3 class="modal-title">确认订单</h3>
            <button onclick="hidePayModal()" class="close-modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="orderForm">
                <input type="hidden" name="_token" value="<?= e($csrf_token) ?>">
                <input type="hidden" name="product_id" id="orderProductId">

                <div style="background: var(--bg-surface-hover); border-radius: var(--radius-md); padding: 1rem; margin-bottom: 1rem;">
                    <p style="color: var(--text-muted); font-size: 0.875rem;">购买产品</p>
                    <p style="font-size: 1.125rem; font-weight: 600; color: var(--text-main);" id="orderProductName"></p>
                    <p style="font-size: 1.5rem; font-weight: 700; color: var(--color-primary); margin-top: 0.5rem;">¥<span id="orderAmount"></span></p>
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

                        <?php if (empty($payMethods)): ?>
                        <p style="text-align: center; color: var(--text-muted); padding: 1rem;">暂无可用的支付方式</p>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($payMethods)): ?>
                <button type="submit" class="btn btn-primary btn-block" style="margin-top: 1rem;">确认支付</button>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>

<style>
.product-card.recommended {
    border: 2px solid var(--color-primary);
}

.product-recommend-tag {
    position: absolute;
    top: 0.75rem;
    right: 4.5rem;
    background: linear-gradient(135deg, #f59e0b, #f97316);
    color: #fff;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.25rem 0.625rem;
    border-radius: var(--radius-sm);
    box-shadow: 0 2px 4px rgba(245, 158, 11, 0.3);
}

.product-type-icon {
    position: absolute;
    top: 0;
    right: 0;
    width: 3.5rem;
    height: 3.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    border-bottom-left-radius: var(--radius-lg);
    opacity: 0.9;
}

.product-type-icon.type-package {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
}

.product-type-icon.type-account {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: #fff;
}

.product-type-icon.type-quota {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: #fff;
}

.product-card-body {
    padding-left: 0;
}

.product-card .card-body {
    padding: 1.25rem;
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

/* 平台提示框样式 */
.platform-tooltip {
    position: relative;
}

.platform-tooltip-content {
    display: none;
    position: absolute;
    left: 0;
    top: 100%;
    background: var(--bg-surface);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    padding: 0.5rem;
    z-index: 10;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    margin-top: 0.25rem;
    min-width: 120px;
}

.platform-tooltip:hover .platform-tooltip-content {
    display: block;
}

.platform-tooltip-item {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    margin: 0.125rem 0.25rem;
    font-size: 0.75rem;
    white-space: nowrap;
}
</style>

<script>
function filterProducts(type) {
    document.querySelectorAll('.product-card').forEach(card => {
        card.style.display = (type === 'all' || card.dataset.type === type) ? '' : 'none';
    });
    
    document.getElementById('filterAll').className = type === 'all' ? 'btn btn-primary btn-sm' : 'btn btn-secondary btn-sm';
    document.getElementById('filterPackage').className = type === 'package' ? 'btn btn-primary btn-sm' : 'btn btn-secondary btn-sm';
    document.getElementById('filterAccount').className = type === 'account' ? 'btn btn-primary btn-sm' : 'btn btn-secondary btn-sm';
    document.getElementById('filterQuota').className = type === 'quota' ? 'btn btn-primary btn-sm' : 'btn btn-secondary btn-sm';
}

function showPayModal(productId, productName, price) {
    document.getElementById('orderProductId').value = productId;
    document.getElementById('orderProductName').textContent = productName;
    document.getElementById('orderAmount').textContent = price.toFixed(2);
    document.getElementById('payModal').classList.add('show');
}

function hidePayModal() {
    document.getElementById('payModal').classList.remove('show');
}
document.querySelectorAll('.pay-method-option').forEach(option => {
    option.addEventListener('click', function() {
        document.querySelectorAll('.pay-method-option').forEach(o => o.classList.remove('selected'));
        this.classList.add('selected');
        this.querySelector('input').checked = true;
    });
});

document.getElementById('orderForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const payMethod = document.querySelector('input[name="pay_method"]:checked');
    if (!payMethod) {
        toast('请选择支付方式', 'error');
        return;
    }
    
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="iconify spin-icon" data-icon="tabler:loader-2"></span><span>正在处理...</span>';
    submitBtn.style.display = 'inline-flex';
    submitBtn.style.alignItems = 'center';
    submitBtn.style.justifyContent = 'center';
    submitBtn.style.gap = '0.5rem';
    
    const formData = new FormData(this);
    fetch('/user/products/buy', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.code === 0) {
            const payFormData = new FormData();
            payFormData.append('_token', '<?= e($csrf_token) ?>');
            payFormData.append('order_no', data.data.order_no);
            payFormData.append('pay_type', payMethod.value);
            
            return fetch('/user/order/pay', {
                method: 'POST',
                body: payFormData,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(res => res.json());
        } else {
            throw new Error(data.message || '订单创建失败');
        }
    })
    .then(payData => {
        if (payData.code === 0 && payData.data) {
            // 判断是跳转还是显示二维码
            if (payData.data.type === 'qrcode') {
                // 显示二维码弹窗
                showQrcodeModal(payData.data);
                submitBtn.disabled = false;
                submitBtn.innerHTML = '确认支付';
                submitBtn.style.display = '';
            } else if (payData.data.url) {
                // 跳转支付
                toast('正在跳转支付...', 'success');
                window.location.href = payData.data.url;
            } else {
                throw new Error('获取支付信息失败');
            }
        } else {
            throw new Error(payData.message || '获取支付链接失败');
        }
    })
    .catch(err => {
        toast(err.message || '网络错误', 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '确认支付';
        submitBtn.style.display = '';
    });
});

// 显示二维码弹窗
function showQrcodeModal(data) {
    hidePayModal();
    
    const payTypeNames = {
        'wechat': '微信',
        'alipay': '支付宝',
        'qqpay': 'QQ钱包'
    };
    const payTypeColors = {
        'wechat': '#07c160',
        'alipay': '#1677ff',
        'qqpay': '#12b7f5'
    };
    
    const modal = document.createElement('div');
    modal.id = 'qrcodeModal';
    modal.className = 'modal show';
    modal.innerHTML = `
        <div class="modal-content" style="max-width: 360px; text-align: center;">
            <div class="modal-header">
                <h3 class="modal-title">扫码支付</h3>
                <button onclick="hideQrcodeModal()" class="close-modal">&times;</button>
            </div>
            <div class="modal-body" style="padding: 1.5rem;">
                <div style="display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; margin-bottom: 1.5rem; padding: 0.5rem 1rem; border: 1px solid ${payTypeColors[data.pay_type]}; border-radius: 2rem; background: ${payTypeColors[data.pay_type]}10;">
                    <img src="/assets/icon/${data.pay_type === 'wechat' ? 'wx' : (data.pay_type === 'qqpay' ? 'qq' : data.pay_type)}.svg" style="width: 1.25rem; height: 1.25rem;">
                    <span style="font-weight: 600; color: ${payTypeColors[data.pay_type]}">${payTypeNames[data.pay_type]}支付</span>
                </div>
                <div id="qrcodeContainer" style="display: flex; justify-content: center; margin-bottom: 1.25rem;"></div>
                <p style="font-size: 1.5rem; font-weight: 700; color: var(--color-primary); margin-bottom: 0.5rem;">¥${parseFloat(data.amount).toFixed(2)}</p>
                <p style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 1rem;">${data.product_name}</p>
                <p style="font-size: 0.75rem; color: var(--text-light);">请使用${payTypeNames[data.pay_type]}扫描二维码完成支付</p>
                <div id="payStatusText" style="display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; margin-top: 1rem; font-size: 0.875rem; color: var(--text-secondary);">
                    <span class="iconify spin-icon" data-icon="tabler:loader-2"></span>
                    <span>等待支付中...</span>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
    
    // 生成二维码
    new QRCode(document.getElementById('qrcodeContainer'), {
        text: data.code_url,
        width: 200,
        height: 200,
        colorDark: '#000000',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.M
    });
    
    // 开始轮询支付状态
    startPayStatusPolling(data.order_no);
}

// 隐藏二维码弹窗
function hideQrcodeModal() {
    const modal = document.getElementById('qrcodeModal');
    if (modal) {
        modal.remove();
    }
    stopPayStatusPolling();
}

// 支付状态轮询
if (typeof payStatusTimer === 'undefined') {
    var payStatusTimer = null;
}
function startPayStatusPolling(orderNo) {
    stopPayStatusPolling();
    
    payStatusTimer = setInterval(() => {
        fetch('/pay/check-status?order_no=' + orderNo)
            .then(res => res.json())
            .then(data => {
                if (data.code === 0 && data.data && data.data.paid) {
                    stopPayStatusPolling();
                    const statusText = document.getElementById('payStatusText');
                    if (statusText) {
                        statusText.innerHTML = '<span class="iconify" data-icon="tabler:circle-check" style="color: var(--color-success); margin-right: 0.25rem;"></span><span>支付成功！</span>';
                    }
                    toast('支付成功！', 'success');
                    setTimeout(() => {
                        window.location.href = '/user/orders?msg=支付成功';
                    }, 1500);
                }
            })
            .catch(() => {});
    }, 2000);
}

function stopPayStatusPolling() {
    if (payStatusTimer) {
        clearInterval(payStatusTimer);
        payStatusTimer = null;
    }
}
</script>

<style>
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.spin-icon {
    animation: spin 1s linear infinite;
    display: inline-block;
}
</style>

<?php $content = ob_get_clean(); ?>
<?php include ML_ROOT . '/views/layouts/user.php'; ?>
