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
    protected $count;//当前的连接数
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
        if(is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function createConn()
    {
        $conn = new CoMysql();
        $res = $conn->connect([
            'host' => Context::getConf('mysql')['host'],
            'port' => Context::getConf('mysql')['port'],
            'user' => Context::getConf('mysql')['user'],
            'password' => Context::getConf('mysql')['pass'],
            'database' => Context::getConf('mysql')['database'],
            'timeout'  => Context::getConf('mysql')['timeout'],
        ]);
        if ($res === false) {
            throw new \Exception("Failed to connect mysql server!so create connection faild");
        }
        return $conn;
    }

    /**
     * 创建连接对象
     */
    protected function createConnObj()
    {
        $conn = $this->createConn();
        return $conn ? ['lastUseTime'=>time(),'conn'=>$conn] : null;
    }

    /**
     * 初始化
     */
    public function init()
    {
        if($this->max%Context::getConf('worker_num') !== 0) {
            echo 'mysql连接池允许的最大值 max 必须为worker_num的整数倍，您当前的worker_num值为：'.Context::getConf('worker_num')."\r\n";
            return false;
        }

        for($i=0;$i<$this->min;$i++) {
            $obj = $this->createConnObj();
            $this->conns->push($obj);
            $this->count++;
        }
        return $this;
    }

    /**
     * 获取连接
     */
    public function getConn()
    {
        $obj = null;
        if($this->conns->isEmpty()) {
            if($this->count < intval(($this->max)/(Context::getConf('worker_num') ?? 4))) {
                $obj = $this->createConnObj();
                $this->count++;
            } else {
                $obj = $this->conns->pop(1);
            }
        } else {
            $obj = $this->conns->pop(1);
        }
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
        }
    }

    /**
     * 定时连接维护
     */
    public function keepConns()
    {
        \Swoole\Timer::tick(30*60000, function() {//半个小时检测一次
            if($this->conns->length() > $this->min) {//不回收
                return;
            }

            while(true) {
                if($this->conns->isEmpty()) {//池子一个不剩，说明比较繁忙,暂不回收
                    break;
                }
                $connObj = $this->conns->pop(0.01);
                //当前连接数大于最小值，并且池子可用的大于最小值,且半小时没用过的 可以回收
                if($this->count++ > $this->min && $this->count > $this->min && (time() - $connObj['lastUseTime']) > $this->freeTime) {
                    $connObj['conn']->close();
                    $this->count--;
                } else {
                    $this->conns->push($connObj);
                }
            }
        });
    }
}