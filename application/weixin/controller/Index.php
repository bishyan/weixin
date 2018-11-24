<?php

/**
 * 微信公众号服务类
 */
namespace app\weixin\controller;
use think\Controller;

class Index extends Controller  {
    private $weixinObj;
         
    public function _initialize() {
        parent::_initialize();
        $this->weixinObj = new Weixin();
    }
    
    public function index() {
                                  
        // 判断是验证还是其他业务
        if ($this->request->isGet()) {
            // 验证微信服务器地址
            $this->weixinObj->wxVerify();
        } else {       
            # 其他业务
            $postData = $this->getWxReqData();
            // 判断数据类型
            if (strtolower($postData['MsgType']) == 'event') {
                // 如果是关注事件(subscribe)
                if (strtolower($postData['Event']) == 'subscribe') {
                    $arr = array(
                        array(
                            'title' => 'guoguo',
                            'description' => "欢迎关注果果爸爸的订阅号, \n回复1: 了解果果的年龄 \n回复2: 了解果果的身高 \n回复3: 了解果果的体重 \n回复4: 果果的个人博客 \n回复5: 单图文信息",
                            'picUrl' => 'http://blog.ai702.com/public/Uploads/Admin/20180517202617219.jpg',
                            'url' => 'http://blog.ai702.com/',
                        ),
                    );
                    $this->weixinObj->responseNews($postData, $arr);
                } else if (strtolower($postData['Event']) == 'click') {
                    switch($postData['EventKey']) {
                        case 'about_me':
                            $this->weixinObj->responseText($postData, '我是果果的爸爸..');
                            break;
                        case 'dVvkdk':
                            $this->weixinObj->responseText($postData, '谢谢你的称赞!');
                            break;
                        default:
                            $this->weixinObj->responseText($postData, '其他东东.');
                            break;
                    }                   

                }
                   
                    
            } else if (strtolower($postData['MsgType']) == 'text') {
                $keyword = trim($postData['Content']);
                
                //天气查询
                if (!(is_numeric($keyword))) {                       
                    $cityList = cache('city_list');
                    if (!$cityList) {
                        $url = 'http://mobile.weather.com.cn/js/citylist.xml';
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                        $res = curl_exec($ch);
                        // 将返回的数据转换成数组
                        $arr = simplexml_load_string($res);
                        $arr = json_encode($arr);
                        $arr = json_decode($arr, true);
                        // 处理数组, 转换成一维
                        $cityList = array();
                        foreach ($arr['c']['d'] as $k=>$v) {
                            $cityList[$v['@attributes']['d2']] = $v['@attributes']['d1'];
                        }
                        cache('city_list', $cityList);
                    }

                    if (array_key_exists($keyword, $cityList)) {
                        $url = "http://www.weather.com.cn/data/cityinfo/{$cityList[$keyword]}.html";
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                        $res = curl_exec($ch);
                        $res = json_decode($res, true);
                        $info = $res['weatherinfo'];

                        $content =  $info['city'] . "-实时天气: \n" . $info['weather'] . "\n最低气温: " . $info['temp1'] . "\n最高气温: " . $info['temp2'];
                    } else {
                        $content = '没有找到'.$keyword . '的天气信息.';
                    }

                    $this->weixinObj->responseText($postData, $content);
                } else {
                    switch( trim($postData['Content']) ) {
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
                            $this->weixinObj->responseNews($postData, $arr);
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
                            $this->weixinObj->responseNews($postData, $arr);
                            break;
                        default:
                            $content = '找不到和' . $postData->Content . '相关的信息';
                            break;
                    }
                }

                if (isset($content)) {
                    $this->weixinObj->responseText($postData, $content);
                } 
            }              
        }        
    }
    
    
    //获取到微信推送过来的post数据(xml格式)
    public function getWxReqData() {
        //$postStr = $GLOBALS['HTTP_RAW_POST_DATA'];
        $postStr = file_get_contents("php://input");
        //$postStr = "<xml>  <ToUserName><![CDATA[toUser]]></ToUserName>  <FromUserName><![CDATA[fromUser]]></FromUserName>  <CreateTime>1348831860</CreateTime>  <MsgType><![CDATA[event]]></MsgType>  <Event><![CDATA[CLICK]]></Event>
        //<EventKey><![CDATA[EVENTKEY]]></EventKey>  <MsgId>1234567890123456</MsgId>  </xml>";
        if (!empty($postStr)) {
            $postData = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            return json_decode(json_encode($postData), true);      
        } else {
            echo 'NULL';
            exit;
        }
    }
    
    // 群发预览
    public function previewSend() {
        /* 单文本
        $array = array(
            'touser' => 'oBqKY1ABzkfRtgZNxu-VzrV5Kt3M',
            'text' => array('content' => urlencode('果果唱歌非常好听! very,very Good!')),
            'msgtype' => 'text'
        );*/
        
        /* 单图文
            {
                "touser":"OPENID",
                "image":{      
                        "media_id":"123dsdajkasd231jhksad"
                        },
                "msgtype":"image" 
            }
         */
        $array = array(
            'touser' => 'oBqKY1ABzkfRtgZNxu-VzrV5Kt3M',
            'image' => array(
                'media_id' => '123dsdajkasd231jhksad',
            ),
            'msgtype' => 'image'
        );
        // 3. 转换->json
        $postJson = urldecode(json_encode($array));
        //dump($postJson); exit;
        $this->weixinObj->sendMsgAllPreview($postJson);
    }
    
