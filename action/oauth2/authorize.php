<?php

return function($oDb1, $code, $state, $oWeixin) {
    $fPutToDb = function($ACCESS_TOKEN, $OPENID)use($oDb1) {
        $url = 'https://api.weixin.qq.com/sns/userinfo?access_token=ACCESS_TOKEN&openid=OPENID&lang=zh_CN';
        $url = str_replace('ACCESS_TOKEN', $ACCESS_TOKEN, $url);
        $url = str_replace('OPENID', $OPENID, $url);
        $str = file_get_contents($url);
        $jsonArr = json_decode($str, TRUE);
        if (isset($jsonArr['errcode'])) {
            print_r($jsonArr);
            return;
        }
        $where = array();
        $where['openid'] = $jsonArr['openid'];
        $oSql = $oDb1->sql()->table('userinfo')->where($where);
        $data = call_user_func(function($data) {
            if (is_array($data['privilege'])) {
                $data['privilege'] = json_encode($data['privilege']);
            }
            return $data;
        }, $jsonArr);
        if ($oSql->find()) {
            unset($data['openid']);
            $data['updated'] = 'CURRENT_TIMESTAMP()';
            $oSql->save($data);
        } else {
            $oSql->add($data);
        }
        unset($data['updated']);
        $data['openid'] = $jsonArr['openid'];
        return $data;
    };
    $getTokenByCode = function($code)use($oDb1, $fPutToDb, $oWeixin) {
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=APPID&secret=SECRET&code=CODE&grant_type=authorization_code';
        $url = str_replace('APPID', $oWeixin->aConfig['APPID'], $url);
        $url = str_replace('SECRET', $oWeixin->aConfig['SECRET'], $url);
        $url = str_replace('CODE', $code, $url);
        $str = file_get_contents($url);
        $jsonArr = json_decode($str, true);
        /*
         * varchar(110) access_token
         * int(10)      expires_in = 7200
         * varchar(110) refresh_token
         * varchar(30)  openid
         * varchar(16)  scope
         */
        $data = array(
            'accessed' => 'CURRENT_TIMESTAMP()',
            'refreshed' => 'CURRENT_TIMESTAMP()',
            'code' => $code,
            'accessToken' => $jsonArr['access_token'],
            'refreshToken' => $jsonArr['refresh_token'],
            'expiresIn' => $jsonArr['expires_in'],
            'openid' => $jsonArr['openid']
        );
        $where = array();
        $where['openid'] = $jsonArr['openid'];
        $oSql = $oDb1->sql()->table('authorize')->where($where);
        if ($oSql->find()) {
            unset($data['openid']);
            $oSql->save($data);
        } else {
            $oSql->add($data);
        }
        return $fPutToDb($jsonArr['access_token'], $jsonArr['openid']);
    };
    $retArr = $getTokenByCode($code);
    ksort($retArr);
    $oDb1->commit();
    $password = $oWeixin->aConfig['password'];
    $query = array();
    $query['time'] = time();
    $query['json'] = json_encode($retArr);
    $query['md5'] = md5($query['time'] . $password . $query['json']);
    header('Location: ' . $state . '?' . http_build_query($query));
    exit;
};
