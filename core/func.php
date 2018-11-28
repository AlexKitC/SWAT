<?php

if(!function_exists("dump")){
    function dump($param){
        echo "<pre>";
        var_dump($param);
        echo "</pre>";
    }
}

if(!function_exists("dealPathInfo")){
    function dealPathInfo($pathinfo){
        $is_moudle = false;
        $is_controller = false;
        $is_action = false;
        $controllerObjs = [];
        $pathInfoArr = array_values(array_filter(explode("/",$pathinfo)));
        $pathInfo = null;
        if(count($pathInfoArr) == 1){
            $pathInfo = $pathInfoArr['0']."/".$this -> APP_CONTROLLER."/".$this -> APP_ACTION;
        }elseif(count($pathInfoArr) == 2){
            $pathInfo = $pathInfoArr['0']."/".$pathInfoArr['1']."/".$this -> APP_ACTION;
        }elseif(count($pathInfoArr) == 3){
            $pathInfo = $pathInfoArr['0']."/".$pathInfoArr['1']."/".$pathInfoArr['2'];
        }else{
            $pathInfo = $pathinfo;
        }
        if (($pathInfo !== '/') && count($pathInfoArr) > 2){
            $urlArray = array_values(array_filter(explode("/",$pathInfo)));
            // 获取模块名
            $moudle = empty($urlArray[0]) ? APP_MOUDLE : $urlArray[0];
            if(!is_dir(APIROOT.'/application/'.$moudle)){
                $is_moudle = false;
            }else{
                $is_moudle = true;
            }
            //检测当前模块
            if($is_moudle){//若存在再检测控制器
                // 获取控制器名
                $controllerName = ucfirst(empty($urlArray[1]) ? $this -> app_controller : $urlArray[1]);
                $controller = $controllerName;
                if(file_exists(APIROOT.'/application/'.$moudle.'/controller/'.$controller.'.php')){
                    $is_controller = true;
                }else{
                    $is_controller = false;
                }
                if($is_controller){//若存在控制器则实例化该控制器类
                    require_once(APIROOT.'/application/'.$moudle.'/controller/'.$controller.'.php');
                    $ControllerObjStr = 'Swoole\Coroutine\\'.$controller;
                    $Controller = "";
                    if(!in_array($ControllerObjStr,$controllerObjs)){
                        $Controller = new $ControllerObjStr();
                        $controllerObjs["$ControllerObjStr"] = $Controller;
                    }else{
                        $Controller = $ControllerObjStr;
                    }
                    //执行对应方法
                    $action = empty($urlArray[2]) ? $this -> app_action : $urlArray[2];
                    if(!method_exists($Controller,$action)){
                        $is_action = false;
                    }else{
                        $is_action = true;
                    }
                    if($is_action){//检测方法是否存在
                        //解析参数
                        $paramsArr = [];
                        foreach($urlArray as $k =>$v){
                            if($k !==0 && $k !== 1 && $k !==2){
                                array_push($paramsArr,$v);
                            }
                        }
                        //重组键值对
                        if(!empty($paramsArr)){//有参数
                            if(count($paramsArr)%2 == 0){//键值对为偶数参数正常
                                $params = [];
                                foreach($paramsArr as $k => $v){
                                    if($k%2 == 0){
                                        $params[$paramsArr[$k]] = null;
                                    }elseif($k%2 == 1){
                                        $params[$paramsArr[$k-1]] = $paramsArr[$k];
                                    }
                                }
                                ob_start();
                                $Controller -> $action($params);
                                return ob_get_clean();
                            }else{//参数个数异常
                                ob_start();
                                echo 'ERROR: unexpected params\'s number';
                                return ob_get_clean();
                            }
                            
                        }else{
                            ob_start();
                            $Controller -> $action();
                            return ob_get_clean();
                        }
                    }else{
                        ob_start();
                        echo 'ERROR: Action: '.$action.'  is  not  exists.';
                        return ob_get_clean();
                        
                    }

                }else{
                    ob_start();
                    echo 'ERROR: Controller: '.$controller.'  is  not  exists.';
                    return ob_get_clean();
                }
            }else{
                ob_start();
                echo 'ERROR: Moudle: '.$moudle.'  is  not  exists.';
                return ob_get_clean();
            }
            
        }else{
            ob_start();
            echo 'please use domain:port/Moudle/Controller/action';
            return ob_get_clean();
        }
        unset($pathInfoArr);  
    }
}





        