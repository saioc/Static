document.writeln("<script src=\'https://www.layuicdn.com/layui-v2.5.6/layui.js\'></script>");
$(function() {
$('.menu-goback').click(function() {
    $('body,html').animate({
        scrollTop: '0px'
    }, 900);
});

$(window).scroll(function() {
        var top2 = $(window).scrollTop();
        if (top2 > 500) {$(".menu-goback").show();} else {$(".menu-goback").hide();}
})//返顶
$(document).ready(function(){
         var p=0,t=0;
          $(window).scroll(function(){
            p=$(this).scrollTop();
             if(t<p){
                  //下滚
				   $(".headerhidden").fadeOut();
             }else{
                       //上滚    
					   $(".headerhidden").fadeIn();       
                     }
                setTimeout(function(){ t = p ; },0)
           })
})

	//懒加载
	$(document).ready(function(){
	  $("img.lazy").lazyload({effect:"fadeIn"});
	});
	
	//头部键盘enter键后触发搜索
$('#searchInput').bind('keydown', function (event) {
    var event = window.event || arguments.callee.caller.arguments[0];
    if (event.keyCode == 13){
		searchFun();
    }
});
//头部搜索
function searchFun(){
	var searchVal = $("#searchInput").val();
	if(searchVal!=""){
		window.location.href = "/search/?k="+searchVal;
	}
}
	
	//判断浏览器
	if (navigator.appName == "Microsoft Internet Explorer" && parseInt(navigator.appVersion.split(";")[1].replace(/[ ]/g, "").replace("MSIE", "")) < 10) {
		layui.use('layer', function(){
			var layer = layui.layer;
			layer.closeAll();
			layer.open({
				type: 1
				,title: '更换升级浏览器提示！'
				,skin: 'layui-layer-rim'
				,area: '480px'
				,shade: 0.5
				,id: 'yly_notice' //设定一个id，防止重复弹出
				,moveType: 1 //拖拽模式，0或者1
				,content: '<div style="padding:0 30px 30px;font-size:15px;line-height:22px;">您使得的IE浏览器版本太低，导致网页布局错误，请升级至IE10或以上。为保证最佳用户体验，请使用Chrome、360浏览器、QQ浏览器等！</div>'
			});
		});
	}  
})
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

//登录弹窗
function login(){
	layui.use(['form', 'layer','jquery'], function(){
		var lailu = "pc";
		if(getCookie("fr"))
		{
			lailu=getCookie("fr");
		}
		var layer = layui.layer;	
		layer.closeAll();
		layer.open({
			type: 1
			,title: '登录'
			,skin: 'layui-layer-rim'
			,area: '480px'
			,shade: 0.5
			,id: 'yly_login' //设定一个id，防止重复弹出
			,moveType: 1 //拖拽模式，0或者1
			,content: '<div class="divcheng login"><form name="form1"  id=\'loginbox\' class="layui-form" method="post" action="https://www.yalayi.com/user/save/"><input type=hidden name=ecmsfrom value="'+window.location.href+'"><div class=bk20></div><input type=hidden name=enews value=login><input name=lifetime type=hidden value=0 ><input name="tobind" type="hidden" id="tobind" value="0"><div class="layui-form-item"><label class="layui-form-label">帐号</label><div class="layui-input-inline"><input type="text" name="username" id="username" autocomplete="off" required lay-verify="username" placeholder="请输入帐号" class="layui-input"></div></div><div class="layui-form-item"><label class="layui-form-label">密码</label><div class="layui-input-inline"><input type="password" name="password" required lay-verify="pass" placeholder="请输入密码" autocomplete="off" class="layui-input"></div></div><div class="layui-form-item"><div class="layui-input-block"><button class="layui-btn" lay-submit lay-filter="formDemo">登 录</button></div></div><div class="layui-form-item"><center><a class="layui-btn layui-btn-fluid" onClick="res()">还没有帐号？去注册一个！</a></center></div><div class="layui-form-item"><div class="other">或者</div></div><div class="layui-form-item wx"><center><a class="layui-btn layui-btn-fluid" href=https://open.weixin.qq.com/connect/qrconnect?appid=wx6eb07bf769012b37&redirect_uri=http%3A%2F%2Fpay.yalayi.cn%2Fpcode.php?lailu='+lailu+'&response_type=code&scope=snsapi_login&state=STATE#wechat_redirect><i class="iconfont icon-weixin"></i>微信一键登录</a></center></div><div class="layui-form-item"><center>忘记帐号密码请联系 QQ 1548494049 找回</center> </div></form></div>'
		});
		var form = layui.form;
		form.verify({
		   username: function(value) {
			   if(value=='')
			   {
			   	return '请输入用户名';
			   }
				
				
					tip=200;
					$.ajax({
						url:"https://www.yalayi.com/e/dongpo/username.php", 
						type:'post',
						data:{username:value},
						dataType:'JSON',
						async:false, 
						success:function(res){
							console.log(res);
							tip=res;
						}
					});
					
					if(tip!=200){
						return '用户名错误！';
					}
				
				
			},
		  pass: function(value) {
			  if(value=='')
			  {
			  	return '请输入密码';
			  }
				
				
				tip=200;
				$.ajax({
					url:"https://www.yalayi.com/e/dongpo/pass.php", 
					type:'post',
					data:{username:$('#username').val(),pass:value},
					dataType:'JSON',
					async:false, 
					success:function(res){
						tip=res;
					}
				});
				if(tip!=200){
					return '密码错误，请重新输入！';
				}
				
			},
		});
	});
}


