<?php
//加载配置
include 'config.php';

//加载Telegram
include 'Telegram.php';
$telegram = new Telegram($bot_token);

//加载Game
include 'Game.php';
$game = new Game($config);
$game->key = $key;

$text = $game->open();

if($text){
    $content = array('chat_id' => $group, 'text' => $text);
    $telegram->sendMessage($content);
}

echo 'success';
?>