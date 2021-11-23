<?php
require 'steamauth.php';
require 'log_db.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Проверка запроса
@$start 	= $_POST['message']; 
  
    


//  Проверка сесиии и откуда пришел клиент
if( isset($start) AND isset($_SESSION['steam_steamid']) )
{
    $d          = new DateTime();
    $data       = $d->format('d/m/y h:i' );
    $Log        = new LogDB();
   
    if($start == 'startroom' )// распечатывает комнаты созданые на странице ДО то го как ты на эту страницу зашел
    {
        $open_rooms = array();
        $user = $Log->check_user_id($_SESSION['steam_steamid']);
        $cursor = R::getCursor('SELECT * FROM `coinfliprooms` WHERE `roomfull` = 0 ');
        while($result = $cursor->getNextItem()){        
            $open_rooms[] = array("time2" => $data,  "username" => 'personaname', "messages" => 'msg', "avatar" => $result['avatar'], "user_id" => $result['steamidwiner'], "bet" => $result['bet'], "id_room" => $result['id']);
        }
        
        $return["data"] = array("status" => true, "message" => "Обновлены данные", "room_message" => $open_rooms);  
        echo json_encode($return);
        
    }
    
    
    if( $start == 'online' ) // опрашивает что произошло
    {
        $online =  $Log->online($_SESSION['steam_steamid']);
        
        $result = $Log->check_chat($_SESSION['steam_steamid']);
        if($result){
            $chat_messages = array("time2" => $data, "username" => $result['personaname'], "messages" => $result['msg'], "avatar" => $result['avatar'], "user_id" => $result['steamid'], "admin" => $_SESSION['admin'], "moder" => $_SESSION['moder'], "youtuber" => $_SESSION['youtuber']);
            $chat_is = true;
        }
        else {
            $chat_messages = '';
            $chat_is = false;
        } 
        
        //проверяем создана ли комната
        $user = $Log->check_user_id($_SESSION['steam_steamid']);
        $result = R::findOne('coinfliprooms', 'id > ? ORDER BY id DESC', array($user -> cf_room_id  ));
        
       
        if($result) 
        {
            
            $Log->room_last_id( $_SESSION['steam_steamid'], $result->id);
            $room_messages = array("time2" => $data,  "username" => 'personaname', "messages" => 'msg', "avatar" => $result['avatar'], "user_id" => $result['steamidwiner'], "bet" => $result['bet'], "id_room" => $result['id']);
            $room_is = true;
            
        }
        else {
            $room_messages = '' ;
            $room_is = false;
        }
        
        //проверка на то что кто-то нажал на join
        $result = R::findOne('coinfliprooms', 'roomfull = 0 AND steamidwiner = ? ', array($_SESSION['steam_steamid']));
        if($result) 
        {
            $result_room = true;
            $result_room_id = $result-> id;
            $flipResult = $result -> flipresult;
            $join_room = true;
            $join_room_id = $result-> id;
            
            
            R::store($result); 
            //Здесь скачиваем аватарку того кто зашел в комнату, то есть нажал на кнопку join
            $result2 = R::findOne('coinflipplayergroup', ' roomnumber = ? AND avatarsecond != "" ', array($join_room_id));
            if($result2) {
                $join_room_avatar = array("avatarsecond" => $result2['avatarsecond']); //загружаем аватарку 
            }
            else{
                $join_room_avatar = '';
            }
        }
        else {
            $join_room = false;
            $join_room_id = false;
            $join_room_avatar = '';
            $flipResult = false;
            $result_room = false;
            $result_room_id = false;
        }
        
        
        
        $return["data"] = array("status" => true, "message" => "Обновлены данные", "on" => $online, "id" => $_SESSION['steam_steamid'], "balance" => $user['balance'], "chat"=>$chat_is, "chat_message" => $chat_messages, "room"=>$room_is, "room_message" => $room_messages, "join_room" => $join_room, "join_room_id" => $join_room_id, "result_room" => $result_room, "result_room_id" => $result_room_id, "join_room_avatar" => $join_room_avatar, "flipresult" => $flipResult );  
        echo json_encode($return);
        
        if (!$result) 
        {
            return false;
        }
        
    }
    else //chat записывает в базу
    {

        $username   = $_SESSION['steam_personaname'];
        $avatar     = $_SESSION['steam_avatar'];
        $login_ip   = $_SERVER['REMOTE_ADDR'];
        
        
        $chat_id = $Log->chat_msg( $_SESSION['steam_steamid'], $_SESSION['steam_avatar'], $_SESSION['steam_personaname'],  $start, $login_ip);
        //echo $chat_id;
        $Log->chat_last_id( $_SESSION['steam_steamid'], $chat_id);
        
        $chat_messages = array("time2" => $data, "username" => $username, "messages" => $start, "avatar" => $avatar, "user_id" => $_SESSION['steam_steamid'], "admin" => $_SESSION['admin'], "moder" => $_SESSION['moder'], "youtuber" => $_SESSION['youtuber']);
        $return["data"] = array("status" => 'success', "chat_message" => $chat_messages );  
        echo json_encode($return);
    }
    

}
else
{
    echo  "вы не зарегистрированны!";
}

?>