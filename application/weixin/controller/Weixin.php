<?php

/**
 *  微信基本功能类
 */
namespace app\weixin\controller;

use think\Controller;

class Weixin extends Controller  {
    //private $appId;
    //private $secret;
    //private $accessToken;
    
    /**
     * 验证微信服务器
     */
    public static function wxVerify() {
 
        // 获得参数 signaturn nonce echostr timestamp, token
        $timestamp = isset($_GET['timestamp'])? $_GET['timestamp']:'';
        $nonce     = isset($_GET['nonce'])? $_GET['nonce'] : '';
        $token     = 'guoguo2016';
        $signature = isset($_GET['signature'])? $_GET['signature'] : '';
        $echostr   = isset($_GET['echostr'])? $_GET['echostr'] : '';
        
        // 将timestamp, nonce, token 三个参数按字典排序 
        $arr = array($timestamp, $nonce, $token);
        sort($arr);
        //拼接成字符串,sha1加密, 然后与signature校对
        $arrStr = sha1(implode('', $arr));
        
        if ($arrStr == $signature) {
            echo $echostr;
        } else {
            echo '验证失败';
        }
    }
    
    /**
     * 回复图文消息
     * @param array $postData   post数据
     * @param array $arr     二维数组, 格式: 
     *      $arr = array(
                array(
                    'title' => 'baidu',
                    'description' => 'baidu搜索',
                    'picUrl' => 'https://www.baidu.com/img/bd_logo1.png',
                    'url' => 'http://www.baidu.com',
                ),
                array(
                    'title' => 'guoguo',
                    'description' => '果果是一个可爱的女孩子',
                    'picUrl' => 'http://blog.ai702.com/public/Uploads/Admin/20180517202617219.jpg',
                    'url' => 'http://blog.ai702.com/a/6',
                ),
            );
     * @return string
     */
    public static function responseNews($postData, $arr) {
        $toUser = $postData['FromUserName'];
        $fromUser = $postData['ToUserName'];
        $template = "<xml>
                <ToUserName><![CDATA[%s]]></ToUserName>
                <FromUserName><![CDATA[%s]]></FromUserName>
                <CreateTime>%s</CreateTime>
                <MsgType><![CDATA[%s]]></MsgType>
                <ArticleCount>" . count($arr) . "</ArticleCount>
                <Articles>";
        foreach($arr as $k=>$v) {
            $template .= "<item><Title><![CDATA[".$v['title']."]]></Title>
                <Description><![CDATA[".$v['description']."]]></Description>
                <PicUrl><![CDATA[".$v['picUrl']."]]></PicUrl>
                <Url><![CDATA[".$v['url']."]]></Url>
                </item>";
        }
        $template .= "</Articles></xml>";    
        
        echo sprintf($template, $toUser, $fromUser, time(), 'news');
    }
    
    /**
     * 回复图片消息
     * @param array $postData   post数据
     * @param string $imgId     图片id
     */
    public static function responseImage($postData, $imgId) {
        $toUser = $postData['FromUserName'];
        $fromUser = $postData['ToUserName'];
        $template = "<xml>
                    <ToUserName>< ![CDATA[%s] ]></ToUserName>
                    <FromUserName>< ![CDATA[%s] ]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType>< ![CDATA[%s] ]></MsgType>
                    <Image>
                        <MediaId>< ![CDATA[%s] ]></MediaId>
                    </Image>
                    </xml>";
        
        echo sprintf($template, $toUser, $fromUser, time(), 'image', $imgId);
    }
    
