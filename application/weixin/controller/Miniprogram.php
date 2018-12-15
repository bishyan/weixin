<?php

namespace app\weixin\controller;
use think\Controller;


class Miniprogram extends Controller {
    public function upload_file() {
       $file = $this->request->file('fileup');
       $savePath = ROOT_PATH . 'public/static/images/';
       $res = $file->move($savePath);
       dump($res);
    }
}
