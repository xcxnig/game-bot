<?php

$config = [
		'database_type' => "mysql", //连接类型：mysql、mssql、sybase  
		'database_name' => "root", //数据库名  
		'server' => "127.0.0.1", //数据库地址   
		'port' => 3306,
		'username' => "root", //数据库账号  
		'password' => "123456", //数据库密码 
		'charset' => 'utf8'
];

$bot_token = '';  //bot_token
$bot_name = '@xx'; //bot_id
$group = '@xx'; //发送开奖信息的群组id
$delete = false; //是否开启自动删除消息
$key = '123'; //开奖网key 申请地址：http://vip.manycai.com

?>