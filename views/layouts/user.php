<?php
$isPartialRequest = !empty($GLOBALS['_PARTIAL_REQUEST']);

if ($isPartialRequest) {
    echo $content ?? '';
    return;
}

$extraCss = ['/assets/css/user.css'];
include ML_ROOT . '/views/layouts/header_dashboard.php';
?>

<div class="user-wrapper">
    <?php include ML_ROOT . '/views/layouts/user_sidebar.php'; ?>

    <main class="user-content">
        <div class="page-body" id="main-content">
            <?= $content ?? '' ?>
        </div>

        <footer class="page-footer">
            <p>&copy; <?= date('Y') ?> <?= e($siteSettings['site_name'] ?? 'Max Login') ?>. All rights reserved.</p>
            <?php if (!empty($siteSettings['site_icp'])): ?>
                <a href="https://beian.miit.gov.cn/" target="_blank" class="icp-link"><?= e($siteSettings['site_icp']) ?></a>
            <?php endif; ?>
        </footer>
    </main>
</div>

<script>
    function copyText(text) {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(() => toast('复制成功', 'success'));
        } else {
            let textArea = document.createElement("textarea");
            textArea.value = text;
            textArea.style.position = "fixed";
            textArea.style.left = "-9999px";
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            try {
                document.execCommand('copy');
                toast('复制成功', 'success');
            } catch (err) {
                toast('复制失败', 'error');
            }
            document.body.removeChild(textArea);
        }
    }
    (function() {
        var sidebar = document.querySelector('.user-sidebar');
        if (!sidebar) return;
        sidebar.addEventListener('click', function(e) {
            var link = e.target.closest('a[href]');
            if (!link) return;
            
            var href = link.getAttribute('href');
            if (!href || href === '#' || href.indexOf('logout') !== -1) return;
            // 忽略锚点链接，让页面内导航正常工作
            if (href.indexOf('#') === 0) return;
            if (href.indexOf('http') === 0 && href.indexOf(window.location.origin) !== 0) return;
            
            e.preventDefault();
            loadPage(href, link.classList.contains('sidebar-item') ? link : null);
        });

        function loadPage(url, clickedLink) {
            var mainContent = document.getElementById('main-content');
            if (!mainContent) {
                window.location.href = url;
                return;
            }
            mainContent.style.opacity = '0.5';
            mainContent.style.pointerEvents = 'none';

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-Partial': '1'
                }
            })
            .then(function(response) {
                if (!response.ok) throw new Error('Network error');
                if (response.redirected && response.url.indexOf('login') !== -1) {
                    window.location.href = response.url;
                    return null;
                }
                return response.text();
            })
            .then(function(html) {
                if (html === null) return;
                if (html.indexOf('<!DOCTYPE') !== -1 || html.indexOf('<html') !== -1) {
                    window.location.href = url;
                    return;
                }
                mainContent.innerHTML = html;
                mainContent.style.opacity = '1';
                mainContent.style.pointerEvents = '';
                if (clickedLink) {
                    sidebar.querySelectorAll('.sidebar-item').forEach(function(item) {
                        item.classList.remove('active');
                    });
                    clickedLink.classList.add('active');
                    // 更新页面标题
                    var linkText = clickedLink.textContent.trim();
                    if (linkText) {
                        document.title = linkText + ' - <?= e($siteSettings['site_name'] ?? 'Max Login') ?>';
                    }
                }
                history.pushState({url: url, title: document.title}, '', url);
                if (typeof window.initIcons === 'function') {
                    window.initIcons();
                }
                mainContent.querySelectorAll('script').forEach(function(oldScript) {
                    var newScript = document.createElement('script');
                    if (oldScript.src) {
                        newScript.src = oldScript.src;
                    } else {
                        newScript.textContent = oldScript.textContent;
                    }
                    oldScript.parentNode.replaceChild(newScript, oldScript);
                });
                mainContent.scrollTop = 0;
            })
            .catch(function(error) {
                console.error('AJAX load error:', error);
                mainContent.style.opacity = '1';
                mainContent.style.pointerEvents = '';
                window.location.href = url;
            });
        }
        window.addEventListener('popstate', function(e) {
            if (e.state && e.state.url) {
                loadPage(e.state.url, null);
                // 恢复页面标题
                var activeLink = sidebar.querySelector('.sidebar-item.active');
                if (activeLink) {
                    var linkText = activeLink.textContent.trim();
                    if (linkText) {
                        document.title = linkText + ' - <?= e($siteSettings['site_name'] ?? 'Max Login') ?>';
                    }
                }
            }
        });
        history.replaceState({url: window.location.href, title: document.title}, '', window.location.href);
    })();
</script>

<?php include ML_ROOT . '/views/layouts/footer_dashboard.php'; ?>