<?php

namespace app\weixin\controller;

use think\Controller;

class Weixin extends Controller  {
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
            
            /*<xml><ToUserName>< ![CDATA[toUser] ]></ToUserName><FromUserName>< ![CDATA[FromUser] ]></FromUserName><CreateTime>123456789</CreateTime><MsgType>< ![CDATA[event] ]></MsgType><Event>< ![CDATA[subscribe] ]></Event></xml>*/
            // 判断数据包是否是订阅的事件推送
            if (strtolower($postObj->MsgType) == 'event') {
                // 如果是关注事件(subscribe)
                if (strtolower($postObj->Event) == 'subscribe') {
                    //拼接回复消息(纯文本)
                    /*<xml> 
                     * <ToUserName>< ![CDATA[toUser] ]></ToUserName> 
                     * <FromUserName>< ![CDATA[fromUser] ]></FromUserName> 
                     * <CreateTime>12345678</CreateTime> 
                     * <MsgType>< ![CDATA[text] ]></MsgType> 
                     * <Content>< ![CDATA[你好] ]></Content> 
                     * </xml>
                    $toUser = $postObj->FromUserName;
                    $fromUser = $postObj->ToUserName;
                    $time = time();
                    $msgType = 'text';
                    $content = '感谢关注果爸的公众号......';*/
                    $toUser = $postObj->FromUserName;
                    $fromUser = $postObj->ToUserName;
                    $time = time();
                    $msgType = 'text';
                    $content = "欢迎关注果果爸爸的订阅号, \n回复1: 了解果果的年龄 \n回复2: 了解果果的身高 \n回复3: 了解果果的体重 \n回复4: 果果的个人博客 \n回复5: 单图文信息";
                    /*$template = "<xml> 
                            <ToUserName><![CDATA[%s]]></ToUserName> 
                            <FromUserName><![CDATA[%s]]></FromUserName> 
                            <CreateTime>%s</CreateTime> 
                            <MsgType><![CDATA[%s]]></MsgType> 
                            <Content><![CDATA[%s]]></Content> 
                            </xml>"; */
                    $template = "<xml> 
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <Content><![CDATA[%s]]></Content>
                            </xml>"; 
                    //$info = sprintf($template, $toUser, $fromUser, $time, $msgType, $content);
                    //echo $info;
                    printf($template, $toUser, $fromUser, $time, $msgType, $content);
                    //echo $info;
                }
            } else if ($postObj->MsgType == 'text') {
                $toUser = $postObj->FromUserName;
                $fromUser = $postObj->ToUserName;
                $time = time();
                $type = 'text';
                $template = "<xml> 
                        <ToUserName><![CDATA[%s]]></ToUserName>
                        <FromUserName><![CDATA[%s]]></FromUserName>
                        <CreateTime>%s</CreateTime>
                        <MsgType><![CDATA[%s]]></MsgType>
                        <Content><![CDATA[%s]]></Content>
                        </xml>";
                switch( trim($postObj->Content) ) {
                    case 1:
                        $content = '果果2岁4个月大了..';
                        break;
                    case 2:
                        $content = '果果目前的身高是86cm';
                        break;
                    case 3:
                        $content = '果果13kg了';
                        break;
                    case 4:
                        $content = "<a href='http://blog.ai702.com'>果果的个人博客</a>";
                        break;
                    case 5: // 回复单图文
                        $arr = array(
                            array(
                                'title' => 'guoguo',
                                'description' => '果果是一个可爱的女孩子',
                                'picUrl' => 'http://blog.ai702.com/public/Uploads/Admin/20180517202617219.jpg',
                                'url' => 'http://blog.ai702.com/a/6',
                            ),
                        );
                        $type = 'news';
                        $template = $this->getNewsTemplate($arr);
                        break;
                    case 6: 
                    // 回复多图文, 当前微信版本 : 图文消息个数；
                    //当用户发送文本、图片、视频、图文、地理位置这五种消息时，开发者只能回复1条图文消息；其余场景最多可回复8条图文消息
                        $arr = array(
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
                            
                            array(
                                'title' => 'bi xin yun',
                                'description' => '果果的大名号闭馨允',
                                'picUrl' => 'http://blog.ai702.com/public/Uploads/Admin/20180517163727743.jpg',
                                'url' => 'http://www.hao123.com',
                            ),
                        );
                        $type = 'news';
                        $template = $this->getNewsTemplate($arr);
                        break;
                    default:
                        $content = '关键字不正确!';
                        break;
                }
                
                if (isset($content)) {
                    printf($template, $toUser, $fromUser, $time, $type, $content);
                } else {
                    printf($template, $toUser, $fromUser, $time, $type);
                }
            }
                      
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

