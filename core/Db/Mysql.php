<?php
namespace Core\Db;
use \Swoole\Coroutine as Co;
use \Swoole\Coroutine\MySQL as CoMysql;
use \Swoole\Coroutine\Channel;
use \Core\Framework\Context;
class Mysql
{
    public static $instance;
    private $min;
    private $max;
    private $currentConnsCount;
    private $conns;
    protected $freeTime;

    private function __clone() {}
    private function __construct()
    {
        $this->min = Context::getConf('mysql')['min'];
        $this->max = Context::getConf('mysql')['max'];
        $this->freeTime = Context::getConf('mysql')['freeTime'];
        $this->conns = new Channel($this->max);
    }

    public static function getInstance()
    {
        if(!self::$instance) {
            return new self();
        }
        return self::$instance;
    }

    private static function createConn()
    {
        $conn = new CoMysql();
        $conn->connect([
            'host' => Context::getConf('mysql')['host'],
            'port' => Context::getConf('mysql')['port'],
            'user' => Context::getConf('mysql')['user'],
            'password' => Context::getConf('mysql')['pass'],
            'database' => Context::getConf('mysql')['database'],
            'timeout'  => Context::getConf('mysql')['timeout'],
        ]);
        return $conn;
    }

    /**
     * 创建连接对象
     */
    private static function createConnObj()
    {
        $conn = self::createConn();
        return $conn ? ['lastUseTime'=>time(),'conn'=>$conn] : null;
    }

    /**
     * 初始化
     */
    public function init()
    {
        for($i=0;$i<$this->min;$i++) {
            $obj = self::createConnObj();
            $this->currentConnsCount++;
            $this->conns->push($obj);
        }
        return $this;
    }

    /**
     * 获取连接
     */
    public function getConn(int $timeout = 3)
    {
        if($this->conns->isEmpty()) {
            echo 'empty!';
            if($this->currentConnsCount < $this->max) {
                $obj = self::createConnObj();
                $this->currentConnsCount++;
            } else {
                $obj = $this->conns->pop($timeout);
            }
        } else {
            echo 'not empty';
            $obj = $this->conns->pop($timeout);
        }
        //保存当前连接实例到当前协程下的资源上下文，以便于Controller父类析构自动回收mysql连接资源
        $resource = $obj['conn']->connected ? $obj['conn'] : $this->getConn();
        return $resource;
    }

    /**
     * 归还连接入池
     * @param $conn 
     */
    public function returnConn($conn)
    {
        if($conn->connected) {
            $this->conns->push(['lastUseTime'=>time(),'conn'=>$conn]);
            echo 'return conn success';
        }
    }

    /**
     * 定时连接维护
     */
    public function keepConns()
    {
        swoole_timer_tick(30*60000, function() {//半个小时检测一次
            if($this->conns->length() > $this->min) {//不回收
                return;
            }

            while(true) {
                if($this->conns->isEmpty()) {//池子一个不剩，说明比较繁忙
                    break;
                }
                $connObj = $this->conns->pop(0.01);
                //当前连接数大于最小值，并且池子可用的大于最小值,且半小时没用过的 可以回收
                if($this->currentConnsCount > $this->min && $this->conns->length() > $this->min && (time() - $connObj['lastUseTime']) > $this->freeTime) {
                    $connObj['conn']->close();
                    $this->currentConnsCount--;
                } else {
                    $this->conns->push($connObj);
                }
            }
        });
    }
}