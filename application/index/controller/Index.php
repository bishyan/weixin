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
            return $echostr;
        } else {
            $this->responseMsg();
        }
        //return  'hello weixin';
    }
    
    public function responseMessage() {
        //1.获取到微信推送过来post数据（xml格式）
        //$postArr = $GLOBALS['HTTP_RAW_POST_DATA'];//php7以上不能用
        $postArr = file_get_contents("php://input"); 
        //2.处理消息类型，并设置回复类型和内容
        $postObj = simplexml_load_string( $postArr );
        //$postObj->ToUserName = '';
        //$postObj->FromUserName = '';
        //$postObj->CreateTime = '';
        //$postObj->MsgType = '';
        //$postObj->Event = '';
        // gh_e79a177814ed
        //判断该数据包是否是订阅的事件推送
        if( strtolower( $postObj->MsgType) == 'event'){
            //如果是关注 subscribe 事件
            if( strtolower($postObj->Event == 'subscribe') ){
                //回复用户消息(纯文本格式) 
                $toUser   = $postObj->FromUserName;
                $fromUser = $postObj->ToUserName;
                $time     = time();
                $msgType  =  'text';
                //$content  = '欢迎关注我们的微信公众账号'.$postObj->FromUserName.'-'.$postObj->ToUserName;
                $content  = '欢迎关注果果爸爸的微信公众账号';
                $template = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <Content><![CDATA[%s]]></Content>
                            </xml>";
                $info     = sprintf($template, $toUser, $fromUser, $time, $msgType, $content);
                echo $info;
            }
        }
    }
    
    // 接收事件推送并回复
    public function responseMsg() {
        // 1. 获取到微信推送过来的post数据(xml格式)
        //$postStr = $GLOBALS['HTTP_RAW_POST_DATA'];
        $postStr = file_get_contents("php://input");
        
        // 2. 处理消息类型, 并设置回复类型和内容
        /*<xml><ToUserName>< ![CDATA[toUser] ]></ToUserName>
         * <FromUserName>< ![CDATA[FromUser] ]></FromUserName>
         * <CreateTime>123456789</CreateTime>
         * <MsgType>< ![CDATA[event] ]></MsgType>
         * <Event>< ![CDATA[subscribe] ]></Event></xml>
         *  参数              描述
            ToUserName      开发者微信号
            FromUserName    发送方帐号（一个OpenID）
            CreateTime      消息创建时间 （整型）
            MsgType         消息类型，event
            Event           事件类型，subscribe(订阅)、unsubscribe(取消订阅)
         */
        $postObj = simplexml_load_string($postStr);   // 把XML信息转换成对象
        // 判断数据包是否是订阅的事件推送
        if (strtolower($postObj->MsgType) == 'event') {
            // 如果是关注subscribe 事件
            if (strtolower($postObj->Event) == 'subscribe') {
                // 回复用户消息
                $toUser = $postObj->FromUserName;
                $fromUser = $postObj->ToUserName;
                $time = time();
                $msgType = 'text';
                $content = '欢迎关注果爸的订阅号';
                /*<xml>  回复文本消息的模板
                 * <ToUserName>< ![CDATA[toUser] ]></ToUserName>
                 * <FromUserName>< ![CDATA[fromUser] ]></FromUserName> 
                 * <CreateTime>12345678</CreateTime>
                 * <MsgType>< ![CDATA[text] ]></MsgType> 
                 * <Content>< ![CDATA[你好] ]></Content> 
                 * </xml>
                 */
                $template = "<xml>
                        <ToUserName>< ![CDATA[%s] ]></ToUserName> '
                        <FromUserName>< ![CDATA[%s] ]></FromUserName> '
                        <CreateTime>%s</CreateTime> '
                        <MsgType>< ![CDATA[%s] ]></MsgType> '
                        <Content>< ![CDATA[%s] ]></Content> '
                        </xml>";
                $info = sprintf($template, $toUser, $fromUser, $time, $msgType, $content);
                return $info;
            } 
        }
    }
}
