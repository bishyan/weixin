<?php

/**
 *  微信网页授权的管理类, 需要网页授权的页面统一继承此类
 *  功能: 判断是否授权, 如果已经有用户信息, 则返回
 *  否则去获取授权信息
 */
namespace app\weixin\controller;
use think\Controller;

class Authorize extends Controller {
    
    public function __construct(\think\Request $request = null) {
        parent::__construct($request);
        $this->checkAuth();
    }
    
    //检查授权
    public function checkAuth() {
        // 存在用户option_id, 并且nickname值不为空(说明已授权获取用户信息)
        /*if (session('?user_info') && !empty(cache(session('user_info.openid'))['nickname']))  {
            echo '不用验证<br>';
            return;
        }*/
        
        $actionName = $this->request->action(); // 请求的动作名
        //判断是否是指定要获取授权信息的动作
        if ($actionName == 'register') {
            if (session('?user_info.nickname')) {
                //已有信息, 无需再取
                return;
            }
        } else {
            if (session('?user_info')) {
                //不是指定动作, 只要有信息,就不再去取
                return;
            }
        }
        
        
        if (!isset($_GET['code']) && !isset($_GET['state'])) {  
            // 回调url:本页面
            $redirect_url = 'http://'.$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $state = md5(uniqid());  //安全验证
            session('state', $state);  
                        
            // 指定register页面去获取用户授权信息, 其他动作只取openid
            if ($actionName == 'register') {
                $scope = 'snsapi_userinfo';
            } else {
                $scope = 'snsapi_base';
            }
            
            /*
            //判断是否是第一次访问(session为空), 或者session存在但没有其他信息, 
             * 则取用户信息, 否则取option_id
            if (!session('?user_info') || (session('?user_info.openid') && 
             * empty(cache(session('user_info.openid'))['nickname']))) 
                $scope = 'snsapi_userinfo';
            else 
                $scope = 'snsapi_base';
            */
            
            //如果用户同意授权，页面将跳转至 redirect_uri/?code=CODE&state=STATE
            $jumpurl = Weixin::getWxAuthorizeUrl($redirect_url, $state, $scope);
            header('Location:' . $jumpurl);
            exit;
        } else {
            if (session('state') == $_GET['state']) {
                session('state', null);
                $res = Weixin::getUserInfo($_GET['code']);
                
                if (isset($res['nickname'])) {
                    // 获取到的是详细信息, 存入缓存和session
                    //echo '详情<br>';
                    //dump($res);
                    session('user_info', $res);
                    cache($res['openid'], $res);
                } else {
                    //echo '简短<br>';
                    // 只获取openid, 通过openid从缓存中获取用户信息
                    //$userinfo = cache($res['openid']);
                    //session('user_info', $userinfo);
                    
                    // 只存openid
                    if ( cache('?'.$res['openid'])){ 
                        $info = cache($res['openid']);
                    } else {
                        $info =  $res['openid'];
                        cache($res['openid'], $info);
                    }
                    session('user_info', $info);
                }
            } else {
                
                $this->error('非法请求!', url('/weixin/wxshop/index'));
            }
        }
    }
}

