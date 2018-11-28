<?php
require_once __DIR__."/config.php";
foreach($config as $k => $v){
    define($k,$v);
}
const APIROOT = __DIR__;
require_once __DIR__."/core/func.php";
class Http{
    public $http;
    public function __construct(){
        $this -> http = new Swoole\Http\Server("0.0.0.0", 9501);
        $this -> http -> set([
            'daemonize' => 0,
            'worker_num' => 4,
            'backlog' => 128,
            'max_request' => 8000,
            'dispatch_mode'=>1, 
        ]);
        $this -> http -> on('request',function($request,$response){
            $server = $request -> server;
            if($server['path_info'] !== "/favicon.ico"){
                if($server['path_info'] == "/"){
                    $response -> end(json_encode(['status'=>'faild','msg'=>'wrong pathinfo! please use http://ip:port/moudle/controller/action']));
                }else{
                    $response -> end(json_encode(['status'=>'success','msg'=>dealPathInfo($server['path_info'])]));
                }
            }
        });
        $this -> http -> start();
    }
}

new Http();
