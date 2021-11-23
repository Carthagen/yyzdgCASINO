<?php
require 'steamauth.php';
require 'userInfo.php';
require 'log_db.php';
    
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
    
    if(isset($_SESSION['steam_steamid'])) //установка в бд ситм_ид
    {
        
        //echo $steamprofile['avatar'];
        //echo "<a href='logout.php'>Выйти</a>";
        
	
	    $Log = new LogDB();
        $result = $Log->check_user_id($_SESSION['steam_steamid']);
		
        echo '
                <a href="" class="dep">
                    <div class="avatar">
                        <img src="'.$steamprofile['avatar'].'" alt="">
                    </div>
                </a>

  				<div class="info">
                      				
  					<div class="username">'.$steamprofile['personaname'].'</div>
  					<div class="money">Баланс: <span id="money"> '.$result['balance'].' </span><i class="fas fa-coins"></i></div>
  				</div> 
                <div class="btns">
                    <a class="btn-mini-profile" rel="popup" data-popup="popup-pay" style="background: #fa2c6b;"><i class="fa fa-plus" aria-hidden="true"></i></a>
                    <a class="btn-mini-profile" rel="popup" data-popup="popup-withdraw"><i class="fa fa-minus" aria-hidden="true"></i></a>
                </div>
                <div class="exit">
      				<a href="logout.php" class="btn"><i class="fa fa-sign-out" aria-hidden="true"></i></a>
      			</div>
        ';
        
        
    }
    else
    {
        loginButton();
        
    }
    
    
    
    
    
    

?>