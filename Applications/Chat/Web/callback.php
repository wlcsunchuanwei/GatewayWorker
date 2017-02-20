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
$name=$arr['nickname']; //用户名
$headimgurl=$arr['headimgurl']; //头像
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
