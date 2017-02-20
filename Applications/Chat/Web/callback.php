<?
header("Content-Type:text/html;charset=utf-8");
session_start();
if($_GET['state']!=$_SESSION["wx_state"]){
      exit("5001");
}
$AppID="wxa1340e0924412e4e";
$AppSecret = '2838bf068c8466cac720d85723e983e4';
$url='https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$AppID.'&secret='.$AppSecret.'&code='.$_GET['code'].'&grant_type=authorization_code';
$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_URL, $url);
$json =  curl_exec($ch);
curl_close($ch);
$arr=json_decode($json,1);
if(empty($arr['access_token']) || empty($arr['openid']))
{
//header("Location: http://".$_SERVER['SERVER_NAME']); 
//特殊情况没有获取到用户数据 给个默认值
$name = '游客';
$headimgurl = "http://".$_SERVER['SERVER_NAME']."/img/moren.jpg";
$YS = "问者";
$strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
$max = strlen($strPol)-1;
for($i=0;$i<29;$i++){
    $str.=$strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
 }
$openid = $str;
header("Location: http://".$_SERVER['SERVER_NAME']."/client.php?name=".urlencode($name)."&headimgurl=".urlencode($headimgurl)."&YS=".urlencode($YS)."&openid=".urlencode($openid)."&room_id=1"); 
exit();
}
else
{
$access_token = $arr['access_token'];
$openid = $arr['openid'];	
}
$url='https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_URL, $url);
$json =  curl_exec($ch);
curl_close($ch);
$arr=json_decode($json,1);
if(empty($arr['nickname']) || empty($arr['headimgurl']))
{
//header("Location: http://".$_SERVER['SERVER_NAME']); 
exit('没有获取到用户名');
}
else
{
$name=$arr['nickname']; //用户名
$headimgurl=$arr['headimgurl']; //头像
}
include("./config.php");
//查询数据库 有值标志 医生
$sql = "select * from yisheng where openid = '".$openid."' ";
$rs = mysql_query($sql);
$rows=mysql_fetch_row($rs);
if(empty($rows))
{
 $YS = "问者"; //问者
}
else
{
 $YS = "医生"; //医生	
}
//跳转
header("Location: http://".$_SERVER['SERVER_NAME']."/client.php?name=".urlencode($name)."&headimgurl=".urlencode($headimgurl)."&YS=".urlencode($YS)."&openid=".urlencode($openid)."&room_id=1"); 
?>
