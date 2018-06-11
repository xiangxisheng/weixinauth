<?php

use FiradioPHP\F;

require '../F.php';
$oDb = F::$oConfig->getInstance('db1');
$oWeixin = F::$oConfig->getInstance('weixin');
//$oDb->rollBack();
$fPutToDb = function($ACCESS_TOKEN, $OPENID)use($oDb) {
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
    $oSql = $oDb->sql()->table('userinfo')->where($where);
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
};
$fRefresh = function($REFRESH_TOKEN)use($oDb) {
    $url = 'https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=APPID&grant_type=refresh_token&refresh_token=REFRESH_TOKEN';
    $url = str_replace('APPID', $oWeixin->aConfig['APPID'], $url);
    $url = str_replace('REFRESH_TOKEN', $REFRESH_TOKEN, $url);
    $str = file_get_contents($url);
    $jsonArr = json_decode($str, TRUE);
    $where = array();
    $where['openid'] = $jsonArr['openid'];
    $oSql = $oDb->sql()->table('authorize')->where($where);
    $data = array(
        'refreshed' => 'CURRENT_TIMESTAMP()',
        'accessToken' => $jsonArr['access_token'],
        'expiresIn' => $jsonArr['expires_in'],
        'refreshToken' => $jsonArr['refresh_token'],
        'scope' => $jsonArr['scope']
    );
    if ($oSql->find()) {
        $oSql->save($data);
    }
    return $jsonArr['access_token'];
};
$field = 'openid,accessToken,refreshToken,(TIMESTAMPDIFF(SECOND,refreshed,NOW())>expiresIn)is_expires';
$oSql = $oDb->sql()->table('authorize')->field($field);
$rows = $oSql->select();
foreach ($rows as $row) {
    if ($row['is_expires']) {
        $row['accessToken'] = $fRefresh($row['refreshToken']);
    }
    $fPutToDb($row['accessToken'], $row['openid']);
}
$oDb->commit();
