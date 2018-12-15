<?php

namespace app\weixin\controller;
use think\Controller;


class Miniprogram extends Controller {
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
        dump($code);
        
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid={$appid}&secret={$secret}&js_code={$code}&grant_type=authorization_code";
        
        $res = Weixin::http_curl($url);
        
        dump($res);
    }
}
