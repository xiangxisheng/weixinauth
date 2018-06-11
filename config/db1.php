<?php

return function() {
    $config = require(__DIR__ . DS . 'db' . DS . 'default.php');
    $config['host'] = '106.14.29.102';
    $config['dbname'] = 'weixin';
    $config['tablepre'] = 'weixin_';
    $config['username'] = 'weixin';
    return $config;
};
