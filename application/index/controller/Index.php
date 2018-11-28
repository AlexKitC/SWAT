<?php
namespace Swoole\Coroutine;
class Index{

    public function index($params){
        $db = null;
        $obj = \Swoole\Coroutine\MysqlPoolCoroutine::getInstance()->getConnection();
        if (!empty($obj)) {
            $db = $obj ? $obj['db'] : null;
        }
        if ($db) {
            $ret = $db->query("select * from user limit 1");
            \Swoole\Coroutine\MysqlPoolCoroutine::getInstance()->free($obj);
            echo json_encode(['status'=>1,'msg'=>$ret,'_GET'=>$params]);
        }
        
    }
}