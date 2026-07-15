<?php $pageTitle = '用户管理';
ob_start(); ?>

<!-- 筛选 -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end;">
            <div style="flex: 1; min-width: 200px;">
                <label class="form-label">登录平台</label>
                <select name="platform" class="form-control">
                    <option value="">全部平台</option>
                    <option value="qq" <?= $filter['platform'] === 'qq' ? 'selected' : '' ?>>QQ</option>
                    <option value="wx" <?= $filter['platform'] === 'wx' ? 'selected' : '' ?>>微信</option>
                    <option value="alipay" <?= $filter['platform'] === 'alipay' ? 'selected' : '' ?>>支付宝</option>
                    <option value="sina" <?= $filter['platform'] === 'sina' ? 'selected' : '' ?>>微博</option>
                    <option value="baidu" <?= $filter['platform'] === 'baidu' ? 'selected' : '' ?>>百度</option>
                    <option value="github" <?= $filter['platform'] === 'github' ? 'selected' : '' ?>>GitHub</option>
                    <option value="google" <?= $filter['platform'] === 'google' ? 'selected' : '' ?>>Google</option>
                    <option value="gitee" <?= $filter['platform'] === 'gitee' ? 'selected' : '' ?>>Gitee</option>
                </select>
            </div>
            <div style="flex: 1; min-width: 200px;">
                <label class="form-label">来源应用</label>
                <select name="app_id" class="form-control">
                    <option value="">全部应用</option>
                    <?php foreach ($userApps as $app): ?>
                        <option value="<?= e($app['app_id']) ?>" <?= $filter['app_id'] === $app['app_id'] ? 'selected' : '' ?>><?= e($app['app_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="flex: 1; min-width: 200px;">
                <label class="form-label">搜索用户</label>
                <input type="text" name="search" value="<?= e($filter['search']) ?>" placeholder="UID或昵称" class="form-control">
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn btn-primary">
                    <span class="iconify" data-icon="tabler:filter" style="margin-right: 0.25rem;"></span> 筛选
                </button>
                <a href="/user/members" class="btn btn-outline">重置</a>
            </div>
        </form>
    </div>
</div>

<!-- 用户列表 -->
<div class="card">
    <div class="card-body" style="padding: 0;">
        <?php if (empty($members)): ?>
            <div style="text-align: center; padding: 4rem 2rem;">
                <div style="color: var(--text-muted); margin-bottom: 1rem;">
                    <span class="iconify" data-icon="tabler:user-search" style="font-size: 4rem; opacity: 0.5;"></span>
                </div>
                <h5 style="color: var(--text-muted); font-size: 1.125rem; margin-bottom: 0.5rem;">暂无登录用户</h5>
                <p style="color: var(--text-light); font-size: 0.875rem;">当有用户通过您的应用登录后，将在此显示</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>用户</th>
                            <th>平台</th>
                            <th>UID</th>
                            <th>来源应用</th>
                            <th>登录次数</th>
                            <th>注册时间</th>
                            <th>最后登录</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($members as $member): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <?php if ($member['avatar']): ?>
                                            <img src="<?= e(ensure_https($member['avatar'])) ?>" alt="" style="width: 2.5rem; height: 2.5rem; border-radius: 50%; object-fit: cover;">
                                        <?php else: ?>
                                            <div style="width: 2.5rem; height: 2.5rem; border-radius: 50%; background-color: var(--bg-surface-hover); display: flex; align-items: center; justify-content: center;">
                                                <span class="iconify" data-icon="tabler:user" style="font-size: 1.25rem; color: var(--text-light);"></span>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div style="font-weight: 500; color: var(--text-main);"><?= e($member['nickname'] ?: '未知用户') ?></div>
                                            <div style="font-size: 0.75rem; color: var(--text-muted);">
                                                <?php
                                                $gender = $member['gender'] ?? 0;
                                                if ($gender == 1) echo '♂ 男';
                                                elseif ($gender == 2) echo '♀ 女';
                                                else echo '未知';
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    $type = $member['type'] ?? '';
                                    $platformIcon = get_platform_icon($type);
                                    $platformName = get_platform_name($type);
                                    ?>
                                    <div style="display: inline-flex; align-items: center; gap: 0.5rem;">
                                        <img src="<?= e($platformIcon) ?>" alt="<?= e($platformName) ?>" style="width: 1.25rem; height: 1.25rem;">
                                        <span style="font-size: 0.8125rem; font-weight: 500; color: var(--text-main);"><?= e($platformName) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <code style="font-size: 0.75rem; background-color: var(--bg-surface-hover); padding: 0.25rem 0.5rem; border-radius: 0.25rem; cursor: pointer;" onclick="copyText('<?= e($member['open_id']) ?>')" title="点击复制">
                                        <?= e(substr($member['open_id'], 0, 20)) ?><?= strlen($member['open_id']) > 20 ? '...' : '' ?>
                                    </code>
                                </td>
                                <td style="color: var(--text-muted);"><?= e($member['app_name'] ?? '-') ?></td>
                                <td style="color: var(--text-muted);"><?= number_format($member['login_count'] ?? 1) ?> 次</td>
                                <td style="color: var(--text-muted); font-size: 0.875rem;"><?= e($member['time']) ?></td>
                                <td style="color: var(--text-muted); font-size: 0.875rem;"><?= e($member['last_time']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.5rem; border-top: 1px solid var(--border-color);">
                    <div style="font-size: 0.875rem; color: var(--text-muted);">共 <?= number_format($total) ?> 个用户</div>
                    <div style="display: flex; gap: 0.5rem;">
                        <?php
                        $queryString = http_build_query(array_filter([
                            'platform' => $filter['platform'],
                            'app_id' => $filter['app_id'],
                            'search' => $filter['search'],
                        ]));
                        $queryPrefix = $queryString ? '&' : '';
                        ?>
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page - 1 ?><?= $queryPrefix . $queryString ?>" class="btn btn-outline btn-sm">上一页</a>
                        <?php endif; ?>
                        <span class="btn btn-sm" style="border: none; background: none; cursor: default;">第 <?= $page ?> / <?= $totalPages ?> 页</span>
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?= $page + 1 ?><?= $queryPrefix . $queryString ?>" class="btn btn-outline btn-sm">下一页</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include ML_ROOT . '/views/layouts/user.php'; ?>