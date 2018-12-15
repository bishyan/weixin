<?php

namespace app\weixin\controller;
use think\Controller;


class Miniprogram extends Controller {
    public function upload_file() {
       $file = $this->request->file('fileup');
       $uploadPath = config('upload_path');
       $subDir = 'mini/';
       
       $res = $file->move($uploadPathPath . $subDir);
       
       dump($res->getSaveName()); 
       if (file_exists($uploadPath . $subDir . $res->getSaveName())) {
           
       }
    }
}
