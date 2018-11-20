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
                    case 5: // 回复图文
                        $type = 'news';
                        $arr = array(
                            array(
                                'title' => 'guoguo',
                                'description' => '果果是一个可爱的女孩子',
                                'picUrl' => 'http://blog.ai702.com/public/Uploads/Admin/20180517202617219.jpg',
                                'url' => 'http://blog.ai702.com/a/6',
                            ),
                            array(
                                'title' => 'baidu',
                                'description' => 'baidu搜索',
                                'picUrl' => 'https://www.baidu.com/s?wd=%E4%BB%8A%E6%97%A5%E6%96%B0%E9%B2%9C%E4%BA%8B&tn=SE_PclogoS_8whnvm25&sa=ire_dl_gh_logo&rsv_dl=igh_logo_pcs',
                                'url' => 'http://www.baidu.com',
                            ),
                            array(
                                'title' => 'bi xin yun',
                                'description' => '果果的大名号闭馨允',
                                'picUrl' => 'http://blog.ai702.com/public/Uploads/Admin/20180517163727743.jpg',
                                'url' => 'http://www.hao123.com',
                            ),
                        );
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
}

