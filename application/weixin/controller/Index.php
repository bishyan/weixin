<?php

namespace app\weixin\controller;

use think\Controller;

class Index extends Controller  {
    use Weixin;
    private $weixinObj;
      
    public function __construct(\think\Request $request = null) {
        parent::__construct($request);
        $this->weixinObj = new Weixin();
    }
    public function index() {
        // 获得参数 signaturn nonce echostr timestamp
        $timestamp = isset($_GET['timestamp'])? $_GET['timestamp']:'';
        $nonce     = isset($_GET['nonce'])? $_GET['nonce'] : '';
        $token     = 'guoguo2016';
        $signature = isset($_GET['signature'])? $_GET['signature'] : '';
        $echostr   = isset($_GET['echostr'])? $_GET['echostr'] : '';
        
        // 将timestamp, nonce, token 三个参数按字典排序 
        $arr = array($timestamp, $nonce, $token);
        sort($arr);
        //拼接成字符串,sha1加密, 然后与signature校对
        $arrStr = implode('', $arr);
        $arrStr = sha1($arrStr);
        
        if ($arrStr == $signature && $echostr) {
            echo $echostr;
            exit;
        } else {
            $this->responseMsg();
        }
    }
    
    
    public function responseMsg() {
        // 1.获取到微信推送过来的post数据(xml格式)
        $postStr = file_get_contents('php://input');
        // 2. 处理数据并回复
        
        if (!empty($postStr)) {
            $postObj = simplexml_load_string($postStr);
            
            $this->weixinObj->responseMsg($postObj);
                      
        }
    }
    
    // 返回图文的模板
    protected function getNewsTemplate($arr=array()) {       

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
        
        return $template;
    }
    
    public function getWxAccessToken() {
        // 1. 请求url地址
        $appid = 'wxe6dfc606143872e8';
        $secret = '0be5d18ee3d0f3df973d06c2a9e22b22';
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='. $appid .'&secret='. $secret;
        
        // 2. 初始化curl
        $curl = curl_init();     
        // 3. 设置参数        
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        // 4. 调用接口 
        $res = curl_exec($curl);

        if (curl_errno($curl)) {
            var_dump(curl_error($curl));
        }
        
        $res = json_decode($res, true);
        var_dump($res);
        // 5. 关闭
        curl_close($curl); 
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

