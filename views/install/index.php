<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? '系统安装') ?> - 星聚合登录</title>
    <link rel="icon" href="/assets/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="/assets/css/app.css">
    <script src="/assets/js/iconify.min.js"></script>
    <style>
        :root {
            --sidebar-width: 280px;
            --card-width-env: 720px;
            --card-width-config: 520px;
        }
        
        @keyframes float-1 {
            0%, 100% { transform: scale(1) rotate(0deg); }
            50% { transform: scale(1.2) rotate(90deg); }
        }
        @keyframes float-2 {
            0%, 100% { transform: scale(1.2) rotate(90deg); }
            50% { transform: scale(1) rotate(0deg); }
        }
        .animate-float-1 { animation: float-1 25s ease-in-out infinite; }
        .animate-float-2 { animation: float-2 30s ease-in-out infinite; }
        
        
        .install-container {
            display: flex;
            background: white;
            border-radius: 1.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            max-width: 95vw;
            width: 900px;
            transition: width 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        
        .install-container.step-config {
            width: 1000px;
        }
        
        
        .step-content {
            opacity: 1;
            transform: translateY(0);
            transition: opacity 0.3s ease, transform 0.3s ease;
        }
        .step-content.hidden {
            display: none;
        }
        .step-content.fade-out {
            opacity: 0;
            transform: translateY(-10px);
        }
        .step-content.fade-in {
            animation: fadeInUp 0.4s ease forwards;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(15px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        
        .brand-sidebar {
            width: var(--sidebar-width);
            min-width: var(--sidebar-width);
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 50%, #7c3aed 100%);
            padding: 3rem 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
        }
        .brand-sidebar .logo {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        .brand-sidebar .logo img {
            width: 60px;
            height: 60px;
            object-fit: contain;
        }
        .brand-sidebar h1 {
            font-size: 1.375rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            line-height: 1.4;
        }
        .brand-sidebar p {
            font-size: 0.875rem;
            opacity: 0.85;
        }
        .brand-sidebar .version {
            margin-top: 2rem;
            font-size: 0.75rem;
            opacity: 0.6;
        }
        
        
        .content-area {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
        }
        
        
        .step-tabs {
            display: flex;
            border-bottom: 1px solid #e5e7eb;
            background: #fafafa;
        }
        .step-tab {
            flex: 1;
            padding: 1rem 1.5rem;
            text-align: center;
            font-weight: 500;
            color: #9ca3af;
            position: relative;
            transition: all 0.3s ease;
            border: none;
            background: none;
            cursor: default;
        }
        .step-tab.active { color: #6366f1; }
        .step-tab.completed { color: #22c55e; }
        .step-tab.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(to right, #6366f1, #4f46e5);
        }
        .step-tab.completed::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: #22c55e;
        }
        .step-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.5rem;
            height: 1.5rem;
            border-radius: 50%;
            font-size: 0.8125rem;
            margin-right: 0.5rem;
            background: #d1d5db;
            color: white;
            transition: all 0.3s ease;
        }
        .step-tab.active .step-number {
            background: linear-gradient(to right, #6366f1, #4f46e5);
        }
        .step-tab.completed .step-number {
            background: #22c55e;
        }
        
        
        .step-content {
            flex: 1;
            padding: 1.5rem 2rem;
            overflow-y: auto;
        }
        .step-content.hidden { display: none; }
        
        
        .config-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
        }
        .config-column {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .config-section {
            background: #f9fafb;
            border-radius: 0.75rem;
            padding: 1rem 1.25rem;
            border: 1px solid #e5e7eb;
        }
        .config-section h4 {
            font-size: 0.875rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .form-group {
            margin-bottom: 0.75rem;
        }
        .form-group:last-child { margin-bottom: 0; }
        .form-group label {
            display: block;
            font-size: 0.8125rem;
            font-weight: 500;
            color: #4b5563;
            margin-bottom: 0.375rem;
        }
        .form-group input {
            width: 100%;
            padding: 0.5rem 0.75rem;
            font-size: 0.8125rem;
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            outline: none;
            transition: all 0.2s;
        }
        .form-group input:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
        .form-group .hint {
            font-size: 0.6875rem;
            color: #9ca3af;
            margin-top: 0.25rem;
        }
        
        
        .env-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
        }
        .env-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            border: 1px solid;
        }
        .env-item.passed {
            background: #f0fdf4;
            border-color: #bbf7d0;
        }
        .env-item.failed {
            background: #fef2f2;
            border-color: #fecaca;
        }
        
        
        .loading-overlay {
            position: fixed;
            inset: 0;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(4px);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        .loading-overlay.show { opacity: 1; visibility: visible; }
        .loading-spinner {
            width: 48px;
            height: 48px;
            border: 3px solid #e5e7eb;
            border-top-color: #6366f1;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        
        
        .error-toast {
            position: fixed;
            top: 1rem;
            right: 1rem;
            max-width: 400px;
            padding: 1rem 1.5rem;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 0.75rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            z-index: 10000;
            transform: translateX(120%);
            transition: transform 0.3s ease;
        }
        .error-toast.show { transform: translateX(0); }
        
        .input-error {
            border-color: #ef4444 !important;
            background-color: #fef2f2 !important;
        }
        
        button:disabled { opacity: 0.6; cursor: not-allowed; }
        
        
        @media (max-width: 900px) {
            .install-container {
                flex-direction: column;
                max-width: 600px;
            }
            .brand-sidebar {
                width: 100%;
                min-width: auto;
                padding: 2rem;
                flex-direction: row;
                gap: 1rem;
            }
            .brand-sidebar .logo {
                width: 60px;
                height: 60px;
                margin-bottom: 0;
            }
            .brand-sidebar .logo img {
                width: 45px;
                height: 45px;
            }
            .brand-sidebar h1 { font-size: 1.125rem; margin-bottom: 0.25rem; }
            .brand-sidebar .version { display: none; }
            .config-grid { grid-template-columns: 1fr; }
            .env-grid { grid-template-columns: 1fr; }
            .step-tab .step-text { display: none; }
            .step-number { margin-right: 0; }
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 relative overflow-auto">
    <!-- 加载遮罩 -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="loading-spinner mb-4"></div>
        <p id="loadingText" class="text-gray-600 font-medium">正在处理...</p>
    </div>
    
    <!-- 错误提示 -->
    <div id="errorToast" class="error-toast">
        <div class="flex items-start gap-3">
            <span class="iconify text-red-500 flex-shrink-0 mt-0.5" data-icon="mdi:alert-circle" data-width="20"></span>
            <div>
                <p id="errorTitle" class="font-medium text-red-800 mb-1">操作失败</p>
                <p id="errorMessage" class="text-sm text-red-600"></p>
            </div>
            <button onclick="hideError()" class="text-red-400 hover:text-red-600 ml-2">
                <span class="iconify" data-icon="mdi:close" data-width="18"></span>
            </button>
        </div>
    </div>

    <!-- 中央提示弹窗 -->
    <div id="centerToast" style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); padding: 1rem 2rem; background: rgba(0, 0, 0, 0.75); color: white; border-radius: 0.75rem; font-size: 0.875rem; z-index: 9998; opacity: 0; visibility: hidden; transition: all 0.3s ease; pointer-events: none; display: flex; align-items: center; gap: 0.5rem;">
        <span class="iconify" data-icon="mdi:information" data-width="20"></span>
        <span id="centerToastText">提示信息</span>
    </div>

    <!-- 背景装饰 -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-blue-400/20 to-indigo-500/20 rounded-full blur-3xl animate-float-1"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-gradient-to-br from-purple-400/20 to-pink-500/20 rounded-full blur-3xl animate-float-2"></div>
    </div>

    <div class="relative min-h-screen flex items-center justify-center py-8 px-4">
        <!-- 主容器 -->
        <div class="install-container" id="installContainer">
            <!-- 左侧品牌区域 -->
            <div class="brand-sidebar">
                <div class="logo">
                    <img src="/assets/logo.png" alt="Logo">
                </div>
                <div>
                    <h1>星聚合登录</h1>
                    <p>系统安装向导</p>
                </div>
                <div class="version">1.0.0</div>
            </div>

            <!-- 右侧内容区域 -->
            <div class="content-area">
                <!-- 步骤指示器 -->
                <div class="step-tabs">
                    <button type="button" id="tab-env" class="step-tab active" disabled>
                        <span class="step-number">1</span>
                        <span class="step-text">环境检测</span>
                    </button>
                    <button type="button" id="tab-config" class="step-tab" disabled>
                        <span class="step-number">2</span>
                        <span class="step-text">系统配置</span>
                    </button>
                    <button type="button" id="tab-complete" class="step-tab" disabled>
                        <span class="step-number">3</span>
                        <span class="step-text">安装完成</span>
                    </button>
                </div>

                <!-- 步骤1: 环境检测 -->
                <div id="step-env" class="step-content">
                    <h3 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        <span class="iconify text-blue-500" data-icon="mdi:server-check" data-width="22"></span>
                        服务器环境检测
                    </h3>
                    
                    <div class="env-grid">
                        <?php foreach ($requirements as $req): ?>
                        <div class="env-item <?= $req['passed'] ? 'passed' : 'failed' ?>">
                            <div class="flex items-center gap-2">
                                <span class="iconify <?= $req['passed'] ? 'text-green-500' : 'text-red-500' ?>" 
                                      data-icon="<?= $req['passed'] ? 'mdi:check-circle' : 'mdi:close-circle' ?>" data-width="18"></span>
                                <span class="font-medium text-gray-700 text-sm"><?= e($req['name']) ?></span>
                            </div>
                            <span class="text-sm <?= $req['passed'] ? 'text-green-600' : 'text-red-600' ?>"><?= e($req['current']) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if (!$allPassed): ?>
                    <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                        <div class="flex items-start gap-2">
                            <span class="iconify text-amber-500 flex-shrink-0 mt-0.5" data-icon="mdi:alert" data-width="18"></span>
                            <div>
                                <p class="font-medium text-amber-800 text-sm">环境检测未通过</p>
                                <p class="text-xs text-amber-600 mt-0.5">请先解决上述标红的问题，然后刷新页面重新检测。</p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="mt-6 flex justify-end">
                        <?php if ($allPassed): ?>
                        <button type="button" onclick="goToStep('config')" class="px-6 py-2.5 bg-gradient-to-r from-indigo-500 to-indigo-600 text-white rounded-lg font-medium hover:from-indigo-600 hover:to-indigo-700 transition flex items-center gap-2 shadow-lg shadow-indigo-500/25 text-sm">
                            下一步
                            <span class="iconify" data-icon="mdi:arrow-right" data-width="18"></span>
                        </button>
                        <?php else: ?>
                        <button type="button" onclick="location.reload()" class="px-5 py-2.5 border border-gray-300 text-gray-600 rounded-lg font-medium hover:bg-gray-50 transition flex items-center gap-2 text-sm">
                            <span class="iconify" data-icon="mdi:refresh" data-width="18"></span>
                            重新检测
                        </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 步骤2: 系统配置 -->
                <div id="step-config" class="step-content hidden">
                    <form id="installForm" onsubmit="return handleInstall(event)">
                        <div class="config-grid">
                            <!-- 左列 -->
                            <div class="config-column">
                                <div class="config-section">
                                    <h4>
                                        <span class="iconify text-blue-500" data-icon="mdi:web" data-width="18"></span>
                                        站点配置
                                    </h4>
                                    <div class="form-group">
                                        <label>站点名称 <span class="text-red-500">*</span></label>
                                        <input type="text" name="site_name" id="site_name" value="星聚合登录" required>
                                    </div>
                                    <div class="form-group">
                                        <label>站点地址 <span class="text-red-500">*</span></label>
                                        <input type="url" name="site_url" id="site_url" placeholder="https://your-domain.com" required>
                                        <p class="hint">不要以斜杠结尾</p>
                                    </div>
                                </div>
                                <div class="config-section">
                                    <h4>
                                        <span class="iconify text-green-500" data-icon="mdi:database" data-width="18"></span>
                                        数据库配置
                                    </h4>
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                                        <div class="form-group">
                                            <label>主机 <span class="text-red-500">*</span></label>
                                            <input type="text" name="db_host" id="db_host" value="localhost" required>
                                        </div>
                                        <div class="form-group">
                                            <label>端口 <span class="text-red-500">*</span></label>
                                            <input type="number" name="db_port" id="db_port" value="3306" required min="1" max="65535">
                                        </div>
                                    </div>
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                                        <div class="form-group">
                                            <label>数据库名 <span class="text-red-500">*</span></label>
                                            <input type="text" name="db_name" id="db_name" placeholder="数据库名称" required>
                                        </div>
                                        <div class="form-group">
                                            <label>用户名 <span class="text-red-500">*</span></label>
                                            <input type="text" name="db_user" id="db_user" placeholder="用户名" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>密码</label>
                                        <div style="display: flex; gap: 0.5rem;">
                                            <div style="flex: 1; position: relative;">
                                                <input type="password" name="db_pass" id="db_pass" placeholder="数据库密码" style="padding-right: 2rem;">
                                                <button type="button" onclick="togglePassword('db_pass')" style="position: absolute; right: 0.5rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: #9ca3af; cursor: pointer; padding: 0;">
                                                    <span class="iconify" data-icon="mdi:eye-off" data-width="16" id="db_pass_icon"></span>
                                                </button>
                                            </div>
                                            <button type="button" onclick="testDbConnection()" id="testDbBtn" style="padding: 0.5rem 0.75rem; background: #10b981; color: white; border: none; border-radius: 0.375rem; font-size: 0.75rem; cursor: pointer; white-space: nowrap; display: flex; align-items: center; gap: 0.25rem;">
                                                <span class="iconify" data-icon="mdi:connection" data-width="14"></span>
                                                测试
                                            </button>
                                        </div>
                                        <p id="dbTestResult" class="hint hidden"></p>
                                    </div>
                                </div>
                            </div>
                            <!-- 右列 -->
                            <div class="config-column">
                                <div class="config-section">
                                    <h4>
                                        <span class="iconify text-purple-500" data-icon="mdi:account-key" data-width="18"></span>
                                        管理员账户
                                    </h4>
                                    <div class="form-group">
                                        <label>用户名 <span class="text-red-500">*</span></label>
                                        <input type="text" name="admin_user" id="admin_user" value="admin" required minlength="3">
                                    </div>
                                    <div class="form-group">
                                        <label>邮箱 <span class="text-red-500">*</span></label>
                                        <input type="email" name="admin_email" id="admin_email" placeholder="admin@example.com" required>
                                    </div>
                                    <div class="form-group">
                                        <label>密码 <span class="text-red-500">*</span></label>
                                        <div style="position: relative;">
                                            <input type="password" name="admin_pass" id="admin_pass" placeholder="至少6位" required minlength="6" style="padding-right: 2rem;">
                                            <button type="button" onclick="togglePassword('admin_pass')" style="position: absolute; right: 0.5rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: #9ca3af; cursor: pointer; padding: 0;">
                                                <span class="iconify" data-icon="mdi:eye-off" data-width="16" id="admin_pass_icon"></span>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>后台路径 <span class="text-red-500">*</span></label>
                                        <div style="position: relative;">
                                            <span style="position: absolute; left: 0.5rem; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 0.8125rem;">/</span>
                                            <input type="text" name="admin_path" id="admin_path" value="admin" required minlength="2" maxlength="32" style="padding-left: 1.25rem;" oninput="validateAdminPath(this)">
                                        </div>
                                        <p class="hint">字母开头，支持字母、数字、下划线、横线</p>
                                        <p id="adminPathError" class="hint text-red-500 hidden"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-between items-center mt-5 pt-4 border-t border-gray-100">
                            <button type="button" onclick="goToStep('env')" class="px-4 py-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition flex items-center gap-1.5 text-sm">
                                <span class="iconify" data-icon="mdi:arrow-left" data-width="16"></span>
                                上一步
                            </button>
                            <button type="submit" id="installBtn" class="px-6 py-2.5 bg-gradient-to-r from-indigo-500 to-indigo-600 text-white rounded-lg font-medium hover:from-indigo-600 hover:to-indigo-700 transition flex items-center gap-2 shadow-lg shadow-indigo-500/25 text-sm">
                                <span class="iconify" data-icon="mdi:rocket-launch" data-width="18"></span>
                                开始安装
                            </button>
                        </div>
                    </form>
                </div>

                <!-- 步骤3: 安装完成 -->
                <div id="step-complete" class="step-content hidden text-center">
                    <div class="py-6">
                        <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-5">
                            <span class="iconify text-green-500" data-icon="mdi:check-circle" data-width="50"></span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-3">安装成功！</h3>
                        <p class="text-gray-500 mb-6 text-sm">系统已成功安装，您现在可以开始使用了。</p>
                        <div class="flex flex-col sm:flex-row gap-3 justify-center">
                            <a href="/" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 border border-gray-300 text-gray-600 rounded-lg font-medium hover:bg-gray-50 transition text-sm">
                                <span class="iconify" data-icon="mdi:home" data-width="18"></span>
                                访问首页
                            </a>
                            <a id="adminLoginLink" href="/admin/login" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-gradient-to-r from-indigo-500 to-indigo-600 text-white rounded-lg font-medium hover:from-indigo-600 hover:to-indigo-700 transition shadow-lg shadow-indigo-500/25 text-sm">
                                <span class="iconify" data-icon="mdi:login" data-width="18"></span>
                                进入管理后台
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentStep = 'env';
        let isSubmitting = false;
        
        function goToStep(step) {
            if (isSubmitting) return;
            const steps = ['env', 'config', 'complete'];
            const currentIndex = steps.indexOf(step);
            const container = document.getElementById('installContainer');
            const currentContent = document.getElementById('step-' + currentStep);
            const nextContent = document.getElementById('step-' + step);
            currentContent.classList.add('fade-out');
            container.classList.remove('step-config');
            if (step === 'config') {
                container.classList.add('step-config');
            }
            setTimeout(() => {
                currentContent.classList.add('hidden');
                currentContent.classList.remove('fade-out');
                nextContent.classList.remove('hidden');
                nextContent.classList.add('fade-in');
                steps.forEach((s, index) => {
                    const tab = document.getElementById('tab-' + s);
                    tab.classList.remove('active', 'completed');
                    if (index < currentIndex) tab.classList.add('completed');
                    else if (index === currentIndex) tab.classList.add('active');
                });
                
                currentStep = step;
                setTimeout(() => {
                    nextContent.classList.remove('fade-in');
                }, 400);
            }, 200);
        }

        function showLoading(text = '正在处理...') {
            document.getElementById('loadingText').textContent = text;
            document.getElementById('loadingOverlay').classList.add('show');
            isSubmitting = true;
        }
        function hideLoading() {
            document.getElementById('loadingOverlay').classList.remove('show');
            isSubmitting = false;
        }
        function showError(title, message) {
            document.getElementById('errorTitle').textContent = title;
            document.getElementById('errorMessage').textContent = message;
            document.getElementById('errorToast').classList.add('show');
            setTimeout(hideError, 5000);
        }
        function hideError() { document.getElementById('errorToast').classList.remove('show'); }
        
        function showCenterToast(message, duration = 2000) {
            const toast = document.getElementById('centerToast');
            document.getElementById('centerToastText').textContent = message;
            toast.style.opacity = '1';
            toast.style.visibility = 'visible';
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.visibility = 'hidden';
            }, duration);
        }
        
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(inputId + '_icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.setAttribute('data-icon', 'mdi:eye');
            } else {
                input.type = 'password';
                icon.setAttribute('data-icon', 'mdi:eye-off');
            }
        }
        
        function setInputError(inputId) {
            const input = document.getElementById(inputId);
            if (input) { input.classList.add('input-error'); input.focus(); }
        }

        function validateAdminPath(input) {
            const value = input.value.trim();
            const errorEl = document.getElementById('adminPathError');
            const pattern = /^[a-zA-Z][a-zA-Z0-9_-]*$/;
            const reserved = ['user', 'api', 'oauth', 'auth', 'install', 'storage', 'assets', 'public', 'connect', 'return', 'pay', 'document'];
            
            if (value.length < 2) {
                errorEl.textContent = '路径长度至少2个字符';
                errorEl.classList.remove('hidden');
                input.classList.add('input-error');
                return false;
            }
            if (!pattern.test(value)) {
                errorEl.textContent = '格式不正确';
                errorEl.classList.remove('hidden');
                input.classList.add('input-error');
                return false;
            }
            if (reserved.includes(value.toLowerCase())) {
                errorEl.textContent = '该路径为系统保留';
                errorEl.classList.remove('hidden');
                input.classList.add('input-error');
                return false;
            }
            errorEl.classList.add('hidden');
            input.classList.remove('input-error');
            return true;
        }

        async function testDbConnection() {
            if (isSubmitting) return;
            const btn = document.getElementById('testDbBtn');
            const result = document.getElementById('dbTestResult');
            const form = document.getElementById('installForm');
            const formData = new FormData(form);
            
            if (!document.getElementById('db_host').value.trim() || !document.getElementById('db_name').value.trim() || !document.getElementById('db_user').value.trim()) {
                showError('验证失败', '请填写数据库主机、名称和用户名');
                return;
            }
            btn.disabled = true;
            btn.innerHTML = '<span class="iconify animate-spin" data-icon="mdi:loading" data-width="14"></span>';
            try {
                const response = await fetch('/install/test-db', { method: 'POST', body: formData });
                const text = await response.text();
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    result.classList.remove('hidden');
                    result.style.color = '#ef4444';
                    result.textContent = text.length > 100 ? '服务器错误' : (text || '服务器返回了无效的响应');
                    btn.disabled = false;
                    btn.innerHTML = '<span class="iconify" data-icon="mdi:connection" data-width="14"></span> 测试';
                    return;
                }
                result.classList.remove('hidden');
                result.style.color = data.code === 0 ? '#10b981' : '#ef4444';
                result.textContent = data.message || (data.code === 0 ? '连接成功' : '连接失败');
            } catch (e) {
                result.classList.remove('hidden');
                result.style.color = '#ef4444';
                result.textContent = e.message || '网络请求失败';
            }
            btn.disabled = false;
            btn.innerHTML = '<span class="iconify" data-icon="mdi:connection" data-width="14"></span> 测试';
        }

        function validateForm() {
            const errors = [];
            const siteUrl = document.getElementById('site_url').value.trim();
            if (!siteUrl) errors.push({ field: 'site_url', message: '请输入站点地址' });
            else if (!/^https?:\/\/.+/.test(siteUrl)) errors.push({ field: 'site_url', message: '站点地址格式不正确' });
            
            const email = document.getElementById('admin_email').value.trim();
            if (!email) errors.push({ field: 'admin_email', message: '请输入管理员邮箱' });
            else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) errors.push({ field: 'admin_email', message: '邮箱格式不正确' });
            
            const password = document.getElementById('admin_pass').value;
            if (!password) errors.push({ field: 'admin_pass', message: '请输入管理员密码' });
            else if (password.length < 6) errors.push({ field: 'admin_pass', message: '密码长度至少6位' });
            
            if (!document.getElementById('db_host').value.trim()) errors.push({ field: 'db_host', message: '请输入数据库主机' });
            if (!document.getElementById('db_name').value.trim()) errors.push({ field: 'db_name', message: '请输入数据库名称' });
            if (!document.getElementById('db_user').value.trim()) errors.push({ field: 'db_user', message: '请输入数据库用户名' });
            if (!validateAdminPath(document.getElementById('admin_path'))) errors.push({ field: 'admin_path', message: '后台路径格式不正确' });
            
            return errors;
        }

        async function handleInstall(e) {
            e.preventDefault();
            if (isSubmitting) return false;
            document.querySelectorAll('.input-error').forEach(el => el.classList.remove('input-error'));
            const errors = validateForm();
            if (errors.length > 0) {
                setInputError(errors[0].field);
                showError('表单验证失败', errors[0].message);
                return false;
            }
            const btn = document.getElementById('installBtn');
            const form = document.getElementById('installForm');
            const formData = new FormData(form);
            showLoading('正在安装系统，请稍候...');
            btn.disabled = true;
            btn.innerHTML = '<span class="iconify animate-spin" data-icon="mdi:loading" data-width="18"></span> 安装中...';
            try {
                const response = await fetch('/install/do', { method: 'POST', body: formData });
                const text = await response.text();
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    hideLoading();
                    showError('服务器错误', text.substring(0, 200) || '服务器返回了无效的响应');
                    btn.disabled = false;
                    btn.innerHTML = '<span class="iconify" data-icon="mdi:rocket-launch" data-width="18"></span> 开始安装';
                    return false;
                }
                hideLoading();
                if (data.code === 0) {
                    document.getElementById('tab-config').classList.remove('active');
                    document.getElementById('tab-config').classList.add('completed');
                    const adminPath = document.getElementById('admin_path').value.trim() || 'admin';
                    document.getElementById('adminLoginLink').href = '/' + adminPath + '/login';
                    goToStep('complete');
                } else {
                    showError('安装失败', data.message || '请检查配置后重试');
                    btn.disabled = false;
                    btn.innerHTML = '<span class="iconify" data-icon="mdi:rocket-launch" data-width="18"></span> 开始安装';
                }
            } catch (e) {
                hideLoading();
                showError('网络错误', '安装请求失败: ' + e.message);
                btn.disabled = false;
                btn.innerHTML = '<span class="iconify" data-icon="mdi:rocket-launch" data-width="18"></span> 开始安装';
            }
            return false;
        }

        document.addEventListener('DOMContentLoaded', function() {
            const siteUrlInput = document.getElementById('site_url');
            if (siteUrlInput && !siteUrlInput.value) siteUrlInput.value = window.location.origin;
            document.querySelectorAll('input').forEach(input => {
                input.addEventListener('input', function() { 
                    this.classList.remove('input-error');
                });
            });
        });
    </script>
</body>
</html>
