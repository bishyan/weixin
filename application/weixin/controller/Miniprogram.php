<?php

namespace app\weixin\controller;
use think\Controller;


class Miniprogram extends Controller {
    public function upload_file() {
       dump($_FILES['fileup']);
    }
}
