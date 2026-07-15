<?php $pageTitle = '计费统计';
ob_start(); ?>

<!-- 统计概览卡片 -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <div class="card">
        <div class="card-body" style="display: flex; align-items: center; padding: 1.5rem;">
            <div style="width: 3rem; height: 3rem; background: linear-gradient(135deg, #a855f7, #9333ea); border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; margin-right: 1rem; box-shadow: var(--shadow-sm);">
                <span class="iconify" data-icon="tabler:crown" style="font-size: 1.5rem; color: white;"></span>
            </div>
            <div>
                <div style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.25rem;">有效套餐</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--text-main); line-height: 1.2;"><?= number_format($packageStats['active_packages'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="display: flex; align-items: center; padding: 1.5rem;">
            <div style="width: 3rem; height: 3rem; background: linear-gradient(135deg, #3b82f6, #2563eb); border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; margin-right: 1rem; box-shadow: var(--shadow-sm);">
                <span class="iconify" data-icon="tabler:chart-line" style="font-size: 1.5rem; color: white;"></span>
            </div>
            <div>
                <div style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.25rem;">今日调用</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--text-main); line-height: 1.2;"><?= number_format($callStats['today_calls'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="display: flex; align-items: center; padding: 1.5rem;">
            <div style="width: 3rem; height: 3rem; background: linear-gradient(135deg, #10b981, #059669); border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; margin-right: 1rem; box-shadow: var(--shadow-sm);">
                <span class="iconify" data-icon="tabler:currency-yuan" style="font-size: 1.5rem; color: white;"></span>
            </div>
            <div>
                <div style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.25rem;">本月收入</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--text-main); line-height: 1.2;">¥<?= number_format($revenueStats['month_revenue'] ?? 0, 2) ?></div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="display: flex; align-items: center; padding: 1.5rem;">
            <div style="width: 3rem; height: 3rem; background: linear-gradient(135deg, #f59e0b, #d97706); border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; margin-right: 1rem; box-shadow: var(--shadow-sm);">
                <span class="iconify" data-icon="tabler:shopping-cart" style="font-size: 1.5rem; color: white;"></span>
            </div>
            <div>
                <div style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.25rem;">已付订单</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--text-main); line-height: 1.2;"><?= number_format($revenueStats['paid_orders'] ?? 0) ?></div>
            </div>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
    <!-- 调用趋势图 -->
    <div class="card">
        <div class="card-header">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 2.5rem; height: 2.5rem; background: linear-gradient(135deg, #3b82f6, #2563eb); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; box-shadow: var(--shadow-sm);">
                    <span class="iconify" data-icon="tabler:chart-bar" style="color: white; font-size: 1.25rem;"></span>
                </div>
                <div>
                    <h3 class="card-title">调用趋势</h3>
                    <p style="font-size: 0.75rem; color: var(--text-secondary); margin: 0;">最近7天API调用统计</p>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div id="trendChart" style="height: 250px; display: flex; align-items: flex-end; gap: 0.5rem; padding: 1rem 0;">
                <?php 
                $maxCount = 1;
                foreach ($dailyTrend as $day) {
                    $maxCount = max($maxCount, $day['count']);
                }
                foreach ($dailyTrend as $day): 
                    $height = ($day['count'] / $maxCount) * 200;
                ?>
                <div style="flex: 1; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;">
                    <div style="font-size: 0.75rem; color: var(--text-main); font-weight: 500;"><?= number_format($day['count']) ?></div>
                    <div style="width: 100%; height: <?= max(4, $height) ?>px; background: linear-gradient(180deg, #3b82f6, #60a5fa); border-radius: 4px 4px 0 0; transition: height 0.3s;"></div>
                    <div style="font-size: 0.6875rem; color: var(--text-muted);"><?= date('m/d', strtotime($day['date'])) ?></div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($dailyTrend)): ?>
                <div style="flex: 1; display: flex; align-items: center; justify-content: center; color: var(--text-muted);">暂无数据</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- 调用统计 -->
    <div class="card">
        <div class="card-header">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 2.5rem; height: 2.5rem; background: linear-gradient(135deg, #10b981, #059669); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; box-shadow: var(--shadow-sm);">
                    <span class="iconify" data-icon="tabler:chart-pie" style="color: white; font-size: 1.25rem;"></span>
                </div>
                <div>
                    <h3 class="card-title">调用统计</h3>
                    <p style="font-size: 0.75rem; color: var(--text-secondary); margin: 0;">按时间段统计</p>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: var(--bg-surface-hover); border-radius: var(--radius-md);">
                    <span style="color: var(--text-secondary); font-size: 0.875rem;">今日调用</span>
                    <span style="font-weight: 600; color: var(--text-main);"><?= number_format($callStats['today_calls'] ?? 0) ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: var(--bg-surface-hover); border-radius: var(--radius-md);">
                    <span style="color: var(--text-secondary); font-size: 0.875rem;">本周调用</span>
                    <span style="font-weight: 600; color: var(--text-main);"><?= number_format($callStats['week_calls'] ?? 0) ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: var(--bg-surface-hover); border-radius: var(--radius-md);">
                    <span style="color: var(--text-secondary); font-size: 0.875rem;">本月调用</span>
                    <span style="font-weight: 600; color: var(--text-main);"><?= number_format($callStats['month_calls'] ?? 0) ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: var(--bg-surface-hover); border-radius: var(--radius-md);">
                    <span style="color: var(--text-secondary); font-size: 0.875rem;">总调用</span>
                    <span style="font-weight: 600; color: var(--text-main);"><?= number_format($callStats['total_calls'] ?? 0) ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem; margin-bottom: 2rem;">
    <!-- 套餐类型分布 -->
    <div class="card">
        <div class="card-header">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 2.5rem; height: 2.5rem; background: linear-gradient(135deg, #a855f7, #9333ea); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; box-shadow: var(--shadow-sm);">
                    <span class="iconify" data-icon="tabler:stack-2" style="color: white; font-size: 1.25rem;"></span>
                </div>
                <div>
                    <h3 class="card-title">套餐分布</h3>
                    <p style="font-size: 0.75rem; color: var(--text-secondary); margin: 0;">按套餐类型统计</p>
                </div>
            </div>
        </div>
        <div class="card-body">
            <?php 
            $typeLabels = ['package' => '套餐包', 'account' => '账号包', 'quota' => '次数包'];
            $typeColors = ['package' => '#a855f7', 'account' => '#3b82f6', 'quota' => '#10b981'];
            ?>
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <?php foreach ($packagesByType as $item): ?>
                <div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span style="font-size: 0.875rem; color: var(--text-main);"><?= $typeLabels[$item['type']] ?? $item['type'] ?></span>
                        <span style="font-size: 0.875rem; color: var(--text-muted);">
                            <?= number_format($item['active_count'] ?? 0) ?> 有效 / <?= number_format($item['count']) ?> 总计
                        </span>
                    </div>
                    <div style="height: 8px; background: var(--bg-surface-hover); border-radius: 4px; overflow: hidden;">
                        <?php 
                        $total = $item['count'] ?: 1;
                        $activePercent = (($item['active_count'] ?? 0) / $total) * 100;
                        ?>
                        <div style="height: 100%; width: <?= $activePercent ?>%; background: <?= $typeColors[$item['type']] ?? '#6b7280' ?>; transition: width 0.3s;"></div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($packagesByType)): ?>
                <p style="color: var(--text-muted); text-align: center; padding: 2rem 0;">暂无套餐数据</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- 按套餐类型调用分布 -->
    <div class="card">
        <div class="card-header">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 2.5rem; height: 2.5rem; background: linear-gradient(135deg, #f59e0b, #d97706); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; box-shadow: var(--shadow-sm);">
                    <span class="iconify" data-icon="tabler:chart-donut" style="color: white; font-size: 1.25rem;"></span>
                </div>
                <div>
                    <h3 class="card-title">调用来源</h3>
                    <p style="font-size: 0.75rem; color: var(--text-secondary); margin: 0;">按套餐类型统计调用</p>
                </div>
            </div>
        </div>
        <div class="card-body">
            <?php 
            $callTypeLabels = ['package' => '套餐包', 'account' => '账号包', 'quota' => '次数包', 'free' => '免费用户'];
            $callTypeColors = ['package' => '#a855f7', 'account' => '#3b82f6', 'quota' => '#10b981', 'free' => '#6b7280'];
            $totalCalls = array_sum(array_column($callsByPackageType, 'count')) ?: 1;
            ?>
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <?php foreach ($callsByPackageType as $item): ?>
                <div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span style="font-size: 0.875rem; color: var(--text-main);"><?= $callTypeLabels[$item['package_type']] ?? $item['package_type'] ?></span>
                        <span style="font-size: 0.875rem; color: var(--text-muted);">
                            <?= number_format($item['count']) ?> (<?= number_format(($item['count'] / $totalCalls) * 100, 1) ?>%)
                        </span>
                    </div>
                    <div style="height: 8px; background: var(--bg-surface-hover); border-radius: 4px; overflow: hidden;">
                        <div style="height: 100%; width: <?= ($item['count'] / $totalCalls) * 100 ?>%; background: <?= $callTypeColors[$item['package_type']] ?? '#6b7280' ?>; transition: width 0.3s;"></div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($callsByPackageType)): ?>
                <p style="color: var(--text-muted); text-align: center; padding: 2rem 0;">暂无调用数据</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
    <!-- 平台调用排行 -->
    <div class="card">
        <div class="card-header">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 2.5rem; height: 2.5rem; background: linear-gradient(135deg, #ec4899, #db2777); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; box-shadow: var(--shadow-sm);">
                    <span class="iconify" data-icon="tabler:apps" style="color: white; font-size: 1.25rem;"></span>
                </div>
                <div>
                    <h3 class="card-title">平台排行</h3>
                    <p style="font-size: 0.75rem; color: var(--text-secondary); margin: 0;">按登录平台统计调用</p>
                </div>
            </div>
        </div>
        <div class="card-body" style="padding: 0;">
            <table class="table" style="margin: 0;">
                <thead>
                    <tr>
                        <th style="width: 60px;">排名</th>
                        <th>平台</th>
                        <th style="text-align: right;">调用次数</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $rank = 1;
                    foreach ($callsByPlatform as $item): 
                    ?>
                    <tr>
                        <td>
                            <?php if ($rank <= 3): ?>
                            <span style="display: inline-flex; align-items: center; justify-content: center; width: 1.5rem; height: 1.5rem; border-radius: 50%; background: <?= $rank === 1 ? '#fbbf24' : ($rank === 2 ? '#9ca3af' : '#cd7f32') ?>; color: white; font-size: 0.75rem; font-weight: 600;"><?= $rank ?></span>
                            <?php else: ?>
                            <span style="color: var(--text-muted);"><?= $rank ?></span>
                            <?php endif; ?>
                        </td>
                        <td style="font-weight: 500;"><?= get_platform_name($item['platform']) ?></td>
                        <td style="text-align: right; color: var(--text-muted);"><?= number_format($item['count']) ?></td>
                    </tr>
                    <?php $rank++; endforeach; ?>
                    <?php if (empty($callsByPlatform)): ?>
                    <tr>
                        <td colspan="3" style="text-align: center; color: var(--text-muted); padding: 2rem;">暂无数据</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 收入统计 -->
    <div class="card">
        <div class="card-header">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 2.5rem; height: 2.5rem; background: linear-gradient(135deg, #10b981, #059669); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; box-shadow: var(--shadow-sm);">
                    <span class="iconify" data-icon="tabler:wallet" style="color: white; font-size: 1.25rem;"></span>
                </div>
                <div>
                    <h3 class="card-title">收入统计</h3>
                    <p style="font-size: 0.75rem; color: var(--text-secondary); margin: 0;">订单收入汇总</p>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                <div style="text-align: center; padding: 1.5rem; background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.1)); border-radius: var(--radius-lg);">
                    <div style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 0.5rem;">累计收入</div>
                    <div style="font-size: 2rem; font-weight: 700; color: #10b981;">¥<?= number_format($revenueStats['total_revenue'] ?? 0, 2) ?></div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div style="padding: 1rem; background: var(--bg-surface-hover); border-radius: var(--radius-md); text-align: center;">
                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">今日收入</div>
                        <div style="font-size: 1.25rem; font-weight: 600; color: var(--text-main);">¥<?= number_format($revenueStats['today_revenue'] ?? 0, 2) ?></div>
                    </div>
                    <div style="padding: 1rem; background: var(--bg-surface-hover); border-radius: var(--radius-md); text-align: center;">
                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">本月收入</div>
                        <div style="font-size: 1.25rem; font-weight: 600; color: var(--text-main);">¥<?= number_format($revenueStats['month_revenue'] ?? 0, 2) ?></div>
                    </div>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: var(--bg-surface-hover); border-radius: var(--radius-md);">
                    <span style="color: var(--text-secondary); font-size: 0.875rem;">已付订单数</span>
                    <span style="font-weight: 600; color: var(--text-main);"><?= number_format($revenueStats['paid_orders'] ?? 0) ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>

<?php include ML_ROOT . '/views/layouts/admin.php'; ?>