//注册弹窗
function res(){
	layui.use(['form', 'layer','jquery'], function(){
		var lailu = "pc";
		if(getCookie("fr"))
		{
			lailu=getCookie("fr");
		}
		var layer = layui.layer;
		layer.closeAll();
		layer.open({
			type: 1
			,title: '用户注册'
			,shade: 0.5
			,area: '480px'
			,skin: 'layui-layer-rim'
			,id: 'yly_res' //设定一个id，防止重复弹出
			,moveType: 1 //拖拽模式，0或者
			,content: '<div class="divcheng res"><form id=\'loginbox\' class="layui-form" method="post" action="/user/save/"><div class=bk20></div><input type=hidden name=enews value=register><input name="groupid" type="hidden" id="groupid" value="1"><input name="tobind" type="hidden" id="tobind" value="0"><div class="layui-form-item"><label class="layui-form-label">帐号</label><div class="layui-input-inline"><input type="text" name="username" id="username" autocomplete="off" required lay-verify="regusername" placeholder="请输入帐号" class="layui-input"></div></div><div class="layui-form-item"><label class="layui-form-label">密码</label><div class="layui-input-inline"><input type="password" name="password"  id="password"  required lay-verify="regpass" placeholder="请输入密码" autocomplete="off" class="layui-input"></div></div><div class="layui-form-item"><label class="layui-form-label">确认密码</label><div class="layui-input-inline"><input type="password" id="repassword"   name="repassword" required lay-verify="regrepass" placeholder="请再输入一次密码" autocomplete="off" class="layui-input"></div></div><div class="layui-form-item"><label class="layui-form-label">邮箱</label><div class="layui-input-inline"><input type="text" name="email" id="email" required lay-verify="email" placeholder="请输入邮箱如 123@qq.com"  autocomplete="off" class="layui-input"></div></div><input name="lailu" type="hidden" value="'+lailu+'"><div class="layui-form-item"><center><a href="/agreement.html" target="_blank"><font color=#6bbcbb>用户服务条款(注册即代表同意)</font></a></center> </div><div class="layui-form-item"><div class="layui-input-block"><button class="layui-btn" lay-submit lay-filter="formDemo">提交注册</button></div></div><div class="layui-form-item"><center><a class="layui-btn layui-btn-fluid" href=https://open.weixin.qq.com/connect/qrconnect?appid=wx6eb07bf769012b37&redirect_uri=http%3A%2F%2Fpay.yalayi.cn%2Fpcode.php?lailu='+lailu+'&response_type=code&scope=snsapi_login&state=STATE#wechat_redirect><i class="iconfont icon-weixin"></i>微信一键注册</a></center></div><div class="layui-form-item"><center><a class="layui-btn layui-btn-fluid" onClick="login()">已有帐号？去登录！</a></center></div></form></div>'
		});
		
		var form = layui.form;
		form.verify({
		   regusername: function(value) {
			   if(value=='')
			   {
			   	return '请输入用户名';
			   }
				
					tip=200;
					$.ajax({
						url:"https://www.yalayi.com/e/dongpo/username.php", 
						type:'post',
						data:{username:value},
						dataType:'JSON',
						async:false, 
						success:function(res){
							console.log(res);
							tip=res;
						}
					});
					if(tip==200){
						return '用户名存在！';
					}
				
			},
			regpass: function(value) {
				if(value=='')
				{
					return '请输入密码';
				}
				if(value.length<6){
					return '密码长度不得小于6位';
				}
			},
			regrepass: function(value) {
				if(value=='')
				{
					return '请再次输入密码';
				}
				var password=$('#password').val();
				if(password!=value)
				{
					return '两次密码不一致，请重新输入！';
				}
			},
			email: function(value) {
				if(value=='')
				{
					return '请输入邮箱';
				}
				 if(!/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/.test(value)){
				  return '邮箱格式不正确';
				}
				tip=-200;
				$.ajax({
					url:"https://www.yalayi.com/e/dongpo/email.php", 
					type:'post',
					data:{email:value},
					dataType:'JSON',
					async:false, 
					success:function(res){
						console.log(res);
						tip=res;
					}
				});
				if(tip==200){
					return '邮箱存在！';
				}
				
			},
		});
	});
}


