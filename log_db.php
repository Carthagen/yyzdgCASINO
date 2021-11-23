<?php
require 'rb-mysql.php';

// Программа для отправки ответа

    R::setup( 'mysql:host=localhost;dbname=warskins_casino',
        'warskins_vik', 'k&SA9_a[uOso' ); //for both mysql or mariaDB
    R::freeze( true ); //will freeze redbeanphp    
        
    

// Программа для записи в базу данных всех действий пользователей

class LogDB
{
    
    
    public function __construct($username = null, $password = null)
    {
       
    }

    public function __destruct() 
    {
    }
    
    public function online($steamid) 
    {
        $login_ip = $_SERVER['REMOTE_ADDR'];
        $online = R::findOne('online', 'login_ip = ?', array($login_ip));
        
        if($online)
        {
            //update
            $online->lastvisit = time();
            R::store($online);
        }
        else
        {
            //create
            $online = R::dispense('online');
            $online->steamid = $steamid;
            $online->lastvisit = time();
            $online->login_ip = $login_ip;
            R::store($online);
        }
        $online_count = R::count('online',"lastvisit >" . ( time() - (3600) ));
        return $online_count;
    }

    public function check_user_id($steamid) //проверка юзеров  айди  
    {
        $result = R::findOne('users', 'steamid = ?', array($steamid));
        
     /*   $query =    "select steamid FROM users WHERE steamid=:user_id limit 1";
        $sth = $this->db->prepare($query);

        $sth->execute(
            array(
                ':user_id'      => $id      
            )
        );

        $result = $sth->fetch();
      */  
        if (!$result) 
        {
            return false;
        }
        return $result;
    }
    
    public function check_chat($steamid) //проверка на ввод смс в чат
    {
        $user = $this->check_user_id($steamid);
        
        $result = R::findOne('chat', 'id > ? ORDER BY id DESC', array($user -> chat_id ));
      
        if (!$result) 
        {
            return false;
        }
        $this->chat_last_id( $steamid, $result->id);    //она обновляет последний номер чата чтобы он повторно не отправлялся
        return $result;
    }
    
   
    
    public function registr($steamid, $personaname, $profileurl, $login_ip) // регистрация юзера в базу данных стимовских данных
    {
        $user = R::dispense('users');
		$user->personaname  =$personaname;
		$user->steamid      =$steamid;
		$user->profileurl   =$profileurl;
		$user->login_ip     =$login_ip;
		$id = R::store($user); 
                    /*$query =    "insert into users (steamid, personaname, profileurl, login_ip )
                                values (:steamid, :personaname, :profileurl, :login_ip)";
                    $sth = $this->db->prepare($query);
            
                    try {
                        $result = $sth->execute(
                            array(
                                ':steamid'      => $steamid,        // Номер в базе данных зарегистрированных узеров.
                                ':personaname'  => $personaname,       
                                ':profileurl'   => $profileurl,            
                                ':login_ip'     => $login_ip
                            )
                        );
                
                        } catch (\Exception $e) {
                            trigger_error( "kod25 Log_db: ". $e->getMessage() );
                            return;
                        }
                        return $result;
                     }
                     */
        return $id;
    }
    
    
    public function chat_msg( $steamid, $avatar, $personaname,  $msg, $login_ip) //смс в чат
    {
        $user = R::dispense('chat');
		$user->steamid  =$steamid;
		$user->avatar      =$avatar;
		$user->personaname   =$personaname;
		$user->msg    =$msg;
		$user->login_ip = $login_ip;
		$id=R::store($user); 
        /*$query =    "insert into chat ( steamid, avatar, personaname, msg, login_ip )
                    values ( :steamid, :avatar, :personaname, :msg, :login_ip )";
        $sth = $this->db->prepare($query);

        try {
            $result = $sth->execute(
                array(
                    ':steamid'      => $steamid,        // Номер в базе данных зарегистрированных узеров.
                    ':avatar'       => $avatar,
                    ':personaname'  => $personaname,       
                    ':msg'          => $msg,            
                    ':login_ip'     => $login_ip
                )
            );

        } catch (\Exception $e) {
            trigger_error( "kod129 Log_db: ". $e->getMessage() );
            return;
        }
        $id = $this->db->lastInsertId();
        return $id;
    
    */
    return $id;
    
    }
    public function chat_last_id( $steamid, $chat_id) //появление последнего смс написаного в бд
    {
        
        $user=$this->check_user_id($steamid);
        $user->chat_id = $chat_id;
        R::store( $user );
        
    }


        
   public function room_last_id( $steamid, $cf_room_id) //появление последней комнаты  в бд
    {
        
        $user=$this->check_user_id($steamid);
        $user->cf_room_id = $cf_room_id;
        R::store( $user );
       
        
    }
    
    public function joinroom_id( $steamid, $room_id) //прописываемв какую комнату зашел человек
    {
        
        $user=$this->check_user_id($steamid);
        $user->cf_inroomnow = $room_id;
        R::store( $user );
        
    }
}

// Эта функция нужна, чтобы многомерные массивы преобразовывались правильно и писались  в кодировке UTF8. ссылка на материал https://php.ru/forum/threads/json-i-php-problemy-s-kirillicej-v-utf-8.23658/
function json_fix_cyr($json_str) {
    $cyr_chars = array (
        '\u0430' => 'а', '\u0410' => 'А',
        '\u0431' => 'б', '\u0411' => 'Б',
        '\u0432' => 'в', '\u0412' => 'В',
        '\u0433' => 'г', '\u0413' => 'Г',
        '\u0434' => 'д', '\u0414' => 'Д',
        '\u0435' => 'е', '\u0415' => 'Е',
        '\u0451' => 'ё', '\u0401' => 'Ё',
        '\u0436' => 'ж', '\u0416' => 'Ж',
        '\u0437' => 'з', '\u0417' => 'З',
        '\u0438' => 'и', '\u0418' => 'И',
        '\u0439' => 'й', '\u0419' => 'Й',
        '\u043a' => 'к', '\u041a' => 'К',
        '\u043b' => 'л', '\u041b' => 'Л',
        '\u043c' => 'м', '\u041c' => 'М',
        '\u043d' => 'н', '\u041d' => 'Н',
        '\u043e' => 'о', '\u041e' => 'О',
        '\u043f' => 'п', '\u041f' => 'П',
        '\u0440' => 'р', '\u0420' => 'Р',
        '\u0441' => 'с', '\u0421' => 'С',
        '\u0442' => 'т', '\u0422' => 'Т',
        '\u0443' => 'у', '\u0423' => 'У',
        '\u0444' => 'ф', '\u0424' => 'Ф',
        '\u0445' => 'х', '\u0425' => 'Х',
        '\u0446' => 'ц', '\u0426' => 'Ц',
        '\u0447' => 'ч', '\u0427' => 'Ч',
        '\u0448' => 'ш', '\u0428' => 'Ш',
        '\u0449' => 'щ', '\u0429' => 'Щ',
        '\u044a' => 'ъ', '\u042a' => 'Ъ',
        '\u044b' => 'ы', '\u042b' => 'Ы',
        '\u044c' => 'ь', '\u042c' => 'Ь',
        '\u044d' => 'э', '\u042d' => 'Э',
        '\u044e' => 'ю', '\u042e' => 'Ю',
        '\u044f' => 'я', '\u042f' => 'Я',
 
        '\r' => '',
        '\n' => '<br />',
        '\t' => ''
    );
 
    foreach ($cyr_chars as $cyr_char_key => $cyr_char) {
        $json_str = str_replace($cyr_char_key, $cyr_char, $json_str);
    }
    return $json_str;
}
