<?php

class Weixin {

    public $aConfig;

    public function __construct($aConfig) {
        $this->aConfig = $aConfig;
    }

}

return array(
    'class' => '\Weixin',
    'APPID' => 'wxc96c93bd624cfb90',
    'SECRET' => file_get_contents(__DIR__ . DS . '~SECRET.txt'),
    'password' => file_get_contents(__DIR__ . DS . '~SECRET.txt')
);
