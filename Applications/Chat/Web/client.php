<?php
error_reporting(E_ALL & ~E_NOTICE);
if (strpos($_SERVER['HTTP_USER_AGENT'],'MicroMessenger') == false ){
 ?>
<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=0">
    </head>
    <body>
        <script type="text/javascript">
            var ua = navigator.userAgent.toLowerCase();
            var isWeixin = ua.indexOf('micromessenger') != -1;
            var isAndroid = ua.indexOf('android') != -1;
            var isIos = (ua.indexOf('iphone') != -1) || (ua.indexOf('ipad') != -1);
            if (!isWeixin) {
                document.head.innerHTML = '<title>抱歉，出错了</title><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=0"><link rel="stylesheet" type="text/css" href="https://res.wx.qq.com/open/libs/weui/0.4.1/weui.css">';
                document.body.innerHTML = '<div class="weui_msg"><div class="weui_icon_area"><i class="weui_icon_info weui_icon_msg"></i></div><div class="weui_text_area"><h4 class="weui_msg_title">请在微信客户端打开链接</h4></div></div>';
            }
        </script>
    </body>
</html>
<?php 
  exit;  
  }
if(empty($_GET['name']) || empty($_GET['headimgurl']) || empty($_GET['YS']) || empty($_GET['openid']))
{     
    header("Location: http://".$_SERVER['SERVER_NAME']); 
    exit;
}
?>
<!DOCTYPE html>  
<html>  
<head>  
    <title>聊天室</title>  
    <meta charset="utf-8" />  
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">  
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />  
    <link href="/css/index.css" rel="stylesheet" />  
    <link href="/css/bootstrap.min.css" rel="stylesheet" media="screen">
    <link href="/css/reset.css" rel="stylesheet" >
    <link  href="/css/toastr.min.css" rel="stylesheet" type="text/css"/>
    <link  href="/css/Huploadify.css" rel="stylesheet" type="text/css"/>
    <script type="text/javascript" src="/js/jquery.min.js"></script>
    <script type="text/javascript" src="/js/index.js"></script>
    <script type="text/javascript" src="/js/jquery.qqFace.js"></script>
    <script type="text/javascript" src="/js/toastr.min.js"></script>
    <script type="text/javascript" src="/js/jquery.Huploadify.js"></script>
</head>  
<body onload="connect();">  
    <div class="main">  
        <div class="main_inner clearfix">  
            <div class="panel"></div>  
            <div id="chatArea" class="box chat">  
                <div class="box_hd"> 当前所在房间(房间<?php echo isset($_GET['room_id'])&&intval($_GET['room_id'])>0 ? intval($_GET['room_id']):1; ?>) &nbsp;&nbsp;&nbsp;&nbsp; <a href="javascript:openurl(1);">房间1</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:openurl(2);">房间2</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:openurl(3);">房间3</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:openurl(4);">房间4</a> </div>  
                <div class="box_bd" id="messageList" >  
                </div> 
                <div id="messageList_end" style="height:0px; overflow:hidden"></div>
                <div class="box_ft">  
                    <div class="box_ft_bd hide">  
                        <div class="emoji_panel">  
                            <ul class="exp_hd">  
                                <li class="exp_hd_item active" data-i="0">  
                                    <a href="javascript:;">在线列表</a>  
                                </li>  
                                <li class="exp_hd_item" data-i="1">  
                                    <a href="javascript:;">QQ表情</a>  
                                </li> 
                                <!-- 
                                <li class="exp_hd_item" data-i="2">  
                                    <a href="javascript:;">动画表情</a>  
                                </li>
                                -->
                            </ul>  
                            <div class="exp_bd">  
                                <div class="exp_cont active" >
                                <ul id="clientAll_list"></ul>
                                </div> 
                                <div class="exp_cont emoji_face " id="biaoqing">  
                                </div>   
                              <!-- <div class="exp_cont tuzki_face"></div> -->
                            </div>  
                        </div>  
                    </div>  
                    <div class="box_ft_hd"> 
                        <div class="eaitWrap"> 
                             <textarea id="editArea" class="editArea"></textarea> 
                        </div> 
                        <div style="padding: 5px;">
                         <select class="form-control" style="width: 150px;" id="client_list">
                             <option value="all">所有人</option>
                        </select> 
                         <p >
                         <input type="hidden" id="doctor_bz" value="<?php echo $_GET['YS'];?>">
                         <a href="javascript:;" class="web_wechat_face" id="web_wechat_face" title="在线列表 QQ表情"></a>
                         <a href="javascript:;" class="web_wechat_pic <?php if($_GET['YS']=='医生'){?> hide <?php } ?> " id="web_wechat_pic" title="发送图片"></a>
                         <button type="button" class="btn btn-default" onclick="onSubmit();">发送</button>  
                        </p>  
                       </div>  
                    </div>  
                <?php
                if(md5($_GET['openid'])==="de36371a5790a730e110515c7c10cb21")
                {
                ?>
                    <div style="width:100%;line-height: 20px;" id="doctor">
                     <div style="float: left;">
                     <select class="form-control" style="width: 150px;" id="doctor_list">
                    </select>
                    </div>
                     <div style="float: left;">
                        <button type="button" class="btn btn-default" onclick="upload_doctor(1);">设置医生</button>  
                        <button type="button" class="btn btn-default" onclick="upload_doctor(0);">取消医生</button>  
                     </div>
                    </div>
                <?php
                }
                ?>    
                </div>  
            </div>  
        </div>  
    </div> 
