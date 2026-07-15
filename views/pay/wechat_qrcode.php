<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>微信支付</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .pay-card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 2rem;
            max-width: 400px;
            width: 100%;
            text-align: center;
        }
        .pay-logo { width: 60px; height: 60px; margin-bottom: 1rem; }
        .pay-title { font-size: 1.25rem; font-weight: 600; color: #333; margin-bottom: 0.5rem; }
        .pay-amount { font-size: 2rem; font-weight: 700; color: #07c160; margin-bottom: 1.5rem; }
        .qrcode-box { background: #f5f5f5; border-radius: 0.75rem; padding: 1.5rem; margin-bottom: 1.5rem; }
        .qrcode-box img { max-width: 200px; width: 100%; height: auto; }
        .pay-tip { font-size: 0.875rem; color: #666; margin-bottom: 1rem; }
        .pay-status { font-size: 0.875rem; color: #999; padding: 0.75rem; background: #f9f9f9; border-radius: 0.5rem; }
        .pay-status.success { background: #e6f7e6; color: #07c160; }
        .btn { display: inline-block; padding: 0.75rem 1.5rem; background: #07c160; color: #fff; border: none; border-radius: 0.5rem; font-size: 1rem; cursor: pointer; text-decoration: none; margin-top: 1rem; }
        .btn:hover { background: #06ad56; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
        .loading { animation: pulse 1.5s infinite; }
    </style>
</head>
<body>
    <div class="pay-card">
        <img src="/assets/icon/wx.svg" alt="微信支付" class="pay-logo">
        <h1 class="pay-title">微信扫码支付</h1>
        <div class="pay-amount">¥<?= number_format($order['amount'], 2) ?></div>
        <div class="qrcode-box">
            <img id="qrcode" src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?= urlencode($codeUrl) ?>" alt="支付二维码">
        </div>
        <p class="pay-tip">请使用微信扫描二维码完成支付</p>
        <div id="payStatus" class="pay-status loading">正在等待支付...</div>
        <a href="/user/orders" class="btn" id="returnBtn" style="display: none;">返回订单列表</a>
    </div>
    <script>
        var orderNo = '<?= e($orderNo) ?>';
        var checkInterval = setInterval(function() {
            fetch('/pay/check-status?order_no=' + orderNo).then(function(res) { return res.json(); }).then(function(data) {
                if (data.code === 0 && data.data.paid) {
                    clearInterval(checkInterval);
                    document.getElementById('payStatus').className = 'pay-status success';
                    document.getElementById('payStatus').textContent = '支付成功！';
                    document.getElementById('returnBtn').style.display = 'inline-block';
                    setTimeout(function() { window.location.href = '/user/orders?msg=支付成功'; }, 3000);
                }
            });
        }, 3000);
        setTimeout(function() {
            clearInterval(checkInterval);
            document.getElementById('payStatus').className = 'pay-status';
            document.getElementById('payStatus').textContent = '二维码已过期，请重新下单';
            document.getElementById('returnBtn').style.display = 'inline-block';
        }, 5 * 60 * 1000);
    </script>
</body>
</html>
