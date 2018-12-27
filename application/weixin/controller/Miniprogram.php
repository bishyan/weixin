<?php

namespace app\weixin\controller;
use think\Controller;


class Miniprogram extends Controller {
    
    // 发送客服消息
    public function sendCustomerMessage() {
        $appid = "wxc2328bb96ba892e8";
        $secret = "c48c0d17d0a6e11049593acd8b9e698d";

        $accessToken = $this->getAccessToken($appid, $secret);
        
        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$accessToken}";
        
        
    }
    
     // 获取access_token
    private function getAccessToken($appid, $secret) {
        $access_token = cache('miniprogram_token')['access_token'];

        if (!$access_token || cache('token_info')['expire_time'] < time()) {
            // 1. 请求url地址
            $getTokenApi = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appid}&secret={$secret}";

            $res = Weixin::http_curl($getTokenApi);
           
            $access_token = $res['access_token'];
            $token['access_token'] = $access_token;
            $token['expire_time'] = time() + 7000;
            cache('miniprogram_token', $token);          
        }
        
        return $access_token;
    }
    
    
    public function upload_file() {
       $file = $this->request->file('fileup');
       $uploadPath = config('upload_path');
       $subDir = 'mini/';
       
       $res = $file->move($uploadPath . $subDir);
       
       //dump($res->getSaveName()); 
       if (file_exists($uploadPath . $subDir . $res->getSaveName())) {
           $res = db('images')->insert([
                    'image_url'=> $subDir .$res->getSaveName(),
                ]);
                dump(['ok'=>1]);
       } else {
           dump(['ok'=>0, 'msg'=>$file->getError()]);
       }
    }
    
    public function getOpenId() {
        $appid = "wxc2328bb96ba892e8";
        $secret = "c48c0d17d0a6e11049593acd8b9e698d";
        $code = $_GET['code'];
        //dump($code);
        
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid={$appid}&secret={$secret}&js_code={$code}&grant_type=authorization_code";
        
        $res = Weixin::http_curl($url);
        
        return json_encode($res);
    }
}
