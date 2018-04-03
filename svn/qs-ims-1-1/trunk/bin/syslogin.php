<?php
include "sysbase.php";
/*Login*/
$params = Qss_Params::getInstance();
$service = new Qss_Service();
$userinfo = new Qss_Model_UserInfo();
$userid = 'admin';
$pass = 'Admin1';
$dept = 1;
$pass = Qss_Util::hmac_md5($pass, 'EP');
$loginservice = $service->Security->UserLogin($userid, $pass, $dept,15,0);
$params->sessions->set('userinfo', $loginservice->getData());
$params->registers->set('userinfo', $loginservice->getData());
$loginret = $loginservice->getStatus();
?>