<?php

/**
 *  微信基本功能类
 */
namespace app\weixin\controller;

use think\Controller;

class Weixin extends Controller  {
    private $appId;
    private $secret;
    private $accessToken;
    
    public function __construct(\think\Request $request = null) {
        parent::__construct($request);
        $this->appId = 'wxf90f6aec3e2fcd91';
        $this->secret = '1830b09c31cdf066fa299025c326b8f3';
        $this->accessToken = $this->getWxAccessToken();
    }
    
    
    /**
     * 验证微信服务器
     */
    public function wxVerify() {
 
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
    public function responseNews($postData, $arr) {
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
    public function responseImage($postData, $imgId) {
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
    public function responseText($postData, $content) {           
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
    public function sendMsgAllPreview($postJson) {
        // 获取access_token
        $access_token = $this->accessToken;
        $url = "https://api.weixin.qq.com/cgi-bin/message/mass/preview?access_token=" . $access_token;

        $res = $this->http_curl($url, 'post', $postJson);
        dump($res);
    }
    
    /**
     * 根据OpenID列表群发【订阅号不可用，服务号认证后可用】
     * 根据标签进行群发【订阅号与服务号认证后均可用】
     * @param string $postArr  post数据(json格式)
     * @param string $type  群发的方式  tag,id两种方式
     */
    public function sendMsgAll($postJson, $type = 'tag') {        
   
        if ($type == 'id') {
            $this->sendMsgAllById($postJson);
        } else {
            $this->sendMsgAllByTag($postJson);
        }
    } 
    
    
     /**
     * 根据OpenID列表群发【订阅号不可用，服务号认证后可用】
     * @param string $postArr  post数据(json格式)
     */
    private function sendMsgAllById($postJson) {
        $access_token = $this->getWxAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token=" . $access_token;
        
        $res = $this->http_curl($url, 'post', $postJson);
        dump($res);
    }
    
    /**
     * 根据标签进行群发【订阅号与服务号认证后均可用】
     * @param string $postArr  post数据(json格式)
     */
    private function sendMsgAllByTag($postJson) {
        $access_token = $this->getWxAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token=" . $access_token;
        
        $res = $this->http_curl($url, 'post', $postJson);
        dump($res);
    } 
    
    
    //上传logo图片(大小限制1MB，推荐像素为300*300，支持JPG格式)
    public function uploadWxImage() {
        $access_token = $this->getWxAccessToken();
        //dump($access_token);
        
        $url = "http://api.weixin.qq.com/cgi-bin/media/uploadimg?access_token=" . $access_token . "&type=image";
        
        $file = dirname(__FILE__) . '/lenovo.jpg';
        //dump($file); exit;
  
        if (class_exists('CURLFile')) {
            //$data['media'] = new \CURLFile(realpath($file));
            $data['media'] = curl_file_create(realpath($file));
        } else {

            $data['media'] = '@' . realpath($file);
        }
        
        dump($data);exit;
        $res = $this->http_curl($url, 'post', $data);
        var_dump($res);
    }
    
    
    /**
     *  发送模板消息
     * @param type $postJson   json格式的数据
     */
    public function sendWxTemplateMsg($postJson) {
        $access_token = $this->getWxAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $access_token;
        
        
        $res = $this->http_curl($url, 'post', $postJson);
        dump($res);        
    }
    
    // 获取access_token
    private function getWxAccessToken() {
        $access_token = cache('access_token')? cache('access_token') : '';
        
        if (empty($access_token) || cache('expire_time') < time()) {
            // 1. 请求url地址
            $appid = 'wxf90f6aec3e2fcd91';
            $secret = '1830b09c31cdf066fa299025c326b8f3';
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='. $appid .'&secret='. $secret;

            $res = $this->http_curl($url);
            $access_token = $res['access_token'];
            cache('access_token', $access_token);
            cache('expire_time', time()+7000);           
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
    public function http_curl($url, $type='get', $arr = '') {
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
    public function getWxServerIp() {
        $accessToken = "15_m8Bbhv7qyXd3o80hYXPU78vY9BXkvyZl_7dfQRE3sfKOIriVM6AKE8grj6DgBjXmO92ygWpO4EC0_OPe1wOJ0AACqgBY8Z_oYd7J2O0AHRrrXr6zyNIUdztkSTkFwyEIibjie1WJY3nbGvztTBBfAJADLL";
        $url = "https://api.weixin.qq.com/cgi-bin/getcallbackip?access_token=$accessToken";
        
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $res = curl_exec($ch);
        $res = json_decode($res, true);
        curl_close($ch);
        
        return $res;
    }
    
    /**
     * 创建自定义菜单
     * @param type $postJson  post数据(json格式)
     */
    public function createMenu($postJson) {
        $access_token = $this->accessToken;
        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=" . $access_token;
        $res = $this->http_curl($url, 'post', $postJson);
        var_dump($res);
    }
    
    /**
     * 获取网页授权code
     * @param string $redirect_url  授权后重定向的回调链接地址， 请使用 urlEncode 对链接进行处理
     * @param type $scope  应用授权作用域，snsapi_base （不弹出授权页面，直接跳转，只能获取用户openid），
     * snsapi_userinfo （弹出授权页面，可通过openid拿到昵称、性别、所在地。并且， 即使在未关注的情况下，只要用户授权，也能获取其信息 ）
     */
    public function getCode($redirect_url, $scope='snsapi_base') {
        $appid = $this->appId;

        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".$redirect_url."&response_type=code&scope=".$scope."&state=123456#wechat_redirect";
        header('location:' . $url);
        exit;  //这里需要exit结束, 不然有可能不跳转
    }
    
    public function getUserInfo($code) {
        //2.获取到网页授权的access_token
        $info = cache($code);
        if (!$info) {
            echo 'guoguoss';
            $appid = $this->appId;
            $secret = $this->secret; 
            $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$secret."&code=".$code."&grant_type=authorization_code";
            $info = $this->http_curl($url);
            cache($code, $info, tim()+30*24*3600);
            cache($code.'.expire_time', 7000);  
        }
        
        if ($info['scope'] == 'snsapi_base') {
            return $info;
        }
        
        dump($info);exit;
        
        // 判断access_token是否过期
        if ($info['scope'] == 'snsapi_userinfo' && $info['expire_time'] < time) {
            $res = $this->refreshToken($info['refresh_token']);
        }
        
        //3. 拉取用户的详细信息
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$info['access_token']."&openid=".$info['openid']."&lang=zh_CN";
        return $this->weixinObj->http_curl($url);
    }
    
    // 刷新网页授权的access_token
    public function refreshToken($refresh_token) {
        $appid = $this->appId;
        $url = "https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=".$appid."&grant_type=refresh_token&refresh_token=" . $refresh_token;
        
        return $this->http_curl($url);
    }
}

