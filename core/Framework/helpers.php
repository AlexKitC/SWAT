<?php
/**
 * 友好的打印输出
 * @param mixed $param
 */
if(!function_exists('dd')) {
    function dd($param) {
        ob_start();
        echo "<pre>";
        var_dump($param);
        echo "</pre>";
        return ob_get_contents();        
    }
}