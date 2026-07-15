<?php 
$pageTitle = '首页';
$extraCss = ['/assets/css/landing.css'];
ob_start(); 
?>

<!-- Hero Section -->
<section class="hero-section" id="hero-section">
    <!-- Grid Background -->
    <div class="hero-grid-bg">
        <svg width="100%" height="100%" viewBox="0 0 1220 810" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid slice">
            <defs>
                <linearGradient id="gridGradient" x1="35" y1="24" x2="904" y2="632" gradientUnits="userSpaceOnUse">
                    <stop stop-color="currentColor" stop-opacity="0"/>
                    <stop offset="1" stop-color="currentColor" stop-opacity="0.15"/>
                </linearGradient>
                <mask id="gridMask" style="mask-type: alpha" maskUnits="userSpaceOnUse" x="10" y="-1" width="1200" height="812">
                    <rect x="10" y="-1" width="1200" height="812" fill="url(#gridGradient)"/>
                </mask>
            </defs>
            <g mask="url(#gridMask)">
                <?php for ($i = 0; $i < 35; $i++): ?>
                    <?php for ($j = 0; $j < 23; $j++): ?>
                        <rect 
                            x="<?= -20 + $i * 36 ?>" 
                            y="<?= 9 + $j * 36 ?>" 
                            width="35.6" 
                            height="35.6" 
                            stroke="currentColor" 
                            stroke-opacity="0.08" 
                            stroke-width="0.4"
                            stroke-dasharray="2 2"
                        />
                    <?php endfor; ?>
                <?php endfor; ?>
            </g>
        </svg>
    </div>
    
    <!-- Glow Effect -->
    <div class="hero-glow animate-appear-zoom delay-500"></div>
    
    <!-- Hero Content -->
    <div class="hero-content">
        <div class="animate-appear">
            <span class="hero-badge">
                <span class="iconify" data-icon="tabler:sparkles"></span>
                MAXLOGIN 聚合登录
            </span>
        </div>
        
        <h1 class="hero-title animate-appear delay-100">
            统一身份认证<br>
            <span class="gradient-text">一个API连接所有平台</span>
        </h1>
        
        <p class="hero-description animate-appear delay-200">
            支持QQ、微信、支付宝、GitHub等50+主流OAuth登录方式。一套API简化身份验证流程，降低开发成本，让您专注核心业务。
        </p>
        
        <div class="hero-buttons animate-appear delay-300">
            <a href="/user/reg" class="btn btn-primary">
                免费开始使用
                <span class="iconify" data-icon="tabler:arrow-right"></span>
            </a>
            <a href="/document" class="btn btn-outline">
                <span class="iconify" data-icon="tabler:book"></span>
                接入文档
            </a>
        </div>
        
        <div class="hero-stats animate-appear delay-400">
            <div class="stat-item">
                <span class="iconify" data-icon="tabler:checks"></span>
                <span>50+ 登录平台</span>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-item">
                <span class="iconify" data-icon="tabler:checks"></span>
                <span>99.9% 服务可用性</span>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-item">
                <span class="iconify" data-icon="tabler:checks"></span>
                <span>企业级安全保障</span>
            </div>
        </div>
    </div>
</section>

<!-- Supported Providers -->
<section class="providers-section">
    <h3 class="providers-title">支持的登录平台</h3>
    <div class="providers-grid">
        <?php
        $platformIcons = ['qq', 'wx', 'alipay', 'sina', 'baidu', 'douyin', 'huawei', 'google', 'microsoft', 'wework', 'dingtalk', 'feishu', 'gitee', 'github', 'xiaomi', 'bilibili'];
        
        if (!empty($platforms)):
            foreach ($platforms as $platform):
                if (!in_array($platform['name'], $platformIcons)) continue; // 跳过未配置图标的平台
        ?>
        <div class="provider-icon" title="<?= htmlspecialchars($platform['platform']) ?>">
            <img src="/assets/icon/<?= e($platform['name']) ?>.svg" alt="<?= htmlspecialchars($platform['platform']) ?>" style="width: 2rem; height: 2rem;">
        </div>
        <?php 
            endforeach;
        else:
        ?>
        <p style="color: var(--color-text-muted); font-size: 0.875rem;">暂无启用的登录平台</p>
        <?php endif; ?>
    </div>
</section>

<!-- Feature Cards -->
<section class="features-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">为什么选择我们</h2>
            <p class="section-description">专为开发者设计，让集成变得简单高效</p>
        </div>
        
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <span class="iconify" data-icon="tabler:code"></span>
                </div>
                <h3 class="feature-title">统一 API</h3>
                <p class="feature-description">一套标准化API接口覆盖所有平台，无需为每个平台编写不同的对接代码，显著降低开发成本。</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <span class="iconify" data-icon="tabler:bolt"></span>
                </div>
                <h3 class="feature-title">极速响应</h3>
                <p class="feature-description">全球边缘节点部署，智能缓存策略，平均响应时间小于100ms，确保用户体验流畅。</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <span class="iconify" data-icon="tabler:shield-check"></span>
                </div>
                <h3 class="feature-title">企业级安全</h3>
                <p class="feature-description">端到端加密传输，多重身份验证，完善的审计日志，满足企业合规要求。</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <span class="iconify" data-icon="tabler:key"></span>
                </div>
                <h3 class="feature-title">令牌管理</h3>
                <p class="feature-description">自动刷新访问令牌，安全存储凭证，完整的生命周期管理，让您无需担心Token过期。</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <span class="iconify" data-icon="tabler:headset"></span>
                </div>
                <h3 class="feature-title">技术支持</h3>
                <p class="feature-description">专业技术团队7×24小时在线支持，快速响应问题，提供一对一接入指导服务。</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <span class="iconify" data-icon="tabler:chart-line"></span>
                </div>
                <h3 class="feature-title">数据统计</h3>
                <p class="feature-description">实时监控登录数据，多维度统计分析，可视化报表展示，助力业务决策优化。</p>
            </div>
        </div>
    </div>
