<?php
namespace app\index\controller;
use think\Controller;
use think\Session;
use think\Request;

define("TOKEN", "xiangqi");
class Wetest extends Controller
{
    public function index(){
        if (isset($_GET['echostr'])) {
            $this->valid();
        }else{
            $this->definedItem();
            $this->responseMsg();
        }
    }


    public function valid()
        {
            $echoStr = $_GET["echostr"];


            //valid signature , option
            if($this->checkSignature() && $echoStr){
            echo $echoStr;
            exit;
            }
        }


    private function checkSignature()
    {
        // you must define TOKEN by yourself
        if (!defined("TOKEN")) {
            throw new Exception('TOKEN is not defined!');
        }
        //获取微信提交的参数
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];    
        $token = TOKEN;
        //微信提交参数放入数组
        $tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule字典序排序
        sort($tmpArr, SORT_STRING);
        //拼接字符串
        $tmpStr = implode( $tmpArr );
        //加密
        $tmpStr = sha1( $tmpStr );
        //检验signature
        if( $tmpStr == $signature){
            return true;
        }else{
            return false;
        }
    }

    public function responseMsg()
    {
        //get post data, May be due to the different environments
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

              //extract post data
        if (!empty($postStr)){
            /* libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
               the best way is to check the validity of xml by yourself */
            libxml_disable_entity_loader(true);
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $RX_TYPE = trim($postObj->MsgType);


            switch ($RX_TYPE)
            {
                case "text":
                	if (mb_substr($postObj->Content,-2,2,"UTF-8") == '天气') {
                		//$resultStr = $this->receiveText($postObj);
                		$resultStr = $this->receiveWeather($postObj);
                	}else{
						$resultStr = $this->receiveText($postObj);
                	}                   
                    break;
                case "image":
                    $resultStr = $this->receivePic($postObj);
                    break;
                case "location":
                    $resultStr = $this->receiveLocation($postObj);
                    break;
                case "voice":
                    $resultStr = $this->receiveVoice($postObj);
                    break;
                case "video":
                    $resultStr = $this->receiveVideo($postObj);
                    break;
                case "link":
                    $resultStr = $this->receiveLink($postObj);
                    break;
                case "event":
                    $resultStr = $this->receiveEvent($postObj);
                    break;
                default:
                    $resultStr = "unknow msg type: ".$RX_TYPE;
                    break;
            }
            echo $resultStr;


        }else {
        echo "";
        exit;
        }
    }

    //文本回复
    private function transmitText($object, $content, $flag = 0)
    {
        $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                    <FuncFlag>%d</FuncFlag>
                    </xml>";
        $resultStr = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content, $flag);
        return $resultStr;
    }
    //天气回复
    private function transmitWeather($object,$flag)
    {
         $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                    <FuncFlag>%d</FuncFlag>
                    </xml>";
        //$arr="tianqi ";
        //$arr=$this->weatherGet(mb_substr($object->Content,0,strlen($object->Content)-2,"UTF-8"));
        //获取城市名
        $arr=$this->weatherGet(str_replace("天气","",$object->Content));
        if (count($arr) == 2) {
        	$content="该城市暂时无法提供天气服务!";
        }else{
/*array(1) {
  ["results"]=>
  array(1) {
    [0]=>
    array(3) {
      ["location"]=>
      array(6) {
        ["id"]=>
        string(12) "WX4FBXXFKE4F"
        ["name"]=>
        string(6) "北京"
        ["country"]=>
        string(2) "CN"
        ["path"]=>
        string(20) "北京,北京,中国"
        ["timezone"]=>
        string(13) "Asia/Shanghai"
        ["timezone_offset"]=>
        string(6) "+08:00"
      }
      ["daily"]=>
      array(1) {
        [0]=>
        array(12) {
          ["date"]=>
          string(10) "2017-08-20"
          ["text_day"]=>
          string(3) "阴"
          ["code_day"]=>
          string(1) "9"
          ["text_night"]=>
          string(6) "多云"
          ["code_night"]=>
          string(1) "4"
          ["high"]=>
          string(2) "30"
          ["low"]=>
          string(2) "23"
          ["precip"]=>
          string(0) ""
          ["wind_direction"]=>
          string(6) "东南"
          ["wind_direction_degree"]=>
          string(3) "135"
          ["wind_speed"]=>
          string(2) "10"
          ["wind_scale"]=>
          string(1) "2"
        }
      }
      ["last_update"]=>
      string(25) "2017-08-20T11:00:00+08:00"
    }
  }
}*/
        	//$content='keyi';
         	$content='地区：'.$arr['results']['0']['location']['path']."\n时区：".$arr['results']['0']['location']['timezone']."\n天气：".$arr['results']['0']['daily']['0']['text_day']."->".$arr['results']['0']['daily']['0']['text_night']."\n"."最高气温：".$arr['results']['0']['daily']['0']['high']."\n"."最低气温：".$arr['results']['0']['daily']['0']['low']."\n"."风向：".$arr['results']['0']['daily']['0']['wind_direction']."\n"."风速：".$arr['results']['0']['daily']['0']['wind_speed']."\n"."更新时间：".$arr['results']['0']['last_update']."\n数据来源：心知天气";       	
        }
        $resultStr = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content,$flag);
        return $resultStr;   	
    }
    //图文回复
    private function transmitPic($object)
    {

		$arr = array(
				array(
					'title'=>'360',
					'description'=>'360链接',
					'picurl'=>'https://p4.ssl.qhimg.com/t01a334284ab2c07df4.png',
					'url'=>'https://hao.360.cn/',
				),
				array(
					'title'=>'百度',
					'description'=>'百度链接',
					'picurl'=>'https://ss0.bdstatic.com/5aV1bjqh_Q23odCf/static/superman/img/logo/bd_logo1_31bdc765.png',
					'url'=>'https://www.baidu.com',
				),
		);
		$textStr =	"<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[%s]]></MsgType>
					<ArticleCount>".count($arr)."</ArticleCount>
					<Articles>";
		foreach ($arr as $key => $value) {
			$textStr .="<item>
						<Title><![CDATA[".$value['title']."]]></Title> 
						<Description><![".$value['description']."]]></Description>
						<PicUrl><![CDATA[".$value['picurl']."]]></PicUrl>
						<Url><![CDATA[".$value['url']."]]></Url>
						</item>";
		}
		$textStr .="</Articles>
					</xml>";
		$resultStr = sprintf($textStr,$object->FromUserName,$object->ToUserName,time(),'news');
		return $resultStr;
	}

    //1.事件消息
    private function receiveEvent($object)
    {
        $contentStr = "";
        switch (strtolower($object->Event))
        {
            case "subscribe":
                switch ($object->EventKey) {
                    case 'qrscene_3000':
                        $contentStr = "欢迎通过扫描永久二维码关注我！";
                        $resultStr = $this->transmitText($object, $contentStr);
                        return $resultStr;
                        break;
                    case 'qrscene_2000':
                        $contentStr = "欢迎通过扫描永久二维码关注我！";
                        $resultStr = $this->transmitText($object, $contentStr);
                        return $resultStr;
                        break;      
                    default:
                        $contentStr = "欢迎关注苏苏的微信！";
                        break;
                }

                    break;
                //$contentStr = "欢迎关注苏苏的微信！";
                //break;
            case "unsubscribe":
                $contentStr = "";
                break;
            case 'scan':
                switch ($object->EventKey) {
                    case '2000':
                        $contentStr = "您已关注，您扫描了临时二维码";
                        $resultStr = $this->transmitText($object, $contentStr);
                        return $resultStr;
                        break;
                    case '3000':
                        $contentStr = "您已关注，您扫描了永久二维码";
                        $resultStr = $this->transmitText($object, $contentStr);
                        return $resultStr;
                        break;
                    
                    default:
                        break;
                }
                $contentStr = "";
                break;
            case "click":
                switch ($object->EventKey)
                {
                    case 'item1':
                        $contentStr = "你点击了菜单: 图文";
                        $resultStr = $this->transmitPic($object);
                        return $resultStr;
                        break;
                    case 'songs':
                        $contentStr = "你点击了菜单: 歌曲";
                        break;
                    default:
                        $contentStr = "你点击了菜单: ".$object->EventKey;
                        break;
                }
                break;
            case 'scancode_waitmsg':
                        $contentStr = "这是一次扫码事件！";
                        $resultStr = $this->transmitText($object,$contentStr);
                        return $resultStr;
                        break;
                break;
            default:
                $contentStr = "receive a new event: ".$object->Event;
                break;
        }
        $resultStr = $this->transmitText($object, $contentStr);
        return $resultStr;
    }

    //2.pic消息
    private function receivePic($object)
    {
        //$funcFlag = 0;
        //$contentStr = "你发送的是文本，内容为：".$object->Content;
        $resultStr = $this->transmitPic($object);
        return $resultStr;
    }

    //2.文本消息
    private function receiveText($object)
    {
        $funcFlag = 0;
        $contentStr = "发送‘城市名天气’可查询天气;\n发送位置可查询经纬度。";
        $resultStr = $this->transmitText($object, $contentStr, $funcFlag);
        return $resultStr;
    }


    //3.图片消息
    private function receiveImage($object)
    {
        $funcFlag = 0;
        $contentStr = "你发送的是图片，地址为：".$object->PicUrl;
        $resultStr = $this->transmitText($object, $contentStr, $funcFlag);
        return $resultStr;
    }


    //4.语音消息
    private function receiveVoice($object)
    {
        $funcFlag = 0;
        $contentStr = "你发送的是语音，媒体ID为：".$object->MediaId;
        $resultStr = $this->transmitText($object, $contentStr, $funcFlag);
        return $resultStr;
    }


    //5.视频消息
    private function receiveVideo($object)
    {
        $funcFlag = 0;
        $contentStr = "你发送的是视频，媒体ID为：".$object->MediaId;
        $resultStr = $this->transmitText($object, $contentStr, $funcFlag);
        return $resultStr;
    }


    //6.位置消息
    private function receiveLocation($object)
    {
        $funcFlag = 0;
        $contentStr = "您发送的是位置，\n纬度为：".$object->Location_X."；\n"."经度为：".$object->Location_Y."；\n"."缩放级别为：".$object->Scale."；\n"."位置为：".$object->Label;
        $resultStr = $this->transmitText($object, $contentStr, $funcFlag);
        return $resultStr;
    }


    //7.链接消息
    private function receiveLink($object)
    {
        $funcFlag = 0;
        $contentStr = "你发送的是链接，标题为：".$object->Title."；内容为：".$object->Description."；链接地址为：".$object->Url;
        $resultStr = $this->transmitText($object, $contentStr, $funcFlag);
        return $resultStr;
    }

    //8.查询天气消息
    private function receiveWeather($object)
    {
        $funcFlag = 0;
        //$contentStr = "你发送的是文本，内容为：".$object->Content;
        $resultStr = $this->transmitWeather($object,$funcFlag);
        return $resultStr;
    }


    //获取天气数据
    public function weatherGet($city)
    {
        // 心知天气接口调用凭据
        $key = 'svzvpjpbhotwjh8q'; // 测试用 key，请更换成您自己的 Key
        $uid = 'UF4A704F31'; // 测试用 用户ID，请更换成您自己的用户ID
        // 参数
        $api = 'https://api.seniverse.com/v3/weather/daily.json'; // 接口地址
        $location = $city; // 城市名称。除拼音外，还可以使用 v3 id、汉语等形式
        // 生成签名。文档：https://www.seniverse.com/doc#sign
        $param = [
            'ts' => time(),
            'ttl' => 300,
            'uid' => $uid,
        ];
        $sig_data = http_build_query($param); // http_build_query会自动进行url编码
        // 使用 HMAC-SHA1 方式，以 API 密钥（key）对上一步生成的参数字符串（raw）进行加密，然后base64编码
        $sig = base64_encode(hash_hmac('sha1', $sig_data, $key, TRUE));
        // 拼接Url中的get参数。文档：https://www.seniverse.com/doc#daily
        $param['sig'] = $sig; // 签名
        $param['location'] = $location;
        $param['start'] = 0; // 开始日期。0=今天天气
        $param['days'] = 1; // 查询天数，1=只查一天
        // 构造url
        $url = $api . '?' . http_build_query($param);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output=curl_exec($ch);
        curl_close($ch);
        //echo "<pre>";
        //var_dump(json_decode($output,true)); 
        return json_decode($output,true); 
        //echo "</pre>";    
    }

    //获取access_token
 /*   public function getAccessToken()
    {
    	$appid='wxe4c13a7af1b86b1e';
    	$appsecret = '71f551559589dd799507e4d46bf7b1d5';
    	$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$appsecret;

    	$ch= curl_init();
    	curl_setopt($ch,CURLOPT_URL,$url);
    	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    	$res = curl_exec($ch);
    	if (curl_errno($ch)) {
    		//echo "string1";
    		var_dump(curl_error($ch));
    	}
    	curl_close($ch);
    	$arr = json_decode($res,true);
    	//echo "2";
    	var_dump($arr);   	
    }
*/
   //获取微信服务器ip地址
    public function getServiceIp()
    {
    	$accessToken = $this->getWxAccessToken();
    	$url = 'https://api.weixin.qq.com/cgi-bin/getcallbackip?access_token='.$accessToken;
    	$ch = curl_init();
    	curl_setopt($ch,CURLOPT_URL,$url);
    	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    	$res = curl_exec($ch);
    	if (curl_errno($ch)) {
    	//echo "string1";
    		var_dump(curl_error($ch));
    	}
    	curl_close($ch);
    	$arr = json_decode($res,true);
    	echo "<pre>";
    	var_dump($arr);
    	echo "</pre>";
    }

    public function http_curl($url,$type='get',$res='json',$arr)
    {
        //获取
        //1.初始化curl
        $ch = curl_init();
        //echo "test";
        //2.设置curl参数
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        if ($type=='post') {
            curl_setopt($ch,CURLOPT_POST,1);
            curl_setopt($ch,CURLOPT_POSTFIELDS,$arr);
        }
        //采集
        $output = curl_exec($ch);
        if($res=='json'){
            if(curl_errno($ch)){
                //请求失败，返回错误信息
                return curl_error($ch);
            }else{
                //请求成功，返回错误信息
                return json_decode($output,true);
            }
        }
        //4.关闭
        curl_close($ch);
        //echo var_dump($output);           
    }

    //返回access_token
    public function getWxAccessToken()
    {//echo $_SESSION['access_token']."1";
        //将access_token存在session/cookie中
        if (Session::get('access_token') && Session::get('expire_in')>time()) {
            //如果access_token存在并且没有过期
            return Session::get('access_token');
        }else{
            $appid='wxe4c13a7af1b86b1e';
            $appsecret = '71f551559589dd799507e4d46bf7b1d5';
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$appsecret;
            $res=$this->http_curl($url,'get','json','');
            $access_token=$res['access_token'];

            //将重新获取到的access_token存到$_SESSION
            Session::set('access_token',$access_token);
            Session::set('expire_in',time()+7000);
            //echo $_SESSION['access_token']=$access_token;
            //echo $_SESSION['expire_in']=time()+7000;
            return $access_token;
        }

    }

    public function definedItem()
    {
        //创建微信菜单
        //目前微信接口的调用方法都是通过curl post/get
        $access_token = $this->getWxAccessToken();
        $url= 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$access_token;

        $postArr = array(
                'button'=>array(
                    array(
                        'name'=>urlencode('图文'),
                        'type'=>'click',
                        'key'=>'item1',

                    ),//第一个一级菜单
                    array(
                        'name'=>urlencode('信息'),
                        'sub_button'=>array(
                                array(
                                    'name'=>urlencode('我的信息'),
                                    'type'=>'view',
                                    'url'=>'http://www.wwfd.club/index/wetest/getUserDetail',
                                    ),//第一个二级菜单
                                array(
                                    'name'=>urlencode('百度'),
                                    'type'=>'view',
                                    'url'=>'http://www.baidu.com',
                                    ),//第二个二级菜单
                                array(
                                    'name'=>urlencode('微信分享'),
                                    'type'=>'view',
                                    'url'=>'http://www.wwfd.club/index/wetest/shareWx',
                                )//第三个二级菜单                               
                            )

                    ),//第二个一级菜单
                    array(
                        "name"=>urlencode("扫码"), 
                        "sub_button"=>array (
                            array(                          
                                "type"=> "scancode_waitmsg", 
                                "name"=> urlencode("扫码带提示"), 
                                "key"=>"1", 
                                //"sub_button"=> array()
                                //可能是自定义事件吧
                            ), 
                            array(
                                "type"=> "scancode_push", 
                                "name"=> urlencode("普通扫码"), 
                                "key"=> "2", 
                                //"sub_button"=> array()
                                //同普通微信扫码
                            )
                        )
                    ),//第三个一级菜单
                )
            );
        $postJson = urldecode(json_encode($postArr));
        $res = $this->http_curl($url,'post','json',$postJson);
        //var_dump($res);

    }

    //群发接口
    public function sendMsgAll()
    {
        //1.获取全局access_token
        $access_token = $this->getWxAccessToken();
        //2.组装群发接口数据
        $url='https://api.weixin.qq.com/cgi-bin/message/mass/preview?access_token='.$access_token;
         /*   {     
                "touser":"OPENID",
                "text":{           
                       "content":"CONTENT"            
                       },     
                "msgtype":"text"
            }   */  
        $array = array(
            'touser'=>'oBS-Qvw72LqbsJCmvtEe_zzCcFew',//微信用户openid
            'text'=>array('content'=>'文字'),//文本内容
            'msgtype'=>'text',
            );
        //3.将array->json
        echo $postJson = json_encode($array);
        //4.调用curl
        $res = $this->http_curl($url,'post','json',$postJson);
        var_dump($res);

    }

    //获取用户openid
   public function getBaseInfo()
    {   
        //1.获取到code
        $appid = "wxe4c13a7af1b86b1e";
        $redirect_uri = urlencode("http://www.wwfd.club/index/wetest/getUserOpenId");
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".$redirect_uri."&response_type=code&scope=snsapi_base&state=123#wechat_redirect ";
        header('location:'.$url); 
        //$this->redirect($url,302);     
    }
 
    public function getUserOpenId()
    {
        //获取网页授权的access_token
        $appid = "wxe4c13a7af1b86b1e";
        $appsecret = "71f551559589dd799507e4d46bf7b1d5";
        //$code =　Request::instance()->get('code');
        $code = input('get.code');
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$appsecret."&code=".$code."&grant_type=authorization_code";
        //拉取用户的openid
        //echo "13";
        $res = $this->http_curl($url,'get','json','');       
        //echo "string";
        
        var_dump($res);
    }

    //获取用户详细信息
    public function getUserDetail()
    {
        //1.获取到code
        $appid = "wxe4c13a7af1b86b1e";
        $redirect_uri = urlencode("http://www.wwfd.club/index/wetest/getUserInfo");
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".$redirect_uri."&response_type=code&scope=snsapi_userinfo&state=123#wechat_redirect ";
        header('location:'.$url);         
    }

    public function getUserInfo()
    {
        $appid = "wxe4c13a7af1b86b1e";
        $appsecret = "71f551559589dd799507e4d46bf7b1d5";
        //$code =　Request::instance()->get('code');
        $code = input('get.code');
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$appsecret."&code=".$code."&grant_type=authorization_code";
        //拉取用户的openid
        $res = $this->http_curl($url,'get','json','');
        //echo "22";
        //var_dump($res);
        $access_token = $res['access_token'];
        $openid = $res['openid'];
        //拉取用户的详细信息
        $url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN'; 
        $res = $this->http_curl($url,'get','json','');
        var_dump($res);
        echo "<br>";
        $arr="id：".$res['openid']."<br>".'昵称：'.$res['nickname']."<br>语言：".$res['language']."<br>城市：".$res['city'];
        echo $arr;      
    }

    //模板消息
    public function sendTemplateMsg()
    {
        //1.获取access_token
        $access_token = $this->getWxAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$access_token;
        //2.组装数组
         /*     {
                   "touser":"OPENID",
                   "template_id":"ngqIpbwh8bUfcSsECmogfXcV14J0tQlEpBO27izEYtY",
                   "url":"http://weixin.qq.com/download",  
                   "miniprogram":{
                     "appid":"xiaochengxuappid12345",
                     "pagepath":"index?foo=bar"
                   }, 
                   "data":{
                        "first": {
                           "value":"恭喜你购买成功！",
                           "color":"#173177"
                       },
                   }          
                   }
               }
               */
        $array = array(
            "touser"=>"oBS-Qvw72LqbsJCmvtEe_zzCcFew",
            "template_id"=>"mI8k1TqwPWMTDR1Yo7REAyKWBWD24PQ7JLIab0wuRdQ",
            "url"=>"www.baidu.com",
            "data"=>array(
                "name"=>array("value"=>"hello","color"=>"#173177"),
                "money"=>array("value"=>"100","color"=>"#173177"),
                "date"=>array("value"=>date('Y-m-d H:i:s'),"color"=>"#173177")
                )
            );
        //3.将数组->json
        $postJson = json_encode($array);
        //4.调用curl函数
        $res = $this->http_curl($url,'post','json',$postJson);
        var_dump($res);
    }

    //临时二维码
    public function getQrCode()
    {
        //1.获取票据
        $access_token = $this->getWxAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=".$access_token;
        //{"expire_seconds": 604800, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": 123}}}
        $postArr = array(
            'expire_seconds'=>604800,
            'action_name'=>'QR_SCENE',
            'action_info'=>array(
                'scene'=>array(
                    'scene_id'=>'2000'
                    )
                )
            );
        $postJson = json_encode($postArr);
        $res = $this->http_curl($url,'post','json',$postJson);
        $ticket=$res['ticket'];
        $url="https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=".$ticket;
        echo "临时二维码";
        echo "<img src=".$url.">";
        //var_dump($res);
    }

    //永久二维码
    public function getForeverCode()
    {
        //1.获取票据
        $access_token = $this->getWxAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=".$access_token;
        //{"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_id": 123}}}
        $postArr = array(           
            'action_name'=>'QR_LIMIT_SCENE',
            'action_info'=>array(
                'scene'=>array(
                    'scene_id'=>'3000'
                    )
                )
            );
        $postJson = json_encode($postArr);
        $res = $this->http_curl($url,'post','json',$postJson);
        $ticket=$res['ticket'];
        $url="https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=".$ticket;
        echo "永久二维码";
        echo "<img src=".$url.">";
        //var_dump($res);        
    }

    public function getJsApiTicket()
    {
        //如果session中保存有效的jsapi_ticket
        if (Session::get('jsapi_ticket') && Session::get('jeapi_ticket_expire_time')>time()) {
            $jsapi_ticket = Session::get('jsapi_ticket');

        }else{
            $access_token = $this->getWxAccessToken();
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=".$access_token."&type=jsapi";
            $res = $this->http_curl($url,'get','json','');
            $jsapi_ticket = $res['ticket'];
            Session::set('jsapi_ticket',$jsapi_ticket);
            Session::set('jeapi_ticket_expire_time',time()+7000);
        }
        //var_dump($jsapi_ticket);
        return $jsapi_ticket;
    }

    //获取随机码
    public function getRandCode($num=16)
    {
        //$str = 'QWERTYUIOPASDFGHJKLZXCVBNMqwertyuiopasdfghjklzxcvbnm0123456789';
        //$noncestr = substr(str_shuffle($str1), 0,16);
        $array = array(
            'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
            'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',
            '0','1','2','3','4','5','6','7','8','9'
            );
        $tmpstr = '';
        $count = count($array);
        for ($i=0; $i < $num; $i++) { 
            $key = rand(0,$count-1);
            $tmpstr .= $array[$key];
        }
        return $tmpstr;
    }

    //分享盆友圈
    public function shareWx()
    {
        //获取jsapi_ticket票据
        $jsapi_ticket = $this->getJsApiTicket();
        $timestamp = time();
        $nonceStr = $this->getRandCode();
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        //$url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
        //获取signature
        $signature = "jsapi_ticket=".$jsapi_ticket."&noncestr=".$nonceStr."&timestamp=".$timestamp."&url=".$url;
        $signature = sha1($signature);
        //echo "string";
        $this->assign('name','susu');
        //echo "11";
        $this->assign('timestamp',$timestamp);
        $this->assign('nonceStr',$nonceStr);
        $this->assign('signature',$signature);
        //echo "11";
        return $this->fetch('wetest/share');
    }



    public function show()
    {
        echo "string";
    	return $this->fetch();
    }

}