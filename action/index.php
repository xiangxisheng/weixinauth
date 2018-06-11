<?php
return function() {
    $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=APPID&redirect_uri=REDIRECT_URI&response_type=code&scope=SCOPE&state=STATE#wechat_redirect';
    $url1 = call_user_func(function($url) {
        $url = str_replace('APPID', 'wxc96c93bd624cfb90', $url);
        $url = str_replace('REDIRECT_URI', urlencode('http://weixin.erbakeji.com/oauth2/authorize'), $url);
        $url = str_replace('SCOPE', 'snsapi_userinfo', $url);
        $url = str_replace('STATE', urlencode('http://ddns.wzxjkj.com:8686/public/weixin'), $url);
        return $url;
    }, $url);
    header('Content-Type: text/html; charset=utf-8');
    // echo $url1;
    ?><html>
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
        </head>
        <body>
            <button style="width:100%;height:20%;font-size:3em" onclick="location.href = ('<?php echo $url1; ?>')">点击微信登录</button>
        </body>
    </html>
    <?php
    exit;
};
