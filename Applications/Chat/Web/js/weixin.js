var ws, name, client_list={},clients={};
    // 连接服务端
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
                say(data['client_id'], data['client_name'], data['client_name']+' 加入了聊天室 房间'+data['room_id'], data['time'],data['headimgurl'],data['YS']);
                if(data['client_list'])
                {
                    client_list = data['client_list'];
                }
                else
                {
                    client_list[data['client_id']] = data['client_name']; 
                    
                }
                if(data['tst'])
                {   
                    clients = data['tst'];
                }
                else
                {
                    clients[data['client_id']] = data['client_name'];
                }   
                flush_client_list();
                console.log(data['client_name']+"登录成功");
                break;
            // 发言
            case 'say':
                //{"type":"say","from_client_id":xxx,"to_client_id":"all/client_id","content":"xxx","time":"xxx"}
                say(data['from_client_id'], data['from_client_name'],data['content'], data['time'],data['headimgurl'],data['YS']);
                break;
            //更新医生 
            case 'upys':
                $("#ys_bz").val(data['YS']);
                if(data['YS']=='医生')
                {
                $("#simg").hide();  
                }   
                else
                {
                $("#simg").show();  
                }   
                clients = data['tst'];
                flush_client_list();
                break;
            // 用户退出 更新用户列表
            case 'logout':
                //{"type":"logout","client_id":xxx,"time":"xxx"}
                say(data['from_client_id'], data['from_client_name'], data['from_client_name']+' 退出了', data['time'],data['headimgurl'],data['YS']);
                delete client_list[data['from_client_id']];
                delete clients[data['from_client_id']];
                flush_client_list();
        }
    }
    // 提交对话
    function onSubmit() {
      var input = document.getElementById("textarea");
      var to_client_id = $("#client_list option:selected").attr("value");
      var to_client_name = $("#client_list option:selected").text();

      ws.send('{"type":"say","to_client_id":"'+to_client_id+'","to_client_name":"'+to_client_name+'","content":"'+escape(input.value)+'"}');
      input.value = "";
      input.focus();
    }
    //设置医生
    function addys()
    {
        var ys_id = $("#ys_list option:selected").attr("value");
        ws.send('{"type":"addys","ys_id":"'+ys_id+'"}');
    }
    
    //取消医生
    function delys()
    {   
        var ys_id = $("#ys_list option:selected").attr("value");
        ws.send('{"type":"delys","ys_id":"'+ys_id+'"}');
    }
    // 刷新用户列表框
    function flush_client_list(){
        var userlist_window = $("#userlist");
        var client_list_slelect = $("#client_list");
        var ys_list=$("#ys_list");
        ys_list.empty();
        userlist_window.empty();
        client_list_slelect.empty();
        userlist_window.append('<h4>在线用户</h4><ul>');
        client_list_slelect.append('<option value="all" id="cli_all">所有人</option>');
        for(var p in client_list){
          
            client_list_slelect.append('<option value="'+p+'">'+client_list[p]+'</option>');
            ys_list.append('<option value="'+p+'">'+client_list[p]+'</option>');
        }
        for(var i in clients)
        {
          userlist_window.append('<li id="'+i+'">'+clients[i]+'</li>');
        }   


        $("#client_list").val(select_client_id);
        userlist_window.append('</ul>');
    }

    // 发言
    function say(from_client_id, from_client_name, content, time,url,ys){
        $("#dialog").append('<div class="speech_item"><img src='+url+' width="40" height="40" class="user_icon" /> '+ys+':'+from_client_name+'<br> '+time+'<div style="clear:both;"></div><p class="triangle-isosceles top">'+unescape(content)+'</p> </div>');
    }
    //房间跳转
    function openurl(room_id)
    {
     var ys = $("#ys_bz").val();
     window.location.href="http://"+document.domain+"/ret.php?name=<?php echo urlencode($_GET['name']); ?>&headimgurl=<?php echo urlencode($_GET['headimgurl']); ?>&YS="+ys+"&openid=<?php echo urlencode($_GET['openid']); ?>&room_id="+room_id;
    }

    $(function(){
        select_client_id = 'all';
        $("#client_list").change(function(){
             select_client_id = $("#client_list option:selected").attr("value");
        });
    });

             <div class="message">  
                        <img class="avatar" src="img/1.jpg" />  
                        <div class="content">  
                            <div class="nickname">友萍 <span class="time">10:12:20</span></div>  
                            <div class="bubble bubble_default left">  
                                <div class="bubble_cont">  
                                    <div class="plain">  
                                        <pre>请你吃一个月大餐！</pre>  
                                    </div>  
                                </div>  
                            </div>  
                        </div>  
                    </div>  


