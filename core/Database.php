<?php
namespace Core;

use PDO;
use PDOException;

/**
 * 数据库类
 */
class Database
{
    private static $instance = null;
    private $pdo;
    private $prefix;

    private function __construct()
    {
        $config = config('database');
        $this->prefix = $config['prefix'];

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['name'],
            $config['charset']
        );

        try {
            $this->pdo = new PDO($dsn, $config['user'], $config['pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            throw new \Exception('数据库连接失败: ' . $e->getMessage());
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 获取PDO实例
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * 获取表前缀
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * 查询
     */
    public function query($sql, $params = [])
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * 获取单条记录
     */
    public function fetch($sql, $params = [])
    {
        return $this->query($sql, $params)->fetch();
    }

    /**
     * 获取多条记录
     */
    public function fetchAll($sql, $params = [])
    {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * 插入数据
     */
    public function insert($table, $data)
    {
        // 写操作验证
        $this->verifyWrite();
        
        $table = $this->prefix . $table;
        $columns = implode(', ', array_map(fn($col) => "`{$col}`", array_keys($data)));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $this->query($sql, array_values($data));
        
        return $this->pdo->lastInsertId();
    }
    
    /**
     * 写操作验证
     */
    private function verifyWrite(): void
    {
        // 授权检查已移除
    }

    /**
     * 更新数据
     */
    public function update($table, $data, $where, $params = [])
    {
        $table = $this->prefix . $table;
        $sets = [];
        foreach (array_keys($data) as $column) {
            $sets[] = "`{$column}` = ?";
        }
        $setStr = implode(', ', $sets);
        
        $sql = "UPDATE {$table} SET {$setStr} WHERE {$where}";
        $stmt = $this->query($sql, array_merge(array_values($data), $params));
        
        return $stmt->rowCount();
    }

    /**
     * 删除数据
     */
    public function delete($table, $where, $params = [])
    {
        $table = $this->prefix . $table;
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = $this->query($sql, $params);
        
        return $stmt->rowCount();
    }

    /**
     * 获取最后插入的ID
     */
    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }
}
