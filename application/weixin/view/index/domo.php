<?php
//设置一下时区
date_default_timezone_set('Asia/Shanghai');
 
$jssdk = new JSSDK("wxafa5f3**5b3a7617", "bbde89d0d696cee2fb01e3054**d7ee8");
$signPackage = $jssdk->GetSignPackage();
?>
 
 
<button id="btn" style="margin-top: 200px;margin-left: 100px;">上传</button>
<div id="pic"></div>
 
    <script type="text/javascript" src="jquery-1.8.3-min.js"></script>
    <script type="text/javascript" src="http://res.wx.qq.com/open/js/jweixin-1.2.0.js"></script>
 
    <script type="text/javascript">
        $("#btn").click(function () {
            wx.config({
                debug: false,
                appId: '<?php echo $signPackage["appId"];?>',
                timestamp: <?php echo $signPackage["timestamp"];?>,
                nonceStr: '<?php echo $signPackage["nonceStr"];?>',
                signature: '<?php echo $signPackage["signature"];?>',
                jsApiList: [
                    // 所有要调用的 API 都要加到这个列表中
                    'chooseImage',
                    'previewImage',
                    'uploadImage',
                    'downloadImage'
                ]
            });
 
            wx.ready(function () {
                wx.checkJsApi({
                    jsApiList: [
                        'chooseImage',
                        'previewImage',
                        'uploadImage',
                        'downloadImage'
                    ],
                    success: function (res) {
                        //alert(JSON.stringify(res));
                        //alert(JSON.stringify(res.checkResult.getLocation));
                        if (res.checkResult.getLocation == false) {
                            alert('你的微信版本太低，不支持微信JS接口，请升级到最新的微信版本！');
                            return;
                        }else{
                            wxChooseImage();
                        }
                    }
                });
            });
            wx.error(function(res){
                // config信息验证失败会执行error函数，如签名过期导致验证失败，具体错误信息可以打开config的debug模式查看，也可以在返回的res参数中查看，对于SPA可以在这里更新签名。
                alert("验证失败，请重试！");
                wx.closeWindow();
            });
 
        });
        var images = {
            localId: [],
            serverId: []
        };
        //拍照或从手机相册中选图接口
        function wxChooseImage() {
 
            wx.chooseImage({
                success: function(res) {
                    images.localId = res.localIds;
                    alert('已选择 ' + res.localIds.length + ' 张图片');
 
                    if (images.localId.length == 0) {
                        alert('请先使用 chooseImage 接口选择图片');
                        return;
                    }
                    var i = 0, length = images.localId.length;
                    images.serverId = [];
                    function upload() {
                        //图片上传
                        wx.uploadImage({
                            localId: images.localId[i],
                            success: function(res) {
                                i++;
                                images.serverId.push(res.serverId);
                                //图片上传完成之后，进行图片的下载，图片上传完成之后会返回一个在腾讯服务器的存放的图片的ID--->serverId
                                wx.downloadImage({
                                    serverId: res.serverId, // 需要下载的图片的服务器端ID，由uploadImage接口获得
                                    isShowProgressTips: 1, // 默认为1，显示进度提示
                                    success: function (res) {
                                        var localId = res.localId; // 返回图片下载后的本地ID
 
                                        //通过下载的本地的ID获取的图片的base64数据，通过对数据的转换进行图片的保存
                                        wx.getLocalImgData({
                                            localId: localId, // 图片的localID
                                            success: function (res) {
                                                var localData = res.localData; // localData是图片的base64数据，可以用img标签显示
 
                                                //通过ajax来将base64数据转换成图片保存在本地
                                                $.ajax({
                                                    url: "./demodeal.php",
                                                    type: "post",
                                                    async: "false",
                                                    dataType: "html",
                                                    data: {
                                                        localData: localData,
                                                    },
                                                    success: function (data) {
                                                       var  mydata = JSON.parse(data);
                                                        if(mydata.code == '0001'){
                                                            alert('已上传：' + i + '/' + length);
                                                        }else{
                                                            alert('第：' + i + '/' + length+'上传失败');
                                                        }
                                                    },
                                                    error: function (XMLHttpRequest, textStatus, errorThrown) {
                                                        alert(errorThrown);
                                                    },
                                                });
                                                $("#pic").append("<img src='"+localData+"'>");
                                            }
                                        });
                                    }
                                });
                                if (i < length) {
                                    upload();
                                }
                            },
                            fail: function(res) {
                                alert(JSON.stringify(res));
                            }
                        });
                    }
                    upload();
                }
            });
        }
 
 
    </script>
<?php
/**
 * 生成signature
 */
class JSSDK {
    private $appId;
    private $appSecret;
 
    public function __construct($appId, $appSecret) {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
    }
 
    public function getSignPackage() {
        $jsapiTicket = $this->getJsApiTicket();
 
        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
 
        $timestamp = time();
        $nonceStr = $this->createNonceStr();
 
        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr×tamp=$timestamp&url=$url";
 
        $signature = sha1($string);
 
        $signPackage = array(
            "appId"     => $this->appId,
            "nonceStr"  => $nonceStr,
            "timestamp" => $timestamp,
            "url"       => $url,
            "signature" => $signature,
            "rawString" => $string
        );
        return $signPackage;
    }
 
    private function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
 
    private function getJsApiTicket() {
        // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
        $data = @json_decode(file_get_contents("./jsapi_ticket.json"));
        //$data = @json_decode(file_get_contents("./jsapi_ticket.json"),true);//如果这是加true的话，将按照数组来处理，不加的将会是数组对象
//        echo "jsapi_ticket";
//        echo gettype($data);
        if ($data->expire_time < time()) {
 
            $accessToken = $this->getAccessToken();
            // 如果是企业号用以下 URL 获取 ticket
            // $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$accessToken";
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
            $res = json_decode($this->httpGet($url));
 
            $ticket = $res->ticket;
            if ($ticket) {
                @$data->expire_time = time() + 7000;
                @$data->jsapi_ticket = $ticket;
                $fp = fopen("./jsapi_ticket.json", "w");
                fwrite($fp, json_encode($data));
                fclose($fp);
            }
        } else {
            $ticket = $data->jsapi_ticket;
        }
 
        return $ticket;
    }
 
    private function getAccessToken() {
        // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
        $data = @json_decode(file_get_contents("./access_token.json"));
//        echo "access_token";
//        echo gettype($data);
        if (@$data->expire_time < time()) {
            // 如果是企业号用以下URL获取access_token
            // $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$this->appId&corpsecret=$this->appSecret";
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appId&secret=$this->appSecret";
            $res = json_decode($this->httpGet($url));
            $access_token = $res->access_token;
            if ($access_token) {
                @$data->expire_time = time() + 7000;
                @$data->access_token = $access_token;
                $fp = fopen("./access_token.json", "w");
                fwrite($fp, json_encode($data));
                fclose($fp);
            }
        } else {
            $access_token = $data->access_token;
        }
        return $access_token;
    }
 
    private function httpGet($url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_URL, $url);
 
        $res = curl_exec($curl);
        curl_close($curl);
 
        return $res;
    }
}


