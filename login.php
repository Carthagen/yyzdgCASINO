<?php
//вхоод на проверку зареган ты или нет
require 'steamauth.php';
require 'userInfo.php';
require 'log_db.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if(isset($_SESSION['steamid']))
{
    $Log = new LogDB();
    
    if($Log->check_user_id($steamprofile['steamid']) )
    {
        //echo "user_est";
    }
    else
    {
        $result = $Log->registr($steamprofile['steamid'], $steamprofile['personaname'], $steamprofile['profileurl'], $_SERVER['REMOTE_ADDR']);
        
    }
    // destroy class
    $Log = null;
}
else
{
    echo "вы не зарегестрированы";
    
}

header("Location: index.php");








?>