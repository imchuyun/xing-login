<!-- 系统设置子布局 -->
<div class="settings-layout">
    <!-- 左侧菜单 -->
    <div class="settings-sidebar-wrapper">
        <div class="card">
            <div class="card-body p-2">
                <nav class="settings-nav" id="settingsNav">
                    <a href="<?= admin_url('settings/site') ?>" class="settings-tab <?= strpos($_SERVER['REQUEST_URI'], '/settings/site') !== false ? 'active' : '' ?>">
                        <span class="iconify" data-icon="tabler:settings"></span>
                        网站信息
                    </a>
                    <a href="<?= admin_url('settings/auth') ?>" class="settings-tab <?= strpos($_SERVER['REQUEST_URI'], '/settings/auth') !== false ? 'active' : '' ?>">
                        <span class="iconify" data-icon="tabler:user-plus"></span>
                        注册登录
                    </a>
                    <a href="<?= admin_url('settings/notify') ?>" class="settings-tab <?= strpos($_SERVER['REQUEST_URI'], '/settings/notify') !== false ? 'active' : '' ?>">
                        <span class="iconify" data-icon="tabler:bell"></span>
                        通知账号
                    </a>
                    <a href="<?= admin_url('settings/payment') ?>" class="settings-tab <?= strpos($_SERVER['REQUEST_URI'], '/settings/payment') !== false ? 'active' : '' ?>">
                        <span class="iconify" data-icon="tabler:credit-card"></span>
                        支付通道
                    </a>
                    <a href="<?= admin_url('settings/verification') ?>" class="settings-tab <?= strpos($_SERVER['REQUEST_URI'], '/settings/verification') !== false ? 'active' : '' ?>">
                        <span class="iconify" data-icon="tabler:shield-check"></span>
                        身份认证
                    </a>
                    <a href="<?= admin_url('settings/security') ?>" class="settings-tab <?= strpos($_SERVER['REQUEST_URI'], '/settings/security') !== false ? 'active' : '' ?>">
                        <span class="iconify" data-icon="tabler:shield-lock"></span>
                        安全访问
                    </a>
                    <a href="<?= admin_url('settings/billing') ?>" class="settings-tab <?= strpos($_SERVER['REQUEST_URI'], '/settings/billing') !== false ? 'active' : '' ?>">
                        <span class="iconify" data-icon="tabler:currency-yuan"></span>
                        计费设置
                    </a>
                </nav>
            </div>
        </div>
    </div>

    <!-- 右侧内容 -->
    <div class="settings-content-wrapper" id="settingsContent">
        <?= $settingsContent ?? '' ?>
    </div>
</div>

<script>
    (function() {
        const tabs = document.querySelectorAll('.settings-tab');
        const contentArea = document.getElementById('settingsContent');

        function updateTabActive(url) {
            tabs.forEach(tab => {
                const href = tab.getAttribute('href');
                if (url.includes(href.split('/').pop())) {
                    tab.classList.add('active');
                } else {
                    tab.classList.remove('active');
                }
            });
        }

        async function loadSettingsTab(url) {
            contentArea.style.opacity = '0.5';
            try {
                const response = await fetch(url);
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newContent = doc.getElementById('settingsContent');

                if (newContent) {
                    contentArea.style.transition = 'opacity 0.15s ease';
                    contentArea.style.opacity = '0';

                    setTimeout(() => {
                        contentArea.innerHTML = newContent.innerHTML;
                        history.pushState({
                            url: url
                        }, '', url);
                        updateTabActive(url);
                        contentArea.querySelectorAll('script').forEach(oldScript => {
                            const newScript = document.createElement('script');
                            newScript.textContent = oldScript.textContent;
                            oldScript.parentNode.replaceChild(newScript, oldScript);
                        });

                        contentArea.style.opacity = '1';
                    }, 150);
                } else {
                    window.location.href = url;
                }
            } catch (error) {
                console.error('Failed to load settings tab:', error);
                window.location.href = url;
            }
        }

        tabs.forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                loadSettingsTab(this.getAttribute('href'));
            });
        });
        window.addEventListener('popstate', function(e) {
            if (e.state && e.state.url) {
                loadSettingsTab(e.state.url);
            } else {
                location.reload();
            }
        });
    })();
</script>