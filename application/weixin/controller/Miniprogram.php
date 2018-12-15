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
}
