<?PHP

require('Captca/Captcha.php');
$captcha = new Captcha();

define('SITE_KEY', $captcha->display_captchaSite()[0]->option_value);
define('SECRET_KEY',$captcha->display_captchaSecret()[0]->option_value);
/*----------------------------------------------------------------------------------*/
function check_token($token, $secret_key) {
	$verif_url  = "https://www.google.com/recaptcha/api/siteverify?secret=$secret_key&response=$token";
	$curl = curl_init($verif_url);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$verif_response = curl_exec($curl);
	if ( empty($verif_response) ) return false;
	else { 
		$json = json_decode($verif_response);
		return $json->success;
	}
}
?>