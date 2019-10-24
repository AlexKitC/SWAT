<?php
namespace Core\Framework;
class Config
{
    private static $instance;
    private function __clone() {}
    private function __construct() {}
    
    public static function getInstance()
    {
        if(!self::$instance) {
            return new self();
        }
        return self::$instance;
    }

    /**
     * 获取配置
     * @param string $key
     */
    public static function get(string $key)
    {
        
    }
}