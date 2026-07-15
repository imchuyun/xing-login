<?php $pageTitle = '订单支付'; ob_start(); ?>

<div style="max-width: 500px; margin: 0 auto; min-height: calc(100vh - 200px); display: flex; flex-direction: column; justify-content: center;">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">订单支付</h3>
        </div>
        <div class="card-body">
            <!-- 订单信息 -->
            <div style="background: var(--bg-surface-hover); border-radius: var(--radius-md); padding: 1.25rem; margin-bottom: 1.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                    <span style="color: var(--text-muted); font-size: 0.875rem;">订单编号</span>
                    <span style="font-family: monospace; font-size: 0.875rem;"><?= e($order['no']) ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                    <span style="color: var(--text-muted); font-size: 0.875rem;">商品名称</span>
                    <span style="font-weight: 500;"><?= e($order['product_name'] ?? $order['product_name'] ?? '订单支付') ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 0.75rem; border-top: 1px dashed var(--border-color);">
                    <span style="color: var(--text-muted); font-size: 0.875rem;">支付金额</span>
                    <span style="font-size: 1.5rem; font-weight: 700; color: var(--color-primary);">¥<?= number_format($order['amount'], 2) ?></span>
                </div>
            </div>

            <?php if (!empty($payMethods)): ?>
            <!-- 支付方式选择 -->
            <form id="payForm">
                <input type="hidden" name="_token" value="<?= e($csrf_token) ?>">
                <input type="hidden" name="order_no" value="<?= e($order['no']) ?>">
                
                <div class="form-group">
                    <label class="form-label">选择支付方式</label>
                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <?php foreach ($payMethods as $key => $method): 
                            $payIcon = $key === 'wechat' ? 'wx' : ($key === 'qqpay' ? 'qq' : $key);
                        ?>
                        <label class="pay-method-option" data-method="<?= $key ?>">
                            <input type="radio" name="pay_type" value="<?= $key ?>" <?= $key === 'alipay' ? 'checked' : '' ?>>
                            <div class="pay-icon" style="background: <?= $key === 'alipay' ? 'linear-gradient(135deg, #1677ff, #0958d9)' : ($key === 'wechat' ? 'linear-gradient(135deg, #07c160, #06ae56)' : 'linear-gradient(135deg, #12b7f5, #0d9ed8)') ?>;">
                                <img src="/assets/icon/<?= e($payIcon) ?>.svg" alt="<?= e($method['name']) ?>" style="width: 1.5rem; height: 1.5rem;">
                            </div>
                            <span style="font-weight: 500;"><?= e($method['name']) ?></span>
                            <span class="iconify check-icon" data-icon="tabler:check"></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block" style="margin-top: 1.5rem; padding: 0.875rem;">
                    <span class="iconify" data-icon="tabler:shield-check" style="margin-right: 0.5rem;"></span>
                    立即支付
                </button>
            </form>
            <?php else: ?>
            <div class="alert alert-warning">
                <span class="iconify" data-icon="tabler:alert-triangle" style="margin-right: 0.5rem;"></span>
                暂无可用的支付方式，请联系管理员
            </div>
            <?php endif; ?>

            <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                <p style="font-size: 0.75rem; color: var(--text-muted); margin: 0;">
                    <span class="iconify" data-icon="tabler:info-circle" style="margin-right: 0.25rem;"></span>
                    订单将在30分钟后自动关闭，请尽快完成支付
                </p>
            </div>
        </div>
    </div>

    <div style="text-align: center; margin-top: 1rem;">
        <a href="/user/orders" style="color: var(--text-muted); font-size: 0.875rem;">
            <span class="iconify" data-icon="tabler:arrow-left" style="margin-right: 0.25rem;"></span>
            返回订单列表
        </a>
    </div>
</div>

<style>
.pay-method-option {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.875rem 1rem;
    border: 2px solid var(--border-color);
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

.pay-method-option .pay-icon {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
}

.pay-method-option .check-icon {
    margin-left: auto;
    color: var(--color-primary);
    font-size: 1.25rem;
    display: none;
}

.pay-method-option.selected {
    border-color: var(--color-primary);
    background: rgba(var(--primary-rgb), 0.05);
}

.pay-method-option.selected .check-icon {
    display: block;
}
</style>

<script>
document.querySelectorAll('.pay-method-option').forEach(function(option) {
    var input = option.querySelector('input');
    if (input.checked) {
        option.classList.add('selected');
    }
    
    option.addEventListener('click', function() {
        document.querySelectorAll('.pay-method-option').forEach(function(o) {
            o.classList.remove('selected');
        });
        this.classList.add('selected');
        this.querySelector('input').checked = true;
    });
});

document.getElementById('payForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    var formData = new FormData(this);
    var submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="iconify" data-icon="tabler:loader-2" style="animation: spin 1s linear infinite; margin-right: 0.5rem;"></span>正在跳转...';
    
    fetch('/user/order/pay', {
        method: 'POST',
        body: formData,
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (data.code === 0 && data.data && data.data.url) {
            window.location.href = data.data.url;
        } else {
            toast(data.message || '支付失败', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<span class="iconify" data-icon="tabler:shield-check" style="margin-right: 0.5rem;"></span>立即支付';
        }
    })
    .catch(function(err) {
        toast('网络错误，请重试', 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<span class="iconify" data-icon="tabler:shield-check" style="margin-right: 0.5rem;"></span>立即支付';
    });
});
</script>

<?php $content = ob_get_clean(); ?>
<?php include ML_ROOT . '/views/layouts/user.php'; ?>
