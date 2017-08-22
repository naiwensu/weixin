<!DOCTYPE html>
<html>
<head>
	<title>微信js分享接口</title>
	<meta name="viewpoint" content="initial-scale=1.0;width=device-width" charset="utf-8"/>
	<meta http-equiv="content" content="text/html;charset=utf-8"/>
	<script type="text/javascript" src="http://res.wx.qq.com/open/js/jweixin-1.2.0.js"></script>
</head>
<body>
	{$name}
	<script>
	wx.config({
	    debug: true, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
	    appId: 'wxe4c13a7af1b86b1e', // 必填，公众号的唯一标识
	    timestamp:'{$time}' , // 必填，生成签名的时间戳
	    nonceStr: '{$noncestr}', // 必填，生成签名的随机串
	    signature: '{$signature}',// 必填，签名，见附录1
	    jsApiList: [
	    	'onMenuShareTimeline',
	    	'onMenuShareAppMessage'
	    ] // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
	});

	wx.ready(function(){
		wx.onMenuShareTimeline({
		    title: 'test', // 分享标题
		    link: 'www.wwfd.club', // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
		    imgUrl: 'https://ss0.bdstatic.com/5aV1bjqh_Q23odCf/static/superman/img/logo/bd_logo1_31bdc765.png', // 分享图标
		    success: function () { 
		        // 用户确认分享后执行的回调函数
		    },
		    cancel: function () { 
		        // 用户取消分享后执行的回调函数
		    }
		});

		wx.onMenuShareAppMessage({
		    title: 'test1', // 分享标题
		    desc: 'test1 share', // 分享描述
		    link: 'www.wwfd.club', // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
		    imgUrl: 'https://ss0.bdstatic.com/5aV1bjqh_Q23odCf/static/superman/img/logo/bd_logo1_31bdc765.png', // 分享图标
		    type: '', // 分享类型,music、video或link，不填默认为link
		    dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
		    success: function () { 
		        // 用户确认分享后执行的回调函数
		    },
		    cancel: function () { 
		        // 用户取消分享后执行的回调函数
		    }
		});
	    
	});

	wx.error(function(res){
 
	});
	</script>
	
</body>
</html>