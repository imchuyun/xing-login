
    <!-- JS -->
    <script src="/assets/js/main.js"></script>
    <script>
        function ajax(url, data, callback) {
            var formData;
            if (data instanceof FormData) {
                formData = data;
            } else {
                formData = new FormData();
                for (var key in data) {
                    if (data.hasOwnProperty(key)) {
                        // 处理数组类型的值
                        if (Array.isArray(data[key])) {
                            data[key].forEach(function(item) {
                                formData.append(key + '[]', item);
                            });
                        } else {
                            formData.append(key, data[key]);
                        }
                    }
                }
            }
            
            fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(function(response) { return response.json(); })
            .then(function(result) { callback(result); })
            .catch(function(error) {
                console.error('AJAX Error:', error);
                callback({ code: 1, message: '请求失败，请重试' });
            });
        }
        function formToData(form) {
            var formData = new FormData(form);
            return formData;
        }
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

        // 自定义确认弹窗
        function showConfirm(message, onConfirm, onCancel) {
            const overlay = document.createElement('div');
            overlay.style.cssText = `
                position: fixed; top: 0; left: 0; right: 0; bottom: 0;
                background: rgba(0, 0, 0, 0.5); z-index: 9998;
                display: flex; align-items: center; justify-content: center;
                animation: fadeIn 0.2s ease-out;
            `;

            const modal = document.createElement('div');
            modal.style.cssText = `
                background: var(--bg-surface); border-radius: var(--radius-lg);
                padding: 1.5rem; max-width: 400px; width: 90%;
                box-shadow: var(--shadow-xl); animation: scaleIn 0.2s ease-out;
            `;

            modal.innerHTML = `
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                    <span class="iconify" data-icon="tabler:alert-circle" style="font-size: 1.5rem; color: var(--color-warning);"></span>
                    <h3 style="font-size: 1rem; font-weight: 600; margin: 0;">确认操作</h3>
                </div>
                <p style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 1.5rem; line-height: 1.5;">${message}</p>
                <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                    <button class="btn btn-outline" id="confirmCancel">取消</button>
                    <button class="btn btn-primary" style="background: var(--color-error); border-color: var(--color-error);" id="confirmOk">确定</button>
                </div>
            `;

            overlay.appendChild(modal);
            document.body.appendChild(overlay);

            // 添加动画样式
            if (!document.getElementById('confirm-style')) {
                const style = document.createElement('style');
                style.id = 'confirm-style';
                style.textContent = `
                    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
                    @keyframes scaleIn { from { transform: scale(0.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }
                `;
                document.head.appendChild(style);
            }

            const closeModal = () => {
                overlay.style.opacity = '0';
                overlay.style.transition = 'opacity 0.2s ease';
                setTimeout(() => overlay.remove(), 200);
            };

            modal.querySelector('#confirmCancel').onclick = () => {
                closeModal();
                if (onCancel) onCancel();
            };

            modal.querySelector('#confirmOk').onclick = () => {
                closeModal();
                if (onConfirm) onConfirm();
            };

            overlay.onclick = (e) => {
                if (e.target === overlay) {
                    closeModal();
                    if (onCancel) onCancel();
                }
            };
        }

    </script>
</body>

</html>
