<?php

namespace app\weixin\controller;

use think\Controller;

class Weixin extends Controller  {
    public function index() {
        // 接收参数 signature nonce token echostr
        $timestamp = $_GET['timestamp'];
        $nonce = $_GET['nonce'];
        $token = 'guoguo2016';
        $signature = $_GET['signature'];
        $echostr = $_GET['echostr'];
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
            $this->responseMessage();
        }
    }
    
        // 接收事件推送并回复
    public function responseMessage() {
        // 1. 获取到微信推送过来的post数据(xml格式)
        //$postStr = $GLOBALS['HTTP_RAW_POST_DATA'];
        $postStr = file_get_contents("php://input"); 
        //2.处理消息类型，并设置回复类型和内容
        if (!empty($postStr)) {
            $postObj = simplexml_load_string( $postStr );
            // 判断数据包是否是订阅的事件推送
            if (strtolower($postObj->MsgType) == 'event') {
                // 如果是关注subscribe 事件
                if (strtolower($postObj->Event) == 'subscribe') {
                    // 回复用户消息
                    $toUser = $postObj->FromUserName;
                    $fromUser = $postObj->ToUserName;
                    $time = time();
                    $msgType = 'text';
                    $content = '欢迎关注果果爸爸的订阅号.....'.$postObj->FromUserName . '-' . $postObj->ToUserName;

                    /*<xml>  回复文本消息的模板
                     * <ToUserName>< ![CDATA[toUser] ]></ToUserName>
                     * <FromUserName>< ![CDATA[fromUser] ]></FromUserName> 
                     * <CreateTime>12345678</CreateTime>
                     * <MsgType>< ![CDATA[text] ]></MsgType> 
                     * <Content>< ![CDATA[你好] ]></Content> 
                     * </xml>
                     */
                    $template = "<xml> 
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <Content><![CDATA[%s]]></Content>
                            </xml>"; 

                    $info = sprintf($template, $toUser, $fromUser, $time, $msgType, $content);
                    echo $info;

                } 
            }
    
    public function responseMsg() {
        // 1.获取到微信推送过来的post数据(xml格式)
        $postStr = file_get_contents('php://input');
        // 2. 处理数据并回复
        
        if (!empty($postStr)) {
            $postObj = simplexml_load_string($postStr);
            // 判断数据包是否是订阅的事件推送
            /*<xml><ToUserName>< ![CDATA[toUser] ]></ToUserName><FromUserName>< ![CDATA[FromUser] ]></FromUserName><CreateTime>123456789</CreateTime><MsgType>< ![CDATA[event] ]></MsgType><Event>< ![CDATA[subscribe] ]></Event></xml>*/
            
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
                    $content = '欢迎关注果果爸爸的订阅号'.$postObj->FromUserName . '-' . $postObj->ToUserName;
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
                    $info = sprintf($template, $toUser, $fromUser, $time, $msgType, $content);
                    echo $info;
                }
            }
        }
    }
}

