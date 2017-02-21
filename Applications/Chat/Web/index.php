<?php
header("Content-type: text/html; charset=utf-8"); 
session_start();
$AppID="wxa1340e0924412e4e";
$AppSecret = '2838bf068c8466cac720d85723e983e4';
$callback  =  urlencode('http://s-210917.abc188.com/callback.php'); //回调地址
$ip = $_SERVER['REMOTE_ADDR'];
$time = time();
$state = md5($time.$ip);
$_SESSION["wx_state"] = $state; //存到SESSION 111
header("Location: https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$AppID."&redirect_uri=".$callback."&response_type=code&scope=snsapi_userinfo&state=".$state."#wechat_redirect"); 
?>
