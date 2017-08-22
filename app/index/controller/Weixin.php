<?php
namespace app\index\controller;
define("TOKEN", "xiangqi");
class Weixin
{
    public function index(){
        if (isset($_GET['echostr'])) {
            $this->valid();
        }else{
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
/*
."/n"."时区：".$arr['results']['0']['location']['timezone']."/n"."天气：".$arr['results']['0']['daily']['text_day']."&nbsp;&nbsp;".$arr['results']['0']['daily']['text_night']."最高气温：".$arr['results']['0']['daily']['high'].""."最低气温：".$arr['results']['0']['daily']['low']."风向：".$arr['results']['0']['daily']['wind_direction']."风速：".$arr['results']['0']['daily']['wind_speed']."更新时间：".$arr['results']['0']['last_update']
 */



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
        switch ($object->Event)
        {
            case "subscribe":
                $contentStr = "欢迎关注苏苏的微信！";
                break;
            case "unsubscribe":
                $contentStr = "";
                break;
            case "CLICK":
                switch ($object->EventKey)
                {
                    default:
                        $contentStr = "你点击了菜单: ".$object->EventKey;
                        break;
                }
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

    public function http_curl()
    {
    	//获取
    	//1.初始化curl
    	$ch = curl_init();
    	$url = 'http://www.baidu.com';
    	//2.设置curl参数
    	curl_setopt($ch,CURLOPT_URL,$url);
    	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    	//来源
    	$output = curl_exec($ch);
    	//4.关闭
    	curl_close($ch);
    	var_dump($output);
    }

    //获取access_token
    public function getAccessToken()
    {
    	$appid='wx9bd7866e81b6bf71';
    	$appsecret = 'ec218d9c7afb91ca91d2d1f72e1056a5';
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

    //获取微信服务器ip地址
    public function getServiceIp()
    {
    	$accessToken = '0V5w__DSAhiczwUWOhN-WchsmBmfIn9fGKv6jx3O_dYCNYn5hrvk6tu2AnVgdMmFXlLiiWzlwzQSZDYAA4trsrlSPsXuIsqk_1A7XrqeVfkSKObAAAJBF';
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

    public function show()
    {
    	echo "string1";
    }

}