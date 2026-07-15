<?php
namespace Core;

/**
 * SMTP邮件发送类
 */
class Mailer
{
    private $host;
    private $port;
    private $username;
    private $password;
    private $encryption;
    private $fromEmail;
    private $fromName;
    private $siteName;
    private $socket;
    private $debug = false;

    public function __construct($config = [])
    {
        $this->host = $config['host'] ?? '';
        $this->port = $config['port'] ?? 465;
        $this->username = $config['username'] ?? '';
        $this->password = $config['password'] ?? '';
        $this->encryption = $config['encryption'] ?? 'ssl';
        $this->fromEmail = $config['from_email'] ?? $this->username;
        $this->fromName = $config['from_name'] ?? 'Max Login';
        $this->siteName = $config['site_name'] ?? $this->fromName;
    }

    /**
     * 发送邮件
     */
    public function send($to, $subject, $body, $isHtml = true)
    {
        if (empty($this->host) || empty($this->username)) {
            return ['success' => false, 'message' => '邮件服务未配置'];
        }

        try {
            $this->connect();
            $this->authenticate();
            $this->sendMail($to, $subject, $body, $isHtml);
            $this->disconnect();
            return ['success' => true, 'message' => '发送成功'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function connect()
    {
        $protocol = $this->encryption === 'ssl' ? 'ssl://' : '';
        $this->socket = @fsockopen($protocol . $this->host, $this->port, $errno, $errstr, 30);
        
        if (!$this->socket) {
            throw new \Exception("连接邮件服务器失败: {$errstr}");
        }
        
        $this->getResponse();
        $this->sendCommand("EHLO " . gethostname());
        
        if ($this->encryption === 'tls') {
            $this->sendCommand("STARTTLS");
            stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            $this->sendCommand("EHLO " . gethostname());
        }
    }

    private function authenticate()
    {
        $this->sendCommand("AUTH LOGIN");
        $this->sendCommand(base64_encode($this->username));
        $this->sendCommand(base64_encode($this->password));
    }

    private function sendMail($to, $subject, $body, $isHtml)
    {
        $this->sendCommand("MAIL FROM:<{$this->fromEmail}>");
        $this->sendCommand("RCPT TO:<{$to}>");
        $this->sendCommand("DATA");

        $headers = "From: {$this->fromName} <{$this->fromEmail}>\r\n";
        $headers .= "To: <{$to}>\r\n";
        $headers .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: " . ($isHtml ? "text/html" : "text/plain") . "; charset=UTF-8\r\n";
        $headers .= "Date: " . date("r") . "\r\n";
        $headers .= "\r\n";
        $headers .= $body . "\r\n";
        $headers .= ".";

        $this->sendCommand($headers);
    }

    private function disconnect()
    {
        $this->sendCommand("QUIT");
        fclose($this->socket);
    }

    private function sendCommand($command)
    {
        fwrite($this->socket, $command . "\r\n");
        return $this->getResponse();
    }

    private function getResponse()
    {
        $response = '';
        while ($line = fgets($this->socket, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) == ' ') break;
        }
        return $response;
    }

    /**
     * 发送验证码邮件
     */
    public function sendCode($to, $code, $type = 'register')
    {
        $types = [
            'register' => '注册验证',
            'login' => '登录验证',
            'reset' => '密码重置',
        ];
        $typeName = $types[$type] ?? '验证';

        $subject = "【{$this->siteName}】{$typeName}验证码";
        $body = <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; padding: 20px;">
    <div style="max-width: 500px; margin: 0 auto; background: #f9f9f9; padding: 30px; border-radius: 10px;">
        <h2 style="color: #333; margin-bottom: 20px;">{$typeName}验证码</h2>
        <p style="color: #666; margin-bottom: 20px;">您的验证码是：</p>
        <div style="background: #6366f1; color: white; font-size: 32px; font-weight: bold; text-align: center; padding: 20px; border-radius: 8px; letter-spacing: 8px;">
            {$code}
        </div>
        <p style="color: #999; margin-top: 20px; font-size: 14px;">验证码有效期为5分钟，请勿泄露给他人。</p>
        <p style="color: #999; font-size: 12px; margin-top: 30px;">如非本人操作，请忽略此邮件。</p>
    </div>
</body>
</html>
HTML;

        return $this->send($to, $subject, $body);
    }
}
