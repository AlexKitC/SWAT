<?php
namespace Core\Framework;
use \Swoole\Coroutine;
use \Swoole\Coroutine\Mysql as CoMysql;
use \Core\Db\Mysql;
class Context
{
    public static $header = [];//请求体，主要保存request response部分，需要用协程号区分

    public static $conf   = [];//项目配置,共用的，无需用协程号区分

    public static function getCid() : int
    {
        return Coroutine::getCid();
    }

    public static function set(\Swoole\Http\Request $request, \Swoole\Http\Response $response)
    {
        $cid = self::getCid();
        self::$header[$cid]['request'] = $request;
        self::$header[$cid]['response'] = $response;
    }

    public static function get()
    {
        return self::$header[self::getCid()];
    }

    public static function getRequest()
    {
        return self::$header[self::getCid()]['request'];
    }

    public static function getResponse()
    {
        return self::$header[self::getCid()]['response'];
    }

    /**
     * 清除上下文请求头部分
     */
    public static function clearContextHeader()
    {
        unset(self::$header[self::getCid()]);
    }

    /**
     * 设置配置到上下文
     * @param array $conf
     */
    public static function setConfContext(array $conf)
    {
        if(!is_null(self::$conf)) {
            self::$conf = $conf;
        }
    }

    /**
     * 获取上下文配置信息
     * @param string $key
     */
    public static function getConf(string $key = null)
    {
        if(is_null($key)) {
            return self::$conf;
        }
        if(key_exists($key, self::$conf)) {
            return self::$conf[$key];
        }
        return null;
    }

}