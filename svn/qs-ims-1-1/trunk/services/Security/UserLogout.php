<?php

/**
 * This can check user right, you can use in xml config or in the base controller
 * 
 * @author HuyBD
 *
 */
class Qss_Service_Security_UserLogout extends Qss_Service_Abstract
{

    public function __doExecute()
    {
        Qss_Session::destroy();
        Qss_Cookie::set('user_id', '');
        Qss_Cookie::set('pass_md5', '');
        
        // echo 'x'; die;
        if (isset($_SERVER['HTTP_COOKIE'])) {
            // echo 'x'; die;
            $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
            
            foreach ($cookies as $cookie) {
                
                $parts = explode('=', $cookie);
                $name = trim($parts[0]);
                if ($name != 'lang' && $name != 'dept_id' && $name != 'moduleOrders') {
                    setcookie($name, null, - 1);
                    setcookie($name, null, - 1, '/');
                }
            }
        }
    }
}