    /**
     * 回复文本消息
     * @param array $postData   post数据
     * @param string $content   文本内容
     */
    public static function responseText($postData, $content) {           
        $toUser   = $postData['FromUserName'];
        $fromUser = $postData['ToUserName'];
        $template = "<xml> 
                        <ToUserName><![CDATA[%s]]></ToUserName>
                        <FromUserName><![CDATA[%s]]></FromUserName>
                        <CreateTime>%s</CreateTime>
                        <MsgType><![CDATA[%s]]></MsgType>
                        <Content><![CDATA[%s]]></Content>
                        </xml>"; 
        echo sprintf($template, $toUser, $fromUser, time(), 'text', $content);

        //printf($template, $toUser, $fromUser, $time, 'text', $content);       
    }
    
    
    /**
     * 群发预览
     * @param string $postArr  post数据(json格式)
     * @param string $type    消息类型
     */
    public static function sendMsgAllPreview($postJson) {
        // 获取access_token
        $url = "https://api.weixin.qq.com/cgi-bin/message/mass/preview?access_token=" . self::getWxAccessToken();

        $res = self::http_curl($url, 'post', $postJson);
        dump($res);
    }
    
    /**
     * 根据OpenID列表群发【订阅号不可用，服务号认证后可用】
     * 根据标签进行群发【订阅号与服务号认证后均可用】
     * @param string $postArr  post数据(json格式)
     * @param string $type  群发的方式  tag,id两种方式
     */
    public static function sendMsgAll($postJson, $type = 'tag') {        
   
        if ($type == 'id') {
            self::sendMsgAllById($postJson);
        } else {
            self::sendMsgAllByTag($postJson);
        }
    } 
    
    
     /**
     * 根据OpenID列表群发【订阅号不可用，服务号认证后可用】
     * @param string $postArr  post数据(json格式)
     */
    private static function sendMsgAllById($postJson) {
        $url = "https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token=" . self::getWxAccessToken();
        
        $res = self::http_curl($url, 'post', $postJson);
        dump($res);
    }
    
    /**
     * 根据标签进行群发【订阅号与服务号认证后均可用】
     * @param string $postArr  post数据(json格式)
     */
    private static function sendMsgAllByTag($postJson) {
        $url = "https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token=" . self::getWxAccessToken();
        
        $res = self::http_curl($url, 'post', $postJson);
        dump($res);
    } 
    
    
    //上传logo图片(大小限制1MB，推荐像素为300*300，支持JPG格式)
    public static function uploadWxImage($file_path) {
        
        $url = "http://api.weixin.qq.com/cgi-bin/media/uploadimg?access_token=" . self::getWxAccessToken() . "&type=image";

        if (class_exists('CURLFile')) {
            //$data['media'] = new \CURLFile(realpath($file));
            $data['media'] = curl_file_create(realpath($file_path));
        } else {

            $data['media'] = '@' . realpath($file_path);
        }

        $res = self::http_curl($url, 'post', $data);
        var_dump($res);
    }
    
    
    /**
     *  发送模板消息
     * @param type $postJson   json格式的数据
     */
    public static function sendWxTemplateMsg($postJson) {
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . self::getWxAccessToken();
            
        $res = self::http_curl($url, 'post', $postJson);
        dump($res);        
    }
    
    // 获取access_token
    private static function getWxAccessToken() {
        $access_token = cache('token_info')['access_token'];

        if (!$access_token || cache('token_info')['expire_time'] < time()) {
            // 1. 请求url地址
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='. config('app_id') .'&secret='. config('secret');

            $res = self::http_curl($url);
           
            $access_token = $res['access_token'];
            $token['access_token'] = $access_token;
            $token['expire_time'] = time() + 7000;
            cache('token_info', $token);          
        }
        
        return $access_token;
    }
    
