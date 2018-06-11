<?php

//error_reporting(0);

use FiradioPHP\F;
use FiradioPHP\System\ConvertCase;

define('DS', DIRECTORY_SEPARATOR);
define('APP_ROOT', __DIR__);
define('DATA_DIR', APP_ROOT . DS . 'data');

require_once __DIR__ . DS . 'vendor' . DS . 'autoload.php';

//初始化F框架，参数是config根目录
F::init(APP_ROOT . DS . 'config');

