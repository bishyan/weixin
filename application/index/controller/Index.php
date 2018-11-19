<?php
namespace app\index\controller;

class Index
{
    // 微信验证
    public function index()
    {
        // 获得参数 signaturn nonce token timestamp
        $timestamp = $_GET['timestamp'];
        $nonce = $_GET['nonce'];
        $token = 'GUO20160704guo';
        $signature = $_GET['signature'];
        $arr = array($timestamp, $token, $nonce);
        sort($arr);
        
        $tmpstr = implode('', $arr);
        $tmpstr = sha1($tmpstr);
        
        if ($tmpstr == $signature) {
            return $_GET['echostr'];
        }
        //return  'hello weixin';
    }
}
