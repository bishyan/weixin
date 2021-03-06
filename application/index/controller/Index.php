<?php
namespace app\index\controller;

class Index
{
    // 微信验证
    public function index()
    {
        // 获得参数 signaturn nonce token timestamp
        $timestamp = isset($_GET['timestamp'])? $_GET['timestamp']:'';
        $nonce     = isset($_GET['nonce'])? $_GET['nonce'] : '';
        $token     = 'GUO20160704guo';
        $signature = isset($_GET['signature'])? $_GET['signature'] : '';
        $echostr   = isset($_GET['echostr'])? $_GET['echostr'] : '';
        
        // 按字典排序
        $arr = array($timestamp, $token, $nonce);
        sort($arr);
        
        // 拼接成字符串, 并用sha1加密, 然后与$signature进行校验
        $tmpstr = implode('', $arr);
        $tmpstr = sha1($tmpstr);
        
        if ($tmpstr == $signature && !empty($echostr)) {
            echo $echostr;
            exit;
        } else {
            $this->responseMsg();
        }
    }
    
    
    // 接收事件推送并回复
    public function responseMsg() {
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
                    $content = '欢迎关注果果爸爸的订阅号'.$postObj->FromUserName . '-' . $postObj->ToUserName;

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
        }
    }
}