//充值
function chongzhi(){
	layui.use('layer', function(){
		var lailu = "pc";
		if(getCookie("fr"))
		{
			lailu=getCookie("fr");
		}
		var layer = layui.layer;
		layer.closeAll();
		 htmlobj=$.ajax({url:"https://www.yalayi.com/e/dongpo/pay.php",async:false});
		console.log(htmlobj);
		layer.open({
			type: 1
			,title: '会员升级/充值'
			,area:'600px'
			,shade:0.5
			,offset:'100px'
			,skin: 'layui-layer-rim'
			,id: 'yly_chongzhi' //设定一个id，防止重复弹出
			,moveType: 1 //拖拽模式，0或者
			,content: htmlobj.responseText
		});
	});
}



function hui() {
			var sn=$('#sn').val();
			if(sn==''){
				layer.msg('兑换码不能为空！');
				return;
			}
            $.ajax({
                url: 'https://www.yalayi.com/user/',
                type: 'POST',
                data: {
                    'sn': sn,
                    'act':'hui'
                },
                success: function (arg) {
					if(arg==-20){
						layer.msg('兑换码不能为空！');
						return;
					}else if(arg==-55){
						layer.msg('兑换码不存在或已过期！');
						return;
					}else if(arg==200){
						layer.msg('兑换成功！',{time:3000},function(){window.location="https://www.yalayi.com/user/";});
						return;
					}
                }
            })
}
		



//充值
function youhuima(){
	layui.use('layer', function(){
		var layer = layui.layer;
	layer.open({
	  type: 1,
	  title:'兑换码信息',
	  offset: '200px',
	  skin: 'layui-layer-rim', //加上边框
	  content: '<div style="padding:30px;"><div class="layui-form">  <div class="layui-form-item"><input type="text" name="sn" lay-verify="sn" id="sn" autocomplete="off" placeholder="请输入您获得的兑换码" class="layui-input"></div><div class="layui-form-item"><button type="submit" class="layui-btn" onclick="hui()">提交</button></div></div><hr/>温馨提示：雅拉伊活动时所派发的8位字符兑换码，包括雅拉伊旗下运营平台及合作供应商。  如有疑问请联系客服。</div>'
	});
	});
}



//跳转设置
function uaredirect(f){try{if(document.getElementById("bdmark")!=null){return}var b=false;if(arguments[1]){var e=window.location.host;var a=window.location.href;if(isSubdomain(arguments[1],e)==1){f=f+"/#m/"+a;b=true}else{if(isSubdomain(arguments[1],e)==2){f=f+"/#m/"+a;b=true}else{f=a;b=false}}}else{b=true}if(b){var c=window.location.hash;if(!c.match("fromapp")){if((navigator.userAgent.match(/(iPhone|iPod|Android|ios|iPad)/i))){location.replace(f)}}}}catch(d){}}function isSubdomain(c,d){this.getdomain=function(f){var e=f.indexOf("://");if(e>0){var h=f.substr(e+3)}else{var h=f}var g=/^www\./;if(g.test(h)){h=h.substr(4)}return h};if(c==d){return 1}else{var c=this.getdomain(c);var b=this.getdomain(d);if(c==b){return 1}else{c=c.replace(".","\\.");var a=new RegExp("\\."+c+"$");if(b.match(a)){return 2}else{return 0}}}};

//uaredirect(location.href.replace('https://www.','https://m.'));

function vipshuoming(){
  location.href="/vip.html";
}