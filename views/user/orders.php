<?php $pageTitle = '我的订单'; ob_start(); 
$typeLabels = ['package' => '订阅套餐', 'quota' => '次数包', 'account' => '账号包'];
$typeBadges = ['package' => 'badge-primary', 'quota' => 'badge-success', 'account' => 'badge-info'];
?>

<!-- 订单列表 -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">订单记录</h3>
        <a href="/user/products" class="btn btn-primary btn-sm">购买产品</a>
    </div>

    <?php if (empty($orders)): ?>
    <div class="card-body" style="text-align: center; padding: 3rem;">
        <span class="iconify" data-icon="tabler:file-text" style="font-size: 3rem; color: var(--text-light); margin-bottom: 1rem; display: block;"></span>
        <p style="color: var(--text-muted);">暂无订单记录</p>
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>订单号</th>
                    <th>产品</th>
                    <th>金额</th>
                    <th>支付方式</th>
                    <th>状态</th>
                    <th>创建时间</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $statusLabels = [0 => '待支付', 1 => '已支付', 2 => '已取消', 3 => '已退款'];
                $statusBadges = [0 => 'badge-warning', 1 => 'badge-success', 2 => 'badge-secondary', 3 => 'badge-error'];
                $payLabels = ['alipay' => '支付宝', 'wechat' => '微信', 'qqpay' => 'QQ钱包'];
                ?>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td style="font-family: monospace; font-size: 0.75rem;"><?= e($order['no']) ?></td>
                    <td>
                        <p style="font-weight: 500; margin: 0;"><?= e($order['product_name']) ?></p>
                        <span class="badge <?= $typeBadges[$order['product_type']] ?? 'badge-secondary' ?>" style="font-size: 0.625rem;">
                            <?= $typeLabels[$order['product_type']] ?? $order['product_type'] ?>
                        </span>
                    </td>
                    <td style="font-weight: 500;">¥<?= number_format($order['amount'], 2) ?></td>
                    <td><?= $payLabels[$order['method']] ?? '-' ?></td>
                    <td>
                        <span class="badge <?= $statusBadges[$order['status']] ?? 'badge-secondary' ?>">
                            <?= $statusLabels[$order['status']] ?? '未知' ?>
                        </span>
                    </td>
                    <td style="color: var(--text-muted);"><?= $order['time'] ?></td>
                    <td>
                        <?php if ($order['status'] == 0): ?>
                        <a href="/user/order/pay/<?= e($order['no']) ?>" class="btn btn-primary btn-sm" style="margin-right: 0.5rem;">去支付</a>
                        <button onclick="cancelOrder('<?= e($order['no']) ?>')" class="btn btn-sm" style="background: #ef4444; color: #fff;">取消</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- 分页 -->
    <?php if ($totalPages > 1): ?>
    <div class="card-footer" style="display: flex; align-items: center; justify-content: space-between;">
        <p style="font-size: 0.875rem; color: var(--text-muted); margin: 0;">共 <?= $total ?> 条记录</p>
        <div style="display: flex; gap: 0.5rem;">
            <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>" class="btn btn-outline btn-sm">上一页</a>
            <?php endif; ?>
            <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?>" class="btn btn-outline btn-sm">下一页</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<!-- 取消订单确认弹窗 -->
<div id="cancelModal" class="modal">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h3 class="modal-title">取消订单</h3>
            <button onclick="hideCancelModal()" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">确定要取消此订单吗？取消后将无法恢复。</p>
            <input type="hidden" id="cancelOrderNo">
            <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                <button onclick="hideCancelModal()" class="btn btn-outline">取消</button>
                <button onclick="confirmCancelOrder()" class="btn" style="background: #ef4444; color: #fff;">确认取消</button>
            </div>
        </div>
    </div>
</div>

<script>
function cancelOrder(orderNo) {
    document.getElementById('cancelOrderNo').value = orderNo;
    document.getElementById('cancelModal').classList.add('show');
}

function hideCancelModal() {
    document.getElementById('cancelModal').classList.remove('show');
}

function confirmCancelOrder() {
    const orderNo = document.getElementById('cancelOrderNo').value;
    
    ajax('/user/order/cancel', {_token: '<?= e($csrf_token) ?>', order_no: orderNo}, function(data) {
        if (data.code === 0) {
            hideCancelModal();
            toast('订单已取消', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            toast(data.message, 'error');
        }
    });
}
</script>

<?php $content = ob_get_clean(); ?>
<?php include ML_ROOT . '/views/layouts/user.php'; ?>
