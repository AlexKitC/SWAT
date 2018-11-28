<?php
namespace application\controller;
class Index{

    public function index($params){
        echo json_encode(['status'=>1,'msg'=>'index page!','_GET'=>$params]);
    }
}