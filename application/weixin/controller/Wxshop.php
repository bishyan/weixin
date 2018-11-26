<?php

/**
 * 微信商城类
 */
namespace app\weixin\controller;
//use think\Controller;

class WxShop extends Authorize{
         
    //首页
    public function index() {
                         
          echo '首页';          
             
    }
    
    // 获取用户的信息
    public function getBaseInfo() {
        $url = url('/weixin/shop/test');
        dump($_SESSION);
        
        echo "<a href='".$url."'>测试页面</a>";

    }
    
    public function test() {
        //session(null);
        dump($_SESSION);
        echo '这是一个测试页面<br>';
        
        $url = url('/weixin/shop/test2');
        echo "<a href='".$url."'>测试页面2</a>";
    }
    
    public function test2() {

        dump($_SESSION);
        echo '这是第二个测试页面';
    }
    
    
    // 注册
    public function register() {
        echo '注册页面';
        dump($_SESSION);
    }
    
    
    public function getUserInfo() {
        $code = $_GET['code'];
        dump($code);
        $res = Weixin::getUserInfo($code);
        
        dump($res);
    }
}