</body>  
<script type="text/javascript">
var ws, client_list={},clientAll_list={};
    //建立连接
    function connect() {
       // 创建websocket
       ws = new WebSocket("ws://"+document.domain+":7272");
       // 当socket连接打开时，输入用户名
       ws.onopen = onopen;
       // 当有消息时根据消息类型显示不同信息
       ws.onmessage = onmessage; 
       ws.onclose = function() {
          console.log("连接关闭，定时重连");
          connect();
       };
       ws.onerror = function() {
          console.log("出现错误");
       }; 
    }
    
    // 连接建立时发送登录信息
    function onopen()
    {
        // 登录
        var login_data = '{"type":"login","client_name":"<?php echo $_GET['name']; ?>","room_id":"<?php echo isset($_GET['room_id']) ? $_GET['room_id'] : 1?>","headimgurl":"<?php echo $_GET['headimgurl']; ?>","YS":"<?php echo $_GET['YS'];?>","openid":"<?php echo $_GET['openid'];?>"}';
        console.log("websocket握手成功，发送登录数据:"+login_data);
        ws.send(login_data);
    }
     // 服务端发来消息时
    function onmessage(e)
    {
        console.log(e.data);
        var data = eval("("+e.data+")");
        switch(data['type']){
            // 服务端ping客户端
            case 'ping':
                ws.send('{"type":"pong"}');
                break;
            // 登录 更新用户列表
            case 'login':
                //{"type":"login","client_id":xxx,"client_name":"xxx","client_list":"[...]","time":"xxx"}
                say(data['client_id'], data['client_name'], data['client_name']+' 加入了聊天室 房间'+data['room_id'], data['time'],data['headimgurl'],data['YS'],data['status']);
                if(data['client_list'])
                {   //房间用户列表
                    client_list = data['client_list'];
                }
                else
                {
                    client_list[data['client_id']] = data['client_name']; 
                }
                if(data['tst'])
                {    
                    //所有在线人员列表
                    clientAll_list = data['tst'];
                }
                 else
                {
                    clientAll_list[data['client_id']] = data['client_name'];
                }
                 //刷新列表
                flush_client_list();
                console.log(data['client_name']+"登录成功");
                break;
            // 发言
            case 'say':
                //{"type":"say","from_client_id":xxx,"to_client_id":"all/client_id","content":"xxx","time":"xxx",头像 医生 状态}
                say(data['from_client_id'], data['from_client_name'],data['content'], data['time'],data['headimgurl'],data['YS'],data['status']);
                break;
            //更新医生 
            case 'upload_doctor':
                $("#doctor_bz").val(data['YS']);
                if(data['YS']=='医生')
                {
                $("#web_wechat_pic").addClass("hide");  
                //$("#web_wechat_pic").hide();  
                }   
                else
                {
                $("#web_wechat_pic").removeClass("hide");
               // $("#web_wechat_pic").show();  
                } 
                break;
             case 'upload_clinetAll': 
                //更新所有在线人列表  
                clientAll_list = data['clientAll_list'];
                //刷新列表
                flush_client_list();
                break;  
            // 用户退出 更新用户列表
            case 'logout':
                //{"type":"logout","client_id":xxx,"time":"xxx"}
                say(data['from_client_id'], data['from_client_name'], data['from_client_name']+' 退出了', data['time'],data['headimgurl'],data['YS']);
                //删除房间列表中退出的人
                delete client_list[data['from_client_id']];
                //删除所有在线人员退出的人
                delete clientAll_list[data['from_client_id']];
                //刷新列表
                flush_client_list();
        }
    }

    // 提交对话
    function onSubmit() {
      var input = document.getElementById("editArea");
      if(input.value.length<=0)
       {
        toastr.error("发送内容为空");
        input.focus();
        return false;
       } 
      var to_client_id = $("#client_list option:selected").attr("value");
      var to_client_name = $("#client_list option:selected").text();
      ws.send('{"type":"say","to_client_id":"'+to_client_id+'","to_client_name":"'+to_client_name+'","content":"'+escape(input.value)+'"}');
      input.value = "";
      //input.focus(); 
      // $('.box_ft_bd').toggleClass('hide',true);
    }

   //设置医生
    function upload_doctor(status)
    {      
        var doctor_id = $("#doctor_list option:selected").attr("value");
        ws.send('{"type":"upload_doctor","doctor_id":"'+doctor_id+'","status":"'+status+'"}');
    }
    
    // 刷新用户列表框
    function flush_client_list(){
        var clientAll_list_slelect = $("#clientAll_list"); //所有在线人
        var client_list_slelect = $("#client_list");  //房间在线人
        var doctor_list=$("#doctor_list");  //设置医生列表
        clientAll_list_slelect.empty(); 
        client_list_slelect.empty();
        doctor_list.empty();
        client_list_slelect.append('<option value="all" id="cli_all">所有人</option>');
        for(var p in client_list){
            client_list_slelect.append('<option value="'+p+'">'+client_list[p]+'</option>');
            doctor_list.append('<option value="'+p+'">'+client_list[p]+'</option>');
        }

        //clientAll_list_slelect.append('<ul>');
        for(var i in clientAll_list)
        {
         clientAll_list_slelect.append('<li id="'+i+'">'+clientAll_list[i]+'</li>');
        }
       // clientAll_list_slelect.append('</ul>'); 

        $("#client_list").val(select_client_id);
        $("#doctor_list").val(doctor_list_id);
        
    }
    // 发言
    function say(from_client_id, from_client_name, content, time,url,ys,status){
    if(status==1)
    {
    $("#messageList").append('<div class="message me"><img class="avatar" src="'+url+'" /><div class="content"><div class="nickname">'+ys+' ：'+from_client_name+'<span class="time">'+time+'</span></div><div class="bubble bubble_primary right"><div class="bubble_cont"><div class="plain">'+replace_em(unescape(content))+'</div></div></div></div></div> ');
    }
    else 
    {
       $("#messageList").append('<div class="message"><img class="avatar" src="'+url+'"  /><div class="content"><div class="nickname">'+ys+' ：'+from_client_name+'<span class="time">'+time+'</span></div><div class="bubble bubble_default left"><div class="bubble_cont"><div class="plain">'+replace_em(unescape(content))+'</div></div></div></div></div>');
    } 
   // throttle(10,setdiv());
    //刷新滚动条
    setdiv();
    //1秒后停止刷新
    setTimeout(setdivcose,200);
   }
     //房间跳转
    function openurl(room_id)
    {
     var ys = $("#doctor_bz").val();
     window.location.href="http://"+document.domain+"/client.php?name=<?php echo urlencode($_GET['name']); ?>&headimgurl=<?php echo urlencode($_GET['headimgurl']); ?>&YS="+ys+"&openid=<?php echo urlencode($_GET['openid']); ?>&room_id="+room_id;
    }
     //表情解析
    function replace_em(str){
    //str = str.replace(/\</g,'&lt;');
    //str = str.replace(/\>/g,'&gt;');
    str = str.replace(/\n/g,'<br/>');
    str = str.replace(/\[em_([0-9]*)\]/g,'<img src="arclist/$1.gif" border="0" />');
    return str;
    }
    //刷新滚动条
    function setdiv()
    {
    var div = document.getElementById('messageList');
    div.scrollTop = div.scrollHeight;   
    t = setTimeout(setdiv,10);
    } 
    //停止刷新
    function setdivcose()
    {
     clearTimeout(t); 
    }
  var throttle = function(delay, action){
  var last = 0 ;
  return function(){
    var curr = +new Date();
    if (curr - last > delay){
      action.apply(this, arguments);
      last = curr ;
    }
  }
}
</script>
<script type="text/javascript">  
$(function(){
      toastr.options = {
        "closeButton": false,
        "debug": false,
        "newestOnTop": false,
        "progressBar": false,
        "positionClass": "toast-top-center",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "1000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
      };
  var up = $('#web_wechat_pic').Huploadify({
    auto:true,
    fileTypeExts:'*.jpg;*.gif;*.png;*.JPEG',
    multi:true,
    formData:{},
    fileSizeLimit:10240,
    showUploadedPercent:true,
    showUploadedSize:true,
    removeTimeout:500,
    uploader:'upload.php',
    onUploadStart:function(file){
      toastr.info(file.name+'开始上传');
    },
    onInit:function(obj){
      console.log('初始化');
      console.log(obj);
    },
    onUploadComplete:function(file,data){
      var img = "http://"+document.domain+"/uploads/"+data;
          img = "<img  class=img-responsive src="+img+">";
      var to_client_id = $("#client_list option:selected").attr("value");
      var to_client_name = $("#client_list option:selected").text();
      ws.send('{"type":"say","to_client_id":"'+to_client_id+'","to_client_name":"'+to_client_name+'","content":"'+unescape(img)+'"}');
      toastr.success(file.name+'上传完成');
      console.log(file.name+'上传完成');
    },
    onCancel:function(file){
      console.log(file.name+'删除成功');
    },
    onClearQueue:function(queueItemCount){
      console.log('有'+queueItemCount+'个文件被删除了');
    },
    onDestroy:function(){
      console.log('destroyed!');
    },
    onSelect:function(file){
      console.log(file.name+'加入上传队列');
    },
    onQueueComplete:function(queueData){
      console.log('队列中的文件全部上传完成',queueData);
    }
  });
  //更新选择的值
  select_client_id = 'all';
  $("#client_list").change(function(){
    select_client_id = $("#client_list option:selected").attr("value");
  });
  doctor_list_id = 'all';
  $("#doctor_list").change(function(){
    doctor_list_id = $("#doctor_list option:selected").attr("value");
  });
});
</script>
</html>  