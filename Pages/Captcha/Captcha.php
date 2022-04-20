<?php 
class Captcha{
    public $resultSite;
    public $resultSecret;
    function display_captchaSite(){
        global $wpdb;
        $resultSite = $wpdb->get_results("SELECT public_key FROM captcha");
	    return $resultSite;
        
    }
    function display_captchaSecret(){
        global $wpdb;
        $resultSecret = $wpdb->get_results("SELECT private_key FROM captcha");
	    return $resultSecret;
    }
}

?>