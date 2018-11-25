<?php

/**
 *  验证是否授权
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
        if (session('?user_info') && !(cache('?'.session('user_info.openid'). 'nickname')))  {
            return;
        }
        
        if (!isset($_GET['code']) && !isset($_GET['state'])) {  
            $redirect_url = 'http://'.$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $state = md5(uniqid());  //安全验证
            session('state', $state);
            
            //如果为空则表示授权获取用户信息, 否则只取option_id
            if (!session('?user_info')) 
                $scope = 'snsapi_userinfo';
            else 
                $scope = 'snsapi_base';
            
            $jumpurl = $this->weixinObj->getWxAuthorizeUrl($redirect_url, $state, $scope);
            header('Location:' . $jumpurl);
            exit;
        } else {
            if (session('state') == $_GET['state']) {
                session('state', null);
                $res = $this->weixinObj->getUserInfo($_GET['code']);
                
                if (isset($res['nickname'])) {
                    // 获取到的是详细信息, 存入缓存和session
                    session('user_info', $res);
                    cache($res['openid'], $res);
                } else {
                    // 只获取openid, 通过openid从缓存中获取用户信息
                    $userinfo = cache($res['openid']);
                    session('user_info', $userinfo);
                }
            } 
        }
    }
}

