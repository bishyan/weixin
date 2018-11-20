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
        
        if ($arrStr == $signature && $signature) {
            echo $echostr;
            exit;
        } else {
            $this->responseMsg();
        }
    }
    
    public function responseMsg() {
        // 1.接收微信发送过来的参数
        $postStr = file_get_contents('php://input');
        // 2. 处理数据并回复
        $postObj = simplexml_load_string($postStr);
        if (!$postObj) {
            // 判断是否是推送事件
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
                     * </xml>*/
                    $toUser = $postObj->FromUserName;
                    $fromUser = $postObj->ToUserName;
                    $time = time();
                    $msgType = 'text';
                    $content = '感谢关注果爸的公众号';
                    $template = "<xml> 
                            <ToUserName>< ![CDATA[%s] ]></ToUserName> 
                            <FromUserName>< ![CDATA[%s] ]></FromUserName> 
                            <CreateTime>%s</CreateTime> 
                            <MsgType>< ![CDATA[%s] ]></MsgType> 
                            <Content>< ![CDATA[%s] ]></Content> 
                            <xml>";
                    printf($template, $toUser, $fromUser, $time, $msgType, $content);
                }
            }
        }
    }
}

