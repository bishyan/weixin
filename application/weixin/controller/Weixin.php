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
                    $content = '欢迎关注果果爸爸的订阅号';
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
                
                if ($postObj->Content == '果果') {                    
                    $content = '果果2岁4个月大了...';   
                } else if (strtolower($postObj->Content) == '身高') {
                    $content = '果果身高85CM';
                }
                
                 printf($template, $toUser, $fromUser, $time, $type, $content);
            }
        }
    }
}

