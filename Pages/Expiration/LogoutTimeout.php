<?php

class LogoutTimeout{
    function sessionLogout(){ 
        $logLength = 1200;
        $ctime = strtotime("now");
        if(!isset($_SESSION['sessionLogout'])){
            $_SESSION['sessionLogout'] = $ctime;  
        }else{ 
            if(((strtotime("now") - $_SESSION['sessionLogout']) > $logLength)){ 
                session_destroy();
                header("Location: ../connexion.php"); 
                exit; 
            }else{ 
                $_SESSION['sessionLogout'] = $ctime; 
            } 
        } 
    } 
}