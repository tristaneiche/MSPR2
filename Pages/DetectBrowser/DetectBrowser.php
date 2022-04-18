<?php
class DetectBrowser{
    function detect_browser() {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $browser        = "Inconnu";
        $browser_array = array( 
                    '/mobile/i'    => 'Handheld Browser',
                    '/msie/i'      => 'Internet Explorer',
                    '/trident/i'   => 'Internet Explorer',
                    '/firefox/i'   => 'Firefox',
                    '/safari/i'    => 'Safari',
                    '/chrome/i'    => 'Chrome',
                    '/edg/i'       => 'Edge',
                    '/opera/i'     => 'Opera'
        );
        foreach ($browser_array as $regex => $value)
        if (preg_match($regex, $user_agent))
            $browser = $value;
        return $browser;
    }
}


?>