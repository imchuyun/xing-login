<?php
/**
 * 系统更新服务
 * 负责检查更新、下载更新、应用更新
 * 
 * 基于开发者API文档 v2.0.0 实现
 */

namespace App\Services;

class UpdateService
{
    /**
     * 构造函数
     */
    public function __construct()
    {
    }
    
    /**
     * 检查是否有可用更新
     * @return array 更新检查结果
     */
    public function checkForUpdates(): array
    {
        $result = [];
        
        if (!$result['success']) {
            return [
                'success' => false,
                'has_update' => false,
                'message' => $result['message'] ?? '检查更新失败'
            ];
        }
        
        $hasUpdate = $result['updated'] ?? false;
        
        return [
            'success' => true,
            'has_update' => $hasUpdate,
            'current_version' => config('site.version', '1.0.0'),
            'latest_version' => $result['latest_version'] ?? null,
            'title' => $result['title'] ?? null,
            'log' => $result['log'] ?? null,
            'force_update' => $result['force_update'] ?? false,
            'download_code' => $result['download_code'] ?? null
        ];
    }

    /**
     * 获取更新文件列表
     * @param string $downloadCode 下载码
     * @return array 更新文件列表
     */
    public function getUpdateFiles(string $downloadCode): array
    {
        if (empty($downloadCode)) {
            return [
                'success' => false,
                'message' => '下载码不能为空'
            ];
        }
        
        $result = $this->validator->getCodeUpdates($downloadCode);
        
        if (!$result['success']) {
            return [
                'success' => false,
                'message' => $result['message'] ?? '获取更新文件失败'
            ];
        }
        
        return [
            'success' => true,
            'latest_version' => $result['latest_version'] ?? null,
            'files' => $result['files'] ?? [],
            'codes' => $result['codes'] ?? null
        ];
    }
    
    /**
     * 下载并应用单个文件更新
     * @param array $fileInfo 文件信息
     * @return array 操作结果
     */
    public function applyFileUpdate(array $fileInfo): array
    {
        $path = $fileInfo['path'] ?? '';
        $action = $fileInfo['action'] ?? '';
        $url = $fileInfo['url'] ?? '';
        $hash = $fileInfo['hash'] ?? '';
        
        if (empty($path) || empty($action)) {
            return [
                'success' => false,
                'message' => '文件信息不完整'
            ];
        }
        
        $fullPath = ML_ROOT . '/' . $path;
        
        switch ($action) {
            case 'delete':
                return $this->deleteFile($fullPath);
                
            case 'add':
            case 'update':
                if (empty($url)) {
                    return [
                        'success' => false,
                        'message' => '缺少下载URL'
                    ];
                }
                return $this->downloadAndSaveFile($url, $fullPath, $hash);
                
            default:
                return [
                    'success' => false,
                    'message' => '未知的操作类型: ' . $action
                ];
        }
    }

    /**
     * 删除文件
     * @param string $path 文件路径
     * @return array 操作结果
     */
    protected function deleteFile(string $path): array
    {
        if (!file_exists($path)) {
            return [
                'success' => true,
                'message' => '文件不存在，无需删除'
            ];
        }
        
        if (@unlink($path)) {
            return [
                'success' => true,
                'message' => '文件删除成功'
            ];
        }
        
        return [
            'success' => false,
            'message' => '文件删除失败'
        ];
    }
    