</section>

<!-- Code Preview -->
<section class="code-section">
    <div class="code-section-inner">
        <div class="section-header">
            <h2 class="section-title">简单集成</h2>
            <p class="section-description">几行代码即可完成接入，开发者友好的SDK</p>
        </div>
        
        <div class="code-card">
            <div class="code-card-header">
                <span class="iconify" data-icon="tabler:terminal"></span>
                <span>快速开始</span>
            </div>
            <div class="code-card-body">
                <pre><code><span class="code-comment">// 配置OAuth客户端</span>
<span class="code-keyword">$config</span> = [
    <span class="code-string">'apiurl'</span>  => <span class="code-string">'https://oauth.example.com/'</span>,
    <span class="code-string">'appid'</span>   => <span class="code-variable">$_ENV</span>[<span class="code-string">'OAUTH_APP_ID'</span>],
    <span class="code-string">'appkey'</span>  => <span class="code-variable">$_ENV</span>[<span class="code-string">'OAUTH_APP_KEY'</span>],
    <span class="code-string">'callback'</span> => <span class="code-string">'https://yoursite.com/callback'</span>
];

<span class="code-comment">// 发起登录请求 - 支持 qq/wechat/alipay/github 等</span>
<span class="code-keyword">$oauth</span> = <span class="code-keyword">new</span> <span class="code-function">Oauth</span>(<span class="code-keyword">$config</span>);
<span class="code-keyword">$result</span> = <span class="code-keyword">$oauth</span>-><span class="code-function">login</span>(<span class="code-string">'qq'</span>);

<span class="code-keyword">if</span> (<span class="code-keyword">$result</span>[<span class="code-string">'code'</span>] == <span class="code-variable">0</span>) {
    <span class="code-function">header</span>(<span class="code-string">'Location: '</span> . <span class="code-keyword">$result</span>[<span class="code-string">'url'</span>]);
}

<span class="code-comment">// 回调处理 - 获取用户信息</span>
<span class="code-keyword">$userInfo</span> = <span class="code-keyword">$oauth</span>-><span class="code-function">callback</span>(<span class="code-variable">$_GET</span>[<span class="code-string">'code'</span>]);
<span class="code-comment">// 返回: social_uid, nickname, faceimg, gender</span></code></pre>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="stats-section">
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">100万+</div>
            <div class="stat-label">月API调用</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">50+</div>
            <div class="stat-label">OAuth平台</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">99.9%</div>
            <div class="stat-label">服务可用性</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">5000+</div>
            <div class="stat-label">开发者用户</div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <h2 class="section-title">准备好开始了吗？</h2>
    <p class="section-description">免费注册，立即体验企业级OAuth聚合登录服务</p>
    <div class="cta-buttons">
        <a href="/user/reg" class="btn btn-primary" style="padding: 0.875rem 2.5rem; font-size: 1rem;">
            免费注册
            <span class="iconify" data-icon="tabler:arrow-right"></span>
        </a>
        <a href="/document" class="btn btn-outline" style="padding: 0.875rem 2.5rem; font-size: 1rem;">
            接入文档
        </a>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const heroSection = document.getElementById('hero-section');
    const gridBg = heroSection.querySelector('.hero-grid-bg svg');
    
    if (heroSection && gridBg) {
        const mouseGradient = document.createElementNS('http://www.w3.org/2000/svg', 'radialGradient');
        mouseGradient.setAttribute('id', 'mouseGlow');
        mouseGradient.innerHTML = `
            <stop offset="0%" stop-color="var(--color-primary)" stop-opacity="0.15"/>
            <stop offset="100%" stop-color="transparent"/>
        `;
        
        const defs = gridBg.querySelector('defs') || document.createElementNS('http://www.w3.org/2000/svg', 'defs');
        defs.appendChild(mouseGradient);
        
        const mouseCircle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
        mouseCircle.setAttribute('r', '150');
        mouseCircle.setAttribute('fill', 'url(#mouseGlow)');
        mouseCircle.style.pointerEvents = 'none';
        mouseCircle.style.transition = 'cx 0.1s ease-out, cy 0.1s ease-out';
        gridBg.appendChild(mouseCircle);
        
        heroSection.addEventListener('mousemove', function(e) {
            const rect = heroSection.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            const scaleX = 1220 / rect.width;
            const scaleY = 810 / rect.height;
            
            mouseCircle.setAttribute('cx', x * scaleX);
            mouseCircle.setAttribute('cy', y * scaleY);
        });
        
        heroSection.addEventListener('mouseleave', function() {
            mouseCircle.setAttribute('cx', '-200');
            mouseCircle.setAttribute('cy', '-200');
        });
    }
});
</script>

<?php $content = ob_get_clean(); ?>
<?php include ML_ROOT . '/views/layouts/main.php'; ?>
