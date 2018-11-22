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
        $this->definedItem(); exit;
                                  
        // 判断是验证还是其他业务
        if ($this->request->isGet()) {
            // 验证微信服务器地址
            $this->weixinObj->wxVerify();
        } else {            
            # 其他业务
            $postObj = $this->getWxReqData();

            // 判断数据类型
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
            } else if (strtolower($postObj->MsgType) == 'text') {
                $postArr = json_decode(json_encode($postObj), true);
                $keyword = trim($postArr['Content']);
                
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

                    $this->weixinObj->responseText($postObj, $content);
                } else {
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
                            $content = '找不到和' . $postObj->Content . '相关的信息';
                            break;
                    }
                }

                if (isset($content)) {
                    $this->weixinObj->responseText($postObj, $content);
                } 
            }              
        }        
    }
    
    
    //获取到微信推送过来的post数据(xml格式)
    public function getWxReqData() {
        //$postStr = $GLOBALS['HTTP_RAW_POST_DATA'];
        $postStr = file_get_contents("php://input");
        //$postStr = "<xml>  <ToUserName><![CDATA[toUser]]></ToUserName>  <FromUserName><![CDATA[fromUser]]></FromUserName>  <CreateTime>1348831860</CreateTime>  <MsgType><![CDATA[text]]></MsgType>  <Content><![CDATA[this is a test]]></Content>  <MsgId>1234567890123456</MsgId>  </xml>";
        if (!empty($postStr)) {
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                    
        } else {
            echo 'NULL';
            exit;
        }
        
        return $postObj;
    }
    

    
    // 创建微信菜单
    public function definedItem() {
        $access_token = $this->weixinObj->getWxAccessToken();
        var_dump($access_token);
        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=" . $access_token;
        $postArr = array(
            'button' => array(
                array(
                    'name' => '果果宝宝',
                    'type' => 'click',
                    'key'  => 'vkakak_dkkd',
                ),  // 第一个一级菜单
                array(
                    'name' => '小蜜',
                    'sub_button' => array(
                        array(
                            'type' => 'view',
                            'name' => '搜索',
                            'url' => 'http://www.soso.com'
                        ),
                        array(
                            'type'  => 'miniprogram',
                            'name'  => 'wxa',
                            'url'   => 'http://mp.weixin.qq.com',
                            'appid' => 'wx286b93c14bbf93aa',
                            'pagepath' => 'pages/lunar/inde'
                        ),
                        array(
                            'type' => 'click',
                            'name' => '赞一下我们',
                            'key'  => 'dVvkdk'
                        ),
                    ),
                    
                ),  // 第二个一级菜单
                array(
                    'name' => '扫码',
                    'sub_button' => array(
                        array(
                            'type' => 'scancode_waitmsg',
                            'name' => '扫码带提示',
                            'key'  => 'rselfmenu-kddk',
                            'sub_button' => array(),
                        ),
                        array(
                            'type' => 'scancode_push',
                            'name' => '扫码事件',
                            'key'  => 'reskksk_0_1',
                            'sub_button' => array(),
                        ),
                    )
                ), // 第三个一级菜单
            ),
      
        );
        $postJson = urlencode(json_encode(urlencode($postArr)));
        $res = $this->weixinObj->http_curl($url, 'post', $postJson);
        var_dump($res);
    }
}