    /**
     * 下载并保存文件
     * @param string $url 下载URL
     * @param string $savePath 保存路径
     * @param string $expectedHash 预期哈希值
     * @return array 操作结果
     */
    protected function downloadAndSaveFile(string $url, string $savePath, string $expectedHash = ''): array
    {
        $parsedUrl = parse_url($url);
        parse_str($parsedUrl['query'] ?? '', $queryParams);
        $token = $queryParams['token'] ?? '';
        
        if (empty($token)) {
            return [
                'success' => false,
                'message' => '无效的下载URL'
            ];
        }
        
        $result = $this->validator->downloadFile($token);
        
        if (!$result['success']) {
            return [
                'success' => false,
                'message' => $result['message'] ?? '下载失败'
            ];
        }
        
        $content = $result['content'];
        
        if (!empty($expectedHash)) {
            $actualHash = hash('sha256', $content);
            if ($actualHash !== $expectedHash) {
                return [
                    'success' => false,
                    'message' => '文件哈希验证失败'
                ];
            }
        }
        
        $dir = dirname($savePath);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                return [
                    'success' => false,
                    'message' => '无法创建目录: ' . $dir
                ];
            }
        }
        
        if (file_put_contents($savePath, $content) === false) {
            return [
                'success' => false,
                'message' => '文件保存失败'
            ];
        }
        
        return [
            'success' => true,
            'message' => '文件更新成功'
        ];
    }

    /**
     * 下载完整更新包
     * @param string $downloadCode 下载码
     * @param string $savePath 保存路径（可选）
     * @return array 操作结果
     */
    public function downloadUpdatePackage(string $downloadCode, string $savePath = ''): array
    {
        if (empty($downloadCode)) {
            return [
                'success' => false,
                'message' => '下载码不能为空'
            ];
        }
        
        $result = $this->validator->downloadUpdatePackage($downloadCode);
        
        if (!$result['success']) {
            return [
                'success' => false,
                'message' => $result['message'] ?? '下载失败'
            ];
        }
        
        if (empty($savePath)) {
            $savePath = ML_ROOT . '/storage/cache/update_' . date('YmdHis') . '.zip';
        }
        
        $dir = dirname($savePath);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                return [
                    'success' => false,
                    'message' => '无法创建目录'
                ];
            }
        }
        
        if (file_put_contents($savePath, $result['content']) === false) {
            return [
                'success' => false,
                'message' => '文件保存失败'
            ];
        }
        
        return [
            'success' => true,
            'message' => '更新包下载成功',
            'path' => $savePath,
            'size' => strlen($result['content'])
        ];
    }

    /**
     * 执行完整更新流程
     * @param string $downloadCode 下载码
     * @return array 更新结果
     */
    public function performUpdate(string $downloadCode): array
    {
        $filesResult = $this->getUpdateFiles($downloadCode);
        
        if (!$filesResult['success']) {
            return $filesResult;
        }
        
        $files = $filesResult['files'] ?? [];
        $codes = $filesResult['codes'] ?? null;
        
        if (empty($files) && empty($codes)) {
            return [
                'success' => true,
                'message' => '没有需要更新的内容'
            ];
        }
        
        $results = [];
        $successCount = 0;
        $failCount = 0;
        
        foreach ($files as $fileInfo) {
            $result = $this->applyFileUpdate($fileInfo);
            $results[] = [
                'path' => $fileInfo['path'] ?? '',
                'action' => $fileInfo['action'] ?? '',
                'success' => $result['success'],
                'message' => $result['message']
            ];
            
            if ($result['success']) {
                $successCount++;
            } else {
                $failCount++;
            }
        }
        
        $codesResult = null;
        if (!empty($codes) && $failCount === 0) {
            $codesResult = $this->executeVersionCodes($codes);
            if (!$codesResult['success']) {
                $failCount++;
            }
        }
        
        $latestVersion = $filesResult['latest_version'] ?? null;
        if ($failCount === 0 && !empty($latestVersion)) {
            $this->updateLocalVersion($latestVersion);
        }
        
        $this->validator->clearCache();
        
        return [
            'success' => $failCount === 0,
            'message' => sprintf('更新完成：成功 %d 个，失败 %d 个', $successCount, $failCount),
            'latest_version' => $latestVersion,
            'details' => $results,
            'codes_result' => $codesResult
        ];
    }

    /**
     * 执行版本指令
     * @param array $codes 版本指令信息
     * @return array 执行结果
     */
    protected function executeVersionCodes(array $codes): array
    {
        $type = $codes['type'] ?? '';
        $code = $codes['code'] ?? '';
        
        if (empty($code)) {
            return [
                'success' => true,
                'message' => '无指令需要执行'
            ];
        }
        
        $results = [];
        
        switch ($type) {
            case 'sql':
                $results['sql'] = $this->executeSqlCode($code);
                break;
                
            case 'url':
                $results['url'] = $this->executeUrlCallback($code);
                break;
                
            case 'mixed':
                $results = $this->executeMixedCodes($code);
                break;
                
            default:
                return [
                    'success' => false,
                    'message' => '未知的指令类型: ' . $type
                ];
        }
        
        $allSuccess = true;
        $messages = [];
        foreach ($results as $key => $result) {
            if (!$result['success']) {
                $allSuccess = false;
            }
            $messages[] = "[{$key}] " . $result['message'];
        }
        
        return [
            'success' => $allSuccess,
            'message' => implode('; ', $messages),
            'details' => $results
        ];
    }

    /**
     * 执行SQL指令
     * @param string $sql SQL语句
     * @return array 执行结果
     */
    protected function executeSqlCode(string $sql): array
    {
        try {
            $db = \Core\Database::getInstance();
            $pdo = $db->getPdo();
            $prefix = $db->getPrefix();
            
            $sql = str_replace('{prefix}', $prefix, $sql);
            $sql = str_replace('{{prefix}}', $prefix, $sql);
            
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            $executedCount = 0;
            
            foreach ($statements as $statement) {
                if (!empty($statement)) {
                    $pdo->exec($statement);
                    $executedCount++;
                }
            }
            
            return [
                'success' => true,
                'message' => "SQL执行成功，共执行 {$executedCount} 条语句"
            ];
        } catch (\PDOException $e) {
            return [
                'success' => false,
                'message' => 'SQL执行失败: ' . $e->getMessage()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'SQL执行异常: ' . $e->getMessage()
            ];
        }
    }

    /**
     * 执行URL回调
     * @param string $url 回调URL
     * @return array 执行结果
     */
    protected function executeUrlCallback(string $url): array
    {
        try {
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                return [
                    'success' => false,
                    'message' => '无效的URL格式'
                ];
            }
            
            $scheme = parse_url($url, PHP_URL_SCHEME);
            if (!in_array(strtolower($scheme), ['http', 'https'])) {
                return [
                    'success' => false,
                    'message' => '不支持的URL协议'
                ];
            }
            
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'timeout' => 30,
                    'ignore_errors' => true
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false
                ]
            ]);
            
            $response = @file_get_contents($url, false, $context);
            
            if ($response === false) {
                return [
                    'success' => false,
                    'message' => 'URL请求失败'
                ];
            }
            
            $jsonResponse = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($jsonResponse['success'])) {
                return [
                    'success' => (bool)$jsonResponse['success'],
                    'message' => $jsonResponse['message'] ?? 'URL回调完成',
                    'response' => $jsonResponse
                ];
            }
            
            return [
                'success' => true,
                'message' => 'URL回调完成'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'URL回调异常: ' . $e->getMessage()
            ];
        }
    }

    /**
     * 执行混合类型指令
     * @param string $code 混合指令内容
     * @return array 执行结果
     */
    protected function executeMixedCodes(string $code): array
    {
        $results = [];
        $lines = array_filter(array_map('trim', explode("\n", $code)));
        $sqlStatements = [];
        
        foreach ($lines as $line) {
            if (preg_match('/^https?:\/\//i', $line)) {
                if (!empty($sqlStatements)) {
                    $sql = implode(";\n", $sqlStatements);
                    $results['sql_' . count($results)] = $this->executeSqlCode($sql);
                    $sqlStatements = [];
                }
                $results['url_' . count($results)] = $this->executeUrlCallback($line);
            } else {
                $sqlStatements[] = rtrim($line, ';');
            }
        }
        
        if (!empty($sqlStatements)) {
            $sql = implode(";\n", $sqlStatements);
            $results['sql_' . count($results)] = $this->executeSqlCode($sql);
        }
        
        return $results;
    }
    
    /**
     * 更新本地配置文件中的版本号
     * @param string $newVersion 新版本号
     * @return bool 是否更新成功
     */
    protected function updateLocalVersion(string $newVersion): bool
    {
        $configPath = ML_ROOT . '/config/config.php';
        
        if (!file_exists($configPath)) {
            return false;
        }
        
        $content = file_get_contents($configPath);
        if ($content === false) {
            return false;
        }
        
        $content = preg_replace(
            "/('version'\s*=>\s*')([^']*)(',\s*\n\s*\],\s*\n\s*'database')/",
            "\${1}{$newVersion}\${3}",
            $content
        );
        
        $content = preg_replace(
            "/('version'\s*=>\s*')([^']*)(',\s*\n\s*'license')/",
            "\${1}{$newVersion}\${3}",
            $content
        );
        
        return file_put_contents($configPath, $content) !== false;
    }
}
