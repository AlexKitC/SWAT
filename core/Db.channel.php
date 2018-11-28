<?php
/**
 * 数据库连接池协程方式
 * User: user
 * Date: 2018/11/28
 * Time: 14:30
 */
namespace Swoole\Coroutine;
class MysqlPoolCoroutine extends \Swoole\Coroutine\AbstractPool
{
    protected $dbConfig = array(
        'host' => 'localhost',
        'port' => 3306,
        'user' => 'root',
        'password' => 'root',
        'database' => 'blog',
        'charset' => 'utf8',
        'timeout' => 10,
    );
    public static $instance;

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new MysqlPoolCoroutine();
        }
        return self::$instance;
    }

    protected function createDb()
    {
        $db = new Mysql();
        $db->connect(
            $this->dbConfig
        );
        return $db;
    }
}
