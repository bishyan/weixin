<?php

/**
 *  微信基本功能类
 */
namespace app\weixin\controller;

use think\Controller;

class Weixin extends Controller  {
    
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
        //1. 获取access_token
        $access_token = $this->getWxAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/message/mass/preview?access_token=" . $access_token;
        //2. 组装群发接口数据
        /*{     
        "touser":"OPENID",
        "text":{           
               "content":"CONTENT"            
               },     
        "msgtype":"text"
        }
        $array = array(
            'touser' => 'oBqKY1ABzkfRtgZNxu-VzrV5Kt3M',
            'text' => array('content' => urlencode('果果唱歌很好听! very Good!')),
            'msgtype' => 'text'
        );*/
        // 3. 转换->json
        //$postJson = urldecode(json_encode($postArr));
        //dump(urldecode($postJson));exit;
        $res = $this->http_curl($url, 'post', $postJson);
        dump($res);
    }
    
    /**
     * 根据OpenID列表群发【订阅号不可用，服务号认证后可用】
     * @param string $postArr  post数据(json格式)
     */
    public function sendMsgAllById($postJson) {
        $access_token = $this->getWxAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token=" . $access_token;
        
        $res = $this->http_curl($url, 'post', $postJson);
        dump($res);
    } 
    
    /**
     * 根据标签进行群发【订阅号与服务号认证后均可用】
     * @param string $postArr  post数据(json格式)
     */
    public function sendMsgAllByTag($postJson) {
        $access_token = $this->getWxAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token=" . $access_token;
        
        $res = $this->http_curl($url, 'post', $postJson);
        dump($res);
    } 
    
    
    //上传logo图片(大小限制1MB，推荐像素为300*300，支持JPG格式)
    public function uploadWxImage() {
        $access_token = $this->getWxAccessToken();
        //dump($access_token);
        
        $url = "http://api.weixin.qq.com/cgi-bin/media/uploadimg?access_token=" . $access_token . '&type=image';
        
        $file = dirname(__FILE__) . '/lenovo.jpg';
        
        $postArr = array(
            'media' => '@'. $file,
        );
        //dump($postArr);exit;
        $res = $this->http_curl($url, 'post', $postArr);
        var_dump($res);
    }
    
    // 获取access_token
    public function getWxAccessToken() {
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
}

