<?php

/**
 *  微信基本功能类
 */
namespace app\weixin\controller;

use think\Controller;

class Weixin extends Controller  {
    
    /**
     * 微信验证
     * @param array $paramArr   参数数组
     * @return boolean
     */
    public function wxVerify($paramArr) {
        // 获得参数 signaturn nonce echostr timestamp
        $timestamp = isset($paramArr['timestamp'])? $paramArr['timestamp'] : '';
        $nonce     = isset($paramArr['nonce'])? $paramArr['nonce'] : '';
        $token     = isset($paramArr['token'])? $paramArr['token'] : '';
        $signature = isset($paramArr['signature'])? $paramArr['signature'] : '';
        $echostr   = isset($paramArr['echostr'])? $paramArr['echostr'] : '';
        
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
     * @param object $postObj   post数据对象
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
    public function responseNews($postObj, $arr) {
        $toUser = $postObj->FromUserName;
        $fromUser = $postObj->ToUserName;
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
     * @param object $postObj   post数据对象
     * @param string $imgId     图片id
     */
    public function responseImage($postObj, $imgId) {
        $toUser = $postObj->FromUserName;
        $fromUser = $postObj->ToUserName;
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
     * @param object $postObj   post数据对象
     * @param string $content   文本内容
     */
    public function responseText($postObj, $content) {           
        $toUser = $postObj->FromUserName;
        $fromUser = $postObj->ToUserName;
        $template = "<xml> 
                        <ToUserName><![CDATA[%s]]></ToUserName>
                        <FromUserName><![CDATA[%s]]></FromUserName>
                        <CreateTime>%s</CreateTime>
                        <MsgType><![CDATA[%s]]></MsgType>
                        <Content><![CDATA[%s]]></Content>
                        </xml>"; 
        //$info = sprintf($template, $toUser, $fromUser, $time, $msgType, $content);
        //echo $info;
        printf($template, $toUser, $fromUser, time(), 'text', $content);       
    }
    
    // 获取access_token
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
        //var_dump($res);
        // 5. 关闭
        curl_close($curl); 
        
        return $res;
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