    // 群发消息
    public function sendMsg() {
        /*{ tag群发文本格式
            "filter":{
               "is_to_all":false,
               "tag_id":2
            },
            "text":{
               "content":"CONTENT"
            },
             "msgtype":"text"
            }*/
//        $arr = array(
//            'filter' => array(
//                'is_to_all' => false,
//                'tag_id' => 1,
//            ),
//            'text' => array(
//                'content' => urlencode('果果唱歌非常好听!果果唱歌非常好听!'),
//            ),
//            'msgtype' => 'text',
//        );
        
        /*{  ID群发文本格式
            "touser":[
             "OPENID1",
             "OPENID2"
            ],
             "msgtype": "text",
             "text": { "content": "hello from boxer."}
        }*/
        
        $arr = array(
            'touser' => array('oBqKY1K2m-zHHaKQ35gJ6Ho_-LYg', 'oBqKY1ABzkfRtgZNxu-VzrV5Kt3M'),
            'msgtype' => 'text',
            'text' => array(
                'content' => urlencode('果果唱歌非常好听!果果唱歌非常好听!果果唱歌非常好听!'),
            ),
        );
        
        $array = urldecode(json_encode($arr));
        //dump($array);exit;
        //$this->weixinObj->sendMsgAllByTag($array);
        $this->weixinObj->sendMsgAll($array, 'id');
    }
    
    // 创建微信菜单
    public function definedItem() {
        $access_token = $this->weixinObj->getWxAccessToken();
        //dump($access_token);
        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=" . $access_token;
        $postArr = array(
            'button' => array(
                array(
                    'name' => urlencode('关于我'),
                    'type' => 'click',
                    'key'  => 'about_me',
                ),  // 第一个一级菜单
                array(
                    'name' => urlencode('生活助手'),
                    'sub_button' => array(
                        array(
                            'type' => 'view',
                            'name' => urlencode('百度搜索'),
                            'url' => 'http://www.baidu.com'
                        ),
                        array(
                            'type'  => 'view',
                            'name'  => urlencode('坐车'),
                            'url'   => 'http://zuoche.com/touch/searincity.jspx',
                        ),
                        array(
                            'type' => 'view',
                            'name' => urlencode('精选文章'),
                            'url'  => 'https://mp.weixin.qq.com/mp/homepage?__biz=MzU0NTgyMDIyMA==&hid=3&sn=d24108c93567a9278f2a2c0a35aad093'
                        ),
                    ),
                    
                ),  // 第二个一级菜单
                array(
                    'name' => urlencode('扫码'),
                    'sub_button' => array(
                        array(
                            'type' => 'location_select',
                            'name' => urlencode('发送位置'),
                            'key'  => 'rselfmenu-kddk',
                        ),
                        array(
                            'type' => 'scancode_push',
                            'name' => urlencode('扫码事件'),
                            'key'  => 'reskksk_0_1',
                            'sub_button' => array(),
                        ),
                    )
                ), // 第三个一级菜单
            ),      
        );
        $postJson = urldecode(json_encode($postArr));
        $res = $this->weixinObj->http_curl($url, 'post', $postJson);
        var_dump($res);
    }
    
    // 发送模板消息 
    public function sendTemplateMsg() {
        /*{ 模板消息格式
           "touser":"OPENID",  
           "template_id":"ngqIpbwh8bUfcSsECmogfXcV14J0tQlEpBO27izEYtY",  
           "url":"http://weixin.qq.com/download",    
           "miniprogram":{
             "appid":"xiaochengxuappid12345",
             "pagepath":"index?foo=bar"
           },          
           "data":{
                   "first": {
                       "value":"恭喜你购买成功！",
                       "color":"#173177"
                   },

           }
       }*/
        
        $array = array(
            'touser' => 'oBqKY1ABzkfRtgZNxu-VzrV5Kt3M',
            'template_id' => 'nZR3hfPuRW7rBjIE1brRQaNn_SczMSXxaJLrHmi9GMM',
            'url' => 'http://www.baidu.com',
            'data' => array(
                'name' => array('value' => '果果', 'color'=> '#173177'),
                'age'  => array('value'=>'2岁了', 'color' => '#173177'),
            )
        );
        
        $postJson= json_encode($array);
        $this->weixinObj->sendWxTemplateMsg($postJson);
    }
    
    public function getBaseInfo() {
        // 1. 获取到code
        $appid = 'wxf90f6aec3e2fcd91';
        $redirect_url = urlencode('http://weixin.ai702.com/weixin/index/getUserOpenId');
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".$redirect_url."&response_type=code&scope=snsapi_base&state=123456#wechat_redirect";
        header('Location: ' . $url);
    }
    
    public function getUserOpenId() {
        //2.获取到网页授权的access_token
        $appid = 'wxf90f6aec3e2fcd91';
        $secret = '1830b09c31cdf066fa299025c326b8f3';
        $code = $_GET['code'];
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$secret."&code=".$code."&grant_type=authorization_code";
        //3.摘取到用户的open_id
        $res = $this->weixinObj->http_curl($url);
        dump($res);
    }
}

