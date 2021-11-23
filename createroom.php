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
    
    if( $start == 'createRoom' )
    {
        @$bet 	= $_POST['amount']; 
        if( isset($bet) AND (int)$bet )
        {
            $bet=(int)$bet;
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
            
            
            $avatar     = $_SESSION['steam_avatar'];
            // В базе данных создаем комнату
            $book = R::dispense('coinfliprooms');
            // Заполняем объект свойствами
            $book-> steamidwiner = $_SESSION['steam_steamid'];
            $book->bank = $bet * 2;
            $book->bet  = $bet;
            $book->avatar = $avatar;
            // Можно обращаться как к массиву
            // Сохраняем объект
            
            $id_room=R::store($book);
            
             // В базе данных прописываем людей в комнате
            $book = R::dispense('coinflipplayergroup');
            // Заполняем объект свойствами
            $book-> steamid = $_SESSION['steam_steamid'];
            $book->roomnumber = $id_room;
            $book->bet  = $bet;
            // Можно обращаться как к массиву
            // Сохраняем объект
            R::store($book);
            
            $user = $Log->check_user_id($_SESSION['steam_steamid']);
            $Log->room_last_id( $_SESSION['steam_steamid'], $id_room);
            $Log->joinroom_id( $_SESSION['steam_steamid'], $id_room); //она записывает в базу данных айди комнаты в которой находиться СЕЙЧАСС пользователь
            $return["data"] = array("status" => true, "message" => "Обновлены данные", "on" => $online, "id_room" => $id_room, "bet" => $bet, "avatar" => $avatar, "id" => $_SESSION['steam_steamid'], "balance" => $user['balance'], "chat"=>$chat_is, "chat_message" => $chat_messages );  
            echo json_encode($return);
        }
    }
    elseif($start == "Joinroom" ) { //срабатывет при присоединении к комнате
        @$id_room = $_POST['id_room'];
       
        if( isset($id_room) AND (int)$id_room )
        {
            $id_room=(int)$id_room;
            
            $result = R::findOne('coinfliprooms', 'id = ?', array($id_room));
            if($result AND $result->steamidwiner != $_SESSION['steam_steamid']){
                
                //проверка на то что заполнена ли комната
                if($result -> roomfull == 0){
                    
                
                    
                    $Log->joinroom_id( $_SESSION['steam_steamid'], $id_room);
                    $avatar     = $_SESSION['steam_avatar'];
                    //в этом блоке мы заносим в базу даных информацию о том пользователе который присоединился к комнате
                    $book = R::dispense('coinflipplayergroup');
                    // Заполняем объект свойствами
                    $book-> steamid = $_SESSION['steam_steamid'];
                    $book->roomnumber = $id_room;
                    $book->bet = $result->bet;
                    $book->avatarsecond = $avatar;
                    // Можно обращаться как к массиву
                    // Сохраняем объект
                    R::store($book);
                    
                    
                    
                    // определяем кто выиграл 
                    
                    $flipResult = rand(0, 1);
                    
                    $result -> flipresult = $flipResult;
                    // устанавливаем обазночение что комната полная
                    $result -> roomfull = 1;
                    R::store($result);
                    
                    
                    $return["data"] = $flipResult; 
                    echo json_encode($return);
                    
                    
                }else{
                    echo "Ошибка 3";
                }
            }
            else{
                echo "оштбка 2";
            }
        }  
        else{
            echo "оштбка 1";
        }
        
    }
    else //chat
    {

        //сделать другие функции
       
    }
}
else
{
    echo  "вы не зарегистрированны!";
}

?>