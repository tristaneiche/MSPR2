<?php

class LogoutTimeout{
    function sessionLogout(){ 
        $logLength = 30;
        $ctime = strtotime("now");
        if(!isset($_SESSION['sessionLogout'])){
            $_SESSION['sessionLogout'] = $ctime;  
        }else{ 
            if(((strtotime("now") - $_SESSION['sessionLogout']) > $logLength)){ 
                echo '<script type="text/javascript">
                window.onload = function () { alert("Session expiré"); } 
                </script>'; 
                session_destroy();
                header("Location: ../connexion.php"); 
                exit; 
            }else{ 
                $_SESSION['sessionLogout'] = $ctime; 
            } 
        } 
    } 
}