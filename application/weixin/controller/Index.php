<?php

namespace app\weixin\controller;

use think\Controller;

class Index extends Controller  {
    private $weixinObj;
         
    public function _initialize() {
        parent::_initialize();
        $this->weixinObj = new Weixin();
    }
    
    public function index() {
        // 获得参数 signaturn nonce echostr timestamp, token
        $params['timestamp'] = isset($_GET['timestamp'])? $_GET['timestamp']:'';
        $params['nonce']     = isset($_GET['nonce'])? $_GET['nonce'] : '';
        $params['token']     = 'guoguo2016';
        $params['signature'] = isset($_GET['signature'])? $_GET['signature'] : '';
        $params['echostr']   = isset($_GET['echostr'])? $_GET['echostr'] : '';
        
        // 判断是验证还是其他
        if (empty($params['echostr'])) {
            # 其他
             // 1.获取到微信推送过来的post数据(xml格式)
            $postStr = file_get_contents('php://input');
            // 2. 处理数据并回复      
            if (!empty($postStr)) {
                $postObj = simplexml_load_string($postStr);

                /*<xml>  事件推送格式
                 * <ToUserName>
                 * < ![CDATA[toUser] ]></ToUserName><FromUserName>
                 * < ![CDATA[FromUser] ]></FromUserName>
                 * <CreateTime>123456789</CreateTime>
                 * <MsgType>< ![CDATA[event] ]></MsgType>
                 * <Event>< ![CDATA[subscribe] ]></Event>
                 * </xml>*/

                // 判断数据包是否是订阅的事件推送
                if (strtolower($postObj->MsgType) == 'event') {
                    // 如果是关注事件(subscribe)
                    if (strtolower($postObj->Event) == 'subscribe') {
                        $arr = array(
                            array(
                                'title' => 'guoguo',
                                'description' => "欢迎关注果果爸爸的订阅号, \n回复1: 了解果果的年龄 \n回复2: 了解果果的身高 \n回复3: 了解果果的体重 \n回复4: 果果的个人博客 \n回复5: 单图文信息",
                                'picUrl' => 'http://blog.ai702.com/public/Uploads/Admin/20180517202617219.jpg',
                                'url' => 'http://blog.ai702.com/',
                            ),
                        );
                        $this->weixinObj->responseNews($postObj, $arr);
                    }
                } else if ($postObj->MsgType == 'text') {
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
                            $this->weixinObj->responseNews($postObj, $arr);
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
                            $this->weixinObj->responseNews($postObj, $arr);
                            break;
                        default:
                            $content = '找不到相关的信息';
                            break;
                    }

                    if (isset($content)) {
                        $this->weixinObj->responseText($postObj, $content);
                    } 
                }

            }
        } else {
            $this->weixinObj->wxVerify($params);
        }        
    }
    
    
    public function responseMsg() {
        // 1.获取到微信推送过来的post数据(xml格式)
        $postStr = file_get_contents('php://input');
        // 2. 处理数据并回复      
        if (!empty($postStr)) {
            $postObj = simplexml_load_string($postStr);
            
            /*<xml>  事件推送格式
             * <ToUserName>
             * < ![CDATA[toUser] ]></ToUserName><FromUserName>
             * < ![CDATA[FromUser] ]></FromUserName>
             * <CreateTime>123456789</CreateTime>
             * <MsgType>< ![CDATA[event] ]></MsgType>
             * <Event>< ![CDATA[subscribe] ]></Event>
             * </xml>*/
            
            // 判断数据包是否是订阅的事件推送
            if (strtolower($postObj->MsgType) == 'event') {
                // 如果是关注事件(subscribe)
                if (strtolower($postObj->Event) == 'subscribe') {
                    $arr = array(
                        array(
                            'title' => 'guoguo',
                            'description' => "欢迎关注果果爸爸的订阅号, \n回复1: 了解果果的年龄 \n回复2: 了解果果的身高 \n回复3: 了解果果的体重 \n回复4: 果果的个人博客 \n回复5: 单图文信息",
                            'picUrl' => 'http://blog.ai702.com/public/Uploads/Admin/20180517202617219.jpg',
                            'url' => 'http://blog.ai702.com/',
                        ),
                    );
                    $this->weixinObj->responseNews($postObj, $arr);
                }
            } else if ($postObj->MsgType == 'text') {
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
                        $this->weixinObj->responseNews($postObj, $arr);
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
                        $this->weixinObj->responseNews($postObj, $arr);
                        break;
                    default:
                        $content = '找不到相关的信息';
                        break;
                }
                
                if (isset($content)) {
                    $this->weixinObj->responseText($postObj, $content);
                } 
            }
                      
        }
    }
}