    /**
     * 获取url接口数据
     * @param string $url   接口url
     * @param string $type  请求的类型
     * @param  $arr  请求的参数
     * @return type
     */
    public static function http_curl($url, $type='get', $arr = '') {
        // 1. 初始化
        $ch = curl_init();
        // 2. 设置参数
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        if ($type == 'post') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $arr);
        }

        // 3. 采集
        $output = curl_exec($ch);    
        // 关闭
        //curl_close($ch);
        
        if (curl_errno($ch)) {
            // 请示失败, 返回错误信息
            $err = curl_error($ch);
            curl_close($ch);
            return $err;
        } else {
            // 成功
            curl_close($ch);
            $res = json_decode($output, true);
            // 判断采集回来的是json还是xml格式
            if (json_last_error() == JSON_ERROR_NONE) {
                // json
                return $res;
            } else {
                // xml 将xml转成数组
                $obj = simplexml_load_string($output);

                return json_decode(json_encode($obj), true);
            }
        }
    }
    
    //获取微信服务器IP
    public static function getWxServerIp() {
        $url = 'https://api.weixin.qq.com/cgi-bin/getcallbackip?access_token='. self::getWxAccessToken();
        
        $res = self::http_curl($url); 
        return $res;
    }
    
    /**
     * 创建自定义菜单
     * @param type $postJson  post数据(json格式)
     */
    public static function createMenu($postJson) {
        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=" . self::getWxAccessToken();
        $res = self::http_curl($url, 'post', $postJson);
        var_dump($res);
    }
    
    /**
     * 返回微信网页授权url
     * @param string $redirect_url  授权后重定向的回调链接地址， 请使用 urlEncode 对链接进行处理
     * @param type $scope  应用授权作用域，snsapi_base （不弹出授权页面，直接跳转，只能获取用户openid），
     *                      snsapi_userinfo （弹出授权页面，可通过openid拿到昵称、性别、所在地。
     *                      并且，即使在未关注的情况下，只要用户授权，也能获取其信息 ）
     */
    public static function getWxAuthorizeUrl($redirect_url, $state='', $scope='snsapi_base') {
        // 微信授权地址
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".config('app_id')."&redirect_uri=".$redirect_url."&response_type=code&scope=".$scope."&state=".$state."#wechat_redirect";
        return $url;
    }
    
    /**
     * 获取网页授权用户信息
     * @param type $code 获取access_token的票据
     * @return type
     */
    public static function getUserInfo($code) {
        //2.获取到网页授权的access_token
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".config('app_id')."&secret=".config('secret')."&code=".$code."&grant_type=authorization_code";
        $info = self::http_curl($url);

        if (isset($info['errcode'])) {
            echo '获取用户信息错误!';
            echo '<br>错误代码: ' . $info['errcode'];
            echo '<br>错误信息: ' . $info['errmsg'];
            exit;
        }
        
        if ($info['scope'] == 'snsapi_base') {
            return $info;
        }
        
        // 判断access_token是否过期
//        if ($info['scope'] == 'snsapi_userinfo' && $info['expire_time'] < time()) {
//            $info = $this->refreshToken($info['refresh_token']);
//            cache($code.'.expire_time', time()+7000);           
//        }
        
        //3. 拉取用户的详细信息
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$info['access_token']."&openid=".$info['openid']."&lang=zh_CN";
        
        return self::http_curl($url);
    }
    
    // 刷新网页授权的access_token
    public static function refreshToken($refresh_token) {
        $url = "https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=".config('app_id')."&grant_type=refresh_token&refresh_token=" . $refresh_token;
        
        return self::http_curl($url);
    }
    
    // 获取jsapi_ticket
    private static function getJsApiTicket() {
        // 如果session中保存着有效的jsapi_ticket
        if (session('?jsapi_ticket') && session('jsapi_ticket_expire_time') > time()) {
            $jsapi_ticket = session('jsapi_ticket');
        } else {
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=".self::getWxAccessToken()."&type=jsapi";
            $res = self::http_curl($url);
            $jsapi_ticket = $res['ticket'];
            dump($res);
            
            session('jsapi_ticket', $jsapi_ticket);
            session('jsapi_ticket_expire_time', time()+7000);
        }
        return $jsapi_ticket;
    }
    
    // 返回随机字符串
    public static function getNoncestr($num=16) {
        $string = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $strlen = strlen($string);
        $randStr = '';
        for($i=1; $i<=$num; ++$i) {
            $randStr .= $string{rand(0, $strlen-1)};
        }
        
        return $randStr;
    }
    
    // 生成JS-SDK权限验证的签名
    public static function getSignature($noncestr,$timestamp,$url) {        
        $tempStr = "jsapi_ticket=".self::getJsApiTicket()."&noncestr=".$noncestr."&timestamp=".$timestamp."&url=".$url;
        
        return sha1($tempStr);
    }
}

