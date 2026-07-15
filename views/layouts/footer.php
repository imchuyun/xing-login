    </main>

    <!-- Footer -->
    <footer style="background-color: var(--bg-surface); border-top: 1px solid var(--border-color); padding: 4rem 0 2rem; margin-top: auto;">
        <div class="container">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 3rem; margin-bottom: 3rem;">
                <div style="grid-column: span 2;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                        <img src="<?= e($siteSettings['site_logo'] ?? '/assets/logo.png') ?>" alt="Logo" style="width: 2rem; height: 2rem; object-fit: contain;">
                        <span style="font-weight: 800; font-size: 1.5rem; color: var(--text-main);"><?= e($siteSettings['site_name'] ?? 'Max Login') ?></span>
                    </div>
                    <p class="text-muted" style="max-width: 300px; font-size: 0.875rem;">
                        企业级聚合登录认证平台，提供安全、稳定、高效的身份验证服务。助力企业快速构建统一身份认证体系。
                    </p>
                </div>

                <div>
                    <h4 style="font-size: 1rem; margin-bottom: 1rem;">产品与服务</h4>
                    <ul style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <li><a href="/document" class="text-muted text-sm hover:text-primary">接入文档</a></li>
                    </ul>
                </div>

                <div>
                    <h4 style="font-size: 1rem; margin-bottom: 1rem;">关于我们</h4>
                    <ul style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <li><a href="/about" class="text-muted text-sm hover:text-primary">公司介绍</a></li>
                        <li><a href="/contact" class="text-muted text-sm hover:text-primary">联系方式</a></li>
                    </ul>
                </div>
            </div>

            <div style="border-top: 1px solid var(--border-color); padding-top: 2rem; display: flex; flex-direction: column; gap: 1rem; align-items: center;">
                <div class="text-muted text-sm text-center">
                    &copy; <?= date('Y') ?> <?= e($siteSettings['site_name'] ?? 'Max Login') ?>. All rights reserved.
                </div>
                <?php if (!empty($siteSettings['site_icp'])): ?>
                    <a href="https://beian.miit.gov.cn/" target="_blank" class="text-muted text-xs hover:text-primary">
                        <?= e($siteSettings['site_icp']) ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </footer>

    <!-- JS -->
    <script src="/assets/js/main.js"></script>
    <script>
        function toast(message, type = 'info') {
            const colors = {
                success: 'var(--color-success)',
                error: 'var(--color-error)',
                info: 'var(--color-primary)'
            };

            const div = document.createElement('div');
            div.style.cssText = `
                position: fixed; top: 24px; right: 24px; 
                background: var(--bg-surface); color: ${colors[type]}; 
                padding: 1rem 1.5rem; border-radius: var(--radius-md); 
                box-shadow: var(--shadow-lg); 
                border-left: 4px solid ${colors[type]};
                z-index: 100000; font-size: 0.875rem; font-weight: 500;
                display: flex; align-items: center; gap: 0.75rem;
                animation: slideIn 0.3s ease-out forwards;
            `;

            let icon = 'info-circle-filled';
            if (type === 'success') icon = 'circle-check-filled';
            if (type === 'error') icon = 'alert-triangle-filled';

            div.innerHTML = `<span class="iconify" data-icon="tabler:${icon}" style="font-size: 1.25rem; margin-right: 0.25rem;"></span> ${message}`;
            document.body.appendChild(div);

            if (!document.getElementById('toast-style')) {
                const style = document.createElement('style');
                style.id = 'toast-style';
                style.textContent = `@keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }`;
                document.head.appendChild(style);
            }

            setTimeout(() => {
                div.style.opacity = '0';
                div.style.transform = 'translateX(100%)';
                div.style.transition = 'all 0.3s ease';
                setTimeout(() => div.remove(), 300);
            }, 3000);
        }
    </script>
    </body>

    </html>