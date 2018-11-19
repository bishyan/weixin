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
        $echostr = $_GET['echostr'];
        
        // 按字典排序
        $arr = array($timestamp, $token, $nonce);
        sort($arr);
        
        // 拼接成字符串, 并用sha1加密, 然后与$signature进行校验
        $tmpstr = implode('', $arr);
        $tmpstr = sha1($tmpstr);
        
        if ($tmpstr == $signature) {
            return $echostr;
        }
        //return  'hello weixin';
    }
}
