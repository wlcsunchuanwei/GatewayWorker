<?php
error_reporting(E_ALL & ~E_NOTICE);
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);

/**
 * 聊天主逻辑
 * 主要是处理 onMessage onClose 
 */
use \GatewayWorker\Lib\Gateway;
use \GatewayWorker\Lib\Db;

class Events
{
   /**
    * 有消息时
    * @param int $client_id
    * @param mixed $message
    */
   public static function onMessage($client_id, $message)
   {
        // 客户端传递的是json数据
        $message_data = json_decode($message, true);
        if(!$message_data)
        {
            return ;
        }
        // 根据类型执行不同的业务
        switch($message_data['type'])
        {
            // 客户端回应服务端的心跳
            case 'pong':
                return;
            // 客户端登录 message格式: {type:login, name:xx, room_id:1} ，添加到客户端，广播给所有客户端xx进入聊天室
            case 'login':
                // 判断是否有房间号
                if(!isset($message_data['room_id']))
                {
                    throw new \Exception("\$message_data['room_id'] not set. client_ip:{$_SERVER['REMOTE_ADDR']} \$message:$message");
                }
                // 把房间号昵称放到session中
                $room_id = $message_data['room_id'];
                $client_name = htmlspecialchars($message_data['client_name']); //姓名
                $headimgurl = $message_data['headimgurl']; //头像
                $YS = $message_data['YS']; //yisheng
                $_SESSION['room_id'] = $room_id;
                $_SESSION['client_name'] = $client_name;
                $_SESSION['headimgurl'] = $headimgurl;
                $_SESSION['YS'] = $YS;
                $_SESSION['openid'] = $message_data['openid'];
                // 转播给当前房间的所有客户端，xx进入聊天室 message {type:login, client_id:xx, name:xx} 
                $new_message = array('type'=>$message_data['type'], 'client_id'=>$client_id, 'client_name'=>htmlspecialchars($client_name),'time'=>date(' H:i:s'),'headimgurl'=>$headimgurl,'YS'=>$YS,'room_id'=>$room_id,'status'=>0);
                //向房间所有客户端发送消息
                Gateway::sendToGroup($room_id, json_encode($new_message));
                   //加入房间
                 Gateway::joinGroup($client_id, $room_id);
                 // 获取房间内所有用户列表 
                 $clients_list = Gateway::getClientSessionsByGroup($room_id);
                 if(isset($clients_list))
                 {
                  foreach($clients_list as $tmp_client_id=>$item)
                  {
                    $clients_list[$tmp_client_id] = $item['client_name'];
                  }
                  // 给当前用户发送用户列表 
                  $new_message['client_list'] = $clients_list;
                 }
                //给当前用户发送
                Gateway::sendToCurrentClient(json_encode($new_message));
                //更新所有在线用户数据
                Events::clinetAll_list();
                return;
             //更新医生
            case 'upload_doctor';
                // 非法请求
                if(!isset($message_data['doctor_id']))
                {
                    throw new \Exception("\$_SESSION['room_id'] not set. client_ip:{$_SERVER['REMOTE_ADDR']}");
                }
                //状态 1 设置医生 0 取消医生
                $status = $message_data['status'];
                //获取提交过来的医生ID 
                $doctor_id = $message_data['doctor_id'];
                //获取医生的session数组
                $doctor=Gateway::getSession($doctor_id);
                //获取医生的openid值
                $doctor_openid=$doctor['openid'];
                //实力化数据库
                $db = Db::instance('db');
                //查询数据库
                $rs = $db->select('openid')->from('yisheng')->where("openid='".$doctor_openid."'")->row();

                //设置医生
                if($status==1)
                {
                //为空插入数据库
                if(empty($rs))
                {
                 $db->insert('yisheng')->cols(array('openid'=>$doctor_openid))->query();
                } 
                //更新session数组
                Gateway::updateSession($doctor_id, array('YS'=>'医生'));
                $doctor_content = '医生设置成功';
                }
                else
                {
                if(isset($rs))
                {
                $db->delete('yisheng')->where("openid='".$doctor_openid."'")->query();    
                } 
                //更新session数组
                Gateway::updateSession($doctor_id, array('YS'=>'问者'));
                $doctor_content = '医生取消成功';
                }
                //获取更新后的医生资料  
                $doctor_session=Gateway::getSession($doctor_id); 
                $oneself_message = array(
                        'type'=>'say',
                        'from_client_id'=>$client_id, 
                        'from_client_name' =>$_SESSION['client_name'],
                        'to_client_id'=>$doctor_id,
                        'content'=>"提示:".nl2br(htmlspecialchars($doctor_content)),
                        'time'=>date(' H:i:s'),
                        'headimgurl' =>$_SESSION['headimgurl'],
                        'YS'=>$_SESSION['YS'],
                        'status'=>1,
                    ); 
                //给自己发信息 
                Gateway::sendToCurrentClient(json_encode($oneself_message));
                $clickid_message = array(
                        'type'=>'say',
                        'from_client_id'=>$client_id, 
                        'from_client_name' =>$_SESSION['client_name'],
                        'to_client_id'=>$doctor_id,
                        'content'=>"对你说:".nl2br(htmlspecialchars($doctor_content)),
                        'time'=>date(' H:i:s'),
                        'headimgurl' =>$_SESSION['headimgurl'],
                        'YS'=>$_SESSION['YS'],
                        'status'=>0,
                    ); 
                //给别人发信息
                Gateway::sendToClient($doctor_id,json_encode($clickid_message));
                     $upload_data = array(
                        'type'=>'upload_doctor',
                        'YS'=>$doctor_session['YS'],
                       );      
                //给他人发送更新信息
                Gateway::sendToClient($doctor_id,json_encode($upload_data));
                        $upload_clinetAll = array(
                        'type'=>'upload_clinetAll',
                        ); 
                //更新所有在线用户数据
                Events::clinetAll_list();
               return;
            // 客户端发言 message: {type:say, to_client_id:xx, content:xx}
            case 'say':
                // 非法请求
                if(!isset($_SESSION['room_id']))
                {
                    throw new \Exception("\$_SESSION['room_id'] not set. client_ip:{$_SERVER['REMOTE_ADDR']}");
                }
                $room_id = $_SESSION['room_id'];
                $client_name = $_SESSION['client_name'];
                $headimgurl = $_SESSION['headimgurl'];
                // 私聊
                if($message_data['to_client_id'] != 'all')
                {
                    $new_message = array(
                        'type'=>'say',
                        'from_client_id'=>$client_id, 
                        'from_client_name' =>$client_name,
                        'to_client_id'=>$message_data['to_client_id'],
                        'content'=>"对你说: ".$message_data['content'],
                        'time'=>date(' H:i:s'),
                        'headimgurl' =>$headimgurl,
                        'YS'=>$_SESSION['YS'],
                        'status'=>0,
                    );
                    Gateway::sendToClient($message_data['to_client_id'], json_encode($new_message));
                    $new_message['content'] = "你对".htmlspecialchars($message_data['to_client_name'])."说: ".nl2br($message_data['content']);
                    $new_message['status'] = 1;
                    return Gateway::sendToCurrentClient(json_encode($new_message));
                }
                $new_message = array(
                    'type'=>'say', 
                    'from_client_id'=>$client_id,
                    'from_client_name' =>$client_name,
                    'to_client_id'=>'all',
                    'content'=>$message_data['content'],
                    'time'=>date(' H:i:s'),
                    'headimgurl' =>$headimgurl,
                    'YS'=>$_SESSION['YS'],
                    'status' => 0,
                );
                return Gateway::sendToGroup($room_id ,json_encode($new_message));
        }
   }
   
