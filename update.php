<?php

//加载配置
include 'config.php';

//加载Telegram
include 'Telegram.php';
$telegram = new Telegram($bot_token);

//加载Redis
$redis = new Redis();
$redis->connect('127.0.0.1', 6379);

//获取message
$result = $telegram->getData();

$text = $result['message'] ['text'];
$chat_id = $result['message']['chat']['id'];
$message_id = $result['message']['message_id'];
$user_id = $result['message']['from']['id'];

//加载Game
include 'Game.php';
$game = new Game($config);
$game->tgid = $user_id;
$game->key = $key;

if (isset($text)) {

    $options = [['绑定账号', '账户查询', '下注'], ['游戏规则', '开奖查询', '取消']];

    $start = [ 
        "/start",
        "取消",
        "/newgame",
        '/newgame'.$bot_name
    ];

    $rule = "欢迎使用GameBot。本游戏每天根据-体彩排列五-第一位数-开奖，数据公平、公正、公开。";

    if (in_array($text,$start)) { //结束

        $redis->del($user_id);

        $keyb = $telegram->buildKeyBoard($options, $onetime = true, $resize = true, $selective = true, $selective = true);
        $content = ['chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => $rule, 'reply_to_message_id' => $message_id ];
        $log = $telegram->sendMessage($content);

    }elseif ($text == '绑定账号') { //结束

        $name = $game->register();

        if ($name) $texts = '绑定成功。用户名：'.$name;
        else $texts = '您的Telegram未绑定账号。';
        
        $content = ['chat_id' => $chat_id, 'text' => $texts, 'reply_to_message_id' => $message_id ];
        $log = $telegram->sendMessage($content);

    } elseif ($text == '账户查询') { //结束

        $text = $game->getinfo();

        $content = ['chat_id' => $chat_id, 'text' => $text, 'reply_to_message_id' => $message_id ];
        $log = $telegram->sendMessage($content);

    } elseif ($text == '游戏规则') { //结束

        $content = ['chat_id' => $chat_id, 'text' => $rule, 'reply_to_message_id' => $message_id ];
        $log = $telegram->sendMessage($content);

    } elseif ($text == '开奖查询') { //结束

        $text = $game->getopendata();
        $content = ['chat_id' => $chat_id, 'text' => $text, 'reply_to_message_id' => $message_id ];
        $log = $telegram->sendMessage($content);

    } elseif ($text == '下注') { //结束

        $option = [['余额', '流量'], ['取消']];
        $keyb = $telegram->buildKeyBoard($option, $onetime = true, $resize = true, $selective = true);
        $content = ['chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => '请选择货币类型', 'reply_to_message_id' => $message_id ];
        $log = $telegram->sendMessage($content);

    } elseif ($text == '余额' OR $text == '流量') { //结束

        $redis->hSet($user_id, '货币类型', $text);

        $option = [['大', '小', '单', '双'], ['取消']];
        $keyb = $telegram->buildKeyBoard($option, $onetime = true, $resize = true, $selective = true);
        $content = ['chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => '请选择投注的项目', 'reply_to_message_id' => $message_id ];
        $log = $telegram->sendMessage($content);

    } elseif (in_array($text,['大','小','单','双']) AND $redis->hExists($user_id, '货币类型') == 'true') { //结束

        $redis->hSet($user_id, '投注项目', $text);
        $option = [['1', '10', '1024', '10240'], ['取消']];
        $keyb = $telegram->buildKeyBoard($option, $onetime = true, $resize = true, $selective = true);
        $content = ['chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => '请请输入投注数额', 'reply_to_message_id' => $message_id ];

        $log = $telegram->sendMessage($content);

    } elseif (is_numeric($text) AND $redis->hExists($user_id, '投注项目') == 'true' AND $text > 0) {

        $res = $game->getuser();

        $data = intval(($res['transfer_enable']-$res['u']-$res['d'])/1024/1024);
        $type = $redis->hGet($user_id, '货币类型');
        if (($type == '流量' AND $data >= $text) OR ($type == '余额' AND $res['money'] >= $text)) {

            $rrr = $text*1024*1024;
            
            if ($type == '流量') 
                $game->setinfo($rrr,"[-]","L")
            else
            if ($type == '余额')
                $game->setinfo($text,"[-]","M")
            
            $expect = $game->setlottery($redis->hGet($user_id, '投注项目'),$type,$text);

            $redis->del($user_id);

            $texts = '恭喜您投注成功 体彩排列三 第'.$expect.'期。';
        } else {
            $texts = '流量或者余额不足';
        }

        $keyb = $telegram->buildKeyBoard($options, $onetime = true, $resize = true, $selective = true);
        $content = ['chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => $texts, 'reply_to_message_id' => $message_id ];
        $log = $telegram->sendMessage($content);
    }
}

if($delete){
    //记录机器人发送的消息和用户命令消息的ID
    if ($log AND $telegram->messageFromGroup()) {
      $mid = $log['result']['message_id'];
      $m = array('id' => $mid, 'time' => time(), 'chat_id' => $chat_id);
      $redis->lpush('gamebotmessage', serialize($m));
      $mm = array('id' => $message_id, 'time' => time(), 'chat_id' => $chat_id);
      $redis->lpush('gamebotmessage', serialize($mm));
    }

    //自动删除消息
    $me = $redis->lrange('gamebotmessage', 0, -1);
    foreach($me as $mee){
        $rsss = unserialize($mee);
      if (time() - $rsss['time'] > 20) {
        $content = array('chat_id' => $rsss['chat_id'], 'message_id' => $rsss['id']);
        $telegram->deleteMessage($content);
        $redis->lrem('gamebotmessage', $mee, 0);
      }
    }
}
?>