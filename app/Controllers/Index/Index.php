<?php
namespace App\Controllers\Index;
use \Core\Framework\Controller;
use \Core\Framework\Context;
use \Core\Db\Mysql;
class Index extends Controller
{
    public function index()
    {
        $conn = Mysql::getInstance()->getConn();
        
            $res = $conn->query("select * from user LIMIT 1");
            Mysql::getInstance()->returnConn($conn);
            // return json_encode($res);
            return 0;
        
        
    }
}