   /**
    * 当客户端断开连接时
    * @param integer $client_id 客户端id
    */
   public static function onClose($client_id)
   {
       
       // 从房间的客户端列表中删除
       if(isset($_SESSION['room_id']))
       {   
           //冲分组中删除
           Gateway::leaveGroup($client_id,$_SESSION['room_id']);
           $room_id = $_SESSION['room_id'];
           $new_message = array('type'=>'logout', 'from_client_id'=>$client_id, 'headimgurl'=>$_SESSION['headimgurl'], 'from_client_name'=>$_SESSION['client_name'], 'time'=>date('H:i:s'),'headimgurl' =>$_SESSION['headimgurl'],'YS'=>$_SESSION['YS'],'room_id'=>$_SESSION['room_id'],'status'=>0);
           Gateway::sendToAll(json_encode($new_message));
       }
   }

   /**
    * 获取所有在线用户并发送更新数据
    * @param integer $client_id 客户端id
    */
   public static function  clinetAll_list()
   {
        $upload_clinetAll = array('type'=>'upload_clinetAll',); 
        //获取所有在线用户
        $clinetAll = Gateway::getAllClientSessions();
        if(!empty($clinetAll))
        {
        foreach($clinetAll as $key=>$value)
        {
        $clinetAll[$key] ='房间'.$value['room_id'].' '.$value['YS'].':'.$value['client_name'];
        }   
        //所有用户列表
        $upload_clinetAll['clientAll_list'] = $clinetAll;  
        }   
        //给所有用户发送更新列表
        Gateway::sendToAll(json_encode($upload_clinetAll));
   }


}
