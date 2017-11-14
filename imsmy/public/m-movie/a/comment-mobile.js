jQuery.cookie = function(name, value, options) {  
	if (typeof value != 'undefined') { // name and value given, set cookie  
		options = options || {};  
		if (value === null) {  
			value = '';  
			options.expires = -1;  
		}  
		var expires = '';  
		if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {  
			var date;  
			if (typeof options.expires == 'number') {  
				date = new Date();  
				date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));  
			} else {  
				date = options.expires;  
			}  
			expires = '; expires=' + date.toUTCString(); // use expires attribute, max-age is not supported by IE  
		}  
		var path = options.path ? '; path=' + options.path : '';  
		var domain = options.domain ? '; domain=' + options.domain : '';  
		var secure = options.secure ? '; secure' : '';  
		document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');  
	} else { // only name given, get cookie  
		var cookieValue = null;  
		if (document.cookie && document.cookie != '') {  
			var cookies = document.cookie.split(';');  
			for (var i = 0; i < cookies.length; i++) {  
				var cookie = jQuery.trim(cookies[i]);  
				// Does this cookie string begin with the name we want?  
				if (cookie.substring(0, name.length + 1) == (name + '=')) {  
					cookieValue = decodeURIComponent(cookie.substring(name.length + 1));  
					break;  
				}  
			}  
		}  
		return cookieValue;  
	}  
};  
function getcookie(name) {  
	var cookie_start = document.cookie.indexOf(name);  
	var cookie_end = document.cookie.indexOf(";", cookie_start);  
	return cookie_start == -1 ? '' : unescape(document.cookie.substring(cookie_start + name.length + 1, (cookie_end > cookie_start ? cookie_end : document.cookie.length)));  
}  
function setcookie(cookieName, cookieValue, seconds, path, domain, secure) {  
	var expires = new Date();  
	expires.setTime(expires.getTime() + seconds);  
	document.cookie = escape(cookieName) + '=' + escape(cookieValue)  
											+ (expires ? '; expires=' + expires.toGMTString() : '')  
											+ (path ? '; path=' + path : '/')  
											+ (domain ? '; domain=' + domain : '')  
											+ (secure ? '; secure' : '');  
}
function getRequst(){   
   var url = location.search;  
   var theRequest = new Object();   
   if (url.indexOf("?") != -1) {   
      var str = url.substr(1);   
      strs = str.split("&");   
      for(var i = 0; i < strs.length; i ++) {   
         theRequest[strs[i].split("=")[0]]=unescape(strs[i].split("=")[1]);   
      }   
   }   
   return theRequest;   
}

	$(document).on('click','.need-login',function(){
		location.href = '/user/login';
	});
	$.extend({
		conf:{
			//保存网站各种配置参数
			starNum:5,		//星的个数，也可通过DOM的data-num设置，后者优先级高。
			centerPage: 5	//分页中间显示个数
		},
		comment:function(obj){
			/*
			*	评论默认配置
			*	type:标识请求哪里的评论，type=0为文章评论，type=1为网剧评论。
			*	page:默认请求第一页的评论。
			*	postid:请求的 文章|网剧 的ID。
			*/
			var def = {page:1,type:0,postid:1,login:0,referid:0};		
			var o = $.extend({},def,obj),cur = 0,ajaxIng = 0;
			var replyuid = replycommentid = 0;
			ajax_get(o);
			pager_bind();
			function ajax_get(o){
				$.post('/Comment/getComment',o,function(res){
					if( res.status == 1 ){
						var pager = {
							tt	:	parseInt(res.ftotalCount),
							ft	:	parseInt(res.totalCount),
							pn 	:	parseInt(res.pageNumber),
							ps 	:	parseInt(res.pageSize)
						}
						$('#comment .comment-title .num').text('已有'+pager.ft+'条点评');

						$('#post-comment').text(pager.ft);
						if( pager.tt != 0 ){
							init(res.data);
						}else{
							$('#com-list').html('');
						}
						if(pager.tt>pager.pn){
							initPager(pager);
						}
						if( o.commentid ){
							series_noscroll();
							location.href="#c_"+o.commentid;
						}
						if (o.page>1) {
							location.href="#comment";
						};
					}
				},'json');
			}
			/*	绑定评论列表各种操作	*/
			function pager_bind(){
				/*	绑定分页操作事件	*/
				$('#comment').on('click','#comment-pager  span[data-p]',function(){
					o.page = $(this).data('p');
					ajax_get(o);
					$(window).scrollTop($("#comment")[0].offsetTop-90);
				});
				$('#comment input[data-sync]').attr('disabled',false);
				
				/*	return操作前写未登录可以做的逻辑操作	*/
				if( o.login == 0)
					return;
				if( Think.LOGIN_USER.bind_weibo == 0 ){
					$('#comment').on('click','input[data-sync]',function(){
						var pObj = $(this).parent(),off = pObj.offset();
						if( $('#comment-sync').length == 0 ){
							$('<div class="comment-sync" id="comment-sync"></div>').appendTo($('body'));
							var str = '<div class="info">去新浪微博授权后才可以回复，点击确定前往相应页面。<a href="javascript:void(0);" id="wb_bd">确定</a><a href="javascript:void(0);" id="wb_cancel" class="info_close">取消</a></div>'+
								'<div class="arrow"><div class="arrow-before"></div><div class="arrow-after"></div></div>';
							$('#comment-sync').html(str).css({
								left:off.left,top:off.top+28
							});
						}else{
							$('#comment-sync').remove();
						}
						return false;
					});
					$(document).on('click',function(evt){
						if( $(evt.target).closest('.comment-sync').length == 0 ){
							$('#comment-sync').remove();
						}
					});
					$(document).on('click','#comment-sync a:last-child',function(){
						$('#comment-sync').remove();
					});
					var wbChild;
					$(document).on('click','#comment-sync a:first-child',function(){
						wbChild = window.open('/Oauth/weibo','weiboChild','width=800,height=600,left=100,top=100');
					});
				}
				/*	绑定回复操作事件	*/
				$('#com-list').on('click','.reply',function(){
					var referid = $(this).data('referid');
					replycommentid = $(this).data('replycommentid');
					replyuid = userid = $(this).data('replyuid');
					var toObj = $(this).parents('li').eq(0);
					if( toObj.next('li#comment-reply').length != 0 ){
			 			$('#comment-reply').slideUp(300,function(){
			 				$(this).remove();
			 			});
			 			return false;
			 		}
			 		$('#comment-reply').remove();
					$('#comment .comment-text:eq(0)').clone().insertAfter(toObj).wrap('<li class="comment-reply dn" id="comment-reply"></li>');
					$('#comment-reply').find('.comment-textarea').removeClass('input-error').data('len',1);
					$('#comment-reply').find('.comment-btn').data({'referid':referid,'replyuid':userid, 'replycommentid':replycommentid}).text('回复');
					$('#comment-reply').find('.expression').addClass('reply-expression').removeClass('expression');
					$('#comment-reply').find('.comment-tip i').text($('.comment-textarea').attr('maxlength')).end()
						.slideDown(300,function(){
						$(this).removeClass('dn');
					});
				});
				$('#comment').on('keyup','.comment-textarea',function(){
					var perLen = $(this).attr('maxlength');
					var len = $.trim($(this).val()).length;
					$(this).next('.comment-ope').find('.comment-tip i').text(perLen-len);
					if( len > 0 )
						$(this).removeClass('input-error');
				});
				$('#comment').on('click','.comment-btn',function(){
					if( ajaxIng == 1 )
						return;
					var obj = $(this).parents('.comment-input').eq(0),
						textarea = obj.find('.comment-textarea'),
						val = $.trim(textarea.val()),minLen = textarea.data('len'),
						_this = $(this);
					if( val.length < minLen ){
						$.alert({tip:(minLen!=1?'最少输入2个字':'回复不能为空'),error:true});
						textarea.addClass('input-error');
						return;
					}
					if ($(this).data('replyuid')==0) {
						replyuid = 0;
					};
					var postObj = {
						postid:o.postid,
						content:val,
						type:o.type,
						referid:$(this).data('referid'),
						replyuid:replyuid,
						replycommentid:replycommentid,
						weibo:$(this).siblings('label').eq(0).find('input[data-sync]').prop('checked')==true?1:0
					};
					ajaxIng = 1;
					_this.text(minLen!=1?'发表中':'回复中');
					$.post('/Comment/index',postObj,function(res){
						ajaxIng = 0;
						if( minLen!=1 )
							_this.text('发表点评');
						else
							_this.text('回复');
						if( res.status == 0 ){
							var href = location.href;
							if( minLen != 1 ){
								// $(init_item(res.data,0)).prependTo($('#com-list'));
							}else{
								var reply = $('#comment-reply');
								var pObj = reply.parent();
								if(pObj.attr('id') == 'com-list'){
									if(reply.next('.com-sub').length==0){
										reply.after('<li class="com-sub clearfix"><ul class="com-list"></ul></li>');
									}
									reply.next('.com-sub').find('.com-list').append($(init_item(res.data,0)));
								}else{
									pObj.append($(init_item(res.data,1)))
								}
								$('#comment-reply').remove();
							}
							$('#comment textarea').val('');
							$('#comment .comment-tip i').text($('.comment-textarea').attr('maxlength'));
							if(res['data']['referid']==0){
								o.page = res.page;
								o.commentid = res['data']['commentid'];
								ajax_get(o);
							}
						}else{
							//错误提示
							$.alert({
								error:true,
								tip:res.msg
							});
						}
						},'json');
				});
				/*	评论条数展开	*/
				$("ul li.com-sub").each(function(){
					$(this).get(0).isHide=false;
				});
				$('#comment').on('click','.count-reply',function(){
					var more = $(this).data('more');
					if( more == 0 ){
						var sub = $(this).parents('li').next('li.com-sub');
						if(!sub.get(0).isHide){
							sub.slideUp();
							sub.get(0).isHide=true;
						}		
						else{
							sub.slideDown();
							sub.get(0).isHide=false;
						}
					}else{
						var _this = $(this),
							comId = _this.data('commentid'),
							_thisLi = $(this).parents('li').eq(0);
						$.post('/Comment/getSubComment',{commentid:comId},function(res){
							var str = '';
							for( var i = 0;i<res.data.length;i++){
								str += init_item(res.data[i],0);
							}
							_thisLi.after($('<li class="com-sub clearfix"><ul class="com-list">'+str+'</ul></li>'));
							_this.data('more',0);
						},'json');
					}
				});
				/*	管理员||自己删除评论	*/
				$('#comment').on('click','.delete',function(){
					var _pLi = $(this).parents('li').eq(0),
						_sib = _pLi.next('.com-sub');
					var comId = $(this).data('commentid');
					$.confirm({
						content:"是否确定删除？",buttons:[
						  {
						    name:"确定",callback:function(){
								$.post('/Comment/delete',{commentid:comId},function(res){
									if( res.status == 0 ){
										_pLi.remove();
										_sib.remove();
									}else{
										$.alert({tip:res.msg,error:true});
									}
								},'json');
						    }
						  },
						  {name:"取消",callback:function(){}}
						]
					});
				});
				$('#comment').on('click','.setwonderful',function(){
					var _this = $(this);
						recommend = _this.data('recommend'),
						commentid = _this.data('commentid');
					if( recommend == 0 ){
						$.post('/Comment/recommend',{commentid:commentid},function(res){
							if( res.status == 0 ){
								$.alert({tip:'操作成功'});
								_this.data('recommend',1);
								_this.text('取消精彩');
								// $('<span class="com-amazing">精彩点评</span>').prependTo(_this.parent().siblings('.title'));
							}else{
								$.alert({tip:res.msg,error:true});
							}
						},'json');
					}else{
						$.post('/Comment/recommend',{commentid:commentid},function(res){
							if( res.status == 0 ){
								$.alert({tip:'操作成功'});
								_this.data('recommend',0);
								_this.text('设为精彩');
								_this.parent().siblings('.title').find('.com-amazing').remove();
							}else{
								$.alert({tip:res.msg,error:true});
							}
						},'json');
					}
				});
				// 评论点赞
				var img = 0
				$('#comment').on('click','.js-com-prove',function(){
					if(img==1){
						return;
					}
					img = 1;
					var _thisObj = $(this),
						commentid = _thisObj.data('commentid');
					if(_thisObj.hasClass('approve')){
						// 二级评论
						var _thisNum = _thisObj.find('span').length ? parseInt(_thisObj.find('span').text()) : 0;
						$.post('/Comment/approve', {commentid : commentid}, function(re){
							if(re.status === 1){
								// 点赞成功
								if(_thisNum === 0){
									_thisObj.append('(<span>1</span>)');
								}else{
									_thisObj.find('span').text(++_thisNum);
								}
								img = 0;
							}else if(re.status === 0){
								// 取消点赞
								(_thisNum - 1) > 0 ? _thisObj.find('span').text(--_thisNum) : _thisObj.text('赞');
								$.alert({tip:'取消成功'});
								img = 0;
							}else{
								// 异常
								$.alert({tip:re.msg,error:true});
								img = 0;				}
						},'json');
					}else{
						// 一级评论
						var _thisNum = _thisObj.text() === '赞同' ? 0 : parseInt(_thisObj.text());
						$.post('/Comment/approve', {commentid:commentid}, function(re) {
							if(re.status === 1){
								// 点赞成功
								_thisObj.text(++_thisNum).addClass('com-prove');
								var _str = '<div class="add-one">+1</div>';
								_thisObj.append(_str);
								$('.add-one').animate({top:'-40px',opcity:0},function(){
									$(this).remove();
								});
								img = 0;
							}else if(re.status === 0){
								// 取消点赞
								_thisNum = (_thisNum - 1) > 0 ? (--_thisNum) : '赞同';
								_thisObj.text(_thisNum).removeClass('com-proved');
								$.alert({tip:'取消成功'});
								img = 0;
							}else{
								// 异常
								$.alert({tip:re.msg,error:true});
								img = 0;
							}
						}, 'json');
					}
				});
			}
			/*	生成分页数据	*/
			function initPager(p){
				var str = '',cn = $.conf.centerPage,sp = Math.floor(cn/2);
				var tp = Math.ceil(p.tt/p.ps);
				str += (p.pn>1?'<span class="prev" data-p="'+(p.pn-1)+'">上一页</span>':'');
				str += ((p.pn-sp)>2?'<span data-p="1">1</span><span class="dot">...</span>':'');
				var start = (p.pn-sp)==2?1:(p.pn-sp),end = (p.pn+sp)==(tp-1)?tp:(p.pn+sp);
				for(var i=1;i<=tp;i++){
					if( start <= i && i <= end ){
						if( i == p.pn )
							str += '<span class="cur" data-p="'+i+'">'+i+'</span>';
						else
							str += '<span data-p="'+i+'">'+i+'</span>';
					}else{

					}
				}
				str += ((p.pn+sp)<(tp-1)?'<span class="dot">...</span><span data-p="'+tp+'">'+tp+'</span>':'');
				str += ((p.pn<tp)?'<span class="next" data-p="'+(p.pn+1)+'">下一页</span>':'');
				if($('#comment-pager').length==0)
					$('#com-list').after('<div class="comment-pager" id="comment-pager"></div>');
				$('#comment-pager').html(str);
			}
				var ua = navigator.userAgent.toLowerCase(),
					isWx = '';
				if(ua.match(/MicroMessenger/i)=="micromessenger") {
					//微信环境
					isWx = 'comment-wx'
				}
			/*	初始化评论列表	*/
			function init(res){
				$('#com-list').html('');
				var ua = navigator.userAgent.toLowerCase();
				var resLength = res.length;
				var isWeixin = ua.match(/MicroMessenger/i)=="micromessenger";
				var request = getRequst();
        		if(pid != 5) {
        			resLength = 3;
        		}

        		// alert(resLength);
				for(var i=0;i<resLength;i++){
					// console.log(resLength-i);
					$(init_item(res[i],0)).appendTo($('#com-list'));
						if( res[i].hassub == 1 && res[i].ref.length != 0 ){
							// var _dom = $('<li class="com-sub clearfix"></li>');
							// _dom.appendTo($('#com-list'));
							// if(request['_vfrom'] != "VmovierApp_weixin" && pid != 1){
							// 	$('<ul class="com-list"></ul>').appendTo(_dom);
							// }
							// for(var j=0;j<res[i].ref.length;j++){
							// 	$(init_item(res[i].ref[j],1)).appendTo(_dom.find('.com-list'));
							// }
						}
				}
				if(!res.length){
        			$(".comment-visiable").hide();
        		}	
			}
			/*	初始化单条评论	*/
			function init_item(res,hassub){
				var ua = navigator.userAgent.toLowerCase();
				var resLength = res.length;
				var isWeixin = ua.match(/MicroMessenger/i)=="micromessenger";
				var weixinHide = isWeixin? "weixinHide" : " ";
				var request = getRequst();
				// console.log(pid);
				// console.log(res);
				if(1 && pid == 1 && pid == 5){
					var str='<li class="clearfix" id="c_'+res.commentid+'">'+
						'<div class="com-img ww">'+
								'<img class="full" src="'+res.userinfo.avatar_180+'" alt="头像">'+
						'</div>'+
						'<div class="com-text-wx">'+
								'<div class="title">'+
									'<span>'+res.userinfo.username+'</span>'+
									(res.userinfo.isadmin == 3?'<span class="plus-v-p-14" title="新片场认证创作人"></span>':res.userinfo.isadmin==4?'<span class="plus-v-c-14" title="新片场认证机构"></span>':'')+
									(hassub==0?'':(res.replyuid == 0?'':'<span> 回复</span> <span>'+res.replyuser.username+'</span>'))+
								'</div><br>'+
								'<span class="time">'+res.addtime.replace(/-/g, ".")+'</span>'+
								'<div class="intro">'+res.content+'</div>'+
						'</div>'+
					'</li>';
				}else{
					var str='<li class="clearfix" id="c_'+res.commentid+'">'+
						'<div class="com-img ww zg" data-zg="点击评论头像">'+
								'<img class="full" src="'+res.userinfo.avatar_180+'" alt="头像">'+
						'</div>'+
						'<div class="com-text-wx">'+
								'<div class="title zg" data-zg="点击评论用户名">'+
									'<span>'+res.userinfo.username+'</span>'+
									(res.userinfo.isadmin == 3?'<span class="plus-v-p-14" title="新片场认证创作人"></span>':res.userinfo.isadmin==4?'<span class="plus-v-c-14" title="新片场认证机构"></span>':'')+
									(hassub==0?'':(res.replyuid == 0?'':' 回复 <span>'+res.replyuser.username+'</span>'))+
								'</div>'+
								'<div class="intro zg" data-zg="点击评论内容">'+res.content+'</div>'+
								'<div class="ope">';
								// if(res.referid==0){
								// 	str += '<a href="javascript:;" data-commentid="'+res.commentid+'" class="fr'+(isWx == 'comment-wx'? ' isWx': o.login==0?' need-login':' approve')+(res.isapprove==0?'':' approved')+" "+weixinHide+'"><i class="icon-approve m-icon'+(res.isapprove==0?'':' icon-approved')+'">&nbsp;</i>'+
								// (res.count_approve==0?'<span>赞</span>':'<span><b>'+res.count_approve+'</b></span>')+'</a>';
								// }
								str +=
									'<span class="time">'+res.addtime+'</span>'+		
								'</div>'+
						'</div>'+
					'</li>';
				}
				
				return str;
			}
		}
	});