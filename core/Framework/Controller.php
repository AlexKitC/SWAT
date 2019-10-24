<?php
namespace Core\Framework;
use \Swoole\Coroutine as Co;
use \Core\Framework\Context;
use \Core\Db\Mysql;
class Controller
{
    public function methodHandle()
    {

    }

    public function __destruct()
    {
        // $result = Context::freeMysqlContext(Co::getCid());
        // if($result) echo '回收成功';
    }

}