<?php $pageTitle = '订单管理';
ob_start(); ?>
<?php
$statusLabels = [0 => '待支付', 1 => '已支付', 2 => '已取消', 3 => '已退款'];
$statusColors = [0 => 'badge-warning', 1 => 'badge-success', 2 => 'badge-secondary', 3 => 'badge-error'];
$payLabels = ['alipay' => '支付宝', 'wechat' => '微信', 'qqpay' => 'QQ钱包'];
?>

<div class="card mb-4">
    <div class="card-body">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main);">订单管理</h2>

            <div style="display: flex; gap: 0.5rem;">
                <a href="?status=" class="btn <?= $filter['status'] === '' ? 'btn-primary' : 'btn-outline' ?> btn-sm">全部</a>
                <a href="?status=0" class="btn <?= $filter['status'] === '0' ? 'btn-primary' : 'btn-outline' ?> btn-sm">待支付</a>
                <a href="?status=1" class="btn <?= $filter['status'] === '1' ? 'btn-primary' : 'btn-outline' ?> btn-sm">已支付</a>
                <a href="?status=2" class="btn <?= $filter['status'] === '2' ? 'btn-primary' : 'btn-outline' ?> btn-sm">已取消</a>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body" style="padding: 0;">
        <?php if (empty($orders)): ?>
            <div style="text-align: center; padding: 4rem 2rem;">
                <div style="width: 4rem; height: 4rem; background-color: var(--bg-surface-hover); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                    <span class="iconify" data-icon="tabler:file-text" style="font-size: 2rem; color: var(--text-muted);"></span>
                </div>
                <p style="color: var(--text-muted);">暂无订单</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>订单号</th>
                            <th>用户</th>
                            <th>产品</th>
                            <th>金额</th>
                            <th>支付方式</th>
                            <th>状态</th>
                            <th>创建时间</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td style="font-family: monospace; color: var(--text-secondary);"><?= e($order['no']) ?></td>
                                <td style="font-weight: 500; color: var(--text-main);"><?= e($order['username'] ?? '-') ?></td>
                                <td>
                                    <div style="font-weight: 500; color: var(--text-main);"><?= e($order['product_name']) ?></div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);"><?= $order['product_type'] === 'package' ? '套餐' : '次数包' ?></div>
                                </td>
                                <td style="font-weight: 700; color: var(--text-main);">¥<?= number_format($order['amount'], 2) ?></td>
                                <td style="color: var(--text-secondary);"><?= $payLabels[$order['method']] ?? '-' ?></td>
                                <td>
                                    <span class="badge <?= $statusColors[$order['status']] ?? '' ?>">
                                        <?= $statusLabels[$order['status']] ?? '未知' ?>
                                    </span>
                                </td>
                                <td style="color: var(--text-muted); font-size: 0.875rem;"><?= $order['time'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- 分页 -->
            <?php if ($totalPages > 1): ?>
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.5rem; border-top: 1px solid var(--border-color);">
                    <p style="font-size: 0.875rem; color: var(--text-muted);">共 <?= $total ?> 条记录</p>
                    <div style="display: flex; gap: 0.5rem;">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page - 1 ?>&status=<?= e($filter['status']) ?>" class="btn btn-outline btn-sm">上一页</a>
                        <?php endif; ?>
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?= $page + 1 ?>&status=<?= e($filter['status']) ?>" class="btn btn-outline btn-sm">下一页</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include ML_ROOT . '/views/layouts/admin.php'; ?>