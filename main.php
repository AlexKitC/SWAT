<?php

define('APP_ROOT',__DIR__);
define('APP_NAME','SWAT');
require APP_ROOT.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';
require APP_ROOT.DIRECTORY_SEPARATOR.'route'.DIRECTORY_SEPARATOR.'routes.php';
\Core\Framework\Init::getInstance()->go($routes);