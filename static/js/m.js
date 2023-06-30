$(document).ready(function(){
    $(".img-box img").lazyload({effect:"fadeIn"});
});


function GetQueryString(name)
{
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) {
        return unescape(r[2]);
    }
    return null;
};


function setCookie(c_name,value,expiredays) {  
        var exdate=new Date();  
        exdate.setDate(exdate.getDate()+expiredays);  
        document.cookie=c_name+ "=" +escape(value)+((expiredays==null) ? "" : ";expires="+exdate.toGMTString())+";path=/;domain=yalayi.com";  
} 

function getCookie(c_name) {  
        if (document.cookie.length>0)  {  
            c_start=document.cookie.indexOf(c_name + "=");  
            if (c_start!=-1)  {   
                c_start=c_start + c_name.length+1 ;  
                c_end=document.cookie.indexOf(";",c_start);  
                    if (c_end==-1) 
                       c_end=document.cookie.length;  
                       return unescape(document.cookie.substring(c_start,c_end));  
            }   
        }  
        return "";  
} 

fr=GetQueryString("fr");

if(fr)
{
	if(!getCookie("fr"))
	{
	fr=fr.replace(/[\'\"\\\/\b\f\n\r\t]/g,'');
	fr=fr.replace(/[\-\_\,\!\|\~\`\(\)\#\$\%\^\&\*\{\}\:\;\"\L\<\>\?]/g,'');
	fr=fr.substring(0,10)
	setCookie("fr",fr); 
	}		
}

function login(){
		layui.use('layer', function(){
		  var layer = layui.layer;
		  var lailu = "m";
			if(getCookie("fr"))
			{
			lailu=getCookie("fr");
			}	
		  layer.closeAll();
		  layer.open({
				type: 1
				,title: '登录'
				,area: '100%'
				,offset: 'b'
				,shade:0.2
				,shadeClose :true
				,anim: 2
				,scrollbar :false
				,id: 'yly_login' //设定一个id，防止重复弹出
				,content: '<div style="padding:20px;"><form name="form1"  id=\'loginbox\' class="layui-form" method="post" action="/user/save/"><input type=hidden name=ecmsfrom value="'+window.location.href+'"><div class=bk20></div><input type=hidden name=enews value=login><input name=lifetime type=hidden value=0 ><input name="tobind" type="hidden" id="tobind" value="0"><div class="layui-form-item"><input type="text" name="username" autocomplete="off" required lay-verify="username" placeholder="请输入帐号" class="layui-input"></div><div class="layui-form-item"><input type="password" name="password" required lay-verify="pass" placeholder="请输入密码" autocomplete="off" class="layui-input"></div><div class="layui-form-item"><button class="layui-btn" lay-submit lay-filter="formDemo">登 录</button></div><div class="layui-form-item"><center><a class="layui-btn layui-btn-fluid" href=https://open.weixin.qq.com/connect/qrconnect?appid=wx6eb07bf769012b37&redirect_uri=http%3A%2F%2Fpay.yalayi.cn%2Fpcode.php?lailu='+lailu+'&response_type=code&scope=snsapi_login&state=STATE#wechat_redirect>微信一键登录(仅支持微信中使用)</a></center></div><div class="layui-form-item"><center><a class="layui-btn layui-btn-fluid" onClick="res()">还没有帐号？去注册一个！</a></center></div><div class="layui-form-item"><center>忘记帐号密码请联系 QQ 1548494049 找回</center> </div></form></div>'
		
			  });
	});
}

function res(){
		
		layui.use('layer', function(){
		  
		  	var lailu = "m";
			if(getCookie("fr"))
			{
			lailu=getCookie("fr");
			}	
		  
		  var layer = layui.layer;
		  layer.closeAll();
		  layer.open({
				type: 1
				,title: '用户注册'
				,area: '100%'
				,offset: 'b'
				,shade:0.2
				,shadeClose :true
				,anim: 2
				,scrollbar :false
				,id: 'yly_res' //设定一个id，防止重复弹出
				,content: '<div style="padding:20px;"><form id=\'loginbox\' class="layui-form" method="post" action="/user/save/"><div class=bk20></div><input type=hidden name=enews value=register><input name="groupid" type="hidden" id="groupid" value="1"><input name="tobind" type="hidden" id="tobind" value="0"><div class="layui-form-item"><input type="text" name="username" autocomplete="off" required lay-verify="username" placeholder="请输入帐号" class="layui-input"></div><div class="layui-form-item"><input type="password" name="password" required lay-verify="pass" placeholder="请输入密码" autocomplete="off" class="layui-input"></div><div class="layui-form-item"><input type="password" name="repassword" required lay-verify="pass" placeholder="请再输入一次密码" autocomplete="off" class="layui-input"></div><div class="layui-form-item"><input type="text" name="email" required lay-verify="email" placeholder="请输入邮箱如 123@qq.com"  autocomplete="off" class="layui-input"></div><input name="lailu" type="hidden" value="'+lailu+'"><div class="layui-form-item"><center><a href="/agreement.html" target="_blank"><font color=#6bbcbb>用户服务条款(注册即代表同意)</font></a></center> </div><div class="layui-form-item"><button class="layui-btn" lay-submit lay-filter="formDemo">提交注册</button></div><div class="layui-form-item"><center><a class="layui-btn layui-btn-fluid" href=https://open.weixin.qq.com/connect/qrconnect?appid=wx6eb07bf769012b37&redirect_uri=http%3A%2F%2Fpay.yalayi.cn%2Fpcode.php?lailu='+lailu+'&response_type=code&scope=snsapi_login&state=STATE#wechat_redirect>微信一键注册(仅支持微信中使用)</a></center></div><div class="layui-form-item"><center><a class="layui-btn layui-btn-fluid" onClick="login()">已经有帐号了？去登录！</a></center></div></form></div>'
		
			  });
	});
}



function chongzhi(){
		
		layui.use('layer', function(){
		  var layer = layui.layer;
		  layer.closeAll();
		  
		  htmlobj=$.ajax({url:"https://m.yalayi.com/e/dongpo/pay.php",async:false});
		  console.log(htmlobj);
		  layer.open({
		  	type: 1
		  	,title: '会员升级/充值'
		  	,area: '100%'
		  	,offset: 'b'
		  	,shade:0.2
		  	,shadeClose :true
		  	,anim: 2
		  	,scrollbar :false
		  	,id: 'yly_chongzhi' //设定一个id，防止重复弹出
		  	,content: htmlobj.responseText
		  });
		  
	});
}

function uaredirect(f){try{if(document.getElementById("bdmark")!=null){return}var b=false;if(arguments[1]){var e=window.location.host;var a=window.location.href;if(isSubdomain(arguments[1],e)==1){f=f+"www"+a;b=true}else{if(isSubdomain(arguments[1],e)==2){f=f+"www"+a;b=true}else{f=a;b=false}}}else{b=true}if(b){var c=window.location.hash;if(!c.match("fromapp")){if(!(navigator.userAgent.match(/(iPhone|iPod|Android|ios|iPad)/i))){location.replace(f)}}}}catch(d){}}function isSubdomain(c,d){this.getdomain=function(f){var e=f.indexOf("://");if(e>0){var h=f.substr(e+3)}else{var h=f}var g=/^www\./;if(g.test(h)){h=h.substr(4)}return h};if(c==d){return 1}else{var c=this.getdomain(c);var b=this.getdomain(d);if(c==b){return 1}else{c=c.replace(".","\\.");var a=new RegExp("\\."+c+"$");if(b.match(a)){return 2}else{return 0}}}};
//uaredirect(location.href.replace('https://m.','https://www.'));
function vipshuoming(){
  location.href="/vip.html";
}
